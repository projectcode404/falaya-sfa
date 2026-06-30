<?php

namespace App\Livewire\Pwa;

use App\Actions\Sales\CreateSalesOrderAction;
use App\Actions\Sales\PostSalesOrderAction;
use App\Actions\Sales\RequestCreditOverrideAction;
use App\Models\Invoice;
use App\Models\StockBalance;
use App\Models\VisitPlan;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class SalesOrderCreate extends Component
{
    public VisitPlan $visitPlan;

    public string $payment_type = 'CASH';

    public string $receiver_name = '';

    public array $items = [];

    public int $step = 1;

    public string $search = '';

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

    public function nextStep(): void
    {
        $total = collect($this->items)->sum(fn ($i) => $i['qty'] * $i['unit_price']);
        if ($total > 0) {
            $this->step = 2;
        }
    }

    public function prevStep(): void
    {
        $this->step = 1;
    }

    public function setPaymentType(string $type): void
    {
        $this->payment_type = $type;
    }

    public function incrementQty(int $productId): void
    {
        foreach ($this->items as $i => $item) {
            if ((int) $item['product_id'] === $productId) {
                if ($this->items[$i]['qty'] < $this->items[$i]['max_qty']) {
                    $this->items[$i]['qty']++;
                }
                break;
            }
        }
    }

    public function decrementQty(int $productId): void
    {
        foreach ($this->items as $i => $item) {
            if ((int) $item['product_id'] === $productId) {
                if ($this->items[$i]['qty'] > 0) {
                    $this->items[$i]['qty']--;
                }
                break;
            }
        }
    }

    public function setQty(int $productId, mixed $value): void
    {
        $qty = max(0, (int) $value);
        foreach ($this->items as $i => $item) {
            if ((int) $item['product_id'] === $productId) {
                $this->items[$i]['qty'] = min($qty, (int) $this->items[$i]['max_qty']);
                break;
            }
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
            $this->redirect(route('pwa.pages.visits.detail', $this->visitPlan->id), navigate: false);

            return;
        } catch (QueryException $e) {
            if (str_contains($e->getMessage(), 'uq_one_active_so_per_visit')) {
                $this->submitError = 'Kunjungan ini sudah punya pesanan aktif. Muat ulang halaman untuk melihat status terkini.';

                return;
            }
            $this->submitError = 'Terjadi kesalahan sistem. Coba lagi atau hubungi Admin.';
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
        $customer = $this->visitPlan->customer;

        // Outstanding untuk credit limit bar
        $customerOutstanding = 0;
        if ($customer->customer_type === 'CREDIT') {
            $customerOutstanding = Invoice::where('customer_id', $customer->id)
                ->whereIn('status', ['UNPAID', 'PARTIAL', 'OVERDUE'])
                ->sum('remaining_amount');
        }

        // Produk yang tersedia (dari stok bawaan salesman)
        $stockBalances = StockBalance::with('product')
            ->where('holder_type', 'SALESMAN')
            ->where('holder_id', Auth::id())
            ->where('condition', 'GOOD')
            ->where('qty', '>', 0)
            ->get();

        $availableProducts = $stockBalances->map(fn ($s) => $s->product)->filter()->when($this->search, fn ($c) => $c->filter(fn ($p) => str_contains(strtolower($p->product_name), strtolower($this->search))))->values();

        $stockItems = $stockBalances->map(fn ($s) => [
            'product_id' => $s->product_id,
            'qty' => (float) $s->qty,
        ])->values();

        // Items sebagai collection untuk firstWhere di blade
        $items = collect($this->items);

        return view('livewire.pwa.sales-order-create', [
            'total' => collect($this->items)->sum(fn ($i) => $i['qty'] * $i['unit_price']),
            'customer' => $customer,
            'customerOutstanding' => (float) $customerOutstanding,
            'availableProducts' => $availableProducts,
            'stockItems' => $stockItems,
            'items' => $items,
            'paymentType' => $this->payment_type,
            'receiverName' => $this->receiver_name,
            'submitError' => $this->submitError,
            'submitSuccess' => $this->submitSuccess,
            'step' => $this->step,
            'overLimit' => $customer->customer_type === 'CREDIT' && $customer->credit_limit
                ? (($customerOutstanding + collect($this->items)->sum(fn ($i) => $i['qty'] * $i['unit_price'])) > $customer->credit_limit)
                : false,
        ])->layout('components.pwa.layout', ['title' => 'Buat Pesanan']);
    }
}
