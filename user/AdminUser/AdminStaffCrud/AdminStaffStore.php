<?php
session_start();
include('../../../includes/config.php');

//admin access only
if (!isset($_SESSION['account_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../../unauthorized.php");
    exit();
}

if (!isset($_POST['action'])) {
    header("Location: AdminStaffIndex.php");
    exit();
}

//create staff
if ($_POST['action'] === 'AdminStaffStore') {

    $username   = trim($_POST['username']);
    $email      = trim($_POST['email']);
    $password   = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $first_name = trim($_POST['first_name']);
    $last_name  = trim($_POST['last_name']);
    $status     = $_POST['status'] ?? 'active';

       //EMAIL MUST BE @gmail.com
    if (!preg_match("/^[a-zA-Z0-9._%+-]+@gmail\.com$/", $email)) {
        $_SESSION['error'] = "Email must be a valid @gmail.com address";
        header("Location: AdminStaffCreate.php");
        exit();
    }

    // PROFILE IMAGE
    $image_name = null;
    if (!empty($_FILES['profile_image']['name'])) {
    $target_dir = "../../../uploads/";
    $image_name = time() . "_" . basename($_FILES["profile_image"]["name"]);

    // Validate file type (image only)
    $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
    $file_extension = strtolower(pathinfo($image_name, PATHINFO_EXTENSION));

    if (!in_array($file_extension, $allowed_types)) {
        $_SESSION['error'] = "Profile image must be a valid image file (JPG, JPEG, PNG, GIF)";
        header("Location: AdminStaffCreate.php");
        exit();
    }

    move_uploaded_file($_FILES["profile_image"]["tmp_name"], $target_dir . $image_name);
} else {
    $image_name = "default.png"; // default placeholder if no image uploaded
}

    // Check duplicates
    $stmtUser = $conn->prepare("SELECT account_id FROM accounts WHERE username=?");
    $stmtUser->bind_param("s", $username);
    $stmtUser->execute();
    $stmtUser->store_result();

    $stmtEmail = $conn->prepare("SELECT account_id FROM accounts WHERE email=?");
    $stmtEmail->bind_param("s", $email);
    $stmtEmail->execute();
    $stmtEmail->store_result();

    if ($stmtUser->num_rows > 0 && $stmtEmail->num_rows > 0) {
        $_SESSION['error'] = "Username and Email already exist";
        header("Location: AdminStaffCreate.php");
        exit();
    } elseif ($stmtUser->num_rows > 0) {
        $_SESSION['error'] = "Username already exists";
        header("Location: AdminStaffCreate.php");
        exit();
    } elseif ($stmtEmail->num_rows > 0) {
        $_SESSION['error'] = "Email already exists";
        header("Location: AdminStaffCreate.php");
        exit();
    }

    // GET ROLE ID FOR STAFF
    $roleQuery = mysqli_query($conn, "SELECT role_id FROM roles WHERE role_name='staff'");
    $role = mysqli_fetch_assoc($roleQuery);
    $role_id = $role['role_id'];

    // Insert account
    $stmt1 = $conn->prepare("
        INSERT INTO accounts (username, email, password_hash, role_id, status)
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmt1->bind_param("sssis", $username, $email, $password, $role_id, $status);
    $stmt1->execute();
    $account_id = $stmt1->insert_id;

    // Insert staff
    $stmt2 = $conn->prepare("
            INSERT INTO staff (account_id, first_name, last_name, profile_image)
            VALUES (?, ?, ?, ?)
    ");
    $stmt2->bind_param("isss", $account_id, $first_name, $last_name, $image_name);
    $stmt2->execute();

    $_SESSION['success'] = "Staff account created successfully";
    header("Location: AdminStaffCreate.php");
    exit();
}

// update staff
if ($_POST['action'] === 'AdminStaffUpdate') {

    $staff_id   = intval($_POST['staff_id']);
    $account_id = intval($_POST['account_id']);
    $redirect   = "AdminStaffUpdate.php?id=" . $staff_id;

    // 🔎 Fetch CURRENT database values
    $stmt = $conn->prepare("SELECT * FROM staff WHERE staff_id=?");
    $stmt->bind_param("i", $staff_id);
    $stmt->execute();
    $current_staff = $stmt->get_result()->fetch_assoc();

    $stmt = $conn->prepare("SELECT status FROM accounts WHERE account_id=?");
    $stmt->bind_param("i", $account_id);
    $stmt->execute();
    $current_account = $stmt->get_result()->fetch_assoc();

    // 📝 New submitted values
    $first_name = trim($_POST['first_name']);
    $last_name  = trim($_POST['last_name']);
    $status     = trim($_POST['status']);

    $updated = false;

    // ================= ACCOUNT STATUS =================
    if (!empty($status) && $status !== $current_account['status']) {
        $stmt = $conn->prepare("UPDATE accounts SET status=? WHERE account_id=?");
        $stmt->bind_param("si", $status, $account_id);
        $stmt->execute();
        $updated = true;
    }

    // ================= STAFF FIELDS =================
    if (!empty($first_name) && $first_name !== $current_staff['first_name']) {
        $stmt = $conn->prepare("UPDATE staff SET first_name=? WHERE staff_id=?");
        $stmt->bind_param("si", $first_name, $staff_id);
        $stmt->execute();
        $updated = true;
    }

    if (!empty($last_name) && $last_name !== $current_staff['last_name']) {
        $stmt = $conn->prepare("UPDATE staff SET last_name=? WHERE staff_id=?");
        $stmt->bind_param("si", $last_name, $staff_id);
        $stmt->execute();
        $updated = true;
    }

    // ================= PROFILE IMAGE =================
    if (!empty($_FILES['profile_image']['name'])) {

        $target_dir = "../../../uploads/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        $image_name = time() . "_" . basename($_FILES['profile_image']['name']);
        $target_file = $target_dir . $image_name;

        // ✅ Validate image
        $check = getimagesize($_FILES["profile_image"]["tmp_name"]);
        if ($check === false) {
            $_SESSION['error'] = "Uploaded file must be an image (jpg, png, gif).";
            header("Location: $redirect");
            exit();
        }

        if (move_uploaded_file($_FILES["profile_image"]["tmp_name"], $target_file)) {

            // Delete old image if exists
            if (!empty($current_staff['profile_image']) &&
                file_exists($target_dir . $current_staff['profile_image'])) {

                unlink($target_dir . $current_staff['profile_image']);
            }

            $stmt = $conn->prepare("UPDATE staff SET profile_image=? WHERE staff_id=?");
            $stmt->bind_param("si", $image_name, $staff_id);
            $stmt->execute();
            $updated = true;
        }
    }

    // ================= FINAL MESSAGE =================
    if ($updated) {
        $_SESSION['success'] = "Staff updated successfully!";
    } else {
        $_SESSION['error'] = "No update detected.";
    }

    header("Location: $redirect");
    exit();
}
