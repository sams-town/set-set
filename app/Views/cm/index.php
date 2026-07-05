<?= $this->extend('inventory/_layout') ?>
<?= $this->section('content') ?>
<?php
$rp  = fn($n) => 'Rp ' . number_format((float)$n, 0, ',', '.');
$pct = fn($n) => number_format((float)$n, 1) . '%';
$bs  = $biaya_summary;
$dt  = $downtime;

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
?>

<!-- Page Header -->
<div class="flex items-center justify-between mb-5">
    <div>
        <h1 class="text-xl font-bold text-gray-800">🔧 Corrective Maintenance</h1>
        <p class="text-sm text-gray-500 mt-0.5">Histori kerusakan & analisis basis pengambilan keputusan</p>
    </div>
    <a href="<?= base_url('admin/work-orders/new?type=corrective') ?>"
       class="inline-flex items-center gap-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium px-4 py-2 rounded-lg shadow-sm">
        + Buat WO Corrective
    </a>
</div>

<!-- ════════════════════════════════════════════════════════════════
     FILTER PERIODE
     ════════════════════════════════════════════════════════════════ -->
<div class="bg-white border rounded-xl px-4 py-3 mb-5 shadow-sm">
    <form method="GET" class="flex flex-wrap gap-3 items-end">
        <div>
            <label class="block text-xs text-gray-500 mb-1">Dari Tanggal</label>
            <input type="date" name="date_from" value="<?= esc($filters['date_from'] ?? '') ?>"
                   class="border border-gray-300 rounded-lg px-3 py-1.5 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none">
        </div>
        <div>
            <label class="block text-xs text-gray-500 mb-1">Sampai Tanggal</label>
            <input type="date" name="date_to" value="<?= esc($filters['date_to'] ?? '') ?>"
                   class="border border-gray-300 rounded-lg px-3 py-1.5 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none">
        </div>
        <div class="flex gap-1.5 mt-4">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white text-sm px-4 py-1.5 rounded-lg">Filter</button>
            <a href="<?= base_url('admin/cm') ?>" class="bg-gray-100 hover:bg-gray-200 text-gray-600 text-sm px-3 py-1.5 rounded-lg">Reset</a>
        </div>
        <?php foreach (['3bln' => '3 Bln', '6bln' => '6 Bln', 'ytd' => 'Tahun Ini'] as $k => $lbl):
            $qFrom = match($k) {
                '3bln' => date('Y-m-d', strtotime('-3 months')),
                '6bln' => date('Y-m-d', strtotime('-6 months')),
                'ytd'  => date('Y-01-01'),
            };
            $isActive = ($filters['date_from'] ?? '') === $qFrom;
        ?>
        <a href="?date_from=<?= $qFrom ?>&date_to=<?= date('Y-m-d') ?>"
           class="mt-4 px-3 py-1.5 rounded-lg text-xs font-medium border transition-colors
                  <?= $isActive ? 'bg-blue-600 text-white border-blue-600' : 'bg-gray-50 text-gray-600 border-gray-300 hover:bg-gray-100' ?>">
            <?= $lbl ?>
        </a>
        <?php endforeach; ?>
    </form>
</div>

<!-- ════════════════════════════════════════════════════════════════
     KPI CARDS — 5 ANALITIK UTAMA
     ════════════════════════════════════════════════════════════════ -->
