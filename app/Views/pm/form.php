<?= $this->extend('inventory/_layout') ?>
<?= $this->section('content') ?>
<?php
$isEdit = !empty($schedule);
$v = fn($key, $default = '') => old($key, $schedule[$key] ?? $default);
$recurringDays = \App\Models\PreventiveMaintenanceModel::RECURRING_DAYS;
?>

<!-- Breadcrumb -->
<div class="flex items-center gap-2 text-sm text-gray-500 mb-4">
    <a href="<?= base_url('admin/pm') ?>" class="hover:text-blue-600">Preventive Maintenance</a>
    <span>›</span>
    <span class="font-medium text-gray-800"><?= $isEdit ? 'Edit ' . esc($schedule['title']) : 'Buat Schedule PM' ?></span>
</div>

<div class="max-w-3xl">
<h1 class="text-xl font-bold text-gray-800 mb-5">
    <?= $isEdit ? '✏️ Edit Schedule PM' : '🛡️ Buat Schedule PM Baru' ?>
</h1>

<form action="<?= $isEdit ? base_url('admin/pm/'.$schedule['id'].'/update') : base_url('admin/pm') ?>"
      method="POST" class="space-y-5">
<?= csrf_field() ?>

<!-- SECTION 1: Aset & Judul -->
<div class="bg-white border rounded-xl p-5 shadow-sm">
    <h2 class="text-sm font-bold text-gray-700 uppercase tracking-wide mb-4 pb-2 border-b flex items-center gap-2">
        <span class="text-blue-600">📋</span> Informasi PM
    </h2>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

        <!-- Aset -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
                Aset <span class="text-red-500">*</span>
            </label>
            <select name="asset_id" required <?= $isEdit ? 'disabled' : '' ?>
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none <?= $isEdit ? 'bg-gray-50' : '' ?>">
                <option value="">-- Pilih Aset --</option>
                <?php foreach ($assets as $aid => $aname): ?>
                <option value="<?= $aid ?>" <?= $v('asset_id') == $aid ? 'selected' : '' ?>>
                    <?= esc($aname) ?>
                </option>
                <?php endforeach; ?>
            </select>
            <?php if ($isEdit): ?>
                <input type="hidden" name="asset_id" value="<?= $schedule['asset_id'] ?>">
            <?php endif; ?>
        </div>

        <!-- Tipe Jadwal -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
                Tipe Jadwal <span class="text-red-500">*</span>
            </label>
            <select name="schedule_type" required
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none">
                <option value="pm" <?= $v('schedule_type', 'pm') === 'pm' ? 'selected' : '' ?>>🛡️ Preventive Maintenance (PM)</option>
                <option value="calibration" <?= $v('schedule_type') === 'calibration' ? 'selected' : '' ?>>🔬 Kalibrasi Alat</option>
            </select>
        </div>

        <!-- Judul PM -->
        <div class="md:col-span-2">
            <label class="block text-sm font-medium text-gray-700 mb-1">
                Judul PM <span class="text-red-500">*</span>
            </label>
            <input type="text" name="title" required
                   value="<?= $v('title') ?>"
                   placeholder="Contoh: Penggantian Filter AC, Servis Genset Bulanan"
                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none">
        </div>

        <!-- Deskripsi / Instruksi -->
        <div class="md:col-span-2">
            <label class="block text-sm font-medium text-gray-700 mb-1">Instruksi / Deskripsi PM</label>
            <textarea name="description" rows="3"
                      placeholder="Langkah-langkah PM yang harus dilakukan teknisi..."
                      class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none resize-none"><?= $v('description') ?></textarea>
        </div>
    </div>
</div>

