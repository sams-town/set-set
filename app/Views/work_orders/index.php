<?= $this->extend('inventory/_layout') ?>
<?= $this->section('content') ?>

<?php
$rp  = fn($n) => 'Rp ' . number_format((float)$n, 0, ',', '.');
$pct = fn($n) => number_format((float)$n, 1) . '%';

$priorityColor = [
    'kritis' => 'bg-red-100 text-red-700 border-red-300',
    'tinggi' => 'bg-orange-100 text-orange-700 border-orange-300',
    'sedang' => 'bg-yellow-100 text-yellow-700 border-yellow-300',
    'rendah' => 'bg-green-100 text-green-700 border-green-300',
];
$statusColor = [
    'open'         => 'bg-red-100 text-red-700',
    'in_progress'  => 'bg-blue-100 text-blue-700',
    'waiting_part' => 'bg-yellow-100 text-yellow-700',
    'done'         => 'bg-green-100 text-green-700',
    'cancelled'    => 'bg-gray-100 text-gray-500',
];
$ds = $dash_stats;
?>

<!-- ── Page Header ─────────────────────────────────────────── -->
<div class="flex items-center justify-between mb-5">
    <div>
        <h1 class="text-xl font-bold text-gray-800">📋 Work Order</h1>
        <p class="text-sm text-gray-500 mt-0.5">
            <?= date('l, d F Y') ?>
            <?php if ($overdue_count > 0): ?>
            · <span class="text-red-600 font-semibold">⚠️ <?= $overdue_count ?> WO Overdue</span>
            <?php endif; ?>
        </p>
    </div>
    <a href="<?= base_url('admin/work-orders/new') ?>"
       class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors shadow-sm">
        + Buat WO Baru
    </a>
</div>

<!-- ================================================================
     DASHBOARD KPI — 8 KARTU
     ================================================================ -->
<div class="grid grid-cols-2 sm:grid-cols-4 xl:grid-cols-8 gap-3 mb-5">
    <?php
    $fmt = \App\Models\WorkOrderModel::class;

    $kpiCards = [
        [
            'label'  => 'WO Baru Hari Ini',
            'value'  => number_format($ds['new_today']),
            'icon'   => '🆕',
            'color'  => 'border-blue-500 bg-blue-50',
            'vcolor' => 'text-blue-700',
            'link'   => '?status=open',
        ],
        [
            'label'  => 'WO Open',
            'value'  => number_format($ds['open']),
            'icon'   => '🔴',
            'color'  => 'border-red-400 bg-red-50',
            'vcolor' => 'text-red-700',
            'link'   => '?status=open',
        ],
        [
            'label'  => 'WO In Progress',
            'value'  => number_format($ds['in_progress']),
            'icon'   => '🔵',
            'color'  => 'border-blue-400 bg-blue-50',
            'vcolor' => 'text-blue-700',
            'link'   => '?status=in_progress',
        ],
        [
            'label'  => 'WO Pending',
            'value'  => number_format($ds['waiting_part']),
            'icon'   => '🟡',
            'color'  => 'border-yellow-400 bg-yellow-50',
            'vcolor' => 'text-yellow-700',
            'link'   => '?status=waiting_part',
        ],
        [
            'label'  => 'WO Closed',
            'value'  => number_format($ds['closed']),
            'icon'   => '✅',
            'color'  => 'border-green-400 bg-green-50',
            'vcolor' => 'text-green-700',
            'link'   => '?status=done',
        ],
        [
            'label'  => 'SLA Compliance',
            'value'  => $pct($ds['sla_compliance']),
            'icon'   => '🎯',
            'color'  => ($ds['sla_compliance'] >= 80) ? 'border-green-500 bg-green-50' : 'border-red-400 bg-red-50',
            'vcolor' => ($ds['sla_compliance'] >= 80) ? 'text-green-700' : 'text-red-700',
            'link'   => '#',
        ],
        [
            'label'  => 'Avg Response Time',
            'value'  => $ds['avg_response_time'] > 0 ? \App\Models\WorkOrderModel::formatMinutes($ds['avg_response_time']) : '-',
            'icon'   => '⚡',
            'color'  => 'border-indigo-400 bg-indigo-50',
            'vcolor' => 'text-indigo-700',
            'link'   => '#',
        ],
        [
            'label'  => 'Avg Repair Time',
            'value'  => $ds['avg_repair_time'] > 0 ? \App\Models\WorkOrderModel::formatMinutes($ds['avg_repair_time']) : '-',
            'icon'   => '🔧',
            'color'  => 'border-purple-400 bg-purple-50',
            'vcolor' => 'text-purple-700',
            'link'   => '#',
        ],
    ];
    foreach ($kpiCards as $c):
    ?>
    <a href="<?= $c['link'] ?>"
       class="border-l-4 <?= $c['color'] ?> rounded-xl p-3 flex flex-col gap-1 shadow-sm hover:shadow-md transition-shadow">
        <div class="flex items-center justify-between">
            <span class="text-lg"><?= $c['icon'] ?></span>
        </div>
        <span class="text-xl font-bold <?= $c['vcolor'] ?> leading-tight mt-1">
            <?= $c['value'] ?>
        </span>
        <span class="text-xs font-semibold text-gray-600 leading-tight"><?= $c['label'] ?></span>
    </a>
    <?php endforeach; ?>
