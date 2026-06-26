<?php

namespace App\Livewire\Pwa;

use App\Actions\Sales\CreateSalesOrderAction;
use App\Actions\Sales\PostSalesOrderAction;
use App\Actions\Sales\RequestCreditOverrideAction;
use App\Models\StockBalance;
use App\Models\VisitPlan;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class SalesOrderCreate extends Component
{
    public VisitPlan $visitPlan;

    public string $payment_type = 'CASH';

    public string $receiver_name = '';

    public array $items = [];

    public string $submitError = '';

    public string $submitSuccess = '';

    public function mount(VisitPlan $visitPlan): void
    {
        if ($visitPlan->salesman_id !== Auth::id()) {
            abort(403);
        }

        if (! in_array($visitPlan->status, ['IN_PROGRESS', 'PLANNED'])) {
            abort(403, 'Kunjungan tidak dalam status yang valid untuk membuat order.');
        }

        $this->visitPlan = $visitPlan->load('customer');

        // Init items dari stok bawaan salesman
        $stok = StockBalance::with('product')
            ->where('holder_type', 'SALESMAN')
            ->where('holder_id', Auth::id())
            ->where('condition', 'GOOD')
            ->where('qty', '>', 0)
            ->get();

        $this->items = $stok->map(fn ($s) => [
            'product_id' => $s->product_id,
            'product_name' => $s->product->product_name,
            'unit' => $s->product->unit,
            'unit_price' => (float) $s->product->selling_price,
            'max_qty' => (float) $s->qty,
            'qty' => 0,
        ])->values()->toArray();
    }

    public function incrementQty(int $index): void
    {
        if ($this->items[$index]['qty'] < $this->items[$index]['max_qty']) {
            $this->items[$index]['qty']++;
        }
    }

    public function decrementQty(int $index): void
    {
        if ($this->items[$index]['qty'] > 0) {
            $this->items[$index]['qty']--;
        }
    }

    public function getTotal(): float
    {
        return collect($this->items)->sum(fn ($i) => $i['qty'] * $i['unit_price']);
    }

    public function submitOrder(
        CreateSalesOrderAction $createAction,
        PostSalesOrderAction $postAction,
        RequestCreditOverrideAction $overrideAction
    ): void {
        $this->submitError = '';
        $this->submitSuccess = '';

        $itemsToSend = array_values(array_filter($this->items, fn ($i) => $i['qty'] > 0));

        if (empty($itemsToSend)) {
            $this->submitError = 'Pilih minimal 1 produk.';

            return;
        }

        $salesOrder = null;

        try {
            $salesOrder = $createAction->execute(
                $this->visitPlan->id,
                $this->visitPlan->customer_id,
                Auth::id(),
                $this->payment_type,
                $itemsToSend,
                $this->receiver_name ?: null,
            );

            $salesOrder = $postAction->execute($salesOrder);

            $this->submitSuccess = "Order {$salesOrder->document_number} berhasil!";
            $this->dispatch('order-success');

        } catch (\RuntimeException $e) {
            if ($salesOrder && str_contains($e->getMessage(), 'credit limit')) {
                $overrideAction->execute($salesOrder->fresh());
                $this->submitError = 'Melebihi limit kredit. Permintaan override dikirim ke Owner.';

                return;
            }
            $this->submitError = $e->getMessage();
        } catch (\LogicException $e) {
            $this->submitError = $e->getMessage();
        }
    }

    public function render()
    {
        return view('livewire.pwa.sales-order-create', [
            'total' => $this->getTotal(),
        ])->layout('components.pwa.layout', ['title' => 'Buat Pesanan']);
    }
}
