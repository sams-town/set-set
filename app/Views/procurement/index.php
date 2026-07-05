<?= $this->extend('inventory/_layout') ?>
<?= $this->section('content') ?>
<?php
$rp  = fn($n) => 'Rp ' . number_format((float)$n, 0, ',', '.');
$ds  = $dash_stats;
?>

<!-- Header -->
<div class="flex items-center justify-between mb-5">
    <div>
        <h1 class="text-xl font-bold text-gray-800">🛒 Procurement</h1>
        <p class="text-sm text-gray-500 mt-0.5">Permintaan → Approval → PO → Inventaris</p>
    </div>
    <a href="<?= base_url('admin/procurement/new') ?>"
       class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-lg shadow-sm">
        + Permintaan Baru
    </a>
</div>

<?php if ($msg = session()->getFlashdata('success')): ?>
<div class="mb-4 px-4 py-3 bg-green-100 text-green-800 rounded-xl text-sm">✅ <?= $msg ?></div>
<?php elseif ($msg = session()->getFlashdata('error')): ?>
<div class="mb-4 px-4 py-3 bg-red-100 text-red-800 rounded-xl text-sm">❌ <?= $msg ?></div>
<?php endif; ?>

<!-- ══ DASHBOARD KPI — 7 KARTU ══════════════════════════════════ -->
<div class="grid grid-cols-2 sm:grid-cols-4 xl:grid-cols-7 gap-3 mb-5">
    <?php
    $kpi = [
        ['label'=>'Request Baru',      'value'=>number_format($ds['draft']+$ds['pending_atasan']),    'icon'=>'📝','border'=>'border-gray-400',  'vcolor'=>'text-gray-700',   'link'=>'?status=pending_atasan'],
        ['label'=>'Approval Pending',  'value'=>number_format($ds['pending_approval']),               'icon'=>'⏳','border'=>'border-yellow-400','vcolor'=>'text-yellow-700', 'link'=>'?status=pending_atasan'],
        ['label'=>'PO Berjalan',       'value'=>number_format($ds['po_berjalan']),                    'icon'=>'🛒','border'=>'border-indigo-400','vcolor'=>'text-indigo-700', 'link'=>'?status=po'],
        ['label'=>'PO Selesai',        'value'=>number_format($ds['po_selesai']),                     'icon'=>'✅','border'=>'border-green-400', 'vcolor'=>'text-green-700',  'link'=>'?status=received'],
        ['label'=>'Nilai Pengadaan',   'value'=>$rp($ds['nilai_pengadaan']),                          'icon'=>'💰','border'=>'border-orange-400','vcolor'=>'text-orange-700', 'link'=>'#'],
        ['label'=>'Lead Time',         'value'=>empty($lead_time) ? '—' : round(array_sum(array_column($lead_time,'avg_lead_time'))/max(1,count($lead_time)),1).' hr','icon'=>'⚡','border'=>'border-purple-400','vcolor'=>'text-purple-700','link'=>'#'],
        ['label'=>'Top Vendor',        'value'=>!empty($top_vendors) ? esc($top_vendors[0]['vendor_name']) : '—', 'icon'=>'🏢','border'=>'border-cyan-400',  'vcolor'=>'text-cyan-700',   'link'=>'#'],
    ];
    foreach ($kpi as $c):
    ?>
    <a href="<?= $c['link'] ?>"
       class="border-l-4 <?= $c['border'] ?> bg-white rounded-xl p-3 flex flex-col gap-1 shadow-sm hover:shadow-md transition-shadow">
        <span class="text-lg"><?= $c['icon'] ?></span>
        <span class="text-lg font-bold <?= $c['vcolor'] ?> leading-tight truncate"><?= $c['value'] ?></span>
        <span class="text-xs font-semibold text-gray-600"><?= $c['label'] ?></span>
    </a>
    <?php endforeach; ?>
</div>