</div>

<!-- ================================================================
     ROW 2 — Progress Bar + Chart Trend
     ================================================================ -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-5">

    <!-- Status Distribution -->
    <div class="bg-white border rounded-xl p-4 shadow-sm">
        <h2 class="text-sm font-bold text-gray-700 mb-3">Distribusi Status WO</h2>
        <?php
        $totalWo = $ds['total'] ?: 1;
        $distItems = [
            ['open',         'Baru (Open)',        'bg-red-500',    $ds['open']],
            ['in_progress',  'Sedang Dikerjakan',  'bg-blue-500',   $ds['in_progress']],
            ['waiting_part', 'Tunggu Suku Cadang', 'bg-yellow-400', $ds['waiting_part']],
            ['done',         'Selesai',            'bg-green-500',  $ds['done']],
            ['cancelled',    'Dibatalkan',         'bg-gray-300',   $ds['cancelled']],
        ];
        foreach ($distItems as [$key, $label, $bar, $count]):
            $pctVal = round($count / $totalWo * 100);
        ?>
        <div class="mb-2">
            <div class="flex justify-between text-xs mb-0.5">
                <span class="text-gray-600"><?= $label ?></span>
                <span class="font-semibold text-gray-800"><?= number_format($count) ?></span>
            </div>
            <div class="h-1.5 bg-gray-100 rounded-full overflow-hidden">
                <div class="h-full <?= $bar ?> rounded-full" style="width:<?= $pctVal ?>%"></div>
            </div>
        </div>
        <?php endforeach; ?>

        <!-- SLA Compliance progress -->
        <div class="mt-3 pt-3 border-t">
            <div class="flex justify-between text-xs mb-1">
                <span class="text-gray-500 font-medium">🎯 SLA Compliance</span>
                <span class="font-bold <?= $ds['sla_compliance'] >= 80 ? 'text-green-600' : 'text-red-600' ?>">
                    <?= $pct($ds['sla_compliance']) ?>
                </span>
            </div>
            <div class="h-2 bg-gray-100 rounded-full overflow-hidden">
                <div class="h-full rounded-full <?= $ds['sla_compliance'] >= 80 ? 'bg-green-500' : ($ds['sla_compliance'] >= 60 ? 'bg-orange-400' : 'bg-red-500') ?>"
                     style="width:<?= min(100, round($ds['sla_compliance'])) ?>%"></div>
            </div>
            <p class="text-xs text-gray-400 mt-1"><?= $ds['sla_ok'] ?> dari <?= $ds['done'] ?> WO selesai tepat waktu</p>
        </div>
    </div>

    <!-- Trend Chart (2 kolom) -->
    <div class="lg:col-span-2 bg-white border rounded-xl p-4 shadow-sm">
        <div class="flex items-center justify-between mb-3">
            <h2 class="text-sm font-bold text-gray-700">Trend Work Order (6 Bulan)</h2>
            <div class="flex gap-3 text-xs text-gray-500">
                <span class="flex items-center gap-1"><span class="w-3 h-1 bg-blue-500 rounded inline-block"></span>Total</span>
                <span class="flex items-center gap-1"><span class="w-3 h-1 bg-green-500 rounded inline-block"></span>Selesai</span>
            </div>
        </div>
        <canvas id="chartTrend" style="max-height:150px;"></canvas>
    </div>
</div>

<!-- ================================================================
     FILTER BAR
     ================================================================ -->
