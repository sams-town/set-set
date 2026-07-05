<?= $this->extend('inventory/_layout') ?>
<?= $this->section('content') ?>
<?php
$rp = fn($n) => 'Rp ' . number_format((float)$n, 0, ',', '.');
$statusColor = [
    'open'         => 'bg-red-100 text-red-700',
    'in_progress'  => 'bg-blue-100 text-blue-700',
    'waiting_part' => 'bg-yellow-100 text-yellow-700',
    'done'         => 'bg-green-100 text-green-700',
    'cancelled'    => 'bg-gray-100 text-gray-500',
];
$priorityColor = [
    'kritis' => 'bg-red-100 text-red-700',
    'tinggi' => 'bg-orange-100 text-orange-700',
    'sedang' => 'bg-yellow-100 text-yellow-700',
    'rendah' => 'bg-green-100 text-green-700',
];
$condLabel = ['baik' => 'Baik', 'rusak_ringan' => 'Rusak Ringan', 'rusak_berat' => 'Rusak Berat'];
?>

<!-- Breadcrumb -->
<div class="flex items-center gap-2 text-sm text-gray-500 mb-4">
    <a href="<?= base_url('admin/cm') ?>" class="hover:text-blue-600">Corrective Maintenance</a>
    <span>›</span>
    <span class="font-medium text-gray-800"><?= esc($asset['name']) ?></span>
</div>

<!-- Header -->
<div class="flex items-start justify-between mb-5 gap-4 flex-wrap">
    <div class="flex items-start gap-4">
        <?php if ($asset['photo']): ?>
        <img src="<?= base_url('uploads/assets/'.$asset['photo']) ?>"
             alt="Foto" class="w-16 h-16 object-cover rounded-xl border shadow-sm shrink-0">
        <?php else: ?>
        <div class="w-16 h-16 bg-gray-100 rounded-xl border flex items-center justify-center text-3xl shrink-0">📦</div>
        <?php endif; ?>
        <div>
            <h1 class="text-xl font-bold text-gray-800"><?= esc($asset['name']) ?></h1>
            <div class="flex gap-2 mt-1 flex-wrap">
                <code class="text-sm text-gray-500 font-mono"><?= esc($asset['asset_code']) ?></code>
                <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-600">
                    <?= esc($asset['category'] ?? '-') ?>
                </span>
                <?php if ($asset['brand']): ?>
                <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-blue-100 text-blue-700">
                    <?= esc($asset['brand']) ?>
                </span>
                <?php endif; ?>
                <span class="px-2 py-0.5 rounded-full text-xs font-semibold
                    <?= $asset['condition'] === 'baik' ? 'bg-green-100 text-green-700' : ($asset['condition'] === 'rusak_berat' ? 'bg-red-100 text-red-700' : 'bg-yellow-100 text-yellow-700') ?>">
                    <?= $condLabel[$asset['condition']] ?? '-' ?>
                </span>
            </div>
            <p class="text-xs text-gray-400 mt-1">
                <?= esc($asset['department_name'] ?? '-') ?>
                <?= $asset['location_name'] ? ' · ' . esc($asset['location_name']) : '' ?>
                · Umur: <strong><?= $age['label'] ?: '-' ?></strong>
            </p>
        </div>
    </div>
    <div class="flex gap-2">
        <a href="<?= base_url('admin/inventory/'.$asset['id']) ?>"
           class="inline-flex items-center gap-1.5 border border-gray-300 text-gray-700 hover:bg-gray-50 text-sm font-medium px-3 py-2 rounded-lg">
            🗃️ Lihat Inventory
        </a>
        <a href="<?= base_url('admin/work-orders/new?asset_id='.$asset['id'].'&type=corrective') ?>"
           class="inline-flex items-center gap-1.5 bg-red-600 hover:bg-red-700 text-white text-sm font-medium px-3 py-2 rounded-lg">
            + WO Baru
        </a>
    </div>
</div>

