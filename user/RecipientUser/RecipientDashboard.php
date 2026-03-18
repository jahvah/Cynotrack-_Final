<?php
session_start();

// --- Ensure session variables exist ---
if (!isset($_SESSION['account_id'], $_SESSION['role'], $_SESSION['role_user_id']) || $_SESSION['role'] !== 'recipient') {
    header("Location: ../../login.php");
    exit();
}

// --- Define base URLs ---
if (!defined('BASE_URL')) define('BASE_URL', '/cynotrack/user/RecipientUser/');
if (!defined('ROOT_URL')) define('ROOT_URL', '/cynotrack/user/');

$role = $_SESSION['role'];
$recipient_id = intval($_SESSION['role_user_id']);

// --- Include configs and head ---
include("../../includes/config.php");
include("../../includes/head.php");

// --- Fetch recipient personal details ---
$stmt = $conn->prepare("
    SELECT r.*, a.status AS account_status, a.username, a.email
    FROM recipients_users r
    INNER JOIN accounts a ON r.account_id = a.account_id
    WHERE r.recipient_id = ?
");
$stmt->bind_param("i", $recipient_id);
$stmt->execute();
$recipient = $stmt->get_result()->fetch_assoc();

if (!$recipient || $recipient['account_status'] !== 'active') {
    session_unset();
    session_destroy();
    header("Location: ../../login.php");
    exit();
}

// --- Fetch donors ---
$search = trim($_GET['search'] ?? '');
if (!empty($search)) {
    $sql = "SELECT donor_id, first_name, last_name, profile_image, blood_type, ethnicity 
            FROM donors_users 
            WHERE evaluation_status = 'approved' 
            AND (first_name LIKE ? OR last_name LIKE ?) 
            ORDER BY donor_id ASC";
    $stmt = mysqli_prepare($conn, $sql);
    $like = "%$search%";
    mysqli_stmt_bind_param($stmt, "ss", $like, $like);
    mysqli_stmt_execute($stmt);
    $donors = mysqli_stmt_get_result($stmt);
} else {
    $donors = mysqli_query($conn, "SELECT donor_id, first_name, last_name, profile_image, blood_type, ethnicity FROM donors_users WHERE evaluation_status = 'approved' ORDER BY donor_id ASC");
}

$totalDonors = mysqli_num_rows($donors);

// --- Stats ---
$pendingStmt = $conn->prepare("SELECT COUNT(*) AS cnt FROM specimen_requests WHERE recipient_id=? AND status='pending'");
$pendingStmt->bind_param("i", $recipient_id);
$pendingStmt->execute();
$pendingCount = $pendingStmt->get_result()->fetch_assoc()['cnt'] ?? 0;

$fulfilledStmt = $conn->prepare("SELECT COUNT(*) AS cnt FROM specimen_requests WHERE recipient_id=? AND status='fulfilled'");
$fulfilledStmt->bind_param("i", $recipient_id);
$fulfilledStmt->execute();
$fulfilledCount = $fulfilledStmt->get_result()->fetch_assoc()['cnt'] ?? 0;
?>

<!-- ================== NAVIGATION HEADER ================== -->
<nav class="bg-emerald-950 text-white shadow-md border-b border-emerald-900 font-sans">
    <div class="max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">
            <div class="flex-shrink-0 flex items-center w-1/4">
                <a href="<?= BASE_URL ?>RecipientDashboard.php" class="text-xl font-bold tracking-tight text-white hover:text-green-400 transition flex items-center">
                    Cyno<span class="text-green-400">Track</span> 
                    <span class="hidden lg:inline-block text-[10px] ml-2 opacity-50 uppercase tracking-widest font-black border border-white/20 px-2 py-0.5 rounded">Recipient</span>
                </a>
            </div>
            <div class="hidden md:flex flex-1 justify-center">
                <div class="flex items-center space-x-1">
                    <a href="<?= BASE_URL ?>RecipientAppointmentCrud/RecipientAppointmentIndex.php" class="hover:bg-emerald-900 px-4 py-2 rounded-lg text-sm font-bold transition text-emerald-50">
                        My Appointments
                    </a>
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

<!-- ================== DASHBOARD / DONOR LIST ================== -->
<div class="max-w-7xl mx-auto py-10 px-4">
    <div class="flex items-center justify-between mb-8 border-b border-green-100 pb-6">
        <div>
            <h1 class="text-3xl font-black text-green-900">Recipient Dashboard</h1>
            <p class="text-green-600 font-medium mt-1">Welcome back, <?= htmlspecialchars($recipient['first_name']) ?>. Browse available donors below.</p>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-green-100 hover:shadow-md transition">
            <p class="text-[10px] font-black text-green-700 uppercase tracking-widest mb-1">Available Donors</p>
            <p class="text-3xl font-black text-slate-900"><?= $totalDonors ?></p>
        </div>
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-green-100 hover:shadow-md transition">
            <p class="text-[10px] font-black text-green-700 uppercase tracking-widest mb-1">Pending Requests</p>
            <p class="text-3xl font-black text-slate-900"><?= $pendingCount ?></p>
        </div>
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-green-100 hover:shadow-md transition">
            <p class="text-[10px] font-black text-green-700 uppercase tracking-widest mb-1">Fulfilled</p>
            <p class="text-3xl font-black text-slate-900"><?= $fulfilledCount ?></p>
        </div>
    </div>

    <!-- Donor Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-6">
        <?php while ($row = mysqli_fetch_assoc($donors)): ?>
            <div class="bg-white rounded-2xl border border-green-50 shadow-sm overflow-hidden hover:shadow-md transition group">
                <div class="h-48 bg-green-50 relative overflow-hidden">
                    <?php if (!empty($row['profile_image'])): ?>
                        <img src="../../uploads/<?= htmlspecialchars($row['profile_image']); ?>" class="w-full h-full object-cover group-hover:scale-105 transition duration-500">
                    <?php else: ?>
                        <div class="w-full h-full flex items-center justify-center text-green-200 text-5xl font-black">
                            <?= substr($row['first_name'],0,1).substr($row['last_name'],0,1) ?>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="p-5">
                    <p class="text-[10px] font-black text-green-600 uppercase tracking-widest mb-1"><?= htmlspecialchars($row['ethnicity'] ?? 'Unknown') ?></p>
                    <h4 class="font-bold text-slate-900 text-lg mb-4"><?= htmlspecialchars($row['first_name'].' '.$row['last_name']) ?></h4>
                    <a href="RecipientSpecimenRequest/RecipientDonorRequestIndex.php?id=<?= intval($row['donor_id']); ?>" 
                       class="block w-full text-center py-2.5 bg-green-50 text-green-800 rounded-xl font-bold text-xs uppercase tracking-widest hover:bg-emerald-800 hover:text-white transition">
                        View Profile
                    </a>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
</div>

<?php include("../../includes/footer.php"); ?>