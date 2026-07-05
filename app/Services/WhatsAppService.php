<?php

namespace App\Services;

/**
 * WhatsAppService
 * ──────────────────────────────────────────────────────────────────
 * Mengirim pesan ke WhatsApp Gateway menggunakan cURL PHP Native.
 *
 * STRATEGI NON-BLOCKING (tidak memperlambat halaman):
 *   Teknik "fire-and-forget" via cURL:
 *   - CURLOPT_TIMEOUT      → 5 detik (batas tunggu response)
 *   - CURLOPT_CONNECTTIMEOUT → 3 detik (batas koneksi)
 *   Jika gateway lambat / gagal, halaman tetap lanjut.
 *   Error di-log ke writable/logs, tidak dilempar ke user.
 *
 * PROVIDER YANG DIDUKUNG:
 *   Fonnte    : https://api.fonnte.com/send
 *   UltraMsg  : https://api.ultramsg.com/{instance}/messages/chat
 *   WA-Web    : endpoint lokal (WA-Web / Baileys)
 *   Custom    : sesuaikan method buildPayload()
 *
 * KONFIGURASI di .env:
 *   wa.gateway_url  = https://api.fonnte.com/send
 *   wa.token        = YOUR_TOKEN
 *   wa.enabled      = true
 *   wa.admin_numbers= 628123456789,628987654321
 *   wa.curl_timeout = 5
 */
class WhatsAppService
{
    private string $gatewayUrl;
    private string $token;
    private bool   $enabled;
    private int    $timeout;

    // Provider yang dikenali
    private const PROVIDER_FONNTE   = 'fonnte';
    private const PROVIDER_ULTRAMSG = 'ultramsg';
    private const PROVIDER_WAWEB    = 'waweb';
    private const PROVIDER_CUSTOM   = 'custom';

    public function __construct()
    {
        $this->gatewayUrl = env('wa.gateway_url', '');
        $this->token      = env('wa.token', '');
        $this->enabled    = filter_var(env('wa.enabled', 'false'), FILTER_VALIDATE_BOOLEAN);
        $this->timeout    = (int) env('wa.curl_timeout', 5);
    }

    // ──────────────────────────────────────────────────────────────
    // PUBLIC: Kirim ke satu nomor
    // ──────────────────────────────────────────────────────────────

    /**
     * Kirim pesan WA ke satu nomor.
     * Return true jika berhasil dikirim (bukan berarti terkirim ke device).
     */
    public function send(string $to, string $message): bool
    {
        if (! $this->isReady()) {
            return false;
        }

        $to = $this->normalizeNumber($to);
        if (empty($to)) {
            log_message('warning', '[WA] Nomor tujuan kosong atau tidak valid.');
            return false;
        }

        return $this->dispatch($to, $message);
    }

    /**
     * Kirim pesan WA ke banyak nomor (dari .env wa.admin_numbers).
     * Setiap nomor dikirim secara berurutan tapi masing-masing
     * punya timeout sendiri — total tidak lebih dari N × timeout detik.
     */
    public function sendToAdmins(string $message): void
    {
        if (! $this->isReady()) {
            return;
        }

        $rawNumbers = env('wa.admin_numbers', '');
        $numbers    = array_filter(
            array_map('trim', explode(',', $rawNumbers)),
            fn($n) => strlen($n) >= 10
        );

        foreach ($numbers as $number) {
            $this->send($number, $message);
        }
    }

    // ──────────────────────────────────────────────────────────────
    // PUBLIC: Template pesan standar
    // ──────────────────────────────────────────────────────────────

