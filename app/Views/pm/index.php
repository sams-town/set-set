<?= $this->extend('inventory/_layout') ?>
<?= $this->section('content') ?>
<?php
$rp = fn($n) => 'Rp ' . number_format((float)$n, 0, ',', '.');
$pct = fn($n) => number_format((float)$n, 1) . '%';
$ds = $dash_stats;
?>

<!-- Page Header -->
<div class="flex items-center justify-between mb-5">
    <div>
        <h1 class="text-xl font-bold text-gray-800">🛡️ Preventive Maintenance</h1>
        <p class="text-sm text-gray-500 mt-0.5">Schedule PM Recurring & Monitoring</p>
    </div>
    <?php if (session()->get('role') !== 'technician'): ?>
    <a href="<?= base_url('admin/pm/new') ?>"
       class="inline-flex items-colors gap-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors shadow-sm">
        + Buat Schedule PM
    </a>
    <?php endif; ?>
</div>

<!-- ══════════════════════════════════════════════════════════════
     DASHBOARD KPI — 7 KARTU
     ══════════════════════════════════════════════════════════════ -->
<div class="grid grid-cols-2 sm:grid-cols-4 xl:grid-cols-7 gap-3 mb-5">
    <?php
    $kpiCards = [
        [
            'label'  => 'PM Due Today',
            'value'  => number_format($ds['pm_due_today']),
            'icon'   => '🔴',
            'color'  => 'border-red-500 bg-red-50',
            'vcolor' => 'text-red-700',
            'link'   => '?status=due_today',
        ],
        [
            'label'  => 'PM This Week',
            'value'  => number_format($ds['pm_this_week']),
            'icon'   => '📅',
            'color'  => 'border-orange-400 bg-orange-50',
            'vcolor' => 'text-orange-700',
            'link'   => '?status=this_week',
        ],
        [
            'label'  => 'PM Completed',
            'value'  => number_format($ds['pm_completed']),
            'icon'   => '✅',
            'color'  => 'border-green-500 bg-green-50',
            'vcolor' => 'text-green-700',
            'link'   => '#',
        ],
        [
            'label'  => 'PM Missed',
            'value'  => number_format($ds['pm_missed']),
            'icon'   => '⚠️',
            'color'  => 'border-yellow-400 bg-yellow-50',
            'vcolor' => 'text-yellow-700',
            'link'   => '?status=overdue',
        ],
        [
            'label'  => 'Compliance %',
            'value'  => $pct($ds['compliance']),
            'icon'   => '🎯',
            'color'  => ($ds['compliance'] >= 80) ? 'border-green-500 bg-green-50' : 'border-red-400 bg-red-50',
            'vcolor' => ($ds['compliance'] >= 80) ? 'text-green-700' : 'text-red-700',
            'link'   => '#',
        ],
        [
            'label'  => 'Equipment Critical',
            'value'  => number_format($ds['equipment_critical']),
            'icon'   => '🔧',
            'color'  => 'border-purple-400 bg-purple-50',
            'vcolor' => 'text-purple-700',
            'link'   => '?priority=kritis',
        ],
        [
            'label'  => 'Total Active Schedule',
            'value'  => number_format($ds['total_active']),
            'icon'   => '📊',
            'color'  => 'border-blue-400 bg-blue-50',
            'vcolor' => 'text-blue-700',
            'link'   => '?is_active=1',
        ],
    ];
    foreach ($kpiCards as $c):
    ?>
    <a href="<?= $c['link'] ?>"
       class="border-l-4 <?= $c['color'] ?> rounded-xl p-3 flex flex-col gap-1 shadow-sm hover:shadow-md transition-shadow">
        <span class="text-lg"><?= $c['icon'] ?></span>
        <span class="text-xl font-bold <?= $c['vcolor'] ?> leading-tight mt-1"><?= $c['value'] ?></span>
        <span class="text-xs font-semibold text-gray-600 leading-tight"><?= $c['label'] ?></span>
    </a>
    <?php endforeach; ?>
</div>

