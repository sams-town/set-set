<?= $this->extend('inventory/_layout') ?>
<?= $this->section('content') ?>
<?php
$isEdit = !empty($wo);
$v      = fn($key, $default = '') => old($key, $wo[$key] ?? $default);

// Deteksi role teknisi yang sedang terpilih (untuk mode edit)
$currentTechRole = '';
if ($isEdit && !empty($wo['assigned_to'])) {
    foreach (['technician','it','atem'] as $grp) {
        foreach (($technicians_by_role[$grp] ?? []) as $t) {
            if ((int)$t['id'] === (int)$wo['assigned_to']) {
                $currentTechRole = $grp;
                break 2;
            }
        }
    }
}
?>

<!-- Breadcrumb -->
<div class="flex items-center gap-2 text-sm text-gray-500 mb-4">
    <a href="<?= base_url('admin/work-orders') ?>" class="hover:text-blue-600">Work Order</a>
    <span>›</span>
    <span class="font-medium text-gray-800">
        <?= $isEdit ? 'Edit ' . esc($wo['wo_code']) : 'Buat WO Baru' ?>
    </span>
</div>

<!-- Alur Proses (visual) -->
<div class="flex items-center gap-1 mb-5 overflow-x-auto pb-1">
    <?php
    $steps = [
        ['icon'=>'📝','label'=>'Input Keluhan','step'=>1],
        ['icon'=>'🔍','label'=>'Assessment','step'=>2],
        ['icon'=>'🔧','label'=>'Pengerjaan','step'=>3],
        ['icon'=>'🧪','label'=>'Testing','step'=>4],
        ['icon'=>'✅','label'=>'Close WO','step'=>5],
    ];
    $curStep = 1;
    if ($isEdit) {
        $curStep = match($wo['status']) {
            'open'         => 1,
            'in_progress'  => 3,
            'waiting_part' => 2,
            'done'         => 5,
            'cancelled'    => 5,
            default        => 1,
        };
        if (!empty($wo['assessment_notes'])) $curStep = max($curStep, 2);
    }
    foreach ($steps as $i => $st):
        $active = $st['step'] <= $curStep;
        $last   = $i === count($steps) - 1;
    ?>
    <div class="flex items-center gap-1 shrink-0">
        <div class="flex flex-col items-center">
            <div class="w-8 h-8 rounded-full flex items-center justify-center text-sm
                        <?= $active ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-400' ?>">
                <?= $st['icon'] ?>
            </div>
            <span class="text-xs mt-0.5 <?= $active ? 'text-blue-700 font-semibold' : 'text-gray-400' ?> whitespace-nowrap">
                <?= $st['label'] ?>
            </span>
        </div>
        <?php if (!$last): ?>
        <div class="w-8 h-0.5 mb-4 <?= $curStep > $st['step'] ? 'bg-blue-400' : 'bg-gray-200' ?>"></div>
        <?php endif; ?>
    </div>
    <?php endforeach; ?>
</div>

<div class="max-w-5xl">
<h1 class="text-xl font-bold text-gray-800 mb-5">
    <?= $isEdit ? '✏️ Edit Work Order' : '📋 Buat Work Order Baru' ?>
    <?php if (!$isEdit): ?>
    <span class="ml-2 text-xs font-normal text-gray-400">Notifikasi WhatsApp dikirim otomatis</span>
    <?php endif; ?>
</h1>

<form action="<?= $isEdit
        ? base_url('admin/work-orders/' . $wo['id'] . '/update')
        : base_url('admin/work-orders') ?>"
      method="POST" enctype="multipart/form-data" class="space-y-5">
<?= csrf_field() ?>

