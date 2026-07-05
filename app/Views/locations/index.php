<?= $this->extend('inventory/_layout') ?>
<?= $this->section('content') ?>

<div class="flex items-center justify-between mb-5">
    <div>
        <h1 class="text-xl font-bold text-gray-800">📍 Manajemen Lokasi</h1>
        <p class="text-sm text-gray-500 mt-0.5"><?= count($locations) ?> lokasi terdaftar</p>
    </div>
    <a href="<?= base_url('admin/locations/new') ?>"
       class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-lg shadow-sm">
        + Tambah Lokasi
    </a>
</div>

<?php if ($msg = session()->getFlashdata('success')): ?>
<div class="mb-4 px-4 py-3 bg-green-100 text-green-800 rounded-xl text-sm"><?= $msg ?></div>
<?php elseif ($msg = session()->getFlashdata('error')): ?>
<div class="mb-4 px-4 py-3 bg-red-100 text-red-800 rounded-xl text-sm"><?= $msg ?></div>
<?php endif; ?>

<!-- Filter -->
<div class="bg-white border rounded-xl px-4 py-3 mb-4 shadow-sm">
    <form method="GET" class="flex flex-wrap gap-2 items-center">
        <input type="text" name="search" value="<?= esc($filters['search'] ?? '') ?>"
               placeholder="Cari nama / gedung..."
               class="flex-1 min-w-[180px] border border-gray-300 rounded-lg px-3 py-1.5 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none">

        <select name="department_id" class="border border-gray-300 rounded-lg px-3 py-1.5 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none">
            <option value="">Semua Departemen</option>
            <?php foreach ($departments as $id => $name): ?>
            <option value="<?= $id ?>" <?= ($filters['department_id'] ?? '') == $id ? 'selected' : '' ?>><?= esc($name) ?></option>
            <?php endforeach; ?>
        </select>

        <select name="room_type_id" class="border border-gray-300 rounded-lg px-3 py-1.5 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none">
            <option value="">Semua Tipe Ruangan</option>
            <?php foreach ($room_types as $id => $name): ?>
            <option value="<?= $id ?>" <?= ($filters['room_type_id'] ?? '') == $id ? 'selected' : '' ?>><?= esc($name) ?></option>
            <?php endforeach; ?>
        </select>

        <div class="flex gap-1.5">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white text-sm px-4 py-1.5 rounded-lg">Filter</button>
            <a href="<?= base_url('admin/locations') ?>" class="bg-gray-100 hover:bg-gray-200 text-gray-600 text-sm px-3 py-1.5 rounded-lg">Reset</a>
        </div>
    </form>
</div>

<!-- Table -->
<div class="bg-white border rounded-xl shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50 border-b text-xs text-gray-500 uppercase tracking-wide">
                    <th class="px-3 py-3 text-left">Foto</th>
                    <th class="px-3 py-3 text-left">Nama Lokasi</th>
                    <th class="px-3 py-3 text-left">Gedung / Lantai</th>
                    <th class="px-3 py-3 text-left">Tipe Ruangan</th>
                    <th class="px-3 py-3 text-left">Departemen</th>
                    <th class="px-3 py-3 text-center">Kapasitas</th>
                    <th class="px-3 py-3 text-center">Aset</th>
                    <th class="px-3 py-3 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <?php if (empty($locations)): ?>
                <tr><td colspan="8" class="text-center text-gray-400 py-10">
                    <div class="text-3xl mb-2">📍</div>Belum ada lokasi.
                </td></tr>
                <?php else: ?>
                <?php foreach ($locations as $l): ?>
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="px-3 py-2.5">
                        <?php if ($l['photo']): ?>
                        <img src="<?= base_url('uploads/locations/'.$l['photo']) ?>"
                             class="w-12 h-10 object-cover rounded-lg border shadow-sm" alt="">
                        <?php else: ?>
                        <div class="w-12 h-10 bg-gray-100 rounded-lg border flex items-center justify-center text-xl">📍</div>
                        <?php endif; ?>
                    </td>
                    <td class="px-3 py-2.5 font-semibold text-gray-800 min-w-[140px]">
                        <?= esc($l['name']) ?>
                        <?php if ($l['notes']): ?>
                        <p class="text-xs text-gray-400 font-normal mt-0.5 truncate max-w-[160px]"><?= esc($l['notes']) ?></p>
                        <?php endif; ?>
                    </td>
                    <td class="px-3 py-2.5 text-xs text-gray-600">
                        <div><?= esc($l['building'] ?? '—') ?></div>
                        <?php if ($l['floor']): ?>
                        <div class="text-gray-400">Lantai <?= esc($l['floor']) ?></div>
                        <?php endif; ?>
                    </td>
                    <td class="px-3 py-2.5 text-xs">
                        <?php if ($l['room_type_name']): ?>
                        <span class="px-2 py-0.5 bg-purple-50 text-purple-700 rounded-full font-medium">
                            <?= esc($l['room_type_name']) ?>
                        </span>
                        <?php else: ?>
                        <span class="text-gray-300">—</span>
                        <?php endif; ?>
                    </td>
                    <td class="px-3 py-2.5 text-xs text-gray-600"><?= esc($l['department_name'] ?? '—') ?></td>
                    <td class="px-3 py-2.5 text-center text-sm">
                        <?= $l['capacity'] ? number_format($l['capacity']) : '<span class="text-gray-300">—</span>' ?>
                    </td>
                    <td class="px-3 py-2.5 text-center">
                        <span class="font-bold text-blue-700"><?= number_format($l['asset_count'] ?? 0) ?></span>
                    </td>
                    <td class="px-3 py-2.5">
                        <div class="flex justify-center gap-1">
                            <a href="<?= base_url('admin/locations/'.$l['id'].'/edit') ?>"
                               class="p-1.5 rounded-lg bg-yellow-50 hover:bg-yellow-100 text-yellow-600 text-xs">✏️</a>
                            <button onclick="confirmDelete('<?= base_url('admin/locations/'.$l['id'].'/delete') ?>','<?= esc($l['name']) ?>')"
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

<div x-data="deleteModal()" x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-sm p-6 text-center">
        <div class="text-4xl mb-3">⚠️</div>
        <h3 class="font-bold text-gray-800">Hapus Lokasi</h3>
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
