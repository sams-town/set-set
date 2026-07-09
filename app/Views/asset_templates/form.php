<?= $this->extend('inventory/_layout') ?>
<?= $this->section('content') ?>

<?php $isEdit = !empty($template); ?>

<div class="flex items-center gap-2 text-sm text-gray-500 mb-4">
    <a href="<?= base_url('admin/asset-templates') ?>" class="hover:text-blue-600">Template Aset</a>
    <span>›</span>
    <span class="text-gray-800 font-medium"><?= $isEdit ? 'Edit Template' : 'Tambah Template' ?></span>
</div>

<div class="max-w-2xl bg-white border rounded-xl shadow-sm p-6">
    <h1 class="text-xl font-bold text-gray-800 mb-5">
        <?= $isEdit ? '✏️ Edit Template Aset' : '➕ Tambah Template Aset Baru' ?>
    </h1>

    <form action="<?= $isEdit
            ? base_url('admin/asset-templates/' . $template['id'] . '/update')
            : base_url('admin/asset-templates') ?>"
          method="POST"
          class="space-y-4">
        <?= csrf_field() ?>

        <!-- Nama Template -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
                Nama Template Aset <span class="text-red-500">*</span>
            </label>
            <input type="text" name="name" required
                   value="<?= old('name', $template['name'] ?? '') ?>"
                   placeholder="Contoh: USG 4D Mindray, Bed Pasien Elektrik"
                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none">
        </div>

        <!-- Kategori -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
                Kategori <span class="text-red-500">*</span>
            </label>
            <select name="category" required
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none">
                <option value="">-- Pilih --</option>
                <?php foreach (asset_categories() as $cat): ?>
                    <option value="<?= esc($cat) ?>" <?= old('category', $template['category'] ?? '') === $cat ? 'selected' : '' ?>><?= esc($cat) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Brand / Merek -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Brand / Merek (Opsional)</label>
            <input type="text" name="brand"
                   value="<?= old('brand', $template['brand'] ?? '') ?>"
                   placeholder="Contoh: Mindray, GE Health, Informa"
                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none">
        </div>

        <!-- Model -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Model / Tipe (Opsional)</label>
            <input type="text" name="model"
                   value="<?= old('model', $template['model'] ?? '') ?>"
                   placeholder="Contoh: DC-40, Premium-V"
                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none">
        </div>

        <!-- Tombol Aksi -->
        <div class="flex items-center gap-3 pt-2">
            <button type="submit"
                    class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-2.5 rounded-xl text-sm transition-colors shadow-sm">
                Simpan
            </button>
            <a href="<?= base_url('admin/asset-templates') ?>"
               class="border border-gray-300 text-gray-700 hover:bg-gray-50 px-5 py-2.5 rounded-xl text-sm font-medium transition-colors">
                Batal
            </a>
        </div>
    </form>
</div>

<?= $this->endSection() ?>
