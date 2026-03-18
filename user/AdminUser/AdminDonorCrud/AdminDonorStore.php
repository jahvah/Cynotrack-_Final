<?php
session_start();
include('../../../includes/config.php');

//admin protection
if (!isset($_SESSION['account_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../../unauthorized.php");
    exit();
}

if (!isset($_POST['action'])) {
    header("Location: AdminDonorIndex.php");
    exit();
}

//create donor with duplicate checks for email and username
if ($_POST['action'] === 'AdminDonorStore') {

    // REQUIRED FIELDS
    $username         = trim($_POST['username']);
    $email            = trim($_POST['email']);
    $password         = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $first_name       = trim($_POST['first_name']);
    $last_name        = trim($_POST['last_name']);
    $status           = $_POST['status'] ?? 'active';
    $medical_history  = trim($_POST['medical_history']);
    $evaluation_status= $_POST['evaluation_status'] ?? 'pending'; // can be 'pending', 'approved', 'rejected'
    $height_cm        = (int)$_POST['height_cm'];
    $weight_kg        = (int)$_POST['weight_kg'];
    $eye_color        = trim($_POST['eye_color']);
    $hair_color       = trim($_POST['hair_color']);
    $blood_type       = trim($_POST['blood_type']);
    $ethnicity        = trim($_POST['ethnicity']);

       //EMAIL MUST BE @gmail.com
    if (!preg_match("/^[a-zA-Z0-9._%+-]+@gmail\.com$/", $email)) {
        $_SESSION['error'] = "Email must be a valid @gmail.com address";
        header("Location: AdminDonorCreate.php");
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
        header("Location: AdminDonorCreate.php");
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
    header("Location: AdminDonorCreate.php");
    exit();
} elseif ($stmtUser->num_rows > 0) {
    $_SESSION['error'] = "Username already exists";
    header("Location: AdminDonorCreate.php");
    exit();
} elseif ($stmtEmail->num_rows > 0) {
    $_SESSION['error'] = "Email already exists";
    header("Location: AdminDonorCreate.php");
    exit();
}


    // GET role_id for donor
    $roleQuery = mysqli_query($conn, "SELECT role_id FROM roles WHERE role_name='donor'");
    $role = mysqli_fetch_assoc($roleQuery);
    $role_id = $role['role_id'];

    // INSERT ACCOUNT
    $stmt1 = $conn->prepare("
        INSERT INTO accounts (username, email, password_hash, role_id, status)
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmt1->bind_param("sssis", $username, $email, $password, $role_id, $status);
    $stmt1->execute();
    $account_id = $stmt1->insert_id;

    // INSERT DONOR
    $medical_document_name = null;
if (!empty($_FILES['medical_document']['name'])) {
    $target_dir_pdf = "../../../medical_docs/";
    $medical_document_name = time() . "_" . basename($_FILES["medical_document"]["name"]);

    // check if file is PDF
    $file_type = strtolower(pathinfo($medical_document_name, PATHINFO_EXTENSION));
    if ($file_type !== 'pdf') {
        $_SESSION['error'] = "Medical document must be a PDF file";
        header("Location: AdminDonorCreate.php");
        exit();
    }

    move_uploaded_file($_FILES["medical_document"]["tmp_name"], $target_dir_pdf . $medical_document_name);
}

// INSERT DONOR with medical_document field
$stmt2 = $conn->prepare("
    INSERT INTO donors_users 
        (account_id, first_name, last_name, profile_image, medical_history, medical_document, evaluation_status, height_cm, weight_kg, eye_color, hair_color, blood_type, ethnicity)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
");
$stmt2->bind_param(
    "issssssiiisss",
    $account_id,
    $first_name,
    $last_name,
    $image_name,
    $medical_history,
    $medical_document_name,  // new PDF field
    $evaluation_status,
    $height_cm,
    $weight_kg,
    $eye_color,
    $hair_color,
    $blood_type,
    $ethnicity
);
$stmt2->execute();

    $_SESSION['success'] = "Donor account created successfully";
    header("Location: AdminDonorCreate.php");
    exit();
}

// admin donor update
if ($_POST['action'] === 'AdminDonorUpdate') {

    $donor_id   = intval($_POST['donor_id']);
    $account_id = intval($_POST['account_id']);
    $redirect   = "AdminDonorUpdate.php?id=" . $donor_id;

    // 🔎 Fetch CURRENT database values
    $stmt = $conn->prepare("SELECT * FROM donors_users WHERE donor_id=?");
    $stmt->bind_param("i", $donor_id);
    $stmt->execute();
    $current_donor = $stmt->get_result()->fetch_assoc();

    $stmt = $conn->prepare("SELECT status FROM accounts WHERE account_id=?");
    $stmt->bind_param("i", $account_id);
    $stmt->execute();
    $current_account = $stmt->get_result()->fetch_assoc();

    // 📝 New submitted values
    $first_name        = trim($_POST['first_name']);
    $last_name         = trim($_POST['last_name']);
    $medical_history   = trim($_POST['medical_history']);
    $evaluation_status = $_POST['evaluation_status_select'] ?? '';
    $height_cm         = trim($_POST['height_cm']);
    $weight_kg         = trim($_POST['weight_kg']);
    $eye_color         = trim($_POST['eye_color']);
    $hair_color        = trim($_POST['hair_color']);
    $blood_type        = trim($_POST['blood_type']);
    $ethnicity         = trim($_POST['ethnicity']);
    $status            = trim($_POST['status']);

    $updated = false;

    // ================= ACCOUNT STATUS =================
    if (!empty($status) && $status !== $current_account['status']) {
        $stmt = $conn->prepare("UPDATE accounts SET status=? WHERE account_id=?");
        $stmt->bind_param("si", $status, $account_id);
        $stmt->execute();
        $updated = true;
    }

    // ================= DONOR FIELDS =================
    if (!empty($first_name) && $first_name !== $current_donor['first_name']) {
        $stmt = $conn->prepare("UPDATE donors_users SET first_name=? WHERE donor_id=?");
        $stmt->bind_param("si", $first_name, $donor_id);
        $stmt->execute();
        $updated = true;
    }

    if (!empty($last_name) && $last_name !== $current_donor['last_name']) {
        $stmt = $conn->prepare("UPDATE donors_users SET last_name=? WHERE donor_id=?");
        $stmt->bind_param("si", $last_name, $donor_id);
        $stmt->execute();
        $updated = true;
    }

    if (!empty($medical_history) && $medical_history !== $current_donor['medical_history']) {
        $stmt = $conn->prepare("UPDATE donors_users SET medical_history=? WHERE donor_id=?");
        $stmt->bind_param("si", $medical_history, $donor_id);
        $stmt->execute();
        $updated = true;
    }

    if (!empty($evaluation_status) && $evaluation_status !== $current_donor['evaluation_status']) {
        $stmt = $conn->prepare("UPDATE donors_users SET evaluation_status=? WHERE donor_id=?");
        $stmt->bind_param("si", $evaluation_status, $donor_id);
        $stmt->execute();
        $updated = true;
    }

    if (!empty($height_cm) && $height_cm != $current_donor['height_cm']) {
        $stmt = $conn->prepare("UPDATE donors_users SET height_cm=? WHERE donor_id=?");
        $stmt->bind_param("ii", $height_cm, $donor_id);
        $stmt->execute();
        $updated = true;
    }

    if (!empty($weight_kg) && $weight_kg != $current_donor['weight_kg']) {
        $stmt = $conn->prepare("UPDATE donors_users SET weight_kg=? WHERE donor_id=?");
        $stmt->bind_param("ii", $weight_kg, $donor_id);
        $stmt->execute();
        $updated = true;
    }

    if (!empty($eye_color) && $eye_color !== $current_donor['eye_color']) {
        $stmt = $conn->prepare("UPDATE donors_users SET eye_color=? WHERE donor_id=?");
        $stmt->bind_param("si", $eye_color, $donor_id);
        $stmt->execute();
        $updated = true;
    }

    if (!empty($hair_color) && $hair_color !== $current_donor['hair_color']) {
        $stmt = $conn->prepare("UPDATE donors_users SET hair_color=? WHERE donor_id=?");
        $stmt->bind_param("si", $hair_color, $donor_id);
        $stmt->execute();
        $updated = true;
    }

    if (!empty($blood_type) && $blood_type !== $current_donor['blood_type']) {
        $stmt = $conn->prepare("UPDATE donors_users SET blood_type=? WHERE donor_id=?");
        $stmt->bind_param("si", $blood_type, $donor_id);
        $stmt->execute();
        $updated = true;
    }

    if (!empty($ethnicity) && $ethnicity !== $current_donor['ethnicity']) {
        $stmt = $conn->prepare("UPDATE donors_users SET ethnicity=? WHERE donor_id=?");
        $stmt->bind_param("si", $ethnicity, $donor_id);
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

        // Optional: Check if file is an image
        $check = getimagesize($_FILES["profile_image"]["tmp_name"]);
        if ($check === false) {
            $_SESSION['error'] = "Uploaded file must be an image (jpg, png, gif).";
            header("Location: $redirect");
            exit();
        }

        if (move_uploaded_file($_FILES["profile_image"]["tmp_name"], $target_file)) {

            // Delete old image if exists and not default
            if (!empty($current_donor['profile_image']) && file_exists($target_dir . $current_donor['profile_image'])) {
                unlink($target_dir . $current_donor['profile_image']);
            }

            $stmt = $conn->prepare("UPDATE donors_users SET profile_image=? WHERE donor_id=?");
            $stmt->bind_param("si", $image_name, $donor_id);
            $stmt->execute();
            $updated = true;
        }
    }

    // ================= FINAL MESSAGE =================
    if ($updated) {
        $_SESSION['success'] = "Donor updated successfully!";
    } else {
        $_SESSION['error'] = "No update detected.";
    }

    header("Location: $redirect");
    exit();
}

