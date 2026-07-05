<?= $this->extend('inventory/_layout') ?>
<?= $this->section('content') ?>
<?php
$rp  = fn($n) => 'Rp ' . number_format((float)$n, 0, ',', '.');
$sf  = $status_flow[$req['status']] ?? ['label'=>$req['status'],'icon'=>'•','color'=>'bg-gray-100 text-gray-600'];
$ug  = $urgency_opts[$req['urgency']] ?? ['label'=>$req['urgency'],'color'=>'bg-gray-100 text-gray-600'];
$uid = $current_user_id;
$role= $current_role;

// Tombol aksi berdasarkan status
$canSubmit  = in_array($req['status'], ['draft','rejected']);
$canApprove = ($req['status'] === 'pending_atasan') ||
              ($req['status'] === 'pending_direktur' && $role === 'admin');
$canReject  = in_array($req['status'], ['pending_atasan','pending_direktur']);
$canRfq     = $req['status'] === 'approved';
$canPo      = in_array($req['status'], ['approved','rfq']);
$canEdit    = in_array($req['status'], ['draft','rejected']);
?>

<!-- Breadcrumb -->
<div class="flex items-center gap-2 text-sm text-gray-500 mb-4">
    <a href="<?= base_url('admin/procurement') ?>" class="hover:text-blue-600">Procurement</a>
    <span>›</span>
    <span class="font-mono font-medium text-gray-800"><?= esc($req['request_code']) ?></span>
</div>

<!-- Header -->
<div class="flex items-start justify-between mb-5 gap-4 flex-wrap">
    <div>
        <h1 class="text-xl font-bold text-gray-800"><?= esc($req['title']) ?></h1>
        <div class="flex gap-2 mt-1.5 flex-wrap">
            <code class="text-sm text-gray-500 font-mono"><?= esc($req['request_code']) ?></code>
            <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold <?= $sf['color'] ?>">
                <?= $sf['icon'] ?> <?= $sf['label'] ?>
            </span>
            <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold <?= $ug['color'] ?>">
                <?= $ug['label'] ?>
            </span>
        </div>
    </div>
    <div class="flex gap-2 flex-wrap">
        <?php if ($canEdit): ?>
        <a href="<?= base_url('admin/procurement/'.$req['id'].'/edit') ?>"
           class="inline-flex items-center gap-1.5 bg-yellow-500 hover:bg-yellow-600 text-white text-sm px-3 py-2 rounded-lg">✏️ Edit</a>
        <?php endif; ?>
        <?php if ($canSubmit): ?>
        <form action="<?= base_url('admin/procurement/'.$req['id'].'/submit') ?>" method="POST" class="inline">
            <?= csrf_field() ?>
            <button type="submit"
                    onclick="return confirm('Submit permintaan ini untuk approval?')"
                    class="inline-flex items-center gap-1.5 bg-blue-600 hover:bg-blue-700 text-white text-sm px-3 py-2 rounded-lg">
                📤 Submit untuk Approval
            </button>
        </form>
        <?php endif; ?>
        <?php if ($canRfq): ?>
        <form action="<?= base_url('admin/procurement/'.$req['id'].'/set-rfq') ?>" method="POST" class="inline">
            <?= csrf_field() ?>
            <button type="submit" class="inline-flex items-center gap-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm px-3 py-2 rounded-lg">
                📋 Mulai RFQ
            </button>
        </form>
        <?php endif; ?>
        <?php if ($canPo): ?>
        <a href="<?= base_url('admin/procurement/'.$req['id'].'/po/new') ?>"
           class="inline-flex items-center gap-1.5 bg-green-600 hover:bg-green-700 text-white text-sm px-3 py-2 rounded-lg">
            🛒 Buat PO
        </a>
        <?php endif; ?>
    </div>
</div>

