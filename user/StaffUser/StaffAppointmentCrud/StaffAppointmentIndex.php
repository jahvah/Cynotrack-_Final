<?php
session_start();
include('../../../includes/config.php');
include('../../../includes/head.php'); 
include('../../../includes/staff_header.php');

// STAFF access only
if (!isset($_SESSION['account_id']) || $_SESSION['role'] !== 'staff') {
    header("Location: ../../../unauthorized.php");
    exit();
}

/* ================= DATA FETCHING ================= */
// Donor Appointments
$donor_query = "SELECT a.appointment_id, a.appointment_date, a.type, a.status, u.first_name, u.last_name
    FROM appointments a JOIN donors_users u ON a.user_id = u.donor_id
    WHERE a.user_type = 'donor' ORDER BY a.appointment_id DESC";
$donor_result = mysqli_query($conn, $donor_query);

// Recipient Appointments
$recipient_query = "SELECT a.appointment_id, a.appointment_date, a.type, a.status, u.first_name, u.last_name
    FROM appointments a JOIN recipients_users u ON a.user_id = u.recipient_id
    WHERE a.user_type = 'recipient' ORDER BY a.appointment_id DESC";
$recipient_result = mysqli_query($conn, $recipient_query);

/**
 * Helper function to render table rows consistently
 */
function renderAppointmentRow($row, $editPath, $deletePath) {
    $status = $row['status'];
    $isLocked = ($status === 'completed' || $status === 'cancelled');
    
    $badgeClass = match($status) {
        'completed' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
        'cancelled' => 'bg-red-100 text-red-800 border-red-200',
        default     => 'bg-amber-100 text-amber-800 border-amber-200',
    };
    ?>
    <tr class="hover:bg-green-50/30 transition">
        <td class="px-6 py-4">
            <div class="text-sm font-bold text-gray-900">#<?= $row['appointment_id']; ?></div>
            <div class="text-[10px] text-green-600 font-medium uppercase tracking-tight">Ref No.</div>
        </td>
        <td class="px-6 py-4 text-sm text-gray-700 font-medium">
            <?= htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?>
        </td>
        <td class="px-6 py-4">
            <div class="text-xs text-gray-900 font-semibold"><?= date("M d, Y", strtotime($row['appointment_date'])); ?></div>
            <div class="text-[10px] text-gray-500"><?= date("h:i A", strtotime($row['appointment_date'])); ?></div>
        </td>
        <td class="px-6 py-4 text-center">
            <span class="text-xs font-bold text-gray-600 uppercase"><?= htmlspecialchars($row['type']); ?></span>
        </td>
        <td class="px-6 py-4 text-center">
            <span class="px-2.5 py-1 rounded-full text-[10px] font-black uppercase tracking-wider border <?= $badgeClass ?>">
                <?= htmlspecialchars($status); ?>
            </span>
        </td>
        <td class="px-6 py-4 text-right">
            <?php if (!$isLocked): ?>
                <div class="flex justify-end gap-3">
                    <a href="<?= $editPath ?>?id=<?= $row['appointment_id']; ?>" class="text-xs font-bold text-amber-600 hover:text-amber-700 uppercase">Edit</a>
                    <a href="<?= $deletePath ?>?id=<?= $row['appointment_id']; ?>" onclick="return confirm('Delete this appointment?');" class="text-xs font-bold text-red-600 hover:text-red-700 uppercase">Delete</a>
                </div>
            <?php else: ?>
                <span class="text-[10px] font-bold text-gray-400 uppercase italic">Locked</span>
            <?php endif; ?>
        </td>
    </tr>
    <?php
}
?>

