<?= $this->extend('inventory/_layout') ?>
<?= $this->section('content') ?>

<!-- ── Page Header ────────────────────────────────────────────── -->
<div class="flex items-center justify-between mb-5">
    <div>
        <h1 class="text-xl font-bold text-gray-800">🗃️ Inventory Aset</h1>
        <p class="text-sm text-gray-500 mt-0.5">Single source of truth — seluruh aset perusahaan</p>
    </div>
    <a href="<?= base_url('admin/inventory/new') ?>"
       class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors shadow-sm">
        + Tambah Aset
    </a>
</div>

<!-- ── Stats Cards ────────────────────────────────────────────── -->
<div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-7 gap-3 mb-5">
    <?php foreach ([
        ['total',            'Total',          'bg-blue-50 text-blue-700',    'border-blue-200'],
        ['normal',           'Normal 🟢',      'bg-green-50 text-green-700',  'border-green-200'],
        ['perhatian',        'Perhatian 🟡',   'bg-yellow-50 text-yellow-800','border-yellow-200'],
        ['warning',          'Warning 🟠',     'bg-orange-50 text-orange-700','border-orange-200'],
        ['critical',         'Critical 🔴',    'bg-red-50 text-red-700',      'border-red-200'],
        ['calibration_soon', 'Kalibrasi ~30h', 'bg-teal-50 text-teal-700',    'border-teal-200'],
        ['warranty_soon',    'Garansi ~30h',    'bg-purple-50 text-purple-700','border-purple-200'],
    ] as [$key, $label, $color, $border]): ?>
    <div class="<?= $color ?> border <?= $border ?> rounded-xl px-3 py-3 text-center shadow-xs">
        <div class="text-2xl font-bold"><?= $stats[$key] ?? 0 ?></div>
        <div class="text-xs font-medium mt-0.5"><?= $label ?></div>
    </div>
    <?php endforeach; ?>
</div>

<!-- ── Filter Bar ─────────────────────────────────────────────── -->
<div class="bg-white border rounded-xl px-4 py-3 mb-4 shadow-sm">
    <form method="GET" class="flex flex-wrap gap-2 items-center">
        <input type="text" name="search"
               value="<?= esc($filters['search'] ?? '') ?>"
               placeholder="Cari nama / kode / brand / type..."
               class="flex-1 min-w-[180px] border border-gray-300 rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">

        <select name="status" class="min-w-[150px] border border-gray-300 rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
            <option value="">Semua Status</option>
            <optgroup label="🟢 Normal">
                <?php foreach (['Aktif', 'Standby', 'Terpasang', 'Siap Operasi'] as $s): ?>
                    <option value="<?= $s ?>" <?= ($filters['status'] ?? '') === $s ? 'selected' : '' ?>><?= $s ?></option>
                <?php endforeach; ?>
            </optgroup>
            <optgroup label="🟡 Perhatian">
                <?php foreach (['Jadwal PM', 'Kalibrasi', 'Menunggu Instalasi', 'Menunggu Sparepart', 'Pengadaan'] as $s): ?>
                    <option value="<?= $s ?>" <?= ($filters['status'] ?? '') === $s ? 'selected' : '' ?>><?= $s ?></option>
                <?php endforeach; ?>
            </optgroup>
            <optgroup label="🟠 Warning">
                <?php foreach (['Rusak Ringan', 'Corrective Maintenance', 'Idle', 'Mutasi'] as $s): ?>
                    <option value="<?= $s ?>" <?= ($filters['status'] ?? '') === $s ? 'selected' : '' ?>><?= $s ?></option>
                <?php endforeach; ?>
            </optgroup>
            <optgroup label="🔴 Critical">
                <?php foreach (['Rusak Berat', 'Tidak Beroperasi', 'Obsolete', 'Penghapusan'] as $s): ?>
                    <option value="<?= $s ?>" <?= ($filters['status'] ?? '') === $s ? 'selected' : '' ?>><?= $s ?></option>
                <?php endforeach; ?>
            </optgroup>
        </select>

        <select name="condition" class="min-w-[130px] border border-gray-300 rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
            <option value="">Semua Kondisi</option>
            <?php foreach (['baik' => 'Baik', 'rusak_ringan' => 'Rusak Ringan', 'rusak_berat' => 'Rusak Berat'] as $v => $l): ?>
                <option value="<?= $v ?>" <?= ($filters['condition'] ?? '') === $v ? 'selected' : '' ?>><?= $l ?></option>
            <?php endforeach; ?>
        </select>

        <select name="status_condition" class="min-w-[110px] border border-gray-300 rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
            <option value="">Baru/2nd</option>
            <?php foreach (['baru' => 'Baru', '2nd' => '2nd', 'bekas' => 'Bekas'] as $v => $l): ?>
                <option value="<?= $v ?>" <?= ($filters['status_condition'] ?? '') === $v ? 'selected' : '' ?>><?= $l ?></option>
            <?php endforeach; ?>
        </select>

        <select name="category" class="min-w-[160px] border border-gray-300 rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
            <option value="">Semua Kategori</option>
            <?php foreach ($categories as $cat): ?>
                <option value="<?= esc($cat) ?>" <?= ($filters['category'] ?? '') === $cat ? 'selected' : '' ?>><?= esc($cat) ?></option>
            <?php endforeach; ?>
        </select>

        <select name="department_id" class="min-w-[150px] border border-gray-300 rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
            <option value="">Semua Dept.</option>
            <?php foreach ($departments as $id => $name): ?>
                <option value="<?= $id ?>" <?= ($filters['department_id'] ?? '') == $id ? 'selected' : '' ?>><?= esc($name) ?></option>
            <?php endforeach; ?>
        </select>

        <div class="flex gap-1.5">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white text-sm px-4 py-1.5 rounded-lg transition-colors">Filter</button>
            <a href="<?= base_url('admin/inventory') ?>" class="bg-gray-100 hover:bg-gray-200 text-gray-600 text-sm px-3 py-1.5 rounded-lg transition-colors">Reset</a>
        </div>

        <label class="flex items-center gap-2 text-sm text-gray-600 cursor-pointer">
            <input type="checkbox" name="warranty_expiring" value="1"
                   <?= !empty($filters['warranty_expiring']) ? 'checked' : '' ?>
                   class="rounded text-blue-600">
            Garansi akan habis
        </label>
    </form>
