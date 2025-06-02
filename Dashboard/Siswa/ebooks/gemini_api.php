<?php
// gemini_api.php

// 1. Mengatur header respons dan CORS (Cross-Origin Resource Sharing)
// Ini memberitahu browser bahwa skrip ini akan mengirimkan data dalam format JSON.
header('Content-Type: application/json');
// Baris di bawah ini mengizinkan permintaan dari domain/port mana saja.
// Untuk development ini oke, tapi untuk produksi, sebaiknya ganti '*' dengan domain frontend Anda.
header('Access-Control-Allow-Origin: *');
// Mengizinkan metode POST dan OPTIONS (OPTIONS penting untuk preflight request CORS)
header('Access-Control-Allow-Methods: POST, OPTIONS');
// Mengizinkan header 'Content-Type' dalam permintaan
header('Access-Control-Allow-Headers: Content-Type');

// Jika permintaan adalah OPTIONS (preflight request dari browser untuk CORS), hentikan skrip.
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit;
}

// 2. Ambil Kunci API dari Environment Variable yang sudah di-set di .htaccess
$apiKey = getenv('GEMINI_API_KEY');

if (empty($apiKey)) {
    // Jika API Key tidak ditemukan, kirim pesan error.
    http_response_code(500); // Kode error server
    echo json_encode(['error' => 'Kunci API tidak dikonfigurasi dengan benar di server. Periksa file .htaccess dan pastikan Apache sudah direstart.']);
    exit; // Hentikan skrip
}

// 3. Tentukan URL Endpoint API Gemini
// Ganti 'gemini-pro' jika Anda menggunakan model lain, misal 'gemini-1.5-flash-latest'
$geminiApiUrl = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash-latest:generateContent?key=' . $apiKey;

// 4. Ambil data 'prompt' yang dikirim dari frontend
// Frontend akan mengirim data dalam format JSON.
$inputJSON = file_get_contents('php://input'); // Membaca raw input stream
$input = json_decode($inputJSON, TRUE); // Mengubah JSON string menjadi array PHP (TRUE untuk array asosiatif)

// Validasi apakah prompt ada dan tidak kosong
if (!isset($input['prompt']) || empty(trim($input['prompt']))) {
    http_response_code(400); // Kode error Bad Request
    echo json_encode(['error' => 'Prompt tidak boleh kosong.']);
    exit;
}
$userPrompt = trim($input['prompt']);

// 5. Siapkan data yang akan dikirim ke API Gemini
$requestData = [
    'contents' => [
        [
            'parts' => [
                ['text' => $userPrompt]
            ]
        ]
    ]
];
$jsonData = json_encode($requestData); // Ubah array PHP menjadi JSON string

// 6. Kirim permintaan ke API Gemini menggunakan cURL
$ch = curl_init(); // Inisialisasi sesi cURL

// Set opsi untuk cURL
curl_setopt($ch, CURLOPT_URL, $geminiApiUrl); // Set URL tujuan
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // Agar curl_exec() mengembalikan respons sebagai string, bukan langsung output
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']); // Set header permintaan
curl_setopt($ch, CURLOPT_POST, true); // Set metode permintaan ke POST
curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData); // Set data JSON yang akan dikirim
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true); // Verifikasi SSL certificate (penting untuk keamanan)
// Anda mungkin perlu menambahkan CURLOPT_CAINFO jika ada masalah SSL di lingkungan lokal tertentu
// curl_setopt($ch, CURLOPT_CAINFO, '/path/to/cacert.pem');

$response = curl_exec($ch); // Eksekusi permintaan cURL
$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE); // Ambil kode status HTTP dari respons
$curlError = curl_error($ch); // Ambil pesan error jika ada masalah dengan cURL itu sendiri

curl_close($ch); // Tutup sesi cURL

// 7. Tangani respons dari API Gemini
if ($curlError) {
    http_response_code(500);
    echo json_encode(['error' => 'Kesalahan cURL: ' . $curlError]);
    exit;
}

if ($httpcode !== 200) {
    // Jika kode status bukan 200 (OK)
    http_response_code($httpcode);
    $errorResponse = json_decode($response, true);
    $errorMessage = 'Gagal menghubungi API Gemini.';
    if (isset($errorResponse['error']['message'])) {
        $errorMessage .= ' Pesan: ' . $errorResponse['error']['message'];
    }
    echo json_encode(['error' => $errorMessage, 'details' => $errorResponse]);
    exit;
}

// Jika berhasil, ubah respons JSON dari Gemini menjadi array PHP
$responseData = json_decode($response, true);

// 8. Ekstrak teks jawaban dari respons Gemini dan kirim kembali ke frontend
// Struktur path ini bisa berbeda tergantung model dan versi API, cek dokumentasi Gemini jika perlu
if (isset($responseData['candidates'][0]['content']['parts'][0]['text'])) {
    echo json_encode(['response' => $responseData['candidates'][0]['content']['parts'][0]['text']]);
} elseif (isset($responseData['promptFeedback'])) {
    // Handle kasus di mana ada feedback error dari API, misal karena safety blocking
    // Anda bisa mencatat error ini di log server
    error_log('Gemini API Feedback: ' . print_r($responseData['promptFeedback'], true));
    http_response_code(400); // Atau kode error lain yang sesuai
    $blockReason = isset($responseData['promptFeedback']['blockReason']) ? $responseData['promptFeedback']['blockReason'] : 'Tidak diketahui';
    $safetyRatings = isset($responseData['promptFeedback']['safetyRatings']) ? json_encode($responseData['promptFeedback']['safetyRatings']) : 'Tidak ada detail';
    echo json_encode([
        'error' => 'Permintaan diblokir oleh filter keamanan Gemini. Alasan: ' . $blockReason,
        'details' => $safetyRatings
    ]);
}
 else {
    // Jika struktur respons tidak seperti yang diharapkan
    error_log('Struktur respons Gemini tidak sesuai: ' . $response); // Catat ini di log error PHP Anda
    http_response_code(500);
    echo json_encode(['error' => 'Gagal memproses respons dari AI. Struktur tidak dikenal.']);
}

?>