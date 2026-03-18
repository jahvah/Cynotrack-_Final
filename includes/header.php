<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['account_id'])) {
    header("Location: login.php");
    exit();
}

$role = $_SESSION['role']; // 'admin', 'staff', etc.
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | CynoTrack</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-green-50/30">

    <nav class="bg-emerald-950 text-white shadow-md border-b border-emerald-900">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                
                <div class="flex items-center">
                    <span class="text-xl font-bold tracking-tight text-white">
                        Cyno<span class="text-green-400">Track</span>
                    </span>
                </div>

                <div class="hidden md:block">
                    <div class="ml-10 flex items-baseline space-x-2">
                        <?php if ($role === 'admin'): ?>
                            <a href="AdminStaffCrud/AdminStaffIndex.php" class="hover:bg-emerald-900 px-3 py-2 rounded-md text-sm font-semibold transition text-emerald-50">Staff</a>
                            <a href="AdminDonorCrud/AdminDonorIndex.php" class="hover:bg-emerald-900 px-3 py-2 rounded-md text-sm font-semibold transition text-emerald-50">Donors</a>
                            <a href="AdminRecipientCrud/AdminRecipientIndex.php" class="hover:bg-emerald-900 px-3 py-2 rounded-md text-sm font-semibold transition text-emerald-50">Recipients</a>
                            <a href="AdminSelfStorageCrud/AdminSelfStorageIndex.php" class="hover:bg-emerald-900 px-3 py-2 rounded-md text-sm font-semibold transition text-emerald-50">Self-Storage</a>
                        
                        <?php elseif ($role === 'staff'): ?>
                            <a href="StaffSpecimenCrud/StaffSpecimenIndex.php" class="hover:bg-emerald-900 px-3 py-2 rounded-md text-sm font-semibold transition text-emerald-50">Specimens</a>
                            <a href="StaffAppointmentCrud/StaffAppointmentIndex.php" class="hover:bg-emerald-900 px-3 py-2 rounded-md text-sm font-semibold transition text-emerald-50">Appointments</a>
                        
                        <?php elseif (in_array($role, ['donor', 'recipient', 'self-storage'])): ?>
                            <?php 
                                $folder = ucfirst(str_replace('-', '', $role)); 
                            ?>
                            <a href="<?= $folder ?>AppointmentCrud/<?= $folder ?>AppointmentIndex.php" class="hover:bg-emerald-900 px-3 py-2 rounded-md text-sm font-semibold transition text-emerald-50">My Appointments</a>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="flex items-center space-x-4">
                    <span class="text-[10px] bg-emerald-900 text-green-300 border border-emerald-800 px-2 py-1 rounded-md uppercase font-black tracking-widest">
                        <?= htmlspecialchars($role) ?>
                    </span>
                    
                    <a href="/cynotrack/user/logout.php" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg text-sm font-bold transition duration-200 shadow-sm shadow-red-950/20">
                        Logout
                    </a>
                </div>

            </div>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto py-10 px-4">