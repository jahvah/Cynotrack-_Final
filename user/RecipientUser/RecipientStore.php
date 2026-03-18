<?php
session_start();
include("../../includes/config.php");

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Redirect if no action provided
if (!isset($_POST['action'])) {
    header("Location: ../login.php");
    exit();
}

$action = $_POST['action'];

// =============================================
// UPDATE PROFILE
// =============================================
if ($action === 'update_profile') {

    if (!isset($_SESSION['account_id'])) {
        header("Location: ../login.php");
        exit();
    }

    $account_id = $_SESSION['account_id'];

    // Fetch current recipient data
    $stmt = $conn->prepare("SELECT * FROM recipients_users WHERE account_id=?");
    $stmt->bind_param("i", $account_id);
    $stmt->execute();
    $recipient = $stmt->get_result()->fetch_assoc();

    if (!$recipient) {
        header("Location: RecipientEditProfile.php?error=" . urlencode("Recipient profile not found."));
        exit();
    }

    $fields = [];
    $types  = "";
    $values = [];

    function addField(&$fields, &$types, &$values, $name, $value, $type) {
        if ($value !== "" && $value !== null) {
            $fields[] = "$name=?";
            $types   .= $type;
            $values[] = $value;
        }
    }

    // Text fields — only update if non-empty
    $first_name  = trim($_POST['first_name']  ?? '');
    $last_name   = trim($_POST['last_name']   ?? '');
    $preferences = trim($_POST['preferences'] ?? '');

    addField($fields, $types, $values, "first_name",  $first_name,  "s");
    addField($fields, $types, $values, "last_name",   $last_name,   "s");
    addField($fields, $types, $values, "preferences", $preferences, "s");

    // Profile image upload
    if (!empty($_FILES['profile_image']['name'])) {

        $upload_dir = "../../uploads/";
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

        $ext     = strtolower(pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];

        if (!in_array($ext, $allowed)) {
            header("Location: RecipientEditProfile.php?error=" . urlencode("Invalid image type. Use JPG, PNG, or GIF."));
            exit();
        }

        if ($_FILES['profile_image']['size'] > 2 * 1024 * 1024) {
            header("Location: RecipientEditProfile.php?error=" . urlencode("Image is too large. Maximum size is 2MB."));
            exit();
        }

        // Delete old image if it exists and isn't the default
        if (!empty($recipient['profile_image']) && $recipient['profile_image'] !== 'default.png') {
            $old_path = $upload_dir . $recipient['profile_image'];
            if (file_exists($old_path)) unlink($old_path);
        }

        $file_name = uniqid("recipient_", true) . "." . $ext;

        if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $upload_dir . $file_name)) {
            addField($fields, $types, $values, "profile_image", $file_name, "s");
        } else {
            header("Location: RecipientEditProfile.php?error=" . urlencode("Failed to upload image. Please try again."));
            exit();
        }
    }

    // Nothing to update
    if (count($fields) === 0) {
        header("Location: RecipientEditProfile.php?error=" . urlencode("No changes detected."));
        exit();
    }

    // Build and execute dynamic UPDATE
    $sql     = "UPDATE recipients_users SET " . implode(", ", $fields) . " WHERE recipient_id=?";
    $types  .= "i";
    $values[] = $recipient['recipient_id'];

    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$values);

    if ($stmt->execute()) {

        // First-time profile completion — profile_image was empty before
        $is_first_time = empty($recipient['profile_image']);

        if ($is_first_time) {
            $_SESSION['flash_message'] = "Profile submitted! Your account is pending admin approval. You will be able to log in once approved.";
            unset($_SESSION['account_id'], $_SESSION['role'], $_SESSION['role_user_id']);
            header("Location: ../login.php");
            exit();
        } else {
            $_SESSION['flash_message'] = "Profile updated successfully!";
            header("Location: RecipientEditProfile.php");
            exit();
        }

    } else {
        header("Location: RecipientEditProfile.php?error=" . urlencode("Update failed. Please try again."));
        exit();
    }
}

// Fallback redirect for unknown actions
header("Location: ../login.php");
exit();
?>
