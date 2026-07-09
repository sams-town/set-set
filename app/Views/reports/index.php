<?= $this->extend('inventory/_layout') ?>
<?= $this->section('content') ?>
<?php $rp = fn($n) => 'Rp ' . number_format((float)$n, 0, ',', '.'); ?>

<!-- Page Header -->
<div class="flex items-center justify-between mb-5">
    <div>
        <h1 class="text-xl font-bold text-gray-800">📑 Laporan</h1>
        <p class="text-sm text-gray-500 mt-0.5">Laporan aset, peminjaman, dan aktivitas</p>
    </div>
    <a href="<?= base_url('admin/reports/print?' . http_build_query(array_filter(array_merge(['type' => $type], $_GET)))) ?>"
       target="_blank"
       class="inline-flex items-center gap-2 border border-gray-300 text-gray-700 hover:bg-gray-50 text-sm font-medium px-4 py-2 rounded-lg transition-colors">
        🖨️ Cetak
    </a>
</div>

<!-- Tab Jenis Laporan -->
<div class="flex gap-1 mb-5 bg-gray-100 rounded-xl p-1 w-fit">
    <?php foreach ([
        'assets'  => ['icon' => '🗃️', 'label' => 'Aset'],
        'borrows' => ['icon' => '🔄', 'label' => 'Peminjaman'],
        'logs'    => ['icon' => '🕐', 'label' => 'Log Aktivitas'],
    ] as $key => $tab): ?>
    <a href="<?= base_url('admin/reports?type=' . $key) ?>"
       class="px-4 py-2 rounded-lg text-sm font-medium transition-all
              <?= $type === $key ? 'bg-white text-blue-700 shadow-sm' : 'text-gray-600 hover:text-gray-800' ?>">
        <?= $tab['icon'] ?> <?= $tab['label'] ?>
    </a>
    <?php endforeach; ?>
</div>

<!-- Filter -->
<?php if ($type === 'assets'): ?>
<div class="bg-white border rounded-xl px-4 py-3 mb-4 shadow-sm">
    <form method="GET" class="flex flex-wrap gap-2 items-center">
        <input type="hidden" name="type" value="assets">

        <select name="status" class="border border-gray-300 rounded-lg px-3 py-1.5 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none">
            <option value="">Semua Status</option>
            <?php foreach (['tersedia','dipinjam','dalam_perbaikan','dihapus'] as $s): ?>
            <option value="<?= $s ?>" <?= ($_GET['status'] ?? '') === $s ? 'selected' : '' ?>>
                <?= ucwords(str_replace('_',' ',$s)) ?>
            </option>
            <?php endforeach; ?>
        </select>

        <select name="category" class="border border-gray-300 rounded-lg px-3 py-1.5 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none">
            <option value="">Semua Kategori</option>
            <?php foreach ($categories as $cat): ?>
            <option value="<?= esc($cat) ?>" <?= ($_GET['category'] ?? '') === $cat ? 'selected' : '' ?>>
                <?= esc($cat) ?>
            </option>
            <?php endforeach; ?>
        </select>

        <select name="department_id" class="border border-gray-300 rounded-lg px-3 py-1.5 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none">
            <option value="">Semua Departemen</option>
            <?php foreach ($departments as $did => $dname): ?>
            <option value="<?= $did ?>" <?= ($_GET['department_id'] ?? '') == $did ? 'selected' : '' ?>>
                <?= esc($dname) ?>
            </option>
            <?php endforeach; ?>
        </select>

        <div class="flex gap-1.5">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white text-sm px-4 py-1.5 rounded-lg">Filter</button>
            <a href="<?= base_url('admin/reports?type=assets') ?>" class="bg-gray-100 hover:bg-gray-200 text-gray-600 text-sm px-3 py-1.5 rounded-lg">Reset</a>
        </div>
    </form>
</div>
<?php elseif ($type === 'borrows'): ?>
<div class="bg-white border rounded-xl px-4 py-3 mb-4 shadow-sm">
    <form method="GET" class="flex flex-wrap gap-2 items-center">
        <input type="hidden" name="type" value="borrows">
        <select name="status" class="border border-gray-300 rounded-lg px-3 py-1.5 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none">
            <option value="">Semua Status</option>
            <?php foreach (['dipinjam' => 'Dipinjam','dikembalikan' => 'Dikembalikan','terlambat' => 'Terlambat'] as $v => $l): ?>
            <option value="<?= $v ?>" <?= ($_GET['status'] ?? '') === $v ? 'selected' : '' ?>><?= $l ?></option>
            <?php endforeach; ?>
        </select>
        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white text-sm px-4 py-1.5 rounded-lg">Filter</button>
    </form>
</div>
<?php endif; ?>

