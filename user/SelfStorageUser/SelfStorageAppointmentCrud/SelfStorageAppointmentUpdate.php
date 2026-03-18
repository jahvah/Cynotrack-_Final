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

// Check for appointment ID
if (!isset($_GET['id'])) {
    header("Location: SelfStorageAppointmentIndex.php");
    exit();
}

$appointment_id = intval($_GET['id']);
$account_id = $_SESSION['account_id'];

// Get logged-in storage user ID
$storage_stmt = $conn->prepare("SELECT storage_user_id, first_name, last_name FROM self_storage_users WHERE account_id = ? LIMIT 1");
$storage_stmt->bind_param("i", $account_id);
$storage_stmt->execute();
$storage_result = $storage_stmt->get_result();

if ($storage_result->num_rows === 0) {
    header("Location: SelfStorageAppointmentIndex.php");
    exit();
}

$storage_data = $storage_result->fetch_assoc();
$storage_user_id = $storage_data['storage_user_id'];

// Fetch ONLY this storage user's appointment
$stmt = $conn->prepare("
    SELECT appointment_date, status, type
    FROM appointments
    WHERE appointment_id = ? 
      AND user_type = 'storage'
      AND user_id = ?
");
$stmt->bind_param("ii", $appointment_id, $storage_user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: SelfStorageAppointmentIndex.php");
    exit();
}

$appointment = $result->fetch_assoc();
?>

<div class="max-w-7xl mx-auto py-10 px-4">
    <div class="mb-6">
        <a href="SelfStorageAppointmentIndex.php" class="text-sm font-bold text-green-700 hover:text-green-800 transition flex items-center gap-1">
            ← Back to My Appointments
        </a>
    </div>

    <div class="max-w-2xl mx-auto">
        <div class="bg-white border border-green-100 rounded-2xl shadow-xl shadow-green-100/20 overflow-hidden">
            
            <div class="bg-green-50/50 border-b border-green-100 px-8 py-6">
                <h2 class="text-2xl font-bold text-green-900">Update Appointment</h2>
                <p class="text-green-600 text-sm mt-1 font-medium">Reschedule your storage or release visit. Note that date changes may be reviewed by staff.</p>
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
                    <input type="hidden" name="action" value="update_storage_appointment">
                    <input type="hidden" name="appointment_id" value="<?= $appointment_id; ?>">

                    <div>
                        <h3 class="text-sm font-black text-green-900 uppercase tracking-widest mb-4 flex items-center gap-2">
                            <span class="w-2 h-2 bg-green-500 rounded-full"></span> Appointment Details
                        </h3>
                        
                        <div class="grid grid-cols-1 gap-6">
                            <div>
                                <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-2">Storage User</label>
                                <input type="text" 
                                    value="<?= htmlspecialchars($storage_data['first_name'] . ' ' . $storage_data['last_name']); ?>" 
                                    class="w-full px-4 py-3 border border-gray-100 rounded-xl bg-gray-50 text-gray-500 cursor-not-allowed outline-none" 
                                    disabled>
                            </div>

                            <div>
                                <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-2">Appointment Type</label>
                                <div class="relative">
                                    <select class="w-full px-4 py-3 border border-gray-100 rounded-xl bg-gray-50 text-gray-500 cursor-not-allowed outline-none appearance-none font-medium" disabled>
                                        <option value="storage" <?= ($appointment['type'] === 'storage') ? 'selected' : ''; ?>>Storage</option>
                                        <option value="release" <?= ($appointment['type'] === 'release') ? 'selected' : ''; ?>>Release</option>
                                    </select>
                                    <span class="absolute right-4 top-1/2 -translate-y-1/2">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                        </svg>
                                    </span>
                                </div>
                            </div>

                            <div>
                                <label class="block text-[10px] font-bold uppercase tracking-wider text-green-800 mb-2">Appointment Date & Time</label>
                                <input type="datetime-local" 
                                    name="appointment_date"
                                    value="<?= date('Y-m-d\TH:i', strtotime($appointment['appointment_date'])); ?>" 
                                    required
                                    class="w-full px-4 py-3 border border-green-100 rounded-xl focus:ring-2 focus:ring-green-500 outline-none transition bg-green-50/10 text-green-900 font-medium">
                            </div>

                            <div>
                                <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-2">Current Status</label>
                                <input type="text" 
                                    value="<?= ucfirst(htmlspecialchars($appointment['status'])); ?>" 
                                    class="w-full px-4 py-3 border border-gray-100 rounded-xl bg-gray-50 text-gray-500 cursor-not-allowed outline-none font-bold italic" 
                                    disabled>
                            </div>
                        </div>
                    </div>

                    <div class="pt-8 border-t border-green-50">
                        <button type="submit" 
                            class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-4 rounded-xl transition duration-200 shadow-lg shadow-green-100 flex items-center justify-center gap-2 active:scale-[0.98]">
                            <span>Save Changes</span>
                        </button>
                    </div>
                </form>
            </div>

            <div class="bg-green-50/30 px-8 py-4 border-t border-green-50">
                <p class="text-[10px] text-green-600 text-center uppercase tracking-widest font-bold">Secure Storage Access Management</p>
            </div>
        </div>
    </div>
</div>

<?php include('../../../includes/footer.php'); ?>