<?php
// WAJIB: Memulai atau melanjutkan session yang sudah ada agar dikenali oleh header.php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
$title = "Tanya AI LibTera"; // Menentukan judul halaman untuk template Anda
include_once __DIR__ . '/../../../layout/header.php';
?>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

<style>
    /* Semua CSS khusus untuk halaman AI ini kita letakkan di sini.
      Ini tidak akan mengganggu file style.css utama Anda.
    */
    body {
        /* Font ini akan diterapkan jika di-load dari link di atas */
        font-family: 'Poppins', Arial, sans-serif; 
    }

    .ai-container {
        width: 100%;
        max-width: 800px;
        background-color: #ffffff;
        padding: 35px 45px;
        border-radius: 16px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
        margin: 0 auto 40px auto; /* Margin atas 0, bawah 40px, kiri-kanan auto */
    }

    /* Gaya lain untuk .ai-container dan elemen di dalamnya tetap sama seperti sebelumnya... */
    .ai-container h1 {
        text-align: center; color: #2c3e50; margin-bottom: 15px; font-weight: 600; font-size: 2.3em;
    }
    .ai-container h1 .fa-book-reader { margin-right: 12px; color: #2980b9; }
    .ai-container .subtitle { text-align: center; color: #566573; margin-bottom: 35px; font-size: 1.1em; line-height: 1.6; }
    .ai-container textarea#promptInput {
        width: 100%; box-sizing: border-box; min-height: 140px; margin-bottom: 20px; padding: 18px; 
        border: 1px solid #dbe2e8; border-radius: 10px; font-size: 16px; line-height: 1.6; resize: vertical;
        transition: border-color 0.3s ease, box-shadow 0.3s ease;
    }
    .ai-container textarea#promptInput:focus {
        border-color: #2980b9; box-shadow: 0 0 8px rgba(41, 128, 185, 0.2); outline: none;
    }
    .ai-container button#submitButton {
        display: flex; align-items: center; justify-content: center; width: 100%; padding: 16px 22px;
        background-image: linear-gradient(to right, #3498db, #2980b9); color: white; border: none;
        border-radius: 10px; cursor: pointer; font-size: 17px; font-weight: 500;
        transition: background-image 0.4s ease, transform 0.2s ease, box-shadow 0.3s ease;
        box-shadow: 0 4px 12px rgba(41, 128, 185, 0.25);
    }
    .ai-container button#submitButton .fa-paper-plane { margin-right: 10px; }
    .ai-container button#submitButton:hover {
        background-image: linear-gradient(to right, #2980b9, #2471a3); transform: translateY(-3px);
        box-shadow: 0 6px 15px rgba(41, 128, 185, 0.35);
    }
    .ai-container button#submitButton:disabled {
        background-image: linear-gradient(to right, #bdc3c7, #95a5a6); cursor: not-allowed;
        box-shadow: none; transform: translateY(0);
    }
    .ai-container button#submitButton:disabled .fa-spinner { margin-right: 10px; }
    .ai-container #responseContainer { margin-top: 40px; border-top: 1px solid #e0e6ed; padding-top: 30px; }
    .ai-container .response-header {
        display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;
    }
    .ai-container #responseContainer h2 {
        color: #2c3e50; font-weight: 600; font-size: 1.7em;
        display: flex; align-items: center; margin: 0;
    }
    .ai-container #responseContainer h2 .fa-comments { margin-right: 10px; color: #16a085; }
    #copyButton {
        background-color: #eaf2f8; color: #2980b9; border: 1px solid #d6eaf8;
        border-radius: 8px; padding: 8px 14px; font-size: 14px; cursor: pointer;
        transition: all 0.2s ease-in-out; display: none;
    }
    #copyButton:hover {
        background-color: #d6eaf8; color: #2471a3; transform: translateY(-2px);
        box-shadow: 0 2px 5px rgba(0,0,0,0.05);
    }
    #copyButton .fa-copy, #copyButton .fa-check { margin-right: 8px; }
    #copyButton.copied {
        background-color: #d1f2eb; color: #16a085; border-color: #a3e4d7;
    }
    .ai-container #response {
        padding: 20px; border: 1px solid #e0e6ed; background-color: #f8f9fc; border-radius: 10px;
        white-space: pre-wrap; word-wrap: break-word; min-height: 80px; font-size: 16px;
        line-height: 1.7; color: #495057;
    }
    .ai-container #response strong { font-weight: 600; color: #2c3e50; }
    .ai-container #response ol, .ai-container #response ul { padding-left: 25px; margin: 10px 0; }
    .ai-container #response li { margin-bottom: 8px; line-height: 1.6; }
    @media (max-width: 480px) {
        .ai-container { padding: 20px 25px; }
        .ai-container .response-header { flex-direction: column; align-items: flex-start; gap: 10px; }
    }