<!-- SECTION 2: Schedule Recurring -->
<div class="bg-white border rounded-xl p-5 shadow-sm">
    <h2 class="text-sm font-bold text-gray-700 uppercase tracking-wide mb-4 pb-2 border-b flex items-center gap-2">
        <span class="text-orange-600">🔄</span> Schedule Recurring
    </h2>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

        <!-- Recurring Dropdown -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
                Frekuensi <span class="text-red-500">*</span>
            </label>
            <select name="recurring" id="recurringSelect" required onchange="updateInterval()"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none">
                <?php foreach ($recurring_opts as $key => $label): ?>
                <option value="<?= $key ?>"
                        data-days="<?= $recurringDays[$key] ?>"
                        <?= $v('recurring', 'monthly') === $key ? 'selected' : '' ?>>
                    <?= $label ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Interval Hari (read-only, auto) -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Interval (Hari)</label>
            <input type="text" id="intervalDisplay" readonly
                   class="w-full border border-gray-200 bg-gray-50 rounded-lg px-3 py-2 text-sm text-gray-600 cursor-not-allowed font-semibold">
            <p class="text-xs text-gray-400 mt-1">Otomatis dari frekuensi</p>
        </div>

        <!-- Tanggal Mulai / Next Due -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
                Tanggal Mulai / First Due <span class="text-red-500">*</span>
            </label>
            <?php if ($isEdit): ?>
                <input type="date" name="next_due"
                       value="<?= $v('next_due') ?>"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none">
                <p class="text-xs text-gray-400 mt-1">Next due date</p>
            <?php else: ?>
                <input type="date" name="start_date" id="startDate"
                       value="<?= $v('start_date', date('Y-m-d')) ?>"
                       onchange="updateNextDue()"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none">
                <p class="text-xs text-gray-400 mt-1">PM pertama akan jatuh pada tanggal ini</p>
            <?php endif; ?>
        </div>

        <!-- Estimasi Durasi -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Estimasi Durasi (Menit)</label>
            <input type="number" name="estimated_duration" min="15" step="15"
                   value="<?= $v('estimated_duration') ?>"
                   placeholder="Contoh: 60"
                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none">
        </div>

        <!-- Preview Next Due -->
        <?php if (!$isEdit): ?>
        <div class="md:col-span-2">
            <label class="block text-sm font-medium text-gray-700 mb-1">Preview Jadwal PM</label>
            <div id="previewDates" class="bg-blue-50 border border-blue-100 rounded-lg px-3 py-2 text-sm text-blue-700 font-medium min-h-[38px]">
                — pilih frekuensi dan tanggal mulai —
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- SECTION 3: Penugasan & Prioritas -->
<div class="bg-white border rounded-xl p-5 shadow-sm">
    <h2 class="text-sm font-bold text-gray-700 uppercase tracking-wide mb-4 pb-2 border-b flex items-center gap-2">
        <span class="text-green-600">👤</span> Penugasan & Prioritas
    </h2>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

        <!-- Teknisi -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Teknisi PIC</label>
            <select name="assigned_to"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none">
                <option value="">-- Belum ditugaskan --</option>
                <?php foreach ($technicians as $tid => $tname): ?>
                <option value="<?= $tid ?>" <?= $v('assigned_to') == $tid ? 'selected' : '' ?>><?= esc($tname) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Prioritas -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
                Prioritas <span class="text-red-500">*</span>
            </label>
            <select name="priority" required
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none">
                <?php foreach ($priority_opts as $p): ?>
                <option value="<?= $p ?>" <?= $v('priority', 'sedang') === $p ? 'selected' : '' ?>>
                    <?= ucfirst($p) ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Status Aktif (edit only) -->
        <?php if ($isEdit): ?>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Status Aktif</label>
            <select name="is_active"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none">
                <option value="1" <?= $v('is_active', '1') == '1' ? 'selected' : '' ?>>✅ Aktif</option>
                <option value="0" <?= $v('is_active', '1') == '0' ? 'selected' : '' ?>>❌ Nonaktif</option>
            </select>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Tombol Aksi -->
<div class="flex items-center gap-3 pt-1">
    <button type="submit"
            class="<?= $isEdit ? 'bg-yellow-500 hover:bg-yellow-600' : 'bg-blue-600 hover:bg-blue-700' ?>
                   text-white font-semibold px-6 py-2.5 rounded-xl text-sm transition-colors shadow-sm">
        <?= $isEdit ? '💾 Perbarui Schedule' : '✅ Buat Schedule PM' ?>
    </button>
    <a href="<?= $isEdit ? base_url('admin/pm/'.$schedule['id']) : base_url('admin/pm') ?>"
       class="border border-gray-300 text-gray-700 hover:bg-gray-50 px-5 py-2.5 rounded-xl text-sm font-medium">
        Batal
    </a>
</div>
</form>
</div>

<script>
const recurringDays = <?= json_encode($recurringDays) ?>;
const recurringLabels = <?= json_encode($recurring_opts) ?>;

function updateInterval() {
    const sel  = document.getElementById('recurringSelect');
    const days = parseInt(sel.options[sel.selectedIndex].getAttribute('data-days')) || 30;
    document.getElementById('intervalDisplay').value = days + ' hari';
    updateNextDue();
}

function updateNextDue() {
    const sel       = document.getElementById('recurringSelect');
    const startEl   = document.getElementById('startDate');
    const preview   = document.getElementById('previewDates');
    if (!sel || !startEl || !preview) return;

    const days  = parseInt(sel.options[sel.selectedIndex].getAttribute('data-days')) || 30;
    const start = startEl.value;
    if (!start) { preview.textContent = '— pilih tanggal mulai —'; return; }

    const d0 = new Date(start);
    let lines = [];
    for (let i = 0; i < 4; i++) {
        const next = new Date(d0.getTime() + i * days * 86400000);
        lines.push('PM ' + (i + 1) + ': ' + next.toLocaleDateString('id-ID', {day:'2-digit', month:'short', year:'numeric'}));
    }
    preview.innerHTML = '📅 ' + lines.join('&nbsp;&nbsp;|&nbsp;&nbsp;') + ' &nbsp; <span class="text-blue-400 text-xs">... dst</span>';
}

document.addEventListener('DOMContentLoaded', () => {
    updateInterval();
    updateNextDue();
});
</script>

<?= $this->endSection() ?>