<div class="bg-white border rounded-xl px-4 py-3 mb-4 shadow-sm">
    <form method="GET" class="flex flex-wrap gap-2 items-center">
        <input type="text" name="search"
               value="<?= esc($filters['search'] ?? '') ?>"
               placeholder="Cari WO / aset / pelapor..."
               class="flex-1 min-w-[180px] border border-gray-300 rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">

        <select name="status" class="border border-gray-300 rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
            <option value="">Semua Status</option>
            <?php foreach ($status_list as $s): ?>
            <option value="<?= $s ?>" <?= ($filters['status'] ?? '') === $s ? 'selected' : '' ?>>
                <?= ucwords(str_replace('_', ' ', $s)) ?>
            </option>
            <?php endforeach; ?>
        </select>

        <select name="priority" class="border border-gray-300 rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
            <option value="">Semua Prioritas</option>
            <?php foreach ($priority_list as $p): ?>
            <option value="<?= $p ?>" <?= ($filters['priority'] ?? '') === $p ? 'selected' : '' ?>>
                <?= ucfirst($p) ?>
            </option>
            <?php endforeach; ?>
        </select>

        <select name="type" class="border border-gray-300 rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
            <option value="">Semua Tipe</option>
            <option value="corrective" <?= ($filters['type'] ?? '') === 'corrective' ? 'selected' : '' ?>>Corrective</option>
            <option value="preventive" <?= ($filters['type'] ?? '') === 'preventive' ? 'selected' : '' ?>>Preventive</option>
            <option value="inspection" <?= ($filters['type'] ?? '') === 'inspection' ? 'selected' : '' ?>>Inspection</option>
            <option value="kalibrasi_alat" <?= ($filters['type'] ?? '') === 'kalibrasi_alat' ? 'selected' : '' ?>>Kalibrasi Alat</option>
        </select>

        <select name="category_wo" class="border border-gray-300 rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
            <option value="">Semua Kategori</option>
            <?php foreach ($categories_wo as $cat): ?>
            <option value="<?= esc($cat) ?>" <?= ($filters['category_wo'] ?? '') === $cat ? 'selected' : '' ?>><?= esc($cat) ?></option>
            <?php endforeach; ?>
        </select>

        <select name="department_id" class="border border-gray-300 rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
            <option value="">Semua Dept.</option>
            <?php foreach ($departments as $did => $dname): ?>
            <option value="<?= $did ?>" <?= ($filters['department_id'] ?? '') == $did ? 'selected' : '' ?>><?= esc($dname) ?></option>
            <?php endforeach; ?>
        </select>

        <select name="assigned_to" class="border border-gray-300 rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
            <option value="">Semua Teknisi</option>
            <?php foreach ($technicians as $tid => $tname): ?>
            <option value="<?= $tid ?>" <?= ($filters['assigned_to'] ?? '') == $tid ? 'selected' : '' ?>><?= esc($tname) ?></option>
            <?php endforeach; ?>
        </select>

        <div class="flex gap-1.5">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white text-sm px-4 py-1.5 rounded-lg">Filter</button>
            <a href="<?= base_url('admin/work-orders') ?>" class="bg-gray-100 hover:bg-gray-200 text-gray-600 text-sm px-3 py-1.5 rounded-lg">Reset</a>
        </div>

        <label class="flex items-center gap-1.5 text-sm text-red-600 cursor-pointer font-medium">
            <input type="checkbox" name="overdue" value="1"
                   <?= !empty($filters['overdue']) ? 'checked' : '' ?>
                   class="rounded text-red-600">
            ⚠️ Overdue saja
        </label>
    </form>
</div>

<!-- ================================================================
     TABEL WORK ORDER
     ================================================================ -->
