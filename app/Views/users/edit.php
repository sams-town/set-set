<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="d-flex align-items-center gap-2 mb-3">
    <a href="<?= base_url('admin/users') ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left"></i></a>
    <h5 class="fw-bold mb-0"><i class="bi bi-pencil me-2 text-warning"></i>Edit User</h5>
</div>

<div class="card border-0 shadow-sm" style="max-width:540px;">
    <div class="card-body">
        <form action="<?= base_url('admin/users/' . $user['id'] . '/update') ?>" method="POST">
            <?= csrf_field() ?>
            <div class="mb-3">
                <label class="form-label fw-semibold">Nama Lengkap <span class="text-danger">*</span></label>
                <input type="text" class="form-control" name="name" value="<?= old('name', $user['name']) ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label fw-semibold">Email <span class="text-danger">*</span></label>
                <input type="email" class="form-control" name="email" value="<?= old('email', $user['email']) ?>" required>
            </div>

            <hr class="my-2">
            <p class="text-muted small mb-2"><i class="bi bi-info-circle me-1"></i>Kosongkan password jika tidak ingin menggantinya.</p>

            <div class="mb-3">
                <label class="form-label fw-semibold">Password Baru</label>
                <input type="password" class="form-control" name="password" minlength="6">
            </div>
            <div class="mb-3">
                <label class="form-label fw-semibold">Konfirmasi Password Baru</label>
                <input type="password" class="form-control" name="password_confirm">
            </div>

            <div class="row g-3 mb-3">
                <div class="col">
                    <label class="form-label fw-semibold">Role</label>
                    <select class="form-select" name="role">
                        <option value="user" <?= old('role', $user['role']) === 'user' ? 'selected' : '' ?>>User</option>
                        <option value="admin" <?= old('role', $user['role']) === 'admin' ? 'selected' : '' ?>>Admin</option>
                    </select>
                </div>
                <div class="col">
                    <label class="form-label fw-semibold">Status Akun</label>
                    <select class="form-select" name="is_active">
                        <option value="1" <?= old('is_active', $user['is_active']) ? 'selected' : '' ?>>Aktif</option>
                        <option value="0" <?= !old('is_active', $user['is_active']) ? 'selected' : '' ?>>Nonaktif</option>
                    </select>
                </div>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-warning"><i class="bi bi-save me-1"></i>Perbarui</button>
                <a href="<?= base_url('admin/users') ?>" class="btn btn-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>

<?= $this->endSection() ?>
