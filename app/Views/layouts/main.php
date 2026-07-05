<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? 'Asset Management') ?> — SiAset</title>
    <!-- Bootstrap 5 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?= base_url('assets/css/app.css') ?>">
</head>
<body>

<!-- Sidebar -->
<div class="d-flex" id="wrapper">
    <nav id="sidebar" class="bg-dark text-white d-flex flex-column p-0" style="min-width:240px;min-height:100vh;">
        <!-- Brand -->
        <div class="sidebar-brand d-flex align-items-center px-3 py-3 border-bottom border-secondary">
            <i class="bi bi-box-seam-fill fs-4 me-2 text-warning"></i>
            <span class="fw-bold fs-5">SiAset</span>
        </div>

        <!-- Nav -->
        <ul class="nav flex-column mt-2 px-2 flex-grow-1">
            <li class="nav-item">
                <a href="<?= base_url('admin/dashboard') ?>" class="nav-link text-white <?= (uri_string() === 'admin/dashboard') ? 'active bg-primary rounded' : '' ?>">
                    <i class="bi bi-speedometer2 me-2"></i> Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a href="<?= base_url('admin/assets') ?>" class="nav-link text-white <?= str_starts_with(uri_string(), 'admin/assets') ? 'active bg-primary rounded' : '' ?>">
                    <i class="bi bi-box2 me-2"></i> Aset
                </a>
            </li>
            <li class="nav-item">
                <a href="<?= base_url('admin/borrows') ?>" class="nav-link text-white <?= str_starts_with(uri_string(), 'admin/borrows') ? 'active bg-primary rounded' : '' ?>">
                    <i class="bi bi-arrow-left-right me-2"></i> Peminjaman
                </a>
            </li>
            <li class="nav-item">
                <a href="<?= base_url('admin/categories') ?>" class="nav-link text-white <?= str_starts_with(uri_string(), 'admin/categories') ? 'active bg-primary rounded' : '' ?>">
                    <i class="bi bi-tags me-2"></i> Kategori
                </a>
            </li>
            <li class="nav-item">
                <a href="<?= base_url('admin/locations') ?>" class="nav-link text-white <?= str_starts_with(uri_string(), 'admin/locations') ? 'active bg-primary rounded' : '' ?>">
                    <i class="bi bi-geo-alt me-2"></i> Lokasi
                </a>
            </li>
            <li class="nav-item">
                <a href="<?= base_url('admin/reports') ?>" class="nav-link text-white <?= str_starts_with(uri_string(), 'admin/reports') ? 'active bg-primary rounded' : '' ?>">
                    <i class="bi bi-file-earmark-bar-graph me-2"></i> Laporan
                </a>
            </li>
            <?php if (session()->get('role') === 'admin'): ?>
            <li class="nav-item">
                <a href="<?= base_url('admin/users') ?>" class="nav-link text-white <?= str_starts_with(uri_string(), 'admin/users') ? 'active bg-primary rounded' : '' ?>">
                    <i class="bi bi-people me-2"></i> User
                </a>
            </li>
            <?php endif; ?>
        </ul>

        <!-- User info -->
        <div class="border-top border-secondary px-3 py-3 mt-auto">
            <div class="d-flex align-items-center gap-2 mb-2">
                <i class="bi bi-person-circle fs-4 text-secondary"></i>
                <div class="lh-sm">
                    <div class="fw-semibold small"><?= esc(session()->get('user_name')) ?></div>
                    <div class="text-muted" style="font-size:.75rem;"><?= esc(session()->get('role')) ?></div>
                </div>
            </div>
            <a href="<?= base_url('logout') ?>" class="btn btn-sm btn-outline-danger w-100">
                <i class="bi bi-box-arrow-right me-1"></i> Logout
            </a>
        </div>
    </nav>

    <!-- Page Content -->
    <div id="page-content" class="flex-grow-1 d-flex flex-column bg-light" style="min-height:100vh;">
        <!-- Topbar -->
        <header class="bg-white shadow-sm px-4 py-2 d-flex align-items-center justify-content-between sticky-top">
            <button class="btn btn-sm btn-light" id="sidebarToggle">
                <i class="bi bi-list fs-5"></i>
            </button>
            <span class="fw-semibold text-secondary small">
                <i class="bi bi-calendar3 me-1"></i>
                <?= date('l, d F Y') ?>
            </span>
        </header>

        <!-- Flash messages -->
        <div class="px-4 pt-3">
            <?php if (session()->getFlashdata('success')): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle me-2"></i><?= session()->getFlashdata('success') ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            <?php if (session()->getFlashdata('error')): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle me-2"></i><?= session()->getFlashdata('error') ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            <?php if (session()->getFlashdata('errors')): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    <ul class="mb-0 mt-1">
                        <?php foreach (session()->getFlashdata('errors') as $err): ?>
                            <li><?= esc($err) ?></li>
                        <?php endforeach; ?>
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
        </div>

        <!-- Main content -->
        <main class="px-4 py-3 flex-grow-1">
            <?= $this->renderSection('content') ?>
        </main>

        <footer class="text-center text-muted small py-3 border-top bg-white">
            &copy; <?= date('Y') ?> SiAset — Sistem Manajemen Aset
        </footer>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= base_url('assets/js/app.js') ?>"></script>
</body>
</html>
