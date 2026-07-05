<?= $this->extend('inventory/_layout') ?>
<?= $this->section('content') ?>
<?php
$rp = fn($n) => 'Rp ' . number_format((float)$n, 0, ',', '.');
$fmt = fn($m) => \App\Models\WorkOrderModel::formatMinutes((int)$m);

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

// Tentukan current step untuk progress timeline
$curStep = match($wo['status']) {
    'open'         => empty($wo['assessment_notes']) ? 1 : 2,
    'in_progress'  => 3,
    'waiting_part' => 2,
    'done'         => 5,
    'cancelled'    => 0,
    default        => 1,
};
if ($wo['status'] === 'in_progress' && !empty($wo['photo_after'])) $curStep = 4;
if ($wo['status'] === 'done') $curStep = 5;

$steps = [
    ['icon'=>'📝','label'=>'Keluhan Masuk',  'desc'=>date('d M Y H:i', strtotime($wo['created_at'])), 'step'=>1],
    ['icon'=>'🔍','label'=>'Assessment',     'desc'=>!empty($wo['assessment_notes']) ? 'Teknisi sudah assessment' : 'Menunggu assessment', 'step'=>2],
    ['icon'=>'🔧','label'=>'Pengerjaan',     'desc'=>$wo['start_date'] ? date('d M Y', strtotime($wo['start_date'])) : 'Belum dimulai', 'step'=>3],
    ['icon'=>'🧪','label'=>'Testing',        'desc'=>!empty($wo['photo_after']) ? 'Foto after tersedia' : 'Belum ada foto after', 'step'=>4],
    ['icon'=>'✅','label'=>'Close WO',       'desc'=>$wo['finish_date'] ? date('d M Y', strtotime($wo['finish_date'])) : ($wo['status']==='cancelled' ? 'Dibatalkan' : 'Belum selesai'), 'step'=>5],
];
?>

<!-- Breadcrumb -->
<div class="flex items-center gap-2 text-sm text-gray-500 mb-4">
    <a href="<?= base_url('admin/work-orders') ?>" class="hover:text-blue-600">Work Order</a>
    <span>›</span>
    <span class="font-mono font-medium text-gray-800"><?= esc($wo['wo_code']) ?></span>
</div>

<!-- Header -->
<div class="flex items-start justify-between mb-5 gap-4 flex-wrap">
    <div>
        <h1 class="text-xl font-bold text-gray-800 flex items-center gap-2">
            <?= esc($wo['wo_code']) ?>
            <?php if ($wo['status'] !== 'done' && $wo['status'] !== 'cancelled' && !empty($wo['target_date']) && strtotime($wo['target_date']) < time()): ?>
            <span class="text-sm font-semibold px-2 py-0.5 bg-red-100 text-red-600 rounded-full">⚠️ OVERDUE</span>
            <?php endif; ?>
        </h1>
        <div class="flex gap-2 mt-1.5 flex-wrap">
            <span class="px-2.5 py-1 rounded-full text-xs font-semibold <?= $statusColor[$wo['status']] ?? 'bg-gray-100' ?>">
                <?= ucwords(str_replace('_', ' ', $wo['status'])) ?>
            </span>
            <span class="px-2.5 py-1 rounded-full text-xs font-semibold border <?= $priorityColor[$wo['priority']] ?? 'bg-gray-100' ?>">
                <?= ucfirst($wo['priority']) ?> Priority
            </span>
            <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-700">
                <?= ucfirst($wo['type']) ?>
            </span>
            <?php if (!empty($wo['category_wo'])): ?>
            <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-600">
                <?= esc($wo['category_wo']) ?>
            </span>
            <?php endif; ?>
        </div>
    </div>
    <div class="flex gap-2 flex-wrap">
        <a href="<?= base_url('admin/work-orders/' . $wo['id'] . '/edit') ?>"
           class="inline-flex items-center gap-1.5 bg-yellow-500 hover:bg-yellow-600 text-white text-sm font-medium px-3 py-2 rounded-lg transition-colors">
            ✏️ Edit
        </a>
        <?php if (in_array($wo['status'], ['open','cancelled'])): ?>
        <button onclick="confirmDelete('<?= base_url('admin/work-orders/' . $wo['id'] . '/delete') ?>', '<?= esc($wo['wo_code']) ?>')"
                class="inline-flex items-center gap-1.5 bg-red-600 hover:bg-red-700 text-white text-sm font-medium px-3 py-2 rounded-lg transition-colors">
            🗑 Hapus
        </button>
        <?php endif; ?>
    </div>
