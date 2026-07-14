<?= $this->extend('inventory/_layout') ?>
<?= $this->section('content') ?>

<div class="flex items-center justify-between mb-5">
    <div>
        <h1 class="text-xl font-bold text-gray-800">👥 Manajemen Staff</h1>
        <p class="text-sm text-gray-500 mt-0.5"><?= count($users) ?> akun terdaftar</p>
    </div>
    <a href="<?= base_url('admin/users/new') ?>"
       class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-lg shadow-sm">
        + Tambah Staff
    </a>
</div>

<?php if ($msg = session()->getFlashdata('success')): ?>
<div class="mb-4 px-4 py-3 bg-green-100 text-green-800 rounded-xl text-sm"><?= $msg ?></div>
<?php elseif ($msg = session()->getFlashdata('error')): ?>
<div class="mb-4 px-4 py-3 bg-red-100 text-red-800 rounded-xl text-sm"><?= $msg ?></div>
<?php endif; ?>

<!-- Stats per Role -->
<div class="grid grid-cols-3 sm:grid-cols-5 gap-3 mb-5">
    <?php
    $roleIcons = ['admin' => '🛡️', 'technician' => '🔧', 'it' => '💻', 'atem' => '🔬', 'user' => '👤'];
    $roleColors = [
        'admin'      => 'border-red-400 bg-red-50 text-red-700',
        'technician' => 'border-orange-400 bg-orange-50 text-orange-700',
        'it'         => 'border-purple-400 bg-purple-50 text-purple-700',
        'atem'       => 'border-teal-400 bg-teal-50 text-teal-700',
        'user'       => 'border-blue-400 bg-blue-50 text-blue-700',
    ];
    foreach ($roles as $key => $label):
    ?>
    <a href="?role=<?= $key ?>"
       class="border-l-4 <?= $roleColors[$key] ?> rounded-xl p-3 shadow-sm hover:shadow-md transition-shadow">
        <div class="text-2xl font-bold"><?= $stats[$key] ?? 0 ?></div>
        <div class="text-xs font-semibold mt-1"><?= $roleIcons[$key] ?> <?= $label ?></div>
    </a>
    <?php endforeach; ?>
</div>

<!-- Filter -->
<div class="bg-white border rounded-xl px-4 py-3 mb-4 shadow-sm">
    <form method="GET" class="flex flex-wrap gap-2 items-center">
        <input type="text" name="search" value="<?= esc($filters['search'] ?? '') ?>"
               placeholder="Cari nama / email / NIP..."
               class="flex-1 min-w-[180px] border border-gray-300 rounded-lg px-3 py-1.5 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none">

        <select name="role" class="border border-gray-300 rounded-lg px-3 py-1.5 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none">
            <option value="">Semua Peran</option>
            <?php foreach ($roles as $key => $label): ?>
            <option value="<?= $key ?>" <?= ($filters['role'] ?? '') === $key ? 'selected' : '' ?>><?= $label ?></option>
            <?php endforeach; ?>
        </select>

        <select name="department_id" class="border border-gray-300 rounded-lg px-3 py-1.5 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none">
            <option value="">Semua Departemen</option>
            <?php foreach ($departments as $id => $name): ?>
            <option value="<?= $id ?>" <?= ($filters['department_id'] ?? '') == $id ? 'selected' : '' ?>><?= esc($name) ?></option>
            <?php endforeach; ?>
        </select>

        <select name="is_active" class="border border-gray-300 rounded-lg px-3 py-1.5 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none">
            <option value="">Semua Status</option>
            <option value="1" <?= ($filters['is_active'] ?? '') === '1' ? 'selected' : '' ?>>Aktif</option>
            <option value="0" <?= ($filters['is_active'] ?? '') === '0' ? 'selected' : '' ?>>Nonaktif</option>
        </select>

        <div class="flex gap-1.5">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white text-sm px-4 py-1.5 rounded-lg">Filter</button>
            <a href="<?= base_url('admin/users') ?>" class="bg-gray-100 hover:bg-gray-200 text-gray-600 text-sm px-3 py-1.5 rounded-lg">Reset</a>
        </div>
    </form>
</div>

