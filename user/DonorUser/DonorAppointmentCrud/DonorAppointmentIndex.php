<?php
session_start();
include('../../../includes/config.php');
include('../../../includes/head.php');
include('../../../includes/donor_header.php');

// DONOR access only
if (!isset($_SESSION['account_id']) || $_SESSION['role'] !== 'donor') {
    header("Location: ../../../unauthorized.php");
    exit();
}

// Get logged-in donor ID
$account_id = $_SESSION['account_id'];
$donor_query = mysqli_query($conn, "SELECT donor_id FROM donors_users WHERE account_id = '$account_id' LIMIT 1");
$donor_data = mysqli_fetch_assoc($donor_query);

if (!$donor_data) {
    echo "<div class='max-w-7xl mx-auto py-10 px-4'><div class='p-4 text-sm text-red-700 bg-red-50 border border-red-100 rounded-lg font-medium'>Donor record not found.</div></div>";
    include('../../../includes/footer.php');
    exit();
}

$donor_id = $donor_data['donor_id'];

/* ================= DONOR OWN APPOINTMENTS ================= */
$appointment_query = "SELECT 
        appointment_id,
        appointment_date,
        type,
        status
    FROM appointments
    WHERE user_type = 'donor' 
      AND user_id = '$donor_id'
    ORDER BY appointment_date DESC";

$appointment_result = mysqli_query($conn, $appointment_query);
?>

<div class="max-w-7xl mx-auto py-10 px-4">
    
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
        <div>
            <h2 class="text-3xl font-bold text-green-900 tracking-tight">My Appointments</h2>
            <p class="text-green-600 font-medium">Manage your upcoming and past clinical visits.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="../DonorDashboard.php" class="px-5 py-2.5 text-sm font-bold text-gray-600 hover:text-gray-800 transition">
                ← Back to Dashboard
            </a>
            <a href="DonorAppointmentCreate.php" class="bg-green-600 hover:bg-green-700 text-white px-6 py-2.5 rounded-xl font-bold text-sm shadow-lg shadow-green-100 transition flex items-center gap-2">
                <span>+ Create New</span>
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
                        <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-green-800">Schedule</th>
                        <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-green-800">Type</th>
                        <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-green-800 text-center">Status</th>
                        <th class="px-6 py-4 text-[10px] font-black uppercase tracking-widest text-green-800 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-green-50">
                    <?php if ($appointment_result && mysqli_num_rows($appointment_result) > 0): ?>
                        <?php while ($row = mysqli_fetch_assoc($appointment_result)): ?>
                            <tr class="hover:bg-green-50/30 transition-colors">
                                <td class="px-6 py-5">
                                    <span class="text-sm font-bold text-gray-400">#<?= $row['appointment_id']; ?></span>
                                </td>
                                <td class="px-6 py-5">
                                    <div class="text-sm font-bold text-green-900"><?= date("M d, Y", strtotime($row['appointment_date'])); ?></div>
                                    <div class="text-xs text-green-600 font-medium"><?= date("h:i A", strtotime($row['appointment_date'])); ?></div>
                                </td>
                                <td class="px-6 py-5">
                                    <span class="text-sm font-semibold text-gray-700 uppercase tracking-tight"><?= $row['type']; ?></span>
                                </td>
                                <td class="px-6 py-5 text-center">
                                    <?php
                                    $status = $row['status'];
                                    $badgeClass = match($status) {
                                        'completed' => 'bg-emerald-100 text-emerald-700',
                                        'cancelled' => 'bg-red-100 text-red-700',
                                        'scheduled' => 'bg-amber-100 text-amber-700',
                                        default => 'bg-gray-100 text-gray-700'
                                    };
                                    ?>
                                    <span class="px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider <?= $badgeClass; ?>">
                                        <?= $status; ?>
                                    </span>
                                </td>
                                <td class="px-6 py-5 text-right">
                                    <?php if ($status === 'scheduled'): ?>
                                        <div class="flex justify-end gap-2">
                                            <a href="DonorAppointmentUpdate.php?id=<?= $row['appointment_id']; ?>" 
                                               class="p-2 text-amber-600 hover:bg-amber-50 rounded-lg transition" title="Edit Appointment">
                                               <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                               </svg>
                                            </a>
                                            <a href="DonorAppointmentDelete.php?id=<?= $row['appointment_id']; ?>" 
                                               onclick="return confirm('Are you sure you want to cancel this appointment?');"
                                               class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition" title="Cancel Appointment">
                                               <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                               </svg>
                                            </a>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-xs font-bold text-gray-300 uppercase tracking-widest italic">Archived</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center">
                                <p class="text-gray-400 font-medium">No appointments recorded yet.</p>
                                <a href="DonorAppointmentCreate.php" class="text-green-600 text-sm font-bold hover:underline">Book your first visit →</a>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <div class="bg-green-50/30 px-8 py-4 border-t border-green-50">
            <p class="text-[10px] text-green-600 text-center uppercase tracking-widest font-bold">Secure Patient Health Portal</p>
        </div>
    </div>
</div>

<?php include('../../../includes/footer.php'); ?>