<div class="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-5 gap-3 mb-5">

    <!-- 1. Total WO Corrective -->
    <div class="border-l-4 border-red-500 bg-red-50 rounded-xl p-4 shadow-sm">
        <div class="flex items-center justify-between mb-2">
            <span class="text-2xl">🔴</span>
            <span class="text-xs text-red-400 font-medium">Open: <?= $dash_stats['open_now'] ?></span>
        </div>
        <div class="text-2xl font-bold text-red-700"><?= number_format($bs['wo_total']) ?></div>
        <div class="text-xs font-semibold text-gray-600 mt-1">Total WO Corrective</div>
        <div class="text-xs text-gray-400">Bulan ini: <?= $bs['wo_bulan_ini'] ?></div>
    </div>

    <!-- 2. Biaya Perbaikan -->
    <div class="border-l-4 border-orange-500 bg-orange-50 rounded-xl p-4 shadow-sm">
        <div class="text-lg mb-1">💰</div>
        <div class="text-lg font-bold text-orange-700 leading-tight"><?= $rp($bs['bulan_ini']) ?></div>
        <div class="text-xs font-semibold text-gray-600 mt-1">Biaya Bulan Ini</div>
        <div class="text-xs text-gray-400">All time: <?= $rp($bs['all_time']) ?></div>
    </div>

    <!-- 3. Total Downtime -->
    <div class="border-l-4 border-yellow-500 bg-yellow-50 rounded-xl p-4 shadow-sm">
        <div class="text-lg mb-1">⏱️</div>
        <div class="text-2xl font-bold text-yellow-700">
            <?php $dh = $dt['total_hours'];
            echo $dh >= 24 ? round($dh/24, 1).' Hari' : round($dh, 1).' Jam'; ?>
        </div>
        <div class="text-xs font-semibold text-gray-600 mt-1">Total Downtime</div>
        <div class="text-xs text-gray-400">dari <?= $dt['total_wo'] ?> WO selesai</div>
    </div>

    <!-- 4. Repeat Breakdown -->
    <div class="border-l-4 border-purple-500 bg-purple-50 rounded-xl p-4 shadow-sm">
        <div class="text-lg mb-1">🔁</div>
        <div class="text-2xl font-bold text-purple-700"><?= count($repeat) ?></div>
        <div class="text-xs font-semibold text-gray-600 mt-1">Repeat Breakdown</div>
        <div class="text-xs text-gray-400">Aset ≥2× rusak dalam 90 hari</div>
    </div>

    <!-- 5. Top Root Cause -->
    <div class="border-l-4 border-blue-500 bg-blue-50 rounded-xl p-4 shadow-sm">
        <div class="text-lg mb-1">🔍</div>
        <div class="text-sm font-bold text-blue-700 leading-tight line-clamp-2">
            <?= esc($dash_stats['top_root_cause'] ?? '-') ?>
        </div>
        <div class="text-xs font-semibold text-gray-600 mt-1">Root Cause Terbanyak</div>
        <div class="text-xs text-gray-400"><?= count($root_causes) ?> jenis kerusakan tercatat</div>
    </div>
</div>

