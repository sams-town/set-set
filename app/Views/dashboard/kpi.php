<?= $this->extend('inventory/_layout') ?>
<?= $this->section('content') ?>
<?php
$rp  = fn($n) => 'Rp ' . number_format((float)$n, 0, ',', '.');
$pct = fn($n) => number_format((float)$n, 1) . '%';

$woColors = [
    'open'         => 'bg-red-100 text-red-700',
    'in_progress'  => 'bg-blue-100 text-blue-700',
    'waiting_part' => 'bg-yellow-100 text-yellow-700',
    'done'         => 'bg-green-100 text-green-700',
    'cancelled'    => 'bg-gray-100 text-gray-500',
];
$priorityColors = [
    'kritis' => 'bg-red-600 text-white',
    'tinggi' => 'bg-orange-500 text-white',
    'sedang' => 'bg-yellow-400 text-gray-900',
    'rendah' => 'bg-green-400 text-gray-900',
];
?>

<!-- ── Page Header ─────────────────────────────────────────── -->
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-xl font-bold text-gray-800">General Affairs Dashboard</h1>
        <p class="text-sm text-gray-500 mt-0.5">
            <?= date('l, d F Y') ?> &nbsp;·&nbsp; Selamat datang,
            <strong><?= esc(session()->get('user_name')) ?></strong>
        </p>
    </div>
    <a href="<?= base_url('admin/inventory') ?>"
       class="text-sm text-blue-600 hover:underline">Lihat Inventory →</a>
</div>

<!-- ================================================================
     15 INDIKATOR KPI — 5 kolom × 3 baris
     ================================================================ -->
<div class="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-5 gap-3 mb-6">

<?php
$cards = [
    // Baris 1 — Aset
    [
        'no'     => 1,
        'label'  => 'Total Asset',
        'value'  => number_format($total_asset) . ' Unit',
        'icon'   => '🗃️',
        'color'  => 'border-blue-500 bg-blue-50',
        'vcolor' => 'text-blue-700',
    ],
    [
        'no'     => 2,
        'label'  => 'Total Nilai Asset',
        'value'  => $rp($total_nilai_asset),
        'icon'   => '💎',
        'color'  => 'border-indigo-500 bg-indigo-50',
        'vcolor' => 'text-indigo-700',
    ],
    [
        'no'     => 3,
        'label'  => 'Asset Aktif',
        'value'  => number_format($asset_aktif),
        'icon'   => '✅',
        'color'  => 'border-green-500 bg-green-50',
        'vcolor' => 'text-green-700',
    ],
    [
        'no'     => 4,
        'label'  => 'Rusak Ringan',
        'value'  => number_format($rusak_ringan),
        'icon'   => '⚠️',
        'color'  => 'border-yellow-400 bg-yellow-50',
        'vcolor' => 'text-yellow-700',
    ],
    [
        'no'     => 5,
        'label'  => 'Rusak Berat',
        'value'  => number_format($rusak_berat),
        'icon'   => '🔴',
        'color'  => 'border-red-500 bg-red-50',
        'vcolor' => 'text-red-700',
    ],

    // Baris 2 — Work Order & Maintenance
    [
        'no'     => 6,
        'label'  => 'Dalam Perbaikan',
        'value'  => number_format($dalam_perbaikan),
        'icon'   => '🔧',
        'color'  => 'border-orange-400 bg-orange-50',
        'vcolor' => 'text-orange-700',
    ],
    [
        'no'     => 7,
        'label'  => 'Pending Approval',
        'value'  => number_format($pending_approval),
        'icon'   => '⏳',
        'color'  => 'border-amber-400 bg-amber-50',
        'vcolor' => 'text-amber-700',
    ],
    [
        'no'     => 8,
        'label'  => 'Work Order Open',
        'value'  => number_format($wo_open),
        'icon'   => '📋',
        'color'  => 'border-rose-400 bg-rose-50',
        'vcolor' => 'text-rose-700',
    ],
    [
        'no'     => 9,
        'label'  => 'Work Order Closed',
        'value'  => number_format($wo_closed),
        'icon'   => '✔️',
        'color'  => 'border-teal-400 bg-teal-50',
        'vcolor' => 'text-teal-700',
    ],
    [
        'no'     => 10,
        'label'  => 'PM Due',
        'value'  => number_format($pm_due),
        'icon'   => '📅',
        'color'  => 'border-red-400 bg-red-50',
        'vcolor' => 'text-red-600',
    ],

    // Baris 3 — PM, Garansi, Vendor, Budget
    [
        'no'     => 11,
        'label'  => 'PM Done',
        'value'  => number_format($pm_done),
        'icon'   => '🛡️',
        'color'  => 'border-green-400 bg-green-50',
        'vcolor' => 'text-green-700',
    ],
    [
        'no'     => 12,
        'label'  => 'Asset Expired Warranty',
        'value'  => number_format($asset_expired_warranty),
        'icon'   => '🔔',
        'color'  => 'border-slate-400 bg-slate-50',
        'vcolor' => 'text-slate-700',
    ],
    [
        'no'     => 13,
        'label'  => 'Vendor Aktif',
        'value'  => number_format($vendor_aktif),
        'icon'   => '🏢',
        'color'  => 'border-cyan-400 bg-cyan-50',
        'vcolor' => 'text-cyan-700',
    ],
    [
        'no'     => 14,
        'label'  => 'Pengadaan Bulan Ini',
        'value'  => $rp($pengadaan_bulan_ini),
        'icon'   => '🛒',
        'color'  => 'border-violet-400 bg-violet-50',
        'vcolor' => 'text-violet-700',
    ],
    [
        'no'     => 15,
        'label'  => 'Budget Terserap',
        'value'  => $pct($budget_terserap_persen),
        'icon'   => '💰',
        'color'  => 'border-purple-500 bg-purple-50',
        'vcolor' => 'text-purple-700',
    ],
];

