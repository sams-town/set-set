<?= $this->extend('inventory/_layout') ?>
<?= $this->section('content') ?>
<?php
$rp = fn($n) => 'Rp ' . number_format((float)$n, 0, ',', '.');
$recurringLabel = \App\Models\PreventiveMaintenanceModel::RECURRING_LABELS;
$priorityColor  = \App\Models\PreventiveMaintenanceModel::PRIORITY_COLORS;
?>

<!-- Breadcrumb -->
<div class="flex items-center gap-2 text-sm text-gray-500 mb-4">
    <a href="<?= base_url('admin/pm') ?>" class="hover:text-blue-600">PM</a>
    <span>›</span>
    <a href="<?= base_url('admin/inventory/'.$schedule['asset_id']) ?>" class="hover:text-blue-600">
        <?= esc($schedule['asset_name']) ?>
    </a>
    <span>›</span>
    <span class="font-medium text-gray-800"><?= esc($schedule['title']) ?></span>
</div>

<!-- Header -->
<div class="flex items-start justify-between mb-5 gap-4 flex-wrap">
    <div>
        <h1 class="text-xl font-bold text-gray-800"><?= esc($schedule['title']) ?></h1>
        <div class="flex gap-2 mt-1.5 flex-wrap">
            <span class="px-2.5 py-1 rounded-full text-xs font-semibold <?= $priorityColor[$schedule['priority']] ?? 'bg-gray-100' ?>">
                <?= ucfirst($schedule['priority']) ?> Priority
            </span>
            <span class="px-2.5 py-1 rounded-full text-xs font-semibold <?= $schedule['is_active'] ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' ?>">
                <?= $schedule['is_active'] ? '✅ Aktif' : '❌ Nonaktif' ?>
            </span>
            <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-700">
                🔄 <?= $recurringLabel[$schedule['recurring']] ?? $schedule['recurring'] ?>
            </span>
            <span class="px-2.5 py-1 rounded-full text-xs font-semibold <?= ($schedule['schedule_type'] ?? 'pm') === 'calibration' ? 'bg-teal-100 text-teal-700' : 'bg-blue-100 text-blue-700' ?>">
                <?= ($schedule['schedule_type'] ?? 'pm') === 'calibration' ? '🔬 Kalibrasi Alat' : '🛡️ PM' ?>
            </span>
        </div>
    </div>
    <div class="flex gap-2 flex-wrap">
        <a href="<?= base_url('admin/inventory/'.$schedule['asset_id']) ?>"
           class="inline-flex items-center gap-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium px-3 py-2 rounded-lg">
            🗃️ Lihat Aset
        </a>
        <button onclick="showMarkDoneModal()"
                class="inline-flex items-center gap-1.5 bg-green-600 hover:bg-green-700 text-white text-sm font-medium px-3 py-2 rounded-lg">
            ✅ Mark as Done
        </button>
        <a href="<?= base_url('admin/pm/'.$schedule['id'].'/edit') ?>"
           class="inline-flex items-center gap-1.5 bg-yellow-500 hover:bg-yellow-600 text-white text-sm font-medium px-3 py-2 rounded-lg">
            ✏️ Edit
        </a>
        <?php if (session()->get('role') === 'admin'): ?>
        <button onclick="confirmDelete('<?= base_url('admin/pm/'.$schedule['id'].'/delete') ?>', '<?= esc($schedule['title']) ?>')"
                class="inline-flex items-center gap-1.5 bg-red-600 hover:bg-red-700 text-white text-sm font-medium px-3 py-2 rounded-lg">
            🗑 Hapus
        </button>
        <?php endif; ?>
    </div>
</div>