<!-- ══ SECTION 1: PENGINPUT (KELUHAN & PELAPOR) ══════════════ -->
<div class="bg-white border rounded-xl p-5 shadow-sm">
    <h2 class="text-sm font-bold text-gray-700 uppercase tracking-wide mb-4 pb-2 border-b flex items-center gap-2">
        <span class="text-blue-600">📝</span> 1. Penginput (Keluhan & Pelapor)
    </h2>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

        <!-- Nomor WO (auto / read-only saat edit) -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Nomor WO</label>
            <input type="text" value="<?= $isEdit ? esc($wo['wo_code']) : 'Auto-generate' ?>"
                   readonly class="w-full border border-gray-200 bg-gray-50 rounded-lg px-3 py-2 text-sm text-gray-500 cursor-not-allowed">
        </div>

        <!-- Tanggal -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal</label>
            <input type="text" value="<?= $isEdit ? date('d M Y H:i', strtotime($wo['created_at'])) : date('d M Y H:i') ?>"
                   readonly class="w-full border border-gray-200 bg-gray-50 rounded-lg px-3 py-2 text-sm text-gray-500 cursor-not-allowed">
        </div>

        <!-- Nama Pelapor -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Nama Pelapor</label>
            <input type="text" name="reporter_name"
                   value="<?= $v('reporter_name', session()->get('user_name')) ?>"
                   placeholder="Nama pelapor..."
                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none">
        </div>

        <!-- Departemen Pelapor -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Departemen</label>
            <select name="department_id"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none">
                <option value="">-- Pilih --</option>
                <?php foreach ($departments as $did => $dname): ?>
                <option value="<?= $did ?>" <?= $v('department_id') == $did ? 'selected' : '' ?>><?= esc($dname) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Lokasi -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Lokasi Kejadian</label>
            <select name="location_id"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none">
                <option value="">-- Pilih --</option>
                <?php foreach ($locations as $lid => $lname): ?>
                <option value="<?= $lid ?>" <?= $v('location_id') == $lid ? 'selected' : '' ?>><?= esc($lname) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Aset -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Aset <span class="text-red-500">*</span></label>
            <select name="asset_id" required <?= $isEdit ? 'disabled' : '' ?>
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none <?= $isEdit ? 'bg-gray-50' : '' ?>">
                <option value="">-- Pilih Aset --</option>
                <?php foreach ($assets as $aid => $aname): ?>
                <option value="<?= $aid ?>" <?= $v('asset_id', $pre_asset_id ?? '') == $aid ? 'selected' : '' ?>><?= esc($aname) ?></option>
                <?php endforeach; ?>
            </select>
            <?php if ($isEdit): ?><input type="hidden" name="asset_id" value="<?= $wo['asset_id'] ?>"><?php endif; ?>
        </div>

        <!-- Deskripsi Keluhan -->
        <div class="md:col-span-3">
            <label class="block text-sm font-medium text-gray-700 mb-1">Deskripsi Keluhan / Masalah <span class="text-red-500">*</span></label>
            <textarea name="problem_desc" rows="3" required minlength="10"
                      placeholder="Jelaskan masalah secara detail: apa yang terjadi, kapan mulai, gejala yang terlihat..."
                      class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none resize-none"><?= $v('problem_desc') ?></textarea>
        </div>

        <!-- Foto Keluhan dari User -->
        <div class="md:col-span-3">
            <label class="block text-sm font-medium text-gray-700 mb-1">📸 Foto Keluhan (dari User)</label>
            <?php if ($isEdit && !empty($wo['photo_complaint'])): ?>
            <div class="mb-2">
                <img src="<?= base_url('uploads/work_orders/' . $wo['photo_complaint']) ?>"
                     alt="Foto Keluhan" class="w-32 h-32 object-cover rounded-xl border shadow-sm">
                <p class="text-xs text-gray-400 mt-1">Foto saat ini</p>
            </div>
            <?php endif; ?>
            <input type="file" name="photo_complaint" accept="image/*"
                   class="w-full text-sm text-gray-600 file:mr-3 file:py-1.5 file:px-4 file:rounded-lg file:border-0
                          file:text-sm file:font-medium file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 cursor-pointer">
            <p class="text-xs text-gray-400 mt-1">Foto dari user yang melaporkan kerusakan. Maks 5 MB, auto-compress ke WebP.</p>
        </div>
    </div>
</div>

