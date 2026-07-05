<?= $this->extend('inventory/_layout') ?>
<?= $this->section('content') ?>
<?php
$rp  = fn($n) => 'Rp ' . number_format((float)$n, 0, ',', '.');
$ps  = $status[$po['status']] ?? ['label'=>$po['status'],'color'=>'bg-gray-100 text-gray-600'];
$canReceive  = in_array($po['status'], ['sent','confirmed','partial']);
$canRegister = $po['status'] === 'completed';
?>

<!-- Breadcrumb -->
<div class="flex items-center gap-2 text-sm text-gray-500 mb-4">
    <a href="<?= base_url('admin/procurement') ?>" class="hover:text-blue-600">Procurement</a>
    <span>›</span>
    <?php if ($req): ?>
    <a href="<?= base_url('admin/procurement/'.$req['id']) ?>" class="hover:text-blue-600"><?= esc($req['request_code']) ?></a>
    <span>›</span>
    <?php endif; ?>
    <span class="font-mono font-medium text-gray-800"><?= esc($po['po_code']) ?></span>
</div>

<!-- Header -->
<div class="flex items-start justify-between mb-5 gap-4 flex-wrap">
    <div>
        <h1 class="text-xl font-bold text-gray-800">Purchase Order — <?= esc($po['po_code']) ?></h1>
        <div class="flex gap-2 mt-1.5">
            <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold <?= $ps['color'] ?>">
                <?= $ps['label'] ?>
            </span>
            <?php if ($po['vendor_name']): ?>
            <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-700">
                🏢 <?= esc($po['vendor_name']) ?>
            </span>
            <?php endif; ?>
        </div>
    </div>
    <div class="flex gap-2 flex-wrap">
        <?php if ($canRegister): ?>
        <form action="<?= base_url('admin/procurement/po/'.$po['id'].'/register') ?>" method="POST" class="inline">
            <?= csrf_field() ?>
            <button type="submit" onclick="return confirm('Register semua item ke Inventory?')"
                    class="inline-flex items-center gap-1.5 bg-purple-600 hover:bg-purple-700 text-white text-sm px-3 py-2 rounded-lg">
                🗃️ Register ke Inventory
            </button>
        </form>
        <?php endif; ?>
    </div>
</div>