foreach ($cards as $c):
?>
<div class="border-l-4 <?= $c['color'] ?> rounded-xl p-3 shadow-sm flex flex-col gap-1 hover:shadow-md transition-shadow">
    <div class="flex items-center justify-between">
        <span class="text-lg"><?= $c['icon'] ?></span>
        <span class="text-xs text-gray-400 font-mono">#<?= $c['no'] ?></span>
    </div>
    <div class="text-xl font-bold <?= $c['vcolor'] ?> leading-tight mt-1 truncate">
        <?= $c['value'] ?>
    </div>
    <div class="text-xs font-semibold text-gray-600 leading-tight">
        <?= $c['label'] ?>
    </div>
</div>
<?php endforeach; ?>
</div>

<!-- ================================================================
     ROW 2 — CHARTS (3 kolom)
     ================================================================ -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-5">

    <!-- Donut: Status Aset -->
    <div class="bg-white border rounded-xl p-4 shadow-sm">
        <h2 class="text-sm font-bold text-gray-700 mb-3">Status Aset</h2>
        <div class="flex items-center gap-4">
            <canvas id="chartCondition" style="max-width:110px;max-height:110px;"></canvas>
            <div class="space-y-2 text-sm flex-1">
                <?php foreach ([
                    ['Normal',   $asset_summary['normal'],   'bg-green-500'],
                    ['Warning',  $asset_summary['warning'],  'bg-yellow-400'],
                    ['Critical', $asset_summary['critical'], 'bg-red-500'],
                ] as [$lbl, $val, $clr]): ?>
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full <?= $clr ?>"></span>
                        <span class="text-gray-600 text-xs"><?= $lbl ?></span>
                    </div>
                    <span class="font-bold text-gray-800 text-xs"><?= number_format($val) ?></span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Bar: Aset per Departemen -->
    <div class="bg-white border rounded-xl p-4 shadow-sm">
        <h2 class="text-sm font-bold text-gray-700 mb-3">Aset per Departemen</h2>
        <canvas id="chartDept" style="max-height:130px;"></canvas>
    </div>

    <!-- Line: Trend Biaya Pemeliharaan -->
    <div class="bg-white border rounded-xl p-4 shadow-sm">
        <h2 class="text-sm font-bold text-gray-700 mb-1">Biaya Pemeliharaan (6 Bln)</h2>
        <?php
            $trendArr = json_decode($chart_cost_trend, true);
            $totalTrend = array_sum($trendArr['data'] ?? []);
        ?>
        <p class="text-xs text-gray-400 mb-3">Total: <strong><?= $rp($totalTrend) ?></strong></p>
        <canvas id="chartCost" style="max-height:110px;"></canvas>
    </div>
