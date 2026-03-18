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
    header("Location: AdminSelfStorageIndex.php");
    exit();
}

$storage_user_id = intval($_GET['id']);

// Fetch self storage user data with account info
$stmt = $conn->prepare("
    SELECT s.storage_user_id, 
           s.account_id, 
           s.first_name, 
           s.last_name, 
           s.profile_image, 
           s.storage_details,
           a.username, 
           a.email, 
           a.status
    FROM self_storage_users s
    JOIN accounts a ON s.account_id = a.account_id
    WHERE s.storage_user_id = ?
");
$stmt->bind_param("i", $storage_user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: AdminSelfStorageIndex.php");
    exit();
}

$storage = $result->fetch_assoc();
?>

<div class="max-w-7xl mx-auto py-10 px-4">
    <div class="mb-6">
        <a href="AdminSelfStorageIndex.php" class="text-sm font-bold text-green-700 hover:text-green-800 transition flex items-center gap-1">
            ← Back to Storage List
        </a>
    </div>

    <div class="max-w-3xl mx-auto">
        <div class="bg-white border border-green-100 rounded-2xl shadow-xl shadow-green-100/20 overflow-hidden">
            
            <div class="bg-green-50/50 border-b border-green-100 px-8 py-6 flex justify-between items-center">
                <div>
                    <h2 class="text-2xl font-bold text-green-900">Update Storage User</h2>
                    <p class="text-green-600 text-sm mt-1 font-medium">Currently Editing: <span class="text-green-900"><?= htmlspecialchars($storage['first_name'] . ' ' . $storage['last_name']); ?></span></p>
                </div>
                <div class="shrink-0 flex flex-col items-end gap-2">
                    <?php if (!empty($storage['profile_image'])): ?>
                        <img src="../../../uploads/<?= htmlspecialchars($storage['profile_image']); ?>" class="w-16 h-16 rounded-full object-cover border-2 border-white shadow-md ring-1 ring-green-100">
                    <?php else: ?>
                        <div class="w-16 h-16 rounded-full bg-green-100 flex items-center justify-center text-green-600 font-bold border-2 border-white shadow-sm">
                            <?= substr($storage['first_name'], 0, 1) . substr($storage['last_name'], 0, 1); ?>
                        </div>
                    <?php endif; ?>
                    <span class="text-[10px] font-black uppercase tracking-widest text-green-800 opacity-40">Storage ID #<?= $storage['storage_user_id']; ?></span>
                </div>
            </div>

            <div class="p-8">
                <?php if (isset($_SESSION['error'])): ?>
                    <div class="mb-6 p-4 text-sm text-red-700 bg-red-50 border border-red-100 rounded-lg font-medium"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
                <?php endif; ?>
                <?php if (isset($_SESSION['success'])): ?>
                    <div class="mb-6 p-4 text-sm text-emerald-700 bg-emerald-50 border border-emerald-100 rounded-lg font-medium"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
                <?php endif; ?>
                <?php if (isset($_SESSION['no_update'])): ?>
                    <div class="mb-6 p-4 text-sm text-amber-700 bg-amber-50 border border-amber-100 rounded-lg font-medium"><?= $_SESSION['no_update']; unset($_SESSION['no_update']); ?></div>
                <?php endif; ?>

                <form action="AdminSelfStorageStore.php" method="POST" enctype="multipart/form-data" class="space-y-8">
                    <input type="hidden" name="action" value="AdminSelfStorageUpdate">
                    <input type="hidden" name="storage_user_id" value="<?= $storage['storage_user_id']; ?>">
                    <input type="hidden" name="account_id" value="<?= $storage['account_id']; ?>">

                    <div>
                        <h3 class="text-sm font-black text-slate-400 uppercase tracking-widest mb-4 flex items-center gap-2">
                            <span class="w-2 h-2 bg-slate-300 rounded-full"></span> Locked Account Details
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="bg-gray-50 border border-gray-100 p-4 rounded-xl">
                                <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-1">Username</label>
                                <p class="text-sm font-bold text-slate-500 italic"><?= htmlspecialchars($storage['username']); ?></p>
                            </div>
                            <div class="bg-gray-50 border border-gray-100 p-4 rounded-xl">
                                <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-1">Email Address</label>
                                <p class="text-sm font-bold text-slate-500 italic"><?= htmlspecialchars($storage['email']); ?></p>
                            </div>
                        </div>
                    </div>

                    <div class="pt-6 border-t border-green-50">
                        <h3 class="text-sm font-black text-green-900 uppercase tracking-widest mb-4 flex items-center gap-2">
                            <span class="w-2 h-2 bg-green-500 rounded-full"></span> Personal Information
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-[10px] font-bold uppercase tracking-wider text-green-800 mb-2">New First Name</label>
                                <input type="text" name="first_name" placeholder="<?= htmlspecialchars($storage['first_name']); ?>"
                                    class="w-full px-4 py-3 border border-green-100 rounded-xl focus:ring-2 focus:ring-green-500 outline-none transition bg-white">
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold uppercase tracking-wider text-green-800 mb-2">New Last Name</label>
                                <input type="text" name="last_name" placeholder="<?= htmlspecialchars($storage['last_name']); ?>"
                                    class="w-full px-4 py-3 border border-green-100 rounded-xl focus:ring-2 focus:ring-green-500 outline-none transition bg-white">
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-[10px] font-bold uppercase tracking-wider text-green-800 mb-2">New Storage Details</label>
                                <textarea name="storage_details" rows="2" placeholder="<?= !empty($storage['storage_details']) ? htmlspecialchars($storage['storage_details']) : 'Enter new storage details...'; ?>"
                                    class="w-full px-4 py-3 border border-green-100 rounded-xl focus:ring-2 focus:ring-green-500 outline-none transition bg-white resize-none"></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="pt-6 border-t border-green-50">
                        <h3 class="text-sm font-black text-green-900 uppercase tracking-widest mb-4 flex items-center gap-2">
                            <span class="w-2 h-2 bg-green-500 rounded-full"></span> Access & Media
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-[10px] font-bold uppercase tracking-wider text-green-800 mb-2">Account Status</label>
                                <select name="status" class="w-full px-4 py-3 border border-green-100 rounded-xl focus:ring-2 focus:ring-green-500 outline-none transition bg-white cursor-pointer text-sm font-medium">
                                    <option value="active" <?= $storage['status'] === 'active' ? 'selected' : ''; ?>>Active</option>
                                    <option value="inactive" <?= $storage['status'] === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                                    <option value="pending" <?= $storage['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold uppercase tracking-wider text-green-800 mb-2">Update Profile Image</label>
                                <input type="file" name="profile_image"
                                    class="w-full px-2 py-2 text-sm text-green-800 border border-green-100 rounded-xl cursor-pointer bg-green-50/30 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-bold file:bg-green-600 file:text-white hover:file:bg-green-700 transition">
                            </div>
                        </div>
                    </div>

                    <div class="pt-8 border-t border-green-50 flex gap-4">
                        <button type="submit" 
                            class="flex-1 bg-green-600 hover:bg-green-700 text-white font-bold py-4 rounded-xl transition duration-200 shadow-lg shadow-green-100">
                            Apply Storage Updates
                        </button>
                        <a href="AdminSelfStorageIndex.php" 
                            class="px-8 py-4 bg-white border border-green-200 text-green-700 font-bold rounded-xl hover:bg-green-50 transition text-center">
                            Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include('../../../includes/footer.php'); ?>