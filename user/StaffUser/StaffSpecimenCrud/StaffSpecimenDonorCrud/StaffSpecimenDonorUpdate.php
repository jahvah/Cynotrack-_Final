<?php
session_start();
include('../../../../includes/config.php');
include('../../../../includes/header.php');

if (!isset($_SESSION['account_id']) || $_SESSION['role'] !== 'staff') {
    header("Location: ../../../../unauthorized.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: StaffSpecimenIndex.php");
    exit();
}

$specimen_id = intval($_GET['id']);

// Fetch existing data to use as placeholders
$stmt = $conn->prepare("SELECT unique_code, quantity, status, storage_location, expiration_date FROM specimens WHERE specimen_id = ? AND specimen_owner_type = 'donor'");
$stmt->bind_param("i", $specimen_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: StaffSpecimenIndex.php");
    exit();
}

$specimen = $result->fetch_assoc();
?>

<div class="max-w-7xl mx-auto py-10 px-4">
    <div class="mb-6">
        <a href="../StaffSpecimenIndex.php" class="text-sm font-bold text-green-700 hover:text-green-800 transition flex items-center gap-1">
            ← Back to Specimen Dashboard
        </a>
    </div>

    <div class="max-w-4xl mx-auto">
        <div class="bg-white border border-green-100 rounded-2xl shadow-xl shadow-green-100/20 overflow-hidden">
            
            <div class="bg-green-50/50 border-b border-green-100 px-8 py-6 flex justify-between items-center">
                <div>
                    <h2 class="text-2xl font-bold text-green-900">Update Donor Specimen</h2>
                    <p class="text-green-600 text-sm mt-1 font-medium">Editing Specimen: <span class="text-green-900 font-bold"><?= htmlspecialchars($specimen['unique_code']); ?></span></p>
                </div>
                <div class="shrink-0 flex flex-col items-end">
                    <span class="text-[10px] font-black uppercase tracking-widest text-green-800 opacity-40">System ID #<?= $specimen_id; ?></span>
                </div>
            </div>

            <div class="p-8">
                <?php if (isset($_SESSION['error'])): ?>
                    <div class="mb-6 p-4 text-sm text-red-700 bg-red-50 border border-red-100 rounded-lg font-medium">
                        <?= $_SESSION['error']; unset($_SESSION['error']); ?>
                    </div>
                <?php endif; ?>

                <form action="StaffSpecimenDonorStore.php" method="POST" class="space-y-8">
                    <input type="hidden" name="action" value="update_donor_specimen">
                    <input type="hidden" name="specimen_id" value="<?= $specimen_id; ?>">

                    <div>
                        <h3 class="text-sm font-black text-slate-400 uppercase tracking-widest mb-4 flex items-center gap-2">
                            <span class="w-2 h-2 bg-slate-300 rounded-full"></span> Locked Identification
                        </h3>
                        <div class="bg-gray-50 border border-gray-100 p-4 rounded-xl">
                            <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-1">Unique Specimen Code</label>
                            <p class="text-sm font-bold text-slate-500 italic"><?= htmlspecialchars($specimen['unique_code']); ?></p>
                            <input type="hidden" name="unique_code" value="<?= htmlspecialchars($specimen['unique_code']); ?>">
                        </div>
                    </div>

                    <div class="pt-6 border-t border-green-50">
                        <h3 class="text-sm font-black text-green-900 uppercase tracking-widest mb-4 flex items-center gap-2">
                            <span class="w-2 h-2 bg-green-500 rounded-full"></span> Inventory Updates
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-[10px] font-bold uppercase tracking-wider text-green-800 mb-2">New Quantity</label>
                                <input type="number" name="quantity" min="0" 
                                    placeholder="Current: <?= $specimen['quantity']; ?>"
                                    class="w-full px-4 py-3 border border-green-100 rounded-xl focus:ring-2 focus:ring-green-500 outline-none transition">
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold uppercase tracking-wider text-green-800 mb-2">Change Status</label>
                                <select name="status" class="w-full px-4 py-3 border border-green-100 rounded-xl focus:ring-2 focus:ring-green-500 outline-none transition bg-white cursor-pointer">
                                    <option value="" disabled selected>Currently: <?= ucfirst($specimen['status']); ?></option>
                                    <option value="approved">Approved</option>
                                    <option value="disapproved">Disapproved</option>
                                    <option value="stored">Stored</option>
                                    <option value="used">Used</option>
                                    <option value="expired">Expired</option>
                                    <option value="disposed">Disposed</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="pt-6 border-t border-green-50">
                        <h3 class="text-sm font-black text-green-900 uppercase tracking-widest mb-4 flex items-center gap-2">
                            <span class="w-2 h-2 bg-green-500 rounded-full"></span> Storage & Logistics
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-[10px] font-bold uppercase tracking-wider text-green-800 mb-2">New Storage Location</label>
                                <input type="text" name="storage_location" 
                                    placeholder="<?= htmlspecialchars($specimen['storage_location']); ?>"
                                    class="w-full px-4 py-3 border border-green-100 rounded-xl focus:ring-2 focus:ring-green-500 outline-none transition">
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold uppercase tracking-wider text-green-800 mb-2">New Expiration Date</label>
                                <input type="date" name="expiration_date" 
                                    class="w-full px-4 py-3 border border-green-100 rounded-xl focus:ring-2 focus:ring-green-500 outline-none transition">
                                <p class="text-[9px] text-green-600 mt-1 italic">Current: <?= $specimen['expiration_date']; ?></p>
                            </div>
                        </div>
                    </div>

                    <div class="pt-8 border-t border-green-50 flex gap-4">
                        <button type="submit" class="flex-1 bg-green-600 hover:bg-green-700 text-white font-bold py-4 rounded-xl transition duration-200 shadow-lg shadow-green-100">
                            Apply Updates
                        </button>
                        <a href="../StaffSpecimenIndex.php" class="px-8 py-4 border border-green-200 text-green-700 font-bold rounded-xl hover:bg-green-50 transition text-center flex items-center justify-center">
                            Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include('../../../../includes/footer.php'); ?>