<!-- ══ SECTION 2: ASSESSMENT & PENUGASAN ═════════════════════ -->
<div class="bg-white border rounded-xl p-5 shadow-sm">
    <h2 class="text-sm font-bold text-gray-700 uppercase tracking-wide mb-4 pb-2 border-b flex items-center gap-2">
        <span class="text-orange-600">🔍</span> 2. Assessment (Diagnosis & Penugasan)
    </h2>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

        <!-- Jenis Kerusakan -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Jenis Kerusakan</label>
            <input type="text" name="damage_type"
                   value="<?= esc($v('damage_type')) ?>"
                   placeholder="Ketik jenis kerusakan..."
                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none">
        </div>

        <!-- Kategori WO -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Kategori</label>
            <select name="category_wo"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none">
                <option value="">-- Pilih --</option>
                <?php foreach ($categories_wo as $cat): ?>
                <option value="<?= esc($cat) ?>" <?= $v('category_wo') === $cat ? 'selected' : '' ?>><?= esc($cat) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Tipe WO -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Tipe WO <span class="text-red-500">*</span></label>
            <select name="type" required
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none">
                <?php foreach ($type_list as $t): ?>
                <option value="<?= $t ?>" <?= $v('type', $pre_type ?? 'corrective') === $t ? 'selected' : '' ?>>
                    <?= $t === 'kalibrasi_alat' ? 'Kalibrasi Alat' : ucfirst($t) ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Prioritas (auto SLA) -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Prioritas <span class="text-red-500">*</span></label>
            <select name="priority" required id="prioritySelect" onchange="updateSla()"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none">
                <?php foreach ($priority_list as $p): ?>
                <option value="<?= $p ?>" <?= $v('priority', 'sedang') === $p ? 'selected' : '' ?>
                        data-sla="<?= $sla_hours[$p] ?? 24 ?>"><?= ucfirst($p) ?> (SLA <?= $sla_hours[$p] ?? 24 ?>j)</option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- SLA Hours (auto-fill, editable) -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Target SLA (Jam)</label>
            <input type="number" name="sla_hours" id="slaHours" min="1"
                   value="<?= $v('sla_hours', $sla_hours[$v('priority', 'sedang')] ?? 24) ?>"
                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none">
            <p class="text-xs text-gray-400 mt-1">Otomatis dari prioritas, bisa diubah manual</p>
        </div>

        <!-- Teknisi -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Ditugaskan ke (Teknisi)</label>

            <!-- Step 1: Pilih Grup -->
            <select id="techGroupSelect"
                    onchange="filterTechByGroup()"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none mb-2">
                <option value="">-- Pilih Grup --</option>
                <option value="technician" <?= ($currentTechRole ?? '') === 'technician' ? 'selected' : '' ?>>🔧 Teknisi</option>
                <option value="it"         <?= ($currentTechRole ?? '') === 'it'         ? 'selected' : '' ?>>💻 IT</option>
                <option value="atem"       <?= ($currentTechRole ?? '') === 'atem'       ? 'selected' : '' ?>>🔬 ATEM</option>
            </select>

            <!-- Step 2: Pilih Nama (muncul setelah pilih grup) -->
            <select name="assigned_to" id="techNameSelect"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none <?= empty($currentTechRole ?? '') ? 'hidden' : '' ?>">
                <option value="">-- Belum ditugaskan --</option>
                <?php foreach (['technician','it','atem'] as $grp):
                    foreach (($technicians_by_role[$grp] ?? []) as $t): ?>
                <option value="<?= $t['id'] ?>"
                        data-group="<?= $grp ?>"
                        <?= $v('assigned_to') == $t['id'] ? 'selected' : '' ?>>
                    <?= esc($t['name']) ?>
                </option>
                <?php endforeach; endforeach; ?>
            </select>
            <p class="text-xs text-gray-400 mt-1">Pilih grup terlebih dahulu, lalu pilih nama</p>
        </div>

        <!-- Vendor -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Vendor (jika outsource)</label>
            <select name="vendor_id"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none">
                <option value="">-- Tidak ada --</option>
                <?php foreach ($vendors as $vid => $vname): ?>
                <option value="<?= $vid ?>" <?= $v('vendor_id') == $vid ? 'selected' : '' ?>><?= esc($vname) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Jadwal & Target -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Jadwal Pengerjaan</label>
            <input type="date" name="scheduled_date"
                   value="<?= $v('scheduled_date') ?>"
                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Target Selesai</label>
            <input type="date" name="target_date" id="targetDate"
                   value="<?= $v('target_date') ?>"
                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none">
            <p class="text-xs text-gray-400 mt-1">Otomatis dari SLA, bisa diubah</p>
        </div>

        <!-- Catatan Assessment -->
        <div class="md:col-span-3">
            <label class="block text-sm font-medium text-gray-700 mb-1">Catatan Assessment Teknisi</label>
            <textarea name="assessment_notes" rows="2"
                      placeholder="Hasil assessment: diagnosis awal, scope pekerjaan, estimasi waktu..."
                      class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none resize-none"><?= $v('assessment_notes') ?></textarea>
        </div>

        <!-- Status (edit only) -->
        <?php if ($isEdit): ?>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Status <span class="text-red-500">*</span></label>
            <select name="status" required
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none">
                <?php foreach ($status_list as $s): ?>
                <option value="<?= $s ?>" <?= $v('status') === $s ? 'selected' : '' ?>>
                    <?= ucwords(str_replace('_', ' ', $s)) ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Mulai</label>
            <input type="date" name="start_date" value="<?= $v('start_date') ?>"
                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Selesai</label>
            <input type="date" name="finish_date" value="<?= $v('finish_date') ?>"
                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none">
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- ══ SECTION 3: STATUS PROGRESS (edit only) ════════════════ -->
<?php if ($isEdit): ?>
<div class="bg-white border rounded-xl p-5 shadow-sm">
    <h2 class="text-sm font-bold text-gray-700 uppercase tracking-wide mb-4 pb-2 border-b flex items-center gap-2">
        <span class="text-purple-600">🔧</span> 3. Status Progress (Pengerjaan & Foto)
    </h2>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

        <!-- Foto Before -->
        <div class="bg-orange-50 border border-orange-100 rounded-xl p-4">
            <h3 class="text-sm font-semibold text-orange-700 mb-2">📷 Foto Sebelum Perbaikan</h3>
            <?php if (!empty($wo['photo_before'])): ?>
            <img src="<?= base_url('uploads/work_orders/' . $wo['photo_before']) ?>"
                 alt="Before" class="w-full max-h-40 object-cover rounded-lg border mb-2 shadow-sm">
            <?php else: ?>
            <div class="w-full h-24 bg-orange-100 rounded-lg flex items-center justify-center text-orange-400 text-sm mb-2">
                Belum ada foto
            </div>
            <?php endif; ?>
            <input type="file" name="photo_before" accept="image/*"
                   class="w-full text-xs text-gray-600 file:mr-2 file:py-1 file:px-3 file:rounded-lg file:border-0
                          file:text-xs file:bg-orange-100 file:text-orange-700 hover:file:bg-orange-200 cursor-pointer">
            <p class="text-xs text-gray-400 mt-1">Kondisi aset sebelum dikerjakan</p>
        </div>

        <!-- Foto After -->
        <div class="bg-green-50 border border-green-100 rounded-xl p-4">
            <h3 class="text-sm font-semibold text-green-700 mb-2">📷 Foto Sesudah Perbaikan (Testing)</h3>
            <?php if (!empty($wo['photo_after'])): ?>
            <img src="<?= base_url('uploads/work_orders/' . $wo['photo_after']) ?>"
                 alt="After" class="w-full max-h-40 object-cover rounded-lg border mb-2 shadow-sm">
            <?php else: ?>
            <div class="w-full h-24 bg-green-100 rounded-lg flex items-center justify-center text-green-400 text-sm mb-2">
                Belum ada foto
            </div>
            <?php endif; ?>
            <input type="file" name="photo_after" accept="image/*"
                   class="w-full text-xs text-gray-600 file:mr-2 file:py-1 file:px-3 file:rounded-lg file:border-0
                          file:text-xs file:bg-green-100 file:text-green-700 hover:file:bg-green-200 cursor-pointer">
            <p class="text-xs text-gray-400 mt-1">Kondisi setelah perbaikan & testing selesai</p>
        </div>

        <!-- Tindakan yang diambil -->
        <div class="md:col-span-2">
            <label class="block text-sm font-medium text-gray-700 mb-1">Tindakan yang Diambil / Hasil Perbaikan</label>
            <textarea name="action_taken" rows="3"
                      placeholder="Deskripsikan pekerjaan yang dilakukan, komponen yang diperbaiki/diganti..."
                      class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none resize-none"><?= $v('action_taken') ?></textarea>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- ══ SECTION 4: DONE (PENYELESAIAN & BIAYA) (edit only) ═════ -->