</div>

<!-- ================================================================
     ROW 3 — WO STATUS + GARANSI
     ================================================================ -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-5">

    <!-- Status Work Order -->
    <div class="bg-white border rounded-xl shadow-sm overflow-hidden">
        <div class="px-4 py-3 border-b bg-gray-50 flex items-center justify-between">
            <h2 class="text-sm font-bold text-gray-700">📋 Status Work Order</h2>
            <?php
                $woTotal = $wo_summary['total'];
                $woRes   = $woTotal > 0 ? round($wo_summary['done'] / $woTotal * 100, 1) : 0;
            ?>
            <span class="text-xs font-bold text-green-600">Resolution <?= $pct($woRes) ?></span>
        </div>
        <div class="p-4 grid grid-cols-2 gap-3">
            <?php foreach ([
                ['open',         'Baru (Open)',        'bg-red-500'],
                ['in_progress',  'Sedang Dikerjakan',  'bg-blue-500'],
                ['waiting_part', 'Tunggu Suku Cadang', 'bg-yellow-400'],
                ['done',         'Selesai',            'bg-green-500'],
                ['cancelled',    'Dibatalkan',         'bg-gray-300'],
            ] as [$key, $label, $bar]):
                $count  = $wo_summary[$key] ?? 0;
                $pctVal = $woTotal > 0 ? ($count / $woTotal * 100) : 0;
            ?>
            <div>
                <div class="flex justify-between text-xs mb-1">
                    <span class="text-gray-600"><?= $label ?></span>
                    <span class="font-bold text-gray-800"><?= number_format($count) ?></span>
                </div>
                <div class="h-1.5 bg-gray-100 rounded-full overflow-hidden">
                    <div class="h-full <?= $bar ?> rounded-full" style="width:<?= round($pctVal) ?>%"></div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Tabel WO Open terbaru -->
        <?php if (!empty($open_wos)): ?>
        <div class="border-t overflow-x-auto">
            <table class="w-full text-xs">
                <thead>
                    <tr class="bg-gray-50 text-gray-500 text-left">
                        <th class="px-3 py-2 font-medium">WO</th>
                        <th class="px-3 py-2 font-medium">Aset</th>
                        <th class="px-3 py-2 font-medium">Prioritas</th>
                        <th class="px-3 py-2 font-medium">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    <?php foreach ($open_wos as $wo): ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-3 py-2 font-mono text-gray-700"><?= esc($wo['wo_code']) ?></td>
                        <td class="px-3 py-2 max-w-[110px] truncate"><?= esc($wo['asset_name'] ?? '-') ?></td>
                        <td class="px-3 py-2">
                            <span class="px-1.5 py-0.5 rounded text-xs font-semibold <?= $priorityColors[$wo['priority']] ?? 'bg-gray-100' ?>">
                                <?= ucfirst($wo['priority']) ?>
                            </span>
                        </td>
                        <td class="px-3 py-2">
                            <span class="px-1.5 py-0.5 rounded text-xs font-medium <?= $woColors[$wo['status']] ?? '' ?>">
                                <?= ucwords(str_replace('_', ' ', $wo['status'])) ?>
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>

    <!-- Garansi Kadaluarsa -->
    <div class="bg-white border rounded-xl shadow-sm overflow-hidden">
        <div class="px-4 py-3 border-b bg-gray-50 flex items-center justify-between">
            <h2 class="text-sm font-bold text-gray-700">⏰ Garansi Akan Kadaluarsa (90 hari)</h2>
            <span class="text-xs font-bold text-orange-600"><?= count($warranty_soon) ?> aset</span>
        </div>
        <?php if (empty($warranty_soon)): ?>
        <div class="text-center py-10 text-gray-400 text-sm">
            <div class="text-3xl mb-2">✅</div>Tidak ada garansi yang akan habis.
        </div>
        <?php else: ?>
        <div class="divide-y">
            <?php foreach ($warranty_soon as $w):
                $daysLeft = (int) ((strtotime($w['warranty_expiry']) - time()) / 86400);
                $urg = $daysLeft <= 14 ? 'text-red-600' : ($daysLeft <= 30 ? 'text-orange-500' : 'text-yellow-600');
            ?>
            <div class="flex items-center justify-between px-4 py-3 hover:bg-gray-50">
                <div>
                    <a href="<?= base_url('admin/inventory/' . $w['id']) ?>"
                       class="text-sm font-semibold text-gray-800 hover:text-blue-600">
                        <?= esc($w['name']) ?>
                    </a>
                    <div class="text-xs text-gray-400 mt-0.5">
                        <code class="bg-gray-100 px-1 rounded"><?= esc($w['asset_code']) ?></code>
                        <?= $w['department_name'] ? ' · ' . esc($w['department_name']) : '' ?>
                    </div>
                </div>
                <div class="text-right ml-3 shrink-0">
                    <div class="text-xs font-bold <?= $urg ?>"><?= $daysLeft ?> hari lagi</div>
                    <div class="text-xs text-gray-400"><?= date('d M Y', strtotime($w['warranty_expiry'])) ?></div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- ================================================================
     ROW 4 — AKTIVITAS + RINGKASAN BAWAH
     ================================================================ -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-6">

    <!-- Aktivitas Terbaru -->
    <div class="lg:col-span-2 bg-white border rounded-xl shadow-sm overflow-hidden">
        <div class="px-4 py-3 border-b bg-gray-50">
            <h2 class="text-sm font-bold text-gray-700">🕐 Aktivitas Terbaru</h2>
        </div>
        <?php if (empty($recent_activity)): ?>
        <p class="text-center text-gray-400 py-8 text-sm">Belum ada aktivitas.</p>
        <?php else: ?>
        <div class="divide-y max-h-72 overflow-y-auto">
            <?php foreach ($recent_activity as $act): ?>
            <div class="flex items-start justify-between px-4 py-2.5 hover:bg-gray-50">
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="px-2 py-0.5 rounded-full bg-gray-100 text-gray-600 text-xs font-medium">
                            <?= esc($act['action']) ?>
                        </span>
                        <span class="text-sm font-semibold text-gray-800 truncate">
                            <?= esc($act['asset_name'] ?? '-') ?>
                        </span>
                        <code class="text-xs text-gray-400"><?= esc($act['asset_code'] ?? '') ?></code>
                    </div>
                    <?php if (!empty($act['description'])): ?>
                    <p class="text-xs text-gray-500 mt-0.5 truncate"><?= esc($act['description']) ?></p>
                    <?php endif; ?>
                    <p class="text-xs text-gray-400 mt-0.5">
                        oleh <strong><?= esc($act['user_name'] ?? 'sistem') ?></strong>
                        <?php if (!empty($act['cost'])): ?>
                        · <span class="text-orange-600 font-medium"><?= $rp($act['cost']) ?></span>
                        <?php endif; ?>
                    </p>
                </div>
                <span class="text-xs text-gray-400 ml-3 shrink-0 mt-0.5">
                    <?= date('d/m H:i', strtotime($act['created_at'])) ?>
                </span>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>

    <!-- Panel Ringkasan Kanan -->
    <div class="space-y-3">

        <!-- Total Nilai Aset -->
        <div class="bg-gradient-to-br from-blue-600 to-blue-800 text-white rounded-xl p-4 shadow-sm">
            <p class="text-xs font-medium text-blue-200 mb-1">Total Nilai Asset</p>
            <p class="text-xl font-bold leading-tight"><?= $rp($total_nilai_asset) ?></p>
            <p class="text-xs text-blue-200 mt-1">Berdasarkan harga pembelian</p>
        </div>

        <!-- Budget Terserap (progress bar) -->
        <div class="bg-white border rounded-xl p-4 shadow-sm">
            <div class="flex items-center justify-between mb-2">
                <p class="text-xs font-bold text-gray-600">💰 Budget Terserap</p>
                <span class="text-lg font-bold text-purple-700"><?= $pct($budget_terserap_persen) ?></span>
            </div>
            <div class="h-2.5 bg-gray-100 rounded-full overflow-hidden mb-2">
                <div class="h-full rounded-full transition-all
                    <?= $budget_terserap_persen >= 90 ? 'bg-red-500' : ($budget_terserap_persen >= 70 ? 'bg-orange-400' : 'bg-purple-500') ?>"
                    style="width:<?= min(100, round($budget_terserap_persen)) ?>%"></div>
            </div>
            <p class="text-xs text-gray-400"><?= $rp($budget_terserap_value) ?> dari total anggaran WO</p>
        </div>

        <!-- PM Summary -->
        <div class="bg-white border rounded-xl p-4 shadow-sm">
            <p class="text-xs font-bold text-gray-600 mb-3">🛡️ Preventive Maintenance</p>
            <div class="grid grid-cols-2 gap-2 text-center">
                <div class="bg-red-50 rounded-lg p-2">
                    <div class="text-xl font-bold text-red-600"><?= number_format($pm_due) ?></div>
                    <div class="text-xs text-gray-500 mt-0.5">PM Due</div>
                </div>
                <div class="bg-green-50 rounded-lg p-2">
                    <div class="text-xl font-bold text-green-600"><?= number_format($pm_done) ?></div>
                    <div class="text-xs text-gray-500 mt-0.5">PM Done</div>
                </div>
            </div>
        </div>

        <!-- Vendor & User -->
        <div class="bg-white border rounded-xl p-4 shadow-sm grid grid-cols-2 gap-3">
            <div class="text-center">
                <div class="text-2xl font-bold text-cyan-700"><?= number_format($vendor_aktif) ?></div>
                <div class="text-xs text-gray-500 mt-0.5">Vendor Aktif</div>
            </div>
            <div class="text-center border-l">
                <div class="text-2xl font-bold text-gray-800"><?= number_format($total_users) ?></div>
                <div class="text-xs text-gray-500 mt-0.5">User Aktif</div>
            </div>
        </div>
    </div>