</style>

<div class="ai-container">
    <h1><i class="fas fa-book-reader"></i>Tera AI</h1>
    <p class="subtitle">Ajukan pertanyaan Anda seputar dunia literasi, materi pembelajaran, atau topik umum lainnya.</p>
    
    <textarea id="promptInput" placeholder="Ketik pertanyaan Anda di sini... (Tekan Enter untuk mengirim, Shift+Enter untuk baris baru)"></textarea>
    <button id="submitButton" onclick="kirimPertanyaan()">
        <i class="fas fa-paper-plane"></i> Tanyakan
    </button>
    
    <div id="responseContainer">
        <div class="response-header">
            <h2><i class="fas fa-comments"></i>Jawaban Tera AI:</h2>
            <button id="copyButton">
                <i class="fas fa-copy"></i> Salin
            </button>
        </div>
        <div id="response" class="placeholder-text">Menunggu pertanyaan Anda...</div>
    </div>
</div>

<script>
    // Konstanta dan event listener tetap sama
    const promptInput = document.getElementById('promptInput');
    const responseDiv = document.getElementById('response');
    const submitButton = document.getElementById('submitButton');
    const copyButton = document.getElementById('copyButton');

    promptInput.addEventListener('keydown', function(event) {
        if (event.key === 'Enter' && !event.shiftKey) {
            event.preventDefault(); 
            kirimPertanyaan();
        }
    });
    
    copyButton.addEventListener('click', () => {
        const textToCopy = responseDiv.innerText;
        navigator.clipboard.writeText(textToCopy).then(() => {
            copyButton.innerHTML = '<i class="fas fa-check"></i> Tersalin!';
            copyButton.classList.add('copied');
            setTimeout(() => {
                copyButton.innerHTML = '<i class="fas fa-copy"></i> Salin';
                copyButton.classList.remove('copied');
            }, 2000);
        });
    });

    // Fungsi markdown dan kirim pertanyaan tetap sama
    function simpleMarkdownToHtml(mdText) {
        let html = mdText
            .replace(/</g, '&lt;').replace(/>/g, '&gt;')
            .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
            .replace(/\n/g, '<br>');
        html = html.replace(/^(<br>|\s)*(\*|\-)\s(.*)/gm, '<ul><li>$3</li></ul>');
        html = html.replace(/^(<br>|\s)*(\d+\.)\s(.*)/gm, '<ol><li>$3</li></ol>');
        html = html.replace(/<\/ul>(<br>|\s)*<ul>/g, '');
        html = html.replace(/<\/ol>(<br>|\s)*<ol>/g, '');
        return html;
    }

    async function kirimPertanyaan() {
        const promptText = promptInput.value;
        if (!promptText.trim()) return;

        submitButton.disabled = true;
        submitButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Mengirim...';
        promptInput.disabled = true;
        copyButton.style.display = 'none';
        responseDiv.innerHTML = '<div class="d-flex align-items-center"><div class="spinner-border spinner-border-sm me-2" role="status"></div><span>Sedang memproses...</span></div>';
        responseDiv.className = '';

        try {
            const response = await fetch('gemini_api.php', { 
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ prompt: promptText })
            });
            const data = await response.json();
            if (!response.ok) throw new Error(data.error || 'Terjadi kesalahan pada server.');
            responseDiv.innerHTML = simpleMarkdownToHtml(data.response);
            copyButton.style.display = 'inline-block';
        } catch (error) {
            console.error('Terjadi kesalahan:', error);
            responseDiv.innerHTML = `<i class="fas fa-shield-alt"></i> Gagal mendapatkan jawaban: ${error.message}`;
        } finally {
            submitButton.disabled = false;
            submitButton.innerHTML = '<i class="fas fa-paper-plane"></i> Tanyakan';
            promptInput.disabled = false;
            promptInput.focus(); 
        }
    }
</script>

<?php
// Terakhir, kita tutup dengan footer
include_once __DIR__ . '/../../../layout/footer.php';
?>