<!-- ════════════════════════════════════════════════════════════════
     ROW 2 — CHARTS (3 KOLOM)
     ════════════════════════════════════════════════════════════════ -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-5">

    <!-- Chart Trend Biaya 12 Bulan -->
    <div class="lg:col-span-2 bg-white border rounded-xl p-4 shadow-sm">
        <div class="flex items-center justify-between mb-3">
            <h2 class="text-sm font-bold text-gray-700">💰 Trend Biaya Perbaikan (12 Bulan)</h2>
            <div class="flex gap-3 text-xs text-gray-400">
                <span class="flex items-center gap-1"><span class="w-3 h-1.5 bg-red-400 rounded inline-block"></span>Material</span>
                <span class="flex items-center gap-1"><span class="w-3 h-1.5 bg-blue-400 rounded inline-block"></span>Jasa</span>
                <span class="flex items-center gap-1"><span class="w-3 h-1.5 bg-gray-800 rounded-full inline-block"></span>WO</span>
            </div>
        </div>
        <canvas id="chartBiaya" style="max-height:200px;"></canvas>
    </div>

    <!-- Root Cause Donut -->
    <div class="bg-white border rounded-xl p-4 shadow-sm">
        <h2 class="text-sm font-bold text-gray-700 mb-3">🔍 Root Cause Analysis</h2>
        <?php if (empty($root_causes)): ?>
        <p class="text-center text-gray-400 py-8 text-sm">Belum ada data</p>
        <?php else: ?>
        <canvas id="chartRootCause" style="max-height:140px;" class="mb-3"></canvas>
        <div class="space-y-1 mt-2">
            <?php
            $rcColors = ['#ef4444','#f97316','#eab308','#22c55e','#3b82f6','#8b5cf6','#ec4899','#6b7280'];
            foreach (array_slice($root_causes, 0, 5) as $i => $rc):
                $pctVal = $rc['total'] > 0 && !empty($dash_stats['biaya_summary']['wo_total'])
                    ? round($rc['total'] / max(1, $dash_stats['biaya_summary']['wo_total']) * 100)
                    : 0;
            ?>
            <div class="flex items-center justify-between text-xs">
                <div class="flex items-center gap-1.5 min-w-0">
                    <span class="w-2.5 h-2.5 rounded-full shrink-0" style="background:<?= $rcColors[$i] ?>"></span>
                    <span class="text-gray-600 truncate"><?= esc($rc['damage_type'] ?? 'Lainnya') ?></span>
                </div>
                <span class="font-bold text-gray-800 ml-2 shrink-0"><?= $rc['total'] ?></span>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- ════════════════════════════════════════════════════════════════
     ROW 3 — TOP 10 + REPEAT BREAKDOWN
     ════════════════════════════════════════════════════════════════ -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-5">

    <!-- Top 10 Asset Rusak -->
    <div class="bg-white border rounded-xl shadow-sm overflow-hidden">
        <div class="px-4 py-3 bg-gray-50 border-b flex items-center justify-between">
            <h2 class="text-sm font-bold text-gray-700">🏆 Top 10 Asset Rusak Terbanyak</h2>
            <span class="text-xs text-gray-400">Periode filter aktif</span>
        </div>
        <?php if (empty($top10)): ?>
        <p class="text-center text-gray-400 py-8 text-sm">Belum ada data</p>
        <?php else: ?>
        <div class="divide-y">
            <?php foreach ($top10 as $i => $t): ?>
            <a href="<?= base_url('admin/cm/asset/' . $t['asset_id']) ?>"
               class="flex items-center gap-3 px-4 py-2.5 hover:bg-gray-50 transition-colors">
                <div class="w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold shrink-0
                            <?= $i < 3 ? 'bg-red-600 text-white' : 'bg-gray-100 text-gray-600' ?>">
                    <?= $i + 1 ?>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="font-semibold text-gray-800 text-sm truncate"><?= esc($t['asset_name']) ?></div>
                    <div class="text-xs text-gray-400">
                        <code><?= esc($t['asset_code']) ?></code>
                        <?= $t['department_name'] ? ' · ' . esc($t['department_name']) : '' ?>
                    </div>
                </div>
                <div class="text-right shrink-0">
                    <div class="text-sm font-bold text-red-600"><?= $t['total_wo'] ?>× rusak</div>
                    <div class="text-xs text-gray-400"><?= $rp($t['total_cost']) ?></div>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>

    <!-- Repeat Breakdown -->
    <div class="bg-white border rounded-xl shadow-sm overflow-hidden">
        <div class="px-4 py-3 bg-gray-50 border-b flex items-center justify-between">
            <h2 class="text-sm font-bold text-gray-700">🔁 Repeat Breakdown (90 Hari)</h2>
            <span class="text-xs font-bold text-purple-600 bg-purple-100 px-2 py-0.5 rounded-full">
                <?= count($repeat) ?> aset
            </span>
        </div>
        <?php if (empty($repeat)): ?>
        <div class="text-center py-8 text-gray-400">
            <div class="text-3xl mb-2">✅</div>
            <p class="text-sm">Tidak ada repeat breakdown</p>
        </div>
        <?php else: ?>
        <div class="divide-y max-h-80 overflow-y-auto">
            <?php foreach ($repeat as $rb): ?>
            <a href="<?= base_url('admin/cm/asset/' . $rb['asset_id']) ?>"
               class="flex items-start gap-3 px-4 py-3 hover:bg-gray-50 transition-colors">
                <div class="shrink-0 mt-0.5">
                    <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-purple-100 text-purple-700 font-bold text-sm">
                        <?= $rb['breakdown_count'] ?>×
                    </span>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="font-semibold text-gray-800 text-sm truncate"><?= esc($rb['asset_name']) ?></div>
                    <div class="text-xs text-gray-400 mt-0.5">
                        <code><?= esc($rb['asset_code']) ?></code>
                        <?= $rb['department_name'] ? ' · ' . esc($rb['department_name']) : '' ?>
                    </div>
                    <?php if ($rb['damage_types']): ?>
                    <div class="text-xs text-gray-500 mt-1 truncate">
                        <?= esc($rb['damage_types']) ?>
                    </div>
                    <?php endif; ?>
                </div>
                <div class="text-right shrink-0 text-xs">
                    <div class="font-semibold text-orange-600"><?= $rp($rb['total_cost']) ?></div>
                    <?php if ($rb['avg_interval_days']): ?>
                    <div class="text-gray-400">~<?= $rb['avg_interval_days'] ?>h sekali</div>
                    <?php endif; ?>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- ════════════════════════════════════════════════════════════════
     ROW 4 — DOWNTIME CHART + ROOT CAUSE TABLE
     ════════════════════════════════════════════════════════════════ -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-5">

    <!-- Downtime per Aset -->
    <div class="bg-white border rounded-xl p-4 shadow-sm">
        <h2 class="text-sm font-bold text-gray-700 mb-1">⏱️ Top Downtime per Aset</h2>
        <p class="text-xs text-gray-400 mb-3">Total: <strong><?= $dt['total_hours'] >= 24 ? round($dt['total_hours']/24,1).' hari' : round($dt['total_hours'],1).' jam' ?></strong></p>
        <?php if (empty($dt['top_assets'])): ?>
        <p class="text-center text-gray-400 py-6 text-sm">Belum ada data downtime</p>
        <?php else: ?>
        <canvas id="chartDowntime" style="max-height:200px;"></canvas>
        <?php endif; ?>
    </div>

    <!-- Root Cause Tabel Lengkap -->
    <div class="bg-white border rounded-xl shadow-sm overflow-hidden">
        <div class="px-4 py-3 bg-gray-50 border-b">
            <h2 class="text-sm font-bold text-gray-700">🔍 Root Cause — Detail</h2>
        </div>
        <?php if (empty($root_causes)): ?>
        <p class="text-center text-gray-400 py-8 text-sm">Belum ada data</p>
        <?php else: ?>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 text-xs text-gray-500 uppercase">
                        <th class="px-3 py-2 text-left">Jenis Kerusakan</th>
                        <th class="px-3 py-2 text-left">Kategori</th>
                        <th class="px-3 py-2 text-right">Total</th>
                        <th class="px-3 py-2 text-right">Biaya</th>
                        <th class="px-3 py-2 text-right">Avg Repair</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    <?php foreach ($root_causes as $rc): ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-3 py-2 font-medium text-gray-800"><?= esc($rc['damage_type'] ?? '-') ?></td>
                        <td class="px-3 py-2 text-gray-500 text-xs"><?= esc($rc['category_wo'] ?? '-') ?></td>
                        <td class="px-3 py-2 text-right font-bold text-red-600"><?= $rc['total'] ?></td>
                        <td class="px-3 py-2 text-right text-xs text-gray-600"><?= $rp($rc['total_cost']) ?></td>
                        <td class="px-3 py-2 text-right text-xs text-gray-400">
                            <?php $arm = (int)($rc['avg_repair_minutes'] ?? 0);
                            echo $arm > 0 ? ($arm >= 60 ? round($arm/60,1).'j' : $arm.'m') : '-'; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- ════════════════════════════════════════════════════════════════
     ROW 4 — DOWNTIME CHART + ROOT CAUSE TABLE
     ════════════════════════════════════════════════════════════════ -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-5">

    <!-- Downtime per Aset -->
    <div class="bg-white border rounded-xl p-4 shadow-sm">
        <h2 class="text-sm font-bold text-gray-700 mb-1">⏱️ Top Downtime per Aset</h2>
        <p class="text-xs text-gray-400 mb-3">Total: <strong><?= $dt['total_hours'] >= 24 ? round($dt['total_hours']/24,1).' hari' : round($dt['total_hours'],1).' jam' ?></strong></p>
        <?php if (empty($dt['top_assets'])): ?>
        <p class="text-center text-gray-400 py-6 text-sm">Belum ada data downtime</p>
        <?php else: ?>
        <canvas id="chartDowntime" style="max-height:200px;"></canvas>
        <?php endif; ?>
    </div>

    <!-- Root Cause Tabel Lengkap -->
    <div class="bg-white border rounded-xl shadow-sm overflow-hidden">
        <div class="px-4 py-3 bg-gray-50 border-b">
            <h2 class="text-sm font-bold text-gray-700">🔍 Root Cause — Detail</h2>
        </div>
        <?php if (empty($root_causes)): ?>
        <p class="text-center text-gray-400 py-8 text-sm">Belum ada data</p>
        <?php else: ?>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 text-xs text-gray-500 uppercase">
                        <th class="px-3 py-2 text-left">Jenis Kerusakan</th>
                        <th class="px-3 py-2 text-left">Kategori</th>
                        <th class="px-3 py-2 text-right">Total</th>
                        <th class="px-3 py-2 text-right">Biaya</th>
                        <th class="px-3 py-2 text-right">Avg Repair</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    <?php foreach ($root_causes as $rc): ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-3 py-2 font-medium text-gray-800"><?= esc($rc['damage_type'] ?? '-') ?></td>
                        <td class="px-3 py-2 text-gray-500 text-xs"><?= esc($rc['category_wo'] ?? '-') ?></td>
                        <td class="px-3 py-2 text-right font-bold text-red-600"><?= $rc['total'] ?></td>
                        <td class="px-3 py-2 text-right text-xs text-gray-600"><?= $rp($rc['total_cost']) ?></td>
                        <td class="px-3 py-2 text-right text-xs text-gray-400">
                            <?php $arm = (int)($rc['avg_repair_minutes'] ?? 0);
                            echo $arm > 0 ? ($arm >= 60 ? round($arm/60,1).'j' : $arm.'m') : '-'; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- ════════════════════════════════════════════════════════════════
     ROW 5 — FILTER + TABEL HISTORI LENGKAP
     ════════════════════════════════════════════════════════════════ -->
