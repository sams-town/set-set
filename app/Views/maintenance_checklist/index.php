<?= $this->extend('inventory/_layout') ?>
<?= $this->section('content') ?>

<!-- Header -->
<div class="flex items-center justify-between mb-5">
    <div class="flex items-center gap-2">
        <span class="text-2xl">📋</span>
        <div>
            <h1 class="text-xl font-bold text-gray-800">Riwayat Checklist Pemeliharaan</h1>
            <p class="text-xs text-gray-400 mt-0.5"><?= number_format($total_records) ?> checklist tercatat</p>
        </div>
    </div>
</div>

<!-- Filter Bar -->
<form method="GET" action="<?= base_url('admin/checklist') ?>"
      class="bg-white border rounded-xl p-4 shadow-sm mb-5">
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">

        <!-- Search -->
        <div class="lg:col-span-1">
            <label class="block text-xs text-gray-500 mb-1">Cari Aset / Teknisi</label>
            <input type="text" name="search"
                   value="<?= esc($filters['search'] ?? '') ?>"
                   placeholder="Nama aset, kode, teknisi..."
                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none">
        </div>

        <!-- Teknisi -->
        <div>
            <label class="block text-xs text-gray-500 mb-1">Teknisi</label>
            <select name="technician_id"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none">
                <option value="">-- Semua Teknisi --</option>
                <?php foreach ($technicians as $t): ?>
                <option value="<?= $t['id'] ?>"
                    <?= ($filters['technician_id'] ?? '') == $t['id'] ? 'selected' : '' ?>>
                    <?= esc($t['name']) ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Tanggal Dari -->
        <div>
            <label class="block text-xs text-gray-500 mb-1">Dari Tanggal</label>
            <input type="date" name="date_from"
                   value="<?= esc($filters['date_from'] ?? '') ?>"
                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none">
        </div>

        <!-- Tanggal Sampai -->
        <div>
            <label class="block text-xs text-gray-500 mb-1">Sampai Tanggal</label>
            <input type="date" name="date_to"
                   value="<?= esc($filters['date_to'] ?? '') ?>"
                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none">
        </div>
    </div>

    <div class="flex items-center gap-2 mt-3">
        <button type="submit"
                class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors">
            🔍 Filter
        </button>
        <?php if (array_filter($filters)): ?>
        <a href="<?= base_url('admin/checklist') ?>"
           class="border border-gray-300 text-gray-600 hover:bg-gray-50 text-sm font-medium px-4 py-2 rounded-lg transition-colors">
            ✕ Reset
        </a>
        <?php endif; ?>
        <span class="text-xs text-gray-400 ml-auto">
            Menampilkan <?= count($checklists) ?> dari <?= number_format($total_records) ?> data
        </span>
    </div>
</form>

