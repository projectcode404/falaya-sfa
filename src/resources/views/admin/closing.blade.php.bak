<x-layouts.app heading="Tutup Hari Operasional">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h3 class="card-title">
                        Closing — {{ $today->translatedFormat('l, d F Y') }}
                    </h3>
                </div>
                <div class="card-body">
                    @if($result['can_close'])
                        <div class="alert alert-success">
                            <div class="d-flex align-items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="icon alert-icon me-2" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><polyline points="20 6 9 17 4 12"/></svg>
                                <div>
                                    <strong>Semua transaksi sudah selesai.</strong>
                                    Siap menutup hari operasional.
                                </div>
                            </div>
                        </div>

                        @if(isset($result['summary']))
                            <div class="mb-3 text-muted small">
                                @foreach($result['summary'] as $line)
                                    <div>· {{ $line }}</div>
                                @endforeach
                            </div>
                        @endif

                        <form method="POST" action="{{ route('admin.closing.execute') }}"
                              onsubmit="return confirm('Tutup hari operasional {{ $today->toDateString() }}? Tindakan ini tidak dapat dibatalkan.')">
                            @csrf
                            <button type="submit" class="btn btn-danger w-100">
                                Tutup Hari Operasional Sekarang
                            </button>
                        </form>
                    @else
                        <div class="alert alert-danger">
                            <strong>❌ Belum bisa ditutup.</strong>
                            {{ count($result['blockers']) }} hal perlu diselesaikan:
                        </div>

                        <div class="list-group mb-4">
                            @foreach($result['blockers'] as $blocker)
                                <div class="list-group-item list-group-item-danger">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <div class="fw-bold">🔴 {{ $blocker['label'] }}</div>
                                            @if(isset($blocker['detail']))
                                                <div class="small text-muted mt-1">{{ $blocker['detail'] }}</div>
                                            @endif
                                        </div>
                                        @if(isset($blocker['url']))
                                            <a href="{{ $blocker['url'] }}" class="btn btn-sm btn-outline-danger ms-3">
                                                Selesaikan →
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="alert alert-info small">
                            💡 Pengajuan approval (Stock Adjustment, Customer Return, dll) tidak menghalangi penutupan hari.
                        </div>

                        <form method="GET" action="{{ route('admin.closing') }}">
                            <button type="submit" class="btn btn-outline-primary w-100">
                                🔄 Cek Ulang Status
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
