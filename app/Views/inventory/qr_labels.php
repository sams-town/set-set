<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title><?= esc($title) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            .no-print { display: none !important; }
            body { margin: 0; background: white; }
        }
    </style>
</head>
<body class="bg-gray-100 p-6">

<div class="no-print flex gap-3 mb-6 justify-center">
    <button onclick="window.print()"
            class="bg-blue-600 text-white px-5 py-2 rounded-lg text-sm font-medium hover:bg-blue-700">
        🖨️ Cetak Semua Label (<?= count($assets) ?>)
    </button>
    <a href="<?= base_url('admin/inventory') ?>"
       class="border border-gray-300 text-gray-700 px-5 py-2 rounded-lg text-sm font-medium hover:bg-gray-50">
        ← Kembali
    </a>
</div>

<!-- Grid label -->
<div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4 max-w-5xl mx-auto">
    <?php foreach ($assets as $asset):
        $stColor = [
            'tersedia'        => 'bg-green-100 text-green-700',
            'dipinjam'        => 'bg-yellow-100 text-yellow-700',
            'dalam_perbaikan' => 'bg-orange-100 text-orange-700',
        ];
    ?>
    <div class="bg-white rounded-xl border-2 border-gray-200 p-3 text-center shadow-sm">
        <div class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-2">SiAset</div>

        <?php if ($asset['qr_b64']): ?>
        <img src="<?= $asset['qr_b64'] ?>" alt="QR" class="w-28 h-28 mx-auto mb-2 rounded border">
        <?php endif; ?>

        <div class="font-mono text-sm font-bold text-gray-900 mb-0.5"><?= esc($asset['asset_code']) ?></div>
        <div class="text-xs font-semibold text-gray-700 mb-1 leading-tight line-clamp-2"><?= esc($asset['name']) ?></div>
        <div class="text-xs text-gray-400"><?= esc($asset['department_name'] ?? '') ?></div>
        <span class="inline-flex mt-1 px-2 py-0.5 rounded-full text-xs font-medium
                     <?= $stColor[$asset['status']] ?? 'bg-gray-100 text-gray-600' ?>">
            <?= ucwords(str_replace('_', ' ', $asset['status'])) ?>
        </span>
    </div>
    <?php endforeach; ?>
</div>

</body>
</html>
