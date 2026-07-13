<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>

<div class="container mx-auto p-4 max-w-6xl">
    <div class="mb-4 flex items-center justify-between">
        <h1 class="text-xl font-bold text-gray-800">Detail Checklist Pemeliharaan</h1>
        <a href="javascript:history.back()" class="text-gray-500 hover:text-gray-700 text-sm">← Kembali</a>
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

    <!-- Checklist Table -->
    <div class="bg-white border rounded-xl p-4 shadow-sm mb-4">
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
                        <td class="border border-gray-300 p-2 text-center">
                            <?php if ($answer['status'] === 'baik'): ?>
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-700">
                                    ✓ Baik
                                </span>
                            <?php elseif ($answer['status'] === 'tidak'): ?>
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-700">
                                    ✗ Tidak
                                </span>
                            <?php else: ?>
                                <span class="text-gray-400 text-xs">-</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Catatan -->
        <?php if ($checklist['notes']): ?>
            <div class="mt-4 pt-4 border-t">
                <label class="block text-sm font-semibold text-gray-700 mb-2">CATATAN DAN KESIMPULAN:</label>
                <p class="text-sm text-gray-800 whitespace-pre-wrap"><?= esc($checklist['notes']) ?></p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?= $this->endSection() ?>
