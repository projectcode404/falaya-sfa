<?php

namespace App\Livewire\Pwa;

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

    public function render()
    {
        return view('livewire.pwa.sales-order-create', [
            'total' => $this->getTotal(),
        ])->layout('components.pwa.layout', ['title' => 'Buat Pesanan']);
    }
}