<!-- Alur Progress -->
<div class="bg-white border rounded-xl p-4 shadow-sm mb-5 overflow-x-auto">
    <?php
    $flowKeys = ['draft','pending_atasan','pending_direktur','approved','rfq','po','received','registered'];
    $curIdx   = array_search($req['status'], $flowKeys);
    if ($req['status'] === 'rejected')   { $curIdx = -1; }
    if ($req['status'] === 'cancelled')  { $curIdx = -1; }
    ?>
    <div class="flex items-center gap-1 min-w-max">
        <?php foreach ($flowKeys as $i => $key):
            $stepSf   = $status_flow[$key];
            $done     = $curIdx !== false && $i <= $curIdx;
            $current  = $curIdx !== false && $i === $curIdx;
            $last     = $i === count($flowKeys) - 1;
        ?>
        <div class="flex items-center gap-1 shrink-0">
            <div class="flex flex-col items-center">
                <div class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold transition-all
                            <?= $current ? 'bg-blue-600 text-white ring-4 ring-blue-200' : ($done ? 'bg-blue-500 text-white' : 'bg-gray-100 text-gray-400') ?>">
                    <?= $done && !$current ? '✓' : $stepSf['icon'] ?>
                </div>
                <span class="text-xs mt-1 <?= $done ? 'text-blue-700 font-semibold' : 'text-gray-400' ?> whitespace-nowrap">
                    <?= $stepSf['label'] ?>
                </span>
            </div>
            <?php if (!$last): ?>
            <div class="w-8 h-0.5 mb-4 <?= $curIdx !== false && $i < $curIdx ? 'bg-blue-400' : 'bg-gray-200' ?>"></div>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
        <?php if ($req['status'] === 'rejected'): ?>
        <div class="ml-3 px-3 py-1 bg-red-100 text-red-700 rounded-full text-xs font-semibold">❌ Ditolak</div>
        <?php endif; ?>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

    <!-- Kolom Kiri -->
    <div class="lg:col-span-2 space-y-5">

        <!-- Detail Permintaan -->
        <div class="bg-white border rounded-xl shadow-sm overflow-hidden">
            <div class="px-4 py-3 bg-gray-50 border-b"><h2 class="text-sm font-bold text-gray-700">📋 Detail Permintaan</h2></div>
            <div class="p-5">
                <div class="grid grid-cols-2 gap-4 text-sm mb-4">
                    <div><p class="text-xs text-gray-400 mb-0.5">Pemohon</p><p class="font-semibold text-gray-800"><?= esc($req['requested_by_name'] ?? '—') ?></p></div>
                    <div><p class="text-xs text-gray-400 mb-0.5">Departemen</p><p class="font-semibold text-gray-800"><?= esc($req['department_name'] ?? '—') ?></p></div>
                    <div><p class="text-xs text-gray-400 mb-0.5">Jumlah</p><p class="font-semibold text-gray-800"><?= number_format($req['quantity']) ?> <?= esc($req['unit']) ?></p></div>
                    <div><p class="text-xs text-gray-400 mb-0.5">Estimasi Total</p><p class="font-semibold text-gray-800"><?= $req['total_estimated'] ? $rp($req['total_estimated']) : '—' ?></p></div>
                    <div><p class="text-xs text-gray-400 mb-0.5">Target Kebutuhan</p><p class="font-semibold text-gray-800"><?= $req['target_date'] ? date('d M Y', strtotime($req['target_date'])) : '—' ?></p></div>
                    <div><p class="text-xs text-gray-400 mb-0.5">Kategori</p><p class="font-semibold text-gray-800"><?= esc($req['category'] ?? '—') ?></p></div>
                </div>
                <?php if ($req['description']): ?>
                <div class="bg-gray-50 rounded-lg p-3 mb-3">
                    <p class="text-xs text-gray-400 mb-1">Deskripsi / Spesifikasi</p>
                    <p class="text-sm text-gray-800 leading-relaxed"><?= nl2br(esc($req['description'])) ?></p>
                </div>
                <?php endif; ?>
                <?php if ($req['photo']): ?>
                <div><p class="text-xs text-gray-400 mb-1">Foto Referensi</p>
                    <img src="<?= base_url('uploads/procurement/'.$req['photo']) ?>"
                         class="w-40 h-40 object-cover rounded-xl border shadow-sm" alt="Foto">
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Trail Approval -->
        <?php if ($req['atasan_note'] || $req['direktur_note'] || $req['rejection_reason']): ?>
        <div class="bg-white border rounded-xl shadow-sm overflow-hidden">
            <div class="px-4 py-3 bg-gray-50 border-b"><h2 class="text-sm font-bold text-gray-700">💬 Catatan Approval</h2></div>
            <div class="p-4 space-y-3">
                <?php if ($req['atasan_note']): ?>
                <div class="bg-blue-50 rounded-lg p-3">
                    <p class="text-xs text-blue-600 font-medium mb-0.5">Catatan Atasan — <?= esc($req['atasan_name'] ?? '') ?></p>
                    <p class="text-sm text-gray-800"><?= esc($req['atasan_note']) ?></p>
                </div>
                <?php endif; ?>
                <?php if ($req['direktur_note']): ?>
                <div class="bg-green-50 rounded-lg p-3">
                    <p class="text-xs text-green-600 font-medium mb-0.5">Catatan Direktur — <?= esc($req['direktur_name'] ?? '') ?></p>
                    <p class="text-sm text-gray-800"><?= esc($req['direktur_note']) ?></p>
                </div>
                <?php endif; ?>
                <?php if ($req['rejection_reason']): ?>
                <div class="bg-red-50 rounded-lg p-3">
                    <p class="text-xs text-red-600 font-medium mb-0.5">Alasan Penolakan</p>
                    <p class="text-sm text-gray-800"><?= esc($req['rejection_reason']) ?></p>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Daftar PO -->
        <?php if (!empty($po_list)): ?>
        <div class="bg-white border rounded-xl shadow-sm overflow-hidden">
            <div class="px-4 py-3 bg-gray-50 border-b flex items-center justify-between">
                <h2 class="text-sm font-bold text-gray-700">🛒 Purchase Order Terkait</h2>
                <span class="text-xs text-gray-400"><?= count($po_list) ?> PO</span>
            </div>
            <div class="divide-y">
                <?php
                $poStatus = \App\Models\PurchaseOrderModel::STATUS;
                foreach ($po_list as $po):
                    $ps = $poStatus[$po['status']] ?? ['label'=>$po['status'],'color'=>'bg-gray-100 text-gray-600'];
                ?>
                <a href="<?= base_url('admin/procurement/po/'.$po['id']) ?>"
                   class="flex items-center justify-between px-4 py-3 hover:bg-gray-50">
                    <div>
                        <code class="font-mono text-blue-600 font-semibold text-sm"><?= esc($po['po_code']) ?></code>
                        <div class="text-xs text-gray-400 mt-0.5"><?= esc($po['vendor_name'] ?? '—') ?> · <?= $po['po_date'] ? date('d M Y', strtotime($po['po_date'])) : '—' ?></div>
                    </div>
                    <div class="text-right">
                        <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium <?= $ps['color'] ?>"><?= $ps['label'] ?></span>
                        <div class="text-xs text-gray-600 mt-0.5"><?= $rp($po['total']) ?></div>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Kolom Kanan -->
    <div class="space-y-4">

        <!-- Panel Approval -->
        <?php if ($canApprove): ?>
        <div class="bg-green-50 border-2 border-green-300 rounded-xl overflow-hidden">
            <div class="px-4 py-3 bg-green-100 border-b">
                <h3 class="text-sm font-bold text-green-700">
                    <?= $req['status'] === 'pending_atasan' ? '👤 Approval Atasan' : '🏆 Approval Direktur' ?>
                </h3>
            </div>
            <div class="p-4">
                <form action="<?= base_url('admin/procurement/'.$req['id'].'/approve') ?>" method="POST">
                    <?= csrf_field() ?>
                    <textarea name="note" rows="2" placeholder="Catatan (opsional)..."
                              class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none resize-none mb-3"></textarea>
                    <button type="submit"
                            onclick="return confirm('Setujui permintaan ini?')"
                            class="w-full bg-green-600 hover:bg-green-700 text-white font-semibold py-2 rounded-xl text-sm">
                        ✅ Setujui
                    </button>
                </form>
            </div>
        </div>
        <?php endif; ?>

        <!-- Panel Tolak -->
        <?php if ($canReject): ?>
        <div class="bg-white border rounded-xl overflow-hidden">
            <div class="px-4 py-3 bg-gray-50 border-b"><h3 class="text-sm font-bold text-gray-700">❌ Tolak Permintaan</h3></div>
            <div class="p-4">
                <form action="<?= base_url('admin/procurement/'.$req['id'].'/reject') ?>" method="POST">
                    <?= csrf_field() ?>
                    <textarea name="rejection_reason" rows="2" required placeholder="Alasan penolakan (wajib)..."
                              class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none resize-none mb-3"></textarea>
                    <button type="submit"
                            onclick="return confirm('Tolak permintaan ini?')"
                            class="w-full bg-red-600 hover:bg-red-700 text-white font-semibold py-2 rounded-xl text-sm">
                        ❌ Tolak
                    </button>
                </form>
            </div>
        </div>
        <?php endif; ?>

        <!-- Info Timestamps -->
        <div class="bg-white border rounded-xl p-4 shadow-sm space-y-2 text-xs text-gray-500">
            <div class="flex justify-between"><span>Dibuat</span><span class="font-medium text-gray-700"><?= date('d M Y H:i', strtotime($req['created_at'])) ?></span></div>
            <div class="flex justify-between"><span>Diperbarui</span><span class="font-medium text-gray-700"><?= date('d M Y H:i', strtotime($req['updated_at'])) ?></span></div>
            <?php if ($req['approved_at']): ?>
            <div class="flex justify-between"><span>Disetujui</span><span class="font-medium text-green-700"><?= date('d M Y H:i', strtotime($req['approved_at'])) ?></span></div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
