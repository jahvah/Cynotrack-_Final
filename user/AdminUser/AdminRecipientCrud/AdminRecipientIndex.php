<?php
session_start();
include('../../../includes/config.php'); 
include('../../../includes/head.php'); 
include('../../../includes/admin_header.php');

// 2. Security Check
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: " . ROOT_URL . "login.php");
    exit();
}

// Fetch recipient data matching the modern schema
$query = "SELECT 
            r.recipient_id, 
            r.account_id,
            r.first_name, 
            r.last_name, 
            r.profile_image, 
            r.preferences,
            a.username,
            a.email, 
            a.status
          FROM recipients_users r
          JOIN accounts a ON r.account_id = a.account_id
          ORDER BY r.recipient_id DESC";

$result = mysqli_query($conn, $query);
?>

<div class="min-h-screen bg-green-50/30 py-10 px-4 sm:px-6 lg:px-8">
    
    <div class="max-w-7xl mx-auto bg-white border border-green-100 rounded-2xl shadow-sm overflow-hidden">
        
        <div class="p-6 sm:p-8 border-b border-green-50 bg-white">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h2 class="text-3xl font-bold text-green-900">Recipient Management</h2>
                    <p class="text-green-600 font-medium text-sm mt-1">Manage hospital recipients, account statuses, and donation preferences.</p>
                </div>
                
                <div class="flex items-center gap-3">
                    <a href="../AdminDashboard.php" class="px-4 py-2 text-sm font-semibold text-green-700 bg-white border border-green-200 rounded-lg hover:bg-green-50 transition">
                        ← Dashboard
                    </a>
                    <a href="AdminRecipientCreate.php" class="px-4 py-2 text-sm font-semibold text-white bg-green-600 rounded-lg hover:bg-green-700 transition shadow-md shadow-green-100">
                        + Add Recipient
                    </a>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-green-50/50 border-b border-green-100">
                        <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-green-700">Recipient Profile</th>
                        <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-green-700">Account Credentials</th>
                        <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-green-700">Preferences</th>
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
                                            <div class="w-10 h-10 rounded-full bg-green-100 flex items-center justify-center text-green-600 font-bold text-xs uppercase">
                                                <?= htmlspecialchars(substr($row['first_name'], 0, 1) . substr($row['last_name'], 0, 1)); ?>
                                            </div>
                                        <?php endif; ?>
                                        <div>
                                            <div class="text-sm font-bold text-gray-900"><?= htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?></div>
                                            <div class="text-[10px] text-green-600 font-medium uppercase tracking-tight">Recipient ID: #<?= htmlspecialchars($row['recipient_id']); ?></div>
                                        </div>
                                    </div>
                                </td>

                                <td class="px-6 py-4">
                                    <div class="text-sm text-gray-600 font-medium"><?= htmlspecialchars($row['email']); ?></div>
                                    <div class="text-xs text-green-600 font-bold">@<?= htmlspecialchars($row['username']); ?></div>
                                </td>

                                <td class="px-6 py-4">
                                    <div class="max-w-[200px] text-xs text-gray-500 italic line-clamp-2">
                                        <?= !empty($row['preferences']) ? htmlspecialchars($row['preferences']) : 'No preferences set'; ?>
                                    </div>
                                </td>

                                <td class="px-6 py-4 text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <?php 
                                            $status = strtolower($row['status']);
                                            $dotColor = match($status) {
                                                'active' => 'bg-emerald-500 shadow-[0_0_8px_rgba(16,185,129,0.5)]',
                                                'inactive' => 'bg-red-500',
                                                'pending' => 'bg-amber-400',
                                                default => 'bg-gray-400'
                                            };
                                        ?>
                                        <span class="w-2 h-2 rounded-full <?= $dotColor ?>"></span>
                                        <span class="text-xs font-bold text-gray-700 capitalize"><?= htmlspecialchars($status) ?></span>
                                    </div>
                                </td>

                                <td class="px-6 py-4 text-right">
                                    <div class="flex justify-end gap-4">
                                        <a href="AdminRecipientUpdate.php?id=<?= urlencode($row['recipient_id']); ?>" class="text-sm font-bold text-amber-600 hover:text-amber-700 transition">
                                            Edit
                                        </a>
                                        <a href="AdminRecipientDelete.php?id=<?= urlencode($row['recipient_id']); ?>" 
                                           onclick="return confirm('Are you sure you want to delete this recipient?');"
                                           class="text-sm font-bold text-red-600 hover:text-red-700 transition">
                                            Delete
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-green-800/50 italic bg-green-50/10">
                                No recipient records found.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="bg-green-50/30 px-8 py-4 border-t border-green-50 flex justify-between items-center">
            <span class="text-[10px] text-green-700 font-bold uppercase tracking-widest">Portal: Hospital Recipients</span>
            <span class="text-[10px] text-green-600 italic">Recipient Management v1.0</span>
        </div>
    </div>
</div>

<?php include('../../../includes/footer.php'); ?>