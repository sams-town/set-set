<?= $this->extend('inventory/_layout') ?>
<?= $this->section('content') ?>
<?php $isEdit = !empty($req); $v = fn($k,$d='') => old($k, $req[$k] ?? $d); ?>

<div class="flex items-center gap-2 text-sm text-gray-500 mb-4">
    <a href="<?= base_url('admin/procurement') ?>" class="hover:text-blue-600">Procurement</a>
    <span>›</span>
    <span class="font-medium text-gray-800"><?= $isEdit ? 'Edit '.$req['request_code'] : 'Permintaan Baru' ?></span>
</div>

<div class="max-w-2xl">
<h1 class="text-xl font-bold text-gray-800 mb-5"><?= $isEdit ? '✏️ Edit Permintaan' : '📝 Permintaan Aset Baru' ?></h1>

<?php if ($errors = session()->getFlashdata('errors')): ?>
<div class="mb-4 px-4 py-3 bg-red-50 border border-red-200 rounded-xl text-sm text-red-700">
    <ul class="list-disc list-inside"><?php foreach((array)$errors as $e): ?><li><?= esc($e) ?></li><?php endforeach; ?></ul>
</div>
<?php endif; ?>

<form action="<?= $isEdit ? base_url('admin/procurement/'.$req['id'].'/update') : base_url('admin/procurement') ?>"
      method="POST" enctype="multipart/form-data" class="space-y-5">
<?= csrf_field() ?>

<div class="bg-white border rounded-xl p-5 shadow-sm space-y-4">
    <h2 class="text-sm font-bold text-gray-700 uppercase tracking-wide pb-2 border-b flex items-center gap-2">
        <span class="text-blue-600">📋</span> Detail Permintaan
    </h2>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Nama / Judul Item <span class="text-red-500">*</span></label>
        <input type="text" name="title" required value="<?= $v('title') ?>"
               placeholder="Contoh: Laptop Dell Latitude 5520 untuk Divisi IT"
               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none">
    </div>

    <div class="grid grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Kategori</label>
            <select name="category" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none">
                <option value="">-- Pilih --</option>
                <?php foreach ($categories as $cat): ?>
                <option value="<?= esc($cat) ?>" <?= $v('category') === $cat ? 'selected' : '' ?>><?= esc($cat) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Departemen</label>
            <select name="department_id" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none">
                <option value="">-- Pilih --</option>
                <?php foreach ($departments as $did => $dn): ?>
                <option value="<?= $did ?>" <?= $v('department_id') == $did ? 'selected' : '' ?>><?= esc($dn) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <div class="grid grid-cols-3 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Jumlah <span class="text-red-500">*</span></label>
            <input type="number" name="quantity" required min="1" id="qty"
                   value="<?= $v('quantity', 1) ?>" oninput="calcTotal()"
                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Satuan</label>
            <select name="unit" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none">
                <?php foreach (['unit','buah','set','pcs','lembar','meter','kg','liter'] as $u): ?>
                <option value="<?= $u ?>" <?= $v('unit','unit') === $u ? 'selected' : '' ?>><?= ucfirst($u) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Tingkat Urgensi</label>
            <select name="urgency" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none">
                <?php foreach ($urgency_opts as $key => $u): ?>
                <option value="<?= $key ?>" <?= $v('urgency','normal') === $key ? 'selected' : '' ?>><?= $u['label'] ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <div class="grid grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Estimasi Harga / Unit (Rp)</label>
            <input type="number" name="estimated_price" min="0" step="1000" id="price"
                   value="<?= $v('estimated_price') ?>" oninput="calcTotal()"
                   placeholder="0"
                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Total Estimasi (Otomatis)</label>
            <input type="text" id="totalDisplay" readonly
                   class="w-full border border-gray-200 bg-gray-50 rounded-lg px-3 py-2 text-sm text-gray-600 cursor-not-allowed font-semibold">
        </div>
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Target Tanggal Kebutuhan</label>
        <input type="date" name="target_date" value="<?= $v('target_date') ?>"
               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none">
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Deskripsi / Spesifikasi</label>
        <textarea name="description" rows="3" placeholder="Jelaskan spesifikasi, alasan kebutuhan, atau referensi barang..."
                  class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none resize-none"><?= $v('description') ?></textarea>
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Foto Referensi Barang</label>
        <?php if ($isEdit && !empty($req['photo'])): ?>
        <img src="<?= base_url('uploads/procurement/'.$req['photo']) ?>"
             class="w-24 h-24 object-cover rounded-xl border shadow-sm mb-2" alt="">
        <?php endif; ?>
        <input type="file" name="photo" accept="image/*"
               class="w-full text-sm text-gray-600 file:mr-3 file:py-1.5 file:px-4 file:rounded-lg
                      file:border-0 file:text-sm file:font-medium file:bg-blue-50 file:text-blue-700
                      hover:file:bg-blue-100 cursor-pointer">
        <p class="text-xs text-gray-400 mt-1">Opsional. Maks 5 MB.</p>
    </div>
</div>

<div class="flex gap-3">
    <button type="submit"
            class="<?= $isEdit ? 'bg-yellow-500 hover:bg-yellow-600' : 'bg-blue-600 hover:bg-blue-700' ?> text-white font-semibold px-6 py-2.5 rounded-xl text-sm">
        <?= $isEdit ? '💾 Perbarui' : '✅ Simpan Draft' ?>
    </button>
    <a href="<?= $isEdit ? base_url('admin/procurement/'.$req['id']) : base_url('admin/procurement') ?>"
       class="border border-gray-300 text-gray-700 hover:bg-gray-50 px-5 py-2.5 rounded-xl text-sm">Batal</a>
</div>
</form>
</div>

<script>
function calcTotal() {
    const q = parseFloat(document.getElementById('qty').value) || 0;
    const p = parseFloat(document.getElementById('price').value) || 0;
    document.getElementById('totalDisplay').value = q && p
        ? 'Rp ' + (q * p).toLocaleString('id-ID') : '—';
}
document.addEventListener('DOMContentLoaded', calcTotal);
</script>

<?= $this->endSection() ?>
