<?= $this->extend('inventory/_layout') ?>
<?= $this->section('content') ?>
<?php
$isEdit = !empty($user);
$v = fn($key, $default = '') => old($key, $user[$key] ?? $default);
?>

<div class="flex items-center gap-2 text-sm text-gray-500 mb-4">
    <a href="<?= base_url('admin/users') ?>" class="hover:text-blue-600">Staff</a>
    <span>›</span>
    <span class="font-medium text-gray-800"><?= $isEdit ? 'Edit ' . esc($user['name']) : 'Tambah Staff Baru' ?></span>
</div>

<div class="max-w-3xl">
<h1 class="text-xl font-bold text-gray-800 mb-5"><?= $isEdit ? '✏️ Edit Staff' : '👤 Tambah Staff Baru' ?></h1>

<?php if ($errors = session()->getFlashdata('errors')): ?>
<div class="mb-4 px-4 py-3 bg-red-50 border border-red-200 rounded-xl text-sm text-red-700">
    <ul class="list-disc list-inside space-y-1">
        <?php foreach ((array)$errors as $e): ?><li><?= esc($e) ?></li><?php endforeach; ?>
    </ul>
</div>
<?php endif; ?>

<form action="<?= $isEdit ? base_url('admin/users/'.$user['id'].'/update') : base_url('admin/users') ?>"
      method="POST" enctype="multipart/form-data" class="space-y-5">
    <?= csrf_field() ?>

    <!-- SECTION 1: Identitas -->
    <div class="bg-white border rounded-xl p-5 shadow-sm">
        <h2 class="text-sm font-bold text-gray-700 uppercase tracking-wide mb-4 pb-2 border-b flex items-center gap-2">
            <span class="text-blue-600">👤</span> Identitas Staff
        </h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap <span class="text-red-500">*</span></label>
                <input type="text" name="name" required value="<?= $v('name') ?>"
                       placeholder="Nama lengkap staff"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">NIP / Employee ID</label>
                <input type="text" name="employee_id" value="<?= $v('employee_id') ?>"
                       placeholder="Nomor Induk Pegawai"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Jabatan / Posisi</label>
                <input type="text" name="position" value="<?= $v('position') ?>"
                       placeholder="Staf IT, Teknisi, Supervisor"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">No. HP / WhatsApp</label>
                <input type="text" name="phone" value="<?= $v('phone') ?>"
                       placeholder="628123456789 (format internasional)"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none">
                <p class="text-xs text-gray-400 mt-1">Digunakan untuk notifikasi WhatsApp</p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Departemen</label>
                <select name="department_id"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none">
                    <option value="">-- Pilih Departemen --</option>
                    <?php foreach ($departments as $did => $dname): ?>
                    <option value="<?= $did ?>" <?= $v('department_id') == $did ? 'selected' : '' ?>><?= esc($dname) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Catatan</label>
                <textarea name="notes" rows="2"
                          class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none resize-none"><?= $v('notes') ?></textarea>
            </div>
        </div>
    </div>

    <!-- SECTION 2: Akun & Peran -->
    <div class="bg-white border rounded-xl p-5 shadow-sm">
        <h2 class="text-sm font-bold text-gray-700 uppercase tracking-wide mb-4 pb-2 border-b flex items-center gap-2">
            <span class="text-red-600">🔐</span> Akun & Peran (Role)
        </h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Email <span class="text-red-500">*</span></label>
                <input type="email" name="email" required value="<?= $v('email') ?>"
                       placeholder="email@perusahaan.com"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Password <?= $isEdit ? '<span class="font-normal text-gray-400">(kosongkan jika tidak diubah)</span>' : '<span class="text-red-500">*</span>' ?>
                </label>
                <input type="password" name="password" <?= $isEdit ? '' : 'required' ?>
                       placeholder="Min. 6 karakter"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none">
            </div>

            <!-- Pilihan Peran -->
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-2">Peran (Role) <span class="text-red-500">*</span></label>
                <div class="grid grid-cols-1 sm:grid-cols-3 lg:grid-cols-5 gap-3">
                    <?php
                    $roleDesc = [
                        'admin'      => 'Akses penuh ke semua modul',
                        'technician' => 'Kelola WO & PM, tidak akses master data',
                        'it'         => 'Kelola WO & PM, tidak akses master data',
                        'atem'       => 'Kelola WO & PM, tidak akses master data',
                        'user'       => 'Input keluhan & lihat status aset',
                    ];
                    $roleIcons = ['admin' => '🛡️', 'technician' => '🔧', 'it' => '💻', 'atem' => '🔬', 'user' => '👤'];
                    $roleColors = [
                        'admin'      => 'border-red-300 bg-red-50',
                        'technician' => 'border-orange-300 bg-orange-50',
                        'it'         => 'border-purple-300 bg-purple-50',
                        'atem'       => 'border-teal-300 bg-teal-50',
                        'user'       => 'border-blue-300 bg-blue-50',
                    ];
                    $selectedRole = $v('role', 'user');
                    foreach ($roles as $key => $label):
                    ?>
                    <label class="flex flex-col gap-1 border-2 rounded-xl p-3 cursor-pointer transition-all
                                  <?= $selectedRole === $key ? 'border-blue-500 bg-blue-50' : 'border-gray-200 hover:border-gray-300' ?>"
                           id="roleCard_<?= $key ?>">
                        <div class="flex items-center gap-2">
                            <input type="radio" name="role" value="<?= $key ?>"
                                   <?= $selectedRole === $key ? 'checked' : '' ?>
                                   onchange="highlightRole()"
                                   class="text-blue-600">
                            <span class="font-semibold text-gray-800 text-sm">
                                <?= $roleIcons[$key] ?> <?= $label ?>
                            </span>
                        </div>
                        <p class="text-xs text-gray-500 pl-5"><?= $roleDesc[$key] ?></p>
                    </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <?php if ($isEdit): ?>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Status Akun</label>
                <select name="is_active"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none">
                    <option value="1" <?= ($user['is_active'] ?? 1) == 1 ? 'selected' : '' ?>>✅ Aktif</option>
                    <option value="0" <?= ($user['is_active'] ?? 1) == 0 ? 'selected' : '' ?>>❌ Nonaktif</option>
                </select>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- SECTION 3: Avatar -->
    <div class="bg-white border rounded-xl p-5 shadow-sm">
        <h2 class="text-sm font-bold text-gray-700 uppercase tracking-wide mb-4 pb-2 border-b flex items-center gap-2">
            <span class="text-pink-600">🖼️</span> Foto Profil / Avatar
        </h2>
        <div class="flex items-start gap-4">
            <?php if ($isEdit && !empty($user['avatar'])): ?>
            <img src="<?= base_url('uploads/avatars/'.$user['avatar']) ?>"
                 class="w-20 h-20 rounded-full object-cover border-2 border-gray-200 shadow-sm" alt="">
            <?php else: ?>
            <div class="w-20 h-20 rounded-full bg-gradient-to-br from-blue-400 to-blue-600 flex items-center justify-center text-white text-2xl font-bold shrink-0">
                <?= $isEdit ? strtoupper(substr($user['name'], 0, 1)) : '?' ?>
            </div>
            <?php endif; ?>
            <div class="flex-1">
                <input type="file" name="avatar" accept="image/*"
                       class="w-full text-sm text-gray-600 file:mr-3 file:py-1.5 file:px-4 file:rounded-full
                              file:border-0 file:text-sm file:font-medium file:bg-blue-50 file:text-blue-700
                              hover:file:bg-blue-100 cursor-pointer">
                <p class="text-xs text-gray-400 mt-1">JPG, PNG, WebP — Maks 2 MB. Auto-resize ke 400×400px.</p>
            </div>
        </div>
    </div>

    <div class="flex gap-3">
        <button type="submit"
                class="<?= $isEdit ? 'bg-yellow-500 hover:bg-yellow-600' : 'bg-blue-600 hover:bg-blue-700' ?> text-white font-semibold px-6 py-2.5 rounded-xl text-sm">
            <?= $isEdit ? '💾 Simpan Perubahan' : '✅ Tambah Staff' ?>
        </button>
        <a href="<?= base_url('admin/users') ?>"
           class="border border-gray-300 text-gray-700 hover:bg-gray-50 px-5 py-2.5 rounded-xl text-sm">Batal</a>
    </div>
</form>
</div>

<script>
function highlightRole() {
    document.querySelectorAll('[id^="roleCard_"]').forEach(card => {
        card.classList.remove('border-blue-500','bg-blue-50');
        card.classList.add('border-gray-200');
    });
    const checked = document.querySelector('input[name="role"]:checked');
    if (checked) {
        const card = document.getElementById('roleCard_' + checked.value);
        if (card) {
            card.classList.remove('border-gray-200');
            card.classList.add('border-blue-500','bg-blue-50');
        }
    }
}
</script>

<?= $this->endSection() ?>
