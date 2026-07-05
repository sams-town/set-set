<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="d-flex align-items-center justify-content-between mb-3">
    <h5 class="fw-bold mb-0"><i class="bi bi-tags me-2 text-primary"></i>Kategori Aset</h5>
    <a href="<?= base_url('admin/categories/new') ?>" class="btn btn-primary btn-sm">
        <i class="bi bi-plus-lg me-1"></i> Tambah Kategori
    </a>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Kode</th>
                        <th>Nama Kategori</th>
                        <th>Deskripsi</th>
                        <th class="text-center">Jumlah Aset</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($categories)): ?>
                    <tr><td colspan="6" class="text-center text-muted py-4">Belum ada kategori.</td></tr>
                    <?php else: ?>
                    <?php foreach ($categories as $i => $cat): ?>
                    <tr>
                        <td class="text-muted small"><?= $i + 1 ?></td>
                        <td><span class="badge bg-dark"><?= esc($cat['code']) ?></span></td>
                        <td class="fw-semibold"><?= esc($cat['name']) ?></td>
                        <td class="text-muted small"><?= esc($cat['description'] ?? '-') ?></td>
                        <td class="text-center">
                            <span class="badge bg-primary rounded-pill"><?= $cat['asset_count'] ?></span>
                        </td>
                        <td class="text-center">
                            <a href="<?= base_url('admin/categories/' . $cat['id'] . '/edit') ?>" class="btn btn-sm btn-outline-warning" title="Edit">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <button class="btn btn-sm btn-outline-danger" title="Hapus"
                                    onclick="confirmDelete('<?= base_url('admin/categories/' . $cat['id'] . '/delete') ?>', '<?= esc($cat['name']) ?>')">
                                <i class="bi bi-trash"></i>
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Hapus -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h6 class="modal-title text-danger"><i class="bi bi-trash me-2"></i>Hapus Kategori</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body py-2">Hapus kategori <strong id="deleteItemName"></strong>?</div>
            <div class="modal-footer border-0 pt-0">
                <button class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Batal</button>
                <form id="deleteForm" method="POST">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-sm btn-danger">Ya, Hapus</button>
                </form>
            </div>
        </div>
    </div>
</div>
<script>
function confirmDelete(url, name) {
    document.getElementById('deleteForm').action = url;
    document.getElementById('deleteItemName').textContent = name;
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}
</script>

<?= $this->endSection() ?>