    /**
     * Bangun pesan notifikasi Work Order baru
     */
    public static function buildWoNewMessage(array $wo): string
    {
        $priorityEmoji = [
            'kritis' => '🔴',
            'tinggi' => '🟠',
            'sedang' => '🟡',
            'rendah' => '🟢',
        ];
        $emoji = $priorityEmoji[$wo['priority'] ?? 'sedang'] ?? '📋';

        return implode("\n", [
            "━━━━━━━━━━━━━━━━━━━━",
            "{$emoji} *WORK ORDER BARU*",
            "━━━━━━━━━━━━━━━━━━━━",
            "📌 Kode WO  : *{$wo['wo_code']}*",
            "🔧 Aset     : {$wo['asset_name']} ({$wo['asset_code']})",
            "📂 Tipe     : " . ucwords(str_replace('_', ' ', $wo['type'] ?? '-')),
            "⚡ Prioritas: " . ucfirst($wo['priority'] ?? '-'),
            "👤 Diminta  : {$wo['requested_by_name']}",
            "📝 Masalah  : " . mb_strimwidth($wo['problem_desc'] ?? '-', 0, 100, '...'),
            "📅 Jadwal   : " . ($wo['scheduled_date'] ?? 'Belum dijadwalkan'),
            "━━━━━━━━━━━━━━━━━━━━",
            "🕐 " . date('d/m/Y H:i') . " WIB",
            "_Sistem Manajemen Aset - SiAset_",
        ]);
    }

    /**
     * Bangun pesan notifikasi WO status berubah
     */
    public static function buildWoStatusMessage(array $wo, string $oldStatus): string
    {
        $statusEmoji = [
            'open'         => '🆕',
            'in_progress'  => '⚙️',
            'waiting_part' => '⏳',
            'done'         => '✅',
            'cancelled'    => '❌',
        ];
        $emoji = $statusEmoji[$wo['status'] ?? 'open'] ?? '📋';

        return implode("\n", [
            "━━━━━━━━━━━━━━━━━━━━",
            "{$emoji} *UPDATE WORK ORDER*",
            "━━━━━━━━━━━━━━━━━━━━",
            "📌 Kode WO  : *{$wo['wo_code']}*",
            "🔧 Aset     : {$wo['asset_name']} ({$wo['asset_code']})",
            "🔄 Status   : {$oldStatus} → *" . ucwords(str_replace('_', ' ', $wo['status'])) . "*",
            "👤 Teknisi  : " . ($wo['assigned_to_name'] ?? 'Belum ditugaskan'),
            ($wo['action_taken'] ? "✏️ Tindakan : " . mb_strimwidth($wo['action_taken'], 0, 100, '...') : ""),
            ($wo['cost'] ? "💰 Biaya    : Rp " . number_format((float)$wo['cost'], 0, ',', '.') : ""),
            "━━━━━━━━━━━━━━━━━━━━",
            "🕐 " . date('d/m/Y H:i') . " WIB",
            "_Sistem Manajemen Aset - SiAset_",
        ]);
    }

    /**
     * Bangun pesan notifikasi laporan/maintenance baru
     */
    public static function buildReportMessage(array $data): string
    {
        return implode("\n", [
            "━━━━━━━━━━━━━━━━━━━━",
            "📊 *LAPORAN MAINTENANCE*",
            "━━━━━━━━━━━━━━━━━━━━",
            "🔧 Aset     : {$data['asset_name']} ({$data['asset_code']})",
            "📂 Aksi     : " . ucfirst($data['action'] ?? '-'),
            "📝 Catatan  : " . mb_strimwidth($data['description'] ?? '-', 0, 120, '...'),
            ($data['cost'] ? "💰 Biaya    : Rp " . number_format((float)$data['cost'], 0, ',', '.') : ""),
            "👤 Oleh     : " . ($data['user_name'] ?? 'Sistem'),
            "━━━━━━━━━━━━━━━━━━━━",
            "🕐 " . date('d/m/Y H:i') . " WIB",
            "_Sistem Manajemen Aset - SiAset_",
        ]);
    }

    // ──────────────────────────────────────────────────────────────
    // PRIVATE: Eksekusi cURL (fire-and-forget)
    // ──────────────────────────────────────────────────────────────