<!-- ══ ROW 2 — TREND + TOP VENDOR ═══════════════════════════════ -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-5">

    <!-- Trend Chart -->
    <div class="lg:col-span-2 bg-white border rounded-xl p-4 shadow-sm">
        <h2 class="text-sm font-bold text-gray-700 mb-3">📈 Trend Permintaan (6 Bulan)</h2>
        <canvas id="chartTrend" style="max-height:180px;"></canvas>
    </div>

    <!-- Top Vendor + Lead Time -->
    <div class="bg-white border rounded-xl shadow-sm overflow-hidden">
        <div class="px-4 py-3 bg-gray-50 border-b">
            <h2 class="text-sm font-bold text-gray-700">🏢 Top Vendor</h2>
        </div>
        <?php if (empty($top_vendors)): ?>
        <p class="text-center text-gray-400 py-6 text-sm">Belum ada data PO</p>
        <?php else: ?>
        <div class="divide-y">
            <?php foreach ($top_vendors as $i => $v): ?>
            <div class="px-4 py-2.5 flex items-center justify-between">
                <div class="flex items-center gap-2 min-w-0">
                    <span class="w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold shrink-0
                                 <?= $i === 0 ? 'bg-yellow-400 text-gray-900' : 'bg-gray-100 text-gray-600' ?>">
                        <?= $i + 1 ?>
                    </span>
                    <div class="min-w-0">
                        <div class="text-sm font-semibold text-gray-800 truncate"><?= esc($v['vendor_name']) ?></div>
                        <div class="text-xs text-gray-400"><?= $v['total_po'] ?> PO</div>
                    </div>
                </div>
                <div class="text-right shrink-0 ml-2">
                    <div class="text-xs font-semibold text-blue-700"><?= $rp($v['total_nilai']) ?></div>
                    <?php if ($v['avg_lead_time']): ?>
                    <div class="text-xs text-gray-400">~<?= $v['avg_lead_time'] ?> hr</div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- ══ ALUR PROSES (visual) ══════════════════════════════════════ -->
<div class="bg-white border rounded-xl p-4 shadow-sm mb-5">
    <p class="text-xs font-bold text-gray-500 uppercase tracking-wide mb-3">Alur Proses Procurement</p>
    <div class="flex items-center gap-1 overflow-x-auto pb-1">
        <?php
        $steps = [
            ['Permintaan','📝'],['Approval Atasan','👤'],['Approval Direktur','🏆'],
            ['RFQ','📋'],['PO','🛒'],['Barang Datang','📦'],['Asset Register','🗃️'],
        ];
        foreach ($steps as $i => [$lbl, $icon]):
            $last = $i === count($steps) - 1;
        ?>
        <div class="flex items-center gap-1 shrink-0">
            <div class="flex flex-col items-center">
                <div class="w-8 h-8 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center text-sm"><?= $icon ?></div>
                <span class="text-xs text-gray-500 mt-1 whitespace-nowrap"><?= $lbl ?></span>
            </div>
            <?php if (!$last): ?>
            <div class="w-6 h-0.5 bg-blue-200 mb-4"></div>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- ══ FILTER ════════════════════════════════════════════════════ -->
<div class="bg-white border rounded-xl px-4 py-3 mb-4 shadow-sm">
    <form method="GET" class="flex flex-wrap gap-2 items-center">
        <input type="text" name="search" value="<?= esc($filters['search'] ?? '') ?>"
               placeholder="Cari kode / judul..."
               class="flex-1 min-w-[180px] border border-gray-300 rounded-lg px-3 py-1.5 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none">

        <select name="status" class="border border-gray-300 rounded-lg px-3 py-1.5 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none">
            <option value="">Semua Status</option>
            <?php foreach ($status_flow as $key => $sf): ?>
            <option value="<?= $key ?>" <?= ($filters['status'] ?? '') === $key ? 'selected' : '' ?>>
                <?= $sf['icon'] ?> <?= $sf['label'] ?>
            </option>
            <?php endforeach; ?>
        </select>

        <select name="urgency" class="border border-gray-300 rounded-lg px-3 py-1.5 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none">
            <option value="">Semua Urgensi</option>
            <?php foreach ($urgency_opts as $key => $u): ?>
            <option value="<?= $key ?>" <?= ($filters['urgency'] ?? '') === $key ? 'selected' : '' ?>><?= $u['label'] ?></option>
            <?php endforeach; ?>
        </select>

        <select name="department_id" class="border border-gray-300 rounded-lg px-3 py-1.5 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none">
            <option value="">Semua Dept.</option>
            <?php foreach ($departments as $did => $dn): ?>
            <option value="<?= $did ?>" <?= ($filters['department_id'] ?? '') == $did ? 'selected' : '' ?>><?= esc($dn) ?></option>
            <?php endforeach; ?>
        </select>

        <div class="flex gap-1.5">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white text-sm px-4 py-1.5 rounded-lg">Filter</button>
            <a href="<?= base_url('admin/procurement') ?>" class="bg-gray-100 hover:bg-gray-200 text-gray-600 text-sm px-3 py-1.5 rounded-lg">Reset</a>
        </div>
    </form>
</div>

