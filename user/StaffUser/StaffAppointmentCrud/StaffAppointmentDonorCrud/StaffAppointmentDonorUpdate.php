<?php
session_start();
include('../../../../includes/config.php');
include('../../../../includes/header.php');

// STAFF access only
if (!isset($_SESSION['account_id']) || $_SESSION['role'] !== 'staff') {
    header("Location: ../../../../unauthorized.php");
    exit();
}

// Check for appointment ID
if (!isset($_GET['id'])) {
    header("Location: ../StaffAppointmentIndex.php");
    exit();
}

$appointment_id = intval($_GET['id']);

// Fetch the donor appointment using new schema
$stmt = $conn->prepare("
    SELECT a.appointment_date, a.status, a.type, u.first_name, u.last_name
    FROM appointments a
    JOIN donors_users u ON a.user_id = u.donor_id
    WHERE a.appointment_id = ? AND a.user_type = 'donor'
");
$stmt->bind_param("i", $appointment_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: ../StaffAppointmentIndex.php");
    exit();
}

$appointment = $result->fetch_assoc();
?>

<style>
.container { padding: 30px; }
form { max-width: 500px; margin: auto; }
label, select { display: block; margin-top: 15px; }
input, select { width: 100%; padding: 10px; margin: 10px 0; }
button {
    padding: 10px 15px;
    background: green;
    color: white;
    border: none;
}
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
    <h2>Update Donor Appointment</h2>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="error"><?= $_SESSION['error']; ?></div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="success"><?= $_SESSION['success']; ?></div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <form action="StaffAppointmentDonorStore.php" method="POST">
        <input type="hidden" name="action" value="update_donor_appointment">
        <input type="hidden" name="appointment_id" value="<?= $appointment_id; ?>">

        <label>Donor Name</label>
        <input type="text" value="<?= htmlspecialchars($appointment['first_name'] . ' ' . $appointment['last_name']); ?>" class="locked" disabled>

        <label>Appointment Date & Time</label>
        <input type="datetime-local" name="appointment_date"
            value="<?= date('Y-m-d\TH:i', strtotime($appointment['appointment_date'])); ?>">

<label>Appointment Type</label>
<select name="appointment_type" required>
    <option value="consultation" <?= $appointment['type']=='consultation'?'selected':'' ?>>Consultation</option>
    <option value="donation" <?= $appointment['type']=='donation'?'selected':'' ?>>Donation</option>
</select>

        <label>Status</label>
        <select name="status">
            <option value="">Select status</option>
            <option value="scheduled" <?= $appointment['status'] == 'scheduled' ? 'selected' : ''; ?>>Scheduled</option>
            <option value="completed" <?= $appointment['status'] == 'completed' ? 'selected' : ''; ?>>Completed</option>
            <option value="cancelled" <?= $appointment['status'] == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
        </select>

        <button type="submit">Update Donor Appointment</button>
        <a href="../StaffAppointmentIndex.php" class="back-btn">← Back to Index</a>
    </form>
</div>

<?php include('../../../../includes/footer.php'); ?>