<!-- Tabel Data -->
<div class="bg-white border rounded-xl shadow-sm overflow-hidden">
    <div class="px-4 py-3 bg-gray-50 border-b flex items-center justify-between">
        <?php $typeLabel = ['assets' => '🗃️ Laporan Aset', 'borrows' => '🔄 Laporan Peminjaman', 'logs' => '🕐 Log Aktivitas']; ?>
        <h2 class="text-sm font-bold text-gray-700"><?= $typeLabel[$type] ?? 'Laporan' ?></h2>
        <span class="text-xs text-gray-400"><?= count($records) ?> record</span>
    </div>

    <div class="overflow-x-auto">

        <?php if ($type === 'assets'): ?>
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50 border-b text-xs text-gray-500 uppercase tracking-wide">
                    <th class="px-4 py-3 text-left">Kode</th>
                    <th class="px-4 py-3 text-left">Nama Aset</th>
                    <th class="px-4 py-3 text-left">Kategori</th>
                    <th class="px-4 py-3 text-left">Departemen</th>
                    <th class="px-4 py-3 text-center">Status</th>
                    <th class="px-4 py-3 text-right">Harga Beli</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <?php if (empty($records)): ?>
                <tr><td colspan="6" class="text-center text-gray-400 py-10">Tidak ada data.</td></tr>
                <?php else: ?>
                <?php
                $condColor = ['baik' => 'bg-green-100 text-green-700', 'rusak_ringan' => 'bg-yellow-100 text-yellow-700', 'rusak_berat' => 'bg-red-100 text-red-700'];
                $stColor   = ['tersedia' => 'bg-green-100 text-green-700', 'dipinjam' => 'bg-yellow-100 text-yellow-700', 'dalam_perbaikan' => 'bg-orange-100 text-orange-700', 'dihapus' => 'bg-red-100 text-red-700'];
                foreach ($records as $r):
                ?>
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-2.5"><code class="text-xs font-mono text-gray-600"><?= esc($r['asset_code']) ?></code></td>
                    <td class="px-4 py-2.5 font-medium text-gray-800"><?= esc($r['name']) ?></td>
                    <td class="px-4 py-2.5 text-xs text-gray-600"><?= esc($r['category'] ?? '-') ?></td>
                    <td class="px-4 py-2.5 text-xs text-gray-600"><?= esc($r['department_name'] ?? '-') ?></td>
                    <td class="px-4 py-2.5 text-center">
                        <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium <?= $stColor[$r['status']] ?? 'bg-gray-100 text-gray-600' ?>">
                            <?= ucwords(str_replace('_',' ',$r['status'])) ?>
                        </span>
                    </td>
                    <td class="px-4 py-2.5 text-right text-xs text-gray-700">
                        <?= $r['purchase_price'] ? $rp($r['purchase_price']) : '—' ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>

        <?php elseif ($type === 'borrows'): ?>
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50 border-b text-xs text-gray-500 uppercase tracking-wide">
                    <th class="px-4 py-3 text-left">Kode</th>
                    <th class="px-4 py-3 text-left">Aset</th>
                    <th class="px-4 py-3 text-left">Peminjam</th>
                    <th class="px-4 py-3 text-left">Tgl Pinjam</th>
                    <th class="px-4 py-3 text-left">Rencana Kembali</th>
                    <th class="px-4 py-3 text-left">Kembali Aktual</th>
                    <th class="px-4 py-3 text-center">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <?php if (empty($records)): ?>
                <tr><td colspan="7" class="text-center text-gray-400 py-10">Tidak ada data.</td></tr>
                <?php else: ?>
                <?php
                $stB = ['dipinjam'=>'bg-yellow-100 text-yellow-700','dikembalikan'=>'bg-green-100 text-green-700','terlambat'=>'bg-red-100 text-red-700'];
                foreach ($records as $r): ?>
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-2.5"><code class="text-xs font-mono"><?= esc($r['borrow_code']) ?></code></td>
                    <td class="px-4 py-2.5 text-sm font-medium text-gray-800"><?= esc($r['asset_name']) ?></td>
                    <td class="px-4 py-2.5 text-sm text-gray-600"><?= esc($r['user_name']) ?></td>
                    <td class="px-4 py-2.5 text-xs text-gray-600"><?= $r['borrow_date'] ? date('d/m/Y', strtotime($r['borrow_date'])) : '-' ?></td>
                    <td class="px-4 py-2.5 text-xs text-gray-600"><?= $r['return_date_plan'] ? date('d/m/Y', strtotime($r['return_date_plan'])) : '—' ?></td>
                    <td class="px-4 py-2.5 text-xs text-gray-600"><?= $r['return_date_actual'] ? date('d/m/Y', strtotime($r['return_date_actual'])) : '—' ?></td>
                    <td class="px-4 py-2.5 text-center">
                        <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium <?= $stB[$r['status']] ?? 'bg-gray-100 text-gray-600' ?>">
                            <?= ucfirst($r['status']) ?>
                        </span>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>

        <?php else: // logs ?>
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50 border-b text-xs text-gray-500 uppercase tracking-wide">
                    <th class="px-4 py-3 text-left">Waktu</th>
                    <th class="px-4 py-3 text-left">Aset</th>
                    <th class="px-4 py-3 text-left">Aksi</th>
                    <th class="px-4 py-3 text-left">Oleh</th>
                    <th class="px-4 py-3 text-left">Keterangan</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <?php if (empty($records)): ?>
                <tr><td colspan="5" class="text-center text-gray-400 py-10">Tidak ada data.</td></tr>
                <?php else: ?>
                <?php foreach ($records as $r): ?>
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-2.5 text-xs text-gray-400 whitespace-nowrap">
                        <?= date('d/m/Y H:i', strtotime($r['created_at'])) ?>
                    </td>
                    <td class="px-4 py-2.5 text-sm">
                        <div class="font-medium text-gray-800"><?= esc($r['asset_name'] ?? '—') ?></div>
                        <code class="text-xs text-gray-400"><?= esc($r['asset_code'] ?? '') ?></code>
                    </td>
                    <td class="px-4 py-2.5">
                        <span class="inline-flex px-2 py-0.5 rounded-full bg-gray-100 text-gray-600 text-xs font-medium">
                            <?= esc($r['action']) ?>
                        </span>
                    </td>
                    <td class="px-4 py-2.5 text-xs text-gray-600"><?= esc($r['user_name'] ?? '—') ?></td>
                    <td class="px-4 py-2.5 text-xs text-gray-600 max-w-xs truncate"><?= esc($r['description'] ?? '—') ?></td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
        <?php endif; ?>

    </div>
</div>

<?= $this->endSection() ?>
