<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($asset['name']) ?> — SiAset QR Info</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <meta name="theme-color" content="#1e40af">
    <style>
        @media print {
            .no-print { display: none !important; }
            body { background: white; }
        }
        .tab-btn.active { @apply bg-white text-blue-700 shadow-sm; }
    </style>
</head>
<body class="bg-gray-100 min-h-screen">

<?php
$rp = fn($n) => 'Rp ' . number_format((float)$n, 0, ',', '.');

$stMap = [
    'tersedia'        => ['bg-green-100 text-green-700',  '✅ Tersedia'],
    'dipinjam'        => ['bg-yellow-100 text-yellow-700','🔄 Dipinjam'],
    'dalam_perbaikan' => ['bg-orange-100 text-orange-700','🔧 Dalam Perbaikan'],
    'dihapus'         => ['bg-red-100 text-red-700',      '❌ Dihapus'],
];
$condLabel = ['baik' => 'Baik', 'rusak_ringan' => 'Rusak Ringan', 'rusak_berat' => 'Rusak Berat'];
$priorityColor = [
    'kritis' => 'bg-red-600 text-white',
    'tinggi' => 'bg-orange-500 text-white',
    'sedang' => 'bg-yellow-400 text-gray-900',
    'rendah' => 'bg-green-400 text-gray-900',
];
$woStatusMap = [
    'open'         => 'bg-red-100 text-red-700',
    'in_progress'  => 'bg-blue-100 text-blue-700',
    'waiting_part' => 'bg-yellow-100 text-yellow-700',
    'done'         => 'bg-green-100 text-green-700',
    'cancelled'    => 'bg-gray-100 text-gray-500',
];
[$stClass, $stLabel] = $stMap[$asset['status']] ?? ['bg-gray-100 text-gray-600', $asset['status']];
?>

<!-- ── Top Bar ──────────────────────────────────────────────── -->
<div class="bg-blue-700 text-white px-4 py-3 flex items-center justify-between shadow-md sticky top-0 z-10">
    <div class="flex items-center gap-2">
        <span class="text-xl font-black">Si</span>
        <span class="text-xl font-bold text-blue-200">Aset</span>
        <span class="text-xs bg-blue-600 px-2 py-0.5 rounded-full ml-1">QR Scan</span>
    </div>
    <div class="text-xs text-blue-200"><?= date('d M Y H:i') ?></div>
</div>

<!-- ── Hero Card ────────────────────────────────────────────── -->
<div class="bg-gradient-to-br from-blue-700 to-blue-900 text-white px-4 pt-5 pb-8">
    <div class="flex gap-4 items-start">
        <!-- Foto Aset -->
        <div class="shrink-0">
            <?php if ($asset['photo']): ?>
            <img src="<?= base_url('uploads/assets/' . $asset['photo']) ?>"
                 alt="Foto" class="w-20 h-20 object-cover rounded-xl border-2 border-blue-400 shadow-lg">
            <?php else: ?>
            <div class="w-20 h-20 bg-blue-600 rounded-xl border-2 border-blue-400 flex items-center justify-center text-4xl shadow-lg">📦</div>
            <?php endif; ?>
        </div>
        <!-- Info -->
        <div class="flex-1 min-w-0">
            <div class="flex items-center gap-2 flex-wrap mb-1">
                <span class="px-2 py-0.5 rounded-full text-xs font-semibold <?= $stClass ?>">
                    <?= $stLabel ?>
                </span>
                <?php if (!empty($asset['status_condition'])): ?>
                <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-blue-500 text-white">
                    <?= strtoupper($asset['status_condition']) ?>
                </span>
                <?php endif; ?>
            </div>
            <h1 class="text-lg font-bold leading-snug"><?= esc($asset['name']) ?></h1>
            <code class="text-blue-200 text-sm font-mono"><?= esc($asset['asset_code']) ?></code>
        </div>
    </div>
</div>

