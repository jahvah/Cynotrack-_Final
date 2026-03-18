<?php
session_start();
include('../../includes/config.php');
include('../../includes/head.php'); // Meta and CSS
include('../../includes/staff_header.php');

// Security Check
if (!isset($_SESSION['account_id']) || $_SESSION['role'] !== 'staff') {
    header("Location: ../../../unauthorized.php");
    exit();
}

// Fetch quick stats for the dashboard
$pending_reqs = $conn->query("SELECT COUNT(*) as count FROM specimen_requests WHERE status = 'pending'")->fetch_assoc()['count'];
$pending_donors = $conn->query("SELECT COUNT(*) as count FROM donors_users WHERE evaluation_status = 'pending'")->fetch_assoc()['count'];
$total_specimens = $conn->query("SELECT SUM(quantity) as count FROM specimens WHERE status = 'stored'")->fetch_assoc()['count'];

// Fetch Transactions for the bottom table
$transactions_query = $conn->query("SELECT * FROM transactions ORDER BY transaction_date DESC LIMIT 10");
?>

<style>
    @import url('https://fonts.googleapis.com/css2?family=DM+Serif+Display&family=DM+Sans:wght@400;500;700&display=swap');
    .page-wrap { font-family: 'DM Sans', sans-serif; }
    .display-font { font-family: 'DM Serif Display', serif; }
    
    .dashboard-card {
        background: white;
        border-radius: 24px;
        border: 1px solid #f1f5f9;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .dashboard-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 20px 40px rgba(0,0,0,0.03);
        border-color: #d1fae5;
    }
    .icon-box {
        width: 50px;
        height: 50px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 20px;
    }
    .fade-up {
        opacity: 0;
        transform: translateY(20px);
        animation: fadeUp 0.6s ease forwards;
    }
    @keyframes fadeUp { to { opacity: 1; transform: translateY(0); } }

    /* Custom scrollbar for the table */
    .custom-scrollbar::-webkit-scrollbar { height: 6px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: #f1f5f9; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
</style>

<div class="page-wrap min-h-screen bg-gradient-to-br from-slate-50 to-green-50/30 py-12 px-6">
    <div class="max-w-6xl mx-auto">
        
        <div class="fade-up flex flex-col md:flex-row md:items-end justify-between mb-12 gap-6">
            <div>
                <p class="text-[10px] font-black uppercase tracking-[0.3em] text-emerald-600 mb-2">Administrative Portal</p>
                <h1 class="display-font text-4xl text-slate-900">Staff Dashboard</h1>
            </div>
            <div class="flex gap-4">
                <div class="bg-white px-6 py-3 rounded-2xl shadow-sm border border-slate-100">
                    <p class="text-[10px] font-bold text-slate-400 uppercase">System Status</p>
                    <p class="text-sm font-black text-emerald-600 flex items-center gap-2">
                        <span class="w-2 h-2 bg-emerald-500 rounded-full animate-pulse"></span> Operational
                    </p>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-12">
            <div class="fade-up bg-white rounded-3xl p-8 border border-slate-100 shadow-sm">
                <p class="text-slate-400 text-xs font-bold uppercase tracking-widest mb-1">Pending Requests</p>
                <h2 class="display-font text-5xl text-slate-800"><?= $pending_reqs ?></h2>
                <p class="text-slate-400 text-[10px] mt-4 font-bold uppercase">Requires immediate review</p>
            </div>
            <div class="fade-up bg-white rounded-3xl p-8 border border-slate-100 shadow-sm" style="animation-delay: 0.1s;">
                <p class="text-slate-400 text-xs font-bold uppercase tracking-widest mb-1">Pending Donors</p>
                <h2 class="display-font text-5xl text-slate-800"><?= $pending_donors ?></h2>
                <p class="text-slate-400 text-[10px] mt-4 font-bold uppercase">Evaluations waiting</p>
            </div>
            <div class="fade-up bg-white rounded-3xl p-8 border border-slate-100 shadow-sm" style="animation-delay: 0.2s;">
                <p class="text-slate-400 text-xs font-bold uppercase tracking-widest mb-1">Available Units</p>
                <h2 class="display-font text-5xl text-slate-800"><?= number_format($total_specimens ?: 0) ?></h2>
                <p class="text-slate-400 text-[10px] mt-4 font-bold uppercase">Total in-stock inventory</p>
            </div>
        </div>

        <h3 class="fade-up text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-6">Management Modules</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-16">
            
            <a href="StaffSpecimenCrud/StaffSpecimenIndex.php" class="fade-up dashboard-card p-8 group">
                <div class="icon-box bg-emerald-100 text-emerald-600 group-hover:bg-emerald-600 group-hover:text-white transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/></svg>
                </div>
                <h4 class="display-font text-xl text-slate-800 mb-2">Specimen Lab</h4>
                <p class="text-sm text-slate-500 leading-relaxed font-medium">Manage unique codes, storage locations, and expiration dates.</p>
            </a>

            <a href="StaffSpecimenRequestCrud/StaffSpecimenRequestIndex.php" class="fade-up dashboard-card p-8 group" style="animation-delay: 0.1s;">
                <div class="icon-box bg-blue-100 text-blue-600 group-hover:bg-blue-600 group-hover:text-white transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                </div>
                <h4 class="display-font text-xl text-slate-800 mb-2">Order Requests</h4>
                <p class="text-sm text-slate-500 leading-relaxed font-medium">Review recipient requests, verify payments, and fulfill orders.</p>
            </a>

            <a href="StaffAppointmentCrud/StaffAppointmentIndex.php" class="fade-up dashboard-card p-8 group" style="animation-delay: 0.2s;">
                <div class="icon-box bg-purple-100 text-purple-600 group-hover:bg-purple-600 group-hover:text-white transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                </div>
                <h4 class="display-font text-xl text-slate-800 mb-2">Appointments</h4>
                <p class="text-sm text-slate-500 leading-relaxed font-medium">Coordinate donor evaluations and recipient consultations.</p>
            </a>
        </div>

        <div class="mt-12">
            <h3 class="fade-up text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-6" style="animation-delay: 0.3s;">System Log: Recent Transactions</h3>
            <div class="fade-up bg-white rounded-[32px] border border-slate-100 shadow-sm overflow-hidden" style="animation-delay: 0.4s;">
                <div class="overflow-x-auto custom-scrollbar">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50/50 border-b border-slate-100">
                                <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest">Transaction ID</th>
                                <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest">Linked Request</th>
                                <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest">Timestamp</th>
                                <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            <?php if ($transactions_query->num_rows > 0): ?>
                                <?php while($row = $transactions_query->fetch_assoc()): 
                                    // Status Badge Color Logic
                                    $badge_class = match($row['status']) {
                                        'completed' => 'bg-emerald-100 text-emerald-700',
                                        'cancelled' => 'bg-rose-100 text-rose-700',
                                        default => 'bg-amber-100 text-amber-700',
                                    };
                                ?>
                                <tr class="hover:bg-slate-50/50 transition-colors">
                                    <td class="px-8 py-5 font-bold text-slate-700 text-sm">
                                        <span class="text-slate-300 font-normal mr-1">#</span>TXN-<?= $row['transaction_id'] ?>
                                    </td>
                                    <td class="px-8 py-5">
                                        <div class="flex items-center gap-2">
                                            <div class="w-2 h-2 rounded-full bg-slate-200"></div>
                                            <span class="text-slate-500 text-sm font-medium uppercase tracking-tight">REQ-<?= $row['request_id'] ?></span>
                                        </div>
                                    </td>
                                    <td class="px-8 py-5 text-slate-500 text-sm font-medium">
                                        <?= date('M d, Y', strtotime($row['transaction_date'])) ?>
                                        <span class="text-slate-300 mx-2">|</span>
                                        <span class="text-xs text-slate-400"><?= date('h:i A', strtotime($row['transaction_date'])) ?></span>
                                    </td>
                                    <td class="px-8 py-5">
                                        <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest <?= $badge_class ?>">
                                            <?= $row['status'] ?>
                                        </span>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" class="px-8 py-12 text-center">
                                        <p class="text-slate-400 text-sm italic">No recent activity detected in the ledger.</p>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>

<?php include('../../includes/footer.php'); ?>