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

    $recipient_id = intval($_POST['recipient_id'] ?? 0);
    $specimen_id  = intval($_POST['specimen_id'] ?? 0);
    $requested_quantity = intval($_POST['requested_quantity'] ?? 0);

    // Validate basic input
    if ($recipient_id <= 0 || $specimen_id <= 0 || $requested_quantity <= 0) {
        $_SESSION['error'] = "Invalid input. Please select a recipient, specimen, and quantity.";
        header("Location: StaffSpecimenRequestRecipientCreate.php");
        exit();
    }

    // Check specimen quantity and get price
    $stmt = $conn->prepare("SELECT quantity, price FROM specimens WHERE specimen_id = ? LIMIT 1");
    $stmt->bind_param("i", $specimen_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        $_SESSION['error'] = "Specimen not found.";
        header("Location: StaffSpecimenRequestRecipientCreate.php");
        exit();
    }

    $specimen = $result->fetch_assoc();

    if ($requested_quantity > $specimen['quantity']) {
        $_SESSION['error'] = "Not enough specimen quantity available.";
        header("Location: StaffSpecimenRequestRecipientCreate.php");
        exit();
    }

    $unit_price = $specimen['price'];
    $total_price = $unit_price * $requested_quantity;

    // Handle receipt upload
    $receipt_path = null;
    if (isset($_FILES['receipt_image']) && $_FILES['receipt_image']['error'] === UPLOAD_ERR_OK) {
        $maxSize = 5 * 1024 * 1024;
        if ($_FILES['receipt_image']['size'] > $maxSize) {
            $_SESSION['error'] = "Receipt file too large. Max 5MB allowed.";
            header("Location: StaffSpecimenRequestRecipientCreate.php");
            exit();
        }
        $ext = pathinfo($_FILES['receipt_image']['name'], PATHINFO_EXTENSION);
        $filename = 'receipt_' . time() . '_' . rand(1000,9999) . '.' . $ext;
        $upload_dir = '../../../../uploads/receipts/';
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
        $target_path = $upload_dir . $filename;
        if (move_uploaded_file($_FILES['receipt_image']['tmp_name'], $target_path)) {
            $receipt_path = 'uploads/receipts/' . $filename;
        } else {
            $_SESSION['error'] = "Failed to upload receipt image.";
            header("Location: StaffSpecimenRequestRecipientCreate.php");
            exit();
        }
    }

    // Begin transaction
    $conn->begin_transaction();

    try {
        // Insert request
        $stmt = $conn->prepare("
            INSERT INTO specimen_requests 
            (recipient_id, specimen_id, requested_quantity, status, payment_status, fulfilled_date, unit_price, total_price, receipt_image)
            VALUES (?, ?, ?, 'fulfilled', 'paid', NOW(), ?, ?, ?)
        ");
        $stmt->bind_param("iiidds", $recipient_id, $specimen_id, $requested_quantity, $unit_price, $total_price, $receipt_path);
        $stmt->execute();
        $request_id = $stmt->insert_id;

        // Decrease specimen quantity
        $stmt = $conn->prepare("UPDATE specimens SET quantity = quantity - ? WHERE specimen_id = ?");
        $stmt->bind_param("ii", $requested_quantity, $specimen_id);
        $stmt->execute();

        // Inventory log
        $stmt = $conn->prepare("INSERT INTO inventory_logs (specimen_id, action, quantity) VALUES (?, 'used', ?)");
        $stmt->bind_param("ii", $specimen_id, $requested_quantity);
        $stmt->execute();

        // Transaction record
        $stmt = $conn->prepare("INSERT INTO transactions (request_id, status) VALUES (?, 'completed')");
        $stmt->bind_param("i", $request_id);
        $stmt->execute();

        $conn->commit();
        $_SESSION['success'] = "Specimen request created successfully.";
        header("Location: StaffSpecimenRequestRecipientCreate.php");
        exit();

    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['error'] = "Failed to create specimen request: " . $e->getMessage();
        header("Location: StaffSpecimenRequestRecipientCreate.php");
        exit();
    }
}

// ================= UPDATE BLOCK =================
if ($action === 'update_specimen_request') {

    $request_id = intval($_POST['request_id'] ?? 0);
    $new_status = $_POST['status'] ?? '';
    $new_payment_status = $_POST['payment_status'] ?? '';

    if ($request_id <= 0 || !in_array($new_status, ['pending','approved','rejected','fulfilled']) || !in_array($new_payment_status, ['unpaid','paid','refunded'])) {
        $_SESSION['error'] = "Invalid input.";
        header("Location: ../StaffSpecimenRequestIndex.php");
        exit();
    }

    // Fetch request and specimen
    $stmt = $conn->prepare("
        SELECT sr.request_id, sr.specimen_id, sr.requested_quantity, sr.status,
               s.quantity AS specimen_quantity
        FROM specimen_requests sr
        INNER JOIN specimens s ON sr.specimen_id = s.specimen_id
        WHERE sr.request_id = ?
        LIMIT 1
    ");
    $stmt->bind_param("i", $request_id);
    $stmt->execute();
    $request = $stmt->get_result()->fetch_assoc();

    if (!$request) {
        $_SESSION['error'] = "Request not found.";
        header("Location: ../StaffSpecimenRequestIndex.php");
        exit();
    }

    $requested_quantity = $request['requested_quantity'];
    $specimen_id = $request['specimen_id'];
    $current_specimen_quantity = $request['specimen_quantity'];

    // Begin transaction
    $conn->begin_transaction();

    try {
        // If status is fulfilled, check quantity
        if ($new_status === 'fulfilled' && $request['status'] !== 'fulfilled') {

            if ($requested_quantity > $current_specimen_quantity) {
                throw new Exception("Cannot fulfill request. Not enough specimen quantity available.");
            }

            // Decrease specimen quantity
            $stmt_update_specimen = $conn->prepare("UPDATE specimens SET quantity = quantity - ? WHERE specimen_id = ?");
            $stmt_update_specimen->bind_param("ii", $requested_quantity, $specimen_id);
            $stmt_update_specimen->execute();

            // Inventory log
            $stmt_log = $conn->prepare("INSERT INTO inventory_logs (specimen_id, action, quantity) VALUES (?, 'used', ?)");
            $stmt_log->bind_param("ii", $specimen_id, $requested_quantity);
            $stmt_log->execute();

            // Transaction record
            $stmt_trans = $conn->prepare("INSERT INTO transactions (request_id, status) VALUES (?, 'completed')");
            $stmt_trans->bind_param("i", $request_id);
            $stmt_trans->execute();

            // Set fulfilled date
            $fulfilled_date_sql = ", fulfilled_date = NOW()";
        } else {
            $fulfilled_date_sql = "";
        }

        // Update request status and payment status
        $stmt_update = $conn->prepare("
            UPDATE specimen_requests 
            SET status = ?, payment_status = ? $fulfilled_date_sql
            WHERE request_id = ?
        ");
        $stmt_update->bind_param("ssi", $new_status, $new_payment_status, $request_id);
        $stmt_update->execute();

        $conn->commit();
        $_SESSION['success'] = "Request updated successfully.";
        header("Location: ../StaffSpecimenRequestIndex.php");
        exit();

    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['error'] = $e->getMessage();
        header("Location: ../StaffSpecimenRequestIndex.php");
        exit();
    }
}
?>