</div>

<!-- ================================================================
     CHART.JS
     ================================================================ -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
(function () {
    if (typeof Chart === 'undefined') {
        console.warn('Chart.js was not loaded. Charts will not be initialized.');
        return;
    }
    const condData = <?= $chart_condition ?>;
    const deptData = <?= $chart_dept ?>;
    const costData = <?= $chart_cost_trend ?>;

    // Donut — Kondisi
    const ctxCond = document.getElementById('chartCondition');
    if (ctxCond && condData.data.some(v => v > 0)) {
        new Chart(ctxCond, {
            type: 'doughnut',
            data: {
                labels: condData.labels,
                datasets: [{ data: condData.data, backgroundColor: condData.colors, borderWidth: 2 }]
            },
            options: {
                cutout: '65%',
                plugins: { legend: { display: false } },
                responsive: true,
                maintainAspectRatio: true,
            }
        });
    }

    // Bar horizontal — Per Departemen
    const ctxDept = document.getElementById('chartDept');
    if (ctxDept && deptData.labels && deptData.labels.length) {
        new Chart(ctxDept, {
            type: 'bar',
            data: {
                labels: deptData.labels,
                datasets: [{ label: 'Aset', data: deptData.data, backgroundColor: '#3b82f6', borderRadius: 4 }]
            },
            options: {
                indexAxis: 'y',
                plugins: { legend: { display: false } },
                scales: {
                    x: { ticks: { font: { size: 10 } } },
                    y: { ticks: { font: { size: 10 } } }
                },
                responsive: true,
                maintainAspectRatio: false,
            }
        });
    }

    // Line — Trend Biaya
    const ctxCost = document.getElementById('chartCost');
    if (ctxCost && costData.labels && costData.labels.length) {
        new Chart(ctxCost, {
            type: 'line',
            data: {
                labels: costData.labels,
                datasets: [{
                    label: 'Biaya',
                    data: costData.data,
                    fill: true,
                    backgroundColor: 'rgba(139,92,246,0.08)',
                    borderColor: '#8b5cf6',
                    borderWidth: 2,
                    pointRadius: 3,
                    tension: 0.4,
                }]
            },
            options: {
                plugins: { legend: { display: false } },
                scales: {
                    x: { ticks: { font: { size: 10 } } },
                    y: {
                        ticks: {
                            font: { size: 10 },
                            callback: v => 'Rp' + (v / 1000000).toFixed(1) + 'jt'
                        }
                    }
                },
                responsive: true,
                maintainAspectRatio: false,
            }
        });
    }
})();
</script>

<?= $this->endSection() ?>
