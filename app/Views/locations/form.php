<?= $this->extend('inventory/_layout') ?>
<?= $this->section('content') ?>
<?php $isEdit = !empty($loc); ?>

<div class="flex items-center gap-2 text-sm text-gray-500 mb-4">
    <a href="<?= base_url('admin/locations') ?>" class="hover:text-blue-600">Lokasi</a>
    <span>›</span>
    <span class="font-medium text-gray-800"><?= $isEdit ? 'Edit ' . esc($loc['name']) : 'Tambah Baru' ?></span>
</div>

<div class="max-w-2xl">
<h1 class="text-xl font-bold text-gray-800 mb-5"><?= $isEdit ? '✏️ Edit Lokasi' : '📍 Tambah Lokasi Baru' ?></h1>

<form action="<?= $isEdit ? base_url('admin/locations/'.$loc['id'].'/update') : base_url('admin/locations') ?>"
      method="POST" enctype="multipart/form-data" class="space-y-5">
    <?= csrf_field() ?>

    <!-- Identitas Lokasi -->
    <div class="bg-white border rounded-xl p-5 shadow-sm">
        <h2 class="text-sm font-bold text-gray-700 uppercase tracking-wide mb-4 pb-2 border-b">📍 Identitas Lokasi</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Nama Lokasi / Ruangan <span class="text-red-500">*</span></label>
                <input type="text" name="name" required
                       value="<?= old('name', $loc['name'] ?? '') ?>"
                       placeholder="Contoh: Ruang Meeting A, Server Room Lt.3"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nama Gedung / Tower</label>
                <input type="text" name="building"
                       value="<?= old('building', $loc['building'] ?? '') ?>"
                       placeholder="Gedung A, Tower 1, Kantor Pusat"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Lantai</label>
                <input type="text" name="floor"
                       value="<?= old('floor', $loc['floor'] ?? '') ?>"
                       placeholder="Lantai 1, LG, GF"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Tipe Ruangan</label>
                <select name="room_type_id"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none">
                    <option value="">-- Pilih Tipe --</option>
                    <?php foreach ($room_types as $rid => $rname): ?>
                    <option value="<?= $rid ?>" <?= old('room_type_id', $loc['room_type_id'] ?? '') == $rid ? 'selected' : '' ?>>
                        <?= esc($rname) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Departemen</label>
                <select name="department_id"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none">
                    <option value="">-- Pilih Departemen --</option>
                    <?php foreach ($departments as $did => $dname): ?>
                    <option value="<?= $did ?>" <?= old('department_id', $loc['department_id'] ?? '') == $did ? 'selected' : '' ?>>
                        <?= esc($dname) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Kapasitas</label>
                <input type="number" name="capacity" min="1"
                       value="<?= old('capacity', $loc['capacity'] ?? '') ?>"
                       placeholder="Jumlah orang / unit"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none">
            </div>

            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Catatan</label>
                <textarea name="notes" rows="2"
                          class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none resize-none"><?= old('notes', $loc['notes'] ?? '') ?></textarea>
            </div>
        </div>
    </div>

    <!-- Foto Ruangan -->
    <div class="bg-white border rounded-xl p-5 shadow-sm">
        <h2 class="text-sm font-bold text-gray-700 uppercase tracking-wide mb-4 pb-2 border-b">📷 Foto Ruangan</h2>
        <div class="flex flex-col sm:flex-row gap-4 items-start">
            <?php if ($isEdit && !empty($loc['photo'])): ?>
            <div class="shrink-0">
                <img src="<?= base_url('uploads/locations/'.$loc['photo']) ?>"
                     class="w-32 h-24 object-cover rounded-xl border shadow-sm" alt="Foto">
                <p class="text-xs text-gray-400 mt-1">Foto saat ini</p>
            </div>
            <?php endif; ?>
            <div class="flex-1">
                <input type="file" name="photo" accept="image/*"
                       class="w-full text-sm text-gray-600 file:mr-3 file:py-1.5 file:px-4 file:rounded-lg
                              file:border-0 file:text-sm file:font-medium file:bg-blue-50 file:text-blue-700
                              hover:file:bg-blue-100 cursor-pointer">
                <p class="text-xs text-gray-400 mt-1">JPG, PNG, WebP maks 5 MB. Auto-compress ke WebP.</p>
            </div>
        </div>
    </div>

    <div class="flex gap-3">
        <button type="submit"
                class="<?= $isEdit ? 'bg-yellow-500 hover:bg-yellow-600' : 'bg-blue-600 hover:bg-blue-700' ?> text-white font-semibold px-6 py-2.5 rounded-xl text-sm">
            <?= $isEdit ? '💾 Simpan Perubahan' : '✅ Tambah Lokasi' ?>
        </button>
        <a href="<?= base_url('admin/locations') ?>"
           class="border border-gray-300 text-gray-700 hover:bg-gray-50 px-5 py-2.5 rounded-xl text-sm">Batal</a>
    </div>
</form>
</div>

<?= $this->endSection() ?>
