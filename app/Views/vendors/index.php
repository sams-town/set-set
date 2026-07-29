<?= $this->extend('inventory/_layout') ?>
<?= $this->section('content') ?>

<!-- Header -->
<div class="flex items-center justify-between mb-5">
    <div class="flex items-center gap-2">
        <span class="text-2xl">🏭</span>
        <div>
            <h1 class="text-xl font-bold text-gray-800">Master Data Vendor</h1>
            <p class="text-xs text-gray-400 mt-0.5">Kelola supplier dan vendor jasa pemeliharaan</p>
        </div>
    </div>
    <a href="<?= base_url('admin/vendors/new') ?>"
       class="inline-flex items-center gap-1.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors">
        + Tambah Vendor
    </a>
</div>

<!-- Flash Messages -->
<?php if ($msg = session()->getFlashdata('success')): ?>
<div class="mb-4 px-4 py-3 bg-green-50 border border-green-200 text-green-700 text-sm rounded-xl"><?= $msg ?></div>
<?php elseif ($msg = session()->getFlashdata('error')): ?>
<div class="mb-4 px-4 py-3 bg-red-50 border border-red-200 text-red-700 text-sm rounded-xl"><?= $msg ?></div>
<?php endif; ?>

<!-- Stats Cards -->
<div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-5">
    <div class="bg-white border rounded-xl p-4 shadow-sm text-center">
        <div class="text-2xl font-bold text-gray-800"><?= $stats['total'] ?></div>
        <div class="text-xs text-gray-500 mt-1">Total Vendor</div>
    </div>
    <div class="bg-white border rounded-xl p-4 shadow-sm text-center">
        <div class="text-2xl font-bold text-green-600"><?= $stats['active'] ?></div>
        <div class="text-xs text-gray-500 mt-1">Aktif</div>
    </div>
    <div class="bg-white border rounded-xl p-4 shadow-sm text-center">
        <div class="text-2xl font-bold text-blue-600"><?= $stats['supplier'] ?></div>
        <div class="text-xs text-gray-500 mt-1">Supplier</div>
    </div>
    <div class="bg-white border rounded-xl p-4 shadow-sm text-center">
        <div class="text-2xl font-bold text-orange-600"><?= $stats['service'] ?></div>
        <div class="text-xs text-gray-500 mt-1">Service / Jasa</div>
    </div>
</div>

<!-- Filter -->
<div class="bg-white border rounded-xl px-4 py-3 mb-4 shadow-sm">
    <form method="GET" class="flex flex-wrap gap-2 items-center">
        <input type="text" name="search"
               value="<?= esc($filters['search'] ?? '') ?>"
               placeholder="Cari nama, kode, kontak..."
               class="flex-1 min-w-[180px] border border-gray-300 rounded-lg px-3 py-1.5 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none">

        <select name="category"
                class="border border-gray-300 rounded-lg px-3 py-1.5 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none">
            <option value="">Semua Kategori</option>
            <?php foreach ($categories as $key => $label): ?>
            <option value="<?= $key ?>" <?= ($filters['category'] ?? '') === $key ? 'selected' : '' ?>>
                <?= $label ?>
            </option>
            <?php endforeach; ?>
        </select>

        <select name="is_active"
                class="border border-gray-300 rounded-lg px-3 py-1.5 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none">
            <option value="">Semua Status</option>
            <option value="1" <?= ($filters['is_active'] ?? '') === '1' ? 'selected' : '' ?>>Aktif</option>
            <option value="0" <?= ($filters['is_active'] ?? '') === '0' ? 'selected' : '' ?>>Nonaktif</option>
        </select>

        <div class="flex gap-1.5">
            <button type="submit"
                    class="bg-blue-600 hover:bg-blue-700 text-white text-sm px-4 py-1.5 rounded-lg">Filter</button>
            <a href="<?= base_url('admin/vendors') ?>"
               class="bg-gray-100 hover:bg-gray-200 text-gray-600 text-sm px-3 py-1.5 rounded-lg">Reset</a>
        </div>
    </form>
</div>

