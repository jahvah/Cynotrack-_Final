<?php
session_start();
include('../../../includes/config.php');
include('../../../includes/head.php'); // Using head.php for Tailwind/Meta
include('../../../includes/staff_header.php'); // Assuming same header style for Staff

// STAFF access only
if (!isset($_SESSION['account_id']) || $_SESSION['role'] !== 'staff') {
    header("Location: ../../../unauthorized.php");
    exit();
}

// Fetch donor specimens
$donor_query = "
    SELECT s.*, d.first_name, d.last_name
    FROM specimens s
    JOIN donors_users d ON s.donor_id = d.donor_id
    ORDER BY s.specimen_id DESC";
$donor_result = mysqli_query($conn, $donor_query);
?>

<div class="min-h-screen bg-green-50/30 py-10 px-4 sm:px-6 lg:px-8">
    <div class="max-w-7xl mx-auto space-y-8">
        
        <?php if (isset($_SESSION['success'])): ?>
            <div class="p-4 mb-4 text-sm text-green-800 rounded-lg bg-green-100 border border-green-200" role="alert">
                <span class="font-bold">Success!</span> <?= $_SESSION['success']; unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>
        <?php if (isset($_SESSION['error'])): ?>
            <div class="p-4 mb-4 text-sm text-red-800 rounded-lg bg-red-100 border border-red-200" role="alert">
                <span class="font-bold">Error!</span> <?= $_SESSION['error']; unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <div class="bg-white border border-green-100 rounded-2xl shadow-sm overflow-hidden">
            <div class="p-6 border-b border-green-50 bg-white flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h2 class="text-2xl font-bold text-green-900">Donor Specimens</h2>
                    <p class="text-green-600 text-sm">Manage biological assets provided by registered donors.</p>
                </div>
                <div class="flex items-center gap-3">
                    <a href="../StaffDashboard.php" class="px-4 py-2 text-sm font-semibold text-green-700 bg-white border border-green-200 rounded-lg hover:bg-green-50 transition">← Dashboard</a>
                    <a href="StaffSpecimenDonorCrud/StaffSpecimenDonorCreate.php" class="px-4 py-2 text-sm font-semibold text-white bg-green-600 rounded-lg hover:bg-green-700 transition shadow-md shadow-green-100">+ Add Donor Specimen</a>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-green-50/50 border-b border-green-100">
                            <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-green-700">Code & ID</th>
                            <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-green-700">Donor Name</th>
                            <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-green-700 text-center">Qty / Price</th>
                            <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-green-700 text-center">Status</th>
                            <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-green-700">Location & Expiry</th>
                            <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-green-700 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-green-50">
                        <?php if ($donor_result && mysqli_num_rows($donor_result) > 0): ?>
                            <?php while ($row = mysqli_fetch_assoc($donor_result)): 
                                $status = $row['status'];
                                $badgeClass = match($status) {
                                    'approved', 'stored' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                                    'expired', 'disposed', 'disapproved', 'used' => 'bg-red-100 text-red-800 border-red-200',
                                    default => 'bg-amber-100 text-amber-800 border-amber-200',
                                };
                            ?>
                                <tr class="hover:bg-green-50/30 transition">
                                    <td class="px-6 py-4">
                                        <div class="text-sm font-bold text-gray-900"><?= htmlspecialchars($row['unique_code']); ?></div>
                                        <div class="text-[10px] text-green-600 font-medium uppercase">ID: #<?= $row['specimen_id']; ?></div>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-700"><?= htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?></td>
                                    <td class="px-6 py-4 text-center">
                                        <div class="text-sm font-semibold text-gray-900"><?= $row['quantity']; ?> units</div>
                                        <div class="text-xs text-green-600">₱<?= number_format($row['price'], 2); ?></div>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-black uppercase tracking-wider border <?= $badgeClass ?>">
                                            <?= htmlspecialchars($status); ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-xs text-gray-600 font-medium"><?= htmlspecialchars($row['storage_location'] ?? 'N/A'); ?></div>
                                        <div class="text-[10px] text-gray-400"><?= $row['expiration_date'] ? date("M d, Y", strtotime($row['expiration_date'])) : 'No Expiry'; ?></div>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <div class="flex justify-end gap-3">
                                            <a href="StaffSpecimenDonorCrud/StaffSpecimenDonorUpdate.php?id=<?= $row['specimen_id']; ?>" class="text-xs font-bold text-amber-600 hover:text-amber-700 uppercase">Edit</a>
                                            <a href="StaffSpecimenDonorCrud/StaffSpecimenDonorDelete.php?type=donor&id=<?= $row['specimen_id']; ?>" onclick="return confirm('Are you sure?');" class="text-xs font-bold text-red-600 hover:text-red-700 uppercase">Delete</a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="6" class="px-6 py-10 text-center text-gray-400 italic">No donor specimens found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="flex justify-between items-center px-4">
            <span class="text-[10px] text-green-700 font-bold uppercase tracking-widest">Inventory Status: Active</span>
            <span class="text-[10px] text-green-600 italic">CryoBank Management System</span>
        </div>
    </div>
</div>

<?php include('../../../includes/footer.php'); ?>