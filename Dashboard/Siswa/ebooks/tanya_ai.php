<?php
// Pastikan session sudah dimulai jika header atau footer Anda memerlukannya
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Include header
// Sesuaikan path ini dengan struktur direktori Anda
include_once __DIR__ . '/../../../layout/header.php'; 
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tanya AI LibTera</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" xintegrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* Gaya CSS Anda tetap di sini, atau bisa dipindahkan ke file CSS eksternal 
           dan di-link dari header.php atau langsung di sini */
        body {
            font-family: 'Poppins', Arial, sans-serif;
            margin: 0;
            /* Padding body mungkin perlu disesuaikan jika header/footer punya tinggi tetap */
            padding: 20px; 
            background-color: #f0f4f8;
            color: #333a45;
            display: flex;
            flex-direction: column; /* Mengubah flex-direction untuk mengakomodasi header/footer */
            align-items: center; /* Pusatkan .container */
            min-height: 100vh;
            box-sizing: border-box;
        }

        /* Pastikan .container tidak terpengaruh oleh styling global header/footer */
        .ai-container { /* Mengganti nama kelas agar tidak konflik jika 'container' sudah dipakai di layout */
            width: 100%;
            max-width: 780px;
            background-color: #ffffff;
            padding: 35px 45px;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            margin-top: 20px; /* Sesuaikan margin jika perlu */
            margin-bottom: 20px; /* Sesuaikan margin jika perlu */
            animation: fadeInShowUp 0.6s ease-out forwards;
            flex-grow: 1; /* Memastikan container mengisi ruang jika konten pendek */
        }

        @keyframes fadeInShowUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .ai-container h1 {
            text-align: center;
            color: #2c3e50;
            margin-bottom: 15px;
            font-weight: 600;
            font-size: 2.3em;
        }
        .ai-container h1 .fa-book-reader {
            margin-right: 12px;
            color: #2980b9;
        }

        .ai-container .subtitle {
            text-align: center;
            color: #566573;
            margin-bottom: 35px;
            font-size: 1.1em;
            line-height: 1.6;
        }

        .ai-container textarea#promptInput {
            width: 100%;
            min-height: 140px;
            margin-bottom: 20px;
            padding: 18px;
            border: 1px solid #dbe2e8;
            border-radius: 10px;
            box-sizing: border-box;
            font-size: 16px;
            line-height: 1.6;
            resize: vertical;
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
        }

        .ai-container textarea#promptInput:focus {
            border-color: #2980b9;
            box-shadow: 0 0 8px rgba(41, 128, 185, 0.2);
            outline: none;
        }

        .ai-container button#submitButton {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            padding: 16px 22px;
            background-image: linear-gradient(to right, #3498db, #2980b9);
            color: white;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            font-size: 17px;
            font-weight: 500;
            transition: background-image 0.4s ease, transform 0.2s ease, box-shadow 0.3s ease;
            box-shadow: 0 4px 12px rgba(41, 128, 185, 0.25);
        }
        .ai-container button#submitButton .fa-paper-plane {
            margin-right: 10px;
        }

        .ai-container button#submitButton:hover {
            background-image: linear-gradient(to right, #2980b9, #2471a3);
            transform: translateY(-3px);
            box-shadow: 0 6px 15px rgba(41, 128, 185, 0.35);
        }
        .ai-container button#submitButton:active {
            transform: translateY(-1px);
            box-shadow: 0 3px 10px rgba(41, 128, 185, 0.3);
        }
        .ai-container button#submitButton:disabled {
            background-image: linear-gradient(to right, #bdc3c7, #95a5a6);
            cursor: not-allowed;
            box-shadow: none;
            transform: translateY(0);
        }
        .ai-container button#submitButton:disabled .fa-spinner {
             margin-right: 10px;
        }

        .ai-container #responseContainer {
            margin-top: 40px;
            border-top: 1px solid #e0e6ed;
            padding-top: 30px;
        }

        .ai-container #responseContainer h2 {
            color: #2c3e50;
            font-weight: 600;
            font-size: 1.7em;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
        }
        .ai-container #responseContainer h2 .fa-comments {
            margin-right: 10px;
            color: #16a085;
        }

        .ai-container #response {
            padding: 20px;
            border: 1px solid #e0e6ed;
            background-color: #f8f9fc;
            border-radius: 10px;
            white-space: pre-wrap;
            word-wrap: break-word;
            min-height: 80px;
            font-size: 16px;
            line-height: 1.7;
            color: #495057;
            transition: background-color 0.3s ease, border-color 0.3s ease;
        }
        .ai-container #response strong, .ai-container #response b {
            font-weight: 600;
            color: #2c3e50;
        }
        .ai-container #response ol, .ai-container #response ul {
            padding-left: 25px;
            margin-top: 10px;
            margin-bottom: 10px;
        }
        .ai-container #response li {
            margin-bottom: 5px;
        }

        .ai-container #response.placeholder-text {
            color: #6c757d;
        }

        .ai-container .status-message {
            padding: 18px 22px;
            border-radius: 10px;
            font-weight: 500;
            text-align: left;
            display: flex;
            align-items: center;
        }
        .ai-container .status-message i {
            margin-right: 12px;
            font-size: 1.2em;
        }

        .ai-container .loading {
            color: #2980b9;
            background-color: #eaf2f8;
            border-left: 5px solid #2980b9;
        }

        .ai-container .error {
            color: #c0392b;
            background-color: #fdedec;
            border-left: 5px solid #c0392b;
        }

        @media (max-width: 768px) {
            .ai-container { padding: 25px 30px; margin-top: 20px; margin-bottom: 20px; }
            .ai-container h1 { font-size: 2em; }
            .ai-container .subtitle { font-size: 1em; margin-bottom: 25px; }
            .ai-container textarea#promptInput { min-height: 120px; padding: 15px; font-size: 15px;}
            .ai-container button#submitButton { padding: 14px 18px; font-size: 16px; }
            .ai-container #responseContainer h2 { font-size: 1.5em; }
            .ai-container #response { padding: 18px; font-size: 15px; }
        }
        @media (max-width: 480px) {
            /* body { padding: 10px; } // Mungkin tidak perlu jika header/footer menangani padding */
            .ai-container { padding: 20px 25px; }
            .ai-container h1 { font-size: 1.7em; }
            .ai-container h1 .fa-book-reader { margin-right: 8px; }
            .ai-container .subtitle { font-size: 0.95em; margin-bottom: 20px; }
            .ai-container textarea#promptInput { min-height: 100px; padding: 12px; font-size: 14px;}
            .ai-container button#submitButton { padding: 12px 15px; font-size: 15px; }
            .ai-container #responseContainer h2 { font-size: 1.3em; }
            .ai-container #responseContainer h2 .fa-comments { margin-right: 8px; }
            .ai-container #response { padding: 15px; font-size: 14px; }
            .ai-container .status-message { padding: 15px 18px; }
        }
    </style>
