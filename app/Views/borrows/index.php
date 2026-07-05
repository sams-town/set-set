<?= $this->extend('inventory/_layout') ?>
<?= $this->section('content') ?>

<div class="flex items-center justify-between mb-5">
    <div>
        <h1 class="text-xl font-bold text-gray-800">🔄 Peminjaman Aset</h1>
        <p class="text-sm text-gray-500 mt-0.5">
            Kelola peminjaman dan pengembalian aset
            <?php if ($overdue > 0): ?>
            · <span class="text-red-600 font-semibold">⚠️ <?= $overdue ?> terlambat</span>
            <?php endif; ?>
        </p>
    </div>
    <a href="<?= base_url('admin/borrows/new') ?>"
       class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-lg shadow-sm">
        + Catat Peminjaman
    </a>
</div>

<?php if ($msg = session()->getFlashdata('success')): ?>
<div class="mb-4 px-4 py-3 bg-green-100 text-green-800 rounded-xl text-sm">✅ <?= $msg ?></div>
<?php elseif ($msg = session()->getFlashdata('error')): ?>
<div class="mb-4 px-4 py-3 bg-red-100 text-red-800 rounded-xl text-sm">❌ <?= $msg ?></div>
<?php endif; ?>

<!-- Filter -->
<div class="bg-white border rounded-xl px-4 py-3 mb-4 shadow-sm">
    <form method="GET" class="flex flex-wrap gap-2 items-center">
        <input type="text" name="search"
               value="<?= esc($filters['search'] ?? '') ?>"
               placeholder="Cari kode / aset / peminjam..."
               class="flex-1 min-w-[200px] border border-gray-300 rounded-lg px-3 py-1.5 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none">

        <select name="status" class="border border-gray-300 rounded-lg px-3 py-1.5 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none">
            <option value="">Semua Status</option>
            <?php foreach (['dipinjam' => 'Dipinjam', 'dikembalikan' => 'Dikembalikan', 'terlambat' => 'Terlambat'] as $v => $l): ?>
            <option value="<?= $v ?>" <?= ($filters['status'] ?? '') === $v ? 'selected' : '' ?>><?= $l ?></option>
            <?php endforeach; ?>
        </select>

        <div class="flex gap-1.5">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white text-sm px-4 py-1.5 rounded-lg">Filter</button>
            <a href="<?= base_url('admin/borrows') ?>" class="bg-gray-100 hover:bg-gray-200 text-gray-600 text-sm px-3 py-1.5 rounded-lg">Reset</a>
        </div>
    </form>
</div>

<!-- Tabel -->
<div class="bg-white border rounded-xl shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50 border-b text-xs text-gray-500 uppercase tracking-wide">
                    <th class="px-4 py-3 text-left">Kode</th>
                    <th class="px-4 py-3 text-left">Aset</th>
                    <th class="px-4 py-3 text-left">Peminjam</th>
                    <th class="px-4 py-3 text-left">Tgl Pinjam</th>
                    <th class="px-4 py-3 text-left">Rencana Kembali</th>
                    <th class="px-4 py-3 text-center">Status</th>
                    <th class="px-4 py-3 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <?php if (empty($borrows)): ?>
                <tr>
                    <td colspan="7" class="text-center text-gray-400 py-12">
                        <div class="text-3xl mb-2">📭</div>
                        Tidak ada data peminjaman.
                    </td>
                </tr>
                <?php else: ?>
                <?php foreach ($borrows as $b):
                    $isOverdueRow = ($b['status'] === 'dipinjam')
                        && !empty($b['return_date_plan'])
                        && $b['return_date_plan'] < date('Y-m-d');
                    $stBadge = [
                        'dipinjam'     => 'bg-yellow-100 text-yellow-700',
                        'dikembalikan' => 'bg-green-100 text-green-700',
                        'terlambat'    => 'bg-red-100 text-red-700',
                    ];
                ?>
                <tr class="hover:bg-gray-50 transition-colors <?= $isOverdueRow ? 'bg-red-50/20' : '' ?>">
                    <td class="px-4 py-2.5">
                        <a href="<?= base_url('admin/borrows/' . $b['id']) ?>"
                           class="font-mono text-blue-600 hover:underline font-semibold text-sm">
                            <?= esc($b['borrow_code']) ?>
                        </a>
                    </td>
                    <td class="px-4 py-2.5 min-w-[150px]">
                        <div class="font-semibold text-gray-800"><?= esc($b['asset_name']) ?></div>
                        <code class="text-xs text-gray-400"><?= esc($b['asset_code']) ?></code>
                    </td>
                    <td class="px-4 py-2.5 text-gray-700"><?= esc($b['user_name']) ?></td>
                    <td class="px-4 py-2.5 text-sm text-gray-600">
                        <?= $b['borrow_date'] ? date('d M Y', strtotime($b['borrow_date'])) : '-' ?>
                    </td>
                    <td class="px-4 py-2.5 text-sm">
                        <?php if ($b['return_date_plan']): ?>
                            <span class="<?= $isOverdueRow ? 'text-red-600 font-semibold' : 'text-gray-600' ?>">
                                <?= date('d M Y', strtotime($b['return_date_plan'])) ?>
                                <?= $isOverdueRow ? ' ⚠️' : '' ?>
                            </span>
                        <?php else: ?>
                            <span class="text-gray-400">—</span>
                        <?php endif; ?>
                    </td>
                    <td class="px-4 py-2.5 text-center">
                        <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-semibold <?= $stBadge[$b['status']] ?? 'bg-gray-100 text-gray-600' ?>">
                            <?= ucfirst($b['status']) ?>
                        </span>
                    </td>
                    <td class="px-4 py-2.5 text-center">
                        <a href="<?= base_url('admin/borrows/' . $b['id']) ?>"
                           class="p-1.5 rounded-lg bg-blue-50 hover:bg-blue-100 text-blue-600 text-xs inline-flex" title="Detail">
                            👁
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <div class="px-4 py-3 bg-gray-50 border-t text-sm text-gray-500">
        Total: <strong><?= count($borrows) ?></strong> record
    </div>
</div>

<?= $this->endSection() ?>
