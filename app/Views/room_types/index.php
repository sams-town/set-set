<?= $this->extend('inventory/_layout') ?>
<?= $this->section('content') ?>

<div class="flex items-center justify-between mb-5">
    <div>
        <h1 class="text-xl font-bold text-gray-800">🚪 Tipe / Jenis Ruangan</h1>
        <p class="text-sm text-gray-500 mt-0.5"><?= count($room_types) ?> tipe terdaftar</p>
    </div>
    <a href="<?= base_url('admin/room-types/new') ?>"
       class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-lg shadow-sm">
        + Tambah Tipe Ruangan
    </a>
</div>

<?php if ($msg = session()->getFlashdata('success')): ?>
<div class="mb-4 px-4 py-3 bg-green-100 text-green-800 rounded-xl text-sm"><?= $msg ?></div>
<?php endif; ?>
<?php if ($msg = session()->getFlashdata('error')): ?>
<div class="mb-4 px-4 py-3 bg-red-100 text-red-800 rounded-xl text-sm"><?= $msg ?></div>
<?php endif; ?>

<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
    <?php if (empty($room_types)): ?>
    <div class="col-span-full text-center text-gray-400 py-12">
        <div class="text-4xl mb-2">🚪</div>Belum ada tipe ruangan.
    </div>
    <?php else: ?>
    <?php foreach ($room_types as $rt): ?>
    <div class="bg-white border rounded-xl p-4 shadow-sm hover:shadow-md transition-shadow flex flex-col gap-3">
        <div class="flex items-start justify-between">
            <div>
                <div class="text-base font-bold text-gray-800"><?= esc($rt['name']) ?></div>
                <?php if ($rt['code']): ?>
                <code class="text-xs bg-blue-50 text-blue-700 px-2 py-0.5 rounded font-mono mt-1 inline-block"><?= esc($rt['code']) ?></code>
                <?php endif; ?>
            </div>
            <span class="px-2 py-0.5 rounded-full text-xs font-medium <?= $rt['is_active'] ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' ?>">
                <?= $rt['is_active'] ? 'Aktif' : 'Nonaktif' ?>
            </span>
        </div>

        <?php if ($rt['description']): ?>
        <p class="text-xs text-gray-500 leading-relaxed"><?= esc($rt['description']) ?></p>
        <?php endif; ?>

        <div class="flex items-center justify-between pt-2 border-t">
            <span class="text-xs text-gray-400">
                <strong class="text-blue-600"><?= number_format($rt['location_count'] ?? 0) ?></strong> lokasi
            </span>
            <div class="flex gap-1">
                <a href="<?= base_url('admin/room-types/'.$rt['id'].'/edit') ?>"
                   class="p-1.5 rounded-lg bg-yellow-50 hover:bg-yellow-100 text-yellow-600 text-xs">✏️</a>
                <button onclick="confirmDelete('<?= base_url('admin/room-types/'.$rt['id'].'/delete') ?>','<?= esc($rt['name']) ?>')"
                        class="p-1.5 rounded-lg bg-red-50 hover:bg-red-100 text-red-600 text-xs">🗑</button>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
    <?php endif; ?>
</div>

<div x-data="deleteModal()" x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-sm p-6 text-center">
        <div class="text-4xl mb-3">⚠️</div>
        <h3 class="font-bold text-gray-800">Hapus Tipe Ruangan</h3>
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