<?php if ($isEdit): ?>
<div class="bg-white border rounded-xl p-5 shadow-sm">
    <h2 class="text-sm font-bold text-gray-700 uppercase tracking-wide mb-4 pb-2 border-b flex items-center gap-2">
        <span class="text-green-600">💰</span> 4. Done (Penyelesaian & Biaya)
    </h2>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

        <!-- Material / Spare Part -->
        <div class="md:col-span-3">
            <label class="block text-sm font-medium text-gray-700 mb-1">Material / Spare Part yang Digunakan</label>
            <textarea name="material_used" rows="2"
                      placeholder="Contoh: Kapasitor 470uF, Kabel Power 2m, RAM DDR4 8GB..."
                      class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none resize-none"><?= $v('material_used') ?></textarea>
        </div>

        <!-- Biaya Material -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Biaya Material (Rp)</label>
            <input type="number" name="material_cost" min="0" step="1000" id="materialCost"
                   value="<?= $v('material_cost') ?>"
                   placeholder="0" oninput="calcTotalCost()"
                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none">
        </div>

        <!-- Biaya Jasa / Labor -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Biaya Jasa / Tenaga (Rp)</label>
            <input type="number" name="labor_cost" min="0" step="1000" id="laborCost"
                   value="<?= $v('labor_cost') ?>"
                   placeholder="0" oninput="calcTotalCost()"
                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none">
        </div>

        <!-- Total Biaya (read-only, auto-calculated) -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Total Biaya (Otomatis)</label>
            <input type="text" id="totalCostDisplay" readonly
                   value="<?php
                       $mc = (float)($wo['material_cost'] ?? 0);
                       $lc = (float)($wo['labor_cost'] ?? 0);
                       echo ($mc > 0 || $lc > 0) ? 'Rp ' . number_format($mc + $lc, 0, ',', '.') : '-';
                   ?>"
                   class="w-full border border-gray-200 bg-gray-50 rounded-lg px-3 py-2 text-sm text-gray-600 cursor-not-allowed font-semibold">
        </div>
    </div>
