<?= $this->extend('inventory/_layout') ?>
<?= $this->section('content') ?>

<div class="max-w-md mx-auto">

    <div class="flex items-center gap-2 text-sm text-gray-500 mb-4">
        <a href="<?= base_url('admin/dashboard') ?>" class="hover:text-blue-600">Dashboard</a>
        <span>›</span>
        <span class="font-medium text-gray-800">Ganti Password</span>
    </div>

    <h1 class="text-xl font-bold text-gray-800 mb-5">🔑 Ganti Password</h1>

    <?php if ($msg = session()->getFlashdata('success')): ?>
    <div class="mb-4 px-4 py-3 bg-green-50 border border-green-200 text-green-700 text-sm rounded-xl">
        ✅ <?= $msg ?>
    </div>
    <?php endif; ?>
    <?php if ($msg = session()->getFlashdata('error')): ?>
    <div class="mb-4 px-4 py-3 bg-red-50 border border-red-200 text-red-700 text-sm rounded-xl">
        ❌ <?= $msg ?>
    </div>
    <?php endif; ?>

    <!-- Info akun -->
    <div class="bg-blue-50 border border-blue-100 rounded-xl px-4 py-3 mb-5 flex items-center gap-3">
        <div class="w-10 h-10 rounded-full bg-blue-600 flex items-center justify-center text-white font-bold text-sm">
            <?= strtoupper(substr(session()->get('user_name'), 0, 1)) ?>
        </div>
        <div>
            <p class="font-semibold text-gray-800 text-sm"><?= esc(session()->get('user_name')) ?></p>
            <p class="text-xs text-gray-500"><?= esc(session()->get('user_email')) ?> · <?= ucfirst(session()->get('role')) ?></p>
        </div>
    </div>

    <div class="bg-white border rounded-xl p-5 shadow-sm">
        <form action="<?= base_url('admin/profile/password') ?>" method="POST" class="space-y-4">
            <?= csrf_field() ?>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Password Saat Ini <span class="text-red-500">*</span>
                </label>
                <input type="password" name="current_password" required
                       placeholder="Password yang sedang digunakan"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Password Baru <span class="text-red-500">*</span>
                </label>
                <input type="password" name="new_password" id="newPass" required
                       placeholder="Minimal 6 karakter"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Konfirmasi Password Baru <span class="text-red-500">*</span>
                </label>
                <input type="password" name="confirm_password" id="confirmPass" required
                       placeholder="Ulangi password baru"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 focus:outline-none">
                <p id="matchMsg" class="text-xs mt-1 hidden"></p>
            </div>

            <div class="flex items-center gap-3 pt-1">
                <button type="submit"
                        class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-2.5 rounded-xl text-sm transition-colors">
                    💾 Simpan Password
                </button>
                <a href="<?= base_url('admin/dashboard') ?>"
                   class="border border-gray-300 text-gray-700 hover:bg-gray-50 px-5 py-2.5 rounded-xl text-sm font-medium">
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>

<script>
document.getElementById('confirmPass').addEventListener('input', function() {
    const newPass = document.getElementById('newPass').value;
    const msg = document.getElementById('matchMsg');
    msg.classList.remove('hidden');
    if (this.value === newPass) {
        msg.textContent = '✅ Password cocok';
        msg.className = 'text-xs mt-1 text-green-600';
    } else {
        msg.textContent = '❌ Password tidak cocok';
        msg.className = 'text-xs mt-1 text-red-500';
    }
});
</script>

<?= $this->endSection() ?>
