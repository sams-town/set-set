<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title><?= esc($title) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            .no-print { display: none !important; }
            body { margin: 0; }
        }
    </style>
</head>
<body class="bg-gray-100 p-6">

<!-- Tombol aksi -->
<div class="no-print flex gap-3 mb-6 justify-center">
    <button onclick="window.print()"
            class="bg-blue-600 text-white px-5 py-2 rounded-lg text-sm font-medium hover:bg-blue-700">
        🖨️ Cetak Label
    </button>
    <a href="<?= base_url('admin/inventory/' . $asset['id']) ?>"
       class="border border-gray-300 text-gray-700 px-5 py-2 rounded-lg text-sm font-medium hover:bg-gray-50">
        ← Kembali
    </a>
    <a href="<?= base_url('admin/qr/' . $asset['id'] . '/download') ?>"
       class="border border-green-500 text-green-700 px-5 py-2 rounded-lg text-sm font-medium hover:bg-green-50">
        ⬇ Download PNG
    </a>
</div>

<!-- Label kartu -->
<div class="flex justify-center">
    <div class="bg-white rounded-2xl shadow-lg p-5 w-64 border-2 border-gray-200 text-center">
        <!-- Logo / Brand -->
        <div class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-3">SiAset</div>

        <!-- QR Code -->
        <?php if ($qr_b64): ?>
        <img src="<?= $qr_b64 ?>" alt="QR Code" class="w-40 h-40 mx-auto mb-3 rounded-lg border">
        <?php else: ?>
        <div class="w-40 h-40 mx-auto mb-3 bg-gray-100 rounded-lg flex items-center justify-center text-gray-400 text-sm">
            QR Error
        </div>
        <?php endif; ?>

        <!-- Kode aset -->
        <div class="font-mono text-lg font-bold text-gray-900 mb-1">
            <?= esc($asset['asset_code']) ?>
        </div>

        <!-- Nama aset -->
        <div class="text-sm font-semibold text-gray-700 mb-2 leading-tight">
            <?= esc($asset['name']) ?>
        </div>

        <!-- Info tambahan -->
        <div class="text-xs text-gray-500 space-y-0.5">
            <?php if ($asset['category']): ?>
            <div><?= esc($asset['category']) ?></div>
            <?php endif; ?>
            <?php if ($asset['department_name']): ?>
            <div><?= esc($asset['department_name']) ?></div>
            <?php endif; ?>
            <?php if ($asset['location_name']): ?>
            <div>📍 <?= esc($asset['location_name']) ?></div>
            <?php endif; ?>
        </div>

        <!-- Status badge -->
        <div class="mt-3">
            <?php
            $stColor = [
                'tersedia'        => 'bg-green-100 text-green-700',
                'dipinjam'        => 'bg-yellow-100 text-yellow-700',
                'dalam_perbaikan' => 'bg-orange-100 text-orange-700',
            ];
            ?>
            <span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold
                         <?= $stColor[$asset['status']] ?? 'bg-gray-100 text-gray-600' ?>">
                <?= ucwords(str_replace('_', ' ', $asset['status'])) ?>
            </span>
        </div>

        <!-- URL untuk scan manual -->
        <div class="mt-3 pt-3 border-t text-xs text-gray-400 break-all">
            <?= base_url('qr/' . $asset['asset_code']) ?>
        </div>
    </div>
</div>

</body>
</html>