<!-- ══ KPI CARDS PER ASET ════════════════════════════════════ -->
<div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-5">

    <!-- Total Breakdown -->
    <div class="border-l-4 border-red-500 bg-red-50 rounded-xl p-4 shadow-sm">
        <div class="text-2xl font-bold text-red-700"><?= count($history) ?></div>
        <div class="text-xs font-semibold text-gray-600 mt-1">Total Breakdown</div>
        <?php if (!empty($history)): ?>
        <div class="text-xs text-gray-400 mt-0.5">
            Terakhir: <?= date('d M Y', strtotime($history[0]['created_at'])) ?>
        </div>
        <?php endif; ?>
    </div>

    <!-- Total Biaya -->
    <div class="border-l-4 border-orange-400 bg-orange-50 rounded-xl p-4 shadow-sm">
        <div class="text-lg font-bold text-orange-700 leading-tight">
            <?= $rp(array_sum(array_column($history, 'cost'))) ?>
        </div>
        <div class="text-xs font-semibold text-gray-600 mt-1">Total Biaya</div>
        <div class="text-xs text-gray-400 mt-0.5">Semua riwayat</div>
    </div>

    <!-- Total Downtime -->
    <div class="border-l-4 border-yellow-400 bg-yellow-50 rounded-xl p-4 shadow-sm">
        <div class="text-2xl font-bold text-yellow-700">
            <?= $total_downtime >= 24 ? round($total_downtime/24,1).'h' : round($total_downtime,1).'j' ?>
        </div>
        <div class="text-xs font-semibold text-gray-600 mt-1">Total Downtime</div>
        <div class="text-xs text-gray-400 mt-0.5">
            <?= $total_downtime >= 24 ? round($total_downtime,1).' jam total' : 'estimasi' ?>
        </div>
    </div>

    <!-- Repeat Breakdown -->
    <div class="border-l-4 <?= count($history) >= 3 ? 'border-red-500 bg-red-50' : 'border-green-400 bg-green-50' ?> rounded-xl p-4 shadow-sm">
        <?php if ($repeat_info): ?>
        <div class="text-2xl font-bold <?= count($history) >= 3 ? 'text-red-700' : 'text-green-700' ?>">
            ~<?= $repeat_info['avg_interval'] ?>h
        </div>
        <div class="text-xs font-semibold text-gray-600 mt-1">Avg Interval Rusak</div>
        <div class="text-xs text-gray-400 mt-0.5"><?= count($history) ?>× breakdown tercatat</div>
        <?php else: ?>
        <div class="text-2xl font-bold text-green-700">✅</div>
        <div class="text-xs font-semibold text-gray-600 mt-1">Tidak Repeat</div>
        <div class="text-xs text-gray-400 mt-0.5">1× breakdown</div>
        <?php endif; ?>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

    <!-- ══ KOLOM KIRI (2/3) ══════════════════════════════════════ -->
    <div class="lg:col-span-2 space-y-5">

        <!-- Trend Biaya Chart -->
        <div class="bg-white border rounded-xl p-4 shadow-sm">
            <h2 class="text-sm font-bold text-gray-700 mb-3">📈 Trend Biaya Perbaikan</h2>
            <canvas id="chartAssetTrend" style="max-height:160px;"></canvas>
        </div>

        <!-- Damage Type Frequency -->
        <?php if (!empty($dmg_frequency)): ?>
        <div class="bg-white border rounded-xl p-4 shadow-sm">
            <h2 class="text-sm font-bold text-gray-700 mb-3">🔍 Frekuensi Jenis Kerusakan</h2>
            <?php
            $maxDmg = max(array_values($dmg_frequency));
            $dmgColors = ['#ef4444','#f97316','#eab308','#3b82f6','#8b5cf6','#6b7280'];
            $di = 0;
            foreach ($dmg_frequency as $type => $count):
                $pct = round($count / max(1, count($history)) * 100);
            ?>
            <div class="mb-3">
                <div class="flex items-center justify-between text-sm mb-1">
                    <div class="flex items-center gap-2">
                        <span class="w-3 h-3 rounded-full shrink-0"
                              style="background:<?= $dmgColors[$di % count($dmgColors)] ?>"></span>
                        <span class="text-gray-700 font-medium"><?= esc($type) ?></span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-xs text-gray-400"><?= $pct ?>%</span>
                        <span class="font-bold text-gray-800 text-sm w-6 text-right"><?= $count ?>×</span>
                    </div>
                </div>
                <div class="h-2 bg-gray-100 rounded-full overflow-hidden">
                    <div class="h-full rounded-full transition-all"
                         style="width:<?= round($count/$maxDmg*100) ?>%;background:<?= $dmgColors[$di % count($dmgColors)] ?>"></div>
                </div>
            </div>
            <?php $di++; endforeach; ?>
        </div>
        <?php endif; ?>

        <!-- Riwayat WO Lengkap -->
        <div class="bg-white border rounded-xl shadow-sm overflow-hidden">
            <div class="px-4 py-3 bg-gray-50 border-b flex items-center justify-between">
                <h2 class="text-sm font-bold text-gray-700">📋 Riwayat Corrective Lengkap</h2>
                <span class="text-xs text-gray-400"><?= count($history) ?> WO</span>
            </div>
            <?php if (empty($history)): ?>
            <div class="text-center py-8 text-gray-400 text-sm">Belum ada riwayat kerusakan</div>
            <?php else: ?>
            <div class="divide-y">
                <?php foreach ($history as $i => $h):
                    $hasDowntime = false;
                    $dtHours = 0;
                    if ($h['start_date'] && $h['finish_date']) {
                        $dtHours = max(0, round((strtotime($h['finish_date'].' 23:59:59') - strtotime($h['start_date'])) / 3600, 1));
                        $hasDowntime = $dtHours > 0;
                    } elseif ($h['repair_time']) {
                        $dtHours = round($h['repair_time'] / 60, 1);
                        $hasDowntime = true;
                    }
                ?>
                <div class="p-4 <?= $h['status'] !== 'done' ? 'bg-yellow-50/30' : '' ?>">
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex items-start gap-3 flex-1 min-w-0">
                            <!-- Nomor urut -->
                            <div class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold shrink-0 mt-0.5
                                        <?= $i < 3 ? 'bg-red-100 text-red-700' : 'bg-gray-100 text-gray-500' ?>">
                                <?= $i + 1 ?>
                            </div>
                            <div class="flex-1 min-w-0">
                                <!-- WO Header -->
                                <div class="flex items-center gap-2 flex-wrap">
                                    <a href="<?= base_url('admin/work-orders/'.$h['id']) ?>"
                                       class="font-mono text-blue-600 hover:underline font-semibold text-sm">
                                        <?= esc($h['wo_code']) ?>
                                    </a>
                                    <span class="px-2 py-0.5 rounded-full text-xs font-semibold <?= $statusColor[$h['status']] ?? 'bg-gray-100 text-gray-500' ?>">
                                        <?= ucwords(str_replace('_',' ',$h['status'])) ?>
                                    </span>
                                    <span class="px-2 py-0.5 rounded-full text-xs font-semibold <?= $priorityColor[$h['priority']] ?? 'bg-gray-100 text-gray-500' ?>">
                                        <?= ucfirst($h['priority']) ?>
                                    </span>
                                    <?php if ($h['damage_type']): ?>
                                    <span class="px-1.5 py-0.5 bg-red-50 text-red-600 rounded text-xs">
                                        <?= esc($h['damage_type']) ?>
                                    </span>
                                    <?php endif; ?>
                                </div>

                                <!-- Deskripsi Masalah -->
                                <div class="mt-2 bg-red-50 rounded-lg px-3 py-2">
                                    <p class="text-xs text-red-600 font-medium mb-0.5">Masalah</p>
                                    <p class="text-sm text-gray-800"><?= esc($h['problem_desc']) ?></p>
                                </div>

                                <!-- Action Taken -->
                                <?php if (!empty($h['action_taken'])): ?>
                                <div class="mt-2 bg-green-50 rounded-lg px-3 py-2">
                                    <p class="text-xs text-green-600 font-medium mb-0.5">Tindakan / Perbaikan</p>
                                    <p class="text-sm text-gray-800"><?= esc($h['action_taken']) ?></p>
                                </div>
                                <?php endif; ?>

                                <!-- Material -->
                                <?php if (!empty($h['material_used'])): ?>
                                <div class="mt-1.5 text-xs text-gray-500">
                                    <span class="font-medium">Material:</span> <?= esc($h['material_used']) ?>
                                </div>
                                <?php endif; ?>

                                <!-- Meta info -->
                                <div class="flex gap-4 mt-2 text-xs text-gray-400 flex-wrap">
                                    <?php if ($h['technician_name']): ?>
                                    <span>🔧 <?= esc($h['technician_name']) ?></span>
                                    <?php endif; ?>
                                    <?php if ($hasDowntime): ?>
                                    <span>⏱️ Downtime: <?= $dtHours >= 24 ? round($dtHours/24,1).' hari' : $dtHours.' jam' ?></span>
                                    <?php endif; ?>
                                    <?php if ($h['cost'] > 0): ?>
                                    <span class="text-orange-600 font-medium">💰 <?= $rp($h['cost']) ?></span>
                                    <?php endif; ?>
                                </div>

                                <!-- Foto Before/After -->
                                <?php if ($h['photo_before'] || $h['photo_after']): ?>
                                <div class="flex gap-2 mt-2">
                                    <?php if ($h['photo_before']): ?>
                                    <a href="<?= base_url('uploads/work_orders/'.$h['photo_before']) ?>" target="_blank"
                                       class="group relative block w-16 h-16 rounded-lg overflow-hidden border shadow-sm hover:shadow-md">
                                        <img src="<?= base_url('uploads/work_orders/'.$h['photo_before']) ?>"
                                             class="w-full h-full object-cover" alt="Before">
                                        <div class="absolute inset-0 bg-orange-900/40 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                                            <span class="text-white text-xs font-bold">Before</span>
                                        </div>
                                    </a>
                                    <?php endif; ?>
                                    <?php if ($h['photo_after']): ?>
                                    <a href="<?= base_url('uploads/work_orders/'.$h['photo_after']) ?>" target="_blank"
                                       class="group relative block w-16 h-16 rounded-lg overflow-hidden border shadow-sm hover:shadow-md">
                                        <img src="<?= base_url('uploads/work_orders/'.$h['photo_after']) ?>"
                                             class="w-full h-full object-cover" alt="After">
                                        <div class="absolute inset-0 bg-green-900/40 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                                            <span class="text-white text-xs font-bold">After</span>
                                        </div>
                                    </a>
                                    <?php endif; ?>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Tanggal -->
                        <div class="text-right shrink-0 text-xs text-gray-400">
                            <div class="font-medium text-gray-600"><?= date('d M Y', strtotime($h['created_at'])) ?></div>
                            <?php if ($h['finish_date']): ?>
                            <div class="mt-0.5">Selesai: <?= date('d M Y', strtotime($h['finish_date'])) ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- ══ KOLOM KANAN (1/3) ═════════════════════════════════════ -->
    <div class="space-y-4">

        <!-- Repeat Breakdown Alert -->
        <?php if ($repeat_info && $repeat_info['count'] >= 3): ?>
        <div class="bg-red-50 border border-red-200 rounded-xl p-4">
            <div class="flex items-center gap-2 mb-2">
                <span class="text-xl">⚠️</span>
                <h3 class="text-sm font-bold text-red-700">Repeat Breakdown Alert</h3>
            </div>
            <p class="text-sm text-red-600">
                Aset ini rusak <strong><?= $repeat_info['count'] ?>×</strong> dengan interval rata-rata
                <strong><?= $repeat_info['avg_interval'] ?> hari</strong>.
            </p>
            <p class="text-xs text-red-500 mt-1">Pertimbangkan penggantian atau overhaul menyeluruh.</p>
            <?php if ($repeat_info['total_cost'] > 0): ?>
            <div class="mt-2 pt-2 border-t border-red-200">
                <p class="text-xs text-red-600">Total biaya perbaikan: <strong><?= $rp($repeat_info['total_cost']) ?></strong></p>
            </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <!-- Info Aset -->
        <div class="bg-white border rounded-xl p-4 shadow-sm">
            <h2 class="text-sm font-bold text-gray-700 pb-2 border-b mb-3">🗃️ Info Aset</h2>
            <div class="space-y-2 text-sm">
                <div>
                    <p class="text-xs text-gray-400">Vendor</p>
                    <p class="font-semibold text-gray-800"><?= esc($asset['vendor_name'] ?? '-') ?></p>
                </div>
                <div>
                    <p class="text-xs text-gray-400">Tanggal Beli</p>
                    <p class="font-semibold text-gray-800">
                        <?= $asset['purchase_date'] ? date('d M Y', strtotime($asset['purchase_date'])) : '-' ?>
                    </p>
                </div>
                <div>
                    <p class="text-xs text-gray-400">Harga Beli</p>
                    <p class="font-semibold text-gray-800">
                        <?= $asset['purchase_price'] ? $rp($asset['purchase_price']) : '-' ?>
                    </p>
                </div>
                <div>
                    <p class="text-xs text-gray-400">Garansi</p>
                    <?php
                    $wExpired = $asset['warranty_expiry'] && strtotime($asset['warranty_expiry']) < time();
                    ?>
                    <p class="font-semibold <?= $wExpired ? 'text-red-600' : 'text-gray-800' ?>">
                        <?= $asset['warranty_expiry'] ? date('d M Y', strtotime($asset['warranty_expiry'])) . ($wExpired ? ' (Expired)' : '') : '-' ?>
                    </p>
                </div>
                <?php if ($asset['serial_number']): ?>
                <div>
                    <p class="text-xs text-gray-400">Serial Number</p>
                    <code class="text-xs text-gray-700"><?= esc($asset['serial_number']) ?></code>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Summary Biaya -->
        <?php if (!empty($history)): ?>
        <div class="bg-white border rounded-xl p-4 shadow-sm">
            <h2 class="text-sm font-bold text-gray-700 pb-2 border-b mb-3">💰 Ringkasan Biaya</h2>
            <?php
            $totalCost    = array_sum(array_column($history, 'cost'));
            $avgCost      = count($history) > 0 ? round($totalCost / count($history)) : 0;
            $maxCostWo    = array_reduce($history, fn($c, $h) => $h['cost'] > ($c['cost'] ?? 0) ? $h : $c, []);
            ?>
            <div class="space-y-2 text-sm">
                <div class="flex justify-between">
                    <span class="text-gray-500">Total biaya</span>
                    <span class="font-bold text-orange-600"><?= $rp($totalCost) ?></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Rata-rata/WO</span>
                    <span class="font-semibold text-gray-700"><?= $rp($avgCost) ?></span>
                </div>
                <?php if ($asset['purchase_price'] && $totalCost > 0): ?>
                <div class="flex justify-between">
                    <span class="text-gray-500">% dari harga beli</span>
                    <span class="font-semibold <?= $totalCost > $asset['purchase_price'] * 0.5 ? 'text-red-600' : 'text-gray-700' ?>">
                        <?= round($totalCost / $asset['purchase_price'] * 100, 1) ?>%
                    </span>
                </div>
                <?php if ($totalCost >= $asset['purchase_price'] * 0.5): ?>
                <div class="mt-2 p-2 bg-red-50 rounded-lg">
                    <p class="text-xs text-red-600 font-medium">⚠️ Biaya perbaikan sudah ≥50% harga beli. Pertimbangkan penggantian.</p>
                </div>
                <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
(function () {
    const trendData = <?= $chart_asset_trend ?>;
    const ctx = document.getElementById('chartAssetTrend');
    if (ctx && trendData.labels?.length) {
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: trendData.labels,
                datasets: [
                    { label: 'Biaya', data: trendData.data,
                      backgroundColor: 'rgba(239,68,68,0.6)', borderRadius: 3, order: 2 },
                    { label: 'WO', data: trendData.count, type: 'line',
                      borderColor: '#3b82f6', backgroundColor: 'transparent',
                      borderWidth: 2, pointRadius: 4, tension: 0.4, yAxisID: 'y2', order: 1 },
                ]
            },
            options: {
                plugins: { legend: { display: false } },
                scales: {
                    x:  { ticks: { font: { size: 10 } } },
                    y:  { ticks: { font: { size: 10 }, callback: v => 'Rp'+(v/1000000).toFixed(1)+'jt' }, beginAtZero: true },
                    y2: { position: 'right', ticks: { font: { size: 10 }, stepSize: 1 }, beginAtZero: true, grid: { display: false } },
                },
                responsive: true, maintainAspectRatio: false,
            }
        });
    }
})();
</script>

<?= $this->endSection() ?>
