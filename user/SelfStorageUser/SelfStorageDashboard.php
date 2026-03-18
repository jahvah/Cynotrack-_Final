<?php
session_start();
include("../../includes/config.php");
include('../../includes/head.php'); 
include("../../includes/selfstorage_header.php"); // Or include('../../includes/storage_header.php') if you have one

// Ensure user is logged in and is Self Storage user
if (!isset($_SESSION['account_id']) || $_SESSION['role'] !== 'self-storage') {
    header("Location: ../login.php");
    exit;
}

$accountId = $_SESSION['account_id'];
$storageUserId = $_SESSION['role_user_id'];

// 1. Fetch Self Storage Profile & Account Info
$stmt = $conn->prepare("
    SELECT s.*, a.status AS account_status, a.username, a.email
    FROM self_storage_users s
    INNER JOIN accounts a ON s.account_id = a.account_id
    WHERE s.storage_user_id = ?
");
$stmt->bind_param("i", $storageUserId);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user || $user['account_status'] !== 'active') {
    $_SESSION['flash_message'] = "Account inactive or not found.";
    unset($_SESSION['account_id'], $_SESSION['role']);
    header("Location: ../login.php");
    exit;
}

// 2. Fetch Storage-Specific Data (Specimens owned by this self-storage user)
$specimenQuery = $conn->query("SELECT * FROM specimens WHERE specimen_owner_type = 'self-storage' AND specimen_owner_id = $storageUserId ORDER BY created_at DESC");
$apptQuery = $conn->query("SELECT * FROM appointments WHERE user_type = 'storage' AND user_id = $storageUserId AND status = 'scheduled' ORDER BY appointment_date ASC LIMIT 3");

// Prepare chart data for Storage Statuses
$statusSummary = $conn->query("SELECT status, COUNT(*) as count FROM specimens WHERE specimen_owner_type = 'self-storage' AND specimen_owner_id = $storageUserId GROUP BY status");
$specLabels = []; $specCounts = [];
while($row = $statusSummary->fetch_assoc()) {
    $specLabels[] = ucfirst($row['status']);
    $specCounts[] = $row['count'];
}
?>

<div id="sidebarOverlay" class="fixed inset-0 bg-black/40 z-40 hidden transition-opacity duration-300 opacity-0" onclick="toggleSidebar()"></div>

<aside id="profileSidebar" class="fixed top-0 right-0 h-full w-80 bg-white z-50 shadow-2xl transform translate-x-full transition-transform duration-300 ease-in-out border-l border-green-100">
    <div class="h-32 bg-emerald-950 relative p-6">
        <button onclick="toggleSidebar()" class="absolute top-4 left-4 text-white/70 hover:text-white transition text-2xl">&times;</button>
        <div class="absolute -bottom-10 left-6">
             <?php if (!empty($user['profile_image'])): ?>
                <img src="../../uploads/<?= htmlspecialchars($user['profile_image']); ?>" class="w-20 h-20 rounded-2xl border-4 border-white shadow-lg object-cover">
            <?php else: ?>
                <div class="w-20 h-20 rounded-2xl border-4 border-white shadow-lg bg-green-100 flex items-center justify-center text-green-700 text-xl font-bold uppercase">
                    <?= substr($user['first_name'], 0, 1) . substr($user['last_name'], 0, 1); ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="p-6 mt-12">
        <h2 class="text-xl font-black text-slate-900"><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></h2>
        <p class="text-xs font-bold text-green-600 uppercase tracking-widest">Storage Client</p>
        
        <div class="mt-8 space-y-3">
            <div class="p-3 bg-green-50 rounded-xl border border-green-100">
                <p class="text-[10px] uppercase font-black text-green-800 opacity-50">Account Email</p>
                <p class="text-sm font-semibold text-slate-700"><?= htmlspecialchars($user['email']); ?></p>
            </div>
            <div class="p-3 bg-green-50 rounded-xl border border-green-100">
                <p class="text-[10px] uppercase font-black text-green-800 opacity-50">Storage ID</p>
                <p class="text-sm font-semibold text-slate-700">#STR-<?= str_pad($user['storage_user_id'], 5, '0', STR_PAD_LEFT); ?></p>
            </div>
        </div>

        <nav class="mt-10 border-t border-gray-100 pt-6 space-y-2">
            <a href="SelfStorageEditProfile.php" class="flex items-center gap-3 p-3 text-sm font-bold text-slate-600 hover:bg-green-50 hover:text-green-700 rounded-xl transition">
                <span>⚙️</span> Edit Profile
            </a>
            <a href="/cynotrack/user/logout.php" class="flex items-center gap-3 p-3 text-sm font-bold text-red-600 hover:bg-red-50 rounded-xl transition">
                <span>🚪</span> Logout
            </a>
        </nav>
    </div>
</aside>

