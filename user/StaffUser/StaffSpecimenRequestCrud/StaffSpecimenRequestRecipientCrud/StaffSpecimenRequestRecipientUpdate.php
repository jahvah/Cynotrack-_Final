<?php
session_start();
include('../../../../includes/config.php');
include('../../../../includes/header.php');

// STAFF access only
if (!isset($_SESSION['account_id']) || $_SESSION['role'] !== 'staff') {
    header("Location: ../../../../unauthorized.php");
    exit();
}

// Get request_id
$request_id = intval($_GET['id'] ?? 0);
if ($request_id <= 0) {
    $_SESSION['error'] = "Invalid request ID.";
    header("Location: ../StaffSpecimenRequestIndex.php");
    exit();
}

// Fetch request
$query = "
SELECT sr.*, 
       ru.first_name AS recipient_first, ru.last_name AS recipient_last,
       s.unique_code,
       du.first_name AS donor_first, du.last_name AS donor_last
FROM specimen_requests sr
INNER JOIN recipients_users ru ON sr.recipient_id = ru.recipient_id
INNER JOIN specimens s ON sr.specimen_id = s.specimen_id
LEFT JOIN donors_users du ON s.specimen_owner_type = 'donor' AND s.specimen_owner_id = du.donor_id
WHERE sr.request_id = ?
LIMIT 1";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $request_id);
$stmt->execute();
$request = $stmt->get_result()->fetch_assoc();

if (!$request) {
    $_SESSION['error'] = "Request not found.";
    header("Location: ../StaffSpecimenRequestIndex.php");
    exit();
}

$status_options = ['pending', 'approved', 'rejected', 'fulfilled'];
$payment_options = ['unpaid', 'paid', 'refunded', 'waiting_payment'];
?>

<style>
.container { padding: 30px; }
form { max-width: 500px; margin: auto; }
label, select { display: block; margin-top: 15px; font-weight: bold; }
input, select { width: 100%; padding: 10px; margin: 10px 0; border: 1px solid #ccc; border-radius: 4px; }
button {
    padding: 10px 15px;
    background: green;
    color: white;
    border: none;
    cursor: pointer;
    width: 100%;
    font-weight: bold;
    margin-top: 20px;
}
button:hover { background: #006400; }
.locked { background:#eee; cursor: not-allowed; }
.error { background:#f8d7da; color:#721c24; padding:10px; margin-bottom: 15px; border-radius: 4px; }
.success { background:#d4edda; color:#155724; padding:10px; margin-bottom: 15px; border-radius: 4px; }

.back-btn {
    display: inline-block;
    margin-bottom: 15px;
    padding: 8px 12px;
    background: #555;
    color: white;
    text-decoration: none;
    border-radius: 5px;
}
.back-btn:hover { background: #333; }
.view-link { color: blue; text-decoration: underline; font-size: 0.9em; }
</style>

<div class="container">
    <h2>Update Specimen Request #<?= $request['request_id']; ?></h2>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="error"><?= $_SESSION['error']; ?></div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="success"><?= $_SESSION['success']; ?></div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <form action="StaffSpecimenRequestRecipientStore.php" method="POST">
        <input type="hidden" name="action" value="update_specimen_request">
        <input type="hidden" name="request_id" value="<?= $request['request_id']; ?>">

        <label>Recipient Name</label>
        <input type="text" value="<?= htmlspecialchars($request['recipient_first'] . ' ' . $request['recipient_last']); ?>" class="locked" disabled>

        <label>Specimen Code (Source: <?= ucfirst($request['donor_first'] ?? 'System'); ?>)</label>
        <input type="text" value="<?= $request['unique_code']; ?> (<?= $request['requested_quantity']; ?> Units)" class="locked" disabled>

        <?php if(!empty($request['receipt_image'])): ?>
            <label>Payment Receipt</label>
            <div style="margin-top: 5px;">
                <a href="../../../<?= $request['receipt_image']; ?>" target="_blank" class="view-link">View Uploaded Attachment</a>
            </div>
        <?php endif; ?>

        <label>Request Status</label>
        <select name="status" required>
            <?php foreach ($status_options as $status): ?>
                <option value="<?= $status; ?>" <?= $request['status'] === $status ? 'selected' : ''; ?>>
                    <?= ucfirst($status); ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label>Payment Status</label>
        <select name="payment_status" required>
            <?php foreach ($payment_options as $payment): ?>
                <option value="<?= $payment; ?>" <?= $request['payment_status'] === $payment ? 'selected' : ''; ?>>
                    <?= ucfirst(str_replace('_', ' ', $payment)); ?>
                </option>
            <?php endforeach; ?>
        </select>

        <button type="submit">Update Request Details</button>
        <div style="text-align: center; margin-top: 15px;">
            <a href="../StaffSpecimenRequestIndex.php" class="back-btn">← Back to Index</a>
        </div>
    </form>
</div>

<?php include('../../../../includes/footer.php'); ?>