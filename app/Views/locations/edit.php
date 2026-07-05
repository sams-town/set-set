<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="d-flex align-items-center gap-2 mb-3">
    <a href="<?= base_url('admin/locations') ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left"></i></a>
    <h5 class="fw-bold mb-0"><i class="bi bi-pencil me-2 text-warning"></i>Edit Lokasi</h5>
</div>

<div class="card border-0 shadow-sm" style="max-width:540px;">
    <div class="card-body">
        <form action="<?= base_url('admin/locations/' . $location['id'] . '/update') ?>" method="POST">
            <?= csrf_field() ?>
            <div class="mb-3">
                <label class="form-label fw-semibold">Nama Lokasi <span class="text-danger">*</span></label>
                <input type="text" class="form-control" name="name" value="<?= old('name', $location['name']) ?>" required>
            </div>
            <div class="row g-3 mb-3">
                <div class="col">
                    <label class="form-label fw-semibold">Gedung</label>
                    <input type="text" class="form-control" name="building" value="<?= old('building', $location['building']) ?>">
                </div>
                <div class="col">
                    <label class="form-label fw-semibold">Lantai</label>
                    <input type="text" class="form-control" name="floor" value="<?= old('floor', $location['floor']) ?>">
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label fw-semibold">Deskripsi</label>
                <textarea class="form-control" name="description" rows="3"><?= old('description', $location['description']) ?></textarea>
            </div>
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-warning"><i class="bi bi-save me-1"></i>Perbarui</button>
                <a href="<?= base_url('admin/locations') ?>" class="btn btn-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>

<?= $this->endSection() ?>