<!-- Table -->
<div class="bg-white border rounded-xl shadow-sm overflow-hidden">
    <?php if (empty($checklists)): ?>
    <div class="text-center py-16 text-gray-400">
        <div class="text-5xl mb-3">📋</div>
        <p class="text-sm font-medium text-gray-500">Belum ada riwayat checklist</p>
        <p class="text-xs mt-1">Checklist dibuat saat teknisi melakukan scan QR aset</p>
    </div>
    <?php else: ?>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50 border-b text-xs text-gray-500 uppercase tracking-wide">
                    <th class="text-left px-4 py-3 w-10">#</th>
                    <th class="text-left px-4 py-3">Aset</th>
                    <th class="text-left px-4 py-3">Departemen</th>
                    <th class="text-left px-4 py-3">Teknisi</th>
                    <th class="text-left px-4 py-3 w-32">Tanggal</th>
                    <th class="text-center px-4 py-3 w-36">Hasil</th>
                    <th class="text-left px-4 py-3">Catatan</th>
                    <th class="text-center px-4 py-3 w-20">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <?php foreach ($checklists as $i => $cl):
                    $total    = (int) $cl['total_items'];
                    $ok       = (int) $cl['items_ok'];
                    $nok      = (int) $cl['items_nok'];
                    $pct      = $total > 0 ? round($ok / $total * 100) : 0;
                    $barColor = $pct >= 80 ? 'bg-green-500' : ($pct >= 50 ? 'bg-yellow-400' : 'bg-red-500');
                    $badgeClass = $pct >= 80 ? 'bg-green-100 text-green-700' : ($pct >= 50 ? 'bg-yellow-100 text-yellow-700' : 'bg-red-100 text-red-700');
                ?>
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="px-4 py-3 text-gray-400 text-xs">
                        <?= ($page - 1) * $per_page + $i + 1 ?>
                    </td>
                    <td class="px-4 py-3">
                        <p class="font-semibold text-gray-800"><?= esc($cl['asset_name'] ?? '-') ?></p>
                        <code class="text-xs text-gray-400"><?= esc($cl['asset_code'] ?? '') ?></code>
                        <?php if (!empty($cl['asset_category'])): ?>
                        <span class="ml-1 text-xs text-blue-600 bg-blue-50 px-1.5 py-0.5 rounded">
                            <?= esc($cl['asset_category']) ?>
                        </span>
                        <?php endif; ?>
                    </td>
                    <td class="px-4 py-3 text-gray-600 text-xs">
                        <?= esc($cl['department_name'] ?? '-') ?>
                    </td>
                    <td class="px-4 py-3">
                        <span class="text-gray-700"><?= esc($cl['technician_name'] ?? '<span class="text-gray-400">-</span>') ?></span>
                    </td>
                    <td class="px-4 py-3 text-gray-600">
                        <?= $cl['checklist_date'] ? date('d M Y', strtotime($cl['checklist_date'])) : '-' ?>
                    </td>
                    <td class="px-4 py-3">
                        <?php if ($total > 0): ?>
                        <div class="flex flex-col items-center gap-1">
                            <span class="text-xs font-semibold <?= $badgeClass ?> px-2 py-0.5 rounded-full">
                                <?= $ok ?>/<?= $total ?> Baik (<?= $pct ?>%)
                            </span>
                            <div class="w-full h-1.5 bg-gray-200 rounded-full overflow-hidden">
                                <div class="h-full rounded-full <?= $barColor ?>"
                                     style="width:<?= $pct ?>%"></div>
                            </div>
                            <?php if ($nok > 0): ?>
                            <span class="text-xs text-red-500"><?= $nok ?> item tidak baik</span>
                            <?php endif; ?>
                        </div>
                        <?php else: ?>
                        <span class="text-xs text-gray-400">— belum diisi —</span>
                        <?php endif; ?>
                    </td>
                    <td class="px-4 py-3 text-gray-500 text-xs max-w-[180px]">
                        <?php if ($cl['notes']): ?>
                        <p class="truncate" title="<?= esc($cl['notes']) ?>"><?= esc($cl['notes']) ?></p>
                        <?php else: ?>
                        <span class="text-gray-300">—</span>
                        <?php endif; ?>
                    </td>
                    <td class="px-4 py-3 text-center">
                        <a href="<?= base_url('admin/checklist/' . $cl['id']) ?>"
                           class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-blue-50 hover:bg-blue-100 text-blue-600 text-xs font-medium transition-colors">
                            👁 Lihat
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <?php if ($total_pages > 1): ?>
    <div class="px-4 py-3 border-t flex items-center justify-between bg-gray-50">
        <p class="text-xs text-gray-500">
            Halaman <?= $page ?> dari <?= $total_pages ?>
            &nbsp;·&nbsp; Total <?= number_format($total_records) ?> checklist
        </p>
        <div class="flex gap-1">
            <?php if ($page > 1): ?>
            <a href="?<?= http_build_query(array_merge($filters, ['page' => $page - 1])) ?>"
               class="px-3 py-1.5 text-xs border border-gray-300 rounded-lg hover:bg-gray-100 text-gray-600">
                ‹ Prev
            </a>
            <?php endif; ?>

            <?php
            $start = max(1, $page - 2);
            $end   = min($total_pages, $page + 2);
            for ($p = $start; $p <= $end; $p++):
            ?>
            <a href="?<?= http_build_query(array_merge($filters, ['page' => $p])) ?>"
               class="px-3 py-1.5 text-xs rounded-lg border transition-colors
                      <?= $p === $page ? 'bg-blue-600 text-white border-blue-600' : 'border-gray-300 hover:bg-gray-100 text-gray-600' ?>">
                <?= $p ?>
            </a>
            <?php endfor; ?>

            <?php if ($page < $total_pages): ?>
            <a href="?<?= http_build_query(array_merge($filters, ['page' => $page + 1])) ?>"
               class="px-3 py-1.5 text-xs border border-gray-300 rounded-lg hover:bg-gray-100 text-gray-600">
                Next ›
            </a>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
    <?php endif; ?>
</div>

<?= $this->endSection() ?>
