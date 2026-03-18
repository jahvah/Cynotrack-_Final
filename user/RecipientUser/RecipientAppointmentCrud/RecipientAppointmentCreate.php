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
?>

<div class="max-w-7xl mx-auto py-10 px-4">
    <div class="mb-6">
        <a href="RecipientAppointmentIndex.php" class="text-sm font-bold text-green-700 hover:text-green-800 transition flex items-center gap-1">
            ← Back to Appointment Dashboard
        </a>
    </div>

    <div class="max-w-2xl mx-auto">
        <div class="bg-white border border-green-100 rounded-2xl shadow-xl shadow-green-100/20 overflow-hidden">
            
            <div class="bg-green-50/50 border-b border-green-100 px-8 py-6">
                <h2 class="text-2xl font-bold text-green-900">Schedule Appointment</h2>
                <p class="text-green-600 text-sm mt-1 font-medium">Please select your preferred date and time for your session.</p>
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

                <form action="RecipientAppointmentStore.php" method="POST" class="space-y-8">
                    <input type="hidden" name="action" value="create_recipient_appointment">
                    <input type="hidden" name="recipient_id" value="<?= $recipient_id; ?>">

                    <div>
                        <h3 class="text-sm font-black text-green-900 uppercase tracking-widest mb-4 flex items-center gap-2">
                            <span class="w-2 h-2 bg-green-500 rounded-full"></span> Appointment Details
                        </h3>
                        
                        <div class="space-y-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-[10px] font-bold uppercase tracking-wider text-green-800 mb-2">
                                        Date & Time
                                    </label>
                                    <input type="datetime-local" name="appointment_date" required
                                        class="w-full px-4 py-3 border border-green-100 rounded-xl focus:ring-2 focus:ring-green-500 outline-none transition bg-green-50/10 text-green-900">
                                </div>
                                <div>
                                    <label class="block text-[10px] font-bold uppercase tracking-wider text-green-800 mb-2">
                                        Visit Type
                                    </label>
                                    <select name="type" required
                                        class="w-full px-4 py-3 border border-green-100 rounded-xl focus:ring-2 focus:ring-green-500 outline-none transition bg-white cursor-pointer font-medium text-slate-700">
                                        <option value="consultation" selected>Consultation</option>
                                        <option value="release">Release</option>
                                        <option value="donation">Donation</option>
                                        <option value="storage">Storage</option>
                                    </select>
                                </div>
                            </div>

                            <input type="hidden" name="status" value="scheduled">
                            
                            <p class="text-xs text-gray-400 font-medium">
                                Note: New appointments are set to "Scheduled" by default and may require administrative review.
                            </p>
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
                <p class="text-[10px] text-green-600 text-center uppercase tracking-widest font-bold">Secure Medical Logistics Management</p>
            </div>
        </div>
    </div>
</div>

<?php include('../../../includes/footer.php'); ?>