<!-- ══════════════════════════════════════════════════════════════
     ROW 2 — BREAKDOWN CHART + CALENDAR MINI
     ══════════════════════════════════════════════════════════════ -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-5">

    <!-- Breakdown Recurring -->
    <div class="bg-white border rounded-xl p-4 shadow-sm">
        <h2 class="text-sm font-bold text-gray-700 mb-3">Distribusi Schedule PM</h2>
        <canvas id="chartBreakdown" style="max-height:180px;"></canvas>
    </div>

    <!-- Calendar 30 Hari (Simple List) -->
    <div class="lg:col-span-2 bg-white border rounded-xl shadow-sm overflow-hidden">
        <div class="px-4 py-3 bg-gray-50 border-b flex items-center justify-between">
            <h2 class="text-sm font-bold text-gray-700">📅 Schedule Calendar (30 Hari)</h2>
            <span class="text-xs text-gray-400"><?= count($calendar_data) ?> PM terjadwal</span>
        </div>
        <?php if (empty($calendar_data)): ?>
        <div class="text-center py-8 text-gray-400 text-sm">Tidak ada PM dalam 30 hari ke depan</div>
        <?php else: ?>
        <div class="divide-y max-h-64 overflow-y-auto">
            <?php foreach ($calendar_data as $pm):
                $status = \App\Models\PreventiveMaintenanceModel::getDueStatus($pm['next_due']);
            ?>
            <a href="<?= base_url('admin/pm/'.$pm['id']) ?>"
               class="flex items-center justify-between px-4 py-2.5 hover:bg-gray-50 transition-colors">
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2">
                        <span class="text-lg"><?= $status['icon'] ?></span>
                        <span class="font-semibold text-gray-800 text-sm truncate"><?= esc($pm['asset_name']) ?></span>
                        <code class="text-xs text-gray-400"><?= esc($pm['asset_code']) ?></code>
                    </div>
                    <p class="text-xs text-gray-500 mt-0.5 truncate"><?= esc($pm['title']) ?></p>
                    <?php if (!empty($pm['assigned_to_name'])): ?>
                    <p class="text-xs text-gray-400 mt-0.5">Teknisi: <?= esc($pm['assigned_to_name']) ?></p>
                    <?php endif; ?>
                </div>
                <div class="text-right ml-3 shrink-0">
                    <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium <?= $status['badge'] ?>">
                        <?= $status['label'] ?>
                    </span>
                    <p class="text-xs text-gray-400 mt-1"><?= date('d M Y', strtotime($pm['next_due'])) ?></p>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- ══════════════════════════════════════════════════════════════
     FILTER BAR
     ══════════════════════════════════════════════════════════════ -->