<div class="max-w-7xl mx-auto py-10 px-4">
    <div class="flex items-center justify-between mb-8 border-b border-green-100 pb-6">
        <div>
            <h1 class="text-3xl font-black text-green-900">Storage Dashboard</h1>
            <p class="text-green-600 font-medium mt-1">Welcome back, <?= htmlspecialchars($user['first_name']) ?>. Manage your stored specimens below.</p>
        </div>
        
        <button onclick="toggleSidebar()" class="group flex items-center gap-3 bg-white border border-green-200 px-4 py-2 rounded-xl hover:bg-green-50 transition shadow-sm">
            <div class="space-y-1">
                <div class="w-5 h-0.5 bg-green-800 rounded-full"></div>
                <div class="w-3 h-0.5 bg-green-800 rounded-full group-hover:w-5 transition-all"></div>
                <div class="w-5 h-0.5 bg-green-800 rounded-full"></div>
            </div>
            <span class="text-xs font-black text-green-900 uppercase tracking-widest">Profile</span>
        </button>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-green-100 hover:shadow-md transition">
            <p class="text-[10px] font-black text-green-700 uppercase tracking-widest mb-1">Stored Specimens</p>
            <p class="text-3xl font-black text-slate-900"><?= $specimenQuery->num_rows ?></p>
        </div>
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-green-100 hover:shadow-md transition">
            <p class="text-[10px] font-black text-green-700 uppercase tracking-widest mb-1">Storage Tier</p>
            <p class="text-3xl font-black text-slate-900">Standard</p>
        </div>
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-green-100 hover:shadow-md transition">
            <p class="text-[10px] font-black text-green-700 uppercase tracking-widest mb-1">Account Status</p>
            <div class="flex items-center gap-2 mt-1">
                <span class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></span>
                <p class="text-2xl font-black text-slate-900 capitalize"><?= $user['account_status'] ?></p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-green-50 overflow-hidden">
            <div class="p-6 border-b border-green-50 flex justify-between items-center">
                <h3 class="font-bold text-slate-800">Your Storage Inventory</h3>
                <span class="text-[10px] font-black bg-green-50 text-green-700 px-2 py-1 rounded">Live Data</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-green-50/50 text-[10px] uppercase font-black text-green-800 opacity-70">
                        <tr>
                            <th class="px-6 py-4">Specimen Code</th>
                            <th class="px-6 py-4">Volume</th>
                            <th class="px-6 py-4">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-green-50">
                        <?php if($specimenQuery->num_rows > 0): ?>
                            <?php while($spec = $specimenQuery->fetch_assoc()): ?>
                            <tr class="text-sm hover:bg-green-50/30 transition">
                                <td class="px-6 py-4 font-mono font-bold text-green-700"><?= $spec['unique_code'] ?></td>
                                <td class="px-6 py-4 text-slate-600"><?= $spec['quantity'] ?> units</td>
                                <td class="px-6 py-4">
                                    <span class="px-2 py-1 rounded-md text-[10px] font-bold uppercase bg-emerald-100 text-emerald-800">
                                        <?= $spec['status'] ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="3" class="px-6 py-10 text-center text-slate-400 italic">No specimens found in storage.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="space-y-6">
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-green-100">
                <h3 class="font-bold text-slate-800 mb-4">Upcoming Appointments</h3>
                <div class="space-y-4">
                    <?php if($apptQuery->num_rows > 0): ?>
                        <?php while($appt = $apptQuery->fetch_assoc()): ?>
                            <div class="flex items-center gap-4 p-3 bg-green-50 rounded-xl border border-green-100/50">
                                <div class="bg-white px-3 py-1 rounded-lg shadow-sm text-center">
                                    <p class="text-[10px] font-black text-green-700 uppercase"><?= date('M', strtotime($appt['appointment_date'])) ?></p>
                                    <p class="text-lg font-black text-slate-800"><?= date('d', strtotime($appt['appointment_date'])) ?></p>
                                </div>
                                <div>
                                    <p class="text-sm font-bold text-slate-800 capitalize"><?= $appt['type'] ?></p>
                                    <p class="text-[10px] text-green-600 font-medium"><?= date('h:i A', strtotime($appt['appointment_date'])) ?></p>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="text-center py-6 border-2 border-dashed border-green-100 rounded-xl">
                             <p class="text-xs text-slate-400 font-medium">No appointments scheduled.</p>
                        </div>
                    <?php endif; ?>
                </div>
                <a href="SelfStorageAppointmentCrud/SelfStorageAppointmentIndex.php" class="block text-center mt-6 text-xs font-bold text-green-700 hover:underline uppercase tracking-widest">Manage Schedule</a>
            </div>

            <div class="bg-white p-6 rounded-2xl shadow-sm border border-green-100 h-[300px]">
                <p class="text-[10px] font-black text-green-800 opacity-50 uppercase mb-4 text-center">Specimen Status Distribution</p>
                <div class="relative h-48">
                    <canvas id="storageChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function toggleSidebar() {
        const sidebar = document.getElementById('profileSidebar');
        const overlay = document.getElementById('sidebarOverlay');
        sidebar.classList.toggle('translate-x-full');
        overlay.classList.toggle('hidden');
        setTimeout(() => overlay.classList.toggle('opacity-0'), 10);
    }

    // Chart initialization
    const ctx = document.getElementById('storageChart').getContext('2d');
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: <?= json_encode($specLabels) ?>,
            datasets: [{
                data: <?= json_encode($specCounts) ?>,
                backgroundColor: ['#064e3b', '#059669', '#34d399', '#a7f3d0'],
                hoverOffset: 4,
                borderWidth: 0
            }]
        },
        options: { 
            maintainAspectRatio: false, 
            plugins: { 
                legend: { 
                    position: 'bottom', 
                    labels: { 
                        boxWidth: 8, 
                        padding: 15,
                        font: { size: 10, weight: 'bold', family: 'Inter' } 
                    } 
                } 
            },
            cutout: '75%'
        }
    });
</script>

<?php include("../../includes/footer.php"); ?>