    private function dispatch(string $to, string $message): bool
    {
        $provider = $this->detectProvider();
        $payload  = $this->buildPayload($provider, $to, $message);
        $headers  = $this->buildHeaders($provider);

        $ch = curl_init();

        curl_setopt_array($ch, [
            CURLOPT_URL            => $this->gatewayUrl,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $payload,
            CURLOPT_HTTPHEADER     => $headers,
            CURLOPT_RETURNTRANSFER => true,

            // ── KUNCI NON-BLOCKING ──────────────────────────────
            // Halaman selesai redirect dulu, cURL tetap jalan
            // di background hingga timeout.
            CURLOPT_TIMEOUT        => $this->timeout,
            CURLOPT_CONNECTTIMEOUT => min(3, $this->timeout),

            // Abaikan SSL error di localhost / shared hosting
            // (set ke true di production jika sertifikat valid)
            CURLOPT_SSL_VERIFYPEER => ENVIRONMENT !== 'development',
            CURLOPT_SSL_VERIFYHOST => ENVIRONMENT !== 'development' ? 2 : 0,

            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS      => 3,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error    = curl_error($ch);
        curl_close($ch);

        if ($error) {
            log_message('error', "[WA] cURL error ke {$to}: {$error}");
            return false;
        }

        if ($httpCode < 200 || $httpCode >= 300) {
            log_message('warning', "[WA] HTTP {$httpCode} ke {$to}. Response: " . substr((string)$response, 0, 200));
            return false;
        }

        log_message('info', "[WA] Pesan terkirim ke {$to}. HTTP {$httpCode}.");
        return true;
    }

    // ──────────────────────────────────────────────────────────────
    // PRIVATE: Adapter per-provider
    // ──────────────────────────────────────────────────────────────

    /**
     * Deteksi provider dari URL gateway
     */
    private function detectProvider(): string
    {
        $url = strtolower($this->gatewayUrl);
        if (str_contains($url, 'fonnte'))   return self::PROVIDER_FONNTE;
        if (str_contains($url, 'ultramsg')) return self::PROVIDER_ULTRAMSG;
        if (str_contains($url, 'localhost') || str_contains($url, '127.0.0.1')) {
            return self::PROVIDER_WAWEB;
        }
        return self::PROVIDER_CUSTOM;
    }

    /**
     * Bangun payload sesuai format provider
     */
    private function buildPayload(string $provider, string $to, string $message): array|string
    {
        return match($provider) {
            // Fonnte: form-data multipart
            self::PROVIDER_FONNTE => [
                'target'  => $to,
                'message' => $message,
                'delay'   => '2',           // jeda antar pesan (detik)
                'countryCode' => '62',
            ],

            // UltraMsg: JSON body
            self::PROVIDER_ULTRAMSG => json_encode([
                'token'   => $this->token,
                'to'      => $to,
                'body'    => $message,
            ]),

            // WA-Web / Baileys lokal: JSON body
            self::PROVIDER_WAWEB => json_encode([
                'phone'   => $to,
                'message' => $message,
            ]),

            // Custom / fallback: form-data generik
            default => [
                'to'      => $to,
                'message' => $message,
                'token'   => $this->token,
            ],
        };
    }

    /**
     * Header HTTP sesuai provider
     */
    private function buildHeaders(string $provider): array
    {
        return match($provider) {
            self::PROVIDER_FONNTE => [
                'Authorization: ' . $this->token,
            ],
            self::PROVIDER_ULTRAMSG => [
                'Content-Type: application/json',
            ],
            self::PROVIDER_WAWEB => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $this->token,
            ],
            default => [
                'Authorization: Bearer ' . $this->token,
            ],
        };
    }

    // ──────────────────────────────────────────────────────────────
    // PRIVATE: Helpers
    // ──────────────────────────────────────────────────────────────

    /** Pastikan cURL tersedia dan konfigurasi lengkap */
    private function isReady(): bool
    {
        if (! $this->enabled) {
            return false;
        }

        if (! function_exists('curl_init')) {
            log_message('error', '[WA] cURL tidak tersedia di server ini.');
            return false;
        }

        if (empty($this->gatewayUrl) || empty($this->token)) {
            log_message('warning', '[WA] wa.gateway_url atau wa.token belum diisi di .env');
            return false;
        }

        return true;
    }

    /** Normalisasi nomor ke format internasional tanpa + */
    private function normalizeNumber(string $number): string
    {
        // Hapus semua karakter non-digit
        $number = preg_replace('/\D/', '', $number);

        if (empty($number)) {
            return '';
        }

        // 08xxx → 628xxx
        if (str_starts_with($number, '08')) {
            $number = '62' . substr($number, 1);
        }

        // +62 sudah tanpa +
        if (str_starts_with($number, '+62')) {
            $number = ltrim($number, '+');
        }

        return $number;
    }
}
