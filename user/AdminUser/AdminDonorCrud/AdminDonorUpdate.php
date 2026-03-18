<?php
session_start();

include('../../../includes/config.php'); 
include('../../../includes/head.php'); 
include('../../../includes/admin_header.php');

// 2. Security Check
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: " . ROOT_URL . "login.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: AdminDonorIndex.php");
    exit();
}

$donor_id = intval($_GET['id']);

// Fetch donor + account info
$stmt = $conn->prepare("
    SELECT d.donor_id, d.account_id, d.first_name, d.last_name, d.profile_image, d.medical_history, d.evaluation_status, d.height_cm, d.weight_kg, d.eye_color, d.hair_color, d.blood_type, d.ethnicity,
    a.username, a.email, a.status
    FROM donors_users d
    JOIN accounts a ON d.account_id = a.account_id
    WHERE d.donor_id = ?
");
$stmt->bind_param("i", $donor_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: AdminDonorIndex.php");
    exit();
}

$donor = $result->fetch_assoc();
?>

<div class="max-w-7xl mx-auto py-10 px-4">
    <div class="mb-6">
        <a href="AdminDonorIndex.php" class="text-sm font-bold text-green-700 hover:text-green-800 transition flex items-center gap-1">
            ← Back to Donor List
        </a>
    </div>

    <div class="max-w-4xl mx-auto">
        <div class="bg-white border border-green-100 rounded-2xl shadow-xl shadow-green-100/20 overflow-hidden">
            
            <div class="bg-green-50/50 border-b border-green-100 px-8 py-6 flex justify-between items-center">
                <div>
                    <h2 class="text-2xl font-bold text-green-900">Update Donor Profile</h2>
                    <p class="text-green-600 text-sm mt-1 font-medium">Currently Editing: <span class="text-green-900"><?= htmlspecialchars($donor['first_name'] . ' ' . $donor['last_name']); ?></span></p>
                </div>
                <div class="shrink-0 flex flex-col items-end gap-2">
                    <?php if (!empty($donor['profile_image'])): ?>
                        <img src="../../../uploads/<?= htmlspecialchars($donor['profile_image']); ?>" class="w-16 h-16 rounded-full object-cover border-2 border-white shadow-md ring-1 ring-green-100">
                    <?php else: ?>
                        <div class="w-16 h-16 rounded-full bg-green-100 flex items-center justify-center text-green-600 font-bold border-2 border-white shadow-sm">
                            <?= substr($donor['first_name'], 0, 1) . substr($donor['last_name'], 0, 1); ?>
                        </div>
                    <?php endif; ?>
                    <span class="text-[10px] font-black uppercase tracking-widest text-green-800 opacity-40">ID #<?= $donor['donor_id']; ?></span>
                </div>
            </div>

            <div class="p-8">
                <?php if (isset($_SESSION['error'])): ?>
                    <div class="mb-6 p-4 text-sm text-red-700 bg-red-50 border border-red-100 rounded-lg font-medium"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
                <?php endif; ?>

                <?php if (isset($_SESSION['success'])): ?>
                    <div class="mb-6 p-4 text-sm text-emerald-700 bg-emerald-50 border border-emerald-100 rounded-lg font-medium"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
                <?php endif; ?>

                <form action="AdminDonorStore.php" method="POST" enctype="multipart/form-data" class="space-y-8">
                    <input type="hidden" name="action" value="AdminDonorUpdate">
                    <input type="hidden" name="donor_id" value="<?= $donor['donor_id']; ?>">
                    <input type="hidden" name="account_id" value="<?= $donor['account_id']; ?>">

                    <div>
                        <h3 class="text-sm font-black text-slate-400 uppercase tracking-widest mb-4 flex items-center gap-2">
                            <span class="w-2 h-2 bg-slate-300 rounded-full"></span> Locked Account Details
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="bg-gray-50 border border-gray-100 p-4 rounded-xl">
                                <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-1">Username</label>
                                <p class="text-sm font-bold text-slate-500 italic"><?= htmlspecialchars($donor['username']); ?></p>
                            </div>
                            <div class="bg-gray-50 border border-gray-100 p-4 rounded-xl">
                                <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-1">Email Address</label>
                                <p class="text-sm font-bold text-slate-500 italic"><?= htmlspecialchars($donor['email']); ?></p>
                            </div>
                        </div>
                    </div>

                    <div class="pt-6 border-t border-green-50">
                        <h3 class="text-sm font-black text-green-900 uppercase tracking-widest mb-4 flex items-center gap-2">
                            <span class="w-2 h-2 bg-green-500 rounded-full"></span> Personal Information
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div>
                                <label class="block text-[10px] font-bold uppercase tracking-wider text-green-800 mb-2">New First Name</label>
                                <input type="text" name="first_name" placeholder="<?= htmlspecialchars($donor['first_name']); ?>"
                                    class="w-full px-4 py-3 border border-green-100 rounded-xl focus:ring-2 focus:ring-green-500 outline-none transition">
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold uppercase tracking-wider text-green-800 mb-2">New Last Name</label>
                                <input type="text" name="last_name" placeholder="<?= htmlspecialchars($donor['last_name']); ?>"
                                    class="w-full px-4 py-3 border border-green-100 rounded-xl focus:ring-2 focus:ring-green-500 outline-none transition">
                            </div>
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold uppercase tracking-wider text-green-800 mb-2">Update Profile Image</label>
                            <input type="file" name="profile_image" class="w-full px-2 py-2 text-sm text-green-800 border border-green-100 rounded-xl cursor-pointer bg-green-50/30 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-bold file:bg-green-600 file:text-white transition">
                        </div>
                    </div>

                    <div class="pt-6 border-t border-green-50">
                        <h3 class="text-sm font-black text-green-900 uppercase tracking-widest mb-4 flex items-center gap-2">
                            <span class="w-2 h-2 bg-green-500 rounded-full"></span> Physical & Medical Details
                        </h3>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                            <div>
                                <label class="block text-[10px] font-bold uppercase tracking-wider text-green-800 mb-2">Height (cm)</label>
                                <input type="number" name="height_cm" min="50" max="250" placeholder="<?= $donor['height_cm']; ?>"
                                    class="w-full px-4 py-3 border border-green-100 rounded-xl focus:ring-2 focus:ring-green-500 outline-none transition">
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold uppercase tracking-wider text-green-800 mb-2">Weight (kg)</label>
                                <input type="number" name="weight_kg" min="20" max="200" placeholder="<?= $donor['weight_kg']; ?>"
                                    class="w-full px-4 py-3 border border-green-100 rounded-xl focus:ring-2 focus:ring-green-500 outline-none transition">
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold uppercase tracking-wider text-green-800 mb-2">Blood Type</label>
                                <input type="text" name="blood_type" placeholder="<?= htmlspecialchars($donor['blood_type']); ?>"
                                    class="w-full px-4 py-3 border border-green-100 rounded-xl focus:ring-2 focus:ring-green-500 outline-none transition">
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold uppercase tracking-wider text-green-800 mb-2">Ethnicity</label>
                                <input type="text" name="ethnicity" placeholder="<?= htmlspecialchars($donor['ethnicity']); ?>"
                                    class="w-full px-4 py-3 border border-green-100 rounded-xl focus:ring-2 focus:ring-green-500 outline-none transition">
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-[10px] font-bold uppercase tracking-wider text-green-800 mb-2">Eye Color</label>
                                <input type="text" name="eye_color" placeholder="<?= htmlspecialchars($donor['eye_color']); ?>"
                                    class="w-full px-4 py-3 border border-green-100 rounded-xl focus:ring-2 focus:ring-green-500 outline-none transition">
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold uppercase tracking-wider text-green-800 mb-2">Hair Color</label>
                                <input type="text" name="hair_color" placeholder="<?= htmlspecialchars($donor['hair_color']); ?>"
                                    class="w-full px-4 py-3 border border-green-100 rounded-xl focus:ring-2 focus:ring-green-500 outline-none transition">
                            </div>
                        </div>
                        <div class="mt-4">
                            <label class="block text-[10px] font-bold uppercase tracking-wider text-green-800 mb-2">Medical History</label>
                            <textarea name="medical_history" rows="3" placeholder="<?= htmlspecialchars($donor['medical_history']); ?>"
                                class="w-full px-4 py-3 border border-green-100 rounded-xl focus:ring-2 focus:ring-green-500 outline-none transition resize-none"></textarea>
                        </div>
                    </div>

                    <div class="pt-6 border-t border-green-50">
                        <h3 class="text-sm font-black text-green-900 uppercase tracking-widest mb-4 flex items-center gap-2">
                            <span class="w-2 h-2 bg-green-500 rounded-full"></span> Status & Approvals
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-[10px] font-bold uppercase tracking-wider text-green-800 mb-2">Evaluation Status</label>
                                <select name="evaluation_status_select" class="w-full px-4 py-3 border border-green-100 rounded-xl focus:ring-2 focus:ring-green-500 outline-none transition bg-white cursor-pointer">
                                    <option value="pending" <?= $donor['evaluation_status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                    <option value="approved" <?= $donor['evaluation_status'] === 'approved' ? 'selected' : ''; ?>>Approved</option>
                                    <option value="rejected" <?= $donor['evaluation_status'] === 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold uppercase tracking-wider text-green-800 mb-2">Account Status</label>
                                <select name="status" class="w-full px-4 py-3 border border-green-100 rounded-xl focus:ring-2 focus:ring-green-500 outline-none transition bg-white cursor-pointer">
                                    <option value="active" <?= $donor['status'] === 'active' ? 'selected' : ''; ?>>Active</option>
                                    <option value="inactive" <?= $donor['status'] === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                                    <option value="pending" <?= $donor['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="pt-8 border-t border-green-50 flex gap-4">
                        <button type="submit" class="flex-1 bg-green-600 hover:bg-green-700 text-white font-bold py-4 rounded-xl transition duration-200 shadow-lg shadow-green-100">
                            Apply Updates
                        </button>
                        <a href="AdminDonorIndex.php" class="px-8 py-4 border border-green-200 text-green-700 font-bold rounded-xl hover:bg-green-50 transition text-center">
                            Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include('../../../includes/footer.php'); ?>