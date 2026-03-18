<?php
session_start();
include('../../../../includes/config.php');

if (!isset($_SESSION['account_id']) || $_SESSION['role'] !== 'staff') {
    header("Location: ../../../../unauthorized.php");
    exit();
}

$action = $_POST['action'] ?? '';

/* ============================================================
   ================= CREATE DONOR APPOINTMENT =================
   ============================================================ */
if ($action === 'create_donor_appointment') {

    $donor_id = intval($_POST['donor_id'] ?? 0);
    $appointment_date = $_POST['appointment_date'] ?? '';
    $appointment_type = $_POST['appointment_type'] ?? 'donation';
    $status = $_POST['status'] ?? 'scheduled';

    if ($donor_id <= 0 || empty($appointment_date)) {
        $_SESSION['error'] = "Please fill in all required fields.";
        header("Location: StaffAppointmentDonorCreate.php");
        exit();
    }

    $appointment_datetime = strtotime($appointment_date);
    $now = time();
    $date_only = date('Y-m-d', $appointment_datetime);
    $today_date = date('Y-m-d');

    // 1️⃣ Cannot book for today
    if ($date_only === $today_date) {
        $_SESSION['error'] = "You cannot create an appointment for the current date.";
        header("Location: StaffAppointmentDonorCreate.php");
        exit();
    }

    // 2️⃣ Past date/time check
    if ($appointment_datetime < $now) {
        $_SESSION['error'] = "Cannot create appointment for past date/time.";
        header("Location: StaffAppointmentDonorCreate.php");
        exit();
    }

    $hour = intval(date('H', $appointment_datetime));

    // 3️⃣ Operating hours check (7AM–7PM)
    if ($hour < 7 || $hour >= 19) {
        $_SESSION['error'] = "Appointments allowed only between 7:00 AM and 7:00 PM.";
        header("Location: StaffAppointmentDonorCreate.php");
        exit();
    }

    // 4️⃣ Check for any upcoming appointment (excluding cancelled & completed)
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
    $result_upcoming = $stmt_upcoming->get_result();

    if ($result_upcoming->num_rows > 0) {
        $_SESSION['error'] = "This donor already has an upcoming appointment.";
        header("Location: StaffAppointmentDonorCreate.php");
        exit();
    }

    // 5️⃣ Check if donor already has appointment that same day
    $stmt_day = $conn->prepare("
        SELECT * FROM appointments 
        WHERE user_type = 'donor' 
          AND user_id = ? 
          AND DATE(appointment_date) = ?
          AND status != 'cancelled'
    ");
    $stmt_day->bind_param("is", $donor_id, $date_only);
    $stmt_day->execute();
    $result_day = $stmt_day->get_result();

    if ($result_day->num_rows > 0) {
        $_SESSION['error'] = "This donor already has an appointment booked for this day.";
        header("Location: StaffAppointmentDonorCreate.php");
        exit();
    }

    // 6️⃣ Check if hour slot already taken by another donor
    $start_hour = date('Y-m-d H:00:00', $appointment_datetime);
    $end_hour   = date('Y-m-d H:59:59', $appointment_datetime);

    $stmt_hour = $conn->prepare("
        SELECT * FROM appointments 
        WHERE user_type = 'donor' 
          AND appointment_date BETWEEN ? AND ?
          AND status != 'cancelled'
    ");
    $stmt_hour->bind_param("ss", $start_hour, $end_hour);
    $stmt_hour->execute();
    $result_hour = $stmt_hour->get_result();

    if ($result_hour->num_rows > 0) {
        $_SESSION['error'] = "This time slot is already booked for a donor.";
        header("Location: StaffAppointmentDonorCreate.php");
        exit();
    }

    // ✅ Insert appointment
    $stmt = $conn->prepare("
    INSERT INTO appointments (user_type, user_id, appointment_date, type, status)
    VALUES ('donor', ?, ?, ?, ?)
");
$stmt->bind_param("isss", $donor_id, $appointment_date, $appointment_type, $status);

    if ($stmt->execute()) {
        $_SESSION['success'] = "Donor appointment created successfully.";
    } else {
        $_SESSION['error'] = "Failed to create appointment.";
    }

    header("Location: StaffAppointmentDonorCreate.php");
    exit();
}


/* ============================================================
   ================= UPDATE DONOR APPOINTMENT =================
   ============================================================ */
   $new_type = $_POST['appointment_type'] ?? '';
   
if ($action === 'update_donor_appointment') {

    $appointment_id = intval($_POST['appointment_id'] ?? 0);
    $new_date = $_POST['appointment_date'] ?? '';
    $new_status = $_POST['status'] ?? '';

    if ($appointment_id <= 0 || empty($new_date)) {
        $_SESSION['error'] = "Invalid appointment or missing date.";
        header("Location: StaffAppointmentDonorUpdate.php?id=" . $appointment_id);
        exit();
    }

    // Fetch current appointment
    $stmt_curr = $conn->prepare("
        SELECT appointment_date, status, type, user_id
        FROM appointments 
        WHERE appointment_id = ? AND user_type = 'donor'
    ");
    $stmt_curr->bind_param("i", $appointment_id);
    $stmt_curr->execute();
    $result_curr = $stmt_curr->get_result();

    if ($result_curr->num_rows === 0) {
        $_SESSION['error'] = "Appointment not found.";
        header("Location: StaffAppointmentDonorUpdate.php?id=" . $appointment_id);
        exit();
    }

    $current = $result_curr->fetch_assoc();
    $donor_id = $current['user_id'];

    $current_date = date('Y-m-d H:i', strtotime($current['appointment_date']));
    $new_date_normalized = date('Y-m-d H:i', strtotime($new_date));

if ($current_date === $new_date_normalized && $current['status'] === $new_status && $current['type'] === $new_type)
    {$_SESSION['error'] = "No changes detected.";
        header("Location: StaffAppointmentDonorUpdate.php?id=" . $appointment_id);
        exit();
    }

    $appointment_datetime = strtotime($new_date);
    $now = time();
    $date_only = date('Y-m-d', $appointment_datetime);
    $today_date = date('Y-m-d');

    // 1️⃣ Cannot set for today
    if ($date_only === $today_date) {
        $_SESSION['error'] = "You cannot set an appointment for the current date.";
        header("Location: StaffAppointmentDonorUpdate.php?id=" . $appointment_id);
        exit();
    }

    // 2️⃣ Past date/time check
    if ($appointment_datetime < $now) {
        $_SESSION['error'] = "Cannot set appointment for past date/time.";
        header("Location: StaffAppointmentDonorUpdate.php?id=" . $appointment_id);
        exit();
    }

    $hour = intval(date('H', $appointment_datetime));

    // 3️⃣ Operating hours check
    if ($hour < 7 || $hour >= 19) {
        $_SESSION['error'] = "Appointments allowed only between 7:00 AM and 7:00 PM.";
        header("Location: StaffAppointmentDonorUpdate.php?id=" . $appointment_id);
        exit();
    }

    // 4️⃣ Check for another upcoming appointment (exclude this one)
    $stmt_upcoming = $conn->prepare("
        SELECT * FROM appointments
        WHERE user_type = 'donor'
          AND user_id = ?
          AND appointment_date > NOW()
          AND status != 'cancelled'
          AND status != 'completed'
          AND appointment_id != ?
    ");
    $stmt_upcoming->bind_param("ii", $donor_id, $appointment_id);
    $stmt_upcoming->execute();
    $result_upcoming = $stmt_upcoming->get_result();

    if ($result_upcoming->num_rows > 0) {
        $_SESSION['error'] = "This donor already has another upcoming appointment.";
        header("Location: StaffAppointmentDonorUpdate.php?id=" . $appointment_id);
        exit();
    }

    // 5️⃣ Hour conflict check
    $start_hour = date('Y-m-d H:00:00', $appointment_datetime);
    $end_hour   = date('Y-m-d H:59:59', $appointment_datetime);

    $stmt_hour = $conn->prepare("
        SELECT * FROM appointments 
        WHERE appointment_date BETWEEN ? AND ?
          AND appointment_id != ?
          AND user_type = 'donor'
          AND status != 'cancelled'
    ");
    $stmt_hour->bind_param("ssi", $start_hour, $end_hour, $appointment_id);
    $stmt_hour->execute();
    $result_hour = $stmt_hour->get_result();

    if ($result_hour->num_rows > 0) {
        $_SESSION['error'] = "This time slot is already booked.";
        header("Location: StaffAppointmentDonorUpdate.php?id=" . $appointment_id);
        exit();
    }

    // ✅ Update appointment
$stmt = $conn->prepare("
UPDATE appointments
SET appointment_date = ?, type = ?, status = ?
WHERE appointment_id = ? AND user_type = 'donor'
");
$stmt->bind_param("sssi", $new_date, $new_type, $new_status, $appointment_id);

    if ($stmt->execute()) {
        $_SESSION['success'] = "Donor appointment updated successfully.";
    } else {
        $_SESSION['error'] = "Failed to update appointment.";
    }

    header("Location: StaffAppointmentDonorUpdate.php?id=" . $appointment_id);
    exit();
}
?>