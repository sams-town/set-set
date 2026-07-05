<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="d-flex align-items-center gap-2 mb-3">
    <a href="<?= base_url('admin/categories') ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left"></i></a>
    <h5 class="fw-bold mb-0"><i class="bi bi-plus-circle me-2 text-primary"></i>Tambah Kategori</h5>
</div>

<div class="card border-0 shadow-sm" style="max-width:540px;">
    <div class="card-body">
        <form action="<?= base_url('admin/categories') ?>" method="POST">
            <?= csrf_field() ?>
            <div class="mb-3">
                <label class="form-label fw-semibold">Kode Kategori <span class="text-danger">*</span></label>
                <input type="text" class="form-control text-uppercase" name="code"
                       value="<?= old('code') ?>" placeholder="Contoh: KOM" maxlength="20" required>
                <div class="form-text">Kode unik max 20 karakter, otomatis huruf kapital.</div>
            </div>
            <div class="mb-3">
                <label class="form-label fw-semibold">Nama Kategori <span class="text-danger">*</span></label>
                <input type="text" class="form-control" name="name" value="<?= old('name') ?>" placeholder="Contoh: Komputer" required>
            </div>
            <div class="mb-3">
                <label class="form-label fw-semibold">Deskripsi</label>
                <textarea class="form-control" name="description" rows="3"><?= old('description') ?></textarea>
            </div>
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i>Simpan</button>
                <a href="<?= base_url('admin/categories') ?>" class="btn btn-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>

<?= $this->endSection() ?>
