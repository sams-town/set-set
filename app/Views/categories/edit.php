<?= $this->extend('inventory/_layout') ?>
<?= $this->section('content') ?>

<!-- Breadcrumb -->
<div class="flex items-center gap-2 text-sm text-gray-500 mb-4">
    <a href="<?= base_url('admin/categories') ?>" class="hover:text-blue-600">Kategori</a>
    <span>›</span>
    <span class="font-medium text-gray-800">Edit <?= esc($category['name']) ?></span>
</div>

<div class="max-w-lg">
    <h1 class="text-xl font-bold text-gray-800 mb-5">✏️ Edit Kategori</h1>

    <?php if (!empty($errors)): ?>
    <div class="bg-red-50 border border-red-200 text-red-700 text-sm px-4 py-3 rounded-lg mb-4 space-y-1">
        <?php foreach ($errors as $e): ?>
        <p>• <?= esc($e) ?></p>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <div class="bg-white border rounded-xl p-5 shadow-sm">
        <form action="<?= base_url('admin/categories/' . $category['id'] . '/update') ?>" method="POST" class="space-y-4">
            <?= csrf_field() ?>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Kode Kategori <span class="text-red-500">*</span>
                </label>
                <input type="text" name="code"
                       value="<?= old('code', $category['code']) ?>"
                       maxlength="20"
                       required
                       oninput="this.value = this.value.toUpperCase()"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none uppercase">
                <p class="text-xs text-gray-400 mt-1">Kode unik max 20 karakter.</p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Nama Kategori <span class="text-red-500">*</span>
                </label>
                <input type="text" name="name"
                       value="<?= old('name', $category['name']) ?>"
                       required
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Deskripsi</label>
                <textarea name="description" rows="3"
                          class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none resize-none"><?= old('description', $category['description']) ?></textarea>
            </div>

            <div class="flex items-center gap-3 pt-1">
                <button type="submit"
                        class="bg-yellow-500 hover:bg-yellow-600 text-white font-semibold px-6 py-2.5 rounded-xl text-sm transition-colors">
                    💾 Perbarui Kategori
                </button>
                <a href="<?= base_url('admin/categories') ?>"
                   class="border border-gray-300 text-gray-700 hover:bg-gray-50 px-5 py-2.5 rounded-xl text-sm font-medium">
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>

<?= $this->endSection() ?>
