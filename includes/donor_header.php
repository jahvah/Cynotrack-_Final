<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!defined('BASE_URL')) define('BASE_URL', '/cynotrack/user/DonorUser/');
if (!defined('ROOT_URL')) define('ROOT_URL', '/cynotrack/user/');

$role = $_SESSION['role'] ?? 'guest';
?>

<nav class="bg-emerald-950 text-white shadow-md border-b border-emerald-900 font-sans">
    <div class="max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">
            
            <div class="flex-shrink-0 flex items-center w-1/4">
                <a href="<?= BASE_URL ?>DonorDashboard.php" class="text-xl font-bold tracking-tight text-white hover:text-green-400 transition flex items-center">
                    Cyno<span class="text-green-400">Track</span> 
                    <span class="hidden lg:inline-block text-[10px] ml-2 opacity-50 uppercase tracking-widest font-black border border-white/20 px-2 py-0.5 rounded">Donor</span>
                </a>
            </div>

            <div class="hidden md:flex flex-1 justify-center">
                <div class="flex items-center space-x-1">
                    <?php if ($role === 'donor'): ?>
                        <a href="<?= BASE_URL ?>DonorAppointmentCrud/DonorAppointmentIndex.php" class="hover:bg-emerald-900 px-4 py-2 rounded-lg text-sm font-bold transition text-emerald-50">My Appointments</a>
                    <?php endif; ?>
                </div>
            </div>

            <div class="flex items-center justify-end space-x-4 w-1/4">
                <div class="hidden sm:flex flex-col items-end leading-tight">
                    <span class="text-[9px] text-green-300 uppercase font-black tracking-tighter opacity-70">Logged as</span>
                    <span class="text-xs font-bold text-white uppercase"><?= htmlspecialchars($role) ?></span>
                </div>
                
                <div class="hidden sm:block h-8 w-px bg-emerald-900"></div>

                <a href="<?= ROOT_URL ?>logout.php" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-xl text-sm font-bold transition duration-200 shadow-lg shadow-red-950/40">
                    Logout
                </a>
            </div>

        </div>
    </div>
</nav>