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
?>

<div class="min-h-screen bg-green-50/30 py-10 px-4 sm:px-6 lg:px-8">
    
    <div class="max-w-2xl mx-auto bg-white border border-green-100 rounded-2xl shadow-sm overflow-hidden">
        
        <div class="p-6 border-b border-green-50 bg-white">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-bold text-green-900">Add New Storage User</h2>
                    <p class="text-green-600 text-sm mt-1">Fill in the details to create a new self-storage account.</p>
                </div>
                <a href="AdminSelfStorageIndex.php" class="text-sm font-semibold text-green-700 hover:text-green-800 transition">
                    ← Back
                </a>
            </div>
        </div>

        <div class="p-8">
            <?php if (isset($_SESSION['error'])): ?>
                <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 text-red-700 text-sm font-medium rounded">
                    <?= $_SESSION['error']; ?>
                </div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>

            <?php if (isset($_SESSION['success'])): ?>
                <div class="mb-6 p-4 bg-emerald-50 border-l-4 border-emerald-500 text-emerald-700 text-sm font-medium rounded">
                    <?= $_SESSION['success']; ?>
                </div>
                <?php unset($_SESSION['success']); ?>
            <?php endif; ?>

            <form action="AdminSelfStorageStore.php" method="POST" enctype="multipart/form-data" class="space-y-5">
                <input type="hidden" name="action" value="AdminSelfStorageStore">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-xs font-bold text-green-700 uppercase tracking-wider mb-2">Username</label>
                        <input type="text" name="username" required 
                               class="w-full px-4 py-2 border border-green-100 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 outline-none transition">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-green-700 uppercase tracking-wider mb-2">Email Address</label>
                        <input type="email" name="email" required 
                               class="w-full px-4 py-2 border border-green-100 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 outline-none transition">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-green-700 uppercase tracking-wider mb-2">Password</label>
                    <input type="password" name="password" required 
                           class="w-full px-4 py-2 border border-green-100 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 outline-none transition">
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-xs font-bold text-green-700 uppercase tracking-wider mb-2">First Name</label>
                        <input type="text" name="first_name" required 
                               class="w-full px-4 py-2 border border-green-100 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 outline-none transition">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-green-700 uppercase tracking-wider mb-2">Last Name</label>
                        <input type="text" name="last_name" required 
                               class="w-full px-4 py-2 border border-green-100 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 outline-none transition">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-green-700 uppercase tracking-wider mb-2">Account Status</label>
                    <select name="status" required 
                            class="w-full px-4 py-2 border border-green-100 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 outline-none transition bg-white">
                        <option value="active" selected>Active</option>
                        <option value="inactive">Inactive</option>
                        <option value="pending">Pending</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-green-700 uppercase tracking-wider mb-2">Profile Image</label>
                    <input type="file" name="profile_image" required 
                           class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-green-50 file:text-green-700 hover:file:bg-green-100 transition">
                </div>

                <div>
                    <label class="block text-xs font-bold text-green-700 uppercase tracking-wider mb-2">Storage Details</label>
                    <textarea name="storage_details" rows="3" placeholder="Enter specific storage unit info or allocated space..." required
                              class="w-full px-4 py-2 border border-green-100 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 outline-none transition"></textarea>
                </div>

                <div class="pt-4">
                    <button type="submit" class="w-full py-3 px-4 bg-green-600 hover:bg-green-700 text-white font-bold rounded-lg shadow-md shadow-green-100 transition-all transform active:scale-[0.98]">
                        Create Self-Storage User
                    </button>
                </div>
            </form>
        </div>

        <div class="bg-green-50/30 px-8 py-4 border-t border-green-50">
            <p class="text-[10px] text-green-600 text-center uppercase tracking-widest font-bold">Secure Administrative Entry</p>
        </div>
    </div>
</div>

<?php include('../../../includes/footer.php'); ?>