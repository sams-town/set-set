<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="d-flex align-items-center gap-2 mb-3">
    <a href="<?= base_url('admin/categories') ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left"></i></a>
    <h5 class="fw-bold mb-0"><i class="bi bi-pencil me-2 text-warning"></i>Edit Kategori</h5>
</div>

<div class="card border-0 shadow-sm" style="max-width:540px;">
    <div class="card-body">
        <form action="<?= base_url('admin/categories/' . $category['id'] . '/update') ?>" method="POST">
            <?= csrf_field() ?>
            <div class="mb-3">
                <label class="form-label fw-semibold">Kode Kategori <span class="text-danger">*</span></label>
                <input type="text" class="form-control text-uppercase" name="code"
                       value="<?= old('code', $category['code']) ?>" maxlength="20" required>
            </div>
            <div class="mb-3">
                <label class="form-label fw-semibold">Nama Kategori <span class="text-danger">*</span></label>
                <input type="text" class="form-control" name="name" value="<?= old('name', $category['name']) ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label fw-semibold">Deskripsi</label>
                <textarea class="form-control" name="description" rows="3"><?= old('description', $category['description']) ?></textarea>
            </div>
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-warning"><i class="bi bi-save me-1"></i>Perbarui</button>
                <a href="<?= base_url('admin/categories') ?>" class="btn btn-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>

<?= $this->endSection() ?>