<!-- Due Status Card -->
<div class="bg-white border rounded-xl p-4 mb-5 shadow-sm <?= $due_status['badge'] ?>">
    <div class="flex items-center justify-between">
        <div>
            <p class="text-sm font-semibold <?= $due_status['badge'] ?> mb-1">
                <?= $due_status['icon'] ?> <?= $due_status['label'] ?>
            </p>
            <p class="text-xs text-gray-500">Next Due: <strong><?= date('d M Y', strtotime($schedule['next_due'])) ?></strong></p>
            <?php if ($schedule['last_done']): ?>
            <p class="text-xs text-gray-400 mt-0.5">Last Done: <?= date('d M Y', strtotime($schedule['last_done'])) ?></p>
            <?php endif; ?>
        </div>
        <div class="text-4xl"><?= $due_status['icon'] ?></div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
    <!-- Kolom Kiri -->
    <div class="lg:col-span-2 space-y-5">
        
        <!-- Detail PM -->
        <div class="bg-white border rounded-xl shadow-sm overflow-hidden">
            <div class="px-4 py-3 bg-gray-50 border-b">
                <h2 class="text-sm font-bold text-gray-700">📋 Detail PM</h2>
            </div>
            <div class="p-4">
                <div class="grid grid-cols-2 gap-4 text-sm mb-4">
                    <div>
                        <p class="text-xs text-gray-400 mb-0.5">Aset</p>
                        <a href="<?= base_url('admin/inventory/'.$schedule['asset_id']) ?>"
                           class="font-semibold text-blue-600 hover:underline">
                            <?= esc($schedule['asset_name']) ?>
                        </a>
                        <code class="text-xs text-gray-400 block mt-0.5"><?= esc($schedule['asset_code']) ?></code>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 mb-0.5">Departemen</p>
                        <p class="font-semibold text-gray-800"><?= esc($schedule['department_name'] ?? '-') ?></p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 mb-0.5">Recurring</p>
                        <p class="font-semibold text-gray-800">
                            <?= $recurringLabel[$schedule['recurring']] ?? '-' ?>
                            <span class="text-gray-400 font-normal text-xs">(<?= $schedule['interval_days'] ?> hari)</span>
                        </p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 mb-0.5">Tipe Jadwal</p>
                        <p class="font-semibold text-gray-800">
                            <?= ($schedule['schedule_type'] ?? 'pm') === 'calibration' ? '🔬 Kalibrasi Alat' : '🛠️ Preventive Maintenance (PM)' ?>
                        </p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 mb-0.5">Estimasi Durasi</p>
                        <p class="font-semibold text-gray-800">
                            <?= $schedule['estimated_duration'] ? $schedule['estimated_duration'] . ' menit' : '-' ?>
                        </p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 mb-0.5">Teknisi PIC</p>
                        <p class="font-semibold text-gray-800"><?= esc($schedule['assigned_to_name'] ?? '-') ?></p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 mb-0.5">Dibuat oleh</p>
                        <p class="font-semibold text-gray-800"><?= esc($schedule['created_by_name'] ?? '-') ?></p>
                    </div>
                </div>

                <?php if ($schedule['description']): ?>
                <div class="bg-blue-50 border border-blue-100 rounded-lg p-3">
                    <p class="text-xs text-blue-600 font-medium mb-1">Instruksi PM</p>
                    <p class="text-sm text-gray-800 leading-relaxed whitespace-pre-line"><?= esc($schedule['description']) ?></p>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Riwayat WO Preventive -->
        <div class="bg-white border rounded-xl shadow-sm overflow-hidden">
            <div class="px-4 py-3 bg-gray-50 border-b flex items-center justify-between">
                <h2 class="text-sm font-bold text-gray-700">📜 Riwayat Work Order PM</h2>
                <span class="text-xs text-gray-400"><?= count($wo_history) ?> WO</span>
            </div>
            <?php if (empty($wo_history)): ?>
            <div class="text-center py-8 text-gray-400 text-sm">Belum ada riwayat WO PM</div>
            <?php else: ?>
            <div class="divide-y">
                <?php foreach ($wo_history as $wo): ?>
                <a href="<?= base_url('admin/work-orders/'.$wo['id']) ?>"
                   class="flex items-center justify-between px-4 py-3 hover:bg-gray-50">
                    <div>
                        <code class="font-mono text-xs text-blue-600 font-semibold"><?= esc($wo['wo_code']) ?></code>
                        <p class="text-xs text-gray-500 mt-0.5"><?= esc($wo['action_taken'] ?? $wo['problem_desc']) ?></p>
                        <?php if ($wo['cost']): ?>
                        <p class="text-xs text-orange-600 mt-0.5 font-medium"><?= $rp($wo['cost']) ?></p>
                        <?php endif; ?>
                    </div>
                    <div class="text-right">
                        <span class="text-xs font-semibold <?= $wo['status'] === 'done' ? 'text-green-600' : 'text-gray-500' ?>">
                            <?= ucfirst($wo['status']) ?>
                        </span>
                        <p class="text-xs text-gray-400"><?= date('d M Y', strtotime($wo['created_at'])) ?></p>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Kolom Kanan -->
    <div class="space-y-5">
        <!-- Schedule Info -->
        <div class="bg-white border rounded-xl p-4 shadow-sm">
            <h2 class="text-sm font-bold text-gray-700 pb-2 border-b mb-3">📅 Jadwal</h2>
            <div class="space-y-3 text-sm">
                <div>
                    <p class="text-xs text-gray-400 mb-0.5">Last Done</p>
                    <p class="font-semibold text-gray-800">
                        <?= $schedule['last_done'] ? date('d M Y', strtotime($schedule['last_done'])) : '<span class="text-gray-400">Belum pernah</span>' ?>
                    </p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 mb-0.5">Next Due</p>
                    <p class="font-semibold text-gray-800"><?= date('d M Y', strtotime($schedule['next_due'])) ?></p>
                </div>
                <div>
                    <p class="text-xs text-gray-400 mb-0.5">Created At</p>
                    <p class="font-semibold text-gray-800"><?= date('d M Y H:i', strtotime($schedule['created_at'])) ?></p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Mark Done Modal -->
<div x-data="{open:false, doneDate:'<?= date('Y-m-d') ?>', actionTaken:'', cost:0}" x-show="open" x-cloak
     class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6">
        <h3 class="font-bold text-gray-800 mb-4">✅ Catat Pelaksanaan PM</h3>
        <form action="<?= base_url('admin/pm/'.$schedule['id'].'/mark-done') ?>" method="POST">
            <?= csrf_field() ?>
            <div class="space-y-3 mb-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Selesai</label>
                    <input type="date" name="done_date" x-model="doneDate" required
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tindakan / Hasil PM</label>
                    <textarea name="action_taken" x-model="actionTaken" rows="2" placeholder="Opsional"
                              class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 resize-none"></textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Biaya (Rp)</label>
                    <input type="number" name="cost" x-model="cost" min="0" step="1000"
                           placeholder="0"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400">
                </div>
            </div>
            <div class="flex gap-3">
                <button type="button" @click="open=false" class="flex-1 border border-gray-300 text-gray-700 py-2 rounded-lg text-sm">Batal</button>
                <button type="submit" class="flex-1 bg-green-600 hover:bg-green-700 text-white py-2 rounded-lg text-sm">✅ Simpan</button>
            </div>
        </form>
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

<script>
function deleteModal() { return { open: false, actionUrl: '', itemName: '' }; }
function confirmDelete(url, name) {
    const m = Alpine.$data(document.querySelector('[x-data="deleteModal()"]'));
    m.actionUrl = url; m.itemName = name; m.open = true;
}
function showMarkDoneModal() {
    Alpine.$data(document.querySelector('[x-data*="doneDate"]')).open = true;
}
</script>

<?= $this->endSection() ?>
