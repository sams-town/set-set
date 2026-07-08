<!DOCTYPE html>
<html lang="id" class="h-full bg-gray-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? 'Dashboard') ?> — RS.Taman Harapan Baru</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: { DEFAULT: '#2563eb', dark: '#1d4ed8', light: '#dbeafe' }
                    }
                }
            }
        }
    </script>
    <!-- Alpine.js untuk interaksi ringan (dropdown, modal) -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        /* Sembunyikan elemen x-cloak hingga Alpine selesai init */
        [x-cloak] { display: none !important; }

        /* Pastikan mobile overlay tersembunyi di layar besar (>= 1024px) */
        @media (min-width: 1024px) {
            .mobile-overlay {
                display: none !important;
                pointer-events: none !important;
            }
        }
    </style>
</head>
<body class="h-full" x-data="{ sidebarOpen: false }">

<div class="flex h-screen overflow-hidden">

    <!-- ── SIDEBAR ──────────────────────────────────────────────── -->
    <!-- Mobile overlay -->
    <div x-show="sidebarOpen" x-cloak x-transition.opacity
         class="fixed inset-0 z-20 bg-black/50 lg:hidden mobile-overlay"
         :style="sidebarOpen ? '' : 'display:none'"
         @click="sidebarOpen = false"></div>

    <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
           class="fixed inset-y-0 left-0 z-30 w-60 flex flex-col bg-gray-900 text-white
                  transition-transform duration-200 lg:relative lg:translate-x-0">

        <!-- Brand -->
        <div class="flex items-center gap-3 px-4 py-3 border-b border-gray-700 bg-white">
            <img src="<?= base_url('uploads/assets/logo.jpg') ?>" alt="Logo RS" class="w-10 h-10 rounded object-contain shrink-0">
            <div class="min-w-0">
                <div class="font-bold text-xs leading-tight text-gray-800">RS. Taman Harapan Baru</div>
                <div class="text-gray-500 text-[10px]">Asset Management</div>
            </div>
        </div>

        <!-- Nav -->
        <nav class="flex-1 px-3 py-4 space-y-0.5 overflow-y-auto text-sm">
            <?php
            $uri = uri_string();

            // Helper: cek apakah URL aktif
            $isActive = fn($url) => str_starts_with($uri, $url);

            // Menu utama dengan filter peran/role
            $allNav = [
                ['url' => 'admin/dashboard',   'label' => 'Dashboard',         'icon' => '📊', 'roles' => ['admin', 'user']],
                ['url' => 'admin/inventory',   'label' => 'Inventory Aset',    'icon' => '🗃️', 'roles' => ['admin', 'user']],
                ['url' => 'admin/procurement', 'label' => 'Procurement',       'icon' => '🛒', 'roles' => ['admin', 'user', 'pembelian']],
                ['url' => 'admin/work-orders', 'label' => 'Work Order',        'icon' => '📋', 'roles' => ['admin', 'user', 'technician']],
                ['url' => 'admin/pm',          'label' => 'Preventive PM',     'icon' => '🛡️', 'roles' => ['admin', 'technician']],
                ['url' => 'admin/cm',          'label' => 'Corrective Maint.', 'icon' => '🔧', 'roles' => ['admin', 'technician']],
                ['url' => 'admin/borrows',     'label' => 'Peminjaman',        'icon' => '🔄', 'roles' => ['admin', 'user']],
                ['url' => 'admin/reports',     'label' => 'Laporan',           'icon' => '📑', 'roles' => ['admin']],
            ];

            $userRole = session()->get('role');
            $mainNav = array_filter($allNav, function($item) use ($userRole) {
                return in_array($userRole, $item['roles']);
            });

            // Master Data group
            $masterNav = [
                ['url' => 'admin/departments',      'label' => 'Departemen',    'icon' => '🏢'],
                ['url' => 'admin/room-types',       'label' => 'Tipe Ruangan',  'icon' => '🚪'],
                ['url' => 'admin/locations',        'label' => 'Lokasi',        'icon' => '📍'],
                ['url' => 'admin/categories',       'label' => 'Kategori Aset', 'icon' => '🏷️'],
                ['url' => 'admin/asset-templates',  'label' => 'Template Aset', 'icon' => '📋'],
            ];

            // Admin-only group
            $adminNav = [
                ['url' => 'admin/users',    'label' => 'Staff & Peran', 'icon' => '👥'],
                ['url' => 'admin/settings', 'label' => 'Pengaturan',   'icon' => '⚙️'],
            ];

            // Cek apakah master data aktif
            $masterActive = false;
            foreach ($masterNav as $m) {
                if ($isActive($m['url'])) { $masterActive = true; break; }
            }
            ?>

            <?php foreach ($mainNav as $item): ?>
            <a href="<?= base_url($item['url']) ?>"
               class="flex items-center gap-3 px-3 py-2 rounded-lg transition-colors
                      <?= $isActive($item['url']) ? 'bg-blue-600 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' ?>">
                <span><?= $item['icon'] ?></span>
                <span><?= $item['label'] ?></span>
            </a>
            <?php endforeach; ?>

            <!-- Master Data (dropdown, Admin Only) -->
            <?php if (session()->get('role') === 'admin'): ?>
            <div x-data="{ open: <?= $masterActive ? 'true' : 'false' ?> }">
                <button @click="open = !open"
                        class="w-full flex items-center justify-between gap-3 px-3 py-2 rounded-lg transition-colors
                               <?= $masterActive ? 'bg-gray-700 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' ?>">
                    <div class="flex items-center gap-3">
                        <span>🗂️</span>
                        <span>Master Data</span>
                    </div>
                    <svg :class="open ? 'rotate-180' : ''"
                         class="w-3 h-3 transition-transform shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
                <div x-show="open" x-transition class="mt-0.5 ml-3 space-y-0.5 border-l border-gray-700 pl-2">
                    <?php foreach ($masterNav as $item): ?>
                    <a href="<?= base_url($item['url']) ?>"
                       class="flex items-center gap-2 px-3 py-1.5 rounded-lg text-xs transition-colors
                              <?= $isActive($item['url']) ? 'bg-blue-600 text-white' : 'text-gray-400 hover:bg-gray-700 hover:text-white' ?>">
                        <span><?= $item['icon'] ?></span>
                        <span><?= $item['label'] ?></span>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <?php if (session()->get('role') === 'admin'): ?>
            <!-- Divider -->
            <div class="border-t border-gray-700 my-1"></div>
            <?php foreach ($adminNav as $item): ?>
            <a href="<?= base_url($item['url']) ?>"
               class="flex items-center gap-3 px-3 py-2 rounded-lg transition-colors
                      <?= $isActive($item['url']) ? 'bg-blue-600 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' ?>">
                <span><?= $item['icon'] ?></span>
                <span><?= $item['label'] ?></span>
            </a>
            <?php endforeach; ?>
            <?php endif; ?>
        </nav>

        <!-- User info -->
        <div class="px-4 py-3 border-t border-gray-700 text-sm">
            <div class="text-gray-400 text-xs mb-1">Login sebagai</div>
            <div class="font-semibold truncate"><?= esc(session()->get('user_name')) ?></div>
            <div class="text-gray-400 text-xs capitalize"><?= esc(session()->get('role')) ?></div>
            <?php if (session()->get('department_name')): ?>
            <div class="flex items-center gap-1 mt-1">
                <span class="text-gray-500 text-xs">🏢</span>
                <span class="text-blue-300 text-xs truncate"><?= esc(session()->get('department_name')) ?></span>
            </div>
            <?php endif; ?>
            <?php if (session()->get('role') !== 'admin' && session()->get('department_name')): ?>
            <div class="mt-1 px-2 py-0.5 bg-yellow-900/40 rounded text-xs text-yellow-300 text-center">
                Akses: Dept. Sendiri
            </div>
            <?php endif; ?>
            <a href="<?= base_url('logout') ?>"
               class="mt-2 flex items-center gap-2 text-red-400 hover:text-red-300 text-xs">
                ⎋ Logout
            </a>
        </div>
    </aside>

    <!-- ── MAIN CONTENT ─────────────────────────────────────────── -->
    <div class="flex-1 flex flex-col overflow-hidden">

        <!-- Topbar -->
        <header class="flex items-center justify-between bg-white border-b px-4 py-3 shadow-sm">
            <button @click="sidebarOpen = !sidebarOpen"
                    class="lg:hidden p-1.5 rounded text-gray-500 hover:bg-gray-100">
                ☰
            </button>
            <div class="text-sm text-gray-500 hidden lg:block">
                <?= date('l, d F Y') ?>
            </div>
            <div class="text-sm font-semibold text-gray-700"><?= esc($title ?? '') ?></div>
        </header>

        <!-- Flash messages -->
        <div class="px-6 pt-4 space-y-2">
            <?php if (session()->getFlashdata('success')): ?>
                <div x-data="{show:true}" x-show="show" x-init="setTimeout(()=>show=false,4000)"
                     class="flex items-start gap-2 bg-green-50 border border-green-200 text-green-800 text-sm px-4 py-3 rounded-lg">
                    <span class="mt-0.5">✓</span>
                    <span><?= session()->getFlashdata('success') ?></span>
                    <button @click="show=false" class="ml-auto text-green-500 hover:text-green-700">✕</button>
                </div>
            <?php endif; ?>
            <?php if (session()->getFlashdata('error')): ?>
                <div x-data="{show:true}" x-show="show"
                     class="flex items-start gap-2 bg-red-50 border border-red-200 text-red-800 text-sm px-4 py-3 rounded-lg">
                    <span class="mt-0.5">⚠</span>
                    <span><?= session()->getFlashdata('error') ?></span>
                    <button @click="show=false" class="ml-auto text-red-400 hover:text-red-600">✕</button>
                </div>
            <?php endif; ?>
            <?php if (session()->getFlashdata('errors')): ?>
                <div class="bg-red-50 border border-red-200 text-red-800 text-sm px-4 py-3 rounded-lg">
                    <p class="font-semibold mb-1">⚠ Terdapat kesalahan:</p>
                    <ul class="list-disc list-inside space-y-0.5">
                        <?php foreach (session()->getFlashdata('errors') as $e): ?>
                            <li><?= esc($e) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
        </div>

        <!-- Page content -->
        <main class="flex-1 overflow-y-auto px-6 py-4">
            <?= $this->renderSection('content') ?>
        </main>
    </div>
</div>

</body>
</html>