<div class="bg-white border rounded-xl px-4 py-3 mb-4 shadow-sm">
    <form method="GET" class="flex flex-wrap gap-2 items-center">
        <?php if (!empty($filters['date_from'])): ?>
            <input type="hidden" name="date_from" value="<?= esc($filters['date_from']) ?>">
            <input type="hidden" name="date_to"   value="<?= esc($filters['date_to']) ?>">
        <?php endif; ?>

        <input type="text" name="search"
               value="<?= esc($filters['search'] ?? '') ?>"
               placeholder="Cari WO / aset / masalah..."
               class="flex-1 min-w-[180px] border border-gray-300 rounded-lg px-3 py-1.5 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none">

        <select name="status" class="border border-gray-300 rounded-lg px-3 py-1.5 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none">
            <option value="">Semua Status</option>
            <?php foreach (['open','in_progress','waiting_part','done','cancelled'] as $s): ?>
            <option value="<?= $s ?>" <?= ($filters['status'] ?? '') === $s ? 'selected' : '' ?>>
                <?= ucwords(str_replace('_',' ',$s)) ?>
            </option>
            <?php endforeach; ?>
        </select>

        <select name="priority" class="border border-gray-300 rounded-lg px-3 py-1.5 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none">
            <option value="">Semua Prioritas</option>
            <?php foreach (['kritis','tinggi','sedang','rendah'] as $p): ?>
            <option value="<?= $p ?>" <?= ($filters['priority'] ?? '') === $p ? 'selected' : '' ?>><?= ucfirst($p) ?></option>
            <?php endforeach; ?>
        </select>

        <select name="damage_type" class="border border-gray-300 rounded-lg px-3 py-1.5 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none">
            <option value="">Semua Jenis Rusak</option>
            <?php foreach ($damage_types as $dt): ?>
            <option value="<?= esc($dt) ?>" <?= ($filters['damage_type'] ?? '') === $dt ? 'selected' : '' ?>><?= esc($dt) ?></option>
            <?php endforeach; ?>
        </select>

        <select name="department_id" class="border border-gray-300 rounded-lg px-3 py-1.5 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none">
            <option value="">Semua Dept.</option>
            <?php foreach ($departments as $did => $dname): ?>
            <option value="<?= $did ?>" <?= ($filters['department_id'] ?? '') == $did ? 'selected' : '' ?>><?= esc($dname) ?></option>
            <?php endforeach; ?>
        </select>

        <div class="flex gap-1.5">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white text-sm px-4 py-1.5 rounded-lg">Filter</button>
            <a href="<?= base_url('admin/cm') ?>" class="bg-gray-100 hover:bg-gray-200 text-gray-600 text-sm px-3 py-1.5 rounded-lg">Reset</a>
        </div>
    </form>
