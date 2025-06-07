<?php
// gemini_api.php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 1. Mengatur header respons dan CORS (Cross-Origin Resource Sharing)
header('Content-Type: application/json');
// Untuk produksi, ganti '*' dengan domain frontend LibTera Anda, misalnya 'https://libtera.smkntambelangan.sch.id'
header('Access-Control-Allow-Origin: *'); 
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit;
}

// 2. Ambil Kunci API dari Environment Variable
$apiKey = getenv('GEMINI_API_KEY');

if (empty($apiKey)) {
    http_response_code(500);
    echo json_encode(['error' => 'Kunci API tidak dikonfigurasi dengan benar di server.']);
    exit;
}

// 3. Tentukan URL Endpoint API Gemini
$geminiApiUrl = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash-latest:generateContent?key=' . $apiKey;

// 4. Ambil data 'prompt' yang dikirim dari frontend
$inputJSON = file_get_contents('php://input');
$input = json_decode($inputJSON, TRUE);

if (!isset($input['prompt']) || empty(trim($input['prompt']))) {
    http_response_code(400);
    echo json_encode(['error' => 'Prompt tidak boleh kosong.']);
    exit;
}
$userPrompt = trim($input['prompt']);

// --- MODIFIKASI DIMULAI DI SINI ---
// 5. Definisikan Instruksi Sistem untuk LibTera AI (SMKN Tambelangan)
$system_instruction = "Kamu adalah Tera AI, asisten AI yang ramah dan berpengetahuan luas, khusus untuk membantu siswa-siswi SMKN Tambelangan. " .
                      "Tugas utamamu adalah: " .
                      "1. Memberikan rekomendasi buku yang relevan dan menarik bagi siswa SMKN Tambelangan, sesuai dengan minat atau kebutuhan belajar mereka. " .
                      "2. Jika siswa bertanya tentang kata kunci atau konsep yang tidak mereka mengerti setelah membaca buku, jelaskan dengan bahasa yang mudah dipahami dan berikan contoh jika perlu. " .
                      "3. Berikan tips atau strategi agar proses belajar dan membaca mereka menjadi lebih efektif dan menyenangkan. " .
                      "4. Selalu berikan semangat dan motivasi kepada siswa agar mereka semakin giat membaca dan belajar untuk meraih masa depan yang lebih baik. " .
                      "Gunakan bahasa Indonesia yang baik, sopan, dan antusias. Ingatlah bahwa kamu berbicara dengan siswa SMK.\n\n" .
                      "Berikut adalah pertanyaan atau permintaan dari siswa:\n";

// Gabungkan instruksi sistem dengan prompt pengguna
$finalPromptToGemini = $system_instruction . $userPrompt;
// --- MODIFIKASI SELESAI ---

// 6. Siapkan data yang akan dikirim ke API Gemini
$requestData = [
    'contents' => [
        [
            'parts' => [
                // Gunakan $finalPromptToGemini yang sudah digabung
                ['text' => $finalPromptToGemini]
            ]
        ]
    ],
    // Anda bisa menambahkan 'generationConfig' di sini jika perlu
    // Misalnya:
    // 'generationConfig' => [
    //     'temperature' => 0.7, // Untuk respons yang lebih kreatif namun tetap relevan
    //     'maxOutputTokens' => 1024,
    // ]
    // Dan 'safetySettings'
    // 'safetySettings' => [
    //     [
    //         'category' => 'HARM_CATEGORY_HARASSMENT',
    //         'threshold' => 'BLOCK_MEDIUM_AND_ABOVE',
    //     ],
    //     // Tambahkan kategori lain jika perlu
    // ]
];
$jsonData = json_encode($requestData);

// 7. Kirim permintaan ke API Gemini menggunakan cURL
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $geminiApiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
// curl_setopt($ch, CURLOPT_CAINFO, '/path/to/cacert.pem'); // Aktifkan jika ada masalah SSL

$response = curl_exec($ch);
$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

// 8. Tangani respons dari API Gemini
if ($curlError) {
    http_response_code(500);
    echo json_encode(['error' => 'Kesalahan cURL: ' . $curlError]);
    exit;
}

if ($httpcode !== 200) {
    http_response_code($httpcode);
    $errorResponse = json_decode($response, true);
    $errorMessage = 'Gagal menghubungi API Gemini.';
    if (isset($errorResponse['error']['message'])) {
        $errorMessage .= ' Pesan: ' . $errorResponse['error']['message'];
    }
    echo json_encode(['error' => $errorMessage, 'details' => $errorResponse]);
    exit;
}

$responseData = json_decode($response, true);

// 9. Ekstrak teks jawaban dari respons Gemini dan kirim kembali ke frontend
if (isset($responseData['candidates'][0]['content']['parts'][0]['text'])) {
    echo json_encode(['response' => $responseData['candidates'][0]['content']['parts'][0]['text']]);
} elseif (isset($responseData['promptFeedback'])) {
    error_log('Gemini API Feedback: ' . print_r($responseData['promptFeedback'], true));
    http_response_code(400); 
    $blockReason = isset($responseData['promptFeedback']['blockReason']) ? $responseData['promptFeedback']['blockReason'] : 'Tidak diketahui';
    $safetyRatings = isset($responseData['promptFeedback']['safetyRatings']) ? json_encode($responseData['promptFeedback']['safetyRatings']) : 'Tidak ada detail';
    echo json_encode([
        'error' => 'Permintaan diblokir oleh filter keamanan Gemini. Alasan: ' . $blockReason,
        'details' => $safetyRatings
    ]);
} else {
    error_log('Struktur respons Gemini tidak sesuai: ' . $response);
    http_response_code(500);
    echo json_encode(['error' => 'Gagal memproses respons dari AI. Struktur tidak dikenal.']);
}
?>
