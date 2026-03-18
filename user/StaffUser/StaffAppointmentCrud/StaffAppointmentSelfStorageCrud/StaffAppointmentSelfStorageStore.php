<?php
session_start();
include('../../../../includes/config.php');

// STAFF access only
if (!isset($_SESSION['account_id']) || $_SESSION['role'] !== 'staff') {
    header("Location: ../../../../unauthorized.php");
    exit();
}

$action = $_POST['action'] ?? '';

/* ============================================================
   ============== CREATE SELF-STORAGE APPOINTMENT =============
   ============================================================ */
if ($action === 'create_storage_appointment') {

    $storage_user_id = intval($_POST['storage_user_id'] ?? 0);
    $appointment_date = $_POST['appointment_date'] ?? '';
    $appointment_type = $_POST['appointment_type'] ?? 'storage';
    $status = $_POST['status'] ?? 'scheduled';

    if ($storage_user_id <= 0 || empty($appointment_date)) {
        $_SESSION['error'] = "Please fill in all required fields.";
        header("Location: StaffAppointmentSelfStorageCreate.php");
        exit();
    }

    $appointment_datetime = strtotime($appointment_date);
    $now = time();
    $date_only = date('Y-m-d', $appointment_datetime);
    $today_date = date('Y-m-d');

    // 1️⃣ Cannot book for today
    if ($date_only === $today_date) {
        $_SESSION['error'] = "You cannot create an appointment for the current date.";
        header("Location: StaffAppointmentSelfStorageCreate.php");
        exit();
    }

    // 2️⃣ Past date/time check
    if ($appointment_datetime < $now) {
        $_SESSION['error'] = "Cannot create appointment for past date/time.";
        header("Location: StaffAppointmentSelfStorageCreate.php");
        exit();
    }

    $hour = intval(date('H', $appointment_datetime));

    // 3️⃣ Operating hours check (7AM–7PM)
    if ($hour < 7 || $hour >= 19) {
        $_SESSION['error'] = "Appointments allowed only between 7:00 AM and 7:00 PM.";
        header("Location: StaffAppointmentSelfStorageCreate.php");
        exit();
    }

    // 4️⃣ Check for any upcoming appointment (excluding cancelled & completed)
    $stmt_upcoming = $conn->prepare("
        SELECT * FROM appointments
        WHERE user_type = 'storage' 
          AND user_id = ? 
          AND appointment_date > NOW() 
          AND status != 'cancelled'
          AND status != 'completed'
    ");
    $stmt_upcoming->bind_param("i", $storage_user_id);
    $stmt_upcoming->execute();
    $result_upcoming = $stmt_upcoming->get_result();

    if ($result_upcoming->num_rows > 0) {
        $_SESSION['error'] = "This user already has an upcoming appointment.";
        header("Location: StaffAppointmentSelfStorageCreate.php");
        exit();
    }

    // 5️⃣ Check if user already has appointment that same day
    $stmt_day = $conn->prepare("
        SELECT * FROM appointments 
        WHERE user_type = 'storage' 
          AND user_id = ? 
          AND DATE(appointment_date) = ?
          AND status != 'cancelled'
    ");
    $stmt_day->bind_param("is", $storage_user_id, $date_only);
    $stmt_day->execute();
    $result_day = $stmt_day->get_result();

    if ($result_day->num_rows > 0) {
        $_SESSION['error'] = "This user already has an appointment booked for this day.";
        header("Location: StaffAppointmentSelfStorageCreate.php");
        exit();
    }

    // 6️⃣ Check if hour slot already taken
    $start_hour = date('Y-m-d H:00:00', $appointment_datetime);
    $end_hour   = date('Y-m-d H:59:59', $appointment_datetime);

    $stmt_hour = $conn->prepare("
        SELECT * FROM appointments 
        WHERE user_type = 'storage' 
          AND appointment_date BETWEEN ? AND ?
          AND status != 'cancelled'
    ");
    $stmt_hour->bind_param("ss", $start_hour, $end_hour);
    $stmt_hour->execute();
    $result_hour = $stmt_hour->get_result();

    if ($result_hour->num_rows > 0) {
        $_SESSION['error'] = "This time slot is already booked for a storage user.";
        header("Location: StaffAppointmentSelfStorageCreate.php");
        exit();
    }

    // ✅ Insert appointment
    $stmt = $conn->prepare("
    INSERT INTO appointments (user_type, user_id, appointment_date, type, status)
    VALUES ('storage', ?, ?, ?, ?)
");
$stmt->bind_param("isss", $storage_user_id, $appointment_date, $appointment_type, $status);

    if ($stmt->execute()) {
        $_SESSION['success'] = "Self-storage appointment created successfully.";
    } else {
        $_SESSION['error'] = "Failed to create appointment.";
    }

    header("Location: StaffAppointmentSelfStorageCreate.php");
    exit();
}


/* ============================================================
   ============== UPDATE SELF-STORAGE APPOINTMENT =============
   ============================================================ */
  $new_type = $_POST['appointment_type'] ?? '';

   if ($action === 'update_storage_appointment') {

    $appointment_id = intval($_POST['appointment_id'] ?? 0);
    $new_date = $_POST['appointment_date'] ?? '';
    $new_status = $_POST['status'] ?? '';

    if ($appointment_id <= 0 || empty($new_date)) {
        $_SESSION['error'] = "Invalid appointment or missing date.";
        header("Location: StaffAppointmentSelfStorageUpdate.php?id=" . $appointment_id);
        exit();
    }

    // Fetch current appointment
    $stmt_curr = $conn->prepare("
        SELECT appointment_date, status, type, user_id
        FROM appointments 
        WHERE appointment_id = ? AND user_type = 'storage'
    ");
    $stmt_curr->bind_param("i", $appointment_id);
    $stmt_curr->execute();
    $result_curr = $stmt_curr->get_result();

    if ($result_curr->num_rows === 0) {
        $_SESSION['error'] = "Appointment not found.";
        header("Location: StaffAppointmentSelfStorageUpdate.php?id=" . $appointment_id);
        exit();
    }

    $current = $result_curr->fetch_assoc();
    $storage_user_id = $current['user_id'];

    $current_date = date('Y-m-d H:i', strtotime($current['appointment_date']));
    $new_date_normalized = date('Y-m-d H:i', strtotime($new_date));

if ($current_date === $new_date_normalized && $current['status'] === $new_status && $current['type'] === $new_type)
    {$_SESSION['error'] = "No changes detected.";
        $_SESSION['error'] = "No changes detected.";
        header("Location: StaffAppointmentSelfStorageUpdate.php?id=" . $appointment_id);
        exit();
    }

    $appointment_datetime = strtotime($new_date);
    $now = time();
    $date_only = date('Y-m-d', $appointment_datetime);
    $today_date = date('Y-m-d');

    // 1️⃣ Cannot set for today
    if ($date_only === $today_date) {
        $_SESSION['error'] = "You cannot set an appointment for the current date.";
        header("Location: StaffAppointmentSelfStorageUpdate.php?id=" . $appointment_id);
        exit();
    }

    // 2️⃣ Past date/time check
    if ($appointment_datetime < $now) {
        $_SESSION['error'] = "Cannot set appointment for past date/time.";
        header("Location: StaffAppointmentSelfStorageUpdate.php?id=" . $appointment_id);
        exit();
    }

    $hour = intval(date('H', $appointment_datetime));

    // 3️⃣ Operating hours check
    if ($hour < 7 || $hour >= 19) {
        $_SESSION['error'] = "Appointments allowed only between 7:00 AM and 7:00 PM.";
        header("Location: StaffAppointmentSelfStorageUpdate.php?id=" . $appointment_id);
        exit();
    }

    // 4️⃣ Check for another upcoming appointment (exclude this one)
    $stmt_upcoming = $conn->prepare("
        SELECT * FROM appointments
        WHERE user_type = 'storage'
          AND user_id = ?
          AND appointment_date > NOW()
          AND status != 'cancelled'
          AND status != 'completed'
          AND appointment_id != ?
    ");
    $stmt_upcoming->bind_param("ii", $storage_user_id, $appointment_id);
    $stmt_upcoming->execute();
    $result_upcoming = $stmt_upcoming->get_result();

    if ($result_upcoming->num_rows > 0) {
        $_SESSION['error'] = "This user already has another upcoming appointment.";
        header("Location: StaffAppointmentSelfStorageUpdate.php?id=" . $appointment_id);
        exit();
    }

    // 5️⃣ Hour conflict check
    $start_hour = date('Y-m-d H:00:00', $appointment_datetime);
    $end_hour   = date('Y-m-d H:59:59', $appointment_datetime);

    $stmt_hour = $conn->prepare("
        SELECT * FROM appointments 
        WHERE appointment_date BETWEEN ? AND ?
          AND appointment_id != ?
          AND user_type = 'storage'
          AND status != 'cancelled'
    ");
    $stmt_hour->bind_param("ssi", $start_hour, $end_hour, $appointment_id);
    $stmt_hour->execute();
    $result_hour = $stmt_hour->get_result();

    if ($result_hour->num_rows > 0) {
        $_SESSION['error'] = "This time slot is already booked.";
        header("Location: StaffAppointmentSelfStorageUpdate.php?id=" . $appointment_id);
        exit();
    }

    // ✅ Update appointment
$stmt = $conn->prepare("
UPDATE appointments
SET appointment_date = ?, type = ?, status = ?
WHERE appointment_id = ? AND user_type = 'storage'
");
$stmt->bind_param("sssi", $new_date, $new_type, $new_status, $appointment_id);

    if ($stmt->execute()) {
        $_SESSION['success'] = "Self-storage appointment updated successfully.";
    } else {
        $_SESSION['error'] = "Failed to update appointment.";
    }

    header("Location: StaffAppointmentSelfStorageUpdate.php?id=" . $appointment_id);
    exit();
}
?>