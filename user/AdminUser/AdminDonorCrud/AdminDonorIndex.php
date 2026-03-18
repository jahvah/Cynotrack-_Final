<?php
// 1. Move up 3 levels to find the root/includes
include('../../../includes/config.php'); 
include('../../../includes/head.php'); 
include('../../../includes/admin_header.php');

// 2. Security Check 
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: " . ROOT_URL . "login.php");
    exit();
}

// 3. Fetch donor data
$query = "SELECT 
            d.donor_id, d.account_id, d.first_name, d.last_name, d.profile_image,
            d.medical_document, d.evaluation_status, a.username, a.email, a.status
          FROM donors_users d
          JOIN accounts a ON d.account_id = a.account_id
          ORDER BY d.donor_id DESC";

$result = mysqli_query($conn, $query);
?>

<div class="min-h-screen bg-green-50/30 py-10 px-4 sm:px-6 lg:px-8">
    
    <div class="max-w-7xl mx-auto bg-white border border-green-100 rounded-2xl shadow-sm overflow-hidden">
        
        <div class="p-6 sm:p-8 border-b border-green-50 bg-white">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h2 class="text-3xl font-bold text-green-900">Donor Management</h2>
                    <p class="text-green-600 font-medium text-sm mt-1">Manage donor profiles, medical history, and account approvals.</p>
                </div>
                
                <div class="flex items-center gap-3">
                    <a href="../AdminDashboard.php" class="px-4 py-2 text-sm font-semibold text-green-700 bg-white border border-green-200 rounded-lg hover:bg-green-50 transition">
                        ← Dashboard
                    </a>
                    <a href="AdminDonorCreate.php" class="px-4 py-2 text-sm font-semibold text-white bg-green-600 rounded-lg hover:bg-green-700 transition shadow-md shadow-green-100">
                        + Add Donor
                    </a>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-green-50/50 border-b border-green-100">
                        <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-green-700">Donor Profile</th>
                        <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-green-700">Account Credentials</th>
                        <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-green-700 text-center">Medical File</th>
                        <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-green-700 text-center">Evaluation</th>
                        <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-green-700 text-center">Status</th>
                        <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-green-700 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-green-50">
                    <?php if ($result && mysqli_num_rows($result) > 0): ?>
                        <?php while ($row = mysqli_fetch_assoc($result)): ?>
                            <tr class="hover:bg-green-50/30 transition">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <?php if (!empty($row['profile_image'])): ?>
                                            <img src="../../../uploads/<?= htmlspecialchars($row['profile_image']); ?>" class="w-10 h-10 rounded-full object-cover border border-green-200">
                                        <?php else: ?>
                                            <div class="w-10 h-10 rounded-full bg-green-100 flex items-center justify-center text-green-600 font-bold text-xs">
                                                <?= htmlspecialchars(substr($row['first_name'], 0, 1) . substr($row['last_name'], 0, 1)); ?>
                                            </div>
                                        <?php endif; ?>
                                        <div>
                                            <div class="text-sm font-bold text-gray-900"><?= htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?></div>
                                            <div class="text-[10px] text-green-600 font-medium uppercase tracking-tight">Donor ID: #<?= htmlspecialchars($row['donor_id']); ?></div>
                                        </div>
                                    </div>
                                </td>

                                <td class="px-6 py-4">
                                    <div class="text-sm text-gray-600 font-medium"><?= htmlspecialchars($row['email']); ?></div>
                                    <div class="text-xs text-green-600 font-bold">@<?= htmlspecialchars($row['username']); ?></div>
                                </td>

                                <td class="px-6 py-4 text-center">
                                    <?php if (!empty($row['medical_document'])): ?>
                                        <a href="../../../medical_docs/<?= htmlspecialchars($row['medical_document']); ?>" target="_blank" 
                                           class="inline-block px-3 py-1 text-[10px] font-black uppercase bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-lg hover:bg-emerald-100 transition">
                                            View PDF
                                        </a>
                                    <?php else: ?>
                                        <span class="text-xs text-gray-300 italic">None</span>
                                    <?php endif; ?>
                                </td>

                                <td class="px-6 py-4 text-center">
                                    <?php 
                                        $eval = $row['evaluation_status'] ?? 'pending';
                                        $evalColor = match($eval) {
                                            'approved' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                                            'pending' => 'bg-amber-100 text-amber-800 border-amber-200',
                                            'rejected' => 'bg-red-100 text-red-800 border-red-200',
                                            default => 'bg-gray-100 text-gray-800 border-gray-200',
                                        };
                                    ?>
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-black uppercase tracking-wider border <?= $evalColor ?>">
                                        <?= htmlspecialchars($eval); ?>
                                    </span>
                                </td>

                                <td class="px-6 py-4 text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <?php if ($row['status'] == 'active'): ?>
                                            <span class="w-2 h-2 rounded-full bg-emerald-500 shadow-[0_0_8px_rgba(16,185,129,0.5)]"></span>
                                        <?php elseif ($row['status'] == 'inactive'): ?>
                                            <span class="w-2 h-2 rounded-full bg-red-500"></span>
                                        <?php else: ?>
                                            <span class="w-2 h-2 rounded-full bg-amber-400"></span>
                                        <?php endif; ?>
                                        <span class="text-xs font-bold text-gray-700 capitalize"><?= htmlspecialchars($row['status']) ?></span>
                                    </div>
                                </td>

                                <td class="px-6 py-4 text-right">
                                    <div class="flex justify-end gap-4">
                                        <a href="AdminDonorUpdate.php?id=<?= $row['donor_id']; ?>" class="text-sm font-bold text-amber-600 hover:text-amber-700 transition">
                                            Edit
                                        </a>
                                        <a href="AdminDonorDelete.php?id=<?= $row['donor_id']; ?>" 
                                           onclick="return confirm('Are you sure you want to delete this donor?');"
                                           class="text-sm font-bold text-red-600 hover:text-red-700 transition">
                                            Delete
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-green-800/50 italic bg-green-50/10">
                                No donor records found.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="bg-green-50/30 px-8 py-4 border-t border-green-50 flex justify-between items-center">
            <span class="text-[10px] text-green-700 font-bold uppercase tracking-widest">System Log: Active</span>
            <span class="text-[10px] text-green-600 italic">Donor Management Portal v1.0</span>
        </div>
    </div>
</div>

<?php include('../../../includes/footer.php'); ?>