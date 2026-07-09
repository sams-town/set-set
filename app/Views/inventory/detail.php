<?= $this->extend('inventory/_layout') ?>
<?= $this->section('content') ?>

<?php
$rp = fn($n) => 'Rp ' . number_format((float)$n, 0, ',', '.');

$getStatusBadgeClass = function($status) {
    $normalList = ['Aktif', 'Standby', 'Terpasang', 'Siap Operasi', 'tersedia'];
    $perhatianList = ['Jadwal PM', 'Kalibrasi', 'Menunggu Instalasi', 'Menunggu Sparepart', 'Pengadaan'];
    $warningList = ['Rusak Ringan', 'Corrective Maintenance', 'Idle', 'Mutasi', 'dalam_perbaikan', 'diperbaiki'];
    $criticalList = ['Rusak Berat', 'Tidak Beroperasi', 'Obsolete', 'Penghapusan', 'dihapus'];

    if (in_array($status, $normalList)) return 'bg-green-100 text-green-700';
    if (in_array($status, $perhatianList)) return 'bg-yellow-100 text-yellow-800 border border-yellow-200';
    if (in_array($status, $warningList)) return 'bg-orange-100 text-orange-700';
    if (in_array($status, $criticalList)) return 'bg-red-100 text-red-700';
    return 'bg-gray-100 text-gray-700';
};

$condMap = ['baik' => 'bg-green-100 text-green-700', 'rusak_ringan' => 'bg-yellow-100 text-yellow-700', 'rusak_berat' => 'bg-red-100 text-red-700'];
$condLabel = ['baik' => 'Baik', 'rusak_ringan' => 'Rusak Ringan', 'rusak_berat' => 'Rusak Berat'];
$scLabel = ['baru' => '🆕 Baru', '2nd' => '🔄 2nd', 'bekas' => '📦 Bekas'];
?>

<!-- Breadcrumb -->
<div class="flex items-center gap-2 text-sm text-gray-500 mb-4">
    <a href="<?= base_url('admin/inventory') ?>" class="hover:text-blue-600">Inventory</a>
    <span>›</span>
    <span class="text-gray-800 font-medium"><?= esc($asset['asset_code']) ?></span>
</div>

