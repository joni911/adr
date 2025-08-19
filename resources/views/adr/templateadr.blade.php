@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">Format CSV untuk Transfer Massal</h4>
                    <div>
                        <button class="btn btn-light btn-sm" onclick="copyTable()">
                            <i class="fas fa-copy"></i> Copy Table
                        </button>
                        <a href="{{ route('adr.create') }}" class="btn btn-light btn-sm ms-2">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </a>
                    </div>
                </div>
                <!-- Tambahkan di bagian atas blade setelah card-header -->
<div class="card-body">
    @if(isset($responseData['data']) && is_array($responseData['data']) && count($responseData['data']) > 0)
        
        <!-- Tombol Download Excel -->
        <div class="mb-3">
            <form action="{{ route('adr.download.excel') }}" method="POST" style="display: inline;">
                @csrf
                <input type="hidden" name="response_data" value="{{ json_encode($responseData) }}">
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-file-excel"></i> Download Excel File
                </button>
            </form>
            <a href="{{ route('adr.create') }}" class="btn btn-primary">
                <i class="fas fa-arrow-left"></i> Kembali ke Pencarian
            </a>
        </div>
    @endif
        <!-- ... sisa konten tetap sama ... -->
                <div class="card-body">
                    @if(isset($responseData['data']) && is_array($responseData['data']) && count($responseData['data']) > 0)
                        
                        <!-- Informasi Header -->
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i>
                            <strong>Informasi:</strong> Berikut adalah format data sesuai spesifikasi CSV untuk {{ count($responseData['data']) }} transaksi.
                            <span class="badge bg-danger">Field MANDATORY ditandai dengan warna merah</span>
                        </div>

                        <!-- Parameter Input untuk Header -->
                        <div class="row mb-4">
                            <div class="col-md-3">
                                <label class="form-label"><strong>User ID:</strong></label>
                                <input type="text" class="form-control" id="user_id" value="MAKER1">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label"><strong>Source of Fund:</strong></label>
                                <select class="form-select" id="source_of_fund">
                                    <option value="SA">SA</option>
                                    <option value="MA" selected>MA</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label"><strong>Approval Mechanism:</strong></label>
                                <select class="form-select" id="approval_mechanism">
                                    <option value="R">R</option>
                                    <option value="F" selected>F</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label"><strong>Instruction Mode:</strong></label>
                                <select class="form-select" id="instruction_mode">
                                    <option value="I">I</option>
                                    <option value="F" selected>F</option>
                                </select>
                            </div>
                        </div>

                        <!-- Tabel Format CSV -->
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped table-sm" id="csvTable">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Row</th>
                                        <th>1</th>
                                        <th>2</th>
                                        <th>3</th>
                                        <th>4</th>
                                        <th>5</th>
                                        <th>6</th>
                                        <th>7</th>
                                        <th>8</th>
                                        <th>9</th>
                                        <th>10</th>
                                        <th>11</th>
                                        <th>12</th>
                                        <th>13</th>
                                        <th>14</th>
                                        <th>15</th>
                                        <th>16</th>
                                        <th>17</th>
                                        <th>18</th>
                                        <th>19</th>
                                        <th>20</th>
                                        <th>21</th>
                                        <th>22</th>
                                        <th>23</th>
                                        <th>24</th>
                                        <th>25</th>
                                        <th>26</th>
                                        <th>27</th>
                                        <th>28</th>
                                        <th>29</th>
                                        <th>30</th>
                                        <th>31</th>
                                        <th>32</th>
                                        <th>33</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Content Header Row -->
                                    <tr class="table-primary">
                                        <td><strong>HEADER</strong></td>
                                        <td>A</td>
                                        <td id="user_id_cell">MAKER1</td>
                                        <td id="source_of_fund_cell">SA</td>
                                        <td></td>
                                        <td></td>
                                        <td>IDR</td>
                                        <td id="approval_mechanism_cell">F</td>
                                        <td id="instruction_mode_cell">I</td>
                                        <td>{{ date('dmY') }}</td>
                                        <td>{{ date('dmY') }}</td>
                                        <td>
                                            @php
                                                $totalAmount = 0;
                                                foreach($responseData['data'] as $item) {
                                                    $totalAmount += (float)($item['kredit'] ?? 0);
                                                }
                                            @endphp
                                            {{ number_format($totalAmount, 2, '.', '') }}
                                        </td>
                                        <td>{{ str_pad(count($responseData['data']), 5, '0', STR_PAD_LEFT) }}</td>
                                        <td>4</td>
                                        <td>MDMC</td>
                                        <td>LL</td>
                                        <td>B</td>
                                        @for($i = 17; $i <= 33; $i++)
                                            <td></td>
                                        @endfor
                                    </tr>

                                    <!-- Content Record Rows -->
                                    @foreach($responseData['data'] as $index => $item)
                                    <tr>
                                        <td><strong>RECORD {{ $index + 1 }}</strong></td>
                                        <td>{{ substr($item['nama_bank'] ?? 'Bank Tidak Diketahui', 0, 22) }}</td>
                                        <td></td>
                                        <td></td>
                                        <td>{{ $item['kode_bank'] ?? '' }}</td>
                                        <!-- Field Mandatory 5: Beneficiary Name -->
                                        <td class="table-danger text-danger fw-bold">
                                            {{ substr($item['nama_pemilik_rekening'] ?? $item['nama'] ?? 'Tidak Diketahui', 0, 70) }}
                                        </td>
                                        <!-- Field Mandatory 6: Beneficiary Account Number -->
                                        <td class="table-danger text-danger fw-bold">
                                            '{{ $item['no_rekening'] ?? '' }}
                                        </td>
                                        <!-- Field Mandatory 7: Beneficiary Currency -->
                                        <td class="table-danger text-danger fw-bold">IDR</td>
                                        <!-- Field Mandatory 8: Transfer Amount -->
                                        <td class="table-danger text-danger fw-bold">
                                            {{ number_format($item['kredit'] ?? 0, 2, '.', '') }}
                                        </td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <!-- Field Mandatory 12: Transaction Type -->
                                        <td class="table-danger text-danger fw-bold">OVB</td>
                                        <!-- Field Mandatory 13: Resident Status -->
                                        <td class="table-danger text-danger fw-bold">0</td>
                                        <!-- Field Mandatory 14: Citizen Status -->
                                        <td class="table-danger text-danger fw-bold">0</td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td>{{ date('dmY') }}</td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td>IDR</td>
                                        <td>CR</td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td>0</td>
                                        <td></td>
                                        <td></td>
                                        <td>1</td>
                                        <td>01</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Mandatory Fields Legend -->
                        <div class="mt-4">
                            <div class="card">
                                <div class="card-header bg-danger text-white">
                                    <h5 class="mb-0">
                                        <i class="fas fa-exclamation-triangle"></i> 
                                        Field MANDATORY yang Harus Diisi
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <h6><i class="fas fa-list-ol"></i> Kolom Mandatory untuk Setiap Record</h6>
                                            <ul class="list-group">
                                                <li class="list-group-item">
                                                    <span class="badge bg-danger me-2">5</span>
                                                    <strong>Beneficiary Name</strong> - Nama penerima ({{ substr($responseData['data'][0]['nama_pemilik_rekening'] ?? $responseData['data'][0]['nama'] ?? 'Tidak Diketahui', 0, 30) }}...)
                                                </li>
                                                <li class="list-group-item">
                                                    <span class="badge bg-danger me-2">6</span>
                                                    <strong>Beneficiary Account Number</strong> - Nomor rekening penerima ('{{ substr($responseData['data'][0]['no_rekening'] ?? '', 0, 15) }}...)
                                                </li>
                                                <li class="list-group-item">
                                                    <span class="badge bg-danger me-2">7</span>
                                                    <strong>Beneficiary Currency</strong> - Mata uang (IDR)
                                                </li>
                                                <li class="list-group-item">
                                                    <span class="badge bg-danger me-2">8</span>
                                                    <strong>Transfer Amount</strong> - Nominal transfer ({{ number_format($responseData['data'][0]['kredit'] ?? 0, 2, '.', '') }})
                                                </li>
                                            </ul>
                                        </div>
                                        <div class="col-md-6">
                                            <h6><i class="fas fa-list-ol"></i> Kolom Mandatory Tambahan</h6>
                                            <ul class="list-group">
                                                <li class="list-group-item">
                                                    <span class="badge bg-danger me-2">12</span>
                                                    <strong>Transaction Type</strong> - Jenis transaksi (OVB)
                                                </li>
                                                <li class="list-group-item">
                                                    <span class="badge bg-danger me-2">13</span>
                                                    <strong>Resident Status</strong> - Status kependudukan (0)
                                                </li>
                                                <li class="list-group-item">
                                                    <span class="badge bg-danger me-2">14</span>
                                                    <strong>Citizen Status</strong> - Status kewarganegaraan (0)
                                                </li>
                                                <li class="list-group-item bg-light">
                                                    <span class="badge bg-warning me-2">4</span>
                                                    <strong>Bank Code</strong> - Kode bank ({{ $responseData['data'][0]['kode_bank'] ?? 'Optional' }})
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                    
                                    <div class="alert alert-warning mt-3">
                                        <i class="fas fa-info-circle"></i>
                                        <strong>Catatan:</strong> 
                                        <ul class="mb-0">
                                            <li>Field dengan background merah <span class="badge bg-danger"> </span> adalah MANDATORY dan harus diisi</li>
                                            <li>Field dengan background kuning <span class="badge bg-warning"> </span> mandatory dalam kondisi tertentu</li>
                                            <li>Pastikan semua field mandatory terisi sebelum mengirim file</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Validation Status -->
                        <div class="mt-3">
                            <div class="card">
                                <div class="card-header bg-info text-white">
                                    <h5 class="mb-0"><i class="fas fa-check-circle"></i> Status Validasi Data</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="text-center p-2 bg-success text-white rounded">
                                                <h6>{{ count($responseData['data']) }} Record</h6>
                                                <small>Total Transaksi</small>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="text-center p-2 bg-primary text-white rounded">
                                                <h6>
                                                    @php
                                                        $validRecords = 0;
                                                        foreach($responseData['data'] as $item) {
                                                            if(isset($item['validasi_status']) && $item['validasi_status'] == '00') {
                                                                $validRecords++;
                                                            }
                                                        }
                                                    @endphp
                                                    {{ $validRecords }} Valid
                                                </h6>
                                                <small>Rekening Terverifikasi</small>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="text-center p-2 bg-warning text-dark rounded">
                                                <h6>
                                                    @php
                                                        $invalidRecords = count($responseData['data']) - $validRecords;
                                                    @endphp
                                                    {{ $invalidRecords }} Perlu Diperiksa
                                                </h6>
                                                <small>Data Tidak Valid</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    @else
                        <div class="alert alert-warning text-center">
                            <i class="fas fa-exclamation-circle"></i>
                            <h5>Tidak ada data transaksi</h5>
                            <p>Silakan kembali ke halaman pencarian dan pilih transaksi terlebih dahulu.</p>
                            <a href="{{ route('adr.create') }}" class="btn btn-primary">Kembali ke Pencarian</a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Update cells when input changes
document.getElementById('user_id').addEventListener('input', function() {
    document.getElementById('user_id_cell').textContent = this.value;
});

document.getElementById('source_of_fund').addEventListener('change', function() {
    document.getElementById('source_of_fund_cell').textContent = this.value;
});

document.getElementById('approval_mechanism').addEventListener('change', function() {
    document.getElementById('approval_mechanism_cell').textContent = this.value;
});

document.getElementById('instruction_mode').addEventListener('change', function() {
    document.getElementById('instruction_mode_cell').textContent = this.value;
});

function copyTable() {
    const table = document.getElementById('csvTable');
    let csvContent = '';
    
    // Get all rows
    const rows = table.querySelectorAll('tr');
    rows.forEach((row, index) => {
        const cells = row.querySelectorAll('td, th');
        const rowData = [];
        cells.forEach(cell => {
            // Remove HTML tags and extra styling for clean CSV
            let cellText = cell.textContent.trim();
            // Remove badge numbers and styling text
            cellText = cellText.replace(/^\d+\s*/, '');
            rowData.push(cellText);
        });
        csvContent += rowData.join(';') + '\n';
    });
    
    // Copy to clipboard
    navigator.clipboard.writeText(csvContent).then(() => {
        alert('Tabel berhasil disalin ke clipboard dalam format CSV!');
    }).catch(err => {
        console.error('Failed to copy: ', err);
        // Fallback: show in alert
        alert('Data tabel (format CSV):\n\n' + csvContent);
    });
}

// Highlight row on hover
$(document).ready(function() {
    $('#csvTable tbody tr').hover(
        function() {
            $(this).addClass('table-warning');
        },
        function() {
            $(this).removeClass('table-warning');
        }
    );
});
</script>
@endpush

@push('styles')
<style>
#csvTable {
    font-size: 0.8rem;
}
#csvTable th {
    font-size: 0.7rem;
    padding: 2px;
    text-align: center;
}
#csvTable td {
    padding: 2px 4px;
    vertical-align: middle;
}
.table-responsive {
    max-height: 600px;
    overflow-y: auto;
}
.table-danger {
    background-color: #f8d7da !important;
}
.text-danger {
    color: #dc3545 !important;
}
.fw-bold {
    font-weight: bold !important;
}
.list-group-item {
    border: 1px solid #dee2e6;
}
</style>
@endpush