<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>

<div class="container mx-auto p-4 max-w-6xl">
    <div class="mb-4 flex items-center justify-between">
        <h1 class="text-xl font-bold text-gray-800">Checklist Pemeliharaan Alat</h1>
    </div>

    <!-- Info Aset -->
    <div class="bg-white border rounded-xl p-4 shadow-sm mb-4">
        <h2 class="text-sm font-semibold text-gray-700 mb-3 pb-2 border-b">Informasi Alat</h2>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
            <div>
                <span class="text-gray-500">Nama Alat:</span>
                <span class="font-medium text-gray-800 ml-2"><?= esc($checklist['asset_name']) ?></span>
            </div>
            <div>
                <span class="text-gray-500">No. Inventaris:</span>
                <span class="font-medium text-gray-800 ml-2"><?= esc($checklist['asset_code']) ?></span>
            </div>
            <div>
                <span class="text-gray-500">Tanggal:</span>
                <span class="font-medium text-gray-800 ml-2"><?= date('d/m/Y', strtotime($checklist['checklist_date'])) ?></span>
            </div>
            <div>
                <span class="text-gray-500">Teknisi:</span>
                <span class="font-medium text-gray-800 ml-2"><?= esc($checklist['technician_name'] ?? '-') ?></span>
            </div>
        </div>
    </div>

    <!-- Form Checklist -->
    <form method="POST" class="bg-white border rounded-xl shadow-sm">
        <div class="p-4">
            <h2 class="text-sm font-semibold text-gray-700 mb-3 pb-2 border-b">CEKLIST PEMELIHARAAN ALAT</h2>
            
            <div class="overflow-x-auto">
                <table class="w-full border-collapse border border-gray-300 text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="border border-gray-300 p-2 w-12 text-center">No</th>
                            <th class="border border-gray-300 p-2 text-left">ITEM PEMERIKSAAN</th>
                            <th class="border border-gray-300 p-2 w-32 text-center">KONDISI</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($checklist['answers'] as $i => $answer): ?>
                        <tr class="<?= $i % 2 === 0 ? 'bg-white' : 'bg-gray-50' ?>">
                            <td class="border border-gray-300 p-2 text-center"><?= $i + 1 ?></td>
                            <td class="border border-gray-300 p-2"><?= esc($answer['item_text']) ?></td>
                            <td class="border border-gray-300 p-2">
                                <div class="flex items-center justify-center gap-2">
                                    <label class="flex items-center gap-1 cursor-pointer">
                                        <input type="radio" 
                                               name="answers[<?= $answer['id'] ?>][status]" 
                                               value="baik" 
                                               <?= $answer['status'] === 'baik' ? 'checked' : '' ?>
                                               class="text-green-600">
                                        <span class="text-xs text-gray-700">Baik</span>
                                    </label>
                                    <label class="flex items-center gap-1 cursor-pointer">
                                        <input type="radio" 
                                               name="answers[<?= $answer['id'] ?>][status]" 
                                               value="tidak" 
                                               <?= $answer['status'] === 'tidak' ? 'checked' : '' ?>
                                               class="text-red-600">
                                        <span class="text-xs text-gray-700">Tidak</span>
                                    </label>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Catatan dan Kesimpulan -->
            <div class="mt-4">
                <label class="block text-sm font-semibold text-gray-700 mb-2">CATATAN DAN KESIMPULAN:</label>
                <textarea name="notes" 
                          class="w-full border border-gray-300 rounded-lg p-3 text-sm" 
                          rows="4"><?= esc($checklist['notes'] ?? '') ?></textarea>
            </div>
        </div>
        
        <!-- Tanda Tangan -->
        <div class="p-4 border-t bg-gray-50">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                <div class="text-center">
                    <div class="border-t pt-2 mt-6">
                        <p class="text-gray-700">Nama dan Paraf Petugas :</p>
                    </div>
                </div>
                <div class="text-center">
                    <div class="border-t pt-2 mt-6">
                        <p class="text-gray-700">Mengetahui Pembeli Tugas :</p>
                    </div>
                </div>
                <div class="text-center">
                    <div class="border-t pt-2 mt-6">
                        <p class="text-gray-700">Nama dan Paraf Pengguna Alat :</p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="p-4 border-t flex justify-end gap-2">
            <button type="submit" 
                    class="bg-blue-600 hover:bg-blue-700 text-white font-medium px-4 py-2 rounded-lg transition-colors">
                Simpan Checklist
            </button>
        </div>
    </form>
</div>

<?= $this->endSection() ?>
