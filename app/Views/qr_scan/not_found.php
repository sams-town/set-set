<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aset Tidak Ditemukan — SiAset</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-lg p-8 max-w-sm w-full text-center">
        <div class="text-6xl mb-4">🔍</div>
        <h1 class="text-xl font-bold text-gray-800 mb-2">Aset Tidak Ditemukan</h1>
        <p class="text-gray-500 text-sm mb-4">
            Kode <code class="bg-gray-100 px-2 py-0.5 rounded font-mono"><?= esc($asset_code) ?></code>
            tidak ditemukan dalam sistem.
        </p>
        <p class="text-xs text-gray-400 mb-6">
            QR Code mungkin sudah kadaluarsa atau aset telah dihapus dari sistem.
            Hubungi tim General Affairs untuk informasi lebih lanjut.
        </p>
        <a href="<?= base_url() ?>"
           class="inline-block bg-blue-600 hover:bg-blue-700 text-white px-6 py-2.5 rounded-xl text-sm font-medium transition-colors">
            Ke Beranda
        </a>
    </div>
</body>
</html>