<div class="bg-white border rounded-xl px-4 py-3 mb-4 shadow-sm">
    <form method="GET" class="flex flex-wrap gap-2 items-center">
        <input type="text" name="search"
               value="<?= esc($filters['search'] ?? '') ?>"
               placeholder="Cari judul / aset..."
               class="flex-1 min-w-[180px] border border-gray-300 rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">

        <select name="recurring" class="border border-gray-300 rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
            <option value="">Semua Recurring</option>
            <?php foreach ($recurring_opts as $key => $label): ?>
            <option value="<?= $key ?>" <?= ($filters['recurring'] ?? '') === $key ? 'selected' : '' ?>><?= $label ?></option>
            <?php endforeach; ?>
        </select>

        <select name="schedule_type" class="border border-gray-300 rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
            <option value="">Semua Tipe Jadwal</option>
            <option value="pm" <?= ($filters['schedule_type'] ?? '') === 'pm' ? 'selected' : '' ?>>Preventive PM</option>
            <option value="calibration" <?= ($filters['schedule_type'] ?? '') === 'calibration' ? 'selected' : '' ?>>Kalibrasi Alat</option>
        </select>

        <select name="priority" class="border border-gray-300 rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
            <option value="">Semua Prioritas</option>
            <?php foreach (['rendah','sedang','tinggi','kritis'] as $p): ?>
            <option value="<?= $p ?>" <?= ($filters['priority'] ?? '') === $p ? 'selected' : '' ?>><?= ucfirst($p) ?></option>
            <?php endforeach; ?>
        </select>

        <select name="status" class="border border-gray-300 rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
            <option value="">Semua Status</option>
            <option value="overdue"   <?= ($filters['status'] ?? '') === 'overdue'   ? 'selected' : '' ?>>Overdue</option>
            <option value="due_today" <?= ($filters['status'] ?? '') === 'due_today' ? 'selected' : '' ?>>Due Hari Ini</option>
            <option value="this_week" <?= ($filters['status'] ?? '') === 'this_week' ? 'selected' : '' ?>>This Week</option>
        </select>

        <select name="assigned_to" class="border border-gray-300 rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
            <option value="">Semua Teknisi</option>
            <?php foreach ($technicians as $tid => $tname): ?>
            <option value="<?= $tid ?>" <?= ($filters['assigned_to'] ?? '') == $tid ? 'selected' : '' ?>><?= esc($tname) ?></option>
            <?php endforeach; ?>
        </select>

        <div class="flex gap-1.5">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white text-sm px-4 py-1.5 rounded-lg">Filter</button>
            <a href="<?= base_url('admin/pm') ?>" class="bg-gray-100 hover:bg-gray-200 text-gray-600 text-sm px-3 py-1.5 rounded-lg">Reset</a>
        </div>
    </form>
</div>

<!-- ══════════════════════════════════════════════════════════════
     TABEL SCHEDULE PM
     ══════════════════════════════════════════════════════════════ -->
