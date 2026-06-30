<x-layouts.app heading="Approval">

    @php
        $tabs = [
            'customer'   => ['label' => 'Customer Kredit', 'count' => $customerCredits->count()],
            'override'   => ['label' => 'Override Limit',  'count' => $creditOverrides->count()],
            'adjustment' => ['label' => 'Stock Adjustment','count' => $stockAdjustments->count()],
            'return'     => ['label' => 'Customer Return', 'count' => $customerReturns->count()],
            'writeoff'   => ['label' => 'Write-off',       'count' => $stockWriteoffs->count()],
        ];
    @endphp

    {{-- Tab navigation --}}
    <div class="mb-5 flex gap-1 overflow-x-auto rounded-xl border border-slate-200 bg-white p-1">
        @foreach($tabs as $key => $t)
        <a href="/owner/approvals?tab={{ $key }}"
           class="flex flex-shrink-0 items-center gap-2 rounded-lg px-3 py-2 text-xs font-semibold transition-colors
                  {{ $tab === $key ? 'bg-slate-900 text-white' : 'text-slate-500 hover:bg-slate-100 hover:text-slate-700' }}">
            {{ $t['label'] }}
            @if($t['count'] > 0)
            <span class="rounded-full px-1.5 py-0.5 text-[10px] font-bold
                         {{ $tab === $key ? 'bg-amber-400 text-slate-900' : 'bg-amber-100 text-amber-700' }}">
                {{ $t['count'] }}
            </span>
            @endif
        </a>
        @endforeach
    </div>

    {{-- ── Customer Kredit ──────────────────────────────── --}}
    @if($tab === 'customer')
        @forelse($customerCredits as $c)
        <div class="mb-4 rounded-xl border border-slate-200 bg-white">
            <div class="flex items-start justify-between px-5 py-4">
                <div>
                    <h3 class="font-semibold text-slate-900">{{ $c->customer_name }}</h3>
                    <p class="mt-0.5 text-xs text-slate-400">
                        Diajukan oleh <span class="font-medium text-slate-600">{{ $c->requestedBy->name ?? '-' }}</span>
                        · {{ $c->created_at->diffForHumans() }}
                    </p>
                </div>
                <span class="rounded-full bg-amber-100 px-2.5 py-1 text-xs font-bold text-amber-700">Pending</span>
            </div>
            <div class="grid grid-cols-3 gap-4 border-t border-slate-50 px-5 py-3">
                <div>
                    <p class="text-xs text-slate-400">Tipe</p>
                    <p class="text-sm font-semibold text-slate-900">{{ $c->customer_type }}</p>
                </div>
                <div>
                    <p class="text-xs text-slate-400">Limit diminta</p>
                    <p class="text-sm font-semibold text-slate-900">Rp {{ number_format($c->credit_limit, 0, ',', '.') }}</p>
                </div>
                <div>
                    <p class="text-xs text-slate-400">Term</p>
                    <p class="text-sm font-semibold text-slate-900">{{ $c->credit_term_days }} hari</p>
                </div>
            </div>
            <p class="border-t border-slate-50 px-5 py-2 text-xs text-slate-400">{{ $c->address }}</p>
            <div class="flex gap-2 border-t border-slate-100 px-5 py-3">
                <form method="POST" action="{{ route('owner.approvals.customer-credit.approve', $c) }}" class="flex flex-1 items-center gap-2">
                    @csrf
                    <input type="number" name="credit_limit"
                           class="w-40 rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-amber-400 focus:outline-none focus:ring-2 focus:ring-amber-100"
                           value="{{ $c->credit_limit }}" placeholder="Limit disetujui">
                    <button class="flex items-center gap-1.5 rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700 transition-colors">
                        <x-heroicon-m-check class="h-4 w-4"/>
                        Setujui
                    </button>
                </form>
                <form method="POST" action="{{ route('owner.approvals.customer-credit.reject', $c) }}" class="flex items-center gap-2">
                    @csrf
                    <input type="text" name="notes"
                           class="w-48 rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-red-300 focus:outline-none focus:ring-2 focus:ring-red-100"
                           placeholder="Alasan penolakan" required>
                    <button class="flex items-center gap-1.5 rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700 transition-colors">
                        <x-heroicon-m-x-mark class="h-4 w-4"/>
                        Tolak
                    </button>
                </form>
            </div>
        </div>
        @empty
        <div class="rounded-xl border border-slate-200 bg-white px-5 py-10 text-center text-sm text-slate-400">
            Tidak ada pengajuan customer kredit.
        </div>
        @endforelse
    @endif

    {{-- ── Credit Override ──────────────────────────────── --}}
    @if($tab === 'override')
        @forelse($creditOverrides as $o)
        <div class="mb-4 rounded-xl border border-slate-200 bg-white">
            <div class="flex items-start justify-between px-5 py-4">
                <div>
                    <h3 class="font-semibold text-slate-900">{{ $o->customer->customer_name ?? '-' }}</h3>
                    <p class="mt-0.5 text-xs text-slate-400">
                        Diajukan oleh <span class="font-medium text-slate-600">{{ $o->requestedBy->name ?? '-' }}</span>
                        · {{ $o->requested_at->diffForHumans() }}
                    </p>
                </div>
                <span class="rounded-full bg-amber-100 px-2.5 py-1 text-xs font-bold text-amber-700">Pending</span>
            </div>
            <div class="grid grid-cols-2 gap-4 border-t border-slate-50 px-5 py-3 md:grid-cols-4">
                <div>
                    <p class="text-xs text-slate-400">Sales Order</p>
                    <p class="text-sm font-semibold text-slate-900 font-mono">{{ $o->salesOrder->document_number ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-xs text-slate-400">Nilai Order</p>
                    <p class="text-sm font-semibold text-slate-900">Rp {{ number_format($o->order_amount, 0, ',', '.') }}</p>
                </div>
                <div>
                    <p class="text-xs text-slate-400">Outstanding</p>
                    <p class="text-sm font-semibold text-slate-900">Rp {{ number_format($o->outstanding_at_request, 0, ',', '.') }}</p>
                </div>
                <div>
                    <p class="text-xs text-slate-400">Limit saat ini</p>
                    <p class="text-sm font-semibold text-slate-900">Rp {{ number_format($o->credit_limit_at_request, 0, ',', '.') }}</p>
                </div>
            </div>
            <div class="flex gap-2 border-t border-slate-100 px-5 py-3">
                <form method="POST" action="{{ route('owner.approvals.credit-override.approve', $o) }}">
                    @csrf
                    <button class="flex items-center gap-1.5 rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700 transition-colors">
                        <x-heroicon-m-check class="h-4 w-4"/>
                        Setujui
                    </button>
                </form>
                <form method="POST" action="{{ route('owner.approvals.credit-override.reject', $o) }}" class="flex items-center gap-2">
                    @csrf
                    <input type="text" name="notes"
                           class="w-48 rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-red-300 focus:outline-none focus:ring-2 focus:ring-red-100"
                           placeholder="Alasan" required>
                    <button class="flex items-center gap-1.5 rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700 transition-colors">
                        <x-heroicon-m-x-mark class="h-4 w-4"/>
                        Tolak
                    </button>
                </form>
            </div>
        </div>
        @empty
        <div class="rounded-xl border border-slate-200 bg-white px-5 py-10 text-center text-sm text-slate-400">
            Tidak ada override pending.
        </div>
        @endforelse
    @endif

    {{-- ── Stock Adjustment ─────────────────────────────── --}}
    @if($tab === 'adjustment')
        @forelse($stockAdjustments as $a)
        <div class="mb-4 rounded-xl border border-slate-200 bg-white">
            <div class="flex items-start justify-between px-5 py-4">
                <div>
                    <h3 class="font-mono text-sm font-semibold text-slate-900">{{ $a->document_number }}</h3>
                    <p class="mt-0.5 text-xs text-slate-400">
                        Diajukan oleh <span class="font-medium text-slate-600">{{ $a->createdBy->name ?? '-' }}</span>
                        · {{ $a->created_at->diffForHumans() }}
                    </p>
                </div>
                <span class="rounded-full bg-amber-100 px-2.5 py-1 text-xs font-bold text-amber-700">Pending</span>
            </div>
            <div class="grid grid-cols-2 gap-4 border-t border-slate-50 px-5 py-3 md:grid-cols-4">
                <div>
                    <p class="text-xs text-slate-400">Produk</p>
                    <p class="text-sm font-semibold text-slate-900">{{ $a->product->product_name ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-xs text-slate-400">Qty</p>
                    <p class="text-sm font-semibold text-slate-900">{{ $a->qty }}</p>
                </div>
                <div>
                    <p class="text-xs text-slate-400">Alasan</p>
                    <p class="text-sm font-semibold text-slate-900">{{ $a->reason }}</p>
                </div>
                <div>
                    <p class="text-xs text-slate-400">Sumber</p>
                    <p class="text-sm font-semibold text-slate-900">{{ $a->source_context }}</p>
                </div>
            </div>
            @if($a->notes)
            <p class="border-t border-slate-50 px-5 py-2 text-xs text-slate-400">{{ $a->notes }}</p>
            @endif
            <div class="flex gap-2 border-t border-slate-100 px-5 py-3">
                <form method="POST" action="{{ route('owner.approvals.stock-adjustment.approve', $a) }}">
                    @csrf
                    <button class="flex items-center gap-1.5 rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700 transition-colors">
                        <x-heroicon-m-check class="h-4 w-4"/>
                        Setujui
                    </button>
                </form>
                <form method="POST" action="{{ route('owner.approvals.stock-adjustment.reject', $a) }}" class="flex items-center gap-2">
                    @csrf
                    <input type="text" name="notes"
                           class="w-48 rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-red-300 focus:outline-none focus:ring-2 focus:ring-red-100"
                           placeholder="Alasan" required>
                    <button class="flex items-center gap-1.5 rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700 transition-colors">
                        <x-heroicon-m-x-mark class="h-4 w-4"/>
                        Tolak
                    </button>
                </form>
            </div>
        </div>
        @empty
        <div class="rounded-xl border border-slate-200 bg-white px-5 py-10 text-center text-sm text-slate-400">
            Tidak ada stock adjustment pending.
        </div>
        @endforelse
    @endif

    {{-- ── Customer Return ──────────────────────────────── --}}
    @if($tab === 'return')
        @forelse($customerReturns as $r)
        <div class="mb-4 rounded-xl border border-slate-200 bg-white">
            <div class="flex items-start justify-between px-5 py-4">
                <div>
                    <h3 class="font-mono text-sm font-semibold text-slate-900">{{ $r->document_number }}</h3>
                    <p class="mt-0.5 text-xs text-slate-400">
                        <span class="font-medium text-slate-600">{{ $r->customer->customer_name ?? '-' }}</span>
                        · Diajukan oleh {{ $r->createdBy->name ?? '-' }}
                        · {{ $r->created_at->diffForHumans() }}
                    </p>
                </div>
                <span class="rounded-full bg-amber-100 px-2.5 py-1 text-xs font-bold text-amber-700">Pending</span>
            </div>
            <div class="grid grid-cols-3 gap-4 border-t border-slate-50 px-5 py-3">
                <div>
                    <p class="text-xs text-slate-400">Invoice</p>
                    <p class="text-sm font-semibold text-slate-900 font-mono">{{ $r->invoice->invoice_number ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-xs text-slate-400">Total</p>
                    <p class="text-sm font-semibold text-slate-900">Rp {{ number_format($r->total_amount, 0, ',', '.') }}</p>
                </div>
                <div>
                    <p class="text-xs text-slate-400">Alasan</p>
                    <p class="text-sm font-semibold text-slate-900">{{ $r->reason }}</p>
                </div>
            </div>
            <div class="flex gap-2 border-t border-slate-100 px-5 py-3">
                <form method="POST" action="{{ route('owner.approvals.customer-return.approve', $r) }}">
                    @csrf
                    <button class="flex items-center gap-1.5 rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700 transition-colors">
                        <x-heroicon-m-check class="h-4 w-4"/>
                        Setujui
                    </button>
                </form>
                <form method="POST" action="{{ route('owner.approvals.customer-return.reject', $r) }}" class="flex items-center gap-2">
                    @csrf
                    <input type="text" name="notes"
                           class="w-48 rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-red-300 focus:outline-none focus:ring-2 focus:ring-red-100"
                           placeholder="Alasan" required>
                    <button class="flex items-center gap-1.5 rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700 transition-colors">
                        <x-heroicon-m-x-mark class="h-4 w-4"/>
                        Tolak
                    </button>
                </form>
            </div>
        </div>
        @empty
        <div class="rounded-xl border border-slate-200 bg-white px-5 py-10 text-center text-sm text-slate-400">
            Tidak ada customer return pending.
        </div>
        @endforelse
    @endif

    {{-- ── Stock Write-off ──────────────────────────────── --}}
    @if($tab === 'writeoff')
        @forelse($stockWriteoffs as $w)
        <div class="mb-4 rounded-xl border border-slate-200 bg-white">
            <div class="flex items-start justify-between px-5 py-4">
                <div>
                    <h3 class="font-mono text-sm font-semibold text-slate-900">{{ $w->document_number }}</h3>
                    <p class="mt-0.5 text-xs text-slate-400">
                        Diajukan oleh <span class="font-medium text-slate-600">{{ $w->createdBy->name ?? '-' }}</span>
                        · {{ $w->created_at->diffForHumans() }}
                    </p>
                </div>
                <span class="rounded-full bg-amber-100 px-2.5 py-1 text-xs font-bold text-amber-700">Pending</span>
            </div>
            <div class="grid grid-cols-3 gap-4 border-t border-slate-50 px-5 py-3">
                <div>
                    <p class="text-xs text-slate-400">Produk</p>
                    <p class="text-sm font-semibold text-slate-900">{{ $w->product->product_name ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-xs text-slate-400">Qty</p>
                    <p class="text-sm font-semibold text-slate-900">{{ $w->qty }}</p>
                </div>
                <div class="col-span-3">
                    <p class="text-xs text-slate-400">Alasan</p>
                    <p class="text-sm text-slate-900">{{ $w->reason }}</p>
                </div>
            </div>
            <div class="flex gap-2 border-t border-slate-100 px-5 py-3">
                <form method="POST" action="{{ route('owner.approvals.stock-writeoff.approve', $w) }}">
                    @csrf
                    <button class="flex items-center gap-1.5 rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700 transition-colors">
                        <x-heroicon-m-check class="h-4 w-4"/>
                        Setujui
                    </button>
                </form>
                <form method="POST" action="{{ route('owner.approvals.stock-writeoff.reject', $w) }}" class="flex items-center gap-2">
                    @csrf
                    <input type="text" name="notes"
                           class="w-48 rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-red-300 focus:outline-none focus:ring-2 focus:ring-red-100"
                           placeholder="Alasan" required>
                    <button class="flex items-center gap-1.5 rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700 transition-colors">
                        <x-heroicon-m-x-mark class="h-4 w-4"/>
                        Tolak
                    </button>
                </form>
            </div>
        </div>
        @empty
        <div class="rounded-xl border border-slate-200 bg-white px-5 py-10 text-center text-sm text-slate-400">
            Tidak ada stock write-off pending.
        </div>
        @endforelse
    @endif

</x-layouts.app>
