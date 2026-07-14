<?= $this->extend('inventory/_layout') ?>
<?= $this->section('content') ?>

<!-- Breadcrumb -->
<div class="flex items-center gap-2 text-sm text-gray-500 mb-4">
    <span class="font-medium text-gray-800">Kategori Aset</span>
</div>

<!-- Header -->
<div class="flex items-center justify-between mb-5">
    <div class="flex items-center gap-2">
        <span class="text-2xl">🏷️</span>
        <h1 class="text-xl font-bold text-gray-800">Kategori Aset</h1>
    </div>
    <a href="<?= base_url('admin/categories/new') ?>"
       class="inline-flex items-center gap-1.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors">
        + Tambah Kategori
    </a>
</div>

<?php if (session()->getFlashdata('success')): ?>
<div class="bg-green-50 border border-green-200 text-green-700 text-sm px-4 py-3 rounded-lg mb-4">
    <?= session()->getFlashdata('success') ?>
</div>
<?php endif; ?>
<?php if (session()->getFlashdata('error')): ?>
<div class="bg-red-50 border border-red-200 text-red-700 text-sm px-4 py-3 rounded-lg mb-4">
    <?= session()->getFlashdata('error') ?>
</div>
<?php endif; ?>

<!-- Table -->
<div class="bg-white border rounded-xl shadow-sm overflow-hidden">
    <table class="w-full text-sm">
        <thead>
            <tr class="bg-gray-50 border-b text-xs text-gray-500 uppercase tracking-wide">
                <th class="text-left px-4 py-3 w-10">#</th>
                <th class="text-left px-4 py-3 w-24">Kode</th>
                <th class="text-left px-4 py-3">Nama Kategori</th>
                <th class="text-left px-4 py-3">Deskripsi</th>
                <th class="text-center px-4 py-3 w-32">Jumlah Aset</th>
                <th class="text-center px-4 py-3 w-24">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            <?php if (empty($categories)): ?>
            <tr>
                <td colspan="6" class="text-center text-gray-400 py-10">
                    <div class="text-3xl mb-2">🏷️</div>
                    <p class="text-sm">Belum ada kategori.</p>
                </td>
            </tr>
            <?php else: ?>
            <?php foreach ($categories as $i => $cat): ?>
            <tr class="hover:bg-gray-50 transition-colors">
                <td class="px-4 py-3 text-gray-400"><?= $i + 1 ?></td>
                <td class="px-4 py-3">
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold bg-gray-800 text-white tracking-wider">
                        <?= esc($cat['code']) ?>
                    </span>
                </td>
                <td class="px-4 py-3 font-semibold text-gray-800"><?= esc($cat['name']) ?></td>
                <td class="px-4 py-3 text-gray-500"><?= esc($cat['description'] ?? '-') ?></td>
                <td class="px-4 py-3 text-center">
                    <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-blue-600 text-white text-xs font-bold">
                        <?= $cat['asset_count'] ?>
                    </span>
                </td>
                <td class="px-4 py-3 text-center">
                    <div class="flex items-center justify-center gap-1.5">
                        <a href="<?= base_url('admin/categories/' . $cat['id'] . '/edit') ?>"
                           class="p-1.5 rounded-lg bg-yellow-50 hover:bg-yellow-100 text-yellow-600 transition-colors" title="Edit">
                            ✏️
                        </a>
                        <button onclick="confirmDelete('<?= base_url('admin/categories/' . $cat['id'] . '/delete') ?>', '<?= esc($cat['name']) ?>')"
                                class="p-1.5 rounded-lg bg-red-50 hover:bg-red-100 text-red-600 transition-colors" title="Hapus">
                            🗑
                        </button>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Delete Modal -->
<div x-data="deleteModal()" x-show="open" x-cloak
     class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-sm p-6">
        <div class="text-center mb-4">
            <div class="text-4xl mb-2">⚠️</div>
            <h3 class="font-bold text-gray-800">Hapus Kategori</h3>
            <p class="text-sm text-gray-500 mt-1">Hapus kategori <strong x-text="itemName" class="text-gray-800"></strong>?</p>
            <p class="text-xs text-red-500 mt-1">Aset yang menggunakan kategori ini tidak akan terhapus.</p>
        </div>
        <div class="flex gap-3">
            <button @click="open = false"
                    class="flex-1 border border-gray-300 text-gray-700 py-2 rounded-lg hover:bg-gray-50 text-sm font-medium">
                Batal
            </button>
            <form :action="actionUrl" method="POST" class="flex-1">
                <?= csrf_field() ?>
                <button type="submit" class="w-full bg-red-600 hover:bg-red-700 text-white py-2 rounded-lg text-sm font-medium">
                    Ya, Hapus
                </button>
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