</div>

<!-- Tabel Histori -->
<div class="bg-white border rounded-xl shadow-sm overflow-hidden mb-6">
    <div class="px-4 py-3 bg-gray-50 border-b flex items-center justify-between">
        <h2 class="text-sm font-bold text-gray-700">📋 Histori Kerusakan Lengkap</h2>
        <span class="text-xs text-gray-400"><?= $total_records ?> record</span>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50 border-b text-gray-500 text-xs uppercase">
                    <th class="px-3 py-3 text-left">WO / Tanggal</th>
                    <th class="px-3 py-3 text-left">Aset</th>
                    <th class="px-3 py-3 text-left">Masalah / Jenis</th>
                    <th class="px-3 py-3 text-left">Teknisi</th>
                    <th class="px-3 py-3 text-center">Prioritas</th>
                    <th class="px-3 py-3 text-center">Status</th>
                    <th class="px-3 py-3 text-right">Biaya</th>
                    <th class="px-3 py-3 text-center">Foto</th>
                    <th class="px-3 py-3 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <?php if (empty($histories)): ?>
                <tr><td colspan="9" class="text-center text-gray-400 py-10">
                    <div class="text-3xl mb-2">📭</div>Tidak ada histori kerusakan.
                </td></tr>
                <?php else: ?>
                <?php foreach ($histories as $h): ?>
                <tr class="hover:bg-gray-50">
                    <td class="px-3 py-2.5 text-xs">
                        <a href="<?= base_url('admin/work-orders/'.$h['wo_id']) ?>"
                           class="font-mono text-blue-600 hover:underline font-semibold"><?= esc($h['wo_code']) ?></a>
                        <div class="text-gray-400 mt-0.5"><?= date('d/m/Y', strtotime($h['created_at'])) ?></div>
                    </td>
                    <td class="px-3 py-2.5 text-xs min-w-[130px]">
                        <a href="<?= base_url('admin/cm/asset/'.$h['asset_id']) ?>"
                           class="font-semibold text-gray-800 hover:text-blue-600 truncate block"><?= esc($h['asset_name']) ?></a>
                        <code class="text-gray-400"><?= esc($h['asset_code']) ?></code>
                        <div class="text-gray-400"><?= esc($h['department_name'] ?? '') ?></div>
                    </td>
                    <td class="px-3 py-2.5 text-xs min-w-[160px]">
                        <p class="text-gray-800 line-clamp-2"><?= esc($h['problem_desc']) ?></p>
                        <?php if ($h['damage_type']): ?>
                        <span class="inline-flex mt-1 px-1.5 py-0.5 bg-red-50 text-red-600 rounded text-xs"><?= esc($h['damage_type']) ?></span>
                        <?php endif; ?>
                    </td>
                    <td class="px-3 py-2.5 text-xs text-gray-600">
                        <?= $h['technician_name'] ? esc($h['technician_name']) : '<span class="text-gray-400">-</span>' ?>
                        <?php if ($h['vendor_name']): ?><div class="text-gray-400"><?= esc($h['vendor_name']) ?></div><?php endif; ?>
                    </td>
                    <td class="px-3 py-2.5 text-center">
                        <span class="px-2 py-0.5 rounded-full text-xs font-semibold <?= $priorityColor[$h['priority']] ?? 'bg-gray-100 text-gray-500' ?>">
                            <?= ucfirst($h['priority']) ?>
                        </span>
                    </td>
                    <td class="px-3 py-2.5 text-center">
                        <span class="px-2 py-0.5 rounded-full text-xs font-medium <?= $statusColor[$h['status']] ?? 'bg-gray-100 text-gray-500' ?>">
                            <?= ucwords(str_replace('_',' ',$h['status'])) ?>
                        </span>
                    </td>
                    <td class="px-3 py-2.5 text-right text-xs">
                        <?php $totalCost = ($h['material_cost'] ?? 0) + ($h['labor_cost'] ?? 0); ?>
                        <div class="font-semibold text-gray-800"><?= $totalCost > 0 ? $rp($totalCost) : ($h['cost'] > 0 ? $rp($h['cost']) : '-') ?></div>
                        <?php if ($h['repair_time']): ?>
                        <div class="text-gray-400">
                            <?= $h['repair_time'] >= 60 ? round($h['repair_time']/60,1).'j' : $h['repair_time'].'m' ?>
                        </div>
                        <?php endif; ?>
                    </td>
                    <td class="px-3 py-2.5 text-center">
                        <div class="flex gap-1 justify-center">
                            <?php if ($h['photo_before']): ?>
                            <a href="<?= base_url('uploads/work_orders/'.$h['photo_before']) ?>" target="_blank"
                               title="Foto Before" class="text-orange-500 hover:text-orange-700 text-xs">📷B</a>
                            <?php endif; ?>
                            <?php if ($h['photo_after']): ?>
                            <a href="<?= base_url('uploads/work_orders/'.$h['photo_after']) ?>" target="_blank"
                               title="Foto After" class="text-green-500 hover:text-green-700 text-xs">📷A</a>
                            <?php endif; ?>
                        </div>
                    </td>
                    <td class="px-3 py-2.5 text-center">
                        <a href="<?= base_url('admin/work-orders/'.$h['wo_id']) ?>"
                           class="p-1.5 rounded-lg bg-blue-50 hover:bg-blue-100 text-blue-600 text-xs inline-flex">👁</a>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="flex items-center justify-between px-4 py-3 border-t bg-gray-50 text-sm text-gray-500">
        <span><?= count($histories) ?> dari <strong><?= $total_records ?></strong> record</span>
        <?php if ($total_pages > 1):
            $qs = http_build_query(array_filter($filters, fn($v) => $v !== null && $v !== ''));
        ?>
        <div class="flex gap-1">
            <?php if ($page > 1): ?>
                <a href="?page=<?= $page-1 ?>&<?= $qs ?>" class="px-3 py-1 rounded-lg border hover:bg-white">‹</a>
            <?php endif; ?>
            <?php for ($p = max(1,$page-2); $p <= min($total_pages,$page+2); $p++): ?>
                <a href="?page=<?= $p ?>&<?= $qs ?>"
                   class="px-3 py-1 rounded-lg border <?= $p===$page ? 'bg-blue-600 text-white border-blue-600' : 'hover:bg-white' ?>">
                    <?= $p ?>
                </a>
            <?php endfor; ?>
            <?php if ($page < $total_pages): ?>
                <a href="?page=<?= $page+1 ?>&<?= $qs ?>" class="px-3 py-1 rounded-lg border hover:bg-white">›</a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
