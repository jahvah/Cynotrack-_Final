<?php
session_start();
include('../../../../includes/config.php');

// STAFF access only
if (!isset($_SESSION['account_id']) || $_SESSION['role'] !== 'staff') {
    header("Location: ../../../../unauthorized.php");
    exit();
}

$action = $_POST['action'] ?? '';

if ($action === 'create_specimen_request') {

    $storage_user_id    = intval($_POST['storage_user_id'] ?? 0);
    $specimen_id        = intval($_POST['specimen_id'] ?? 0);
    $requested_quantity = intval($_POST['requested_quantity'] ?? 0);

    // Basic validation
    if ($storage_user_id <= 0 || $specimen_id <= 0 || $requested_quantity <= 0) {
        $_SESSION['error'] = "Invalid input.";
        header("Location: StaffSpecimenRequestSelfStorageCreate.php");
        exit();
    }

    // 1️⃣ Fetch specimen and price
    $stmt = $conn->prepare("SELECT quantity, price FROM specimens WHERE specimen_id = ? LIMIT 1");
    $stmt->bind_param("i", $specimen_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        $_SESSION['error'] = "Specimen not found.";
        header("Location: StaffSpecimenRequestSelfStorageCreate.php");
        exit();
    }

    $specimen = $result->fetch_assoc();

    if ($requested_quantity > $specimen['quantity']) {
        $_SESSION['error'] = "Not enough specimen quantity available.";
        header("Location: StaffSpecimenRequestSelfStorageCreate.php");
        exit();
    }

    $unit_price  = $specimen['price'];
    $total_price = $unit_price * $requested_quantity;

    // 2️⃣ Handle receipt upload
    $receipt_path = null;

    if (isset($_FILES['receipt_image']) && $_FILES['receipt_image']['error'] === UPLOAD_ERR_OK) {

        $maxSize = 5 * 1024 * 1024;

        if ($_FILES['receipt_image']['size'] > $maxSize) {
            $_SESSION['error'] = "Receipt file too large. Max 5MB allowed.";
            header("Location: StaffSpecimenRequestSelfStorageCreate.php");
            exit();
        }

        $ext = pathinfo($_FILES['receipt_image']['name'], PATHINFO_EXTENSION);
        $filename = 'receipt_' . time() . '_' . rand(1000,9999) . '.' . $ext;

        $upload_dir = '../../../../uploads/receipts/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }

        $target_path = $upload_dir . $filename;

        if (move_uploaded_file($_FILES['receipt_image']['tmp_name'], $target_path)) {
            $receipt_path = 'uploads/receipts/' . $filename;
        } else {
            $_SESSION['error'] = "Failed to upload receipt image.";
            header("Location: StaffSpecimenRequestSelfStorageCreate.php");
            exit();
        }
    }

    // Begin transaction
    $conn->begin_transaction();

    try {

        // 3️⃣ Insert request (UPDATED)
        $stmt = $conn->prepare("
            INSERT INTO specimen_requests
            (owner_request_type, owner_request_id, specimen_id, requested_quantity, status, payment_status, fulfilled_date, unit_price, total_price, receipt_image)
            VALUES ('storage', ?, ?, ?, 'fulfilled', 'paid', NOW(), ?, ?, ?)
        ");

        $stmt->bind_param(
            "iiidds",
            $storage_user_id,
            $specimen_id,
            $requested_quantity,
            $unit_price,
            $total_price,
            $receipt_path
        );

        $stmt->execute();
        $request_id = $stmt->insert_id;

        // 4️⃣ Decrease specimen quantity
        $stmt = $conn->prepare("
            UPDATE specimens
            SET quantity = quantity - ?
            WHERE specimen_id = ?
        ");
        $stmt->bind_param("ii", $requested_quantity, $specimen_id);
        $stmt->execute();

        // 5️⃣ Inventory log
        $stmt = $conn->prepare("
            INSERT INTO inventory_logs (specimen_id, action, quantity)
            VALUES (?, 'used', ?)
        ");
        $stmt->bind_param("ii", $specimen_id, $requested_quantity);
        $stmt->execute();

        // 6️⃣ Transaction record
        $stmt = $conn->prepare("
            INSERT INTO transactions (request_id, status)
            VALUES (?, 'completed')
        ");
        $stmt->bind_param("i", $request_id);
        $stmt->execute();

        $conn->commit();

        $_SESSION['success'] = "Self-storage specimen request created successfully.";
        header("Location: StaffSpecimenRequestSelfStorageCreate.php");
        exit();

    } catch (Exception $e) {

        $conn->rollback();

        $_SESSION['error'] = "Failed: " . $e->getMessage();
        header("Location: StaffSpecimenRequestSelfStorageCreate.php");
        exit();
    }
}
?>