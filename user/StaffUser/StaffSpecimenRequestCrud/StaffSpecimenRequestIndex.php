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
$request_query = "
    SELECT sr.*, ru.first_name, ru.last_name, s.unique_code
    FROM specimen_requests sr
    INNER JOIN specimens s ON sr.specimen_id = s.specimen_id
    INNER JOIN recipients_users ru ON sr.recipient_id = ru.recipient_id
    ORDER BY sr.request_id DESC";
$request_result = mysqli_query($conn, $request_query);
?>

<div class="min-h-screen bg-green-50/30 py-10 px-4 sm:px-6 lg:px-8">
    <div class="max-w-7xl mx-auto space-y-8">
        
        <?php if (isset($_SESSION['success'])): ?>
            <div class="p-4 mb-4 text-sm text-green-800 rounded-lg bg-green-100 border border-green-200 font-medium">
                <span class="font-bold">Success!</span> <?= $_SESSION['success']; unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>
        <?php if (isset($_SESSION['error'])): ?>
            <div class="p-4 mb-4 text-sm text-red-800 rounded-lg bg-red-100 border border-red-200 font-medium">
                <span class="font-bold">Error!</span> <?= $_SESSION['error']; unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <div class="bg-white border border-green-100 rounded-2xl shadow-sm overflow-hidden">
            <div class="p-6 border-b border-green-50 bg-white flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h2 class="text-2xl font-bold text-green-900">Recipient Specimen Requests</h2>
                    <p class="text-green-600 text-sm">Review and fulfill specimen acquisition requests from recipients.</p>
                </div>
                <div class="flex items-center gap-3">
                    <a href="../StaffDashboard.php" class="px-4 py-2 text-sm font-semibold text-green-700 bg-white border border-green-200 rounded-lg hover:bg-green-50 transition">← Dashboard</a>
                    <a href="StaffSpecimenRequestRecipientCrud/StaffSpecimenRequestRecipientCreate.php" class="px-4 py-2 text-sm font-semibold text-white bg-green-600 rounded-lg hover:bg-green-700 transition shadow-md shadow-green-100">+ Create Request</a>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-green-50/50 border-b border-green-100">
                            <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-green-700">Request & Date</th>
                            <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-green-700">Recipient & Specimen</th>
                            <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-green-700 text-center">Qty / Pricing</th>
                            <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-green-700 text-center">Status</th>
                            <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-green-700 text-center">Payment & Receipt</th>
                            <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-green-700 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-green-50">
                        <?php if ($request_result && mysqli_num_rows($request_result) > 0): ?>
                            <?php while ($row = mysqli_fetch_assoc($request_result)): 
                                $status = $row['status'];
                                $isLocked = ($status === 'fulfilled' || $status === 'rejected');
                                
                                $statusBadge = match($status) {
                                    'approved'  => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                                    'fulfilled' => 'bg-blue-100 text-blue-800 border-blue-200',
                                    'rejected'  => 'bg-red-100 text-red-800 border-red-200',
                                    default     => 'bg-amber-100 text-amber-800 border-amber-200',
                                };

                                $payStatus = $row['payment_status'];
                                $payBadge = match($payStatus) {
                                    'paid'            => 'bg-green-100 text-green-800',
                                    'refunded'        => 'bg-slate-100 text-slate-800',
                                    'waiting_payment' => 'bg-amber-50 text-amber-700 border-amber-100',
                                    default           => 'bg-red-50 text-red-700',
                                };
                            ?>
                                <tr class="hover:bg-green-50/30 transition">
                                    <td class="px-6 py-4">
                                        <div class="text-sm font-bold text-gray-900">#REQ-<?= $row['request_id']; ?></div>
                                        <div class="text-[10px] text-green-600 font-medium uppercase"><?= date("M d, Y", strtotime($row['request_date'])); ?></div>
                                    </td>

                                    <td class="px-6 py-4">
                                        <div class="text-sm font-semibold text-gray-800"><?= htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?></div>
                                        <div class="flex items-center gap-1.5 mt-0.5">
                                            <span class="w-1.5 h-1.5 bg-green-400 rounded-full"></span>
                                            <span class="text-[11px] font-mono text-gray-500"><?= $row['unique_code']; ?></span>
                                        </div>
                                    </td>

                                    <td class="px-6 py-4 text-center">
                                        <div class="text-sm font-bold text-gray-900"><?= $row['requested_quantity']; ?> Units</div>
                                        <div class="text-[10px] text-gray-500 italic">₱<?= number_format($row['unit_price'], 2); ?> /ea</div>
                                        <div class="text-xs font-black text-green-700 mt-0.5">Total: ₱<?= number_format($row['total_price'], 2); ?></div>
                                    </td>

                                    <td class="px-6 py-4 text-center">
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-black uppercase tracking-wider border <?= $statusBadge ?>">
                                            <?= htmlspecialchars($status); ?>
                                        </span>
                                        <?php if ($row['fulfilled_date']): ?>
                                            <div class="text-[9px] text-gray-400 mt-1 italic">Sent: <?= date("m/d/y", strtotime($row['fulfilled_date'])); ?></div>
                                        <?php endif; ?>
                                    </td>

                                    <td class="px-6 py-4 text-center">
                                        <div class="inline-block px-2 py-0.5 rounded text-[9px] font-bold uppercase border <?= $payBadge ?>">
                                            <?= str_replace('_', ' ', $payStatus); ?>
                                        </div>
                                        <div class="mt-2">
                                            <?php if (!empty($row['receipt_image'])): ?>
                                                <a href="../../../<?= $row['receipt_image']; ?>" target="_blank" class="text-[10px] font-bold text-blue-600 hover:underline flex items-center justify-center gap-1">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                    </svg>
                                                    View Receipt
                                                </a>
                                            <?php else: ?>
                                                <span class="text-[10px] text-gray-400 italic">No proof</span>
                                            <?php endif; ?>
                                        </div>
                                    </td>

                                    <td class="px-6 py-4 text-right">
                                        <?php if (!$isLocked): ?>
                                            <div class="flex justify-end gap-3">
                                                <a href="StaffSpecimenRequestRecipientCrud/StaffSpecimenRequestRecipientUpdate.php?id=<?= $row['request_id']; ?>" class="text-xs font-bold text-amber-600 hover:text-amber-700 uppercase">Edit</a>
                                                <a href="StaffSpecimenRequestRecipientCrud/StaffSpecimenRequestRecipientDelete.php?id=<?= $row['request_id']; ?>" onclick="return confirm('Delete this request?');" class="text-xs font-bold text-red-600 hover:text-red-700 uppercase">Delete</a>
                                            </div>
                                        <?php else: ?>
                                            <span class="text-[10px] font-bold text-gray-400 uppercase italic">Locked</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="6" class="px-6 py-10 text-center text-gray-400 italic">No specimen requests found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="flex justify-between items-center px-4">
            <span class="text-[10px] text-green-700 font-bold uppercase tracking-widest">Request Module: Active</span>
            <span class="text-[10px] text-green-600 italic">CryoBank Management System</span>
        </div>
    </div>
</div>

<?php include('../../../includes/footer.php'); ?>