</div>

<!-- ══════════════════════════════════════════════════════════════
     TIMELINE PROGRESS (5 TAHAP)
     ══════════════════════════════════════════════════════════════ -->
<div class="bg-white border rounded-xl p-5 shadow-sm mb-5">
    <h2 class="text-sm font-bold text-gray-700 mb-4">🚀 Alur Progress Work Order</h2>
    <div class="flex items-center gap-2 overflow-x-auto pb-2">
        <?php foreach ($steps as $i => $st):
            $active = $st['step'] <= $curStep || $wo['status'] === 'cancelled';
            $last   = $i === count($steps) - 1;
        ?>
        <div class="flex items-center gap-2 shrink-0">
            <div class="flex flex-col items-center">
                <div class="w-10 h-10 rounded-full flex items-center justify-center text-lg font-bold transition-all
                            <?= $active ? 'bg-blue-600 text-white shadow-md' : 'bg-gray-100 text-gray-400' ?>">
                    <?= $st['icon'] ?>
                </div>
                <div class="mt-2 text-center max-w-[90px]">
                    <p class="text-xs font-semibold <?= $active ? 'text-blue-700' : 'text-gray-400' ?> leading-tight">
                        <?= $st['label'] ?>
                    </p>
                    <p class="text-xs text-gray-400 mt-0.5 leading-tight"><?= $st['desc'] ?></p>
                </div>
            </div>
            <?php if (!$last): ?>
            <div class="w-10 h-1 mb-12 rounded-full transition-all <?= $curStep > $st['step'] ? 'bg-blue-400' : 'bg-gray-200' ?>"></div>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

    <!-- ══ KOLOM KIRI (2/3) ══════════════════════════════════════ -->
    <div class="lg:col-span-2 space-y-5">

        <!-- CARD 1: Detail Keluhan -->
        <div class="bg-white border rounded-xl shadow-sm overflow-hidden">
            <div class="px-4 py-3 bg-gray-50 border-b flex items-center gap-2">
                <span>📝</span>
                <h2 class="text-sm font-bold text-gray-700">Detail Keluhan</h2>
            </div>
            <div class="p-4">
                <div class="grid grid-cols-2 gap-4 text-sm mb-4">
                    <div>
                        <p class="text-xs text-gray-400 mb-0.5">Aset</p>
                        <a href="<?= base_url('admin/inventory/'.$wo['asset_id']) ?>"
                           class="font-semibold text-blue-600 hover:underline">
                            <?= esc($wo['asset_name'] ?? '-') ?>
                        </a>
                        <code class="text-xs text-gray-400 block mt-0.5"><?= esc($wo['asset_code'] ?? '') ?></code>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 mb-0.5">Pelapor</p>
                        <p class="font-semibold text-gray-800"><?= esc($wo['reporter_name'] ?? $wo['requested_by_name'] ?? '-') ?></p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 mb-0.5">Departemen</p>
                        <p class="font-semibold text-gray-800"><?= esc($wo['department_name'] ?? '-') ?></p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 mb-0.5">Lokasi</p>
                        <p class="font-semibold text-gray-800">
                            <?= esc($wo['location_name'] ?? '-') ?>
                            <?= $wo['location_building'] ? '<span class="text-gray-400"> · '.esc($wo['location_building']).'</span>' : '' ?>
                        </p>
                    </div>
                    <?php if (!empty($wo['damage_type'])): ?>
                    <div>
                        <p class="text-xs text-gray-400 mb-0.5">Jenis Kerusakan</p>
                        <p class="font-semibold text-gray-800"><?= esc($wo['damage_type']) ?></p>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Deskripsi Masalah -->
                <div class="bg-red-50 border border-red-100 rounded-lg p-3 mb-3">
                    <p class="text-xs text-red-600 font-medium mb-1">Deskripsi Masalah</p>
                    <p class="text-sm text-gray-800 leading-relaxed"><?= nl2br(esc($wo['problem_desc'])) ?></p>
                </div>

                <!-- Foto Keluhan -->
                <?php if (!empty($wo['photo_complaint'])): ?>
                <div class="border rounded-lg p-3">
                    <p class="text-xs text-gray-500 mb-2">📸 Foto Keluhan dari User</p>
                    <img src="<?= base_url('uploads/work_orders/'.$wo['photo_complaint']) ?>"
                         alt="Foto Keluhan"
                         class="w-full max-h-64 object-cover rounded-lg border shadow-sm">
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- CARD 2: Assessment Teknisi -->
        <?php if (!empty($wo['assessment_notes']) || !empty($wo['assigned_to_name'])): ?>
        <div class="bg-white border rounded-xl shadow-sm overflow-hidden">
            <div class="px-4 py-3 bg-gray-50 border-b flex items-center gap-2">
                <span>🔍</span>
                <h2 class="text-sm font-bold text-gray-700">Assessment Teknisi</h2>
            </div>
            <div class="p-4">
                <?php if (!empty($wo['assigned_to_name'])): ?>
                <div class="flex items-center gap-2 mb-3">
                    <span class="text-xs text-gray-400">Teknisi:</span>
                    <span class="font-semibold text-gray-800"><?= esc($wo['assigned_to_name']) ?></span>
                    <?php if (!empty($wo['assigned_to_phone'])): ?>
                    <span class="text-xs text-gray-400">· <?= esc($wo['assigned_to_phone']) ?></span>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
                <?php if (!empty($wo['vendor_name'])): ?>
                <div class="flex items-center gap-2 mb-3">
                    <span class="text-xs text-gray-400">Vendor:</span>
                    <span class="font-semibold text-gray-800"><?= esc($wo['vendor_name']) ?></span>
                </div>
                <?php endif; ?>
                <?php if (!empty($wo['assessment_notes'])): ?>
                <div class="bg-blue-50 border border-blue-100 rounded-lg p-3">
                    <p class="text-xs text-blue-600 font-medium mb-1">Hasil Assessment</p>
                    <p class="text-sm text-gray-800 leading-relaxed"><?= nl2br(esc($wo['assessment_notes'])) ?></p>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- CARD 3: Pengerjaan & Foto Before/After -->
        <?php if (!empty($wo['photo_before']) || !empty($wo['photo_after']) || !empty($wo['action_taken'])): ?>
        <div class="bg-white border rounded-xl shadow-sm overflow-hidden">
            <div class="px-4 py-3 bg-gray-50 border-b flex items-center gap-2">
                <span>🔧</span>
                <h2 class="text-sm font-bold text-gray-700">Pengerjaan, Testing & Dokumentasi</h2>
            </div>
            <div class="p-4">
                <?php if (!empty($wo['action_taken'])): ?>
                <div class="bg-green-50 border border-green-100 rounded-lg p-3 mb-4">
                    <p class="text-xs text-green-600 font-medium mb-1">Tindakan yang Diambil</p>
                    <p class="text-sm text-gray-800 leading-relaxed"><?= nl2br(esc($wo['action_taken'])) ?></p>
                </div>
                <?php endif; ?>

                <!-- Grid Foto Before/After -->
                <div class="grid grid-cols-2 gap-4">
                    <!-- Before -->
                    <div class="bg-orange-50 border border-orange-100 rounded-xl p-3">
                        <p class="text-xs text-orange-600 font-semibold mb-2">📷 Sebelum Perbaikan</p>
                        <?php if (!empty($wo['photo_before'])): ?>
                        <img src="<?= base_url('uploads/work_orders/'.$wo['photo_before']) ?>"
                             alt="Before" class="w-full h-40 object-cover rounded-lg border shadow-sm">
                        <?php else: ?>
                        <div class="w-full h-40 bg-orange-100 rounded-lg flex items-center justify-center text-orange-400 text-sm">
                            Belum ada foto
                        </div>
                        <?php endif; ?>
                    </div>

                    <!-- After -->
                    <div class="bg-green-50 border border-green-100 rounded-xl p-3">
                        <p class="text-xs text-green-600 font-semibold mb-2">📷 Sesudah Testing</p>
                        <?php if (!empty($wo['photo_after'])): ?>
                        <img src="<?= base_url('uploads/work_orders/'.$wo['photo_after']) ?>"
                             alt="After" class="w-full h-40 object-cover rounded-lg border shadow-sm">
                        <?php else: ?>
                        <div class="w-full h-40 bg-green-100 rounded-lg flex items-center justify-center text-green-400 text-sm">
                            Belum ada foto
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- CARD 4: Material & Biaya -->
        <?php if (!empty($wo['material_used']) || ($wo['material_cost'] > 0) || ($wo['labor_cost'] > 0)): ?>
        <div class="bg-white border rounded-xl shadow-sm overflow-hidden">
            <div class="px-4 py-3 bg-gray-50 border-b flex items-center gap-2">
                <span>💰</span>
                <h2 class="text-sm font-bold text-gray-700">Material & Biaya</h2>
            </div>
            <div class="p-4">
                <?php if (!empty($wo['material_used'])): ?>
                <div class="mb-3">
                    <p class="text-xs text-gray-400 mb-1">Material / Spare Part</p>
                    <p class="text-sm text-gray-800 leading-relaxed"><?= nl2br(esc($wo['material_used'])) ?></p>
                </div>
                <?php endif; ?>
                <div class="grid grid-cols-3 gap-3 text-sm">
                    <div class="bg-gray-50 rounded-lg p-3">
                        <p class="text-xs text-gray-400 mb-1">Biaya Material</p>
                        <p class="font-bold text-gray-800"><?= $rp($wo['material_cost'] ?? 0) ?></p>
                    </div>
                    <div class="bg-gray-50 rounded-lg p-3">
                        <p class="text-xs text-gray-400 mb-1">Biaya Jasa</p>
                        <p class="font-bold text-gray-800"><?= $rp($wo['labor_cost'] ?? 0) ?></p>
                    </div>
                    <div class="bg-blue-50 rounded-lg p-3">
                        <p class="text-xs text-gray-400 mb-1">Total Biaya</p>
                        <p class="font-bold text-blue-700"><?= $rp($wo['cost'] ?? 0) ?></p>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- ══ KOLOM KANAN (1/3) ═════════════════════════════════════ -->
    <div class="space-y-5">

        <!-- SLA Status -->
        <div class="bg-white border rounded-xl shadow-sm overflow-hidden">
            <div class="px-4 py-3 bg-gray-50 border-b">
                <h2 class="text-sm font-bold text-gray-700">🎯 SLA Status</h2>
            </div>
            <div class="p-4 <?= $sla_status['bg'] ?>">
                <div class="flex items-center justify-between mb-2">
                    <p class="text-sm font-semibold <?= $sla_status['color'] ?>">
                        <?= $sla_status['label'] ?>
                    </p>
                    <div class="text-2xl">
                        <?= $sla_status['met'] ? '✅' : '⚠️' ?>
                    </div>
                </div>
                <p class="text-xs text-gray-500">Target: <?= $sla_status['deadline'] ?></p>
                <?php if (!empty($sla_status['hours_left'])): ?>
                <p class="text-xs text-gray-400 mt-1">
                    <?= $sla_status['hours_left'] < 0 ? 'Terlambat' : 'Tersisa' ?>
                    <?= abs(round($sla_status['hours_left'], 1)) ?> jam
                </p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Info Waktu -->
        <div class="bg-white border rounded-xl p-4 shadow-sm space-y-3">
            <h2 class="text-sm font-bold text-gray-700 pb-2 border-b">⏱️ Info Waktu</h2>
            <?php if ($wo['response_time']): ?>
            <div>
                <p class="text-xs text-gray-400 mb-0.5">Response Time</p>
                <p class="font-semibold text-gray-800"><?= $fmt($wo['response_time']) ?></p>
            </div>
            <?php endif; ?>
            <?php if ($wo['repair_time']): ?>
            <div>
                <p class="text-xs text-gray-400 mb-0.5">Repair Time</p>
                <p class="font-semibold text-gray-800"><?= $fmt($wo['repair_time']) ?></p>
            </div>
            <?php endif; ?>
            <div>
                <p class="text-xs text-gray-400 mb-0.5">Target SLA</p>
                <p class="font-semibold text-gray-800"><?= ($wo['sla_hours'] ?? 24) ?> Jam</p>
            </div>
            <?php if ($wo['scheduled_date']): ?>
            <div>
                <p class="text-xs text-gray-400 mb-0.5">Jadwal</p>
                <p class="font-semibold text-gray-800"><?= date('d M Y', strtotime($wo['scheduled_date'])) ?></p>
            </div>
            <?php endif; ?>
        </div>

        <!-- Catatan -->
        <?php if (!empty($wo['notes'])): ?>
        <div class="bg-white border rounded-xl p-4 shadow-sm">
            <h2 class="text-sm font-bold text-gray-700 mb-2 pb-2 border-b">📌 Catatan</h2>
            <p class="text-sm text-gray-700 leading-relaxed"><?= nl2br(esc($wo['notes'])) ?></p>
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

<script>
function deleteModal() { return { open: false, actionUrl: '', itemName: '' }; }
function confirmDelete(url, name) {
    const m = Alpine.$data(document.querySelector('[x-data="deleteModal()"]'));
    m.actionUrl = url; m.itemName = name; m.open = true;
}
</script>

<?= $this->endSection() ?>
