<?php
session_start();
include('../../includes/config.php'); 
include('../../includes/head.php'); 
include('../../includes/donor_header.php');

// Security Check
if (!isset($_SESSION['account_id']) || $_SESSION['role'] !== 'donor') {
    header("Location: ../login.php");
    exit();
}

$accountId = $_SESSION['account_id'];

// 1. Fetch Donor Personal Details & Account Info
$donorQuery = $conn->prepare("
    SELECT a.username, a.email, d.* FROM accounts a 
    JOIN donors_users d ON a.account_id = d.account_id 
    WHERE a.account_id = ?
");
$donorQuery->bind_param("i", $accountId);
$donorQuery->execute();
$donor = $donorQuery->get_result()->fetch_assoc();
$donorId = $donor['donor_id'];

// 2. Data Fetching
$specimenQuery = $conn->query("SELECT * FROM specimens WHERE specimen_owner_type = 'donor' AND specimen_owner_id = $donorId ORDER BY created_at DESC");
$apptQuery = $conn->query("SELECT * FROM appointments WHERE user_type = 'donor' AND user_id = $donorId AND status = 'scheduled' ORDER BY appointment_date ASC LIMIT 3");

// Prepare chart data for Specimen Statuses
$statusSummary = $conn->query("SELECT status, COUNT(*) as count FROM specimens WHERE specimen_owner_id = $donorId GROUP BY status");
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
             <?php if (!empty($donor['profile_image'])): ?>
                <img src="../../uploads/<?= htmlspecialchars($donor['profile_image']); ?>" class="w-20 h-20 rounded-2xl border-4 border-white shadow-lg object-cover">
            <?php else: ?>
                <div class="w-20 h-20 rounded-2xl border-4 border-white shadow-lg bg-green-100 flex items-center justify-center text-green-700 text-xl font-bold uppercase">
                    <?= substr($donor['first_name'], 0, 1) . substr($donor['last_name'], 0, 1); ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="p-6 mt-12">
        <h2 class="text-xl font-black text-slate-900"><?= htmlspecialchars($donor['first_name'] . ' ' . $donor['last_name']); ?></h2>
        <p class="text-xs font-bold text-green-600 uppercase tracking-widest">Verified Donor</p>
        
        <div class="mt-8 space-y-3">
            <div class="p-3 bg-green-50 rounded-xl border border-green-100">
                <p class="text-[10px] uppercase font-black text-green-800 opacity-50">Evaluation Status</p>
                <span class="text-xs font-bold uppercase px-2 py-0.5 rounded <?= $donor['evaluation_status'] == 'approved' ? 'bg-green-100 text-green-700' : 'bg-orange-100 text-orange-700' ?>">
                    <?= $donor['evaluation_status'] ?>
                </span>
            </div>
            <div class="p-3 bg-green-50 rounded-xl border border-green-100">
                <p class="text-[10px] uppercase font-black text-green-800 opacity-50">Email Address</p>
                <p class="text-sm font-semibold text-slate-700"><?= htmlspecialchars($donor['email']); ?></p>
            </div>
        </div>

        <nav class="mt-10 border-t border-gray-100 pt-6 space-y-2">
            <a href="DonorEditProfile.php" class="flex items-center gap-3 p-3 text-sm font-bold text-slate-600 hover:bg-green-50 hover:text-green-700 rounded-xl transition">
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
            <h1 class="text-3xl font-black text-green-900">Donor Dashboard</h1>
            <p class="text-green-600 font-medium mt-1">Hello, <?= htmlspecialchars($donor['first_name']) ?>. Here is your current status.</p>
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
            <p class="text-[10px] font-black text-green-700 uppercase tracking-widest mb-1">Total Specimens</p>
            <p class="text-3xl font-black text-slate-900"><?= $specimenQuery->num_rows ?></p>
        </div>
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-green-100 hover:shadow-md transition">
            <p class="text-[10px] font-black text-green-700 uppercase tracking-widest mb-1">Blood Type</p>
            <p class="text-3xl font-black text-slate-900"><?= $donor['blood_type'] ?? 'N/A' ?></p>
        </div>
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-green-100 hover:shadow-md transition">
            <p class="text-[10px] font-black text-green-700 uppercase tracking-widest mb-1">Evaluation Status</p>
            <p class="text-2xl font-black text-slate-900 capitalize"><?= $donor['evaluation_status'] ?></p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-green-50 overflow-hidden">
            <div class="p-6 border-b border-green-50">
                <h3 class="font-bold text-slate-800">Specimen Inventory</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-green-50 text-[10px] uppercase font-black text-green-800 opacity-70">
                        <tr>
                            <th class="px-6 py-4">Code</th>
                            <th class="px-6 py-4">Quantity</th>
                            <th class="px-6 py-4">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-green-50">
                        <?php while($spec = $specimenQuery->fetch_assoc()): ?>
                        <tr class="text-sm hover:bg-green-50/30 transition">
                            <td class="px-6 py-4 font-mono font-bold text-green-700"><?= $spec['unique_code'] ?></td>
                            <td class="px-6 py-4 text-slate-600"><?= $spec['quantity'] ?> units</td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 rounded-md text-[10px] font-bold uppercase bg-green-100 text-green-800">
                                    <?= $spec['status'] ?>
                                </span>
                            </td>
                        </tr>
                        <?php endwhile; ?>
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
                        <p class="text-sm text-slate-400 italic py-4">No scheduled appointments.</p>
                    <?php endif; ?>
                </div>
                <a href="DonorAppointmentCrud/DonorAppointmentIndex.php" class="block text-center mt-6 text-xs font-bold text-green-700 hover:underline uppercase tracking-widest">Manage All</a>
            </div>

            <div class="bg-white p-6 rounded-2xl shadow-sm border border-green-50 h-[280px]">
                <p class="text-[10px] font-black text-green-800 opacity-50 uppercase mb-4 text-center">Specimen Status Distribution</p>
                <canvas id="specimenChart"></canvas>
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

    new Chart(document.getElementById('specimenChart'), {
        type: 'doughnut',
        data: {
            labels: <?= json_encode($specLabels) ?>,
            datasets: [{
                data: <?= json_encode($specCounts) ?>,
                backgroundColor: ['#064e3b', '#059669', '#34d399', '#a7f3d0'],
                borderWidth: 0
            }]
        },
        options: { 
            maintainAspectRatio: false, 
            plugins: { 
                legend: { position: 'bottom', labels: { boxWidth: 10, font: { size: 10, weight: 'bold' } } } 
            },
            cutout: '70%'
        }
    });
</script>
<?php include('../../includes/footer.php'); ?>