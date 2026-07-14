<?= $this->extend('inventory/_layout') ?>

<?= $this->section('content') ?>

<div class="max-w-3xl mx-auto">

    <!-- Breadcrumb -->
    <div class="flex items-center gap-2 text-sm text-gray-500 mb-4">
        <a href="<?= base_url('admin/checklist') ?>" class="hover:text-blue-600">Checklist</a>
        <span>›</span>
        <span class="font-medium text-gray-800">Isi Checklist — <?= esc($checklist['asset_name']) ?></span>
    </div>

    <!-- Info Aset -->
    <div class="bg-white border rounded-xl p-4 shadow-sm mb-4">
        <h2 class="text-sm font-semibold text-gray-700 mb-3 pb-2 border-b">Informasi Alat</h2>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
            <div>
                <p class="text-xs text-gray-400">Nama Alat</p>
                <p class="font-medium text-gray-800"><?= esc($checklist['asset_name']) ?></p>
            </div>
            <div>
                <p class="text-xs text-gray-400">No. Inventaris</p>
                <p class="font-medium text-gray-800"><?= esc($checklist['asset_code']) ?></p>
            </div>
            <div>
                <p class="text-xs text-gray-400">Tanggal</p>
                <p class="font-medium text-gray-800"><?= date('d/m/Y', strtotime($checklist['checklist_date'])) ?></p>
            </div>
            <div>
                <p class="text-xs text-gray-400">Teknisi</p>
                <p class="font-medium text-gray-800"><?= esc($checklist['technician_name'] ?? '-') ?></p>
            </div>
        </div>
    </div>

    <!-- FIX #1: action mengarah ke POST /admin/checklist/{id} bukan /edit -->
    <form method="POST" action="<?= base_url('admin/checklist/' . $checklist['id']) ?>">
        <?= csrf_field() ?>

        <!-- Tabel Checklist -->
        <div class="bg-white border rounded-xl shadow-sm mb-4">
            <div class="p-4 border-b">
                <h2 class="text-sm font-semibold text-gray-700">CEKLIST PEMELIHARAAN ALAT</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full border-collapse text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="border border-gray-200 p-2.5 w-12 text-center text-xs text-gray-500">No</th>
                            <th class="border border-gray-200 p-2.5 text-left text-xs text-gray-500">ITEM PEMERIKSAAN</th>
                            <th class="border border-gray-200 p-2.5 w-40 text-center text-xs text-gray-500">KONDISI</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($checklist['answers'] as $i => $answer): ?>
                        <tr class="<?= $i % 2 === 0 ? 'bg-white' : 'bg-gray-50/50' ?>">
                            <td class="border border-gray-200 p-2.5 text-center text-gray-500 text-xs"><?= $i + 1 ?></td>
                            <td class="border border-gray-200 p-2.5 text-gray-800"><?= esc($answer['item_text']) ?></td>
                            <td class="border border-gray-200 p-2.5">
                                <!-- FIX #3: Radio pakai name unik per baris, checked PHP tidak konflik JS -->
                                <div class="flex items-center justify-center gap-4">
                                    <label class="flex items-center gap-1.5 cursor-pointer select-none">
                                        <input type="radio"
                                               name="answers[<?= (int)$answer['id'] ?>][status]"
                                               value="baik"
                                               <?= ($answer['status'] === 'baik') ? 'checked' : '' ?>
                                               class="w-4 h-4 accent-green-600 cursor-pointer">
                                        <span class="text-sm text-gray-700">Baik</span>
                                    </label>
                                    <label class="flex items-center gap-1.5 cursor-pointer select-none">
                                        <input type="radio"
                                               name="answers[<?= (int)$answer['id'] ?>][status]"
                                               value="tidak"
                                               <?= ($answer['status'] === 'tidak') ? 'checked' : '' ?>
                                               class="w-4 h-4 accent-red-600 cursor-pointer">
                                        <span class="text-sm text-gray-700">Tidak</span>
                                    </label>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Catatan -->
            <div class="p-4 border-t">
                <label class="block text-sm font-semibold text-gray-700 mb-2">CATATAN DAN KESIMPULAN:</label>
                <textarea name="notes"
                          rows="4"
                          placeholder="Tuliskan catatan dan kesimpulan pemeliharaan..."
                          class="w-full border border-gray-300 rounded-lg p-3 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none resize-none"><?= esc($checklist['notes'] ?? '') ?></textarea>
            </div>
        </div>

        <!-- FIX #2: Tanda tangan dengan input text yang bisa diketik -->
        <div class="bg-white border rounded-xl shadow-sm mb-4">
            <div class="p-4 border-b">
                <h2 class="text-sm font-semibold text-gray-700">TANDA TANGAN</h2>
            </div>
            <div class="p-4">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 text-sm">

                    <div class="flex flex-col">
                        <label class="text-xs text-gray-500 mb-1 font-medium">Nama dan Paraf Petugas</label>
                        <input type="text"
                               name="technician_signature"
                               value="<?= esc($checklist['technician_signature'] ?? '') ?>"
                               placeholder="Nama petugas..."
                               class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none mb-2">
                        <div class="border-b-2 border-gray-400 mt-10 mb-1"></div>
                        <p class="text-xs text-gray-500 text-center">Nama dan Paraf Petugas</p>
                    </div>

                    <div class="flex flex-col">
                        <label class="text-xs text-gray-500 mb-1 font-medium">Mengetahui Pemberi Tugas</label>
                        <input type="text"
                               name="supervisor_signature"
                               value="<?= esc($checklist['supervisor_signature'] ?? '') ?>"
                               placeholder="Nama pemberi tugas..."
                               class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none mb-2">
                        <div class="border-b-2 border-gray-400 mt-10 mb-1"></div>
                        <p class="text-xs text-gray-500 text-center">Mengetahui Pemberi Tugas</p>
                    </div>

                    <div class="flex flex-col">
                        <label class="text-xs text-gray-500 mb-1 font-medium">Nama dan Paraf Pengguna Alat</label>
                        <input type="text"
                               name="user_signature"
                               value="<?= esc($checklist['user_signature'] ?? '') ?>"
                               placeholder="Nama pengguna alat..."
                               class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none mb-2">
                        <div class="border-b-2 border-gray-400 mt-10 mb-1"></div>
                        <p class="text-xs text-gray-500 text-center">Nama dan Paraf Pengguna Alat</p>
                    </div>

                </div>
            </div>
        </div>

        <!-- Tombol Simpan -->
        <div class="flex items-center gap-3">
            <button type="submit"
                    class="bg-blue-600 hover:bg-blue-700 text-white font-medium px-6 py-2.5 rounded-xl text-sm transition-colors shadow-sm">
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