<!-- ── Main Content (card overlap) ─────────────────────────── -->
<div class="px-4 -mt-4 space-y-4 pb-8">

    <!-- Card 1: Identitas Aset ──────────────────────────────── -->
    <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
        <div class="px-4 py-3 bg-gray-50 border-b flex items-center gap-2">
            <span class="text-blue-600">🏷️</span>
            <h2 class="text-sm font-bold text-gray-700">Identitas Aset</h2>
        </div>
        <div class="p-4 grid grid-cols-2 gap-3 text-sm">
            <div>
                <p class="text-xs text-gray-400">Brand / Merek</p>
                <p class="font-semibold text-gray-800"><?= esc($asset['brand'] ?? '-') ?></p>
            </div>
            <div>
                <p class="text-xs text-gray-400">Type / Tipe</p>
                <p class="font-semibold text-gray-800"><?= esc($asset['type'] ?? '-') ?></p>
            </div>
            <div>
                <p class="text-xs text-gray-400">Model</p>
                <p class="font-semibold text-gray-800"><?= esc($asset['model'] ?? '-') ?></p>
            </div>
            <div>
                <p class="text-xs text-gray-400">Serial Number</p>
                <p class="font-semibold text-gray-800 font-mono text-xs break-all"><?= esc($asset['serial_number'] ?? '-') ?></p>
            </div>
            <div>
                <p class="text-xs text-gray-400">Kategori</p>
                <p class="font-semibold text-gray-800"><?= esc($asset['category'] ?? '-') ?></p>
            </div>
            <div>
                <p class="text-xs text-gray-400">Kondisi</p>
                <p class="font-semibold text-gray-800"><?= $condLabel[$asset['condition']] ?? '-' ?></p>
            </div>
            <div>
                <p class="text-xs text-gray-400">Departemen</p>
                <p class="font-semibold text-gray-800"><?= esc($asset['department_name'] ?? '-') ?></p>
            </div>
            <div>
                <p class="text-xs text-gray-400">Lokasi</p>
                <p class="font-semibold text-gray-800">
                    <?= esc($asset['location_name'] ?? '-') ?>
                    <?= $asset['building'] ? '<span class="text-gray-400 font-normal text-xs"> · ' . esc($asset['building']) . '</span>' : '' ?>
                </p>
            </div>
            <div>
                <p class="text-xs text-gray-400">Quantity</p>
                <p class="font-semibold text-gray-800">
                    <?= number_format($asset['quantity'] ?? 1) ?>
                    <span class="text-gray-400 font-normal text-xs"><?= esc($asset['unit'] ?? 'unit') ?></span>
                </p>
            </div>
            <div>
                <p class="text-xs text-gray-400">Umur Aset</p>
                <p class="font-semibold text-gray-800"><?= $age['label'] ?: '-' ?></p>
            </div>
        </div>
    </div>

    <!-- Card 2: Garansi ─────────────────────────────────────── -->
    <?php
    $wStatus = $warranty_status;
    $cardBg  = $wStatus['expired'] ? 'bg-red-50 border-red-200' : ($wStatus['status'] === 'expiring' ? 'bg-orange-50 border-orange-200' : 'bg-green-50 border-green-200');
    ?>
    <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
        <div class="px-4 py-3 bg-gray-50 border-b flex items-center gap-2">
            <span>🛡️</span>
            <h2 class="text-sm font-bold text-gray-700">Garansi</h2>
        </div>
        <div class="p-4">
            <?php if ($wStatus['status'] === 'none'): ?>
            <p class="text-gray-400 text-sm text-center py-2">Tidak ada data garansi</p>
            <?php else: ?>
            <div class="border rounded-xl p-4 <?= $cardBg ?>">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs text-gray-500 mb-0.5">Berlaku hingga</p>
                        <p class="text-xl font-bold text-gray-800"><?= $wStatus['date'] ?></p>
                        <p class="text-sm font-semibold <?= $wStatus['color'] ?> mt-1">
                            <?= $wStatus['label'] ?>
                            <?php if (!empty($wStatus['detail'])): ?>
                            <span class="font-normal">(<?= $wStatus['detail'] ?>)</span>
                            <?php endif; ?>
                        </p>
                    </div>
                    <div class="text-5xl">
                        <?php if ($wStatus['expired']): ?>🔴
                        <?php elseif ($wStatus['status'] === 'expiring'): ?>🟡
                        <?php else: ?>🟢<?php endif; ?>
                    </div>
                </div>
                <?php if (!$wStatus['expired'] && !empty($wStatus['days_left'])): ?>
                <div class="mt-3">
                    <?php
                    $pct = min(100, max(0, 100 - round($wStatus['days_left'] / 730 * 100)));
                    ?>
                    <div class="flex justify-between text-xs text-gray-500 mb-1">
                        <span>Sisa garansi</span>
                        <span><?= $wStatus['days_left'] ?> hari</span>
                    </div>
                    <div class="h-2 bg-gray-200 rounded-full overflow-hidden">
                        <div class="h-full rounded-full <?= $pct > 70 ? 'bg-red-500' : ($pct > 40 ? 'bg-orange-400' : 'bg-green-500') ?>"
                             style="width:<?= $pct ?>%"></div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Card 3: Riwayat Perbaikan ───────────────────────────── -->
    <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
        <div class="px-4 py-3 bg-gray-50 border-b flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span>🔧</span>
                <h2 class="text-sm font-bold text-gray-700">Riwayat Perbaikan (Corrective)</h2>
            </div>
            <span class="text-xs font-bold text-orange-600 bg-orange-100 px-2 py-0.5 rounded-full">
                <?= count($repair_history) ?> log
            </span>
        </div>
        <?php if (empty($repair_history)): ?>
        <div class="text-center py-8 text-gray-400">
            <div class="text-3xl mb-2">✅</div>
            <p class="text-sm">Belum ada riwayat perbaikan</p>
        </div>
        <?php else: ?>
        <div class="divide-y">
            <?php foreach ($repair_history as $log):
                $isFinish = $log['action'] === 'perbaikan_selesai';
            ?>
            <div class="p-4">
                <div class="flex items-start justify-between gap-3">
                    <div class="flex-1 min-w-0">
                        <!-- Action badge -->
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold
                            <?= $isFinish ? 'bg-green-100 text-green-700' : 'bg-orange-100 text-orange-700' ?>">
                            <?= $isFinish ? '✅ Selesai Diperbaiki' : '🔧 Perbaikan Dimulai' ?>
                        </span>

                        <!-- WO Info (jika ada) -->
                        <?php if (!empty($log['wo_code'])): ?>
                        <div class="mt-1.5">
                            <span class="font-mono text-xs bg-gray-100 px-1.5 py-0.5 rounded text-gray-600">
                                <?= esc($log['wo_code']) ?>
                            </span>
                        </div>
                        <?php endif; ?>

                        <!-- Masalah / Deskripsi -->
                        <?php if (!empty($log['problem_desc'])): ?>
                        <div class="mt-2">
                            <p class="text-xs text-gray-400 mb-0.5">Masalah</p>
                            <p class="text-sm text-gray-700 font-medium"><?= esc($log['problem_desc']) ?></p>
                        </div>
                        <?php endif; ?>

                        <!-- Tindakan yang dilakukan -->
                        <?php if (!empty($log['action_taken'])): ?>
                        <div class="mt-2 bg-green-50 rounded-lg p-2.5">
                            <p class="text-xs text-green-600 font-medium mb-0.5">Tindakan / Penggantian</p>
                            <p class="text-sm text-gray-700"><?= esc($log['action_taken']) ?></p>
                        </div>
                        <?php elseif (!empty($log['description'])): ?>
                        <div class="mt-2">
                            <p class="text-xs text-gray-400 mb-0.5">Keterangan</p>
                            <p class="text-sm text-gray-700"><?= esc($log['description']) ?></p>
                        </div>
                        <?php endif; ?>

                        <!-- Biaya -->
                        <?php if (!empty($log['cost'])): ?>
                        <div class="mt-2 flex items-center gap-1.5">
                            <span class="text-xs text-gray-400">Biaya:</span>
                            <span class="text-sm font-semibold text-orange-600"><?= $rp($log['cost']) ?></span>
                        </div>
                        <?php endif; ?>

                        <!-- Oleh siapa -->
                        <?php if (!empty($log['user_name'])): ?>
                        <p class="text-xs text-gray-400 mt-1">oleh <?= esc($log['user_name']) ?></p>
                        <?php endif; ?>
                    </div>
                    <!-- Tanggal -->
                    <div class="text-right shrink-0">
                        <p class="text-xs text-gray-500"><?= date('d M Y', strtotime($log['created_at'])) ?></p>
                        <p class="text-xs text-gray-400"><?= date('H:i', strtotime($log['created_at'])) ?></p>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>

    <!-- Card 4: Riwayat Preventive Maintenance ──────────────── -->
    <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
        <div class="px-4 py-3 bg-gray-50 border-b flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span>🛠️</span>
                <h2 class="text-sm font-bold text-gray-700">Riwayat Preventive Maintenance</h2>
            </div>
            <span class="text-xs font-bold text-blue-600 bg-blue-100 px-2 py-0.5 rounded-full">
                <?= count($pm_history) ?> PM
            </span>
        </div>

        <?php if (!empty($asset['pm_interval_days'])): ?>
        <div class="px-4 py-2 bg-blue-50 border-b">
            <p class="text-xs text-blue-700 font-medium">
                📅 Jadwal PM: Setiap
                <?php
                $d = (int) $asset['pm_interval_days'];
                if ($d >= 365)    echo round($d/365, 1) . ' Tahun';
                elseif ($d >= 30) echo round($d/30) . ' Bulan';
                elseif ($d >= 7)  echo round($d/7) . ' Minggu';
                else              echo $d . ' Hari';
                ?> (<?= $asset['pm_interval_days'] ?> hari)
            </p>
        </div>
        <?php endif; ?>

        <?php if (empty($pm_history)): ?>
        <div class="text-center py-8 text-gray-400">
            <div class="text-3xl mb-2">📋</div>
            <p class="text-sm">Belum ada riwayat preventive maintenance</p>
        </div>
        <?php else: ?>
        <div class="divide-y">
            <?php foreach ($pm_history as $pm):
                $isDone = $pm['status'] === 'done';
            ?>
            <div class="p-4">
                <div class="flex items-start justify-between gap-3">
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="font-mono text-xs bg-gray-100 px-1.5 py-0.5 rounded text-gray-600">
                                <?= esc($pm['wo_code']) ?>
                            </span>
                            <span class="px-2 py-0.5 rounded-full text-xs font-semibold
                                <?= $isDone ? 'bg-green-100 text-green-700' : ($woStatusMap[$pm['status']] ?? 'bg-gray-100 text-gray-600') ?>">
                                <?= ucwords(str_replace('_', ' ', $pm['status'])) ?>
                            </span>
                        </div>

                        <!-- Tindakan PM -->
                        <?php if (!empty($pm['action_taken'])): ?>
                        <div class="mt-2 bg-blue-50 rounded-lg p-2.5">
                            <p class="text-xs text-blue-600 font-medium mb-0.5">Yang Dilakukan</p>
                            <p class="text-sm text-gray-700"><?= esc($pm['action_taken']) ?></p>
                        </div>
                        <?php endif; ?>

                        <!-- Teknisi -->
                        <?php if (!empty($pm['assigned_to_name'])): ?>
                        <p class="text-xs text-gray-400 mt-1.5">
                            Teknisi: <span class="font-medium text-gray-600"><?= esc($pm['assigned_to_name']) ?></span>
                        </p>
                        <?php endif; ?>

                        <!-- Biaya -->
                        <?php if (!empty($pm['cost'])): ?>
                        <div class="flex items-center gap-1.5 mt-1">
                            <span class="text-xs text-gray-400">Biaya:</span>
                            <span class="text-sm font-semibold text-orange-600"><?= $rp($pm['cost']) ?></span>
                        </div>
                        <?php endif; ?>
                    </div>
                    <!-- Tanggal -->
                    <div class="text-right shrink-0">
                        <?php if ($pm['finish_date']): ?>
                        <p class="text-xs font-semibold text-gray-700"><?= date('d M Y', strtotime($pm['finish_date'])) ?></p>
                        <p class="text-xs text-gray-400">Selesai</p>
                        <?php elseif ($pm['scheduled_date']): ?>
                        <p class="text-xs font-semibold text-gray-700"><?= date('d M Y', strtotime($pm['scheduled_date'])) ?></p>
                        <p class="text-xs text-gray-400">Dijadwalkan</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>

    <!-- Card 5: QR Code (untuk share/print) ─────────────────── -->
    <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
        <div class="px-4 py-3 bg-gray-50 border-b flex items-center gap-2">
            <span>🔲</span>
            <h2 class="text-sm font-bold text-gray-700">QR Code Aset</h2>
        </div>
        <div class="p-4 flex flex-col items-center">
            <?php if ($qr_b64): ?>
            <img src="<?= $qr_b64 ?>" alt="QR" class="w-44 h-44 rounded-xl border shadow-sm mb-3">
            <?php endif; ?>
            <code class="font-mono text-sm text-gray-700 font-bold mb-1"><?= esc($asset['asset_code']) ?></code>
            <p class="text-xs text-gray-400 mb-4 text-center"><?= esc($asset['name']) ?></p>
            <?php if (session()->get('id')): ?>
                <div class="mb-4 w-full no-print">
                    <a href="<?= base_url('admin/checklist/new/' . $asset['asset_code']) ?>"
                       class="block text-center text-sm bg-orange-600 hover:bg-orange-700 text-white py-3 rounded-xl font-medium transition-colors">
                        📝 Isi Checklist Pemeliharaan
                    </a>
                </div>
            <?php endif; ?>
            <div class="flex gap-3 w-full no-print">
                <a href="<?= base_url('admin/qr/' . $asset['id'] . '/label') ?>" target="_blank"
                   class="flex-1 text-center text-sm bg-gray-800 hover:bg-gray-900 text-white py-2.5 rounded-xl font-medium transition-colors">
                    🖨️ Cetak Label
                </a>
                <a href="<?= base_url('admin/qr/' . $asset['id'] . '/download') ?>"
                   class="flex-1 text-center text-sm border border-gray-300 hover:bg-gray-50 text-gray-700 py-2.5 rounded-xl font-medium transition-colors">
                    ⬇ Download
                </a>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <div class="text-center text-xs text-gray-400 py-2">
        SiAset — Sistem Manajemen Aset · <?= date('Y') ?>
    </div>
</div>

</body>
</html>