<div class="bg-white border rounded-xl shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50 border-b text-gray-600 text-left text-xs uppercase tracking-wide">
                    <th class="px-3 py-3">Kode WO</th>
                    <th class="px-3 py-3">Pelapor / Dept</th>
                    <th class="px-3 py-3">Aset</th>
                    <th class="px-3 py-3">Kategori / Jenis</th>
                    <th class="px-3 py-3">Prioritas</th>
                    <th class="px-3 py-3">Status</th>
                    <th class="px-3 py-3">Teknisi</th>
                    <th class="px-3 py-3">Target / SLA</th>
                    <th class="px-3 py-3 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <?php if (empty($work_orders)): ?>
                <tr>
                    <td colspan="9" class="text-center text-gray-400 py-12">
                        <div class="text-3xl mb-2">📭</div>
                        Tidak ada work order.
                    </td>
                </tr>
                <?php else: ?>
                <?php foreach ($work_orders as $wo):
                    // Overdue check
                    $isOverdue = $wo['target_date']
                        && strtotime($wo['target_date']) < time()
                        && !in_array($wo['status'], ['done', 'cancelled']);
                ?>
                <tr class="hover:bg-gray-50 transition-colors <?= $isOverdue ? 'bg-red-50/30' : '' ?>">

                    <!-- Kode WO + Tanggal -->
                    <td class="px-3 py-2.5">
                        <a href="<?= base_url('admin/work-orders/' . $wo['id']) ?>"
                           class="font-mono text-blue-600 hover:underline font-semibold text-sm">
                            <?= esc($wo['wo_code']) ?>
                        </a>
                        <?php if ($isOverdue): ?>
                        <span class="ml-1 px-1.5 py-0.5 bg-red-100 text-red-600 text-xs rounded font-semibold">OVERDUE</span>
                        <?php endif; ?>
                        <div class="text-xs text-gray-400 mt-0.5">
                            <?= date('d/m/Y H:i', strtotime($wo['created_at'])) ?>
                        </div>
                        <div class="text-xs text-gray-500 mt-0.5">
                            <?= $wo['type'] === 'kalibrasi_alat' ? 'Kalibrasi Alat' : ucfirst($wo['type'] ?? '-') ?>
                        </div>
                    </td>

                    <!-- Pelapor / Dept -->
                    <td class="px-3 py-2.5 text-xs">
                        <div class="font-medium text-gray-800">
                            <?= esc($wo['reporter_name'] ?? $wo['requested_by_name'] ?? '-') ?>
                        </div>
                        <div class="text-gray-400"><?= esc($wo['department_name'] ?? '-') ?></div>
                        <div class="text-gray-400"><?= esc($wo['location_name'] ?? '') ?></div>
                    </td>

                    <!-- Aset -->
                    <td class="px-3 py-2.5 text-xs min-w-[140px]">
                        <div class="font-medium text-gray-800 truncate max-w-[140px]">
                            <?= esc($wo['asset_name'] ?? '-') ?>
                        </div>
                        <code class="text-gray-400"><?= esc($wo['asset_code'] ?? '') ?></code>
                    </td>

                    <!-- Kategori / Jenis -->
                    <td class="px-3 py-2.5 text-xs">
                        <div class="font-medium text-gray-700"><?= esc($wo['category_wo'] ?? '-') ?></div>
                        <div class="text-gray-400"><?= esc($wo['damage_type'] ?? '') ?></div>
                    </td>

                    <!-- Prioritas -->
                    <td class="px-3 py-2.5">
                        <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold border
                                     <?= $priorityColor[$wo['priority']] ?? 'bg-gray-100 text-gray-600 border-gray-200' ?>">
                            <?= ucfirst($wo['priority'] ?? '-') ?>
                        </span>
                    </td>

                    <!-- Status -->
                    <td class="px-3 py-2.5">
                        <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium
                                     <?= $statusColor[$wo['status']] ?? 'bg-gray-100 text-gray-600' ?>">
                            <?= ucwords(str_replace('_', ' ', $wo['status'])) ?>
                        </span>
                    </td>

                    <!-- Teknisi -->
                    <td class="px-3 py-2.5 text-xs text-gray-600">
                        <?php if ($wo['assigned_to_name']): ?>
                            <span class="font-medium"><?= esc($wo['assigned_to_name']) ?></span>
                        <?php else: ?>
                            <span class="text-gray-400 italic">Belum</span>
                        <?php endif; ?>
                        <?php if ($wo['vendor_name']): ?>
                        <div class="text-gray-400"><?= esc($wo['vendor_name']) ?></div>
                        <?php endif; ?>
                    </td>

                    <!-- Target / SLA -->
                    <td class="px-3 py-2.5 text-xs">
                        <?php if ($wo['target_date']): ?>
                        <div class="<?= $isOverdue ? 'text-red-600 font-semibold' : 'text-gray-700' ?>">
                            <?= date('d/m/Y', strtotime($wo['target_date'])) ?>
                        </div>
                        <?php else: ?>
                        <div class="text-gray-400">-</div>
                        <?php endif; ?>
                        <?php if ($wo['sla_hours']): ?>
                        <div class="text-gray-400">SLA: <?= $wo['sla_hours'] ?>j</div>
                        <?php endif; ?>
                    </td>

                    <!-- Aksi -->
                    <td class="px-3 py-2.5">
                        <div class="flex items-center justify-center gap-1">
                            <a href="<?= base_url('admin/work-orders/' . $wo['id']) ?>"
                               title="Detail" class="p-1.5 rounded-lg bg-blue-50 hover:bg-blue-100 text-blue-600 text-xs">👁</a>
                            <?php 
                            $canEdit = (session()->get('role') === 'admin' || session()->get('role') === 'user' || (session()->get('role') === 'technician' && (int)$wo['assigned_to'] === (int)session()->get('user_id')));
                            if ($canEdit): 
                            ?>
                            <a href="<?= base_url('admin/work-orders/' . $wo['id'] . '/edit') ?>"
                               title="Edit" class="p-1.5 rounded-lg bg-yellow-50 hover:bg-yellow-100 text-yellow-600 text-xs">✏️</a>
                            <?php endif; ?>
                            <?php if (session()->get('role') === 'admin' && in_array($wo['status'], ['open', 'cancelled'])): ?>
                            <button onclick="confirmDelete('<?= base_url('admin/work-orders/' . $wo['id'] . '/delete') ?>', '<?= esc($wo['wo_code']) ?>')"
                                    class="p-1.5 rounded-lg bg-red-50 hover:bg-red-100 text-red-600 text-xs">🗑</button>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Footer Pagination -->
    <div class="flex items-center justify-between px-4 py-3 border-t bg-gray-50 text-sm text-gray-500">
        <span><?= count($work_orders) ?> dari <strong><?= $total_records ?></strong> WO</span>
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

