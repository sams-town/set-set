<?= $this->extend('inventory/_layout') ?>
<?= $this->section('content') ?>

<div class="flex items-center gap-2 text-sm text-gray-500 mb-4">
    <a href="<?= base_url('admin/borrows') ?>" class="hover:text-blue-600">Peminjaman</a>
    <span>›</span>
    <span class="font-medium text-gray-800">Catat Peminjaman Baru</span>
</div>

<div class="max-w-2xl">
<h1 class="text-xl font-bold text-gray-800 mb-5">🔄 Catat Peminjaman</h1>

<?php if ($errors = session()->getFlashdata('errors')): ?>
<div class="mb-4 px-4 py-3 bg-red-50 border border-red-200 rounded-xl text-sm text-red-700">
    <ul class="list-disc list-inside space-y-1">
        <?php foreach ((array)$errors as $e): ?><li><?= esc($e) ?></li><?php endforeach; ?>
    </ul>
</div>
<?php endif; ?>

<form action="<?= base_url('admin/borrows') ?>" method="POST"
      class="bg-white border rounded-xl p-6 shadow-sm space-y-4">
    <?= csrf_field() ?>

    <!-- Aset -->
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">
            Aset yang Dipinjam <span class="text-red-500">*</span>
        </label>
        <select name="asset_id" required
                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none">
            <option value="">-- Pilih Aset --</option>
            <?php foreach ($assets as $a): ?>
            <option value="<?= $a['id'] ?>" <?= old('asset_id') == $a['id'] ? 'selected' : '' ?>>
                [<?= esc($a['asset_code']) ?>] <?= esc($a['name']) ?>
                <?= !empty($a['department_name']) ? ' — ' . esc($a['department_name']) : '' ?>
            </option>
            <?php endforeach; ?>
        </select>
        <p class="text-xs text-gray-400 mt-1">Hanya aset berstatus <strong>Tersedia</strong> yang ditampilkan.</p>
    </div>

    <!-- Peminjam -->
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">
            Peminjam <span class="text-red-500">*</span>
        </label>
        <select name="user_id" required
                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none">
            <option value="">-- Pilih Peminjam --</option>
            <?php foreach ($users as $u): ?>
            <option value="<?= $u['id'] ?>" <?= old('user_id') == $u['id'] ? 'selected' : '' ?>>
                <?= esc($u['name']) ?> (<?= esc($u['email']) ?>)
            </option>
            <?php endforeach; ?>
        </select>
    </div>

    <!-- Tanggal -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
                Tanggal Pinjam <span class="text-red-500">*</span>
            </label>
            <input type="date" name="borrow_date" required
                   value="<?= old('borrow_date', date('Y-m-d')) ?>"
                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Rencana Kembali</label>
            <input type="date" name="return_date_plan"
                   value="<?= old('return_date_plan') ?>"
                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none">
        </div>
    </div>

    <!-- Keperluan -->
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Keperluan / Tujuan</label>
        <textarea name="purpose" rows="3"
                  placeholder="Tuliskan tujuan peminjaman..."
                  class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none resize-none"><?= old('purpose') ?></textarea>
    </div>

    <div class="flex gap-3 pt-2">
        <button type="submit"
                class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-2.5 rounded-xl text-sm">
            💾 Catat Peminjaman
        </button>
        <a href="<?= base_url('admin/borrows') ?>"
           class="border border-gray-300 text-gray-700 hover:bg-gray-50 px-5 py-2.5 rounded-xl text-sm">
            Batal
        </a>
    </div>
</form>
</div>

<?= $this->endSection() ?>
