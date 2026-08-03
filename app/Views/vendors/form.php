<?= $this->extend('inventory/_layout') ?>
<?= $this->section('content') ?>
<?php
$isEdit = !empty($vendor);
$v = fn($key, $default = '') => old($key, $vendor[$key] ?? $default);
?>

<!-- Breadcrumb -->
<div class="flex items-center gap-2 text-sm text-gray-500 mb-4">
    <a href="<?= base_url('admin/vendors') ?>" class="hover:text-blue-600">Vendor</a>
    <span>›</span>
    <span class="font-medium text-gray-800"><?= $isEdit ? 'Edit ' . esc($vendor['name']) : 'Tambah Vendor' ?></span>
</div>

<div class="max-w-2xl">
    <h1 class="text-xl font-bold text-gray-800 mb-5">
        <?= $isEdit ? '✏️ Edit Vendor' : '🏭 Tambah Vendor Baru' ?>
    </h1>

    <?php if (!empty($errors = session()->getFlashdata('errors'))): ?>
    <div class="mb-4 px-4 py-3 bg-red-50 border border-red-200 rounded-xl text-sm text-red-700 space-y-1">
        <?php foreach ((array)$errors as $e): ?><p>• <?= esc($e) ?></p><?php endforeach; ?>
    </div>
    <?php endif; ?>

    <form action="<?= $isEdit ? base_url('admin/vendors/'.$vendor['id'].'/update') : base_url('admin/vendors') ?>"
          method="POST" class="space-y-5">
        <?= csrf_field() ?>

        <!-- Identitas Vendor -->
        <div class="bg-white border rounded-xl p-5 shadow-sm">
            <h2 class="text-sm font-bold text-gray-700 uppercase tracking-wide mb-4 pb-2 border-b flex items-center gap-2">
                <span class="text-blue-600">🏭</span> Identitas Vendor
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Nama Vendor <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="name"
                           value="<?= $v('name') ?>"
                           placeholder="Nama lengkap perusahaan vendor"
                           required
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Kode Vendor</label>
                    <input type="text" name="code"
                           value="<?= $v('code') ?>"
                           placeholder="Contoh: VND-001"
                           maxlength="20"
                           oninput="this.value = this.value.toUpperCase()"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none uppercase">
                    <p class="text-xs text-gray-400 mt-1">Kode unik, opsional</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Kategori <span class="text-red-500">*</span>
                    </label>
                    <select name="category" required
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none">
                        <?php foreach ($categories as $key => $label): ?>
                        <option value="<?= $key ?>" <?= $v('category', 'umum') === $key ? 'selected' : '' ?>>
                            <?= $label ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

            </div>
        </div>

        <!-- Kontak -->
        <div class="bg-white border rounded-xl p-5 shadow-sm">
            <h2 class="text-sm font-bold text-gray-700 uppercase tracking-wide mb-4 pb-2 border-b flex items-center gap-2">
                <span class="text-green-600">📞</span> Informasi Kontak
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nama Kontak / PIC</label>
                    <input type="text" name="contact"
                           value="<?= $v('contact') ?>"
                           placeholder="Nama person in charge"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">No. Telepon / HP</label>
                    <input type="text" name="phone"
                           value="<?= $v('phone') ?>"
                           placeholder="08xx / 021-xxxx"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input type="email" name="email"
                           value="<?= $v('email') ?>"
                           placeholder="email@vendor.com"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Alamat</label>
                    <textarea name="address" rows="2"
                              placeholder="Alamat lengkap vendor..."
                              class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none resize-none"><?= $v('address') ?></textarea>
                </div>

            </div>
        </div>

        <!-- Catatan & Status -->
        <div class="bg-white border rounded-xl p-5 shadow-sm">
            <h2 class="text-sm font-bold text-gray-700 uppercase tracking-wide mb-4 pb-2 border-b flex items-center gap-2">
                <span class="text-gray-500">📌</span> Catatan & Status
            </h2>
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Catatan</label>
                    <textarea name="notes" rows="3"
                              placeholder="Catatan tambahan tentang vendor ini..."
                              class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none resize-none"><?= $v('notes') ?></textarea>
                </div>

                <?php if ($isEdit): ?>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <select name="is_active"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none">
                        <option value="1" <?= $v('is_active', '1') == '1' ? 'selected' : '' ?>>✅ Aktif</option>
                        <option value="0" <?= $v('is_active', '1') == '0' ? 'selected' : '' ?>>❌ Nonaktif</option>
                    </select>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Tombol -->
        <div class="flex items-center gap-3">
            <button type="submit"
                    class="<?= $isEdit ? 'bg-yellow-500 hover:bg-yellow-600' : 'bg-blue-600 hover:bg-blue-700' ?>
                           text-white font-semibold px-6 py-2.5 rounded-xl text-sm transition-colors shadow-sm">
                <?= $isEdit ? '💾 Perbarui Vendor' : '✅ Simpan Vendor' ?>
            </button>
            <a href="<?= base_url('admin/vendors') ?>"
               class="border border-gray-300 text-gray-700 hover:bg-gray-50 px-5 py-2.5 rounded-xl text-sm font-medium">
                Batal
            </a>
        </div>
    </form>
</div>

<?= $this->endSection() ?>
