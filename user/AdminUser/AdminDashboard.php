<?php
session_start();
include('../../includes/config.php');
include('../../includes/head.php');
include('../../includes/admin_header.php');

// Security Check
if (!isset($_SESSION['account_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// Fetch Admin Details
$adminId = $_SESSION['account_id'];
$adminQuery = $conn->query("SELECT a.username, a.email, s.first_name, s.last_name, s.profile_image 
                            FROM accounts a 
                            LEFT JOIN staff s ON a.account_id = s.account_id 
                            WHERE a.account_id = $adminId");
$admin = $adminQuery->fetch_assoc();

// Data Fetching (Counts)
$staffCount = $conn->query("SELECT COUNT(*) as total FROM staff")->fetch_assoc()['total'];
$donorCount = $conn->query("SELECT COUNT(*) as total FROM donors_users")->fetch_assoc()['total'];
$recipientCount = $conn->query("SELECT COUNT(*) as total FROM recipients_users")->fetch_assoc()['total'];

// Status Data
$statusData = $conn->query("SELECT status, COUNT(*) as count FROM accounts GROUP BY status");
$statuses = []; $statusCounts = [];
while($row = $statusData->fetch_assoc()) {
    $statuses[] = ucfirst($row['status']);
    $statusCounts[] = $row['count'];
}
?>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div id="sidebarOverlay" class="fixed inset-0 bg-black/40 z-40 hidden transition-opacity duration-300 opacity-0" onclick="toggleSidebar()"></div>

<aside id="profileSidebar" class="fixed top-0 right-0 h-full w-80 bg-white z-50 shadow-2xl transform translate-x-full transition-transform duration-300 ease-in-out border-l border-green-100">
    <div class="h-32 bg-emerald-950 relative p-6">
        <button onclick="toggleSidebar()" class="absolute top-4 left-4 text-white/70 hover:text-white transition text-2xl">&times;</button>
        <div class="absolute -bottom-10 left-6">
             <?php if (!empty($admin['profile_image'])): ?>
                <img src="../../uploads/<?= htmlspecialchars($admin['profile_image']); ?>" class="w-20 h-20 rounded-2xl border-4 border-white shadow-lg object-cover">
            <?php else: ?>
                <div class="w-20 h-20 rounded-2xl border-4 border-white shadow-lg bg-green-100 flex items-center justify-center text-green-700 text-xl font-bold">
                    <?= substr($admin['first_name'], 0, 1) . substr($admin['last_name'], 0, 1); ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="p-6 mt-12">
        <h2 class="text-xl font-black text-slate-900"><?= htmlspecialchars($admin['first_name'] . ' ' . $admin['last_name']); ?></h2>
        <p class="text-xs font-bold text-green-600 uppercase tracking-widest">System Administrator</p>
        
        <div class="mt-8 space-y-4">
            <div class="p-3 bg-green-50 rounded-xl border border-green-100">
                <p class="text-[10px] uppercase font-black text-green-800 opacity-50">Username</p>
                <p class="text-sm font-semibold text-slate-700"><?= htmlspecialchars($admin['username']); ?></p>
            </div>
            <div class="p-3 bg-green-50 rounded-xl border border-green-100">
                <p class="text-[10px] uppercase font-black text-green-800 opacity-50">Email</p>
                <p class="text-sm font-semibold text-slate-700"><?= htmlspecialchars($admin['email']); ?></p>
            </div>
        </div>

        <nav class="mt-10 border-t border-gray-100 pt-6 space-y-2">
            <a href="../profile_edit.php" class="flex items-center gap-3 p-3 text-sm font-bold text-slate-600 hover:bg-green-50 hover:text-green-700 rounded-xl transition">
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
            <h1 class="text-3xl font-bold text-green-900">Account Overview</h1>
            <p class="text-green-600 font-medium mt-1">Real-time breakdown of user accounts.</p>
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
        <div class="bg-white p-6 rounded-xl shadow-sm border border-green-100 hover:shadow-md transition">
            <p class="text-sm font-bold text-green-700">Staff</p>
            <p class="text-3xl font-black text-slate-900"><?= $staffCount ?></p>
        </div>
        <div class="bg-white p-6 rounded-xl shadow-sm border border-green-100 hover:shadow-md transition">
            <p class="text-sm font-bold text-green-700">Donors</p>
            <p class="text-3xl font-black text-slate-900"><?= $donorCount ?></p>
        </div>
        <div class="bg-white p-6 rounded-xl shadow-sm border border-green-100 hover:shadow-md transition">
            <p class="text-sm font-bold text-green-700">Recipients</p>
            <p class="text-3xl font-black text-slate-900"><?= $recipientCount ?></p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <div class="bg-white p-8 rounded-2xl shadow-sm border border-green-50 shadow-green-100/20 h-[400px]">
            <canvas id="userTypeChart"></canvas>
        </div>
        <div class="bg-white p-8 rounded-2xl shadow-sm border border-green-50 shadow-green-100/20 h-[400px]">
            <canvas id="statusChart"></canvas>
        </div>
    </div>
</div>

<script>
    // SIDEBAR TOGGLE LOGIC
    function toggleSidebar() {
        const sidebar = document.getElementById('profileSidebar');
        const overlay = document.getElementById('sidebarOverlay');
        
        sidebar.classList.toggle('translate-x-full');
        overlay.classList.toggle('hidden');
        setTimeout(() => overlay.classList.toggle('opacity-0'), 10);
    }

    // CHARTS
    new Chart(document.getElementById('userTypeChart'), {
        type: 'doughnut',
        data: {
            labels: ['Staff', 'Donors', 'Recipients'],
            datasets: [{
                data: [<?= $staffCount ?>, <?= $donorCount ?>, <?= $recipientCount ?>],
                backgroundColor: ['#064e3b', '#10b981', '#34d399'],
                borderWidth: 0
            }]
        },
        options: { maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } }
    });

    new Chart(document.getElementById('statusChart'), {
        type: 'bar',
        data: {
            labels: <?= json_encode($statuses) ?>,
            datasets: [{
                data: <?= json_encode($statusCounts) ?>,
                backgroundColor: '#059669',
                borderRadius: 10
            }]
        },
        options: { maintainAspectRatio: false, plugins: { legend: { display: false } } }
    });
</script>

<?php include('../../includes/footer.php'); ?>