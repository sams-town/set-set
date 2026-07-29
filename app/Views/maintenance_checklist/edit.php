<?= $this->extend('inventory/_layout') ?>
<?= $this->section('content') ?>
<?php
$followUp = !empty($checklist['follow_up']) ? json_decode($checklist['follow_up'], true) : [];
$orgName  = 'RS. Taman Harapan Baru';
?>

<!-- Breadcrumb + Tombol Print -->
<div class="flex items-center justify-between mb-4">
    <div class="flex items-center gap-2 text-sm text-gray-500">
        <a href="<?= base_url('admin/checklist') ?>" class="hover:text-blue-600">Checklist</a>
        <span>›</span>
        <span class="font-medium text-gray-800">Isi — <?= esc($checklist['asset_name']) ?></span>
    </div>
    <a href="<?= base_url('admin/checklist/' . $checklist['id'] . '/print') ?>" target="_blank"
       class="inline-flex items-center gap-1.5 bg-gray-700 hover:bg-gray-800 text-white text-sm font-medium px-4 py-2 rounded-lg no-print">
        🖨️ Print / PDF
    </a>
</div>

<div class="max-w-3xl mx-auto">
<form method="POST" action="<?= base_url('admin/checklist/' . $checklist['id']) ?>">
    <?= csrf_field() ?>

    <!-- ══ FORM UTAMA ══ -->
    <div class="bg-white border rounded-xl shadow-sm overflow-hidden mb-4">

        <!-- Header Form -->
        <div class="bg-teal-700 text-white px-6 py-4">
            <h1 class="text-base font-bold uppercase tracking-wide">FORM CEKLIS PEMELIHARAAN PERALATAN RUMAH SAKIT</h1>
            <p class="text-teal-200 text-xs mt-0.5 italic">(Alat Medis dan Non-Medis)</p>
        </div>

        <!-- Header Info -->
        <div class="px-6 py-4 border-b grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-2 text-sm">
            <div class="flex items-center gap-2">
                <span class="font-semibold text-gray-700 whitespace-nowrap w-36">Nama Rumah Sakit</span>
                <span class="text-gray-500">:</span>
                <span class="flex-1 border-b border-dotted border-gray-400 pb-0.5 text-gray-800"><?= esc($orgName) ?></span>
            </div>
            <div class="flex items-center gap-2">
                <span class="font-semibold text-gray-700 whitespace-nowrap w-28">Merk/Tipe</span>
                <span class="text-gray-500">:</span>
                <span class="flex-1 border-b border-dotted border-gray-400 pb-0.5 text-gray-800">
                    <?= esc(($checklist['brand'] ?? '') . ($checklist['asset_model'] ? ' / ' . $checklist['asset_model'] : '') ?: '-') ?>
                </span>
            </div>
            <div class="flex items-center gap-2">
                <span class="font-semibold text-gray-700 whitespace-nowrap w-36">Unit/Ruangan</span>
                <span class="text-gray-500">:</span>
                <input type="text" name="unit_ruangan"
                       value="<?= esc($checklist['unit_ruangan'] ?? $checklist['location_name'] ?? '') ?>"
                       placeholder="Unit / Ruangan..."
                       class="flex-1 border-b border-dotted border-gray-400 pb-0.5 text-gray-800 focus:outline-none focus:border-teal-500 bg-transparent text-sm">
            </div>
            <div class="flex items-center gap-2">
                <span class="font-semibold text-gray-700 whitespace-nowrap w-28">No. Seri</span>
                <span class="text-gray-500">:</span>
                <span class="flex-1 border-b border-dotted border-gray-400 pb-0.5 text-gray-800">
                    <?= esc($checklist['serial_number'] ?? '-') ?>
                </span>
            </div>
            <div class="flex items-center gap-2">
                <span class="font-semibold text-gray-700 whitespace-nowrap w-36">Nama Alat</span>
                <span class="text-gray-500">:</span>
                <span class="flex-1 border-b border-dotted border-gray-400 pb-0.5 text-gray-800">
                    <?= esc($checklist['asset_name']) ?>
                </span>
            </div>
            <div class="flex items-center gap-2">
                <span class="font-semibold text-gray-700 whitespace-nowrap w-28">Tanggal Pemeriksaan</span>
                <span class="text-gray-500">:</span>
                <span class="flex-1 border-b border-dotted border-gray-400 pb-0.5 text-gray-800">
                    <?= date('d/m/Y', strtotime($checklist['checklist_date'])) ?>
                </span>
            </div>
            <div class="flex items-center gap-2">
                <span class="font-semibold text-gray-700 whitespace-nowrap w-36">Nomor Inventaris</span>
                <span class="text-gray-500">:</span>
                <span class="flex-1 border-b border-dotted border-gray-400 pb-0.5 text-gray-800">
                    <?= esc($checklist['asset_code']) ?>
                </span>
            </div>
            <div class="flex items-center gap-2">
                <span class="font-semibold text-gray-700 whitespace-nowrap w-28">Petugas Pemeriksa</span>
                <span class="text-gray-500">:</span>
                <input type="text" name="petugas_nama"
                       value="<?= esc($checklist['petugas_nama'] ?? $checklist['technician_name'] ?? '') ?>"
                       placeholder="Nama petugas..."
                       class="flex-1 border-b border-dotted border-gray-400 pb-0.5 text-gray-800 focus:outline-none focus:border-teal-500 bg-transparent text-sm">
            </div>
        </div>

        <!-- Tabel Checklist -->
        <div class="px-6 py-4">
            <table class="w-full border-collapse border border-gray-300 text-sm">
                <thead>
                    <tr class="bg-gray-50">
                        <th class="border border-gray-300 px-3 py-2 text-center w-10 text-xs font-semibold text-gray-600">No</th>
                        <th class="border border-gray-300 px-3 py-2 text-left text-xs font-semibold text-gray-600">Item Pemeriksaan</th>
                        <th class="border border-gray-300 px-3 py-2 text-center w-20 text-xs font-semibold text-gray-600">Baik (✓)</th>
                        <th class="border border-gray-300 px-3 py-2 text-center w-24 text-xs font-semibold text-gray-600">Tidak Baik (X)</th>
                        <th class="border border-gray-300 px-3 py-2 text-left text-xs font-semibold text-gray-600">Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($checklist['answers'] as $i => $answer): ?>
                    <tr class="hover:bg-gray-50/50">
                        <td class="border border-gray-300 px-3 py-2.5 text-center text-gray-500 text-xs"><?= $i + 1 ?></td>
                        <td class="border border-gray-300 px-3 py-2.5 text-gray-800"><?= esc($answer['item_text']) ?></td>
                        <td class="border border-gray-300 px-3 py-2.5 text-center">
                            <input type="radio"
                                   name="answers[<?= (int)$answer['id'] ?>][status]"
                                   value="baik"
                                   <?= ($answer['status'] === 'baik') ? 'checked' : '' ?>
                                   class="w-4 h-4 accent-teal-600 cursor-pointer">
                        </td>
                        <td class="border border-gray-300 px-3 py-2.5 text-center">
                            <input type="radio"
                                   name="answers[<?= (int)$answer['id'] ?>][status]"
                                   value="tidak"
                                   <?= ($answer['status'] === 'tidak') ? 'checked' : '' ?>
                                   class="w-4 h-4 accent-red-600 cursor-pointer">
                        </td>
                        <td class="border border-gray-300 px-1 py-1">
                            <input type="text"
                                   name="answers[<?= (int)$answer['id'] ?>][notes]"
                                   value="<?= esc($answer['notes'] ?? '') ?>"
                                   placeholder="Keterangan..."
                                   class="w-full px-2 py-1 text-xs text-gray-700 focus:outline-none focus:ring-1 focus:ring-teal-400 rounded bg-transparent">
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Tindak Lanjut -->
        <div class="px-6 py-4 border-t">
            <p class="font-semibold text-teal-700 mb-3 text-sm">Tindak Lanjut</p>
            <div class="space-y-2">
                <?php
                $followUpOptions = [
                    'tidak_ada_perbaikan'      => 'Tidak ada perbaikan',
                    'pemeliharaan_ringan'       => 'Perlu pemeliharaan ringan',
                    'perbaikan_teknisi_vendor'  => 'Perlu perbaikan teknisi/vendor',
                    'alat_tidak_boleh_digunakan'=> 'Alat tidak boleh digunakan',
                ];
                foreach ($followUpOptions as $val => $label): ?>
                <label class="flex items-center gap-2 cursor-pointer text-sm text-gray-700">
                    <input type="checkbox"
                           name="follow_up[]"
                           value="<?= $val ?>"
                           <?= in_array($val, $followUp) ? 'checked' : '' ?>
                           class="w-4 h-4 accent-teal-600 cursor-pointer">
                    <?= $label ?>
                </label>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Catatan -->
        <div class="px-6 py-4 border-t">
            <p class="font-semibold text-gray-700 mb-2 text-sm">Catatan :</p>
            <textarea name="notes"
                      rows="3"
                      placeholder="Tuliskan catatan tambahan..."
                      class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:ring-2 focus:ring-teal-400 focus:outline-none resize-none"><?= esc($checklist['notes'] ?? '') ?></textarea>
        </div>

        <!-- Tanda Tangan -->
        <div class="px-6 py-4 border-t bg-gray-50">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

                <div class="text-center">
                    <label class="block text-xs text-gray-500 mb-1">Nama dan Paraf Petugas</label>
                    <input type="text" name="technician_signature"
                           value="<?= esc($checklist['technician_signature'] ?? '') ?>"
                           placeholder="Nama petugas..."
                           class="w-full border border-gray-300 rounded px-2 py-1.5 text-sm text-center focus:outline-none focus:ring-1 focus:ring-teal-400 mb-2">
                    <div class="h-12 border-b-2 border-gray-400"></div>
                    <p class="text-xs text-gray-500 mt-1">Nama dan Paraf Petugas</p>
                </div>

                <div class="text-center">
                    <label class="block text-xs text-gray-500 mb-1">Mengetahui Pemberi Tugas</label>
                    <input type="text" name="supervisor_signature"
                           value="<?= esc($checklist['supervisor_signature'] ?? '') ?>"
                           placeholder="Nama pemberi tugas..."
                           class="w-full border border-gray-300 rounded px-2 py-1.5 text-sm text-center focus:outline-none focus:ring-1 focus:ring-teal-400 mb-2">
                    <div class="h-12 border-b-2 border-gray-400"></div>
                    <p class="text-xs text-gray-500 mt-1">Mengetahui Pemberi Tugas</p>
                </div>

                <div class="text-center">
                    <label class="block text-xs text-gray-500 mb-1">Nama dan Paraf Pengguna Alat</label>
                    <input type="text" name="user_signature"
                           value="<?= esc($checklist['user_signature'] ?? '') ?>"
                           placeholder="Nama pengguna alat..."
                           class="w-full border border-gray-300 rounded px-2 py-1.5 text-sm text-center focus:outline-none focus:ring-1 focus:ring-teal-400 mb-2">
                    <div class="h-12 border-b-2 border-gray-400"></div>
                    <p class="text-xs text-gray-500 mt-1">Nama dan Paraf Pengguna Alat</p>
                </div>

            </div>
        </div>
    </div>

    <!-- Tombol Simpan -->
    <div class="flex items-center gap-3 mb-8">
        <button type="submit"
                class="bg-teal-600 hover:bg-teal-700 text-white font-medium px-6 py-2.5 rounded-xl text-sm transition-colors shadow-sm">
            💾 Simpan Checklist
        </button>
        <a href="<?= base_url('admin/checklist') ?>"
           class="border border-gray-300 text-gray-600 hover:bg-gray-50 px-5 py-2.5 rounded-xl text-sm font-medium">
            Kembali
        </a>
    </div>
</form>
</div>

<?= $this->endSection() ?>
