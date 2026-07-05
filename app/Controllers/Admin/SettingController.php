<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Services\WhatsAppService;

/**
 * SettingController — Pengaturan Sistem
 *
 * Routes:
 *   GET  /admin/settings          → index (semua tab)
 *   POST /admin/settings/whatsapp → saveWhatsapp
 *   POST /admin/settings/wa-test  → testWhatsapp
 */
class SettingController extends BaseController
{
    public function index()
    {
        return view('settings/index', [
            'title'    => 'Pengaturan Sistem',
            'envPath'  => ROOTPATH . '.env',
            'wa'       => $this->readWaConfig(),
            'appConfig'=> $this->readAppConfig(),
        ]);
    }

    // ================================================================
    // POST /admin/settings/whatsapp  — Simpan konfigurasi WA ke .env
    // ================================================================
    public function saveWhatsapp()
    {
        $rules = [
            'wa_gateway_url'   => 'required|valid_url',
            'wa_token'         => 'required|min_length[10]',
            'wa_admin_numbers' => 'required',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()
                ->with('wa_errors', $this->validator->getErrors());
        }

        $gatewayUrl    = trim($this->request->getPost('wa_gateway_url'));
        $token         = trim($this->request->getPost('wa_token'));
        $adminNumbers  = trim($this->request->getPost('wa_admin_numbers'));
        $enabled       = $this->request->getPost('wa_enabled') === '1' ? 'true' : 'false';
        $timeout       = max(3, (int) ($this->request->getPost('wa_curl_timeout') ?: 5));

        // Normalisasi nomor admin (pisah koma, trim, buang non-digit)
        $numbers = array_filter(
            array_map(fn($n) => preg_replace('/\D/', '', str_starts_with(trim($n), '08')
                ? '62' . substr(trim($n), 1) : trim($n)),
                explode(',', $adminNumbers)
            ),
            fn($n) => strlen($n) >= 10
        );
        $adminNumbers = implode(',', $numbers);

        $this->updateEnv([
            'wa.gateway_url'   => $gatewayUrl,
            'wa.token'         => $token,
            'wa.enabled'       => $enabled,
            'wa.admin_numbers' => $adminNumbers,
            'wa.curl_timeout'  => (string) $timeout,
        ]);

        return redirect()->to('/admin/settings#whatsapp')
            ->with('success', 'Konfigurasi WhatsApp Gateway berhasil disimpan.');
    }

    // ================================================================
    // POST /admin/settings/wa-test  — Test kirim pesan ke nomor sendiri
    // ================================================================
    public function testWhatsapp()
    {
        $testNumber = trim($this->request->getPost('test_number'));
        if (empty($testNumber)) {
            return redirect()->back()->with('wa_test_error', 'Nomor tujuan test tidak boleh kosong.');
        }

        $wa = new WhatsAppService();
        $message = implode("\n", [
            "━━━━━━━━━━━━━━━━━━━━",
            "✅ *TEST KONEKSI WHATSAPP*",
            "━━━━━━━━━━━━━━━━━━━━",
            "Halo! Pesan ini dikirim dari SiAset.",
            "📅 Waktu : " . date('d/m/Y H:i:s') . " WIB",
            "🌐 Gateway: " . env('wa.gateway_url'),
            "━━━━━━━━━━━━━━━━━━━━",
            "Jika Anda menerima pesan ini,",
            "konfigurasi WhatsApp Gateway sudah *berhasil*.",
            "_SiAset — Sistem Manajemen Aset_",
        ]);

        $success = $wa->send($testNumber, $message);

        if ($success) {
            return redirect()->back()
                ->with('wa_test_success', 'Pesan test berhasil dikirim ke ' . $testNumber . '. Cek WhatsApp Anda.');
        }

        return redirect()->back()
            ->with('wa_test_error', 'Gagal mengirim pesan. Periksa token, URL gateway, dan pastikan WhatsApp aktif. Detail error ada di log server.');
    }

    // ================================================================
    // POST /admin/settings/app — Simpan pengaturan umum aplikasi
    // ================================================================
    public function saveApp()
    {
        $this->updateEnv([
            'app.appName'     => trim($this->request->getPost('app_name') ?: 'SiAset'),
            'app.appTimezone' => trim($this->request->getPost('app_timezone') ?: 'Asia/Jakarta'),
        ]);

        return redirect()->to('/admin/settings#app')
            ->with('success', 'Pengaturan aplikasi berhasil disimpan.');
    }

    // ================================================================
    // PRIVATE HELPERS
    // ================================================================

    /**
     * Baca konfigurasi WA dari env
     */
    private function readWaConfig(): array
    {
        return [
            'gateway_url'   => env('wa.gateway_url', 'https://api.fonnte.com/send'),
            'token'         => env('wa.token', ''),
            'enabled'       => filter_var(env('wa.enabled', 'false'), FILTER_VALIDATE_BOOLEAN),
            'admin_numbers' => env('wa.admin_numbers', ''),
            'curl_timeout'  => (int) env('wa.curl_timeout', 5),
        ];
    }

    private function readAppConfig(): array
    {
        return [
            'app_name'     => env('app.appName', 'SiAset'),
            'app_timezone' => env('app.appTimezone', 'Asia/Jakarta'),
            'base_url'     => env('app.baseURL', ''),
            'environment'  => env('CI_ENVIRONMENT', 'development'),
        ];
    }

    /**
     * Update key-value di file .env
     * Hanya mengubah baris yang sudah ada atau menambah di akhir.
     */
    private function updateEnv(array $updates): void
    {
        $envPath = ROOTPATH . '.env';
        if (! file_exists($envPath)) { return; }

        $content = file_get_contents($envPath);

        foreach ($updates as $key => $value) {
            // Jika value mengandung spasi, bungkus dengan tanda kutip
            $safeValue = str_contains($value, ' ') ? "'{$value}'" : $value;

            // Cari baris dengan key ini (termasuk yang dikomentari)
            $pattern = '/^[#\s]*' . preg_quote($key, '/') . '\s*=.*$/m';

            if (preg_match($pattern, $content)) {
                // Replace baris yang ada
                $content = preg_replace($pattern, $key . ' = ' . $safeValue, $content);
            } else {
                // Tambah di akhir file
                $content .= "\n" . $key . ' = ' . $safeValue;
            }
        }

        file_put_contents($envPath, $content);
    }
}
