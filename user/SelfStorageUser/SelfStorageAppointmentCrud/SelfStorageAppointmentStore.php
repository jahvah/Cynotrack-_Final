<?php
session_start();
include('../../../includes/config.php');

if (!isset($_SESSION['account_id']) || $_SESSION['role'] !== 'self-storage') {
    header("Location: ../../../../unauthorized.php");
    exit();
}

$action = $_POST['action'] ?? '';
$account_id = $_SESSION['account_id'];

// Get logged-in storage_user_id
$stmt_storage = $conn->prepare("SELECT storage_user_id FROM self_storage_users WHERE account_id = ? LIMIT 1");
$stmt_storage->bind_param("i", $account_id);
$stmt_storage->execute();
$result_storage = $stmt_storage->get_result();

if ($result_storage->num_rows === 0) {
    $_SESSION['error'] = "Storage user record not found.";
    header("Location: ../SelfStorageAppointmentIndex.php");
    exit();
}

$storage_user_id = $result_storage->fetch_assoc()['storage_user_id'];

/// ================= CREATE SELF-STORAGE APPOINTMENT =================// ================= CREATE SELF-STORAGE APPOINTMENT =================
if ($action === 'create_storage_appointment') {

    $appointment_date = $_POST['appointment_date'] ?? '';

    if (empty($appointment_date)) {
        $_SESSION['error'] = "Please select appointment date.";
        header("Location: SelfStorageAppointmentCreate.php");
        exit();
    }

    $appointment_datetime = strtotime($appointment_date); 
    $now = time();
    $today_date = date('Y-m-d', $now);
    $date_only = date('Y-m-d', $appointment_datetime);

    // 1️⃣ Cannot book for today
    if ($date_only === $today_date) {
        $_SESSION['error'] = "You cannot create an appointment for the current date.";
        header("Location: SelfStorageAppointmentCreate.php");
        exit();
    }

    // 2️⃣ Past date/time check
    if ($appointment_datetime < $now) {
        $_SESSION['error'] = "Cannot create appointment for past date/time.";
        header("Location: SelfStorageAppointmentCreate.php");
        exit();
    }

    $hour = intval(date('H', $appointment_datetime));

    // 3️⃣ Operating hours check
    if ($hour < 7 || $hour >= 19) {
        $_SESSION['error'] = "Appointments allowed only between 7:00 AM and 7:00 PM.";
        header("Location: SelfStorageAppointmentCreate.php");
        exit();
    }

    // 4️⃣ Check for any upcoming appointment for this storage user (excluding cancelled ones)
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
    if ($stmt_upcoming->get_result()->num_rows > 0) {
        $_SESSION['error'] = "You already have an upcoming appointment.";
        header("Location: SelfStorageAppointmentCreate.php");
        exit();
    }

    // ✅ Insert appointment
    $stmt = $conn->prepare("
        INSERT INTO appointments (user_type, user_id, appointment_date, type)
        VALUES ('storage', ?, ?, 'storage')
    ");
    $stmt->bind_param("is", $storage_user_id, $appointment_date);

    if ($stmt->execute()) {
        $_SESSION['success'] = "Appointment created successfully.";
    } else {
        $_SESSION['error'] = "Failed to create appointment.";
    }

    header("Location: SelfStorageAppointmentIndex.php");
    exit();
}

// ================= UPDATE STORAGE APPOINTMENT =================
if ($action === 'update_storage_appointment') {

    $appointment_id = intval($_POST['appointment_id'] ?? 0);
    $new_date = $_POST['appointment_date'] ?? '';

    if ($appointment_id <= 0 || empty($new_date)) {
        $_SESSION['error'] = "Invalid appointment or missing date.";
        header("Location: SelfStorageAppointmentUpdate.php?id=" . $appointment_id);
        exit();
    }

    // Ensure appointment belongs to this storage user
    $stmt_curr = $conn->prepare("
        SELECT appointment_date 
        FROM appointments 
        WHERE appointment_id = ? 
        AND user_type = 'storage'
        AND user_id = ?
    ");
    $stmt_curr->bind_param("ii", $appointment_id, $storage_user_id);
    $stmt_curr->execute();
    $result_curr = $stmt_curr->get_result();

    if ($result_curr->num_rows === 0) {
        $_SESSION['error'] = "Appointment not found.";
        header("Location: SelfStorageAppointmentIndex.php");
        exit();
    }

    $current = $result_curr->fetch_assoc();

    // No changes check
    if (date('Y-m-d H:i', strtotime($current['appointment_date'])) === 
        date('Y-m-d H:i', strtotime($new_date))) {

        $_SESSION['error'] = "No changes detected.";
        header("Location: SelfStorageAppointmentUpdate.php?id=" . $appointment_id);
        exit();
    }

    $appointment_datetime = strtotime($new_date);
    $now = time();

    if ($appointment_datetime < $now) {
        $_SESSION['error'] = "Cannot set appointment for past date.";
        header("Location: SelfStorageAppointmentUpdate.php?id=" . $appointment_id);
        exit();
    }

    $hour = intval(date('H', $appointment_datetime));
    if ($hour < 7 || $hour >= 19) {
        $_SESSION['error'] = "Appointments allowed only between 7:00 AM and 7:00 PM.";
        header("Location: SelfStorageAppointmentUpdate.php?id=" . $appointment_id);
        exit();
    }

    // Hour conflict check (excluding current appointment and cancelled appointments)
$stmt_hour = $conn->prepare("
    SELECT * FROM appointments 
    WHERE appointment_date BETWEEN ? AND ?
    AND appointment_id != ?
    AND user_type = 'storage'
    AND status != 'cancelled'
    AND status != 'completed'
");
$stmt_hour->bind_param("ssi", $start_hour, $end_hour, $appointment_id);
$stmt_hour->execute();

if ($stmt_hour->get_result()->num_rows > 0) {
    $_SESSION['error'] = "This time slot is already booked.";
    header("Location: SelfStorageAppointmentUpdate.php?id=" . $appointment_id);
    exit();
}
    // ✅ Update (status NOT changed by storage user)
    $stmt = $conn->prepare("
        UPDATE appointments
        SET appointment_date = ?
        WHERE appointment_id = ?
        AND user_type = 'storage'
        AND user_id = ?
    ");
    $stmt->bind_param("sii", $new_date, $appointment_id, $storage_user_id);

    if ($stmt->execute()) {
        $_SESSION['success'] = "Appointment updated successfully.";
    } else {
        $_SESSION['error'] = "Failed to update appointment.";
    }

    header("Location: SelfStorageAppointmentIndex.php");
    exit();
}
?>