<div class="min-h-screen bg-green-50/30 py-10 px-4 sm:px-6 lg:px-8">
    <div class="max-w-7xl mx-auto space-y-8">
        
        <?php if (isset($_SESSION['success'])): ?>
            <div class="p-4 mb-4 text-sm text-green-800 rounded-lg bg-green-100 border border-green-200 font-medium">
                <span class="font-bold">Success!</span> <?= $_SESSION['success']; unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>

        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h1 class="text-3xl font-black text-green-900 tracking-tight">Appointment Management</h1>
                <p class="text-green-600 text-sm">Coordinate donor and recipient schedules.</p>
            </div>
            <a href="../StaffDashboard.php" class="px-4 py-2 text-sm font-semibold text-green-700 bg-white border border-green-200 rounded-lg hover:bg-green-50 transition w-fit">
                ← Back to Dashboard
            </a>
        </div>

        <!-- Donor Appointments Table -->
        <div class="bg-white border border-green-100 rounded-2xl shadow-sm overflow-hidden">
            <div class="p-6 border-b border-green-50 flex flex-col md:flex-row md:items-center justify-between gap-4">
                <h2 class="text-xl font-bold text-green-900">Donor Appointments</h2>
                <a href="StaffAppointmentDonorCrud/StaffAppointmentDonorCreate.php" class="px-4 py-2 text-sm font-semibold text-white bg-green-600 rounded-lg hover:bg-green-700 transition shadow-md shadow-green-100">+ New Donor Appt</a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-green-50/50 border-b border-green-100">
                            <th class="px-6 py-4 text-xs font-bold uppercase text-green-700">ID</th>
                            <th class="px-6 py-4 text-xs font-bold uppercase text-green-700">Donor Name</th>
                            <th class="px-6 py-4 text-xs font-bold uppercase text-green-700">Date & Time</th>
                            <th class="px-6 py-4 text-xs font-bold uppercase text-green-700 text-center">Type</th>
                            <th class="px-6 py-4 text-xs font-bold uppercase text-green-700 text-center">Status</th>
                            <th class="px-6 py-4 text-xs font-bold uppercase text-green-700 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-green-50">
                        <?php if ($donor_result && mysqli_num_rows($donor_result) > 0): ?>
                            <?php while ($row = mysqli_fetch_assoc($donor_result)) renderAppointmentRow($row, 'StaffAppointmentDonorCrud/StaffAppointmentDonorUpdate.php', 'StaffAppointmentDonorCrud/StaffAppointmentDonorDelete.php'); ?>
                        <?php else: ?>
                            <tr><td colspan="6" class="px-6 py-10 text-center text-gray-400 italic">No donor appointments scheduled.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Recipient Appointments Table -->
        <div class="bg-white border border-green-100 rounded-2xl shadow-sm overflow-hidden">
            <div class="p-6 border-b border-green-50 flex flex-col md:flex-row md:items-center justify-between gap-4">
                <h2 class="text-xl font-bold text-green-900">Recipient Appointments</h2>
                <a href="StaffAppointmentRecipientCrud/StaffAppointmentRecipientCreate.php" class="px-4 py-2 text-sm font-semibold text-white bg-green-600 rounded-lg hover:bg-green-700 transition shadow-md shadow-green-100">+ New Recipient Appt</a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-green-50/50 border-b border-green-100">
                            <th class="px-6 py-4 text-xs font-bold uppercase text-green-700">ID</th>
                            <th class="px-6 py-4 text-xs font-bold uppercase text-green-700">Recipient Name</th>
                            <th class="px-6 py-4 text-xs font-bold uppercase text-green-700">Date & Time</th>
                            <th class="px-6 py-4 text-xs font-bold uppercase text-green-700 text-center">Type</th>
                            <th class="px-6 py-4 text-xs font-bold uppercase text-green-700 text-center">Status</th>
                            <th class="px-6 py-4 text-xs font-bold uppercase text-green-700 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-green-50">
                        <?php if ($recipient_result && mysqli_num_rows($recipient_result) > 0): ?>
                            <?php while ($row = mysqli_fetch_assoc($recipient_result)) renderAppointmentRow($row, 'StaffAppointmentRecipientCrud/StaffAppointmentRecipientUpdate.php', 'StaffAppointmentRecipientCrud/StaffAppointmentRecipientDelete.php'); ?>
                        <?php else: ?>
                            <tr><td colspan="6" class="px-6 py-10 text-center text-gray-400 italic">No recipient appointments scheduled.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="flex justify-between items-center px-4">
            <span class="text-[10px] text-green-700 font-bold uppercase tracking-widest">Scheduling Module: Active</span>
            <span class="text-[10px] text-green-600 italic">CryoBank Management System</span>
        </div>
    </div>
</div>

<?php include('../../../includes/footer.php'); ?>