</div>
<?php endif; ?>

<!-- ══ SECTION 5: CATATAN ═════════════════════════════════════ -->
<div class="bg-white border rounded-xl p-5 shadow-sm">
    <h2 class="text-sm font-bold text-gray-700 uppercase tracking-wide mb-4 pb-2 border-b flex items-center gap-2">
        <span class="text-gray-500">📌</span> Catatan Tambahan
    </h2>
    <textarea name="notes" rows="2"
              placeholder="Catatan tambahan, instruksi khusus, rekomendasi tindak lanjut..."
              class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none resize-none"><?= $v('notes') ?></textarea>
</div>

<!-- ══ TOMBOL AKSI ════════════════════════════════════════════ -->
<div class="flex items-center gap-3 pt-1">
    <button type="submit"
            class="<?= $isEdit ? 'bg-yellow-500 hover:bg-yellow-600' : 'bg-blue-600 hover:bg-blue-700' ?>
                   text-white font-semibold px-6 py-2.5 rounded-xl text-sm transition-colors shadow-sm">
        <?= $isEdit ? '💾 Perbarui Work Order' : '✅ Buat Work Order' ?>
    </button>
    <a href="<?= $isEdit ? base_url('admin/work-orders/'.$wo['id']) : base_url('admin/work-orders') ?>"
       class="border border-gray-300 text-gray-700 hover:bg-gray-50 px-5 py-2.5 rounded-xl text-sm font-medium transition-colors">
        Batal
    </a>
