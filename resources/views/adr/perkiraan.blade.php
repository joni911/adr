@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4>Pilih Perkiraan</h4>
                </div>
                <div class="card-body">
                    <!-- Form Tanggal dan Pencarian -->
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <label for="tanggal" class="form-label">Tanggal</label>
                            <input type="date" class="form-control" id="tanggal" name="tanggal" required>
                        </div>
                        <div class="col-md-8">
                            <label for="search" class="form-label">Cari Perkiraan</label>
                            <input type="text" class="form-control" id="search" placeholder="Cari kode atau nama perkiraan...">
                        </div>
                    </div>

                    <!-- Tabel Data Perkiraan -->
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="perkiraanTable">
                            <thead>
                                <tr>
                                    <th>Kode Perkiraan</th>
                                    <th>Nama Perkiraan</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if(isset($data['data']))
                                    @foreach($data['data'] as $perkiraan)
                                        <tr class="perkiraan-row" data-kode="{{ $perkiraan['kode_perk'] }}" data-nama="{{ $perkiraan['nama_perk'] }}">
                                            <td>{{ $perkiraan['kode_perk'] }}</td>
                                            <td>{{ $perkiraan['nama_perk'] }}</td>
                                            <td>
                                                <button class="btn btn-primary btn-sm pilih-perkiraan" 
                                                        data-kode="{{ $perkiraan['kode_perk'] }}"
                                                        data-nama="{{ $perkiraan['nama_perk'] }}">
                                                    Pilih
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                @endif
                            </tbody>
                        </table>
                    </div>

                    <!-- Informasi Perkiraan Terpilih -->
                    <div class="mt-4 p-3 bg-light border rounded" id="selectedInfo" style="display: none;">
                        <h5>Perkiraan Terpilih:</h5>
                        <p><strong>Kode:</strong> <span id="selectedKode"></span></p>
                        <p><strong>Nama:</strong> <span id="selectedNama"></span></p>
                        <input type="hidden" id="selectedKodeInput" name="kode_perk">
                        
                        <button type="button" class="btn btn-success" id="btnLanjutkan" disabled>
                            Lanjutkan
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Konfirmasi -->
<div class="modal fade" id="konfirmasiModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Konfirmasi Data</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p><strong>Tanggal:</strong> <span id="modalTanggal"></span></p>
                <p><strong>Kode Perkiraan:</strong> <span id="modalKode"></span></p>
                <p><strong>Nama Perkiraan:</strong> <span id="modalNama"></span></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" id="btnSubmit">Lanjutkan</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    console.log('jQuery loaded');
    
    // Set tanggal default ke hari ini
    const today = new Date().toISOString().split('T')[0];
    $('#tanggal').val(today);

    // Fungsi pencarian
    $('#search').on('keyup', function() {
        var searchTerm = $(this).val().toLowerCase();
        
        $('.perkiraan-row').each(function() {
            var kode = $(this).data('kode').toString().toLowerCase();
            var nama = $(this).data('nama').toString().toLowerCase();
            
            if (kode.includes(searchTerm) || nama.includes(searchTerm)) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    });

    // Fungsi pilih perkiraan
    $(document).on('click', '.pilih-perkiraan', function() {
        var kode = $(this).data('kode');
        var nama = $(this).data('nama');
        
        $('#selectedKode').text(kode);
        $('#selectedNama').text(nama);
        $('#selectedKodeInput').val(kode);
        $('#selectedInfo').show();
        $('#btnLanjutkan').prop('disabled', false);
    });

    // Fungsi tombol lanjutkan
    $('#btnLanjutkan').on('click', function() {
        var tanggal = $('#tanggal').val();
        var kode = $('#selectedKode').text();
        var nama = $('#selectedNama').text();
        
        if (!tanggal) {
            alert('Silakan pilih tanggal terlebih dahulu!');
            return;
        }
        
        if (!kode) {
            alert('Silakan pilih perkiraan terlebih dahulu!');
            return;
        }
        
        // Tampilkan modal konfirmasi
        $('#modalTanggal').text(tanggal);
        $('#modalKode').text(kode);
        $('#modalNama').text(nama);
        
        // Show modal using Bootstrap 5 syntax
        var modal = new bootstrap.Modal(document.getElementById('konfirmasiModal'));
        modal.show();
    });

    // Fungsi submit form
    $('#btnSubmit').on('click', function() {
        var tanggal = $('#tanggal').val();
        var kode_perk = $('#selectedKodeInput').val();
        
        // Redirect ke URL dengan parameter
        window.location.href = '/adr/next?tanggal=' + tanggal + '&kode_perk=' + encodeURIComponent(kode_perk);
    });

    // Highlight row saat hover
    $(document).on('mouseenter', '.perkiraan-row', function() {
        $(this).addClass('table-primary');
    }).on('mouseleave', '.perkiraan-row', function() {
        $(this).removeClass('table-primary');
    });
});
</script>
@endpush

@push('styles')
<style>
.perkiraan-row {
    cursor: pointer;
}
.perkiraan-row:hover {
    background-color: #f8f9fa;
}
#search {
    margin-bottom: 15px;
}
</style>
@endpush