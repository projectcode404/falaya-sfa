<?php

namespace App\Livewire\Pwa;

use App\Models\VisitPlan;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class VisitDetail extends Component
{
    public VisitPlan $visitPlan;

    public function mount(VisitPlan $visitPlan): void
    {
        if ($visitPlan->salesman_id !== Auth::id()) {
            abort(403);
        }

        $this->visitPlan = $visitPlan->load(['customer', 'realization']);
    }

    public function render()
    {
        return view('livewire.pwa.visit-detail')
            ->layout('components.pwa.layout', ['title' => 'Detail Kunjungan']);
    }
}
