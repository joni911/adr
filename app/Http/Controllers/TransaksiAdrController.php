<?php

namespace App\Http\Controllers;

use App\Models\TransaksiAdr;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use GuzzleHttp\Psr7\Request as GuzzleRequest;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Border;

class TransaksiAdrController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return "index";
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $client = new Client();
        
        try {
            $response = $client->get('http://127.0.0.1:8000/v1/perkiraan', [
                'headers' => [
                    'x-api-key' => 'Trojan212!',
                    'Content-Type' => 'application/json',
                    'accept' => 'application/json'
                ],
            ]);
            
            $body = $response->getBody()->getContents();
            $data = json_decode($body, true);
            
            // Hanya return view, bukan response JSON
            return view('adr.perkiraan', compact('data'));
                
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function next(Request $request)
    {
        $request->validate([
            'tanggal' => 'required|date',
            'kode_perk' => 'required|string'
        ]);
        
        $tanggal = $request->get('tanggal');
        $kode_perk = $request->get('kode_perk');
        
        try {
            Log::info('Memulai proses transaksi', [
                'tanggal' => $tanggal,
                'kode_perk' => $kode_perk
            ]);
            
            $client = new Client();
            
            // Headers
            $headers = [
                'x-api-key' => 'Trojan212!',
                'Content-Type' => 'application/json'
            ];
            
            // Body data
            $body = json_encode([
                'tgl_trans' => $tanggal,
                'kode_perk' => $kode_perk
            ]);
            
            // Create request
            $guzzleRequest = new GuzzleRequest('POST', 'http://127.0.0.1:8000/v1/transaksi', $headers, $body);
            
            // Send request dengan timeout
            Log::info('Mengirim request ke API transaksi');
            $response = $client->send($guzzleRequest, [
                'timeout' => 30 // Timeout 30 detik
            ]);
            
            // Get response
            $responseBody = $response->getBody()->getContents();
            $responseData = json_decode($responseBody, true);
            
            Log::info('Response dari API transaksi diterima', [
                'count' => $responseData['count'] ?? 0
            ]);
            
            // Jika ada data transaksi, lakukan validasi rekening untuk setiap item
            if (isset($responseData['data']) && is_array($responseData['data'])) {
                $itemCount = count($responseData['data']);
                Log::info('Memulai validasi rekening untuk item', ['count' => $itemCount]);
                
                foreach ($responseData['data'] as $index => &$item) {
                    Log::info('Memproses item ke-' . ($index + 1));
                    
                    // Validasi rekening jika ada kode_bank dan no_rekening
                    if (isset($item['kode_bank']) && isset($item['no_rekening'])) {
                        Log::info('Melakukan validasi rekening', [
                            'kode_bank' => $item['kode_bank'],
                            'no_rekening' => $item['no_rekening']
                        ]);
                        
                        $startTime = microtime(true);
                        $validationResult = $this->validateRekening(
                            $item['kode_bank'], 
                            $item['no_rekening']
                        );
                        $endTime = microtime(true);
                        $duration = round(($endTime - $startTime) * 1000); // dalam milidetik
                        
                        Log::info('Validasi rekening selesai', [
                            'duration_ms' => $duration,
                            'result_status' => $validationResult['status'] ?? 'unknown'
                        ]);
                        
                        // Tambahkan informasi validasi ke data
                        $item['validasi_status'] = $validationResult['status'] ?? 'unknown';
                        $item['validasi_message'] = $validationResult['message'] ?? 'Tidak dapat divalidasi';
                        $item['nama_pemilik_rekening'] = $validationResult['nama_pemilik'] ?? 'Tidak diketahui';
                        $item['nama_bank'] = $validationResult['nama_bank'] ?? 'Tidak diketahui';
                    } else {
                        Log::info('Item tidak memiliki kode_bank atau no_rekening, dilewati');
                        // Set default values untuk item tanpa rekening
                        $item['validasi_status'] = 'skipped';
                        $item['validasi_message'] = 'Tidak perlu validasi';
                        $item['nama_pemilik_rekening'] = 'N/A';
                        $item['nama_bank'] = 'N/A';
                    }
                }
            }
            
            Log::info('Proses validasi selesai');
            
            // Return data yang sudah di enrich dengan informasi validasi
            // return response()->json($responseData);
            return view('adr.templateadr',compact('responseData', 'tanggal', 'kode_perk'));
            
        } catch (\Exception $e) {
            Log::error('Gagal memproses data', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => 'Gagal memproses data: ' . $e->getMessage()], 500);
        }
    }
    
    private function validateRekening($kodeBank, $noRekening)
    {
        try {
            Log::info('Memulai koneksi ke API validasi rekening');
            
            $client = new Client();
            
            // Headers untuk API validasi rekening
            $headers = [
                'X-API-KEY' => 'Bprbangli@1968!',
                'Content-Type' => 'application/json'
            ];
            
            // Body data
            $body = json_encode([
                'kode_bank' => $kodeBank,
                'no_rekening' => $noRekening
            ]);
            
            Log::info('Mengirim request ke API validasi rekening', [
                'kode_bank' => $kodeBank,
                'no_rekening' => substr($noRekening, 0, 3) . '***' // Log sebagian no rekening
            ]);
            
            // Create request untuk validasi rekening dengan timeout lebih pendek
            $guzzleRequest = new GuzzleRequest(
                'POST', 
                'https://cekrek.joniartha.my.id/cek-rekening', 
                $headers, 
                $body
            );
            
            // Send request dengan timeout pendek (10 detik) untuk menghindari hanging
            $response = $client->send($guzzleRequest, [
                'timeout' => 10, // Timeout hanya 10 detik
                'connect_timeout' => 5 // Timeout koneksi 5 detik
            ]);
            
            // Get response
            $responseBody = $response->getBody()->getContents();
            $validationData = json_decode($responseBody, true);
            
            Log::info('Response validasi rekening diterima', [
                'status' => $validationData['status'] ?? 'unknown'
            ]);
            
            // Format hasil validasi
            return [
                'status' => $validationData['status'] ?? 'unknown',
                'message' => $validationData['message'] ?? 'No message',
                'nama_pemilik' => $validationData['nama_pemilik'] ?? 'Tidak ditemukan',
                'nama_bank' => $validationData['nama_bank'] ?? 'Tidak ditemukan'
            ];
            
        } catch (\Exception $e) {
            Log::error('Gagal validasi rekening', [
                'error' => $e->getMessage(),
                'kode_bank' => $kodeBank,
                'no_rekening' => substr($noRekening, 0, 3) . '***'
            ]);
            
            // Jika validasi gagal, return error info
            return [
                'status' => 'error',
                'message' => 'Gagal validasi: Timeout atau error koneksi',
                'nama_pemilik' => 'Tidak dapat divalidasi',
                'nama_bank' => 'Tidak dapat divalidasi'
            ];
        }
    }

    private function generateExcel($responseData)
    {
        try {
            // Create new Spreadsheet object
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            
            // Set document properties
            $spreadsheet->getProperties()
                ->setCreator("Sistem Perkiraan")
                ->setTitle("Format Transfer Massal")
                ->setSubject("Data Transaksi untuk Transfer Massal");
            
            // Set default style
            $spreadsheet->getDefaultStyle()
                ->getFont()
                ->setName('Calibri')
                ->setSize(11);
            
            // Create Header Row
            $headerData = [
                'A', // 1 - Content Header Marker
                'MAKER1', // 2 - User ID
                'SA', // 3 - Source of Fund
                '', // 4 - Debited Account Name
                '', // 5 - Debited Account Number
                'IDR', // 6 - Debited Account Currency
                'F', // 7 - Approval Mechanism
                'I', // 8 - Instruction Mode
                date('dmY'), // 9 - Instruction Date
                date('dmY'), // 10 - Expiry Date
                $this->calculateTotalAmount($responseData), // 11 - Total Amount
                str_pad(count($responseData['data'] ?? []), 5, '0', STR_PAD_LEFT), // 12 - Total Record
                '4', // 13 - Service Code
                'MDMC', // 14 - Transaction Method
                'LL', // 15 - Bulk Payment Transaction Currency
                'B' // 16 - Charge To
            ];
            
            // Fill header data (A1:P1)
            $headerRange = 'A1:P1';
            $sheet->fromArray($headerData, NULL, 'A1');
            
            // Style untuk header
            $sheet->getStyle($headerRange)->applyFromArray([
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF']
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '4472C4']
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => '000000']
                    ]
                ]
            ]);
            
            // Create Record Rows
            $rowIndex = 2; // Mulai dari baris 2
            
            if (isset($responseData['data']) && is_array($responseData['data'])) {
                foreach ($responseData['data'] as $item) {
                    $recordData = [
                        substr($item['nama_bank'] ?? 'Bank Tidak Diketahui', 0, 22), // 1 - Bank Name
                        '', // 2 - Bank Address
                        '', // 3 - Bank City
                        $item['kode_bank'] ?? '', // 4 - Bank Code
                        substr($item['nama_pemilik_rekening'] ?? $item['nama'] ?? 'Tidak Diketahui', 0, 70), // 5 - Beneficiary Name (MANDATORY)
                        "'" . ($item['no_rekening'] ?? ''), // 6 - Beneficiary Account Number (MANDATORY)
                        'IDR', // 7 - Beneficiary Currency (MANDATORY)
                        number_format($item['kredit'] ?? 0, 2, '.', ''), // 8 - Transfer Amount (MANDATORY)
                        '', // 9 - Description 1
                        '', // 10 - Description 2
                        '', // 11 - Beneficiary Email Address
                        'OVB', // 12 - Transaction Type (MANDATORY)
                        '0', // 13 - Resident Status (MANDATORY)
                        '0', // 14 - Citizen Status (MANDATORY)
                        '', // 15 - Bank Branch Name
                        '', // 16 - NPK
                        '', // 17 - Customer Reference Number
                        date('dmY'), // 18 - Effective Date
                        '', // 19 - SMS Notification
                        '', // 20 - Fax Notification
                        '', // 21 - Debited Account Name
                        '', // 22 - Debited Account Number
                        'IDR', // 23 - Debited Account Currency
                        'CR', // 24 - Exchange Rate
                        '', // 25 - Treasury Confirmation / Reference Number
                        '', // 26 - Beneficiary Address 1
                        '', // 27 - Beneficiary Address 2
                        '', // 28 - Beneficiary Address 3
                        '0', // 29 - Indonesian Migrant Worker Flag
                        '', // 30 - Location of Receiving Bank
                        '', // 31 - Underlying Documents
                        '1', // 32 - Beneficiary Type
                        '01' // 33 - Transaction Purpose Code
                    ];
                    
                    // Fill record data
                    $sheet->fromArray($recordData, NULL, 'A' . $rowIndex);
                    
                    // Style untuk field mandatory (kolom 5, 6, 7, 8, 12, 13, 14)
                    $mandatoryColumns = ['E', 'F', 'G', 'H', 'L', 'M', 'N'];
                    foreach ($mandatoryColumns as $column) {
                        $sheet->getStyle($column . $rowIndex)->applyFromArray([
                            'fill' => [
                                'fillType' => Fill::FILL_SOLID,
                                'startColor' => ['rgb' => 'F8D7DA']
                            ],
                            'font' => [
                                'bold' => true,
                                'color' => ['rgb' => 'DC3545']
                            ],
                            'borders' => [
                                'allBorders' => [
                                    'borderStyle' => Border::BORDER_THIN,
                                    'color' => ['rgb' => '000000']
                                ]
                            ]
                        ]);
                    }
                    
                    // Border untuk semua sel
                    $sheet->getStyle('A' . $rowIndex . ':AG' . $rowIndex)->applyFromArray([
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => Border::BORDER_THIN,
                                'color' => ['rgb' => 'CCCCCC']
                            ]
                        ]
                    ]);
                    
                    $rowIndex++;
                }
            }
            
            // Auto size columns
            foreach (range('A', 'AG') as $column) {
                $sheet->getColumnDimension($column)->setAutoSize(true);
            }
            
            // Freeze pane
            $sheet->freezePane('A2');
            
            // Create Excel file
            $writer = new Xlsx($spreadsheet);
            
            // Generate filename
            $filename = 'transfer_massal_' . date('Ymd_His') . '.xlsx';
            
            // Output to browser
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="' . $filename . '"');
            header('Cache-Control: max-age=0');
            
            $writer->save('php://output');
            exit;
            
        } catch (\Exception $e) {
            Log::error('Gagal generate Excel', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Gagal generate file Excel: ' . $e->getMessage()], 500);
        }
    }
    
    private function calculateTotalAmount($responseData)
    {
        $total = 0;
        if (isset($responseData['data']) && is_array($responseData['data'])) {
            foreach ($responseData['data'] as $item) {
                $total += (float)($item['kredit'] ?? 0);
            }
        }
        return number_format($total, 2, '.', '');
    }
    
    public function downloadExcel(Request $request)
    {
        $responseData = $request->get('response_data');
        if (!$responseData) {
            return redirect()->back()->with('error', 'Data tidak ditemukan');
        }
        
        return $this->generateExcel(json_decode($responseData, true));
    }
    
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(TransaksiAdr $transaksiAdr)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(TransaksiAdr $transaksiAdr)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, TransaksiAdr $transaksiAdr)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(TransaksiAdr $transaksiAdr)
    {
        //
    }
}