<!-- Delete Modal -->
<div x-data="deleteModal()" x-show="open" x-cloak
     class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-sm p-6">
        <div class="text-center mb-4">
            <div class="text-4xl mb-2">⚠️</div>
            <h3 class="font-bold text-gray-800">Hapus Work Order</h3>
            <p class="text-sm text-gray-500 mt-1">Hapus WO <strong x-text="itemName"></strong>?</p>
        </div>
        <div class="flex gap-3">
            <button @click="open=false" class="flex-1 border border-gray-300 text-gray-700 py-2 rounded-lg text-sm">Batal</button>
            <form :action="actionUrl" method="POST" class="flex-1">
                <?= csrf_field() ?>
                <button type="submit" class="w-full bg-red-600 hover:bg-red-700 text-white py-2 rounded-lg text-sm">Hapus</button>
            </form>
        </div>
    </div>
</div>

<!-- Chart.js + Scripts -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
(function () {
    if (typeof Chart === 'undefined') {
        console.warn('Chart.js was not loaded. Charts will not be initialized.');
        return;
    }
    const trendData = <?= $chart_trend ?>;
    const ctx = document.getElementById('chartTrend');
    if (ctx && trendData.labels && trendData.labels.length) {
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: trendData.labels,
                datasets: [
                    {
                        label: 'Total WO',
                        data: trendData.total,
                        backgroundColor: 'rgba(59,130,246,0.6)',
                        borderRadius: 4,
                        order: 2,
                    },
                    {
                        label: 'Selesai',
                        data: trendData.selesai,
                        type: 'line',
                        borderColor: '#22c55e',
                        backgroundColor: 'rgba(34,197,94,0.1)',
                        borderWidth: 2,
                        pointRadius: 4,
                        fill: true,
                        tension: 0.4,
                        order: 1,
                    },
                ]
            },
            options: {
                plugins: { legend: { display: false } },
                scales: {
                    x: { ticks: { font: { size: 10 } } },
                    y: { ticks: { font: { size: 10 }, stepSize: 1 }, beginAtZero: true }
                },
                responsive: true,
                maintainAspectRatio: false,
            }
        });
    }
})();

function deleteModal() { return { open: false, actionUrl: '', itemName: '' }; }
function confirmDelete(url, name) {
    const m = Alpine.$data(document.querySelector('[x-data="deleteModal()"]'));
    m.actionUrl = url; m.itemName = name; m.open = true;
}
</script>

<?= $this->endSection() ?>