<!-- ══ TABEL REQUEST ══════════════════════════════════════════════ -->
<div class="bg-white border rounded-xl shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50 border-b text-xs text-gray-500 uppercase tracking-wide">
                    <th class="px-3 py-3 text-left">Kode</th>
                    <th class="px-3 py-3 text-left">Judul Item</th>
                    <th class="px-3 py-3 text-left">Dept.</th>
                    <th class="px-3 py-3 text-center">Qty</th>
                    <th class="px-3 py-3 text-right">Estimasi</th>
                    <th class="px-3 py-3 text-center">Urgensi</th>
                    <th class="px-3 py-3 text-center">Status</th>
                    <th class="px-3 py-3 text-left">Tanggal</th>
                    <th class="px-3 py-3 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <?php if (empty($requests)): ?>
                <tr><td colspan="9" class="text-center text-gray-400 py-10">
                    <div class="text-3xl mb-2">📭</div>Belum ada permintaan.
                </td></tr>
                <?php else: ?>
                <?php foreach ($requests as $r):
                    $sf = $status_flow[$r['status']] ?? ['label'=>$r['status'],'icon'=>'•','color'=>'bg-gray-100 text-gray-600'];
                    $ug = $urgency_opts[$r['urgency']] ?? ['label'=>$r['urgency'],'color'=>'bg-gray-100 text-gray-600'];
                ?>
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="px-3 py-2.5">
                        <a href="<?= base_url('admin/procurement/' . $r['id']) ?>"
                           class="font-mono text-blue-600 hover:underline font-semibold text-sm">
                            <?= esc($r['request_code']) ?>
                        </a>
                        <div class="text-xs text-gray-400 mt-0.5"><?= esc($r['requested_by_name'] ?? '—') ?></div>
                    </td>
                    <td class="px-3 py-2.5 min-w-[160px]">
                        <div class="font-medium text-gray-800 truncate max-w-[200px]"><?= esc($r['title']) ?></div>
                        <div class="text-xs text-gray-400"><?= esc($r['category'] ?? '—') ?></div>
                    </td>
                    <td class="px-3 py-2.5 text-xs text-gray-600"><?= esc($r['department_name'] ?? '—') ?></td>
                    <td class="px-3 py-2.5 text-center text-sm font-semibold text-gray-700">
                        <?= number_format($r['quantity']) ?>
                        <span class="text-xs text-gray-400 font-normal"><?= esc($r['unit']) ?></span>
                    </td>
                    <td class="px-3 py-2.5 text-right text-xs text-gray-700">
                        <?= $r['total_estimated'] ? $rp($r['total_estimated']) : '—' ?>
                    </td>
                    <td class="px-3 py-2.5 text-center">
                        <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium <?= $ug['color'] ?>">
                            <?= $ug['label'] ?>
                        </span>
                    </td>
                    <td class="px-3 py-2.5 text-center">
                        <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium <?= $sf['color'] ?>">
                            <?= $sf['icon'] ?> <?= $sf['label'] ?>
                        </span>
                    </td>
                    <td class="px-3 py-2.5 text-xs text-gray-500">
                        <?= date('d/m/Y', strtotime($r['created_at'])) ?>
                        <?php if ($r['target_date']): ?>
                        <div class="text-gray-400">Target: <?= date('d/m/Y', strtotime($r['target_date'])) ?></div>
                        <?php endif; ?>
                    </td>
                    <td class="px-3 py-2.5 text-center">
                        <a href="<?= base_url('admin/procurement/' . $r['id']) ?>"
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
        <span><?= count($requests) ?> dari <strong><?= $total_records ?></strong> permintaan</span>
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
(function(){
    const t = <?= $chart_trend ?>;
    const ctx = document.getElementById('chartTrend');
    if (ctx && t.labels?.length) {
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: t.labels,
                datasets: [
                    { label: 'Request', data: t.total, backgroundColor: 'rgba(59,130,246,0.6)', borderRadius: 3, order: 2 },
                    { label: 'Nilai', data: t.nilai, type: 'line',
                      borderColor: '#f97316', backgroundColor: 'transparent',
                      borderWidth: 2, pointRadius: 4, tension: 0.4, yAxisID: 'y2', order: 1 },
                ]
            },
            options: {
                plugins: { legend: { display: false } },
                scales: {
                    x: { ticks: { font: { size: 10 } } },
                    y:  { ticks: { font: { size: 10 }, stepSize: 1 }, beginAtZero: true },
                    y2: { position: 'right', ticks: { font: { size: 10 }, callback: v => 'Rp'+(v/1000000).toFixed(1)+'jt' },
                          beginAtZero: true, grid: { display: false } },
                },
                responsive: true, maintainAspectRatio: false,
            }
        });
    }
})();
</script>

<?= $this->endSection() ?>
