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

// Get logged-in recipient ID
$account_id = $_SESSION['account_id'];
$recipient_query = mysqli_query($conn, "SELECT recipient_id FROM recipients_users WHERE account_id = '$account_id' LIMIT 1");
$recipient_data = mysqli_fetch_assoc($recipient_query);

if (!$recipient_data) {
    echo "<div class='max-w-7xl mx-auto py-10 px-4'><div class='p-4 text-sm text-red-700 bg-red-50 border border-red-100 rounded-lg font-medium'>Recipient record not found.</div></div>";
    include('../../../includes/footer.php');
    exit();
}

$recipient_id = $recipient_data['recipient_id'];

/* ================= RECIPIENT OWN APPOINTMENTS ================= */
$appointment_query = "SELECT 
        appointment_id,
        appointment_date,
        type,
        status
    FROM appointments
    WHERE user_type = 'recipient' 
      AND user_id = '$recipient_id'
    ORDER BY appointment_date DESC";

$appointment_result = mysqli_query($conn, $appointment_query);
?>

<div class="max-w-7xl mx-auto py-10 px-4">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
        <div>
            <h2 class="text-3xl font-black text-green-900 tracking-tight">My Appointments</h2>
            <p class="text-green-600 font-medium mt-1">Manage and track your scheduled clinic visits.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="../RecipientDashboard.php" class="px-5 py-2.5 text-sm font-bold text-slate-600 hover:text-slate-800 transition flex items-center gap-2">
                ← Dashboard
            </a>
            <a href="RecipientAppointmentCreate.php" class="bg-green-600 hover:bg-green-700 text-white px-6 py-2.5 rounded-xl font-bold text-sm transition shadow-lg shadow-green-100 flex items-center gap-2">
                <span class="text-lg">+</span> Schedule New
            </a>
        </div>
    </div>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="mb-6 p-4 text-sm text-red-700 bg-red-50 border border-red-100 rounded-xl font-medium">
            <?= $_SESSION['error']; ?>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="mb-6 p-4 text-sm text-emerald-700 bg-emerald-50 border border-emerald-100 rounded-xl font-medium">
            <?= $_SESSION['success']; ?>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <div class="bg-white border border-green-100 rounded-2xl shadow-xl shadow-green-100/20 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-green-50/50 border-b border-green-100">
                        <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-green-800">ID</th>
                        <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-green-800">Date & Time</th>
                        <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-green-800">Type</th>
                        <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-green-800 text-center">Status</th>
                        <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-green-800 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-green-50">
                    <?php if ($appointment_result && mysqli_num_rows($appointment_result) > 0): ?>
                        <?php while ($row = mysqli_fetch_assoc($appointment_result)): 
                            $status = $row['status'];
                            // Status Badge Styling
                            $statusClasses = [
                                'completed' => 'bg-emerald-100 text-emerald-700 border-emerald-200',
                                'cancelled' => 'bg-red-100 text-red-700 border-red-200',
                                'scheduled' => 'bg-amber-100 text-amber-700 border-amber-200'
                            ];
                            $badgeStyle = $statusClasses[$status] ?? 'bg-slate-100 text-slate-700 border-slate-200';
                        ?>
                            <tr class="hover:bg-green-50/30 transition">
                                <td class="px-6 py-4 text-sm font-bold text-slate-400">#<?= $row['appointment_id']; ?></td>
                                <td class="px-6 py-4">
                                    <div class="text-sm font-bold text-slate-700"><?= date("M d, Y", strtotime($row['appointment_date'])); ?></div>
                                    <div class="text-xs text-slate-400 font-medium"><?= date("h:i A", strtotime($row['appointment_date'])); ?></div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="text-sm font-semibold text-slate-600 uppercase tracking-tight"><?= ucfirst($row['type']); ?></span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase border <?= $badgeStyle ?>">
                                        <?= ucfirst($status); ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <?php if ($status === 'scheduled'): ?>
                                        <div class="flex justify-end gap-2">
                                            <a href="RecipientAppointmentUpdate.php?id=<?= $row['appointment_id']; ?>" 
                                               class="p-2 text-amber-600 hover:bg-amber-50 rounded-lg transition" title="Edit Appointment">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                                </svg>
                                            </a>
                                            <a href="RecipientAppointmentDelete.php?id=<?= $row['appointment_id']; ?>" 
                                               onclick="return confirm('Are you sure you want to delete this appointment?');"
                                               class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition" title="Delete">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </a>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-slate-300 text-xs font-medium italic">No actions available</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center">
                                <div class="text-slate-400 font-medium">No appointments found.</div>
                                <a href="RecipientAppointmentCreate.php" class="text-green-600 text-sm font-bold hover:underline mt-2 inline-block">Schedule your first visit now</a>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <div class="bg-green-50/30 px-8 py-4 border-t border-green-50">
            <p class="text-[10px] text-green-600 text-center uppercase tracking-widest font-bold font-mono">End of Record</p>
        </div>
    </div>
</div>

<?php include('../../../includes/footer.php'); ?>