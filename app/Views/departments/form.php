<?= $this->extend('inventory/_layout') ?>
<?= $this->section('content') ?>
<?php $isEdit = !empty($dept); ?>

<div class="flex items-center gap-2 text-sm text-gray-500 mb-4">
    <a href="<?= base_url('admin/departments') ?>" class="hover:text-blue-600">Departemen</a>
    <span>›</span>
    <span class="font-medium text-gray-800"><?= $isEdit ? 'Edit '  . esc($dept['name']) : 'Tambah Baru' ?></span>
</div>

<div class="max-w-2xl">
<h1 class="text-xl font-bold text-gray-800 mb-5"><?= $isEdit ? '✏️ Edit Departemen' : '🏢 Tambah Departemen' ?></h1>

<?php if (!empty(session()->getFlashdata('errors'))): ?>
<div class="mb-4 px-4 py-3 bg-red-50 border border-red-200 rounded-xl text-sm text-red-700">
    <ul class="list-disc list-inside space-y-1">
        <?php foreach ((array)session()->getFlashdata('errors') as $err): ?>
        <li><?= esc($err) ?></li>
        <?php endforeach; ?>
    </ul>
</div>
<?php endif; ?>

<form action="<?= $isEdit ? base_url('admin/departments/'.$dept['id'].'/update') : base_url('admin/departments') ?>"
      method="POST" class="bg-white border rounded-xl p-6 shadow-sm space-y-4">
    <?= csrf_field() ?>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <!-- Nama -->
        <div class="md:col-span-2">
            <label class="block text-sm font-medium text-gray-700 mb-1">Nama Departemen <span class="text-red-500">*</span></label>
            <input type="text" name="name" required
                   value="<?= old('name', $dept['name'] ?? '') ?>"
                   placeholder="Contoh: General Affairs, IT, Finance"
                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none">
        </div>

        <!-- Kode -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Kode Departemen</label>
            <input type="text" name="code"
                   value="<?= old('code', $dept['code'] ?? '') ?>"
                   placeholder="Contoh: GA, IT, FIN"
                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none uppercase"
                   oninput="this.value = this.value.toUpperCase()">
            <p class="text-xs text-gray-400 mt-1">Opsional, otomatis uppercase</p>
        </div>

        <!-- Manajer -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Nama Manajer</label>
            <input type="text" name="manager"
                   value="<?= old('manager', $dept['manager'] ?? '') ?>"
                   placeholder="Nama manajer / kepala departemen"
                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none">
        </div>

        <!-- Telepon -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Telepon / Ext</label>
            <input type="text" name="phone"
                   value="<?= old('phone', $dept['phone'] ?? '') ?>"
                   placeholder="08xx / ext 1234"
                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none">
        </div>

        <!-- Status (edit only) -->
        <?php if ($isEdit): ?>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
            <select name="is_active" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none">
                <option value="1" <?= ($dept['is_active'] ?? 1) == 1 ? 'selected' : '' ?>>✅ Aktif</option>
                <option value="0" <?= ($dept['is_active'] ?? 1) == 0 ? 'selected' : '' ?>>❌ Nonaktif</option>
            </select>
        </div>
        <?php endif; ?>

        <!-- Deskripsi -->
        <div class="md:col-span-2">
            <label class="block text-sm font-medium text-gray-700 mb-1">Deskripsi</label>
            <textarea name="description" rows="2" placeholder="Keterangan singkat tentang departemen ini..."
                      class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none resize-none"><?= old('description', $dept['description'] ?? '') ?></textarea>
        </div>
    </div>

    <div class="flex gap-3 pt-2">
        <button type="submit"
                class="<?= $isEdit ? 'bg-yellow-500 hover:bg-yellow-600' : 'bg-blue-600 hover:bg-blue-700' ?> text-white font-semibold px-6 py-2.5 rounded-xl text-sm">
            <?= $isEdit ? '💾 Simpan Perubahan' : '✅ Tambah Departemen' ?>
        </button>
        <a href="<?= base_url('admin/departments') ?>"
           class="border border-gray-300 text-gray-700 hover:bg-gray-50 px-5 py-2.5 rounded-xl text-sm">Batal</a>
    </div>
</form>
</div>

<?= $this->endSection() ?>
