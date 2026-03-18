<?php
session_start();
include('../../../includes/config.php');
include('../../../includes/head.php'); 
include('../../../includes/header.php');

// SELF-STORAGE access only
if (!isset($_SESSION['account_id']) || $_SESSION['role'] !== 'self-storage') {
    header("Location: ../../../unauthorized.php");
    exit();
}

// Get logged-in storage user ID
$account_id = $_SESSION['account_id'];
$storage_query = mysqli_query($conn, "SELECT storage_user_id FROM self_storage_users WHERE account_id = '$account_id' LIMIT 1");
$storage_data = mysqli_fetch_assoc($storage_query);

if (!$storage_data) {
    echo "<div class='max-w-7xl mx-auto py-10 px-4'><div class='p-4 text-sm text-red-700 bg-red-50 border border-red-100 rounded-lg font-medium'>Storage user record not found.</div></div>";
    include('../../../includes/footer.php');
    exit();
}

$storage_user_id = $storage_data['storage_user_id'];
?>

<div class="max-w-7xl mx-auto py-10 px-4">
    <div class="mb-6">
        <a href="SelfStorageAppointmentIndex.php" class="text-sm font-bold text-green-700 hover:text-green-800 transition flex items-center gap-1">
            ← Back to Appointment Dashboard
        </a>
    </div>

    <div class="max-w-2xl mx-auto">
        <div class="bg-white border border-green-100 rounded-2xl shadow-xl shadow-green-100/20 overflow-hidden">
            
            <div class="bg-green-50/50 border-b border-green-100 px-8 py-6">
                <h2 class="text-2xl font-bold text-green-900">Create Appointment</h2>
                <p class="text-green-600 text-sm mt-1 font-medium">Select your appointment type and preferred schedule.</p>
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

                <form action="SelfStorageAppointmentStore.php" method="POST" class="space-y-8">
                    <input type="hidden" name="action" value="create_storage_appointment">
                    <input type="hidden" name="storage_user_id" value="<?= $storage_user_id; ?>">

                    <div>
                        <h3 class="text-sm font-black text-green-900 uppercase tracking-widest mb-4 flex items-center gap-2">
                            <span class="w-2 h-2 bg-green-500 rounded-full"></span> Appointment Details
                        </h3>
                        
                        <div class="space-y-6">
                            <div>
                                <label class="block text-[10px] font-bold uppercase tracking-wider text-green-800 mb-2">
                                    Appointment Type
                                </label>
                                <select name="type" required
                                    class="w-full px-4 py-3 border border-green-100 rounded-xl focus:ring-2 focus:ring-green-500 outline-none transition bg-white text-green-900 text-sm appearance-none">
                                    <option value="" disabled selected>-- Select Type --</option>
                                    <option value="storage">Storage</option>
                                    <option value="release">Release</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-[10px] font-bold uppercase tracking-wider text-green-800 mb-2">
                                    Appointment Date & Time
                                </label>
                                <input type="datetime-local" name="appointment_date" required
                                    class="w-full px-4 py-3 border border-green-100 rounded-xl focus:ring-2 focus:ring-green-500 outline-none transition bg-white text-green-900 text-sm">
                                <p class="mt-2 text-xs text-gray-400 font-medium">Please ensure the selected time is within facility operating hours.</p>
                            </div>
                        </div>
                    </div>

                    <div class="pt-8 border-t border-green-50">
                        <button type="submit" 
                            class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-4 rounded-xl transition duration-200 shadow-lg shadow-green-100 flex items-center justify-center gap-2 active:scale-[0.98]">
                            <span>Confirm & Create Appointment</span>
                        </button>
                    </div>
                </form>
            </div>

            <div class="bg-green-50/30 px-8 py-4 border-t border-green-50 text-center">
                <p class="text-[10px] text-green-600 uppercase tracking-widest font-bold">Secure Storage Portal</p>
            </div>
        </div>
    </div>
</div>

<?php include('../../../includes/footer.php'); ?>