<!-- Page header -->
<div class="flex items-start justify-between mb-5 gap-4 flex-wrap">
    <div>
        <h1 class="text-xl font-bold text-gray-800"><?= esc($asset['name']) ?></h1>
        <div class="flex items-center gap-2 mt-1 flex-wrap">
            <code class="text-sm text-gray-500 font-mono"><?= esc($asset['asset_code']) ?></code>
            <span class="px-2 py-0.5 rounded-full text-xs font-semibold <?= $getStatusBadgeClass($asset['status']) ?>">
                <?= esc($asset['status']) ?>
            </span>
            <span class="px-2 py-0.5 rounded-full text-xs font-semibold <?= $condMap[$asset['condition']] ?? 'bg-gray-100' ?>">
                <?= $condLabel[$asset['condition']] ?? $asset['condition'] ?>
            </span>
            <?php if (!empty($asset['status_condition'])): ?>
            <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-blue-100 text-blue-700">
                <?= $scLabel[$asset['status_condition']] ?? $asset['status_condition'] ?>
            </span>
            <?php endif; ?>
        </div>
    </div>
    <div class="flex items-center gap-2 flex-wrap">
        <a href="<?= base_url('admin/qr/' . $asset['id'] . '/label') ?>" target="_blank"
           class="inline-flex items-center gap-1.5 bg-gray-700 hover:bg-gray-800 text-white text-sm font-medium px-3 py-2 rounded-lg transition-colors">
            🔲 Label QR
        </a>
        <?php if (session()->get('role') === 'admin'): ?>
        <a href="<?= base_url('admin/inventory/' . $asset['id'] . '/edit') ?>"
           class="inline-flex items-center gap-1.5 bg-yellow-500 hover:bg-yellow-600 text-white text-sm font-medium px-3 py-2 rounded-lg transition-colors">
            ✏️ Edit
        </a>
        <?php endif; ?>
        <?php if (session()->get('role') === 'admin'): ?>
        <button onclick="confirmDelete('<?= base_url('admin/inventory/' . $asset['id'] . '/delete') ?>', '<?= esc($asset['name']) ?>')"
                class="inline-flex items-center gap-1.5 bg-red-600 hover:bg-red-700 text-white text-sm font-medium px-3 py-2 rounded-lg transition-colors">
            🗑 Hapus
        </button>
        <?php endif; ?>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

    <!-- ── Kolom Kiri (2/3) ───────────────────────────────────── -->
    <div class="lg:col-span-2 space-y-5">

        <!-- SECTION 1: Identitas Aset -->
        <div class="bg-white border rounded-xl p-5 shadow-sm">
            <h2 class="text-sm font-bold text-gray-700 mb-4 pb-2 border-b flex items-center gap-2">
                <span class="text-blue-600">📋</span> Identitas Aset
            </h2>
            <div class="flex flex-col sm:flex-row gap-5">
                <!-- Foto -->
                <div class="shrink-0">
                    <?php if ($asset['photo']): ?>
                        <img src="<?= base_url('uploads/assets/' . $asset['photo']) ?>"
                             alt="Foto" class="w-36 h-36 object-cover rounded-xl border shadow-sm">
                    <?php else: ?>
                        <div class="w-36 h-36 bg-gray-100 rounded-xl border flex items-center justify-center text-5xl">📦</div>
                    <?php endif; ?>
                </div>

                <!-- Info Grid -->
                <div class="flex-1">
                    <dl class="grid grid-cols-2 gap-x-6 gap-y-3 text-sm">
                        <div>
                            <dt class="text-xs text-gray-400">Kategori</dt>
                            <dd class="mt-0.5">
                                <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold <?= category_badge_class($asset['category'] ?? '') ?>">
                                    <?= esc($asset['category'] ?? '-') ?>
                                </span>
                            </dd>
                        </div>
                        <div>
                            <dt class="text-xs text-gray-400">Type / Tipe</dt>
                            <dd class="font-semibold text-gray-800"><?= esc($asset['type'] ?? '-') ?></dd>
                        </div>
                        <div>
                            <dt class="text-xs text-gray-400">Brand / Merek</dt>
                            <dd class="font-semibold text-gray-800"><?= esc($asset['brand'] ?? '-') ?></dd>
                        </div>
                        <div>
                            <dt class="text-xs text-gray-400">Model</dt>
                            <dd class="font-semibold text-gray-800"><?= esc($asset['model'] ?? '-') ?></dd>
                        </div>
                        <div>
                            <dt class="text-xs text-gray-400">Serial Number</dt>
                            <dd class="font-mono font-medium text-gray-800"><?= esc($asset['serial_number'] ?? '-') ?></dd>
                        </div>
                        <div>
                            <dt class="text-xs text-gray-400">Status Kondisi</dt>
                            <dd class="font-semibold text-gray-800">
                                <?= $scLabel[$asset['status_condition'] ?? 'baru'] ?? '-' ?>
                            </dd>
                        </div>
                        <div>
                            <dt class="text-xs text-gray-400">Quantity</dt>
                            <dd class="font-semibold text-gray-800">
                                <?= number_format($asset['quantity'] ?? 1) ?>
                                <span class="text-gray-400 font-normal"><?= esc($asset['unit'] ?? 'unit') ?></span>
                            </dd>
                        </div>
                        <div>
                            <dt class="text-xs text-gray-400">Vendor</dt>
                            <dd class="font-semibold text-gray-800"><?= esc($asset['vendor_name'] ?? '-') ?></dd>
                        </div>
                        <div>
                            <dt class="text-xs text-gray-400">Departemen</dt>
                            <dd class="font-semibold text-gray-800"><?= esc($asset['department_name'] ?? '-') ?></dd>
                        </div>
                        <div>
                            <dt class="text-xs text-gray-400">Lokasi</dt>
                            <dd class="font-semibold text-gray-800">
                                <?= esc($asset['location_name'] ?? '-') ?>
                                <?= $asset['building'] ? '<span class="text-gray-400 font-normal"> · ' . esc($asset['building']) . '</span>' : '' ?>
                            </dd>
                        </div>
                    </dl>
                    <?php if ($asset['description']): ?>
                    <div class="mt-3 pt-3 border-t">
                        <p class="text-xs text-gray-400 mb-1">Deskripsi</p>
                        <p class="text-sm text-gray-700"><?= nl2br(esc($asset['description'])) ?></p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- SECTION 2: Pembelian, Depresiasi & Nilai Buku -->
        <div class="bg-white border rounded-xl p-5 shadow-sm">
            <h2 class="text-sm font-bold text-gray-700 mb-4 pb-2 border-b flex items-center gap-2">
                <span class="text-purple-600">💰</span> Pembelian, Depresiasi & Nilai Buku
            </h2>
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 text-sm mb-4">
                <div class="bg-gray-50 rounded-lg p-3">
                    <p class="text-xs text-gray-400 mb-1">Tanggal Perolehan</p>
                    <p class="font-semibold text-gray-800">
                        <?= $asset['purchase_date'] ? date('d M Y', strtotime($asset['purchase_date'])) : '-' ?>
                    </p>
                </div>
                <div class="bg-gray-50 rounded-lg p-3">
                    <p class="text-xs text-gray-400 mb-1">Harga Perolehan</p>
                    <p class="font-semibold text-gray-800">
                        <?= $asset['purchase_price'] ? $rp($asset['purchase_price']) : '-' ?>
                    </p>
                </div>
                <div class="bg-gray-50 rounded-lg p-3">
                    <p class="text-xs text-gray-400 mb-1">Umur Aset</p>
                    <p class="font-semibold text-blue-700"><?= $age['label'] ?: '-' ?></p>
                </div>
                <div class="bg-gray-50 rounded-lg p-3">
                    <p class="text-xs text-gray-400 mb-1">Masa Pakai</p>
                    <p class="font-semibold text-gray-800">
                        <?= $asset['depreciation_years'] ? $asset['depreciation_years'] . ' Tahun' : '-' ?>
                    </p>
                </div>
                <div class="bg-purple-50 rounded-lg p-3">
                    <p class="text-xs text-gray-400 mb-1">Depresiasi / Tahun</p>
                    <p class="font-semibold text-purple-700">
                        <?= $asset['depreciation_value'] ? $rp($asset['depreciation_value']) : '-' ?>
                    </p>
                </div>
                <div class="<?= $book_value > 0 ? 'bg-green-50' : 'bg-red-50' ?> rounded-lg p-3">
                    <p class="text-xs text-gray-400 mb-1">Nilai Buku Saat Ini</p>
                    <p class="font-bold <?= $book_value > 0 ? 'text-green-700' : 'text-red-600' ?>">
                        <?= ($asset['purchase_price'] && $asset['depreciation_value']) ? $rp($book_value) : '-' ?>
                    </p>
                    <?php if ($asset['purchase_price'] && $asset['depreciation_value'] && $book_value <= 0): ?>
                    <p class="text-xs text-red-400 mt-0.5">Sudah habis terdepresiasi</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Garansi -->
            <?php
            $warrantyExpired = $asset['warranty_expiry'] && strtotime($asset['warranty_expiry']) < time();
            $daysLeft = $asset['warranty_expiry'] ? (int) ((strtotime($asset['warranty_expiry']) - time()) / 86400) : null;
            ?>
            <div class="border rounded-lg p-3 <?= $warrantyExpired ? 'bg-red-50 border-red-200' : 'bg-gray-50' ?>">
                <p class="text-xs text-gray-400 mb-1">Garansi Hingga</p>
                <?php if ($asset['warranty_expiry']): ?>
                <p class="font-semibold <?= $daysLeft < 0 ? 'text-red-600' : ($daysLeft <= 30 ? 'text-orange-600' : 'text-gray-800') ?>">
                    <?= date('d M Y', strtotime($asset['warranty_expiry'])) ?>
                    <span class="text-xs font-normal ml-1">
                        (<?= $daysLeft < 0 ? 'Expired ' . abs($daysLeft) . ' hari lalu' : ($daysLeft === 0 ? 'Hari ini!' : $daysLeft . ' hari lagi') ?>)
                    </span>
                </p>
                <?php else: ?>
                <p class="font-semibold text-gray-400">-</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- SECTION 3: Pemeliharaan Preventif -->
        <?php if ($asset['pm_interval_days']): ?>
        <div class="bg-white border rounded-xl p-5 shadow-sm">
            <h2 class="text-sm font-bold text-gray-700 mb-4 pb-2 border-b flex items-center gap-2">
                <span class="text-orange-600">🔧</span> Pemeliharaan Preventif
            </h2>
            <div class="flex items-center gap-4">
                <div class="bg-orange-50 border border-orange-200 rounded-xl p-4 text-center">
                    <div class="text-3xl font-bold text-orange-600"><?= $asset['pm_interval_days'] ?></div>
                    <div class="text-xs text-gray-500 mt-1">Hari Interval</div>
                </div>
                <div>
                    <p class="font-semibold text-gray-800">
                        <?php
                        $d = (int) $asset['pm_interval_days'];
                        if ($d >= 365)       echo 'Setiap ' . round($d/365, 1) . ' Tahun';
                        elseif ($d >= 30)    echo 'Setiap ' . round($d/30) . ' Bulan';
                        elseif ($d >= 7)     echo 'Setiap ' . round($d/7) . ' Minggu';
                        else                 echo 'Setiap ' . $d . ' Hari';
                        ?>
                    </p>
                    <p class="text-xs text-gray-400 mt-1">Interval pemeliharaan preventif rutin</p>
                    <a href="<?= base_url('admin/work-orders/new?asset_id=' . $asset['id'] . '&type=preventive') ?>"
                       class="inline-flex items-center gap-1 mt-2 text-xs text-orange-600 hover:underline font-medium">
                        + Buat Work Order PM →
                    </a>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- SECTION 3.5: Detail Kalibrasi Alat Medis -->
        <?php if (!empty($asset['requires_calibration']) && $asset['requires_calibration'] == 1): ?>
        <div class="bg-white border rounded-xl p-5 shadow-sm">
            <h2 class="text-sm font-bold text-gray-700 mb-4 pb-2 border-b flex items-center gap-2">
                <span class="text-teal-600">🔬</span> Uji Kalibrasi Alat Medis
            </h2>
            
            <?php
            $calStatus = 'Terkalibrasi';
            $calClass = 'bg-green-100 text-green-700';
            
            if (empty($asset['next_calibration_date'])) {
                $calStatus = 'Belum Terjadwal';
                $calClass = 'bg-yellow-100 text-yellow-700';
            } else {
                $nextCal = strtotime($asset['next_calibration_date']);
                $days = (int) (($nextCal - time()) / 86400);
                if ($days < 0) {
                    $calStatus = 'Kalibrasi Kadaluwarsa';
                    $calClass = 'bg-red-100 text-red-700';
                } elseif ($days <= 30) {
                    $calStatus = 'Perlu Kalibrasi Segera';
                    $calClass = 'bg-orange-100 text-orange-700';
                }
            }
            ?>
            
            <div class="flex items-center gap-3 mb-4">
                <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-semibold <?= $calClass ?>">
                    <?= $calStatus ?>
                </span>
                <?php if (!empty($asset['next_calibration_date'])): ?>
                <span class="text-xs text-gray-500">
                    Jadwal berikutnya: <?= date('d M Y', strtotime($asset['next_calibration_date'])) ?>
                    (<?= $days < 0 ? 'Expired ' . abs($days) . ' hari lalu' : $days . ' hari lagi' ?>)
                </span>
                <?php endif; ?>
            </div>
            
            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-3 text-sm">
                <div>
                    <dt class="text-xs text-gray-400">Kalibrasi Terakhir</dt>
                    <dd class="font-semibold text-gray-800">
                        <?= !empty($asset['last_calibration_date']) ? date('d M Y', strtotime($asset['last_calibration_date'])) : '-' ?>
                    </dd>
                </div>
                <div>
                    <dt class="text-xs text-gray-400">Sertifikat Kalibrasi</dt>
                    <dd class="font-semibold text-gray-800"><?= esc($asset['calibration_certificate'] ?: '-') ?></dd>
                </div>
                <div>
                    <dt class="text-xs text-gray-400">Lembaga Penguji</dt>
                    <dd class="font-semibold text-gray-800"><?= esc($asset['calibration_vendor'] ?: '-') ?></dd>
                </div>
            </dl>
        </div>
        <?php endif; ?>
    </div>

    <!-- ── Kolom Kanan (1/3) ─────────────────────────────────── -->
    <div class="space-y-5">

        <!-- QR Code -->
        <div class="bg-white border rounded-xl shadow-sm overflow-hidden">
            <div class="px-4 py-3 border-b bg-gray-50 flex items-center justify-between">
                <h2 class="text-sm font-bold text-gray-700">🔲 QR Code</h2>
                <a href="<?= base_url('admin/qr/' . $asset['id'] . '/download') ?>"
                   class="text-xs text-blue-600 hover:underline">⬇ Download</a>
            </div>
            <div class="p-4 text-center">
                <img src="<?= base_url('admin/qr/' . $asset['id']) ?>"
                     alt="QR <?= esc($asset['asset_code']) ?>"
                     class="w-40 h-40 mx-auto rounded-lg border"
                     onerror="this.parentElement.innerHTML='<p class=\'text-xs text-gray-400 py-6\'>QR tidak tersedia</p>'">
                <p class="font-mono text-xs text-gray-500 mt-2"><?= esc($asset['asset_code']) ?></p>
                <div class="flex gap-2 mt-3 justify-center">
                    <a href="<?= base_url('admin/qr/' . $asset['id'] . '/label') ?>" target="_blank"
                       class="flex-1 text-center text-xs bg-gray-800 hover:bg-gray-900 text-white py-1.5 rounded-lg transition-colors">
                        🖨️ Cetak Label
                    </a>
                    <a href="<?= base_url('admin/qr/' . $asset['id'] . '/svg') ?>" target="_blank"
                       class="flex-1 text-center text-xs border border-gray-300 hover:bg-gray-50 text-gray-700 py-1.5 rounded-lg transition-colors">
                        SVG
                    </a>
                </div>
            </div>
        </div>

        <!-- Ringkasan Angka -->
        <?php if ($asset['purchase_price'] && $asset['depreciation_value']): ?>
        <div class="bg-white border rounded-xl p-4 shadow-sm space-y-3">
            <h2 class="text-sm font-bold text-gray-700 pb-2 border-b">📊 Ringkasan Keuangan</h2>
            <?php
            $persen = $asset['purchase_price'] > 0
                ? round((1 - ($book_value / $asset['purchase_price'])) * 100, 1)
                : 0;
            $persen = max(0, min(100, $persen));
            ?>
            <div>
                <div class="flex justify-between text-xs mb-1">
                    <span class="text-gray-500">Nilai Terdepresiasi</span>
                    <span class="font-semibold"><?= $persen ?>%</span>
                </div>
                <div class="h-2 bg-gray-100 rounded-full overflow-hidden">
                    <div class="h-full rounded-full <?= $persen >= 80 ? 'bg-red-500' : ($persen >= 50 ? 'bg-orange-400' : 'bg-blue-500') ?>"
                         style="width:<?= $persen ?>%"></div>
                </div>
                <div class="flex justify-between text-xs mt-1 text-gray-400">
                    <span>Beli: <?= $rp($asset['purchase_price']) ?></span>
                    <span>Buku: <?= $rp($book_value) ?></span>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Riwayat Aktivitas -->
        <div class="bg-white border rounded-xl shadow-sm overflow-hidden">
            <div class="px-4 py-3 border-b bg-gray-50">
                <h2 class="text-sm font-bold text-gray-700">🕐 Riwayat Aktivitas</h2>
            </div>
            <div class="divide-y overflow-y-auto" style="max-height:420px;">
                <?php if (empty($logs)): ?>
                <div class="text-center text-gray-400 py-8 text-sm">Belum ada riwayat.</div>
                <?php else: ?>
                <?php foreach ($logs as $log): ?>
                <div class="px-4 py-3">
                    <div class="flex items-start justify-between gap-2">
                        <span class="px-2 py-0.5 rounded-full bg-gray-100 text-gray-600 text-xs font-medium">
                            <?= esc($log['action']) ?>
                        </span>
                        <span class="text-gray-400 text-xs shrink-0">
                            <?= date('d/m H:i', strtotime($log['created_at'])) ?>
                        </span>
                    </div>
                    <?php if ($log['description']): ?>
                    <p class="text-xs text-gray-600 mt-1"><?= esc($log['description']) ?></p>
                    <?php endif; ?>
                    <?php if (!empty($log['user_name'])): ?>
                    <p class="text-xs text-gray-400 mt-0.5">oleh <?= esc($log['user_name']) ?></p>
                    <?php endif; ?>
                    <?php if (!empty($log['cost'])): ?>
                    <p class="text-xs text-orange-600 mt-0.5 font-medium"><?= $rp($log['cost']) ?></p>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Delete Modal -->
<div x-data="deleteModal()" x-show="open" x-cloak
     class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-sm p-6">
        <div class="text-center mb-4">
            <div class="text-4xl mb-2">⚠️</div>
            <h3 class="font-bold text-gray-800">Hapus Aset</h3>
            <p class="text-sm text-gray-500 mt-1">Hapus <strong x-text="itemName" class="text-gray-800"></strong>?</p>
        </div>
        <div class="flex gap-3">
            <button @click="open = false"
                    class="flex-1 border border-gray-300 text-gray-700 py-2 rounded-lg hover:bg-gray-50 text-sm font-medium">Batal</button>
            <form :action="actionUrl" method="POST" class="flex-1">
                <?= csrf_field() ?>
                <button type="submit" class="w-full bg-red-600 hover:bg-red-700 text-white py-2 rounded-lg text-sm font-medium">Ya, Hapus</button>
            </form>
        </div>
    </div>
</div>

<script>
function deleteModal() { return { open: false, actionUrl: '', itemName: '' }; }
function confirmDelete(url, name) {
    const m = Alpine.$data(document.querySelector('[x-data="deleteModal()"]'));
    m.actionUrl = url; m.itemName = name; m.open = true;
}
</script>

<?= $this->endSection() ?>