(function () {
    const biaya     = <?= $chart_biaya ?>;
    const rootCause = <?= $chart_root_cause ?>;
    const top10     = <?= $chart_top10 ?>;
    const downtime  = <?= $chart_downtime ?>;
    const colors    = ['#ef4444','#f97316','#eab308','#22c55e','#3b82f6','#8b5cf6','#ec4899','#6b7280'];

    // Trend Biaya — Stacked bar
    const ctxBiaya = document.getElementById('chartBiaya');
    if (ctxBiaya && biaya.labels?.length) {
        new Chart(ctxBiaya, {
            type: 'bar',
            data: {
                labels: biaya.labels,
                datasets: [
                    { label: 'Material', data: biaya.material, backgroundColor: 'rgba(239,68,68,0.7)', borderRadius: 2, stack: 'cost' },
                    { label: 'Jasa',     data: biaya.jasa,     backgroundColor: 'rgba(59,130,246,0.7)',  borderRadius: 2, stack: 'cost' },
                    { label: 'WO', data: biaya.wo_count, type: 'line',
                      borderColor: '#1f2937', backgroundColor: 'transparent',
                      borderWidth: 2, pointRadius: 3, tension: 0.4, yAxisID: 'y2', stack: '' },
                ]
            },
            options: {
                plugins: { legend: { display: false } },
                scales: {
                    x:  { ticks: { font: { size: 10 } } },
                    y:  { ticks: { font: { size: 10 }, callback: v => 'Rp'+(v/1000000).toFixed(1)+'jt' }, beginAtZero: true, stacked: true },
                    y2: { position: 'right', ticks: { font: { size: 10 }, stepSize: 1 }, beginAtZero: true, grid: { display: false } },
                },
                responsive: true, maintainAspectRatio: false,
            }
        });
    }

    // Root Cause — Donut
    const ctxRc = document.getElementById('chartRootCause');
    if (ctxRc && rootCause.labels?.length) {
        new Chart(ctxRc, {
            type: 'doughnut',
            data: { labels: rootCause.labels,
                    datasets: [{ data: rootCause.data, backgroundColor: colors, borderWidth: 2 }] },
            options: { cutout: '60%', plugins: { legend: { display: false } }, responsive: true, maintainAspectRatio: true }
        });
    }

    // Downtime — Horizontal bar
    const ctxDt = document.getElementById('chartDowntime');
    if (ctxDt && downtime.labels?.length) {
        new Chart(ctxDt, {
            type: 'bar',
            data: { labels: downtime.labels,
                    datasets: [{ label: 'Jam', data: downtime.data,
                                 backgroundColor: 'rgba(234,179,8,0.7)', borderRadius: 3 }] },
            options: {
                indexAxis: 'y',
                plugins: { legend: { display: false } },
                scales: {
                    x: { ticks: { font: { size: 10 }, callback: v => v+'j' }, beginAtZero: true },
                    y: { ticks: { font: { size: 9 } } }
                },
                responsive: true, maintainAspectRatio: false,
            }
        });
    }
})();
</script>

<?= $this->endSection() ?>
