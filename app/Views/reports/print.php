<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Cetak Laporan — SiAset</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <style>
        body { font-size: 12px; }
        @media print {
            .no-print { display: none !important; }
            body { font-size: 11px; }
        }
    </style>
</head>
<body class="p-4">

<div class="d-flex justify-content-between align-items-start mb-3">
    <div>
        <h5 class="fw-bold mb-0">SiAset — Sistem Manajemen Aset</h5>
        <div class="text-muted">
            <?php $typeLabel = ['assets' => 'Laporan Data Aset', 'borrows' => 'Laporan Peminjaman', 'logs' => 'Log Aktivitas']; ?>
            <?= $typeLabel[$type] ?? 'Laporan' ?>
        </div>
        <div class="small text-muted">Dicetak: <?= $print_date ?></div>
    </div>
    <button class="btn btn-sm btn-primary no-print" onclick="window.print()">
        <i class="bi bi-printer"></i> Cetak
    </button>
</div>

<hr>

<?php if ($type === 'assets'): ?>
<table class="table table-bordered table-sm">
    <thead class="table-dark">
        <tr><th>#</th><th>Kode Aset</th><th>Nama Aset</th><th>Kategori</th><th>Lokasi</th><th>Kondisi</th><th>Status</th><th>Harga Beli</th></tr>
    </thead>
    <tbody>
        <?php foreach ($records as $i => $r): ?>
        <tr>
            <td><?= $i + 1 ?></td>
            <td><?= esc($r['asset_code']) ?></td>
            <td><?= esc($r['name']) ?></td>
            <td><?= esc($r['category']) ?></td>
            <td><?= esc($r['location_name'] ?? '-') ?></td>
            <td><?= esc($r['condition']) ?></td>
            <td><?= esc($r['status']) ?></td>
            <td><?= $r['purchase_price'] ? 'Rp ' . number_format($r['purchase_price'], 0, ',', '.') : '-' ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
    <tfoot><tr><td colspan="8" class="text-end fw-semibold">Total: <?= count($records) ?> aset</td></tr></tfoot>
</table>

<?php elseif ($type === 'borrows'): ?>
<table class="table table-bordered table-sm">
    <thead class="table-dark">
        <tr><th>#</th><th>Kode</th><th>Aset</th><th>Peminjam</th><th>Tgl Pinjam</th><th>Rencana Kembali</th><th>Kembali Aktual</th><th>Status</th></tr>
    </thead>
    <tbody>
        <?php foreach ($records as $i => $r): ?>
        <tr>
            <td><?= $i + 1 ?></td>
            <td><?= esc($r['borrow_code']) ?></td>
            <td><?= esc($r['asset_name']) ?> (<?= esc($r['asset_code']) ?>)</td>
            <td><?= esc($r['user_name']) ?></td>
            <td><?= esc($r['borrow_date']) ?></td>
            <td><?= esc($r['return_date_plan'] ?? '-') ?></td>
            <td><?= esc($r['return_date_actual'] ?? '-') ?></td>
            <td><?= esc($r['status']) ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
    <tfoot><tr><td colspan="8" class="text-end fw-semibold">Total: <?= count($records) ?> transaksi</td></tr></tfoot>
</table>

<?php else: ?>
<table class="table table-bordered table-sm">
    <thead class="table-dark">
        <tr><th>#</th><th>Waktu</th><th>Aset</th><th>Aksi</th><th>Oleh</th><th>Keterangan</th></tr>
    </thead>
    <tbody>
        <?php foreach ($records as $i => $r): ?>
        <tr>
            <td><?= $i + 1 ?></td>
            <td><?= esc($r['created_at']) ?></td>
            <td><?= esc($r['asset_name'] ?? '-') ?></td>
            <td><?= esc($r['action']) ?></td>
            <td><?= esc($r['user_name'] ?? '-') ?></td>
            <td><?= esc($r['description'] ?? '-') ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php endif; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</body>
</html>
