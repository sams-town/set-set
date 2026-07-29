<?= $this->extend('inventory/_layout') ?>
<?= $this->section('content') ?>

<?php
$stBadge = [
    'open'         => 'bg-red-100 text-red-700',
    'in_progress'  => 'bg-blue-100 text-blue-700',
    'waiting_part' => 'bg-yellow-100 text-yellow-700',
    'done'         => 'bg-green-100 text-green-700',
    'cancelled'    => 'bg-gray-100 text-gray-500',
];
$priBadge = [
    'kritis' => 'bg-red-600 text-white',
    'tinggi' => 'bg-orange-500 text-white',
    'sedang' => 'bg-yellow-400 text-gray-900',
    'rendah' => 'bg-green-400 text-gray-900',
];
?>

<!-- Greeting -->
<div class="mb-6">
    <h1 class="text-xl font-bold text-gray-800">
        👋 Halo, <?= esc(session()->get('user_name')) ?>!
    </h1>
    <p class="text-sm text-gray-500 mt-0.5">
        <?= session()->get('department_name') ? '🏢 ' . esc(session()->get('department_name')) . ' · ' : '' ?>
        <?= date('l, d F Y') ?>
    </p>
</div>

<!-- Stat Cards -->
<div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">

    <div class="bg-white border rounded-xl p-4 shadow-sm text-center">
        <div class="text-3xl font-bold text-blue-600"><?= $myWoTotal ?></div>
        <div class="text-xs text-gray-500 mt-1">Total WO Saya</div>
    </div>

    <div class="bg-white border-l-4 border-red-500 rounded-xl p-4 shadow-sm text-center">
        <div class="text-3xl font-bold text-red-600"><?= $myWoOpen ?></div>
        <div class="text-xs text-gray-500 mt-1">WO Aktif</div>
    </div>

    <div class="bg-white border-l-4 border-green-500 rounded-xl p-4 shadow-sm text-center">
        <div class="text-3xl font-bold text-green-600"><?= $myWoDone ?></div>
        <div class="text-xs text-gray-500 mt-1">WO Selesai</div>
    </div>

    <?php if ($myAssets > 0): ?>
    <div class="bg-white border-l-4 border-purple-500 rounded-xl p-4 shadow-sm text-center">
        <div class="text-3xl font-bold text-purple-600"><?= $myAssets ?></div>
        <div class="text-xs text-gray-500 mt-1">Aset Departemen</div>
    </div>
    <?php endif; ?>

</div>

<!-- Aksi Cepat -->
<div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mb-6">
    <a href="<?= base_url('admin/work-orders/new') ?>"
       class="flex items-center gap-3 bg-blue-600 hover:bg-blue-700 text-white px-5 py-4 rounded-xl font-medium text-sm transition-colors shadow-sm">
        <span class="text-2xl">📋</span>
        <div>
            <div class="font-semibold">Buat Work Order</div>
            <div class="text-xs text-blue-200">Laporkan kerusakan / permintaan</div>
        </div>
    </a>
    <a href="<?= base_url('admin/inventory/new') ?>"
       class="flex items-center gap-3 bg-teal-600 hover:bg-teal-700 text-white px-5 py-4 rounded-xl font-medium text-sm transition-colors shadow-sm">
        <span class="text-2xl">🗃️</span>
        <div>
            <div class="font-semibold">Input Aset Baru</div>
            <div class="text-xs text-teal-200">Tambah data aset ke inventori</div>
        </div>
    </a>
    <a href="<?= base_url('admin/borrows/new') ?>"
       class="flex items-center gap-3 bg-orange-500 hover:bg-orange-600 text-white px-5 py-4 rounded-xl font-medium text-sm transition-colors shadow-sm">
        <span class="text-2xl">🔄</span>
        <div>
            <div class="font-semibold">Catat Peminjaman</div>
            <div class="text-xs text-orange-100">Pinjam / kembalikan aset</div>
        </div>
    </a>
</div>

<!-- WO Terbaru Saya -->
<div class="bg-white border rounded-xl shadow-sm overflow-hidden">
    <div class="px-4 py-3 border-b bg-gray-50 flex items-center justify-between">
        <h2 class="text-sm font-bold text-gray-700">📋 Work Order Saya Terbaru</h2>
        <a href="<?= base_url('admin/work-orders') ?>"
           class="text-xs text-blue-600 hover:underline">Lihat Semua →</a>
    </div>

    <?php if (empty($myWoRecent)): ?>
    <div class="text-center py-10 text-gray-400">
        <div class="text-4xl mb-2">📋</div>
        <p class="text-sm">Belum ada Work Order</p>
        <a href="<?= base_url('admin/work-orders/new') ?>"
           class="inline-block mt-2 text-sm text-blue-600 hover:underline">+ Buat WO Pertama</a>
    </div>
    <?php else: ?>
    <div class="divide-y">
        <?php foreach ($myWoRecent as $wo): ?>
        <a href="<?= base_url('admin/work-orders/' . $wo['id']) ?>"
           class="flex items-center justify-between px-4 py-3 hover:bg-gray-50 transition-colors">
            <div class="min-w-0">
                <div class="flex items-center gap-2 flex-wrap">
                    <code class="font-mono text-xs text-blue-600 font-semibold"><?= esc($wo['wo_code']) ?></code>
                    <span class="px-2 py-0.5 rounded-full text-xs font-semibold <?= $stBadge[$wo['status']] ?? 'bg-gray-100' ?>">
                        <?= ucwords(str_replace('_', ' ', $wo['status'])) ?>
                    </span>
                    <span class="px-2 py-0.5 rounded-full text-xs font-semibold <?= $priBadge[$wo['priority']] ?? 'bg-gray-100' ?>">
                        <?= ucfirst($wo['priority']) ?>
                    </span>
                </div>
                <p class="text-xs text-gray-600 mt-0.5 truncate"><?= esc($wo['asset_name'] ?? '-') ?></p>
                <p class="text-xs text-gray-400 truncate"><?= esc(substr($wo['problem_desc'] ?? '', 0, 60)) ?></p>
            </div>
            <div class="text-xs text-gray-400 shrink-0 ml-4 text-right">
                <?= date('d M Y', strtotime($wo['created_at'])) ?>
            </div>
        </a>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>

<?= $this->endSection() ?>
