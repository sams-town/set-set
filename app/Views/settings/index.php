<?= $this->extend('inventory/_layout') ?>
<?= $this->section('content') ?>

<div class="flex items-center justify-between mb-5">
    <div>
        <h1 class="text-xl font-bold text-gray-800">⚙️ Pengaturan</h1>
        <p class="text-sm text-gray-500 mt-0.5">Konfigurasi WhatsApp Gateway — RS.Taman Harapan Baru</p>
    </div>
</div>

<?php if ($msg = session()->getFlashdata('success')): ?>
<div class="mb-4 px-4 py-3 bg-green-100 text-green-800 rounded-xl text-sm flex items-center gap-2">
    ✅ <?= $msg ?>
</div>
<?php endif; ?>

<!-- Tab Navigation dihapus — hanya ada satu tab -->

<!-- ════════════════════════════════════════════════════════════════
     WHATSAPP GATEWAY FONNTE
     ════════════════════════════════════════════════════════════════ -->
<div id="tab_whatsapp" class="space-y-5">

    <!-- Status Badge -->
    <div class="flex items-center gap-3 p-4 <?= $wa['enabled'] ? 'bg-green-50 border-green-200' : 'bg-gray-50 border-gray-200' ?> border rounded-xl">
        <div class="text-3xl"><?= $wa['enabled'] ? '🟢' : '🔴' ?></div>
        <div>
            <p class="font-bold text-gray-800"><?= $wa['enabled'] ? 'WhatsApp Gateway AKTIF' : 'WhatsApp Gateway NONAKTIF' ?></p>
            <p class="text-sm text-gray-500">
                Provider terdeteksi:
                <?php
                $url = strtolower($wa['gateway_url'] ?? '');
                if (str_contains($url, 'fonnte'))         echo '<strong>Fonnte</strong>';
                elseif (str_contains($url, 'ultramsg'))   echo '<strong>UltraMsg</strong>';
                elseif (str_contains($url, 'localhost'))  echo '<strong>WA-Web Lokal</strong>';
                else                                      echo '<strong>Custom Gateway</strong>';
                ?>
            </p>
        </div>
    </div>

    <?php if ($errors = session()->getFlashdata('wa_errors')): ?>
    <div class="px-4 py-3 bg-red-50 border border-red-200 rounded-xl text-sm text-red-700">
        <ul class="list-disc list-inside space-y-1">
            <?php foreach ((array)$errors as $e): ?><li><?= esc($e) ?></li><?php endforeach; ?>
        </ul>
    </div>
    <?php endif; ?>

    <!-- Form Konfigurasi -->
    <form action="<?= base_url('admin/settings/whatsapp') ?>" method="POST"
          class="bg-white border rounded-xl p-6 shadow-sm space-y-4">
        <?= csrf_field() ?>

        <h2 class="text-sm font-bold text-gray-700 uppercase tracking-wide pb-2 border-b flex items-center gap-2">
            <span>💬</span> Konfigurasi Fonnte / Gateway
        </h2>

        <!-- Enable/Disable Toggle -->
        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
            <div>
                <p class="text-sm font-medium text-gray-700">Aktifkan Notifikasi WhatsApp</p>
                <p class="text-xs text-gray-400">Kirim notifikasi otomatis ke teknisi dan admin</p>
            </div>
            <label class="relative inline-flex items-center cursor-pointer">
                <input type="checkbox" name="wa_enabled" value="1" class="sr-only peer"
                       <?= $wa['enabled'] ? 'checked' : '' ?>
                       onchange="this.form.querySelector('[name=wa_enabled_hidden]').value = this.checked ? '1' : '0'">
                <input type="hidden" name="wa_enabled" value="<?= $wa['enabled'] ? '1' : '0' ?>">
                <div class="w-11 h-6 bg-gray-200 peer-checked:bg-green-500 rounded-full peer
                            peer-focus:ring-2 peer-focus:ring-green-300 transition-colors
                            after:content-[''] after:absolute after:top-0.5 after:left-0.5
                            after:bg-white after:rounded-full after:h-5 after:w-5
                            after:transition-all peer-checked:after:translate-x-5"></div>
            </label>
        </div>

        <!-- Gateway URL -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
                Gateway URL <span class="text-red-500">*</span>
            </label>
            <div class="relative">
                <input type="url" name="wa_gateway_url" required
                       value="<?= esc($wa['gateway_url']) ?>"
                       placeholder="https://api.fonnte.com/send"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none pr-24">
                <div class="absolute right-2 top-1/2 -translate-y-1/2 flex gap-1">
                    <?php foreach ([
                        ['label' => 'Fonnte',   'url' => 'https://api.fonnte.com/send'],
                        ['label' => 'Lokal',    'url' => 'http://localhost:3000/send'],
                    ] as $preset): ?>
                    <button type="button" onclick="setUrl('<?= $preset['url'] ?>')"
                            class="text-xs bg-blue-50 hover:bg-blue-100 text-blue-600 px-2 py-0.5 rounded font-medium">
                        <?= $preset['label'] ?>
                    </button>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Token / API Key -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
                Token / API Key <span class="text-red-500">*</span>
            </label>
            <div class="relative">
                <input type="password" name="wa_token" id="wa_token" required
                       value="<?= esc($wa['token']) ?>"
                       placeholder="Token dari Fonnte / API Key"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none pr-20">
                <button type="button" onclick="toggleToken()"
                        class="absolute right-2 top-1/2 -translate-y-1/2 text-xs text-blue-600 hover:text-blue-800 font-medium">
                    Lihat
                </button>
            </div>
            <p class="text-xs text-gray-400 mt-1">
                Untuk Fonnte: dapatkan token di
                <a href="https://app.fonnte.com" target="_blank" class="text-blue-500 hover:underline">app.fonnte.com</a>
                → Devices → Token
            </p>
        </div>

        <!-- Nomor Admin -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
                Nomor Admin (penerima notifikasi) <span class="text-red-500">*</span>
            </label>
            <textarea name="wa_admin_numbers" rows="2" required
                      placeholder="628123456789,628987654321&#10;(pisah koma, format internasional tanpa +)"
                      class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none resize-none"><?= esc($wa['admin_numbers']) ?></textarea>
            <p class="text-xs text-gray-400 mt-1">
                Format: <code class="bg-gray-100 px-1 rounded">628123456789</code> atau
                <code class="bg-gray-100 px-1 rounded">0812xxx</code> (otomatis dikonversi)
            </p>
        </div>

        <!-- Curl Timeout -->
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Timeout (detik)</label>
                <input type="number" name="wa_curl_timeout" min="3" max="30"
                       value="<?= $wa['curl_timeout'] ?>"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none">
                <p class="text-xs text-gray-400 mt-1">Rekomendasi: 5 detik</p>
            </div>
        </div>

        <div class="flex gap-3 pt-2">
            <button type="submit"
                    class="bg-green-600 hover:bg-green-700 text-white font-semibold px-6 py-2.5 rounded-xl text-sm">
                💾 Simpan Konfigurasi
            </button>
        </div>
    </form>

    <!-- Panel Test Kirim -->
    <div class="bg-white border rounded-xl p-6 shadow-sm">
        <h2 class="text-sm font-bold text-gray-700 uppercase tracking-wide pb-2 border-b flex items-center gap-2 mb-4">
            <span>🧪</span> Test Kirim Pesan
        </h2>

        <?php if ($msg = session()->getFlashdata('wa_test_success')): ?>
        <div class="mb-3 px-4 py-3 bg-green-100 text-green-800 rounded-xl text-sm">✅ <?= $msg ?></div>
        <?php endif; ?>
        <?php if ($msg = session()->getFlashdata('wa_test_error')): ?>
        <div class="mb-3 px-4 py-3 bg-red-100 text-red-800 rounded-xl text-sm">❌ <?= $msg ?></div>
        <?php endif; ?>

        <form action="<?= base_url('admin/settings/wa-test') ?>" method="POST">
            <?= csrf_field() ?>
            <div class="flex gap-3 items-end">
                <div class="flex-1">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Kirim Test ke Nomor</label>
                    <input type="text" name="test_number" required
                           placeholder="628123456789 atau 0812xxx"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none">
                </div>
                <button type="submit"
                        class="bg-blue-600 hover:bg-blue-700 text-white font-medium px-5 py-2.5 rounded-xl text-sm whitespace-nowrap">
                    📤 Kirim Test
                </button>
            </div>
            <p class="text-xs text-gray-400 mt-2">
                ⚠️ Pastikan konfigurasi token & URL sudah disimpan sebelum test.
            </p>
        </form>

        <!-- Panduan Fonnte -->
        <div class="mt-5 bg-blue-50 border border-blue-100 rounded-xl p-4">
            <p class="text-sm font-bold text-blue-700 mb-2">📖 Panduan Setup Fonnte</p>
            <ol class="text-xs text-blue-800 space-y-1 list-decimal list-inside">
                <li>Daftar/Login di <a href="https://app.fonnte.com" target="_blank" class="underline">app.fonnte.com</a></li>
                <li>Tambah Device → Scan QR Code dari HP</li>
                <li>Salin <strong>Token</strong> dari halaman Devices</li>
                <li>Isi Gateway URL: <code class="bg-blue-100 px-1 rounded">https://api.fonnte.com/send</code></li>
                <li>Paste token di field Token/API Key di atas</li>
                <li>Klik <strong>Test Kirim</strong> untuk verifikasi</li>
            </ol>
        </div>
    </div>
</div>

<!-- Scripts -->
<script>
function setUrl(url) {
    document.querySelector('[name="wa_gateway_url"]').value = url;
}
function toggleToken() {
    const inp = document.getElementById('wa_token');
    inp.type = inp.type === 'password' ? 'text' : 'password';
}
</script>

<?= $this->endSection() ?>
