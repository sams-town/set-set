<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ceklis Pemeliharaan — <?= esc($checklist['asset_name']) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            .no-print { display: none !important; }
            body { background: white !important; }
            @page { margin: 15mm; }
        }
        body { font-family: Arial, sans-serif; background: #f3f4f6; }
    </style>
</head>
<body class="min-h-screen bg-gray-100 py-6 px-4">

    <!-- Tombol Print -->
    <div class="no-print flex gap-3 mb-5 max-w-3xl mx-auto">
        <button onclick="window.print()"
                class="bg-teal-700 hover:bg-teal-800 text-white text-sm font-medium px-5 py-2 rounded-lg flex items-center gap-2">
            🖨️ Cetak / Simpan PDF
        </button>
        <a href="<?= base_url('admin/checklist/' . $checklist['id'] . '/edit') ?>"
           class="border border-gray-300 text-gray-600 hover:bg-gray-50 text-sm font-medium px-5 py-2 rounded-lg">
            ✏️ Edit
        </a>
        <a href="<?= base_url('admin/checklist') ?>"
           class="border border-gray-300 text-gray-600 hover:bg-gray-50 text-sm font-medium px-5 py-2 rounded-lg">
            ← Kembali
        </a>
    </div>

    <!-- Dokumen -->
    <div class="bg-white max-w-3xl mx-auto shadow-lg border">

        <!-- Header -->
        <div class="border-b-4 border-teal-700 px-8 pt-6 pb-4">
            <h1 class="text-lg font-bold uppercase text-teal-800 leading-tight">
                FORM CEKLIS PEMELIHARAAN PERALATAN RUMAH SAKIT
            </h1>
            <p class="text-xs italic text-gray-500 mt-1">(Alat Medis dan Non-Medis)</p>
        </div>

        <!-- Info Header -->
        <div class="px-8 py-4 border-b grid grid-cols-2 gap-x-8 gap-y-1.5 text-sm">
            <?php
            $dot = '.......................................................';
            $fields = [
                ['Nama Rumah Sakit', 'RS. Taman Harapan Baru'],
                ['Merk/Tipe', ($checklist['brand'] ?? '') . ($checklist['asset_model'] ? ' / ' . $checklist['asset_model'] : '') ?: '-'],
                ['Unit/Ruangan', $checklist['unit_ruangan'] ?: ($checklist['location_name'] ?? '-')],
                ['No. Seri', $checklist['serial_number'] ?? '-'],
                ['Nama Alat', $checklist['asset_name']],
                ['Tanggal Pemeriksaan', date('d/m/Y', strtotime($checklist['checklist_date']))],
                ['Nomor Inventaris', $checklist['asset_code']],
                ['Petugas Pemeriksa', $checklist['petugas_nama'] ?: ($checklist['technician_name'] ?? '-')],
            ];
            foreach ($fields as [$label, $value]):
            ?>
            <div class="flex items-baseline gap-1">
                <span class="font-semibold text-gray-800 whitespace-nowrap"><?= $label ?></span>
                <span class="text-gray-500 mx-0.5">:</span>
                <span class="flex-1 border-b border-dotted border-gray-400 text-gray-800 pb-0.5"><?= esc($value) ?></span>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Tabel Checklist -->
        <div class="px-8 py-4">
            <table class="w-full border-collapse border border-gray-400 text-sm">
                <thead>
                    <tr class="bg-gray-100">
                        <th class="border border-gray-400 px-2 py-2 text-center w-8 text-xs">No</th>
                        <th class="border border-gray-400 px-3 py-2 text-left text-xs">Item Pemeriksaan</th>
                        <th class="border border-gray-400 px-2 py-2 text-center w-16 text-xs">Baik (✓)</th>
                        <th class="border border-gray-400 px-2 py-2 text-center w-20 text-xs">Tidak Baik (X)</th>
                        <th class="border border-gray-400 px-3 py-2 text-left text-xs">Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($checklist['answers'] as $i => $answer): ?>
                    <tr>
                        <td class="border border-gray-400 px-2 py-2 text-center text-xs text-gray-600"><?= $i + 1 ?></td>
                        <td class="border border-gray-400 px-3 py-2 text-gray-800 text-xs"><?= esc($answer['item_text']) ?></td>
                        <td class="border border-gray-400 px-2 py-2 text-center text-base">
                            <?= $answer['status'] === 'baik' ? '✓' : '' ?>
                        </td>
                        <td class="border border-gray-400 px-2 py-2 text-center text-base">
                            <?= $answer['status'] === 'tidak' ? 'X' : '' ?>
                        </td>
                        <td class="border border-gray-400 px-3 py-2 text-xs text-gray-700">
                            <?= esc($answer['notes'] ?? '') ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Tindak Lanjut -->
        <?php
        $followUp = !empty($checklist['follow_up']) ? json_decode($checklist['follow_up'], true) : [];
        $followUpOptions = [
            'tidak_ada_perbaikan'       => 'Tidak ada perbaikan',
            'pemeliharaan_ringan'        => 'Perlu pemeliharaan ringan',
            'perbaikan_teknisi_vendor'   => 'Perlu perbaikan teknisi/vendor',
            'alat_tidak_boleh_digunakan' => 'Alat tidak boleh digunakan',
        ];
        ?>
        <div class="px-8 py-3 border-t">
            <p class="font-semibold text-teal-800 mb-2 text-sm">Tindak Lanjut</p>
            <div class="grid grid-cols-2 gap-1">
                <?php foreach ($followUpOptions as $val => $label): ?>
                <div class="flex items-center gap-2 text-sm text-gray-700">
                    <span class="w-4 h-4 border border-gray-500 flex items-center justify-center text-xs font-bold">
                        <?= in_array($val, $followUp) ? '✓' : '' ?>
                    </span>
                    <?= $label ?>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Catatan -->
        <div class="px-8 py-3 border-t">
            <p class="font-semibold text-gray-800 mb-1 text-sm">Catatan :</p>
            <div class="border-b border-dotted border-gray-400 py-1 text-sm text-gray-700 min-h-[24px]">
                <?= esc($checklist['notes'] ?? '') ?>
            </div>
            <div class="border-b border-dotted border-gray-400 mt-2 min-h-[24px]"></div>
        </div>

        <!-- Tanda Tangan -->
        <div class="px-8 py-4 border-t">
            <div class="grid grid-cols-3 gap-6 text-center text-xs">
                <div>
                    <div class="h-14 border-b border-gray-500"></div>
                    <p class="mt-1 text-gray-700">
                        <?= esc($checklist['technician_signature'] ?: '&nbsp;') ?>
                    </p>
                    <p class="font-semibold text-gray-800 mt-0.5">(Nama dan Paraf Petugas)</p>
                </div>
                <div>
                    <div class="h-14 border-b border-gray-500"></div>
                    <p class="mt-1 text-gray-700">
                        <?= esc($checklist['supervisor_signature'] ?: '&nbsp;') ?>
                    </p>
                    <p class="font-semibold text-gray-800 mt-0.5">(Kepala Departemen)</p>
                </div>
                <div>
                    <div class="h-14 border-b border-gray-500"></div>
                    <p class="mt-1 text-gray-700">
                        <?= esc($checklist['user_signature'] ?: '&nbsp;') ?>
                    </p>
                    <p class="font-semibold text-gray-800 mt-0.5">(Nama dan Paraf Pengguna Alat)</p>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="px-8 py-2 border-t bg-gray-50 flex justify-between text-xs text-gray-400">
            <span>RS. Taman Harapan Baru — Sistem Manajemen Aset</span>
            <span>Dicetak: <?= date('d/m/Y H:i') ?></span>
        </div>
    </div>

</body>
</html>
