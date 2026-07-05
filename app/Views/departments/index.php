<?= $this->extend('inventory/_layout') ?>
<?= $this->section('content') ?>

<div class="flex items-center justify-between mb-5">
    <div>
        <h1 class="text-xl font-bold text-gray-800">🏢 Daftar Departemen</h1>
        <p class="text-sm text-gray-500 mt-0.5"><?= count($departments) ?> departemen terdaftar</p>
    </div>
    <a href="<?= base_url('admin/departments/new') ?>"
       class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-lg shadow-sm">
        + Tambah Departemen
    </a>
</div>

<?php if (session()->getFlashdata('success')): ?>
<div class="mb-4 px-4 py-3 bg-green-100 text-green-800 rounded-xl text-sm">
    <?= session()->getFlashdata('success') ?>
</div>
<?php endif; ?>
<?php if (session()->getFlashdata('error')): ?>
<div class="mb-4 px-4 py-3 bg-red-100 text-red-800 rounded-xl text-sm">
    <?= session()->getFlashdata('error') ?>
</div>
<?php endif; ?>

<div class="bg-white border rounded-xl shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50 border-b text-xs text-gray-500 uppercase tracking-wide">
                    <th class="px-4 py-3 text-left">#</th>
                    <th class="px-4 py-3 text-left">Kode</th>
                    <th class="px-4 py-3 text-left">Nama Departemen</th>
                    <th class="px-4 py-3 text-left">Manajer</th>
                    <th class="px-4 py-3 text-left">Telepon</th>
                    <th class="px-4 py-3 text-center">Aset</th>
                    <th class="px-4 py-3 text-center">Staf</th>
                    <th class="px-4 py-3 text-center">Status</th>
                    <th class="px-4 py-3 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <?php if (empty($departments)): ?>
                <tr><td colspan="9" class="text-center text-gray-400 py-10">
                    <div class="text-3xl mb-2">🏢</div>Belum ada departemen.
                </td></tr>
                <?php else: ?>
                <?php foreach ($departments as $i => $d): ?>
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 text-gray-400 text-xs"><?= $i + 1 ?></td>
                    <td class="px-4 py-3">
                        <?php if ($d['code']): ?>
                        <code class="bg-blue-50 text-blue-700 px-2 py-0.5 rounded text-xs font-mono"><?= esc($d['code']) ?></code>
                        <?php else: ?>
                        <span class="text-gray-300">—</span>
                        <?php endif; ?>
                    </td>
                    <td class="px-4 py-3 font-semibold text-gray-800">
                        <?= esc($d['name']) ?>
                        <?php if ($d['description']): ?>
                        <p class="text-xs text-gray-400 font-normal mt-0.5 truncate max-w-xs"><?= esc($d['description']) ?></p>
                        <?php endif; ?>
                    </td>
                    <td class="px-4 py-3 text-gray-600"><?= esc($d['manager'] ?? '—') ?></td>
                    <td class="px-4 py-3 text-gray-600"><?= esc($d['phone'] ?? '—') ?></td>
                    <td class="px-4 py-3 text-center">
                        <span class="font-bold text-blue-700"><?= number_format($d['asset_count'] ?? 0) ?></span>
                    </td>
                    <td class="px-4 py-3 text-center">
                        <span class="font-bold text-green-700"><?= number_format($d['user_count'] ?? 0) ?></span>
                    </td>
                    <td class="px-4 py-3 text-center">
                        <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium
                            <?= $d['is_active'] ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' ?>">
                            <?= $d['is_active'] ? 'Aktif' : 'Nonaktif' ?>
                        </span>
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex justify-center gap-1">
                            <a href="<?= base_url('admin/departments/'.$d['id'].'/edit') ?>"
                               class="p-1.5 rounded-lg bg-yellow-50 hover:bg-yellow-100 text-yellow-600 text-xs">✏️</a>
                            <button onclick="confirmDelete('<?= base_url('admin/departments/'.$d['id'].'/delete') ?>','<?= esc($d['name']) ?>')"
                                    class="p-1.5 rounded-lg bg-red-50 hover:bg-red-100 text-red-600 text-xs">🗑</button>
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
<div x-data="deleteModal()" x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-sm p-6 text-center">
        <div class="text-4xl mb-3">⚠️</div>
        <h3 class="font-bold text-gray-800">Hapus Departemen</h3>
        <p class="text-sm text-gray-500 mt-1">Hapus <strong x-text="itemName"></strong>?</p>
        <div class="flex gap-3 mt-4">
            <button @click="open=false" class="flex-1 border border-gray-300 text-gray-700 py-2 rounded-lg text-sm">Batal</button>
            <form :action="actionUrl" method="POST" class="flex-1">
                <?= csrf_field() ?>
                <button type="submit" class="w-full bg-red-600 hover:bg-red-700 text-white py-2 rounded-lg text-sm">Hapus</button>
            </form>
        </div>
    </div>
</div>
<script>
function deleteModal() { return { open:false, actionUrl:'', itemName:'' }; }
function confirmDelete(url, name) {
    const m = Alpine.$data(document.querySelector('[x-data="deleteModal()"]'));
    m.actionUrl = url; m.itemName = name; m.open = true;
}
</script>

<?= $this->endSection() ?>
