<?= $this->extend('inventory/_layout') ?>
<?= $this->section('content') ?>
<?php
$stBadge = [
    'dipinjam'     => 'bg-yellow-100 text-yellow-700',
    'dikembalikan' => 'bg-green-100 text-green-700',
    'terlambat'    => 'bg-red-100 text-red-700',
];
$isLate = ($borrow['status'] === 'dipinjam')
    && !empty($borrow['return_date_plan'])
    && $borrow['return_date_plan'] < date('Y-m-d');
?>

<!-- Breadcrumb -->
<div class="flex items-center gap-2 text-sm text-gray-500 mb-4">
    <a href="<?= base_url('admin/borrows') ?>" class="hover:text-blue-600">Peminjaman</a>
    <span>›</span>
    <span class="font-mono font-medium text-gray-800"><?= esc($borrow['borrow_code']) ?></span>
</div>

<!-- Header -->
<div class="flex items-start justify-between mb-5 gap-4 flex-wrap">
    <div>
        <h1 class="text-xl font-bold text-gray-800">Detail Peminjaman</h1>
        <div class="flex items-center gap-2 mt-1.5 flex-wrap">
            <code class="text-sm text-gray-500 font-mono"><?= esc($borrow['borrow_code']) ?></code>
            <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold <?= $stBadge[$borrow['status']] ?? 'bg-gray-100 text-gray-600' ?>">
                <?= ucfirst($borrow['status']) ?>
            </span>
            <?php if ($isLate): ?>
            <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-red-100 text-red-700">
                ⚠️ Terlambat
            </span>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

    <!-- Kolom Kiri: Detail -->
    <div class="lg:col-span-2 space-y-5">

        <!-- Info Peminjaman -->
        <div class="bg-white border rounded-xl shadow-sm overflow-hidden">
            <div class="px-4 py-3 bg-gray-50 border-b">
                <h2 class="text-sm font-bold text-gray-700">📋 Informasi Peminjaman</h2>
            </div>
            <div class="p-5">
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                    <div>
                        <dt class="text-xs text-gray-400 mb-0.5">Aset</dt>
                        <dd>
                            <a href="<?= base_url('admin/inventory/' . $borrow['asset_id']) ?>"
                               class="font-semibold text-blue-600 hover:underline">
                                <?= esc($borrow['asset_name']) ?>
                            </a>
                            <code class="text-xs text-gray-400 block mt-0.5"><?= esc($borrow['asset_code']) ?></code>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-xs text-gray-400 mb-0.5">Peminjam</dt>
                        <dd class="font-semibold text-gray-800">
                            <?= esc($borrow['user_name']) ?>
                            <span class="text-xs text-gray-400 font-normal block"><?= esc($borrow['user_email'] ?? '') ?></span>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-xs text-gray-400 mb-0.5">Tanggal Pinjam</dt>
                        <dd class="font-semibold text-gray-800">
                            <?= $borrow['borrow_date'] ? date('d M Y', strtotime($borrow['borrow_date'])) : '-' ?>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-xs text-gray-400 mb-0.5">Rencana Kembali</dt>
                        <dd class="font-semibold <?= $isLate ? 'text-red-600' : 'text-gray-800' ?>">
                            <?= $borrow['return_date_plan'] ? date('d M Y', strtotime($borrow['return_date_plan'])) : '—' ?>
                            <?= $isLate ? ' <span class="text-xs">(Terlambat)</span>' : '' ?>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-xs text-gray-400 mb-0.5">Tanggal Kembali Aktual</dt>
                        <dd class="font-semibold text-gray-800">
                            <?= !empty($borrow['return_date_actual'])
                                ? date('d M Y', strtotime($borrow['return_date_actual']))
                                : '<span class="text-gray-400 font-normal">Belum dikembalikan</span>' ?>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-xs text-gray-400 mb-0.5">Disetujui Oleh</dt>
                        <dd class="font-semibold text-gray-800"><?= esc($borrow['approver_name'] ?? '—') ?></dd>
                    </div>
                </dl>

                <?php if (!empty($borrow['purpose'])): ?>
                <div class="mt-4 pt-4 border-t">
                    <p class="text-xs text-gray-400 mb-1">Keperluan / Tujuan</p>
                    <p class="text-sm text-gray-700 leading-relaxed"><?= nl2br(esc($borrow['purpose'])) ?></p>
                </div>
                <?php endif; ?>

                <?php if (!empty($borrow['notes'])): ?>
                <div class="mt-3 pt-3 border-t">
                    <p class="text-xs text-gray-400 mb-1">Catatan</p>
                    <p class="text-sm text-gray-700 leading-relaxed"><?= nl2br(esc($borrow['notes'])) ?></p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Kolom Kanan: Aksi -->
    <div class="space-y-4">

        <!-- Panel Kembalikan -->
        <?php if ($borrow['status'] === 'dipinjam'): ?>
        <div class="bg-white border-2 <?= $isLate ? 'border-red-300' : 'border-yellow-300' ?> rounded-xl shadow-sm overflow-hidden">
            <div class="px-4 py-3 <?= $isLate ? 'bg-red-50' : 'bg-yellow-50' ?> border-b">
                <h2 class="text-sm font-bold <?= $isLate ? 'text-red-700' : 'text-yellow-700' ?>">
                    🔙 Kembalikan Aset
                </h2>
                <?php if ($isLate): ?>
                <p class="text-xs text-red-600 mt-0.5">Peminjaman sudah melewati batas waktu!</p>
                <?php endif; ?>
            </div>
            <div class="p-4">
                <form action="<?= base_url('admin/borrows/' . $borrow['id'] . '/return') ?>" method="POST">
                    <?= csrf_field() ?>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Catatan Pengembalian
                        </label>
                        <textarea name="notes" rows="3"
                                  placeholder="Kondisi aset saat dikembalikan..."
                                  class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none resize-none"></textarea>
                    </div>
                    <button type="submit"
                            onclick="return confirm('Konfirmasi pengembalian aset ini?')"
                            class="w-full <?= $isLate ? 'bg-red-600 hover:bg-red-700' : 'bg-yellow-500 hover:bg-yellow-600' ?> text-white font-semibold py-2.5 rounded-xl text-sm">
                        ✅ Konfirmasi Dikembalikan
                    </button>
                </form>
            </div>
        </div>
        <?php else: ?>
        <div class="bg-green-50 border border-green-200 rounded-xl p-4 text-center">
            <div class="text-3xl mb-2">✅</div>
            <p class="text-sm font-semibold text-green-700">Aset Sudah Dikembalikan</p>
            <?php if (!empty($borrow['return_date_actual'])): ?>
            <p class="text-xs text-gray-500 mt-1">
                pada <?= date('d M Y', strtotime($borrow['return_date_actual'])) ?>
            </p>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <!-- Tombol kembali -->
        <a href="<?= base_url('admin/borrows') ?>"
           class="flex items-center justify-center gap-2 border border-gray-300 text-gray-700 hover:bg-gray-50 py-2 rounded-xl text-sm font-medium transition-colors">
            ← Kembali ke Daftar
        </a>
    </div>
</div>

<?= $this->endSection() ?>
