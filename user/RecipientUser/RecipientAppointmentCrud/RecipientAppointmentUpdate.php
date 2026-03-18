<?php
session_start();
include('../../../includes/config.php');
include('../../../includes/head.php');
include('../../../includes/recipient_header.php');

// RECIPIENT access only
if (!isset($_SESSION['account_id']) || $_SESSION['role'] !== 'recipient') {
    header("Location: ../../../unauthorized.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: RecipientAppointmentIndex.php");
    exit();
}

$appointment_id = intval($_GET['id']);
$account_id = $_SESSION['account_id'];

// Fetch appointment and verify ownership
$stmt = $conn->prepare("
    SELECT 
        a.appointment_id,
        a.appointment_date,
        a.type,
        a.status,
        a.created_at,
        r.recipient_id,
        r.first_name,
        r.last_name
    FROM appointments a
    JOIN recipients_users r ON a.user_id = r.recipient_id
    WHERE a.appointment_id = ? 
      AND a.user_type = 'recipient' 
      AND r.account_id = ?
");
$stmt->bind_param("is", $appointment_id, $account_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: RecipientAppointmentIndex.php");
    exit();
}

$appointment = $result->fetch_assoc();

// Only allow editing if the appointment is still 'scheduled'
if ($appointment['status'] !== 'scheduled') {
    $_SESSION['error'] = "Completed or cancelled appointments cannot be modified.";
    header("Location: RecipientAppointmentIndex.php");
    exit();
}

$formatted_date = date('Y-m-d\TH:i', strtotime($appointment['appointment_date']));
?>

<div class="max-w-7xl mx-auto py-10 px-4">
    <div class="mb-6">
        <a href="RecipientAppointmentIndex.php" class="text-sm font-bold text-green-700 hover:text-green-800 transition flex items-center gap-1">
            ← Back to My Appointments
        </a>
    </div>

    <div class="max-w-2xl mx-auto">
        <div class="bg-white border border-green-100 rounded-2xl shadow-xl shadow-green-100/20 overflow-hidden">

            <div class="bg-green-50/50 border-b border-green-100 px-8 py-6">
                <div class="flex justify-between items-start">
                    <div>
                        <h2 class="text-2xl font-bold text-green-900">Reschedule Appointment</h2>
                        <p class="text-green-600 text-sm mt-1 font-medium">Update your visit details for Appointment #<?= $appointment['appointment_id']; ?></p>
                    </div>
                    <span class="px-3 py-1 bg-amber-100 text-amber-700 border border-amber-200 rounded-full text-[10px] font-black uppercase tracking-widest">
                        <?= $appointment['status']; ?>
                    </span>
                </div>
            </div>

            <div class="p-8">
                <?php if (isset($_SESSION['error'])): ?>
                    <div class="mb-6 p-4 text-sm text-red-700 bg-red-50 border border-red-100 rounded-lg font-medium">
                        <?= $_SESSION['error']; unset($_SESSION['error']); ?>
                    </div>
                <?php endif; ?>

                <form action="RecipientAppointmentStore.php" method="POST" class="space-y-8">
                    <input type="hidden" name="action" value="update_recipient_appointment">
                    <input type="hidden" name="appointment_id" value="<?= $appointment['appointment_id']; ?>">

                    <div>
                        <h3 class="text-sm font-black text-green-900 uppercase tracking-widest mb-4 flex items-center gap-2">
                            <span class="w-2 h-2 bg-green-500 rounded-full"></span> Change Logistics
                        </h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-[10px] font-bold uppercase tracking-wider text-green-800 mb-2">New Date & Time</label>
                                <input type="datetime-local" name="appointment_date" required
                                    value="<?= htmlspecialchars($formatted_date); ?>"
                                    class="w-full px-4 py-3 border border-green-100 rounded-xl focus:ring-2 focus:ring-green-500 outline-none transition bg-green-50/10 text-green-900">
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold uppercase tracking-wider text-green-800 mb-2">Visit Type</label>
                                <select name="type" required
                                    class="w-full px-4 py-3 border border-green-100 rounded-xl focus:ring-2 focus:ring-green-500 outline-none transition bg-white cursor-pointer font-medium text-slate-700">
                                    <option value="consultation" <?= $appointment['type'] === 'consultation' ? 'selected' : ''; ?>>Consultation</option>
                                    <option value="release"      <?= $appointment['type'] === 'release'      ? 'selected' : ''; ?>>Release</option>
                                    <option value="donation"     <?= $appointment['type'] === 'donation'     ? 'selected' : ''; ?>>Donation</option>
                                    <option value="storage"      <?= $appointment['type'] === 'storage'      ? 'selected' : ''; ?>>Storage</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="pt-6 border-t border-green-50">
                        <div class="bg-slate-50 border border-slate-100 p-4 rounded-xl flex justify-between items-center">
                            <div>
                                <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">Originally Booked On</label>
                                <p class="text-xs font-bold text-slate-500 uppercase">
                                    <?= date('F d, Y — h:i A', strtotime($appointment['created_at'])); ?>
                                </p>
                            </div>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>

                    <div class="pt-8 border-t border-green-50 flex flex-col md:flex-row gap-4">
                        <button type="submit"
                            class="flex-1 bg-green-600 hover:bg-green-700 text-white font-bold py-4 rounded-xl transition duration-200 shadow-lg shadow-green-100">
                            Save Changes
                        </button>
                        <a href="RecipientAppointmentIndex.php"
                           class="px-8 py-4 border border-green-200 text-green-700 font-bold rounded-xl hover:bg-green-50 transition text-center">
                            Discard Changes
                        </a>
                    </div>
                </form>
            </div>

            <div class="bg-green-50/30 px-8 py-4 border-t border-green-50 text-center">
                <p class="text-[10px] text-green-600 uppercase tracking-widest font-bold font-mono">Verified Session Management</p>
            </div>
        </div>
    </div>
</div>

<?php include('../../../includes/footer.php'); ?>