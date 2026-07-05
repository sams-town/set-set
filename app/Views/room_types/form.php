<?= $this->extend('inventory/_layout') ?>
<?= $this->section('content') ?>
<?php $isEdit = !empty($rt); ?>

<div class="flex items-center gap-2 text-sm text-gray-500 mb-4">
    <a href="<?= base_url('admin/room-types') ?>" class="hover:text-blue-600">Tipe Ruangan</a>
    <span>›</span>
    <span class="font-medium text-gray-800"><?= $isEdit ? 'Edit' : 'Tambah Baru' ?></span>
</div>

<div class="max-w-xl">
<h1 class="text-xl font-bold text-gray-800 mb-5"><?= $isEdit ? '✏️ Edit Tipe Ruangan' : '🚪 Tambah Tipe Ruangan' ?></h1>

<form action="<?= $isEdit ? base_url('admin/room-types/'.$rt['id'].'/update') : base_url('admin/room-types') ?>"
      method="POST" class="bg-white border rounded-xl p-6 shadow-sm space-y-4">
    <?= csrf_field() ?>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Nama Tipe <span class="text-red-500">*</span></label>
        <input type="text" name="name" required
               value="<?= old('name', $rt['name'] ?? '') ?>"
               placeholder="Contoh: Ruang Kantor, Server Room, Gudang, Toilet"
               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none">
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Kode</label>
        <input type="text" name="code"
               value="<?= old('code', $rt['code'] ?? '') ?>"
               placeholder="Contoh: OFFICE, SERVER, GDG"
               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none uppercase"
               oninput="this.value = this.value.toUpperCase()">
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Deskripsi</label>
        <textarea name="description" rows="2"
                  class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none resize-none"><?= old('description', $rt['description'] ?? '') ?></textarea>
    </div>

    <?php if ($isEdit): ?>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
        <select name="is_active" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none">
            <option value="1" <?= ($rt['is_active'] ?? 1) == 1 ? 'selected' : '' ?>>✅ Aktif</option>
            <option value="0" <?= ($rt['is_active'] ?? 1) == 0 ? 'selected' : '' ?>>❌ Nonaktif</option>
        </select>
    </div>
    <?php endif; ?>

    <div class="flex gap-3 pt-2">
        <button type="submit"
                class="<?= $isEdit ? 'bg-yellow-500 hover:bg-yellow-600' : 'bg-blue-600 hover:bg-blue-700' ?> text-white font-semibold px-6 py-2.5 rounded-xl text-sm">
            <?= $isEdit ? '💾 Simpan' : '✅ Tambah' ?>
        </button>
        <a href="<?= base_url('admin/room-types') ?>"
           class="border border-gray-300 text-gray-700 hover:bg-gray-50 px-5 py-2.5 rounded-xl text-sm">Batal</a>
    </div>
</form>
</div>

<?= $this->endSection() ?>
