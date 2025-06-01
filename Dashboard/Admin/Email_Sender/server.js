const express = require('express');
const nodemailer = require('nodemailer');
const rateLimit = require('express-rate-limit');
const helmet = require('helmet');
const { body, validationResult } = require('express-validator');
const slowDown = require('express-slow-down');
const cors = require('cors'); // Pastikan ini ada dan sudah diinstal (npm install cors)
require('dotenv').config();

const app = express();

// ==================== MIDDLEWARES ====================
app.use(cors()); // Izinkan semua origin untuk development. Untuk produksi, batasi: app.use(cors({ origin: 'http://url-php-anda.com' }));
app.use(helmet());
app.use(express.json({ limit: '10kb' }));

const limiter = rateLimit({
  windowMs: 15 * 60 * 1000,
  max: 30, // Naikkan sedikit untuk testing, bisa disesuaikan lagi
  message: 'Terlalu banyak permintaan dari IP ini, silakan coba lagi setelah 15 menit',
  standardHeaders: true,
  legacyHeaders: false,
});
app.use(limiter);

const speedLimiter = slowDown({
  windowMs: 15 * 60 * 1000,
  delayAfter: 10, // Naikkan sedikit untuk testing
  delayMs: (hits) => hits * 100,
});

const validateEmailInput = [
  body('to').isEmail().withMessage('Format email tujuan tidak valid').normalizeEmail(),
  body('subject').trim().isLength({ min: 1, max: 150 }).withMessage('Subjek harus antara 1 dan 150 karakter').escape(),
  body('message').trim().isLength({ min: 1 }).withMessage('Pesan tidak boleh kosong'), // Tidak perlu escape di sini jika message adalah HTML
  body('template').optional().trim().escape()
];

const requestCache = new Map();
const CACHE_TTL = 5 * 60 * 1000;

// ==================== EMAIL TRANSPORTER ====================
const transporter = nodemailer.createTransport({
  service: 'gmail',
  pool: true,
  maxConnections: 1,
  maxMessages: 10,
  auth: {
    user: process.env.EMAIL_USER,
    pass: process.env.EMAIL_PASS
  },
  tls: {
    rejectUnauthorized: false 
  }
});