</div>

</form>
</div><!-- end max-w-5xl -->


<script>
// Data aset beserta kategorinya
const assetsData = <?= json_encode($assets_data ?? []) ?>;

// Auto-update WO category saat aset berubah
function updateCategory() {
    const assetSelect = document.querySelector('select[name="asset_id"]');
    const categorySelect = document.querySelector('select[name="category_wo"]');
    
    if (assetSelect && categorySelect && assetsData) {
        const selectedId = assetSelect.value;
        if (selectedId && assetsData[selectedId]) {
            const assetCategory = assetsData[selectedId].category;
            // Cari option dengan value yang sesuai
            for (let opt of categorySelect.options) {
                if (opt.value === assetCategory) {
                    categorySelect.value = assetCategory;
                    break;
                }
            }
        }
    }
}

// Auto-update SLA hours saat prioritas berubah
function updateSla() {
    const sel = document.getElementById('prioritySelect');
    const opt = sel.options[sel.selectedIndex];
    const sla = opt.getAttribute('data-sla');
    document.getElementById('slaHours').value = sla;

    // Auto-set target date
    const now   = new Date();
    const hours = parseInt(sla) || 24;
    now.setHours(now.getHours() + hours);
    const y = now.getFullYear();
    const m = String(now.getMonth()+1).padStart(2,'0');
    const d = String(now.getDate()).padStart(2,'0');
    const td = document.getElementById('targetDate');
    if (td && !td.value) { td.value = `${y}-${m}-${d}`; }
}

function calcTotalCost() {
    const mat   = parseFloat(document.getElementById('materialCost')?.value) || 0;
    const labor = parseFloat(document.getElementById('laborCost')?.value)    || 0;
    const disp  = document.getElementById('totalCostDisplay');
    if (disp) {
        const total = mat + labor;
        disp.value = total > 0 ? 'Rp ' + total.toLocaleString('id-ID') : '-';
    }
}

document.addEventListener('DOMContentLoaded', () => {
    // Inisialisasi event listener untuk aset
    const assetSelect = document.querySelector('select[name="asset_id"]');
    if (assetSelect) {
        assetSelect.addEventListener('change', updateCategory);
        // Trigger awal untuk set category saat halaman dimuat
        updateCategory();
    }
    
    const td = document.getElementById('targetDate');
    if (td && !td.value) { updateSla(); }

    // Inisialisasi dropdown teknisi (untuk mode edit — sudah ada nilai terpilih)
    const groupSel = document.getElementById('techGroupSelect');
    if (groupSel && groupSel.value) {
        filterTechByGroup();
    }
});

// ── Dropdown teknisi bertingkat ──────────────────────────────
function filterTechByGroup() {
    const group    = document.getElementById('techGroupSelect').value;
    const nameSel  = document.getElementById('techNameSelect');
    const allOpts  = nameSel.querySelectorAll('option[data-group]');
    const curVal   = nameSel.value; // simpan nilai sebelumnya

    // Sembunyikan/tampilkan option sesuai grup
    allOpts.forEach(opt => {
        if (!group || opt.getAttribute('data-group') === group) {
            opt.style.display = '';
        } else {
            opt.style.display = 'none';
        }
    });

    // Tampilkan dropdown nama
    if (group) {
        nameSel.classList.remove('hidden');
        // Jika nilai sebelumnya masih valid di grup ini, pertahankan; kalau tidak, reset
        const stillValid = group && nameSel.querySelector(`option[data-group="${group}"][value="${curVal}"]`);
        if (!stillValid) { nameSel.value = ''; }
    } else {
        nameSel.classList.add('hidden');
        nameSel.value = '';
    }
}
</script>


<?= $this->endSection() ?>