</div>

<!-- ── Table ──────────────────────────────────────────────────── -->
<div class="bg-white border rounded-xl shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50 border-b text-gray-600 text-left text-xs uppercase tracking-wide">
                    <th class="px-3 py-3 w-8">#</th>
                    <th class="px-3 py-3">Foto</th>
                    <th class="px-3 py-3">Kode / Nama Aset</th>
                    <th class="px-3 py-3">Kategori / Type</th>
                    <th class="px-3 py-3">Dept / Lokasi</th>
                    <th class="px-3 py-3 text-center">Qty</th>
                    <th class="px-3 py-3">Harga / Depresiasi</th>
                    <th class="px-3 py-3">Umur Aset</th>
                    <th class="px-3 py-3">Kondisi</th>
                    <th class="px-3 py-3">Status</th>
                    <th class="px-3 py-3">Garansi</th>
                    <th class="px-3 py-3 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <?php if (empty($assets)): ?>
                <tr>
                    <td colspan="12" class="text-center text-gray-400 py-12">
                        <div class="text-3xl mb-2">📭</div>
                        Tidak ada data aset.
                    </td>
                </tr>
                <?php else:
                $rowNum = ($page - 1) * $per_page + 1;
                foreach ($assets as $a):
                    $condMap   = ['baik' => 'bg-green-100 text-green-700', 'rusak_ringan' => 'bg-yellow-100 text-yellow-700', 'rusak_berat' => 'bg-red-100 text-red-700'];
                    $condLabel = ['baik' => 'Baik', 'rusak_ringan' => 'Rusak Ringan', 'rusak_berat' => 'Rusak Berat'];
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
                    $scMap     = ['baru' => 'bg-blue-100 text-blue-700', '2nd' => 'bg-amber-100 text-amber-700', 'bekas' => 'bg-gray-100 text-gray-600'];

                    // Garansi
                    $wClass = 'text-gray-400'; $wLabel = '-';
                    if ($a['warranty_expiry']) {
                        $d = (int) ((strtotime($a['warranty_expiry']) - time()) / 86400);
                        if ($d < 0)       { $wClass = 'text-red-500 font-semibold'; $wLabel = 'Expired'; }
                        elseif ($d <= 30) { $wClass = 'text-orange-500 font-semibold'; $wLabel = $d . 'h lagi'; }
                        else              { $wLabel = date('d/m/Y', strtotime($a['warranty_expiry'])); }
                    }

                    // Umur aset
                    $age = \App\Models\InventoryAssetModel::calcAge($a['purchase_date'] ?? null);
                ?>
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="px-3 py-2 text-gray-400 text-xs"><?= $rowNum++ ?></td>

                    <!-- Foto Thumbnail -->
                    <td class="px-3 py-2">
                        <?php if ($a['photo']): ?>
                            <img src="<?= base_url('uploads/assets/' . $a['photo']) ?>"
                                 alt="foto"
                                 class="w-10 h-10 object-cover rounded-lg border shadow-sm">
                        <?php else: ?>
                            <div class="w-10 h-10 bg-gray-100 rounded-lg border flex items-center justify-center text-lg">📦</div>
                        <?php endif; ?>
                    </td>

                    <!-- Kode / Nama -->
                    <td class="px-3 py-2 min-w-[160px]">
                        <a href="<?= base_url('admin/inventory/' . $a['id']) ?>"
                           class="font-semibold text-gray-800 hover:text-blue-600 transition-colors block">
                            <?= esc($a['name']) ?>
                        </a>
                        <code class="text-xs bg-gray-100 px-1.5 py-0.5 rounded font-mono text-gray-500">
                            <?= esc($a['asset_code']) ?>
                        </code>
                        <?php if (!empty($a['status_condition'])): ?>
                        <span class="ml-1 px-1.5 py-0.5 rounded text-xs font-medium <?= $scMap[$a['status_condition']] ?? '' ?>">
                            <?= strtoupper($a['status_condition']) ?>
                        </span>
                        <?php endif; ?>
                    </td>

                    <!-- Kategori / Type -->
                    <td class="px-3 py-2 text-xs">
                        <div class="text-gray-700 font-medium"><?= esc($a['category']) ?></div>
                        <?php if (!empty($a['type'])): ?>
                        <div class="text-gray-400"><?= esc($a['type']) ?></div>
                        <?php endif; ?>
                        <?php if ($a['brand']): ?>
                        <div class="text-gray-400"><?= esc($a['brand']) ?><?= $a['model'] ? ' · ' . esc($a['model']) : '' ?></div>
                        <?php endif; ?>
                    </td>

                    <!-- Dept / Lokasi -->
                    <td class="px-3 py-2 text-xs">
                        <div class="font-medium text-gray-700"><?= esc($a['department_name'] ?? '-') ?></div>
                        <div class="text-gray-400"><?= esc($a['location_name'] ?? '') ?><?= $a['building'] ? ' · ' . esc($a['building']) : '' ?></div>
                    </td>

                    <!-- Qty -->
                    <td class="px-3 py-2 text-center text-sm font-semibold text-gray-700">
                        <?= number_format($a['quantity'] ?? 1) ?>
                        <div class="text-xs text-gray-400 font-normal"><?= esc($a['unit'] ?? 'unit') ?></div>
                    </td>

                    <!-- Harga / Depresiasi -->
                    <td class="px-3 py-2 text-xs">
                        <div class="font-medium text-gray-800">
                            <?= $a['purchase_price'] ? 'Rp ' . number_format($a['purchase_price'], 0, ',', '.') : '-' ?>
                        </div>
                        <?php if (!empty($a['depreciation_value'])): ?>
                        <div class="text-gray-400">
                            Dep/thn: <?= 'Rp ' . number_format($a['depreciation_value'], 0, ',', '.') ?>
                        </div>
                        <?php endif; ?>
                        <?php if (!empty($a['depreciation_years'])): ?>
                        <div class="text-gray-400"><?= $a['depreciation_years'] ?> thn masa pakai</div>
                        <?php endif; ?>
                    </td>

                    <!-- Umur Aset -->
                    <td class="px-3 py-2 text-xs text-gray-600">
                        <?= $age['label'] ?: '-' ?>
                        <?php if ($a['pm_interval_days']): ?>
                        <div class="text-gray-400">PM: <?= $a['pm_interval_days'] ?> hari</div>
                        <?php endif; ?>
                        <?php if ($a['requires_calibration']): ?>
                        <div class="mt-0.5 font-medium text-teal-600">
                            🔬
                            <?php if ($a['next_calibration_date']):
                                $nextCal = strtotime($a['next_calibration_date']);
                                $cDays = (int) (($nextCal - time()) / 86400);
                                if ($cDays < 0) echo '<span class="text-red-500 font-bold">Kalibrasi Expired</span>';
                                else echo 'Kal: ' . date('d/m/y', $nextCal);
                            else:
                                echo 'Belum Kalibrasi';
                            endif; ?>
                        </div>
                        <?php endif; ?>
                    </td>

                    <!-- Kondisi -->
                    <td class="px-3 py-2">
                        <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium <?= $condMap[$a['condition']] ?? 'bg-gray-100 text-gray-600' ?>">
                            <?= $condLabel[$a['condition']] ?? $a['condition'] ?>
                        </span>
                    </td>

                    <!-- Status -->
                    <td class="px-3 py-2">
                        <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium <?= $getStatusBadgeClass($a['status']) ?>">
                            <?= esc($a['status']) ?>
                        </span>
                    </td>

                    <!-- Garansi -->
                    <td class="px-3 py-2 text-xs <?= $wClass ?>"><?= $wLabel ?></td>

                    <!-- Aksi -->
                    <td class="px-3 py-2">
                        <div class="flex items-center justify-center gap-1">
                            <a href="<?= base_url('admin/inventory/' . $a['id']) ?>"
                               title="Detail" class="p-1.5 rounded-lg bg-blue-50 hover:bg-blue-100 text-blue-600 text-xs">👁</a>
                            <a href="<?= base_url('admin/inventory/' . $a['id'] . '/edit') ?>"
                               title="Edit" class="p-1.5 rounded-lg bg-yellow-50 hover:bg-yellow-100 text-yellow-600 text-xs">✏️</a>
                            <?php if (session()->get('role') === 'admin'): ?>
                            <button type="button"
                                    onclick="confirmDelete('<?= base_url('admin/inventory/' . $a['id'] . '/delete') ?>', '<?= esc($a['name']) ?>')"
                                    class="p-1.5 rounded-lg bg-red-50 hover:bg-red-100 text-red-600 text-xs">🗑</button>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="flex items-center justify-between px-4 py-3 border-t bg-gray-50 text-sm text-gray-500">
        <span>Menampilkan <?= count($assets) ?> dari <strong><?= $total_records ?></strong> aset</span>
        <?php if ($total_pages > 1):
            $qsParams = array_filter($filters, fn($v) => $v !== null && $v !== '');
            $qs = $qsParams ? '&' . http_build_query($qsParams) : '';
        ?>
        <div class="flex items-center gap-1">
            <?php if ($page > 1): ?>
                <a href="?page=<?= $page - 1 . $qs ?>" class="px-3 py-1 rounded-lg border hover:bg-white">‹</a>
            <?php endif; ?>
            <?php for ($p = max(1, $page - 2); $p <= min($total_pages, $page + 2); $p++): ?>
                <a href="?page=<?= $p . $qs ?>"
                   class="px-3 py-1 rounded-lg border transition-colors <?= $p === $page ? 'bg-blue-600 text-white border-blue-600' : 'hover:bg-white' ?>">
                    <?= $p ?>
                </a>
            <?php endfor; ?>
            <?php if ($page < $total_pages): ?>
                <a href="?page=<?= $page + 1 . $qs ?>" class="px-3 py-1 rounded-lg border hover:bg-white">›</a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Delete Modal -->
<div x-data="deleteModal()" x-show="open" x-cloak
     class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-sm p-6">
        <div class="text-center mb-4">
            <div class="text-4xl mb-2">⚠️</div>
            <h3 class="font-bold text-gray-800">Hapus Aset</h3>
            <p class="text-sm text-gray-500 mt-1">
                Hapus <strong x-text="itemName" class="text-gray-800"></strong>? Tindakan ini tidak dapat dibatalkan.
            </p>
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
