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

// Updated query to use unified specimens table for self-storage users
$stmt = $conn->prepare("SELECT unique_code FROM specimens WHERE specimen_id = ? AND specimen_owner_type = 'storage'");
$stmt->bind_param("i", $specimen_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: StaffSpecimenIndex.php");
    exit();
}

$specimen = $result->fetch_assoc();
?>

<style>
.container { padding: 30px; }
form { max-width: 500px; margin: auto; }
label, select { display: block; margin-top: 15px; }
input, select { width: 100%; padding: 10px; margin: 10px 0; }
button { padding: 10px 15px; background: green; color: white; border: none; }
.locked { background:#eee; }
.error { background:#f8d7da; color:#721c24; padding:10px; }
.success { background:#d4edda; color:#155724; padding:10px; }

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
</style>

<div class="container">
    <h2>Update Self-Storage Specimen</h2>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="error"><?= $_SESSION['error']; ?></div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="success"><?= $_SESSION['success']; ?></div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <form action="StaffSpecimenSelfStorageStore.php" method="POST">
        <input type="hidden" name="action" value="update_self_storage_specimen">
        <input type="hidden" name="specimen_id" value="<?= $specimen_id; ?>">

        <label>Unique Code</label>
        <input type="text" value="<?= htmlspecialchars($specimen['unique_code']); ?>" class="locked" disabled>
        <input type="hidden" name="unique_code" value="<?= htmlspecialchars($specimen['unique_code']); ?>">

        <label>Quantity</label>
        <input type="number" name="quantity" min="0">

        <label>Status</label>
        <select name="status">
            <option value="">Select status</option>
            <option value="approved">Approved</option>
            <option value="disapproved">Disapproved</option>
            <option value="stored">Stored</option>
            <option value="used">Used</option>
            <option value="expired">Expired</option>
            <option value="disposed">Disposed</option>
        </select>

        <label>Storage Location</label>
        <input type="text" name="storage_location">

        <label>Expiration Date</label>
        <input type="date" name="expiration_date">

        <button type="submit">Update Self-Storage Specimen</button>
        <a href="../StaffSpecimenIndex.php" class="back-btn">← Back to Index</a>
    </form>
</div>

<?php include('../../../../includes/footer.php'); ?>