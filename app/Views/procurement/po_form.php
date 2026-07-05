<?= $this->extend('inventory/_layout') ?>
<?= $this->section('content') ?>
<?php $rp = fn($n) => 'Rp ' . number_format((float)$n, 0, ',', '.'); ?>

<div class="flex items-center gap-2 text-sm text-gray-500 mb-4">
    <a href="<?= base_url('admin/procurement') ?>" class="hover:text-blue-600">Procurement</a>
    <span>›</span>
    <a href="<?= base_url('admin/procurement/'.$req['id']) ?>" class="hover:text-blue-600"><?= esc($req['request_code']) ?></a>
    <span>›</span>
    <span class="font-medium text-gray-800">Buat Purchase Order</span>
</div>

<div class="max-w-4xl">
<h1 class="text-xl font-bold text-gray-800 mb-1">🛒 Buat Purchase Order</h1>
<p class="text-sm text-gray-500 mb-5">Berdasarkan permintaan: <strong><?= esc($req['title']) ?></strong></p>

<form action="<?= base_url('admin/procurement/'.$req['id'].'/po') ?>" method="POST" id="poForm" class="space-y-5">
<?= csrf_field() ?>

<!-- Info PO -->
<div class="bg-white border rounded-xl p-5 shadow-sm">
    <h2 class="text-sm font-bold text-gray-700 uppercase tracking-wide pb-2 border-b mb-4">📄 Informasi PO</h2>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Vendor / Supplier <span class="text-red-500">*</span></label>
            <select name="vendor_id" required
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none">
                <option value="">-- Pilih Vendor --</option>
                <?php foreach ($vendors as $vid => $vname): ?>
                <option value="<?= $vid ?>"><?= esc($vname) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal PO</label>
            <input type="date" name="po_date" value="<?= date('Y-m-d') ?>"
                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Estimasi Tanggal Datang</label>
            <input type="date" name="expected_date" id="expectedDate"
                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none">
            <p class="text-xs text-gray-400 mt-1">Untuk hitung lead time otomatis</p>
        </div>
    </div>
</div>

<!-- Item PO (dynamic rows) -->
<div class="bg-white border rounded-xl p-5 shadow-sm">
    <div class="flex items-center justify-between pb-2 border-b mb-4">
        <h2 class="text-sm font-bold text-gray-700 uppercase tracking-wide">📦 Item Pembelian</h2>
        <button type="button" onclick="addRow()"
                class="bg-blue-50 hover:bg-blue-100 text-blue-700 text-xs font-medium px-3 py-1.5 rounded-lg">
            + Tambah Item
        </button>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-sm" id="itemTable">
            <thead>
                <tr class="bg-gray-50 text-xs text-gray-500 uppercase">
                    <th class="px-2 py-2 text-left min-w-[200px]">Deskripsi Item</th>
                    <th class="px-2 py-2 text-left min-w-[130px]">Kategori</th>
                    <th class="px-2 py-2 text-right w-20">Qty</th>
                    <th class="px-2 py-2 text-left w-20">Satuan</th>
                    <th class="px-2 py-2 text-right w-32">Harga / Unit</th>
                    <th class="px-2 py-2 text-right w-32">Total</th>
                    <th class="px-2 py-2 w-10"></th>
                </tr>
            </thead>
            <tbody id="itemBody">
                <!-- Row default pre-filled dari request -->
                <tr class="item-row border-t">
                    <td class="px-2 py-1.5"><input type="text" name="item_desc[]" value="<?= esc($req['title']) ?>" required placeholder="Nama/deskripsi item" class="w-full border border-gray-300 rounded-lg px-2 py-1 text-sm focus:ring-1 focus:ring-blue-400 focus:outline-none"></td>
                    <td class="px-2 py-1.5">
                        <select name="item_category[]" class="w-full border border-gray-300 rounded-lg px-2 py-1 text-sm focus:outline-none">
                            <option value="">—</option>
                            <?php foreach ($categories as $cat): ?>
                            <option value="<?= esc($cat) ?>" <?= ($req['category'] ?? '') === $cat ? 'selected' : '' ?>><?= esc($cat) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                    <td class="px-2 py-1.5"><input type="number" name="item_qty[]" value="<?= $req['quantity'] ?>" min="1" class="w-full border border-gray-300 rounded-lg px-2 py-1 text-sm text-right focus:ring-1 focus:ring-blue-400 focus:outline-none row-qty" oninput="recalcRow(this)"></td>
                    <td class="px-2 py-1.5"><select name="item_unit[]" class="w-full border border-gray-300 rounded-lg px-1 py-1 text-sm focus:outline-none"><option>unit</option><option>buah</option><option>set</option><option>pcs</option></select></td>
                    <td class="px-2 py-1.5"><input type="number" name="item_price[]" value="<?= $req['estimated_price'] ?? 0 ?>" min="0" step="100" class="w-full border border-gray-300 rounded-lg px-2 py-1 text-sm text-right focus:ring-1 focus:ring-blue-400 focus:outline-none row-price" oninput="recalcRow(this)"></td>
                    <td class="px-2 py-1.5 text-right text-sm font-semibold text-gray-700 row-total">—</td>
                    <td class="px-2 py-1.5 text-center"><button type="button" onclick="removeRow(this)" class="text-red-400 hover:text-red-600 text-sm">🗑</button></td>
                </tr>
            </tbody>
            <tfoot>
                <tr class="border-t-2 border-gray-200 bg-gray-50">
                    <td colspan="5" class="px-2 py-2 text-right text-sm font-semibold text-gray-700">Subtotal</td>
                    <td class="px-2 py-2 text-right text-sm font-bold text-gray-800" id="subtotalDisplay">—</td>
                    <td></td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