<!-- Table -->
<div class="bg-white border rounded-xl shadow-sm overflow-hidden">
    <?php if (empty($vendors)): ?>
    <div class="text-center py-16 text-gray-400">
        <div class="text-5xl mb-3">🏭</div>
        <p class="text-sm font-medium text-gray-500">Belum ada vendor</p>
        <a href="<?= base_url('admin/vendors/new') ?>"
           class="inline-block mt-3 text-sm text-blue-600 hover:underline">+ Tambah vendor pertama</a>
    </div>
    <?php else: ?>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50 border-b text-xs text-gray-500 uppercase tracking-wide">
                    <th class="text-left px-4 py-3 w-10">#</th>
                    <th class="text-left px-4 py-3">Nama Vendor</th>
                    <th class="text-left px-4 py-3 w-24">Kode</th>
                    <th class="text-left px-4 py-3">Kontak</th>
                    <th class="text-left px-4 py-3 w-32">Kategori</th>
                    <th class="text-center px-4 py-3 w-24">Aset</th>
                    <th class="text-center px-4 py-3 w-24">Status</th>
                    <th class="text-center px-4 py-3 w-24">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <?php
                $catBadge = [
                    'supplier' => 'bg-blue-100 text-blue-700',
                    'service'  => 'bg-orange-100 text-orange-700',
                    'both'     => 'bg-purple-100 text-purple-700',
                ];
                foreach ($vendors as $i => $v):
                ?>
                <tr class="hover:bg-gray-50 transition-colors <?= !$v['is_active'] ? 'opacity-60' : '' ?>">
                    <td class="px-4 py-3 text-gray-400 text-xs"><?= $i + 1 ?></td>
                    <td class="px-4 py-3">
                        <p class="font-semibold text-gray-800"><?= esc($v['name']) ?></p>
                        <?php if ($v['email']): ?>
                        <p class="text-xs text-gray-400 mt-0.5"><?= esc($v['email']) ?></p>
                        <?php endif; ?>
                    </td>
                    <td class="px-4 py-3">
                        <?php if ($v['code']): ?>
                        <span class="inline-flex px-2 py-0.5 rounded text-xs font-bold bg-gray-800 text-white tracking-wider">
                            <?= esc($v['code']) ?>
                        </span>
                        <?php else: ?>
                        <span class="text-gray-300 text-xs">—</span>
                        <?php endif; ?>
                    </td>
                    <td class="px-4 py-3 text-sm">
                        <?php if ($v['contact']): ?>
                        <p class="text-gray-700"><?= esc($v['contact']) ?></p>
                        <?php endif; ?>
                        <?php if ($v['phone']): ?>
                        <p class="text-xs text-gray-400"><?= esc($v['phone']) ?></p>
                        <?php endif; ?>
                        <?php if (!$v['contact'] && !$v['phone']): ?>
                        <span class="text-gray-300 text-xs">—</span>
                        <?php endif; ?>
                    </td>
                    <td class="px-4 py-3">
                        <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold <?= $catBadge[$v['category']] ?? 'bg-gray-100 text-gray-600' ?>">
                            <?= $categories[$v['category']] ?? $v['category'] ?>
                        </span>
                    </td>
                    <td class="px-4 py-3 text-center">
                        <?php if ($v['asset_count'] > 0): ?>
                        <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-blue-600 text-white text-xs font-bold">
                            <?= $v['asset_count'] ?>
                        </span>
                        <?php else: ?>
                        <span class="text-gray-300 text-xs">0</span>
                        <?php endif; ?>
                    </td>
                    <td class="px-4 py-3 text-center">
                        <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium
                            <?= $v['is_active'] ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' ?>">
                            <?= $v['is_active'] ? 'Aktif' : 'Nonaktif' ?>
                        </span>
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex items-center justify-center gap-1.5">
                            <a href="<?= base_url('admin/vendors/' . $v['id'] . '/edit') ?>"
                               class="p-1.5 rounded-lg bg-yellow-50 hover:bg-yellow-100 text-yellow-600 transition-colors" title="Edit">
                                ✏️
                            </a>
                            <button onclick="confirmDelete('<?= base_url('admin/vendors/' . $v['id'] . '/delete') ?>', '<?= esc($v['name']) ?>')"
                                    class="p-1.5 rounded-lg bg-red-50 hover:bg-red-100 text-red-600 transition-colors" title="Hapus">
                                🗑
                            </button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <div class="px-4 py-2 border-t bg-gray-50">
        <p class="text-xs text-gray-400">Total <?= count($vendors) ?> vendor</p>
    </div>
    <?php endif; ?>
</div>

<!-- Delete Modal -->
<div x-data="deleteModal()" x-show="open" x-cloak
     class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-sm p-6 text-center">
        <div class="text-4xl mb-3">⚠️</div>
        <h3 class="font-bold text-gray-800">Hapus Vendor</h3>
        <p class="text-sm text-gray-500 mt-1">Hapus <strong x-text="itemName"></strong>?</p>
        <p class="text-xs text-red-500 mt-1">Vendor yang masih digunakan aset tidak dapat dihapus.</p>
        <div class="flex gap-3 mt-4">
            <button @click="open=false"
                    class="flex-1 border border-gray-300 text-gray-700 py-2 rounded-lg text-sm">Batal</button>
            <form :action="actionUrl" method="POST" class="flex-1">
                <?= csrf_field() ?>
                <button type="submit"
                        class="w-full bg-red-600 hover:bg-red-700 text-white py-2 rounded-lg text-sm">Hapus</button>
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
