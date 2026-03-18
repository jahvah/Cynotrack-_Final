<?php
session_start();
include('../../../includes/config.php');

if (!isset($_SESSION['account_id']) || $_SESSION['role'] !== 'donor') {
    header("Location: ../../../../unauthorized.php");
    exit();
}

$action = $_POST['action'] ?? '';
$account_id = $_SESSION['account_id'];

// Get logged-in donor_id
$stmt_donor = $conn->prepare("SELECT donor_id FROM donors_users WHERE account_id = ? LIMIT 1");
$stmt_donor->bind_param("i", $account_id);
$stmt_donor->execute();
$result_donor = $stmt_donor->get_result();

if ($result_donor->num_rows === 0) {
    $_SESSION['error'] = "Donor record not found.";
    header("Location: ../DonorAppointmentIndex.php");
    exit();
}

$donor_id = $result_donor->fetch_assoc()['donor_id'];


// ================= CREATE DONOR APPOINTMENT =================
if ($action === 'create_donor_appointment') {

    $appointment_date = $_POST['appointment_date'] ?? '';

    if (empty($appointment_date)) {
        $_SESSION['error'] = "Please select appointment date.";
        header("Location: DonorAppointmentCreate.php");
        exit();
    }

    $appointment_datetime = strtotime($appointment_date); 
    $now = time();
    $today_date = date('Y-m-d', $now);
    $date_only = date('Y-m-d', $appointment_datetime);

    // 1️⃣ Cannot book for today
    if ($date_only === $today_date) {
        $_SESSION['error'] = "You cannot create an appointment for the current date.";
        header("Location: DonorAppointmentCreate.php");
        exit();
    }

    // 2️⃣ Past date/time check
    if ($appointment_datetime < $now) {
        $_SESSION['error'] = "Cannot create appointment for past date/time.";
        header("Location: DonorAppointmentCreate.php");
        exit();
    }

    $hour = intval(date('H', $appointment_datetime));

    // 3️⃣ Operating hours check
    if ($hour < 7 || $hour >= 19) {
        $_SESSION['error'] = "Appointments allowed only between 7:00 AM and 7:00 PM.";
        header("Location: DonorAppointmentCreate.php");
        exit();
    }

    // 4️⃣ Check for any upcoming appointment (excluding cancelled ones)
    $stmt_upcoming = $conn->prepare("
        SELECT * FROM appointments
        WHERE user_type = 'donor' 
          AND user_id = ? 
          AND appointment_date > NOW() 
          AND status != 'cancelled'
          AND status != 'completed'
    ");
    $stmt_upcoming->bind_param("i", $donor_id);
    $stmt_upcoming->execute();
    if ($stmt_upcoming->get_result()->num_rows > 0) {
        $_SESSION['error'] = "You already have an upcoming appointment.";
        header("Location: DonorAppointmentCreate.php");
        exit();
    }

    // ✅ Insert appointment
    $stmt = $conn->prepare("
        INSERT INTO appointments (user_type, user_id, appointment_date, type)
        VALUES ('donor', ?, ?, 'donation')
    ");
    $stmt->bind_param("is", $donor_id, $appointment_date);

    if ($stmt->execute()) {
        $_SESSION['success'] = "Appointment created successfully.";
    } else {
        $_SESSION['error'] = "Failed to create appointment.";
    }

    header("Location: DonorAppointmentIndex.php");
    exit();
}

// ================= UPDATE DONOR APPOINTMENT =================
if ($action === 'update_donor_appointment') {

    $appointment_id = intval($_POST['appointment_id'] ?? 0);
    $new_date = $_POST['appointment_date'] ?? '';

    if ($appointment_id <= 0 || empty($new_date)) {
        $_SESSION['error'] = "Invalid appointment or missing date.";
        header("Location: DonorAppointmentUpdate.php?id=" . $appointment_id);
        exit();
    }

    // Ensure appointment belongs to this donor
    $stmt_curr = $conn->prepare("
        SELECT appointment_date 
        FROM appointments 
        WHERE appointment_id = ? 
        AND user_type = 'donor'
        AND user_id = ?
    ");
    $stmt_curr->bind_param("ii", $appointment_id, $donor_id);
    $stmt_curr->execute();
    $result_curr = $stmt_curr->get_result();

    if ($result_curr->num_rows === 0) {
        $_SESSION['error'] = "Appointment not found.";
        header("Location: DonorAppointmentIndex.php");
        exit();
    }

    $current = $result_curr->fetch_assoc();

    // No changes check
    if (date('Y-m-d H:i', strtotime($current['appointment_date'])) === 
        date('Y-m-d H:i', strtotime($new_date))) {

        $_SESSION['error'] = "No changes detected.";
        header("Location: DonorAppointmentUpdate.php?id=" . $appointment_id);
        exit();
    }

    $appointment_datetime = strtotime($new_date);
    $now = time();

    if ($appointment_datetime < $now) {
        $_SESSION['error'] = "Cannot set appointment for past date.";
        header("Location: DonorAppointmentUpdate.php?id=" . $appointment_id);
        exit();
    }

    $hour = intval(date('H', $appointment_datetime));
    if ($hour < 7 || $hour >= 19) {
        $_SESSION['error'] = "Appointments allowed only between 7:00 AM and 7:00 PM.";
        header("Location: DonorAppointmentUpdate.php?id=" . $appointment_id);
        exit();
    }

    // Hour conflict check (excluding current appointment and cancelled appointments)
$stmt_hour = $conn->prepare("
    SELECT * FROM appointments 
    WHERE appointment_date BETWEEN ? AND ?
    AND appointment_id != ?
    AND user_type = 'donor'
    AND status != 'cancelled'
    
");
$stmt_hour->bind_param("ssi", $start_hour, $end_hour, $appointment_id);
$stmt_hour->execute();

if ($stmt_hour->get_result()->num_rows > 0) {
    $_SESSION['error'] = "This time slot is already booked.";
    header("Location: DonorAppointmentUpdate.php?id=" . $appointment_id);
    exit();
}

    // ✅ Update (status NOT changed by donor)
    $stmt = $conn->prepare("
        UPDATE appointments
        SET appointment_date = ?
        WHERE appointment_id = ?
        AND user_type = 'donor'
        AND user_id = ?
    ");
    $stmt->bind_param("sii", $new_date, $appointment_id, $donor_id);

    if ($stmt->execute()) {
        $_SESSION['success'] = "Appointment updated successfully.";
    } else {
        $_SESSION['error'] = "Failed to update appointment.";
    }

    header("Location: DonorAppointmentIndex.php");
    exit();
}
?>