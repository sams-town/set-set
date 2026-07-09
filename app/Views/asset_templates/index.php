<?= $this->extend('inventory/_layout') ?>
<?= $this->section('content') ?>

<div class="flex items-center justify-between mb-5">
    <div>
        <h1 class="text-xl font-bold text-gray-800">📋 Template Aset</h1>
        <p class="text-sm text-gray-500 mt-0.5">Master template nama dan kategori aset untuk kemudahan registrasi</p>
    </div>
    <a href="<?= base_url('admin/asset-templates/new') ?>"
       class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors shadow-sm">
        + Tambah Template
    </a>
</div>

<div class="bg-white border rounded-xl shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50 border-b text-gray-600 text-left text-xs uppercase tracking-wide">
                    <th class="px-4 py-3 w-12">#</th>
                    <th class="px-4 py-3">Nama Template</th>
                    <th class="px-4 py-3">Kategori</th>
                    <th class="px-4 py-3">Brand / Merek</th>
                    <th class="px-4 py-3">Model</th>
                    <th class="px-4 py-3 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <?php if (empty($templates)): ?>
                <tr>
                    <td colspan="6" class="text-center text-gray-400 py-12">
                        <div class="text-3xl mb-2">📭</div>
                        Belum ada template aset.
                    </td>
                </tr>
                <?php else: ?>
                <?php foreach ($templates as $i => $t): ?>
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="px-4 py-3 text-gray-400 text-xs"><?= $i + 1 ?></td>
                    <td class="px-4 py-3 font-semibold text-gray-800"><?= esc($t['name']) ?></td>
                    <td class="px-4 py-3">
                        <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium <?= category_badge_class($t['category']) ?>">
                            <?= esc($t['category']) ?>
                        </span>
                    </td>
                    <td class="px-4 py-3 text-gray-600"><?= esc($t['brand'] ?: '-') ?></td>
                    <td class="px-4 py-3 text-gray-600"><?= esc($t['model'] ?: '-') ?></td>
                    <td class="px-4 py-3 text-center">
                        <div class="flex items-center justify-center gap-2">
                            <a href="<?= base_url('admin/asset-templates/' . $t['id'] . '/edit') ?>"
                               class="p-1.5 rounded-lg bg-yellow-50 hover:bg-yellow-100 text-yellow-600 text-xs" title="Edit">✏️</a>
                            <button type="button"
                                    onclick="confirmDelete('<?= base_url('admin/asset-templates/' . $t['id'] . '/delete') ?>', '<?= esc($t['name']) ?>')"
                                    class="p-1.5 rounded-lg bg-red-50 hover:bg-red-100 text-red-600 text-xs" title="Hapus">🗑</button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Delete Modal -->
<div x-data="deleteModal()" x-show="open" x-cloak
     class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-sm p-6">
        <div class="text-center mb-4">
            <div class="text-4xl mb-2">⚠️</div>
            <h3 class="font-bold text-gray-800">Hapus Template</h3>
            <p class="text-sm text-gray-500 mt-1">
                Hapus template <strong x-text="itemName" class="text-gray-800"></strong>? Aset yang menggunakan template ini tidak akan terhapus.
            </p>
        </div>
        <div class="flex gap-3">
            <button @click="open = false"
                    class="flex-1 border border-gray-300 text-gray-700 py-2 rounded-lg hover:bg-gray-50 text-sm font-medium">Batal</button>
            <form :action="actionUrl" method="POST" class="flex-1">
                <?= csrf_field() ?>
                <button type="submit" class="w-full bg-red-600 hover:bg-red-700 text-white py-2 rounded-lg text-sm font-medium">Ya, Hapus</button>
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
