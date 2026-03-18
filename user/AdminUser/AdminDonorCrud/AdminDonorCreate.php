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

<div class="max-w-7xl mx-auto py-10 px-4">
    <div class="mb-6">
        <a href="AdminDonorIndex.php" class="text-sm font-bold text-green-700 hover:text-green-800 transition flex items-center gap-1">
            ← Back to Donor List
        </a>
    </div>

    <div class="max-w-4xl mx-auto">
        <div class="bg-white border border-green-100 rounded-2xl shadow-xl shadow-green-100/20 overflow-hidden">
            
            <div class="bg-green-50/50 border-b border-green-100 px-8 py-6">
                <h2 class="text-2xl font-bold text-green-900">Add New Donor</h2>
                <p class="text-green-600 text-sm mt-1 font-medium">Register a new donor profile and medical records.</p>
            </div>

            <div class="p-8">
                <?php if (isset($_SESSION['error'])): ?>
                    <div class="mb-6 p-4 text-sm text-red-700 bg-red-50 border border-red-100 rounded-lg font-medium">
                        <?= $_SESSION['error']; ?>
                    </div>
                    <?php unset($_SESSION['error']); ?>
                <?php endif; ?>

                <?php if (isset($_SESSION['success'])): ?>
                    <div class="mb-6 p-4 text-sm text-emerald-700 bg-emerald-50 border border-emerald-100 rounded-lg font-medium">
                        <?= $_SESSION['success']; ?>
                    </div>
                    <?php unset($_SESSION['success']); ?>
                <?php endif; ?>

                <form action="AdminDonorStore.php" method="POST" enctype="multipart/form-data" class="space-y-8">
                    <input type="hidden" name="action" value="AdminDonorStore">

                    <div>
                        <h3 class="text-sm font-black text-green-900 uppercase tracking-widest mb-4 flex items-center gap-2">
                            <span class="w-2 h-2 bg-green-500 rounded-full"></span> Account Information
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-[10px] font-bold uppercase tracking-wider text-green-800 mb-2">Username</label>
                                <input type="text" name="username" required placeholder="User123"
                                    class="w-full px-4 py-3 border border-green-100 rounded-xl focus:ring-2 focus:ring-green-500 outline-none transition bg-green-50/10">
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold uppercase tracking-wider text-green-800 mb-2">Email Address</label>
                                <input type="email" name="email" required placeholder="donor@example.com"
                                    class="w-full px-4 py-3 border border-green-100 rounded-xl focus:ring-2 focus:ring-green-500 outline-none transition bg-green-50/10">
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold uppercase tracking-wider text-green-800 mb-2">Password</label>
                                <input type="password" name="password" required placeholder="••••••••"
                                    class="w-full px-4 py-3 border border-green-100 rounded-xl focus:ring-2 focus:ring-green-500 outline-none transition bg-green-50/10">
                            </div>
                        </div>
                    </div>

                    <div class="pt-6 border-t border-green-50">
                        <h3 class="text-sm font-black text-green-900 uppercase tracking-widest mb-4 flex items-center gap-2">
                            <span class="w-2 h-2 bg-green-500 rounded-full"></span> Personal & Medical
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div>
                                <label class="block text-[10px] font-bold uppercase tracking-wider text-green-800 mb-2">First Name</label>
                                <input type="text" name="first_name" required
                                    class="w-full px-4 py-3 border border-green-100 rounded-xl focus:ring-2 focus:ring-green-500 outline-none transition">
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold uppercase tracking-wider text-green-800 mb-2">Last Name</label>
                                <input type="text" name="last_name" required
                                    class="w-full px-4 py-3 border border-green-100 rounded-xl focus:ring-2 focus:ring-green-500 outline-none transition">
                            </div>
                        </div>
                        <div class="mb-4">
                            <label class="block text-[10px] font-bold uppercase tracking-wider text-green-800 mb-2">Medical History Summary</label>
                            <textarea name="medical_history" rows="3" placeholder="Brief overview of medical conditions..."
                                class="w-full px-4 py-3 border border-green-100 rounded-xl focus:ring-2 focus:ring-green-500 outline-none transition resize-none"></textarea>
                        </div>
                        <div class="w-full md:w-1/2">
                            <label class="block text-[10px] font-bold uppercase tracking-wider text-green-800 mb-2">Evaluation Status</label>
                            <select name="evaluation_status" required
                                class="w-full px-4 py-3 border border-green-100 rounded-xl focus:ring-2 focus:ring-green-500 outline-none transition bg-white cursor-pointer">
                                <option value="pending" selected>Pending</option>
                                <option value="approved">Approved</option>
                                <option value="rejected">Rejected</option>
                            </select>
                        </div>
                    </div>

                    <div class="pt-6 border-t border-green-50">
                        <h3 class="text-sm font-black text-green-900 uppercase tracking-widest mb-4 flex items-center gap-2">
                            <span class="w-2 h-2 bg-green-500 rounded-full"></span> Physical Characteristics
                        </h3>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                            <div>
                                <label class="block text-[10px] font-bold uppercase tracking-wider text-green-800 mb-2">Height (cm)</label>
                                <input type="number" name="height_cm" min="50" max="250"
                                    class="w-full px-4 py-3 border border-green-100 rounded-xl focus:ring-2 focus:ring-green-500 outline-none transition">
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold uppercase tracking-wider text-green-800 mb-2">Weight (kg)</label>
                                <input type="number" name="weight_kg" min="20" max="200"
                                    class="w-full px-4 py-3 border border-green-100 rounded-xl focus:ring-2 focus:ring-green-500 outline-none transition">
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold uppercase tracking-wider text-green-800 mb-2">Blood Type</label>
                                <input type="text" name="blood_type" placeholder="e.g. O+"
                                    class="w-full px-4 py-3 border border-green-100 rounded-xl focus:ring-2 focus:ring-green-500 outline-none transition">
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold uppercase tracking-wider text-green-800 mb-2">Ethnicity</label>
                                <input type="text" name="ethnicity"
                                    class="w-full px-4 py-3 border border-green-100 rounded-xl focus:ring-2 focus:ring-green-500 outline-none transition">
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                            <div>
                                <label class="block text-[10px] font-bold uppercase tracking-wider text-green-800 mb-2">Eye Color</label>
                                <input type="text" name="eye_color"
                                    class="w-full px-4 py-3 border border-green-100 rounded-xl focus:ring-2 focus:ring-green-500 outline-none transition">
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold uppercase tracking-wider text-green-800 mb-2">Hair Color</label>
                                <input type="text" name="hair_color"
                                    class="w-full px-4 py-3 border border-green-100 rounded-xl focus:ring-2 focus:ring-green-500 outline-none transition">
                            </div>
                        </div>
                    </div>

                    <div class="pt-6 border-t border-green-50">
                        <h3 class="text-sm font-black text-green-900 uppercase tracking-widest mb-4 flex items-center gap-2">
                            <span class="w-2 h-2 bg-green-500 rounded-full"></span> Documents & Photos
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-[10px] font-bold uppercase tracking-wider text-green-800 mb-2">Profile Image</label>
                                <input type="file" name="profile_image" required
                                    class="w-full px-2 py-2 text-sm text-green-800 border border-green-100 rounded-xl cursor-pointer bg-green-50/30 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-bold file:bg-green-600 file:text-white hover:file:bg-green-700 transition">
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold uppercase tracking-wider text-green-800 mb-2">Medical History PDF <span class="text-gray-400 capitalize font-normal"></span></label>
                                <input type="file" name="medical_document" accept="application/pdf"
                                    class="w-full px-2 py-2 text-sm text-green-800 border border-green-100 rounded-xl cursor-pointer bg-green-50/30 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-bold file:bg-emerald-800 file:text-white hover:file:bg-emerald-900 transition" required>
                            </div>
                        </div>
                    </div>

                    <div class="pt-8 border-t border-green-50">
                        <button type="submit" 
                            class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-4 rounded-xl transition duration-200 shadow-lg shadow-green-100 flex items-center justify-center gap-2">
                            <span>Register New Donor</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include('../../../includes/footer.php'); ?>