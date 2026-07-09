<?= $this->extend('inventory/_layout') ?>
<?= $this->section('content') ?>

<?php $isEdit = !empty($asset); ?>

<div class="flex items-center gap-2 text-sm text-gray-500 mb-4">
    <a href="<?= base_url('admin/inventory') ?>" class="hover:text-blue-600">Inventory</a>
    <span>›</span>
    <?php if ($isEdit): ?>
        <a href="<?= base_url('admin/inventory/' . $asset['id']) ?>" class="hover:text-blue-600">
            <?= esc($asset['asset_code']) ?>
        </a>
        <span>›</span>
        <span class="text-gray-800 font-medium">Edit</span>
    <?php else: ?>
        <span class="text-gray-800 font-medium">Tambah Aset</span>
    <?php endif; ?>
</div>

<div class="max-w-5xl">
    <h1 class="text-xl font-bold text-gray-800 mb-5">
        <?= $isEdit ? '✏️ Edit Aset' : '➕ Tambah Aset Baru' ?>
    </h1>

    <form action="<?= $isEdit
            ? base_url('admin/inventory/' . $asset['id'] . '/update')
            : base_url('admin/inventory') ?>"
          method="POST" enctype="multipart/form-data"
          class="space-y-5"
          id="assetForm">
        <?= csrf_field() ?>

        <!-- ══════════════════════════════════════════════════════════
             SECTION 1 — INFORMASI DASAR
             ══════════════════════════════════════════════════════════ -->
        <div class="bg-white border rounded-xl p-5 shadow-sm">
            <h2 class="text-sm font-bold text-gray-700 uppercase tracking-wide mb-4 pb-2 border-b flex items-center gap-2">
                <span class="text-blue-600">📋</span> Informasi Dasar
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <!-- Template Aset (Hanya muncul jika ada template) -->
                <?php if (!empty($templates)): ?>
                <div class="md:col-span-3">
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Pilih dari Template Aset <span class="text-xs text-gray-400">(Mengisi otomatis nama, kategori, brand, model)</span>
                    </label>
                    <select id="templateSelect" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none">
                        <option value="">-- Kustom / Tanpa Template --</option>
                        <?php foreach ($templates as $t): ?>
                            <option value="<?= $t['id'] ?>"
                                    data-name="<?= esc($t['name']) ?>"
                                    data-category="<?= esc($t['category']) ?>"
                                    data-brand="<?= esc($t['brand'] ?? '') ?>"
                                    data-model="<?= esc($t['model'] ?? '') ?>">
                                <?= esc($t['name']) ?> (<?= esc($t['category']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php endif; ?>

                <!-- Kode Aset -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Kode Aset</label>
                    <input type="text" name="asset_code"
                           value="<?= old('asset_code', $asset['asset_code'] ?? '') ?>"
                           placeholder="Otomatis jika kosong"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm font-mono focus:ring-2 focus:ring-blue-400 focus:outline-none">
                    <p class="text-xs text-gray-400 mt-1">Kosongkan untuk auto-generate</p>
                </div>

                <!-- Nama Aset -->
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Nama Aset <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="name" required
                           value="<?= old('name', $asset['name'] ?? '') ?>"
                           placeholder="Contoh: Laptop Dell Latitude 5420"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none">
                </div>

                <!-- Kategori -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Kategori <span class="text-red-500">*</span>
                    </label>
                    <select name="category" required
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none">
                        <option value="">-- Pilih --</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= esc($cat) ?>"
                                <?= old('category', $asset['category'] ?? '') === $cat ? 'selected' : '' ?>>
                                <?= esc($cat) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Type / Tipe -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Type / Tipe</label>
                    <input type="text" name="type"
                           value="<?= old('type', $asset['type'] ?? '') ?>"
                           placeholder="Contoh: Workstation, Server"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none">
                </div>

                <!-- Brand -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Brand / Merek</label>
                    <input type="text" name="brand"
                           value="<?= old('brand', $asset['brand'] ?? '') ?>"
                           placeholder="Contoh: Dell, HP"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none">
                </div>

                <!-- Model -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Model</label>
                    <input type="text" name="model"
                           value="<?= old('model', $asset['model'] ?? '') ?>"
                           placeholder="Contoh: Latitude 5420"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none">
                </div>

                <!-- Serial Number -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Serial Number</label>
                    <input type="text" name="serial_number"
                           value="<?= old('serial_number', $asset['serial_number'] ?? '') ?>"
                           placeholder="SN/Serial"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm font-mono focus:ring-2 focus:ring-blue-400 focus:outline-none">
                </div>

                <!-- Kondisi (hidden default to baik) -->
                <input type="hidden" name="condition" value="<?= esc(old('condition', $asset['condition'] ?? 'baik')) ?>">

                <!-- Status -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Status <span class="text-red-500">*</span>
                    </label>
                    <select name="status" required
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none">
                        <optgroup label="🟢 Normal">
                            <?php foreach (['Aktif', 'Standby', 'Terpasang', 'Siap Operasi'] as $s): ?>
                                <option value="<?= $s ?>" <?= old('status', $asset['status'] ?? 'Standby') === $s ? 'selected' : '' ?>><?= $s ?></option>
                            <?php endforeach; ?>
                        </optgroup>
                        <optgroup label="🟡 Perhatian">
                            <?php foreach (['Jadwal PM', 'Kalibrasi', 'Menunggu Instalasi', 'Menunggu Sparepart', 'Pengadaan'] as $s): ?>
                                <option value="<?= $s ?>" <?= old('status', $asset['status'] ?? 'Standby') === $s ? 'selected' : '' ?>><?= $s ?></option>
                            <?php endforeach; ?>
                        </optgroup>
                        <optgroup label="🟠 Warning">
                            <?php foreach (['Rusak Ringan', 'Corrective Maintenance', 'Idle', 'Mutasi'] as $s): ?>
                                <option value="<?= $s ?>" <?= old('status', $asset['status'] ?? 'Standby') === $s ? 'selected' : '' ?>><?= $s ?></option>
                            <?php endforeach; ?>
                        </optgroup>
                        <optgroup label="🔴 Critical">
                            <?php foreach (['Rusak Berat', 'Tidak Beroperasi', 'Obsolete', 'Penghapusan'] as $s): ?>
                                <option value="<?= $s ?>" <?= old('status', $asset['status'] ?? 'Standby') === $s ? 'selected' : '' ?>><?= $s ?></option>
                            <?php endforeach; ?>
                        </optgroup>
                    </select>
                </div>

                <!-- Deskripsi -->
                <div class="md:col-span-3">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Deskripsi / Spesifikasi</label>
                    <textarea name="description" rows="2"
                              placeholder="Catatan tambahan, spesifikasi teknis..."
                              class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none resize-none"><?= old('description', $asset['description'] ?? '') ?></textarea>
                </div>
            </div>
        </div>

        <!-- ══════════════════════════════════════════════════════════
             SECTION 2 — PENEMPATAN & QUANTITY
             ══════════════════════════════════════════════════════════ -->
        <div class="bg-white border rounded-xl p-5 shadow-sm">
            <h2 class="text-sm font-bold text-gray-700 uppercase tracking-wide mb-4 pb-2 border-b flex items-center gap-2">
                <span class="text-green-600">📍</span> Penempatan & Kuantitas
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

                <!-- Departemen -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Departemen</label>
                    <select name="department_id"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none">
                        <option value="">-- Pilih --</option>
                        <?php foreach ($departments as $id => $name): ?>
                            <option value="<?= $id ?>"
                                <?= old('department_id', $asset['department_id'] ?? '') == $id ? 'selected' : '' ?>>
                                <?= esc($name) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Lokasi -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Lokasi / Ruangan</label>
                    <select name="location_id"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none">
                        <option value="">-- Pilih --</option>
                        <?php foreach ($locations_with_dept as $loc): ?>
                            <option value="<?= $loc['id'] ?>"
                                    data-department-id="<?= esc($loc['department_id'] ?? '') ?>"
                                    <?= old('location_id', $asset['location_id'] ?? '') == $loc['id'] ? 'selected' : '' ?>>
                                <?= esc($loc['name']) ?><?= $loc['building'] ? ' — ' . esc($loc['building']) : '' ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Vendor -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Vendor / Supplier</label>
                    <select name="vendor_id"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none">
                        <option value="">-- Pilih --</option>
                        <?php foreach ($vendors as $id => $name): ?>
                            <option value="<?= $id ?>"
                                <?= old('vendor_id', $asset['vendor_id'] ?? '') == $id ? 'selected' : '' ?>>
                                <?= esc($name) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Quantity -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Quantity / Jumlah</label>
                    <input type="number" name="quantity" min="1" step="1"
                           value="<?= old('quantity', $asset['quantity'] ?? 1) ?>"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none">
                </div>

                <!-- Unit / Satuan -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Satuan</label>
                    <select name="unit"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none">
                        <?php foreach ($units as $u): ?>
                            <option value="<?= $u ?>" <?= old('unit', $asset['unit'] ?? 'unit') === $u ? 'selected' : '' ?>>
                                <?= ucfirst($u) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Status Kondisi (baru/2nd/bekas) -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status Kondisi</label>
                    <select name="status_condition"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none">
                        <?php foreach (['baru' => 'Baru', '2nd' => '2nd'] as $v => $l): ?>
                            <option value="<?= $v ?>" <?= old('status_condition', $asset['status_condition'] ?? 'baru') === $v ? 'selected' : '' ?>>
                                <?= $l ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>

        <!-- ══════════════════════════════════════════════════════════
             SECTION 3 — PEMBELIAN, DEPRESIASI, GARANSI
             ══════════════════════════════════════════════════════════ -->
        <div class="bg-white border rounded-xl p-5 shadow-sm">
            <h2 class="text-sm font-bold text-gray-700 uppercase tracking-wide mb-4 pb-2 border-b flex items-center gap-2">
                <span class="text-purple-600">💰</span> Pembelian, Depresiasi & Garansi
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

                <!-- Tanggal Pembelian -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Pembelian</label>
                    <input type="date" name="purchase_date" id="purchaseDate"
                           value="<?= old('purchase_date', $asset['purchase_date'] ?? '') ?>"
                           onchange="calcAge()"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none">
                </div>

                <!-- Harga Beli -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Harga Beli (Rp)</label>
                    <input type="number" name="purchase_price" id="purchasePrice" min="0" step="1000"
                           value="<?= old('purchase_price', $asset['purchase_price'] ?? '') ?>"
                           placeholder="0" oninput="calcDepreciation()"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none">
                </div>

                <!-- Masa Pakai (dropdown) -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Masa Pakai (Tahun)</label>
                    <select name="depreciation_years" id="depreciationYears" onchange="calcDepreciation()"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none">
                        <option value="">-- Pilih --</option>
                        <?php foreach ($depreciation_years_opts as $y => $label): ?>
                            <option value="<?= $y ?>"
                                <?= old('depreciation_years', $asset['depreciation_years'] ?? '') == $y ? 'selected' : '' ?>>
                                <?= $label ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <p class="text-xs text-gray-400 mt-1">Untuk perhitungan depresiasi garis lurus</p>
                </div>

                <!-- Nilai Depresiasi Per Tahun (read-only, auto-calculated) -->
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nilai Depresiasi per Tahun (Otomatis)</label>
                    <input type="text" id="depreciationValueDisplay" readonly
                           value="<?= isset($asset['depreciation_value']) && $asset['depreciation_value'] > 0 
                                      ? 'Rp ' . number_format($asset['depreciation_value'], 0, ',', '.') 
                                      : '-' ?>"
                           class="w-full border border-gray-200 bg-gray-50 rounded-lg px-3 py-2 text-sm text-gray-600 cursor-not-allowed">
                    <p class="text-xs text-gray-400 mt-1">Harga Beli ÷ Masa Pakai</p>
                </div>

                <!-- Umur Aset (read-only, auto-calculated) -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Umur Aset (Otomatis)</label>
                    <input type="text" id="assetAge" readonly
                           value="<?= $isEdit && $asset['purchase_date'] 
                                      ? \App\Models\InventoryAssetModel::calcAge($asset['purchase_date'])['label'] 
                                      : '-' ?>"
                           class="w-full border border-gray-200 bg-gray-50 rounded-lg px-3 py-2 text-sm text-gray-600 cursor-not-allowed">
                    <p class="text-xs text-gray-400 mt-1">Dihitung dari tanggal pembelian</p>
                </div>

                <!-- Garansi Hingga -->
                <div class="md:col-span-3">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Garansi Hingga</label>
                    <input type="date" name="warranty_expiry"
                           value="<?= old('warranty_expiry', $asset['warranty_expiry'] ?? '') ?>"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none">
                </div>
            </div>
        </div>

        <!-- ══════════════════════════════════════════════════════════
             SECTION 4 — PEMELIHARAAN
             ══════════════════════════════════════════════════════════ -->
        <div class="bg-white border rounded-xl p-5 shadow-sm">
            <h2 class="text-sm font-bold text-gray-700 uppercase tracking-wide mb-4 pb-2 border-b flex items-center gap-2">
                <span class="text-orange-600">🔧</span> Pemeliharaan Preventif
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                <!-- Interval PM (Preventive Maintenance) -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Interval Pemeliharaan Rutin</label>
                    <select name="pm_interval_days"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none">
                        <option value="">-- Tidak ada jadwal rutin --</option>
                        <?php foreach ($pm_intervals as $days => $label): ?>
                            <option value="<?= $days ?>"
                                <?= old('pm_interval_days', $asset['pm_interval_days'] ?? '') == $days ? 'selected' : '' ?>>
                                <?= $label ?> (<?= $days ?> hari)
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <p class="text-xs text-gray-400 mt-1">Interval untuk preventive maintenance (PM)</p>
                </div>
            </div>
        </div>

        <!-- ══════════════════════════════════════════════════════════
             SECTION 4.5 — KALIBRASI ALAT MEDIS
             ══════════════════════════════════════════════════════════ -->
        <div class="bg-white border rounded-xl p-5 shadow-sm">
            <h2 class="text-sm font-bold text-gray-700 uppercase tracking-wide mb-4 pb-2 border-b flex items-center gap-2">
                <span class="text-teal-600">🔬</span> Kalibrasi Alat Medis
            </h2>
            <div class="space-y-4">
                <label class="flex items-center gap-2 text-sm text-gray-700 font-medium cursor-pointer">
                    <input type="checkbox" name="requires_calibration" id="requiresCalibration" value="1"
                           <?= old('requires_calibration', $asset['requires_calibration'] ?? 0) == 1 ? 'checked' : '' ?>
                           class="rounded text-teal-600 focus:ring-teal-400 h-4 w-4">
                    Memerlukan Kalibrasi Berkala
                </label>

                <div id="calibrationFields" class="grid grid-cols-1 md:grid-cols-2 gap-4 hidden">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Kalibrasi Terakhir</label>
                        <input type="date" name="last_calibration_date"
                               value="<?= old('last_calibration_date', $asset['last_calibration_date'] ?? '') ?>"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-teal-400 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Jadwal Kalibrasi Berikutnya</label>
                        <input type="date" name="next_calibration_date"
                               value="<?= old('next_calibration_date', $asset['next_calibration_date'] ?? '') ?>"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-teal-400 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nomor Sertifikat Kalibrasi</label>
                        <input type="text" name="calibration_certificate"
                               value="<?= old('calibration_certificate', $asset['calibration_certificate'] ?? '') ?>"
                               placeholder="Sertifikat/Keterangan Lolos Uji"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-teal-400 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Lembaga / Vendor Penguji</label>
                        <input type="text" name="calibration_vendor"
                               value="<?= old('calibration_vendor', $asset['calibration_vendor'] ?? '') ?>"
                               placeholder="Contoh: BPFK Surabaya"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-teal-400 focus:outline-none">
                    </div>
                </div>
            </div>
        </div>

        <!-- ══════════════════════════════════════════════════════════
             SECTION 5 — FOTO ASET
             ══════════════════════════════════════════════════════════ -->
        <div class="bg-white border rounded-xl p-5 shadow-sm">
            <h2 class="text-sm font-bold text-gray-700 uppercase tracking-wide mb-4 pb-2 border-b flex items-center gap-2">
                <span class="text-pink-600">📷</span> Foto Aset
            </h2>
            <div class="flex flex-col md:flex-row gap-4 items-start">
                <?php if ($isEdit && $asset['photo']): ?>
                <div class="shrink-0">
                    <p class="text-xs text-gray-500 mb-1">Foto saat ini:</p>
                    <img src="<?= base_url('uploads/assets/' . $asset['photo']) ?>"
                         alt="Foto"
                         class="w-32 h-32 object-cover rounded-xl border shadow-sm">
                </div>
                <?php endif; ?>
                <div class="flex-1">
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        <?= $isEdit ? 'Ganti Foto (Opsional)' : 'Upload Foto' ?>
                    </label>
                    <input type="file" name="photo" accept="image/*"
                           class="w-full text-sm text-gray-600 file:mr-3 file:py-1.5 file:px-4
                                  file:rounded-lg file:border-0 file:text-sm file:font-medium
                                  file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 cursor-pointer">
                    <p class="text-xs text-gray-400 mt-1">
                        JPG, PNG, WebP, GIF — Maks. 5 MB. Otomatis ter-compress ke WebP & resize.
                    </p>
                </div>
            </div>
        </div>

        <!-- ══════════════════════════════════════════════════════════
             TOMBOL AKSI
             ══════════════════════════════════════════════════════════ -->
        <div class="flex items-center gap-3 pt-2">
            <button type="submit"
                    class="<?= $isEdit ? 'bg-yellow-500 hover:bg-yellow-600' : 'bg-blue-600 hover:bg-blue-700' ?>
                           text-white font-semibold px-6 py-2.5 rounded-xl text-sm transition-colors shadow-sm">
                <?= $isEdit ? '💾 Perbarui Aset' : '✅ Simpan Aset' ?>
            </button>
            <a href="<?= $isEdit ? base_url('admin/inventory/' . $asset['id']) : base_url('admin/inventory') ?>"
               class="border border-gray-300 text-gray-700 hover:bg-gray-50 px-5 py-2.5 rounded-xl text-sm font-medium transition-colors">
                Batal
            </a>
        </div>
    </form>
</div>

<!-- ══════════════════════════════════════════════════════════════
     JAVASCRIPT — Auto-calculate Depresiasi & Umur Aset
     ══════════════════════════════════════════════════════════════ -->
<script>
function calcDepreciation() {
    const price = parseFloat(document.getElementById('purchasePrice').value) || 0;
    const years = parseInt(document.getElementById('depreciationYears').value) || 0;
    const display = document.getElementById('depreciationValueDisplay');

    if (price > 0 && years > 0) {
        const depValue = Math.round(price / years);
        display.value = 'Rp ' + depValue.toLocaleString('id-ID');
    } else {
        display.value = '-';
    }
}

function calcAge() {
    const dateStr = document.getElementById('purchaseDate').value;
    const ageField = document.getElementById('assetAge');

    if (!dateStr) {
        ageField.value = '-';
        return;
    }

    const purchaseDate = new Date(dateStr);
    const now = new Date();
    let years = now.getFullYear() - purchaseDate.getFullYear();
    let months = now.getMonth() - purchaseDate.getMonth();
    let days = now.getDate() - purchaseDate.getDate();

    if (days < 0) {
        months--;
        days += new Date(now.getFullYear(), now.getMonth(), 0).getDate();
    }
    if (months < 0) {
        years--;
        months += 12;
    }

    let label = '';
    if (years > 0) label += years + ' Tahun ';
    if (months > 0) label += months + ' Bulan';
    if (years === 0 && months === 0) label = days + ' Hari';

    ageField.value = label.trim() || '-';
}

// Kalibrasi toggle
const requiresCalibration = document.getElementById('requiresCalibration');
const calibrationFields = document.getElementById('calibrationFields');

function toggleCalibration() {
    if (requiresCalibration && requiresCalibration.checked) {
        calibrationFields.classList.remove('hidden');
    } else if (calibrationFields) {
        calibrationFields.classList.add('hidden');
    }
}

// Template selection auto-fill
const templateSelect = document.getElementById('templateSelect');
const nameInput = document.querySelector('input[name="name"]');
const categorySelect = document.querySelector('select[name="category"]');
const brandInput = document.querySelector('input[name="brand"]');
const modelInput = document.querySelector('input[name="model"]');

function handleTemplateChange() {
    if (!templateSelect) return;
    const opt = templateSelect.options[templateSelect.selectedIndex];
    if (opt && opt.value !== '') {
        if (nameInput) nameInput.value = opt.getAttribute('data-name') || '';
        if (categorySelect) categorySelect.value = opt.getAttribute('data-category') || '';
        if (brandInput) brandInput.value = opt.getAttribute('data-brand') || '';
        if (modelInput) modelInput.value = opt.getAttribute('data-model') || '';
    }
}

// Department - Location dynamic filter
const deptSelect = document.querySelector('select[name="department_id"]');
const locSelect = document.querySelector('select[name="location_id"]');

let allLocOptions = [];

function initLocationsFilter() {
    if (!deptSelect || !locSelect) return;
    allLocOptions = Array.from(locSelect.options);
    
    function filterLocations() {
        const selectedDeptId = deptSelect.value;
        const currentVal = locSelect.value;
        
        locSelect.innerHTML = '';
        locSelect.appendChild(allLocOptions[0]); // Keep "-- Pilih --"
        
        allLocOptions.forEach(opt => {
            if (opt.value === '') return;
            const optDeptId = opt.getAttribute('data-department-id');
            if (!selectedDeptId || !optDeptId || optDeptId === selectedDeptId) {
                locSelect.appendChild(opt);
            }
        });
        
        locSelect.value = currentVal;
    }
    
    deptSelect.addEventListener('change', filterLocations);
    filterLocations();
}

// Init saat load
document.addEventListener('DOMContentLoaded', () => {
    calcDepreciation();
    calcAge();
    
    if (requiresCalibration) {
        requiresCalibration.addEventListener('change', toggleCalibration);
        toggleCalibration();
    }
    
    if (templateSelect) {
        templateSelect.addEventListener('change', handleTemplateChange);
    }
    
    initLocationsFilter();
});
</script>

<?= $this->endSection() ?>