<!-- Tabel -->
<div class="bg-white border rounded-xl shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50 border-b text-xs text-gray-500 uppercase tracking-wide">
                    <th class="px-3 py-3 text-left">Avatar</th>
                    <th class="px-3 py-3 text-left">Nama / NIP</th>
                    <th class="px-3 py-3 text-left">Email</th>
                    <th class="px-3 py-3 text-left">Jabatan</th>
                    <th class="px-3 py-3 text-left">Departemen</th>
                    <th class="px-3 py-3 text-center">Peran</th>
                    <th class="px-3 py-3 text-center">Status</th>
                    <th class="px-3 py-3 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <?php if (empty($users)): ?>
                <tr><td colspan="8" class="text-center text-gray-400 py-10">
                    <div class="text-3xl mb-2">👥</div>Belum ada staff.
                </td></tr>
                <?php else: ?>
                <?php
                $roleBadge = [
                    'admin'      => 'bg-red-100 text-red-700',
                    'technician' => 'bg-orange-100 text-orange-700',
                    'it'         => 'bg-purple-100 text-purple-700',
                    'atem'       => 'bg-teal-100 text-teal-700',
                    'user'       => 'bg-blue-100 text-blue-700',
                ];
                foreach ($users as $u):
                    $isSelf = (int)$u['id'] === (int)session()->get('user_id');
                ?>
                <tr class="hover:bg-gray-50 <?= $isSelf ? 'bg-blue-50/40' : '' ?>">
                    <td class="px-3 py-2.5">
                        <?php if ($u['avatar']): ?>
                        <img src="<?= base_url('uploads/avatars/'.$u['avatar']) ?>"
                             class="w-9 h-9 rounded-full object-cover border shadow-sm" alt="">
                        <?php else: ?>
                        <div class="w-9 h-9 rounded-full bg-gradient-to-br from-blue-400 to-blue-600 flex items-center justify-center text-white text-sm font-bold">
                            <?= strtoupper(substr($u['name'], 0, 1)) ?>
                        </div>
                        <?php endif; ?>
                    </td>
                    <td class="px-3 py-2.5">
                        <div class="font-semibold text-gray-800">
                            <?= esc($u['name']) ?>
                            <?php if ($isSelf): ?><span class="text-xs text-blue-500 ml-1">(Anda)</span><?php endif; ?>
                        </div>
                        <?php if ($u['employee_id']): ?>
                        <code class="text-xs text-gray-400"><?= esc($u['employee_id']) ?></code>
                        <?php endif; ?>
                    </td>
                    <td class="px-3 py-2.5 text-xs text-gray-600"><?= esc($u['email']) ?></td>
                    <td class="px-3 py-2.5 text-xs text-gray-600">
                        <div><?= esc($u['position'] ?? '—') ?></div>
                        <?php if ($u['phone']): ?>
                        <div class="text-gray-400"><?= esc($u['phone']) ?></div>
                        <?php endif; ?>
                    </td>
                    <td class="px-3 py-2.5 text-xs text-gray-600"><?= esc($u['department_name'] ?? '—') ?></td>
                    <td class="px-3 py-2.5 text-center">
                        <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold <?= $roleBadge[$u['role']] ?? 'bg-gray-100 text-gray-600' ?>">
                            <?= $roleIcons[$u['role']] ?? '' ?> <?= $roles[$u['role']] ?? $u['role'] ?>
                        </span>
                    </td>
                    <td class="px-3 py-2.5 text-center">
                        <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium <?= $u['is_active'] ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' ?>">
                            <?= $u['is_active'] ? 'Aktif' : 'Nonaktif' ?>
                        </span>
                    </td>
                    <td class="px-3 py-2.5">
                        <div class="flex justify-center gap-1">
                            <a href="<?= base_url('admin/users/'.$u['id'].'/edit') ?>"
                               class="p-1.5 rounded-lg bg-yellow-50 hover:bg-yellow-100 text-yellow-600 text-xs">✏️</a>
                            <?php if (! $isSelf): ?>
                            <button onclick="confirmDelete('<?= base_url('admin/users/'.$u['id'].'/delete') ?>','<?= esc($u['name']) ?>')"
                                    class="p-1.5 rounded-lg bg-red-50 hover:bg-red-100 text-red-600 text-xs">🗑</button>
                            <?php endif; ?>
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
        <h3 class="font-bold text-gray-800">Hapus Staff</h3>
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