</head>
<body> 
    <?php // Main content area - Anda mungkin punya div pembungkus utama dari layout ?>
    <div class="ai-container">
        <h1><i class="fas fa-book-reader"></i>Tera AI</h1>
        <p class="subtitle">Selamat datang di LibTera AI! Ajukan pertanyaan Anda seputar dunia literasi, materi pembelajaran, atau topik umum lainnya.</p>
        
        <textarea id="promptInput" placeholder="Ketik pertanyaan Anda di sini... (Tekan Enter untuk mengirim, Shift+Enter untuk baris baru)"></textarea>
        <button id="submitButton" onclick="kirimPertanyaan()">
            <i class="fas fa-paper-plane"></i> Tanyakan
        </button>
        
        <div id="responseContainer">
            <h2><i class="fas fa-comments"></i>Jawaban Tera AI:</h2>
            <div id="response" class="placeholder-text">Menunggu pertanyaan Anda...</div>
        </div>
    </div>

    <script>
        const promptInput = document.getElementById('promptInput');
        const responseDiv = document.getElementById('response');
        const submitButton = document.getElementById('submitButton');

        promptInput.addEventListener('keydown', function(event) {
            if (event.key === 'Enter' && !event.shiftKey) {
                event.preventDefault(); 
                kirimPertanyaan();
            }
        });

        function simpleMarkdownToHtml(mdText) {
            let htmlText = mdText;
            htmlText = htmlText.replace(/\n/g, '<br>');
            htmlText = htmlText.replace(/\*\*(.*?)\*\*|__(.*?)__/g, '<strong>$1$2</strong>');
            htmlText = htmlText.replace(/^(\d+)\.\s+(.*?)(\<br\>|$)/gm, '<li>$2</li>');
            if (/<li>(.*?)<\/li>/.test(htmlText)) {
                if (!htmlText.startsWith('<ol>') && !htmlText.startsWith('<ul>')) {
                    htmlText = htmlText.replace(/(<li>.*?<\/li>(\s*<br\s*\/?>\s*<li>.*?<\/li>)*)/g, '<ol>$1</ol>');
                    htmlText = htmlText.replace(/<\/li>\s*<br\s*\/?>\s*<li>/g, '</li><li>');
                }
            }
            htmlText = htmlText.replace(/<\/li><br>($|<\/ol>)/g, '</li>$1');
            return htmlText;
        }

        async function kirimPertanyaan() {
            const promptText = promptInput.value;
            
            if (!promptText.trim()) {
                responseDiv.innerHTML = '<i class="fas fa-exclamation-circle"></i> Mohon masukkan pertanyaan terlebih dahulu.';
                responseDiv.className = 'status-message error';
                return;
            }

            submitButton.disabled = true;
            submitButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Mengirim...';
            promptInput.disabled = true;

            responseDiv.innerHTML = '<i class="fas fa-hourglass-half fa-spin"></i> Sedang memproses permintaan Anda...';
            responseDiv.className = 'status-message loading';
            responseDiv.classList.remove('placeholder-text');

            try {
                // Pastikan URL ini sesuai dengan lokasi file gemini_api.php Anda
                // Jika tanya_ai.php dan gemini_api.php ada di folder yang sama:
                const response = await fetch('gemini_api.php', { 
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ prompt: promptText })
                });

                const data = await response.json();

                if (!response.ok) {
                    const errorMessage = data.error || `Terjadi kesalahan HTTP: ${response.status} ${response.statusText}`;
                    throw new Error(errorMessage + (data.details ? ` Detail: ${JSON.stringify(data.details)}` : ''));
                }

                if (data.response) {
                    responseDiv.innerHTML = simpleMarkdownToHtml(data.response);
                    responseDiv.className = ''; 
                } else if (data.error) {
                    responseDiv.innerHTML = `<i class="fas fa-times-circle"></i> Gagal mendapatkan jawaban: ${data.error}` + (data.details ? ` Detail: ${JSON.stringify(data.details)}` : '');
                    responseDiv.className = 'status-message error';
                } else {
                    responseDiv.innerHTML = '<i class="fas fa-question-circle"></i> Format respons dari server tidak dikenali.';
                    responseDiv.className = 'status-message error';
                }

            } catch (error) {
                console.error('Terjadi kesalahan:', error);
                responseDiv.innerHTML = `<i class="fas fa-shield-alt"></i> Gagal mendapatkan jawaban: ${error.message}`;
                responseDiv.className = 'status-message error';
            } finally {
                submitButton.disabled = false;
                submitButton.innerHTML = '<i class="fas fa-paper-plane"></i> Tanyakan';
                promptInput.disabled = false;
                promptInput.focus(); 
            }
        }
    </script>
</body>
</html>
