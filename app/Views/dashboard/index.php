<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="d-flex align-items-center justify-content-between mb-4">
    <h5 class="fw-bold mb-0"><i class="bi bi-speedometer2 me-2 text-primary"></i>Dashboard</h5>
    <span class="text-muted small">Selamat datang, <strong><?= esc(session()->get('user_name')) ?></strong></span>
</div>

<!-- Stats Cards -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-circle bg-primary bg-opacity-10 p-3">
                    <i class="bi bi-box2 fs-4 text-primary"></i>
                </div>
                <div>
                    <div class="fs-2 fw-bold"><?= $stats['total'] ?></div>
                    <div class="text-muted small">Total Aset</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-circle bg-success bg-opacity-10 p-3">
                    <i class="bi bi-check-circle fs-4 text-success"></i>
                </div>
                <div>
                    <div class="fs-2 fw-bold"><?= $stats['tersedia'] ?></div>
                    <div class="text-muted small">Tersedia</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-circle bg-warning bg-opacity-10 p-3">
                    <i class="bi bi-arrow-left-right fs-4 text-warning"></i>
                </div>
                <div>
                    <div class="fs-2 fw-bold"><?= $stats['dipinjam'] ?></div>
                    <div class="text-muted small">Dipinjam</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-circle bg-danger bg-opacity-10 p-3">
                    <i class="bi bi-tools fs-4 text-danger"></i>
                </div>
                <div>
                    <div class="fs-2 fw-bold"><?= $stats['diperbaiki'] ?></div>
                    <div class="text-muted small">Diperbaiki</div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <!-- Overdue Borrows -->
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom fw-semibold">
                <i class="bi bi-exclamation-triangle-fill text-danger me-2"></i>
                Peminjaman Terlambat
                <?php if (!empty($overdue)): ?>
                    <span class="badge bg-danger ms-1"><?= count($overdue) ?></span>
                <?php endif; ?>
            </div>
            <div class="card-body p-0">
                <?php if (empty($overdue)): ?>
                    <p class="text-muted text-center py-4 mb-0 small">
                        <i class="bi bi-check-circle text-success d-block fs-3 mb-1"></i>
                        Tidak ada peminjaman yang terlambat.
                    </p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Aset</th>
                                    <th>Peminjam</th>
                                    <th>Rencana Kembali</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($overdue as $b): ?>
                                <tr>
                                    <td>
                                        <a href="<?= base_url('admin/assets/' . $b['asset_id']) ?>" class="text-decoration-none">
                                            <?= esc($b['asset_name']) ?>
                                        </a>
                                        <div class="text-muted" style="font-size:.75rem;"><?= esc($b['asset_code']) ?></div>
                                    </td>
                                    <td><?= esc($b['user_name']) ?></td>
                                    <td><span class="badge bg-danger"><?= esc($b['return_date_plan']) ?></span></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Recent Activity Logs -->
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom fw-semibold">
                <i class="bi bi-clock-history me-2 text-secondary"></i>Aktivitas Terbaru
            </div>
            <div class="card-body p-0">
                <?php if (empty($recent_logs)): ?>
                    <p class="text-muted text-center py-4 mb-0 small">Belum ada aktivitas.</p>
                <?php else: ?>
                    <ul class="list-group list-group-flush">
                        <?php foreach ($recent_logs as $log): ?>
                        <li class="list-group-item px-3 py-2">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <span class="badge bg-secondary me-1"><?= esc($log['action']) ?></span>
                                    <span class="small fw-semibold"><?= esc($log['asset_name'] ?? '-') ?></span>
                                    <?php if ($log['description']): ?>
                                        <div class="text-muted" style="font-size:.75rem;"><?= esc($log['description']) ?></div>
                                    <?php endif; ?>
                                </div>
                                <span class="text-muted" style="font-size:.72rem;white-space:nowrap;">
                                    <?= esc($log['created_at']) ?>
                                </span>
                            </div>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