<?php if ($msg = session()->getFlashdata('success')): ?>
<div class="mb-4 px-4 py-3 bg-green-100 text-green-800 rounded-xl text-sm">✅ <?= $msg ?></div>
<?php elseif ($msg = session()->getFlashdata('error')): ?>
<div class="mb-4 px-4 py-3 bg-red-100 text-red-800 rounded-xl text-sm">❌ <?= $msg ?></div>
<?php endif; ?>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

    <!-- Kolom Kiri: Items -->
    <div class="lg:col-span-2 space-y-5">

        <!-- Info PO -->
        <div class="bg-white border rounded-xl shadow-sm overflow-hidden">
            <div class="px-4 py-3 bg-gray-50 border-b"><h2 class="text-sm font-bold text-gray-700">📄 Informasi PO</h2></div>
            <div class="p-4 grid grid-cols-2 sm:grid-cols-3 gap-4 text-sm">
                <div><p class="text-xs text-gray-400 mb-0.5">Vendor</p><p class="font-semibold text-gray-800"><?= esc($po['vendor_name'] ?? '—') ?></p><p class="text-xs text-gray-400"><?= esc($po['vendor_phone'] ?? '') ?></p></div>
                <div><p class="text-xs text-gray-400 mb-0.5">Tanggal PO</p><p class="font-semibold text-gray-800"><?= $po['po_date'] ? date('d M Y', strtotime($po['po_date'])) : '—' ?></p></div>
                <div><p class="text-xs text-gray-400 mb-0.5">Est. Tanggal Datang</p><p class="font-semibold text-gray-800"><?= $po['expected_date'] ? date('d M Y', strtotime($po['expected_date'])) : '—' ?></p></div>
                <?php if ($po['received_date']): ?>
                <div><p class="text-xs text-gray-400 mb-0.5">Tanggal Diterima</p><p class="font-semibold text-green-700"><?= date('d M Y', strtotime($po['received_date'])) ?></p></div>
                <?php endif; ?>
                <?php if ($po['lead_time_days']): ?>
                <div><p class="text-xs text-gray-400 mb-0.5">Lead Time</p><p class="font-semibold text-gray-800"><?= $po['lead_time_days'] ?> hari</p></div>
                <?php endif; ?>
                <div><p class="text-xs text-gray-400 mb-0.5">Dibuat oleh</p><p class="font-semibold text-gray-800"><?= esc($po['created_by_name'] ?? '—') ?></p></div>
            </div>
            <?php if ($po['notes']): ?>
            <div class="px-4 pb-4"><div class="bg-gray-50 rounded-lg p-3"><p class="text-xs text-gray-400 mb-1">Catatan</p><p class="text-sm text-gray-700"><?= esc($po['notes']) ?></p></div></div>
            <?php endif; ?>
        </div>

        <!-- Tabel Item -->
        <div class="bg-white border rounded-xl shadow-sm overflow-hidden">
            <div class="px-4 py-3 bg-gray-50 border-b flex items-center justify-between">
                <h2 class="text-sm font-bold text-gray-700">📦 Item Pembelian</h2>
                <span class="text-xs text-gray-400"><?= count($items) ?> item</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50 text-xs text-gray-500 uppercase">
                            <th class="px-3 py-2 text-left">Deskripsi</th>
                            <th class="px-3 py-2 text-right">Qty Order</th>
                            <th class="px-3 py-2 text-right">Qty Diterima</th>
                            <th class="px-3 py-2 text-right">Harga/Unit</th>
                            <th class="px-3 py-2 text-right">Total</th>
                            <th class="px-3 py-2 text-center">Asset</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        <?php foreach ($items as $item):
                            $isComplete = (int)$item['qty_received'] >= (int)$item['quantity'];
                        ?>
                        <tr class="<?= $isComplete ? 'bg-green-50/30' : '' ?>">
                            <td class="px-3 py-2.5">
                                <div class="font-medium text-gray-800"><?= esc($item['description']) ?></div>
                                <?php if ($item['category']): ?>
                                <div class="text-xs text-gray-400"><?= esc($item['category']) ?></div>
                                <?php endif; ?>
                            </td>
                            <td class="px-3 py-2.5 text-right"><?= number_format($item['quantity']) ?> <?= esc($item['unit']) ?></td>
                            <td class="px-3 py-2.5 text-right <?= $isComplete ? 'text-green-700 font-semibold' : 'text-orange-600' ?>">
                                <?= number_format($item['qty_received']) ?>
                                <?= $isComplete ? ' ✓' : '' ?>
                            </td>
                            <td class="px-3 py-2.5 text-right text-gray-700"><?= $rp($item['unit_price']) ?></td>
                            <td class="px-3 py-2.5 text-right font-semibold text-gray-800"><?= $rp($item['total_price']) ?></td>
                            <td class="px-3 py-2.5 text-center">
                                <?php if ($item['asset_id']): ?>
                                <a href="<?= base_url('admin/inventory/'.$item['asset_id']) ?>"
                                   class="text-xs text-purple-600 hover:underline">🗃️ Terdaftar</a>
                                <?php else: ?>
                                <span class="text-xs text-gray-400">—</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr class="bg-gray-50 border-t-2 border-gray-200">
                            <td colspan="3" class="px-3 py-2 text-right text-xs text-gray-500">Subtotal</td>
                            <td colspan="2" class="px-3 py-2 text-right font-semibold text-gray-800"><?= $rp($po['subtotal']) ?></td>
                            <td></td>
                        </tr>
                        <?php if ($po['tax'] > 0): ?>
                        <tr><td colspan="3" class="px-3 py-1.5 text-right text-xs text-gray-500">PPN</td><td colspan="2" class="px-3 py-1.5 text-right text-sm text-gray-700"><?= $rp($po['tax']) ?></td><td></td></tr>
                        <?php endif; ?>
                        <?php if ($po['shipping'] > 0): ?>
                        <tr><td colspan="3" class="px-3 py-1.5 text-right text-xs text-gray-500">Ongkir</td><td colspan="2" class="px-3 py-1.5 text-right text-sm text-gray-700"><?= $rp($po['shipping']) ?></td><td></td></tr>
                        <?php endif; ?>
                        <tr class="border-t-2">
                            <td colspan="3" class="px-3 py-2 text-right font-bold text-gray-800">TOTAL</td>
                            <td colspan="2" class="px-3 py-2 text-right font-bold text-blue-700 text-base"><?= $rp($po['total']) ?></td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <!-- Kolom Kanan -->
    <div class="space-y-4">

        <!-- Panel Terima Barang -->
        <?php if ($canReceive): ?>
        <div class="bg-white border-2 border-blue-300 rounded-xl shadow-sm overflow-hidden">
            <div class="px-4 py-3 bg-blue-50 border-b">
                <h3 class="text-sm font-bold text-blue-700">📦 Catat Penerimaan Barang</h3>
            </div>
            <div class="p-4">
                <form action="<?= base_url('admin/procurement/po/'.$po['id'].'/receive') ?>" method="POST">
                    <?= csrf_field() ?>
                    <div class="mb-3">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Terima</label>
                        <input type="date" name="received_date" value="<?= date('Y-m-d') ?>"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none">
                    </div>
                    <div class="space-y-2 mb-3">
                        <?php foreach ($items as $item): ?>
                        <div class="flex items-center justify-between gap-2">
                            <span class="text-xs text-gray-600 truncate flex-1"><?= esc(mb_strimwidth($item['description'], 0, 30, '...')) ?></span>
                            <input type="number" name="received_qty[<?= $item['id'] ?>]"
                                   value="<?= max(0, (int)$item['quantity'] - (int)$item['qty_received']) ?>"
                                   min="0" max="<?= (int)$item['quantity'] - (int)$item['qty_received'] ?>"
                                   class="w-16 border border-gray-300 rounded px-2 py-1 text-sm text-right focus:ring-1 focus:ring-blue-400 focus:outline-none">
                            <span class="text-xs text-gray-400 shrink-0"><?= esc($item['unit']) ?></span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <button type="submit"
                            onclick="return confirm('Catat penerimaan barang ini?')"
                            class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 rounded-xl text-sm">
                        ✅ Catat Penerimaan
                    </button>
                </form>
            </div>
        </div>
        <?php endif; ?>

        <!-- Info Register -->
        <?php if ($canRegister): ?>
        <div class="bg-purple-50 border border-purple-200 rounded-xl p-4">
            <p class="text-sm font-semibold text-purple-700 mb-1">🗃️ Siap Register ke Inventory</p>
            <p class="text-xs text-gray-600">Semua barang sudah diterima. Klik tombol <strong>Register ke Inventory</strong> di atas untuk otomatis membuat data aset baru.</p>
        </div>
        <?php endif; ?>

        <!-- Status Badge -->
        <div class="bg-white border rounded-xl p-4 shadow-sm space-y-2 text-xs text-gray-500">
            <div class="flex justify-between"><span>PO Dibuat</span><span class="font-medium text-gray-700"><?= date('d M Y', strtotime($po['created_at'])) ?></span></div>
            <div class="flex justify-between"><span>Terakhir Update</span><span class="font-medium text-gray-700"><?= date('d M Y H:i', strtotime($po['updated_at'])) ?></span></div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
