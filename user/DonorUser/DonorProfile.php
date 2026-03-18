<?php
session_start();
include('../../includes/config.php'); 
include('../../includes/head.php');

// 1. Security Check
if (!isset($_SESSION['account_id']) || $_SESSION['role'] !== 'donor') {
    header("Location: ../login.php");
    exit();
}

$accountId = $_SESSION['account_id'];

// 2. Fetch Current Data for Background Placeholders
$stmt = $conn->prepare("SELECT * FROM donors_users WHERE account_id = ?");
$stmt->bind_param("i", $accountId);
$stmt->execute();
$donor = $stmt->get_result()->fetch_assoc();

if (!$donor) {
    die("<div class='p-10 text-center text-red-600 font-bold'>Donor profile not found.</div>");
}
?>

<div class="max-w-7xl mx-auto py-10 px-4">
    <div class="max-w-3xl mx-auto">
        <div class="bg-white border border-green-100 rounded-2xl shadow-xl shadow-green-100/20 overflow-hidden">
            
            <div class="bg-green-50/50 border-b border-green-100 px-8 py-6 flex justify-between items-center">
                <div>
                    <h2 class="text-2xl font-bold text-green-900">Complete Profile</h2>
                    <p class="text-green-600 text-sm mt-1 font-medium">Please provide your medical and physical details.</p>
                </div>
                <div class="shrink-0">
                    <?php if (!empty($donor['profile_image'])): ?>
                        <img id="preview" src="../../uploads/<?= htmlspecialchars($donor['profile_image']); ?>" class="w-16 h-16 rounded-2xl object-cover border-2 border-white shadow-md ring-1 ring-green-100">
                    <?php else: ?>
                        <div id="preview-placeholder" class="w-16 h-16 rounded-2xl bg-green-100 flex items-center justify-center text-green-600 font-bold border-2 border-white shadow-sm">
                            <?= strtoupper(substr($donor['first_name'] ?? 'D', 0, 1)); ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="p-8">
                <form action="DonorStore.php" method="POST" enctype="multipart/form-data" class="space-y-8">
                    <input type="hidden" name="action" value="update_profile">

                    <div>
                        <h3 class="text-sm font-black text-green-900 uppercase tracking-widest mb-4 flex items-center gap-2">
                            <span class="w-2 h-2 bg-green-500 rounded-full"></span> Physical Characteristics
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-[10px] font-bold uppercase tracking-wider text-green-800 mb-2">Height (cm)</label>
                                <input type="number" name="height_cm" placeholder="<?= $donor['height_cm'] ?: 'Enter height'; ?>"required
                                    class="w-full px-4 py-3 border border-green-100 rounded-xl focus:ring-2 focus:ring-green-500 outline-none transition text-sm">
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold uppercase tracking-wider text-green-800 mb-2">Weight (kg)</label>
                                <input type="number" name="weight_kg" placeholder="<?= $donor['weight_kg'] ?: 'Enter weight'; ?>" required
                                    class="w-full px-4 py-3 border border-green-100 rounded-xl focus:ring-2 focus:ring-green-500 outline-none transition text-sm">
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold uppercase tracking-wider text-green-800 mb-2">Eye Color</label>
                                <input type="text" name="eye_color" placeholder="<?= htmlspecialchars($donor['eye_color'] ?: 'e.g. Brown'); ?>" required
                                    class="w-full px-4 py-3 border border-green-100 rounded-xl focus:ring-2 focus:ring-green-500 outline-none transition text-sm">
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold uppercase tracking-wider text-green-800 mb-2">Hair Color</label>
                                <input type="text" name="hair_color" placeholder="<?= htmlspecialchars($donor['hair_color'] ?: 'e.g. Black'); ?>" required
                                    class="w-full px-4 py-3 border border-green-100 rounded-xl focus:ring-2 focus:ring-green-500 outline-none transition text-sm">
                            </div>
                        </div>
                    </div>

                    <div class="pt-6 border-t border-green-50">
                        <h3 class="text-sm font-black text-green-900 uppercase tracking-widest mb-4 flex items-center gap-2">
                            <span class="w-2 h-2 bg-green-500 rounded-full"></span> Medical Information
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-[10px] font-bold uppercase tracking-wider text-green-800 mb-2">Blood Type</label>
                                <input type="text" name="blood_type" placeholder="<?= htmlspecialchars($donor['blood_type'] ?: 'e.g. O+'); ?>" required 
                                    class="w-full px-4 py-3 border border-green-100 rounded-xl focus:ring-2 focus:ring-green-500 outline-none transition text-sm">
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold uppercase tracking-wider text-green-800 mb-2">Ethnicity</label>
                                <input type="text" name="ethnicity" placeholder="<?= htmlspecialchars($donor['ethnicity'] ?: 'Enter ethnicity'); ?>" required
                                    class="w-full px-4 py-3 border border-green-100 rounded-xl focus:ring-2 focus:ring-green-500 outline-none transition text-sm">
                            </div>
                        </div>
                    </div>

                    <div class="pt-6 border-t border-green-50">
                        <h3 class="text-sm font-black text-green-900 uppercase tracking-widest mb-4 flex items-center gap-2">
                            <span class="w-2 h-2 bg-green-500 rounded-full"></span> Documents & Media
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-[10px] font-bold uppercase tracking-wider text-green-800 mb-2">Profile Image</label>
                                <input type="file" name="profile_image" id="imgInput" accept="image/*" required
                                    class="w-full px-2 py-2 text-sm text-green-800 border border-green-100 rounded-xl cursor-pointer bg-green-50/30 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-bold file:bg-green-600 file:text-white hover:file:bg-green-700 transition">
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold uppercase tracking-wider text-green-800 mb-2">Medical Document (PDF)</label>
                                <input type="file" name="medical_document" accept="application/pdf" required
                                    class="w-full px-2 py-2 text-sm text-green-800 border border-green-100 rounded-xl cursor-pointer bg-green-50/30 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-bold file:bg-slate-600 file:text-white hover:file:bg-slate-700 transition">
                                <?php if (!empty($donor['medical_document'])): ?>
                                    <a href="../../medical_docs/<?= htmlspecialchars($donor['medical_document']); ?>" target="_blank" class="inline-block mt-2 text-[10px] font-bold text-green-700 hover:underline">
                                        View Current PDF →
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <div class="pt-8 border-t border-green-50 flex gap-4">
                        <button type="submit" 
                            class="flex-1 bg-green-600 hover:bg-green-700 text-white font-bold py-4 rounded-xl transition duration-200 shadow-lg shadow-green-100 active:scale-95">
                            Save Donor Profile
                        </button>
                        <a href="DonorDashboard.php" 
                            class="px-8 py-4 bg-white border border-green-200 text-green-700 font-bold rounded-xl hover:bg-green-50 transition text-center">
                            Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    // Live Image Preview
    document.getElementById('imgInput').onchange = evt => {
        const [file] = evt.target.files;
        if (file) {
            const preview = document.getElementById('preview') || document.getElementById('preview-placeholder');
            if (preview.tagName === 'DIV') {
                const newImg = document.createElement('img');
                newImg.id = 'preview';
                newImg.className = 'w-16 h-16 rounded-2xl object-cover border-2 border-white shadow-md ring-1 ring-green-100';
                preview.parentNode.replaceChild(newImg, preview);
                newImg.src = URL.createObjectURL(file);
            } else {
                preview.src = URL.createObjectURL(file);
            }
        }
    }
</script>

<?php include("../../includes/footer.php"); ?>