// ==================== EMAIL ENDPOINT ====================
app.post('/send-email', speedLimiter, validateEmailInput, async (req, res) => {
  try {
    const errors = validationResult(req);
    if (!errors.isEmpty()) {
      return res.status(400).json({ errors: errors.array() });
    }

    let { to, subject, message, template } = req.body;

    if (!template || !['default', 'dark', 'struck'].includes(template)) {
        template = 'default'; 
    }
    
    // 'message' yang dikirim dari PHP adalah HTML yang sudah diformat dengan placeholder yang diganti.
    // Template wrapper di server.js akan membungkus 'message' ini.
    const htmlWrappers = {
      default: (subjectMessage, bodyMessage, toEmail) => 
                `<div style="font-family: Arial, sans-serif; padding: 20px; background: #f9f9f9; border: 1px solid #ddd; border-radius: 8px; max-width: 700px; margin: auto;">
                  <h2 style="color: #2c3e50; border-bottom: 1px solid #eee; padding-bottom: 10px;">${subjectMessage}</h2>
                  <div style="color: #333; font-size: 16px; line-height: 1.6;">${bodyMessage}</div>
                  <hr style="margin: 20px 0; border: 0; border-top: 1px solid #ccc;">
                  <p style="font-size: 12px; color: #999; text-align: center;">Email ini dikirim melalui Sistem Notifikasi Perpustakaan Libtera.</p>
                </div>`,
      dark:    (subjectMessage, bodyMessage, toEmail) => 
                `<div style="background: #1e1e1e; color: #f0f0f0; padding: 20px; border-radius: 8px; font-family: monospace, Arial, sans-serif; max-width: 700px; margin: auto;">
                  <h2 style="color: #4caf50; border-bottom: 1px solid #333; padding-bottom: 10px;">${subjectMessage}</h2>
                  <div style="white-space: pre-wrap; word-wrap: break-word; line-height: 1.6; color: #ccc;">${bodyMessage}</div>
                  <hr style="margin: 20px 0; border: 0; border-top: 1px solid #333;">
                  <p style="font-size: 10px; color: #777; text-align: center;">Email ini dikirim melalui Sistem Notifikasi Perpustakaan Libtera.</p>
                </div>`,
      struck:  (subjectMessage, bodyMessage, toEmail) => 
                `<div style="padding:20px; border:1px dashed #222; font-size:15px; font-family: 'Courier New', Courier, monospace; max-width: 700px; margin: auto;">
                  <h2 style="color: #333; border-bottom: 1px dashed #555; padding-bottom: 10px;">${subjectMessage}</h2>
                  <p>Kepada: <b>${toEmail}</b>,</p>
                  <br>
                  <div style="white-space: pre-wrap; word-wrap: break-word;">${bodyMessage}</div>
                  <br>
                  <hr style="border:0px; border-top:1px dashed #222; margin: 20px 0;">
                  <p style="font-size: 12px; text-align: center;">Email ini dikirim melalui Sistem Notifikasi Perpustakaan Libtera.</p>
                </div>`
    };
    
    const selectedTemplateFunction = htmlWrappers[template] || htmlWrappers['default'];
    // Message dari PHP adalah isi utama, subject adalah subjek email.
    const finalHtmlMessage = selectedTemplateFunction(subject, message, to);


    const requestKey = `${to}-${subject}-${message.substring(0, 50)}`;
    if (requestCache.has(requestKey) && (Date.now() - requestCache.get(requestKey) < CACHE_TTL)) {
      return res.status(429).json({
        message: 'Email serupa baru saja dikirim. Mohon tunggu beberapa saat sebelum mengirim lagi.'
      });
    }
    
    const blockedDomains = (process.env.BLOCKED_DOMAINS || 'example.com,test.com').split(',');
    const recipientDomainSplitted = to.split('@');
    if (recipientDomainSplitted.length > 1) {
        const recipientDomain = recipientDomainSplitted[1];
        if (blockedDomains.includes(recipientDomain.toLowerCase())) {
          return res.status(400).json({ message: 'Domain email ini tidak diizinkan.' });
        }
    }

    const mailOptions = {
      from: `"Admin Perpustakaan Libtera" <${process.env.EMAIL_USER}>`,
      to: to,
      subject: subject, 
      html: finalHtmlMessage, 
      priority: 'normal'
    };

    const sendMailPromise = transporter.sendMail(mailOptions);
    const timeoutPromise = new Promise((_, reject) =>
      setTimeout(() => reject(new Error('timeout')), 15000) // Timeout sedikit lebih lama (15 detik)
    );

    await Promise.race([sendMailPromise, timeoutPromise]);
    
    requestCache.set(requestKey, Date.now());
    res.json({ message: "Email berhasil dikirim!" });

  } catch (error) {
    console.error('Kesalahan pada endpoint /send-email:', error);
    let errorMessage = "Gagal mengirim email. Silakan coba lagi nanti.";
    let statusCode = 500;

    if (error.message && error.message.toLowerCase().includes('timeout')) {
      errorMessage = "Pengiriman email terlalu lama (timeout).";
      statusCode = 504;
    } else if (error.code === 'ECONNECTION' || error.code === 'EENVELOPE') { 
      errorMessage = "Layanan email tidak tersedia atau ada masalah konfigurasi. Periksa kredensial email.";
      statusCode = 503;
    } else if (error.responseCode === 535) { 
        errorMessage = "Autentikasi email gagal. Periksa EMAIL_USER dan EMAIL_PASS di .env.";
        statusCode = 401;
    }
    res.status(statusCode).json({ message: errorMessage, details: error.message });
  }
});

// ==================== SERVER SETUP ====================
const PORT = process.env.PORT || 3000;
app.listen(PORT, () => {
  console.log(`🛡️ Server Email Sender aman berjalan di port ${PORT}`);
  console.log(`   Endpoint: http://localhost:${PORT}/send-email`);
});

setInterval(() => {
  const now = Date.now();
  requestCache.forEach((timestamp, key) => {
    if (now - timestamp > CACHE_TTL) {
      requestCache.delete(key);
      // console.log(`Cache untuk key ${key} dihapus.`); // Bisa di-uncomment untuk debugging cache
    }
  });
}, CACHE_TTL); // Jalankan pembersihan cache sesuai TTL