<?php
session_start();
include('../../../includes/config.php');
include('../../../includes/head.php'); // Ensure head is included for Tailwind/Styles
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
?>

<div class="max-w-7xl mx-auto py-10 px-4">
    <div class="mb-6">
        <a href="DonorAppointmentIndex.php" class="text-sm font-bold text-green-700 hover:text-green-800 transition flex items-center gap-1">
            ← Back to Appointment Dashboard
        </a>
    </div>

    <div class="max-w-2xl mx-auto">
        <div class="bg-white border border-green-100 rounded-2xl shadow-xl shadow-green-100/20 overflow-hidden">
            
            <div class="bg-green-50/50 border-b border-green-100 px-8 py-6">
                <h2 class="text-2xl font-bold text-green-900">Schedule Appointment</h2>
                <p class="text-green-600 text-sm mt-1 font-medium">Please select your preferred date and time for your visit.</p>
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

                <form action="DonorAppointmentStore.php" method="POST" class="space-y-8">
                    <input type="hidden" name="action" value="create_donor_appointment">
                    <input type="hidden" name="donor_id" value="<?= $donor_id; ?>">

                    <div>
                        <h3 class="text-sm font-black text-green-900 uppercase tracking-widest mb-4 flex items-center gap-2">
                            <span class="w-2 h-2 bg-green-500 rounded-full"></span> Schedule Information
                        </h3>
                        
                        <div class="space-y-4">
                            <div>
                                <label class="block text-[10px] font-bold uppercase tracking-wider text-green-800 mb-2">
                                    Appointment Date & Time
                                </label>
                                <input type="datetime-local" name="appointment_date" required
                                    class="w-full px-4 py-3 border border-green-100 rounded-xl focus:ring-2 focus:ring-green-500 outline-none transition bg-green-50/10 text-green-900">
                                <p class="mt-2 text-xs text-gray-400 font-medium">Appointments are subject to availability and staff approval.</p>
                            </div>
                        </div>
                    </div>

                    <div class="pt-8 border-t border-green-50">
                        <button type="submit" 
                            class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-4 rounded-xl transition duration-200 shadow-lg shadow-green-100 flex items-center justify-center gap-2">
                            <span>Confirm Appointment</span>
                        </button>
                    </div>
                </form>
            </div>

            <div class="bg-green-50/30 px-8 py-4 border-t border-green-50">
                <p class="text-[10px] text-green-600 text-center uppercase tracking-widest font-bold">Secure Appointment Management</p>
            </div>
        </div>
    </div>
</div>

<?php include('../../../includes/footer.php'); ?>