<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tanda Terima Peminjaman — <?= esc($borrow['borrow_code']) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            .no-print { display: none !important; }
            body { background: white !important; }
            .page { box-shadow: none !important; margin: 0 !important; }
        }
        body { font-family: 'Arial', sans-serif; background: #f3f4f6; }
    </style>
</head>
<body class="min-h-screen flex flex-col items-center py-8 px-4">

    <!-- Tombol Aksi (tidak tercetak) -->
    <div class="no-print flex gap-3 mb-6 w-full max-w-2xl">
        <button onclick="window.print()"
                class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-5 py-2 rounded-lg flex items-center gap-2">
            🖨️ Cetak / Simpan PDF
        </button>
        <a href="<?= base_url('admin/borrows/' . $borrow['id']) ?>"
           class="border border-gray-300 text-gray-600 hover:bg-gray-50 text-sm font-medium px-5 py-2 rounded-lg">
            ← Kembali
        </a>
    </div>

    <!-- Dokumen Tanda Terima -->
    <div class="page bg-white w-full max-w-2xl shadow-lg rounded-xl overflow-hidden">

        <!-- Header Organisasi -->
        <div class="border-b-4 border-blue-700 p-6">
            <div class="flex items-start justify-between">
                <div>
                    <h1 class="text-xl font-bold text-blue-800 uppercase tracking-wide">
                        <?= esc($orgName) ?>
                    </h1>
                    <p class="text-xs text-gray-500 mt-0.5">Sistem Manajemen Aset</p>
                </div>
                <div class="text-right">
                    <div class="text-xs text-gray-400 uppercase tracking-widest mb-1">No. Dokumen</div>
                    <div class="font-mono font-bold text-blue-700 text-lg"><?= esc($borrow['borrow_code']) ?></div>
                </div>
            </div>

            <div class="mt-4 text-center">
                <h2 class="text-base font-bold text-gray-800 uppercase tracking-wider border border-gray-300 inline-block px-6 py-1 rounded">
                    TANDA TERIMA PEMINJAMAN ASET
                </h2>
            </div>
        </div>

        <!-- Body -->
        <div class="p-6 space-y-5">

            <!-- Info Aset -->
            <div>
                <h3 class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-2 pb-1 border-b">
                    A. Data Aset yang Dipinjam
                </h3>
                <table class="w-full text-sm">
                    <tr>
                        <td class="py-1 text-gray-500 w-44">Nama Aset</td>
                        <td class="py-1 font-medium text-gray-800">: <?= esc($borrow['asset_name']) ?></td>
                    </tr>
                    <tr>
                        <td class="py-1 text-gray-500">Kode / No. Inventaris</td>
                        <td class="py-1 font-mono font-medium text-gray-800">: <?= esc($borrow['asset_code']) ?></td>
                    </tr>
                    <tr>
                        <td class="py-1 text-gray-500">Brand / Tipe</td>
                        <td class="py-1 font-medium text-gray-800">: <?= esc(($asset['brand'] ?? '') . ($asset['model'] ? ' / ' . $asset['model'] : '')) ?: '-' ?></td>
                    </tr>
                    <tr>
                        <td class="py-1 text-gray-500">Kondisi Saat Dipinjam</td>
                        <td class="py-1 font-medium text-gray-800">: <?= esc($asset['condition'] ?? '-') ?></td>
                    </tr>
                    <tr>
                        <td class="py-1 text-gray-500">Departemen Aset</td>
                        <td class="py-1 font-medium text-gray-800">: <?= esc($asset['department_name'] ?? '-') ?></td>
                    </tr>
                    <tr>
                        <td class="py-1 text-gray-500">Lokasi Aset</td>
                        <td class="py-1 font-medium text-gray-800">: <?= esc($asset['location_name'] ?? '-') ?></td>
                    </tr>
                </table>
            </div>

            <!-- Info Peminjam -->
            <div>
                <h3 class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-2 pb-1 border-b">
                    B. Data Peminjam
                </h3>
                <table class="w-full text-sm">
                    <tr>
                        <td class="py-1 text-gray-500 w-44">Nama Peminjam</td>
                        <td class="py-1 font-medium text-gray-800">: <?= esc($borrow['borrower_name'] ?: $borrow['user_name']) ?></td>
                    </tr>
                    <tr>
                        <td class="py-1 text-gray-500">Departemen</td>
                        <td class="py-1 font-medium text-gray-800">: <?= esc($borrow['borrower_dept'] ?? '-') ?></td>
                    </tr>
                    <tr>
                        <td class="py-1 text-gray-500">No. HP</td>
                        <td class="py-1 font-medium text-gray-800">: <?= esc($borrow['borrower_phone'] ?? '-') ?></td>
                    </tr>
                    <tr>
                        <td class="py-1 text-gray-500">Email</td>
                        <td class="py-1 font-medium text-gray-800">: <?= esc($borrow['user_email'] ?? '-') ?></td>
                    </tr>
                </table>
            </div>

            <!-- Info Peminjaman -->
            <div>
                <h3 class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-2 pb-1 border-b">
                    C. Detail Peminjaman
                </h3>
                <table class="w-full text-sm">
                    <tr>
                        <td class="py-1 text-gray-500 w-44">Tanggal Pinjam</td>
                        <td class="py-1 font-medium text-gray-800">: <?= $borrow['borrow_date'] ? date('d F Y', strtotime($borrow['borrow_date'])) : '-' ?></td>
                    </tr>
                    <tr>
                        <td class="py-1 text-gray-500">Rencana Kembali</td>
                        <td class="py-1 font-medium text-gray-800">: <?= $borrow['return_date_plan'] ? date('d F Y', strtotime($borrow['return_date_plan'])) : '—' ?></td>
                    </tr>
                    <?php if ($borrow['return_date_actual']): ?>
                    <tr>
                        <td class="py-1 text-gray-500">Tanggal Dikembalikan</td>
                        <td class="py-1 font-medium text-green-700">: <?= date('d F Y', strtotime($borrow['return_date_actual'])) ?></td>
                    </tr>
                    <?php endif; ?>
                    <tr>
                        <td class="py-1 text-gray-500">Status</td>
                        <td class="py-1 font-semibold <?= $borrow['status'] === 'dipinjam' ? 'text-yellow-600' : 'text-green-600' ?>">
                            : <?= strtoupper($borrow['status']) ?>
                        </td>
                    </tr>
                    <tr>
                        <td class="py-1 text-gray-500">Keperluan</td>
                        <td class="py-1 font-medium text-gray-800">: <?= esc($borrow['purpose'] ?: '-') ?></td>
                    </tr>
                    <tr>
                        <td class="py-1 text-gray-500">Disetujui Oleh</td>
                        <td class="py-1 font-medium text-gray-800">: <?= esc($borrow['approver_name'] ?? '-') ?></td>
                    </tr>
                </table>
            </div>

            <!-- Pernyataan -->
            <div class="bg-gray-50 border rounded-lg p-3 text-xs text-gray-600">
                <p>Dengan ditandatanganinya dokumen ini, peminjam menyatakan telah menerima aset tersebut di atas dalam kondisi baik dan bersedia bertanggung jawab atas keselamatannya selama masa peminjaman. Peminjam wajib mengembalikan aset sesuai waktu yang telah ditentukan.</p>
            </div>

            <!-- Tanda Tangan -->
            <div class="grid grid-cols-3 gap-6 pt-2">
                <div class="text-center text-sm">
                    <p class="text-xs text-gray-500 mb-1">Yang Meminjam</p>
                    <div class="h-16 border-b border-gray-400 mt-2"></div>
                    <p class="font-medium text-gray-800 mt-1"><?= esc($borrow['borrower_name'] ?: $borrow['user_name']) ?></p>
                    <p class="text-xs text-gray-400"><?= esc($borrow['borrower_dept'] ?? '') ?></p>
                </div>
                <div class="text-center text-sm">
                    <p class="text-xs text-gray-500 mb-1">Mengetahui</p>
                    <div class="h-16 border-b border-gray-400 mt-2"></div>
                    <p class="font-medium text-gray-800 mt-1">Kepala Departemen</p>
                    <p class="text-xs text-gray-400"><?= esc($asset['department_name'] ?? '') ?></p>
                </div>
                <div class="text-center text-sm">
                    <p class="text-xs text-gray-500 mb-1">Diserahkan Oleh</p>
                    <div class="h-16 border-b border-gray-400 mt-2"></div>
                    <p class="font-medium text-gray-800 mt-1"><?= esc($borrow['approver_name'] ?? '-') ?></p>
                    <p class="text-xs text-gray-400">Pengelola Aset</p>
                </div>
            </div>

            <!-- Footer -->
            <div class="border-t pt-3 flex items-center justify-between text-xs text-gray-400">
                <span>Dicetak: <?= date('d F Y H:i') ?></span>
                <span><?= esc($orgName) ?> — Sistem Manajemen Aset</span>
                <span><?= esc($borrow['borrow_code']) ?></span>
            </div>
        </div>
    </div>

</body>
</html>
