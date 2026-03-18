<?php
session_start(); // must be first line
include("../../includes/config.php");
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Redirect if no action
if (!isset($_POST['action'])) {
    header("Location: ../login.php");
    exit();
}

$action = $_POST['action'];

//update profile
if ($action === 'update_profile') {

    if (!isset($_SESSION['account_id'])) {
        header("Location: ../login.php");
        exit();
    }

    $account_id = $_SESSION['account_id'];

    // Fetch current profile
    $stmt = $conn->prepare("SELECT * FROM self_storage_users WHERE account_id=?");
    $stmt->bind_param("i", $account_id);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();

    if (!$user) {
        header("Location: SelfStorageEditProfile.php?error=Profile not found");
        exit();
    }

    $fields = [];
    $types  = "";
    $values = [];

    function addField(&$fields, &$types, &$values, $name, $value, $type) {
        if ($value !== "" && $value !== null) {
            $fields[] = "$name=?";
            $types .= $type;
            $values[] = $value;
        }
    }

    // Fields
    addField($fields, $types, $values, "first_name", trim($_POST['first_name']), "s");
    addField($fields, $types, $values, "last_name", trim($_POST['last_name']), "s");
    addField($fields, $types, $values, "storage_details", trim($_POST['storage_details']), "s");

    // Handle profile image
    if (!empty($_FILES['profile_image']['name'])) {
        $upload_dir = "../../uploads/";
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

        $ext = strtolower(pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg','jpeg','png','gif'];

        if (!in_array($ext, $allowed)) {
            header("Location: SelfStorageEditProfile.php?error=Invalid image type");
            exit();
        }

        if ($_FILES['profile_image']['size'] > 2 * 1024 * 1024) {
            header("Location: SelfStorageEditProfile.php?error=Image too large");
            exit();
        }

        $file_name = uniqid("storage_", true).".".$ext;
        if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $upload_dir.$file_name)) {
            addField($fields, $types, $values, "profile_image", $file_name, "s");
        }
    }

    if (count($fields) === 0) {
        header("Location: SelfStorageEditProfile.php?error=No changes detected");
        exit();
    }

    // Prepare update
    $sql = "UPDATE self_storage_users SET ".implode(", ", $fields)." WHERE storage_user_id=?";
    $types .= "i";
    $values[] = $user['storage_user_id'];

    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$values);

    if ($stmt->execute()) {

        // Detect first-time profile completion
        $first_time = empty($user['profile_image']) && empty($user['storage_details']);

        if ($first_time) {
            // First-time completion: log out and redirect to login
            $_SESSION['flash_message'] = "Your account is pending approval. Please login after admin approval.";

            unset($_SESSION['account_id']);
            unset($_SESSION['role']);
            unset($_SESSION['role_user_id']);

            header("Location: ../login.php");
            exit();
        } else {
            // Normal update: stay on edit page with success
            $_SESSION['flash_message'] = "Profile updated successfully!";
            header("Location: SelfStorageEditProfile.php");
            exit();
        }

    } else {
        header("Location: SelfStorageEditProfile.php?error=Update failed");
        exit();
    }
}

?>
