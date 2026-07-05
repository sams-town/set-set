<?= $this->extend('layouts/auth') ?>
<?= $this->section('content') ?>

<div class="card shadow-sm border-0">
    <div class="card-body p-4">
        <!-- Brand -->
        <div class="text-center mb-4 d-flex flex-column align-items-center">
            <img src="<?= base_url('uploads/assets/logo.jpg') ?>" alt="Logo RS Taman Harapan Baru" class="img-fluid mb-2" style="max-height: 110px;">
            <p class="text-muted small mb-0">Sistem Manajemen Aset</p>
        </div>

        <!-- Flash error -->
        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger py-2 small">
                <i class="bi bi-exclamation-triangle me-1"></i>
                <?= session()->getFlashdata('error') ?>
            </div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('errors')): ?>
            <div class="alert alert-danger py-2 small">
                <?php foreach (session()->getFlashdata('errors') as $e): ?>
                    <div><i class="bi bi-dot"></i><?= esc($e) ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('success')): ?>
            <div class="alert alert-success py-2 small">
                <i class="bi bi-check-circle me-1"></i>
                <?= session()->getFlashdata('success') ?>
            </div>
        <?php endif; ?>

        <form action="<?= base_url('login') ?>" method="POST">
            <?= csrf_field() ?>

            <div class="mb-3">
                <label for="email" class="form-label fw-semibold">Email</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                    <input type="email" class="form-control" id="email" name="email"
                           value="<?= old('email') ?>" placeholder="admin@example.com" required autofocus>
                </div>
            </div>

            <div class="mb-4">
                <label for="password" class="form-label fw-semibold">Password</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-lock"></i></span>
                    <input type="password" class="form-control" id="password" name="password"
                           placeholder="••••••••" required>
                    <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                        <i class="bi bi-eye" id="eyeIcon"></i>
                    </button>
                </div>
            </div>

            <button type="submit" class="btn btn-primary w-100 fw-semibold">
                <i class="bi bi-box-arrow-in-right me-1"></i> Masuk
            </button>
        </form>
    </div>
</div>

<script>
document.getElementById('togglePassword').addEventListener('click', function () {
    const pwd  = document.getElementById('password');
    const icon = document.getElementById('eyeIcon');
    if (pwd.type === 'password') {
        pwd.type  = 'text';
        icon.className = 'bi bi-eye-slash';
    } else {
        pwd.type  = 'password';
        icon.className = 'bi bi-eye';
    }
});
</script>

<?= $this->endSection() ?>
