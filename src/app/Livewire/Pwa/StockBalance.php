<?php

namespace App\Livewire\Pwa;

use App\Models\StockBalance as StockBalanceModel;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class StockBalance extends Component
{
    public function render()
    {
        $stok = StockBalanceModel::with('product')
            ->where('holder_type', 'SALESMAN')
            ->where('holder_id', Auth::id())
            ->where('condition', 'GOOD')
            ->orderBy('qty', 'desc')
            ->get();
        $totalQty = $stok->sum('qty');

        return view('livewire.pwa.stock-balance', [
            'stok' => $stok,
            'totalQty' => $totalQty,
        ])->layout('components.pwa.layout', ['title' => 'Stok Bawaan']);
    }
}