<div class="bg-white border rounded-xl shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50 border-b text-gray-600 text-left text-xs uppercase tracking-wide">
                    <th class="px-3 py-3">Aset</th>
                    <th class="px-3 py-3">Tipe</th>
                    <th class="px-3 py-3">Judul PM</th>
                    <th class="px-3 py-3">Recurring</th>
                    <th class="px-3 py-3">Teknisi</th>
                    <th class="px-3 py-3">Prioritas</th>
                    <th class="px-3 py-3">Last Done</th>
                    <th class="px-3 py-3">Next Due</th>
                    <th class="px-3 py-3 text-center">Status</th>
                    <th class="px-3 py-3 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <?php if (empty($schedules)): ?>
                <tr>
                    <td colspan="10" class="text-center text-gray-400 py-12">
                        <div class="text-3xl mb-2">📋</div>Tidak ada schedule PM.
                    </td>
                </tr>
                <?php else: ?>
                <?php foreach ($schedules as $pm):
                    $status = \App\Models\PreventiveMaintenanceModel::getDueStatus($pm['next_due']);
                    $priorityColor = \App\Models\PreventiveMaintenanceModel::PRIORITY_COLORS;
                ?>
                <tr class="hover:bg-gray-50 transition-colors <?= $status['status'] === 'overdue' ? 'bg-red-50/30' : '' ?>">
                    
                    <!-- Aset -->
                    <td class="px-3 py-2.5 text-xs min-w-[140px]">
                        <div class="font-medium text-gray-800 truncate"><?= esc($pm['asset_name'] ?? '-') ?></div>
                        <code class="text-gray-400"><?= esc($pm['asset_code'] ?? '') ?></code>
                        <div class="text-gray-400"><?= esc($pm['department_name'] ?? '') ?></div>
                    </td>
                    
                    <!-- Tipe Jadwal -->
                    <td class="px-3 py-2.5 text-xs">
                        <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold <?= ($pm['schedule_type'] ?? 'pm') === 'calibration' ? 'bg-teal-100 text-teal-700' : 'bg-blue-100 text-blue-700' ?>">
                            <?= ($pm['schedule_type'] ?? 'pm') === 'calibration' ? '🔬 Kalibrasi' : '🛠️ PM' ?>
                        </span>
                    </td>

                    <!-- Judul PM -->
                    <td class="px-3 py-2.5 min-w-[160px]">
                        <a href="<?= base_url('admin/pm/'.$pm['id']) ?>"
                           class="font-semibold text-blue-600 hover:underline text-sm block truncate">
                            <?= esc($pm['title']) ?>
                        </a>
                        <?php if (!empty($pm['description'])): ?>
                        <p class="text-xs text-gray-400 mt-0.5 truncate"><?= esc(substr($pm['description'], 0, 60)) ?></p>
                        <?php endif; ?>
                    </td>

                    <!-- Recurring -->
                    <td class="px-3 py-2.5 text-xs">
                        <span class="font-medium text-gray-800">
                            <?= \App\Models\PreventiveMaintenanceModel::RECURRING_LABELS[$pm['recurring']] ?? '-' ?>
                        </span>
                        <div class="text-gray-400"><?= $pm['interval_days'] ?> hari</div>
                    </td>

                    <!-- Teknisi -->
                    <td class="px-3 py-2.5 text-xs text-gray-600">
                        <?= $pm['assigned_to_name'] ? '<span class="font-medium">'.esc($pm['assigned_to_name']).'</span>' : '<span class="text-gray-400 italic">Belum</span>' ?>
                    </td>

                    <!-- Prioritas -->
                    <td class="px-3 py-2.5">
                        <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold <?= $priorityColor[$pm['priority']] ?? 'bg-gray-100 text-gray-600' ?>">
                            <?= ucfirst($pm['priority']) ?>
                        </span>
                    </td>

                    <!-- Last Done -->
                    <td class="px-3 py-2.5 text-xs text-gray-600">
                        <?= $pm['last_done'] ? date('d M Y', strtotime($pm['last_done'])) : '<span class="text-gray-400">-</span>' ?>
                    </td>

                    <!-- Next Due -->
                    <td class="px-3 py-2.5 text-xs">
                        <div class="<?= $status['status'] === 'overdue' ? 'text-red-600 font-semibold' : 'text-gray-700' ?>">
                            <?= date('d M Y', strtotime($pm['next_due'])) ?>
                        </div>
                    </td>

                    <!-- Status Badge -->
                    <td class="px-3 py-2.5 text-center">
                        <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium <?= $status['badge'] ?>">
                            <?= $status['icon'] ?> <?= $status['days'] !== null ? abs($status['days']).'h' : '' ?>
                        </span>
                    </td>

                    <!-- Aksi -->
                    <td class="px-3 py-2.5">
                        <div class="flex items-center justify-center gap-1">
                            <a href="<?= base_url('admin/pm/'.$pm['id']) ?>"
                               title="Detail" class="p-1.5 rounded-lg bg-blue-50 hover:bg-blue-100 text-blue-600 text-xs">👁</a>
                            <a href="<?= base_url('admin/pm/'.$pm['id'].'/edit') ?>"
                               title="Edit" class="p-1.5 rounded-lg bg-yellow-50 hover:bg-yellow-100 text-yellow-600 text-xs">✏️</a>
                            <?php if (session()->get('role') === 'admin'): ?>
                            <button onclick="confirmDelete('<?= base_url('admin/pm/'.$pm['id'].'/delete') ?>', '<?= esc($pm['title']) ?>')"
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

    <!-- Pagination -->
    <div class="flex items-center justify-between px-4 py-3 border-t bg-gray-50 text-sm text-gray-500">
        <span><?= count($schedules) ?> dari <strong><?= $total_records ?></strong> schedule</span>
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
            <h3 class="font-bold text-gray-800">Hapus Schedule PM</h3>
            <p class="text-sm text-gray-500 mt-1">Hapus <strong x-text="itemName"></strong>?</p>
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

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
(function () {
    const breakdownData = <?= $chart_breakdown ?>;
    const ctx = document.getElementById('chartBreakdown');
    if (ctx && breakdownData.labels && breakdownData.labels.length) {
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: breakdownData.labels,
                datasets: [{
                    label: 'Schedule',
                    data: breakdownData.data,
                    backgroundColor: ['#3b82f6','#10b981','#f59e0b','#ef4444','#8b5cf6','#ec4899'],
                    borderRadius: 4,
                }]
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