<!-- Biaya Tambahan & Catatan -->
<div class="bg-white border rounded-xl p-5 shadow-sm">
    <h2 class="text-sm font-bold text-gray-700 uppercase tracking-wide pb-2 border-b mb-4">💰 Biaya Tambahan</h2>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">PPN / Pajak (Rp)</label>
            <input type="number" name="tax" min="0" step="1000" value="0" id="taxInput" oninput="recalcTotal()"
                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Ongkos Kirim (Rp)</label>
            <input type="number" name="shipping" min="0" step="1000" value="0" id="shippingInput" oninput="recalcTotal()"
                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Total Akhir (Otomatis)</label>
            <input type="text" id="grandTotalDisplay" readonly
                   class="w-full border border-gray-200 bg-gray-50 rounded-lg px-3 py-2 text-sm text-gray-700 cursor-not-allowed font-bold">
        </div>
    </div>
    <div class="grid grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Catatan</label>
            <textarea name="notes" rows="2" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none resize-none"></textarea>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Syarat & Ketentuan</label>
            <textarea name="terms" rows="2" placeholder="Misal: Pembayaran 30 hari setelah barang diterima"
                      class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none resize-none"></textarea>
        </div>
    </div>
</div>

<div class="flex gap-3">
    <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-semibold px-6 py-2.5 rounded-xl text-sm">
        ✅ Buat Purchase Order
    </button>
    <a href="<?= base_url('admin/procurement/'.$req['id']) ?>"
       class="border border-gray-300 text-gray-700 hover:bg-gray-50 px-5 py-2.5 rounded-xl text-sm">Batal</a>
</div>
</form>
</div>

<script>
// Template row baru
const categoryOptions = `<?php foreach ($categories as $cat): ?><option value="<?= esc($cat) ?>"><?= esc($cat) ?></option><?php endforeach; ?>`;
const unitOptions = `<option>unit</option><option>buah</option><option>set</option><option>pcs</option>`;

function addRow() {
    const tbody = document.getElementById('itemBody');
    const tr = document.createElement('tr');
    tr.className = 'item-row border-t';
    tr.innerHTML = `
        <td class="px-2 py-1.5"><input type="text" name="item_desc[]" required placeholder="Deskripsi item" class="w-full border border-gray-300 rounded-lg px-2 py-1 text-sm focus:ring-1 focus:ring-blue-400 focus:outline-none"></td>
        <td class="px-2 py-1.5"><select name="item_category[]" class="w-full border border-gray-300 rounded-lg px-2 py-1 text-sm focus:outline-none"><option value="">—</option>${categoryOptions}</select></td>
        <td class="px-2 py-1.5"><input type="number" name="item_qty[]" value="1" min="1" class="w-full border border-gray-300 rounded-lg px-2 py-1 text-sm text-right focus:ring-1 focus:ring-blue-400 focus:outline-none row-qty" oninput="recalcRow(this)"></td>
        <td class="px-2 py-1.5"><select name="item_unit[]" class="w-full border border-gray-300 rounded-lg px-1 py-1 text-sm focus:outline-none">${unitOptions}</select></td>
        <td class="px-2 py-1.5"><input type="number" name="item_price[]" value="0" min="0" step="100" class="w-full border border-gray-300 rounded-lg px-2 py-1 text-sm text-right focus:ring-1 focus:ring-blue-400 focus:outline-none row-price" oninput="recalcRow(this)"></td>
        <td class="px-2 py-1.5 text-right text-sm font-semibold text-gray-700 row-total">—</td>
        <td class="px-2 py-1.5 text-center"><button type="button" onclick="removeRow(this)" class="text-red-400 hover:text-red-600 text-sm">🗑</button></td>
    `;
    tbody.appendChild(tr);
    recalcTotal();
}

function removeRow(btn) {
    if (document.querySelectorAll('.item-row').length <= 1) return;
    btn.closest('tr').remove();
    recalcTotal();
}

function recalcRow(input) {
    const row   = input.closest('tr');
    const qty   = parseFloat(row.querySelector('.row-qty').value) || 0;
    const price = parseFloat(row.querySelector('.row-price').value) || 0;
    const total = qty * price;
    row.querySelector('.row-total').textContent = total > 0 ? 'Rp ' + total.toLocaleString('id-ID') : '—';
    recalcTotal();
}

function recalcTotal() {
    let subtotal = 0;
    document.querySelectorAll('.item-row').forEach(row => {
        const qty   = parseFloat(row.querySelector('.row-qty').value) || 0;
        const price = parseFloat(row.querySelector('.row-price').value) || 0;
        subtotal   += qty * price;
    });
    const tax      = parseFloat(document.getElementById('taxInput').value) || 0;
    const shipping = parseFloat(document.getElementById('shippingInput').value) || 0;
    const grand    = subtotal + tax + shipping;

    document.getElementById('subtotalDisplay').textContent  = 'Rp ' + subtotal.toLocaleString('id-ID');
    document.getElementById('grandTotalDisplay').value = 'Rp ' + grand.toLocaleString('id-ID');
}

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.row-qty, .row-price').forEach(el => el.dispatchEvent(new Event('input')));
});
</script>

<?= $this->endSection() ?>
