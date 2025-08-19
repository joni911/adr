@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">Hasil Validasi Transaksi</h4>
                    <div>
                        <span class="badge bg-light text-dark">
                            {{ $responseData['count'] ?? 0 }} Data
                        </span>
                        <a href="{{ route('adr.create') }}" class="btn btn-light btn-sm ms-2">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </a>
                    </div>
                </div>
                
                <div class="card-body">
                    <!-- Informasi Parameter -->
                    @if(isset($responseData['params']))
                    <div class="row mb-4 p-3 bg-light rounded">
                        <div class="col-md-6">
                            <p class="mb-1"><strong>Tanggal Transaksi:</strong></p>
                            <p class="mb-0">{{ \Carbon\Carbon::parse($responseData['params']['tgl_trans'])->format('d F Y') }}</p>
                        </div>
                        <div class="col-md-6">
                            <p class="mb-1"><strong>Kode Perkiraan:</strong></p>
                            <p class="mb-0">{{ $responseData['params']['kode_perk'] }}</p>
                        </div>
                    </div>
                    @endif

                    <!-- Data Transaksi -->
                    @if(isset($responseData['data']) && is_array($responseData['data']) && count($responseData['data']) > 0)
                        @foreach($responseData['data'] as $index => $item)
                        <div class="card mb-3 shadow-sm">
                            <div class="card-header bg-secondary text-white">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h5 class="mb-0">Transaksi #{{ $index + 1 }}</h5>
                                    @if(isset($item['validasi_status']))
                                        @if($item['validasi_status'] == '00')
                                            <span class="badge bg-success">VALID</span>
                                        @elseif($item['validasi_status'] == 'error')
                                            <span class="badge bg-danger">INVALID</span>
                                        @elseif($item['validasi_status'] == 'skipped')
                                            <span class="badge bg-info">DILEWATI</span>
                                        @else
                                            <span class="badge bg-warning">PENDING</span>
                                        @endif
                                    @endif
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <!-- Informasi Transaksi -->
                                    <div class="col-lg-6">
                                        <h6 class="text-primary"><i class="fas fa-file-invoice"></i> Detail Transaksi</h6>
                                        <hr>
                                        <div class="row">
                                            <div class="col-sm-4"><strong>Tanggal:</strong></div>
                                            <div class="col-sm-8">{{ $item['tgl_trans'] ?? '-' }}</div>
                                        </div>
                                        <div class="row">
                                            <div class="col-sm-4"><strong>Kode Perk:</strong></div>
                                            <div class="col-sm-8">{{ $item['kode_perk'] ?? '-' }}</div>
                                        </div>
                                        <div class="row">
                                            <div class="col-sm-4"><strong>Nama Perk:</strong></div>
                                            <div class="col-sm-8">{{ $item['nama_perk'] ?? '-' }}</div>
                                        </div>
                                        <div class="row">
                                            <div class="col-sm-4"><strong>Debet:</strong></div>
                                            <div class="col-sm-8 text-danger">Rp {{ number_format($item['debet'] ?? 0, 2, ',', '.') }}</div>
                                        </div>
                                        <div class="row">
                                            <div class="col-sm-4"><strong>Kredit:</strong></div>
                                            <div class="col-sm-8 text-success">Rp {{ number_format($item['kredit'] ?? 0, 2, ',', '.') }}</div>
                                        </div>
                                        <div class="row">
                                            <div class="col-sm-4"><strong>Kwitansi:</strong></div>
                                            <div class="col-sm-8">{{ $item['kwitansi'] ?? '-' }}</div>
                                        </div>
                                        <div class="row">
                                            <div class="col-sm-4"><strong>Nama:</strong></div>
                                            <div class="col-sm-8">{{ $item['nama'] ?? '-' }}</div>
                                        </div>
                                    </div>

                                    <!-- Informasi Rekening & Validasi -->
                                    <div class="col-lg-6">
                                        <h6 class="text-primary"><i class="fas fa-university"></i> Rekening & Validasi</h6>
                                        <hr>
                                        @if(isset($item['kode_bank']) && isset($item['no_rekening']))
                                            <div class="row">
                                                <div class="col-sm-4"><strong>Kode Bank:</strong></div>
                                                <div class="col-sm-8">{{ $item['kode_bank'] ?? '-' }}</div>
                                            </div>
                                            <div class="row">
                                                <div class="col-sm-4"><strong>No Rekening:</strong></div>
                                                <div class="col-sm-8">{{ $item['no_rekening'] ?? '-' }}</div>
                                            </div>
                                            <div class="row">
                                                <div class="col-sm-4"><strong>Nama Bank:</strong></div>
                                                <div class="col-sm-8">{{ $item['nama_bank'] ?? '-' }}</div>
                                            </div>
                                            <div class="row">
                                                <div class="col-sm-4"><strong>Nama Pemilik:</strong></div>
                                                <div class="col-sm-8">{{ $item['nama_pemilik_rekening'] ?? '-' }}</div>
                                            </div>
                                            <div class="row mt-2">
                                                <div class="col-sm-4"><strong>Status Validasi:</strong></div>
                                                <div class="col-sm-8">
                                                    @if($item['validasi_status'] == '00')
                                                        <span class="badge bg-success">VALID - {{ $item['validasi_message'] ?? '-' }}</span>
                                                    @elseif($item['validasi_status'] == 'error')
                                                        <span class="badge bg-danger">INVALID - {{ $item['validasi_message'] ?? '-' }}</span>
                                                    @elseif($item['validasi_status'] == 'skipped')
                                                        <span class="badge bg-info">DILEWATI - {{ $item['validasi_message'] ?? '-' }}</span>
                                                    @else
                                                        <span class="badge bg-secondary">{{ $item['validasi_message'] ?? $item['validasi_status'] }}</span>
                                                    @endif
                                                </div>
                                            </div>
                                        @else
                                            <div class="alert alert-info">
                                                <i class="fas fa-info-circle"></i> Transaksi ini tidak melibatkan rekening bank.
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                <!-- Keterangan Asli -->
                                @if(isset($item['keterangan_asli']))
                                <div class="row mt-3">
                                    <div class="col-12">
                                        <h6 class="text-primary"><i class="fas fa-comment"></i> Keterangan</h6>
                                        <div class="border p-2 bg-light rounded">
                                            {{ $item['keterangan_asli'] }}
                                        </div>
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>
                        @endforeach
                    @else
                        <div class="alert alert-warning text-center">
                            <i class="fas fa-exclamation-circle"></i>
                            <h5>Tidak ada data transaksi ditemukan</h5>
                            <p>Silakan coba dengan parameter yang berbeda.</p>
                        </div>
                    @endif
                </div>
                <div class="card-footer">
                    <div class="d-flex justify-content-between">
                        <div>
                            <small class="text-muted">
                                <i class="fas fa-clock"></i> 
                                Diolah pada: {{ now()->format('d F Y H:i:s') }}
                            </small>
                        </div>
                        <div>
                            @if(isset($responseData['data']) && count($responseData['data']) > 0)
                                <button class="btn btn-success btn-sm" onclick="printResults()">
                                    <i class="fas fa-print"></i> Cetak
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.card {
    border: 1px solid #dee2e6;
}
.card-header {
    font-weight: bold;
}
.row > div {
    margin-bottom: 5px;
}
.badge {
    font-size: 0.8em;
}
.alert {
    border-radius: 0.375rem;
}
</style>
@endpush

@push('scripts')
<script>
function printResults() {
    window.print();
}

// Highlight card on hover
$(document).ready(function() {
    $('.card').hover(
        function() {
            $(this).addClass('shadow');
        },
        function() {
            $(this).removeClass('shadow');
        }
    );
});
</script>
@endpush