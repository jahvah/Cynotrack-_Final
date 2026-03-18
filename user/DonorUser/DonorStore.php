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

    // Get donor data
    $stmt = $conn->prepare("SELECT * FROM donors_users WHERE account_id=?");
    $stmt->bind_param("i", $account_id);
    $stmt->execute();
    $donor = $stmt->get_result()->fetch_assoc();

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

    addField($fields,$types,$values,"first_name", trim($_POST['first_name']), "s");
    addField($fields,$types,$values,"last_name", trim($_POST['last_name']), "s");
    addField($fields,$types,$values,"eye_color", trim($_POST['eye_color']), "s");
    addField($fields,$types,$values,"hair_color", trim($_POST['hair_color']), "s");
    addField($fields,$types,$values,"blood_type", trim($_POST['blood_type']), "s");
    addField($fields,$types,$values,"ethnicity", trim($_POST['ethnicity']), "s");

    if ($_POST['height_cm'] !== "") addField($fields,$types,$values,"height_cm",(int)$_POST['height_cm'],"i");
    if ($_POST['weight_kg'] !== "") addField($fields,$types,$values,"weight_kg",(int)$_POST['weight_kg'],"i");

    // Handle profile image
    if (!empty($_FILES['profile_image']['name'])) {
        $upload_dir = "../../uploads/";
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

        $ext = strtolower(pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg','jpeg','png','gif'];

        if (!in_array($ext, $allowed)) {
            header("Location: DonorEditProfile.php?error=Invalid image type");
            exit();
        }

        if ($_FILES['profile_image']['size'] > 2 * 1024 * 1024) {
            header("Location: DonorEditProfile.php?error=Image too large");
            exit();
        }

        $file_name = uniqid("donor_", true).".".$ext;
        if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $upload_dir.$file_name)) {
            addField($fields,$types,$values,"profile_image",$file_name,"s");
        }
    }

    // Handle medical document upload (PDF only)
    if (!empty($_FILES['medical_document']['name'])) {

    $upload_dir = "../../medical_docs/";
    if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

    $ext = strtolower(pathinfo($_FILES['medical_document']['name'], PATHINFO_EXTENSION));

    if ($ext !== 'pdf') {
        header("Location: DonorEditProfile.php?error=Medical document must be PDF");
        exit();
    }

    if ($_FILES['medical_document']['size'] > 5 * 1024 * 1024) {
        header("Location: DonorEditProfile.php?error=PDF too large (Max 5MB)");
        exit();
    }

    $file_name = uniqid("medical_", true) . ".pdf";

    if (move_uploaded_file($_FILES['medical_document']['tmp_name'], $upload_dir . $file_name)) {
        addField($fields, $types, $values, "medical_document", $file_name, "s");
    }
}


    if (count($fields) === 0) {
        header("Location: DonorEditProfile.php?error=No changes detected");
        exit();
    }

    $sql = "UPDATE donors_users SET ".implode(", ", $fields)." WHERE donor_id=?";
    $types .= "i";
    $values[] = $donor['donor_id'];

    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$values);

    if ($stmt->execute()) {

        // Detect first-time profile completion
        $first_time = empty($donor['height_cm']) && empty($donor['weight_kg']) && empty($donor['profile_image']);

        if ($first_time) {
            // First-time completion: log out and redirect to login with message
            $_SESSION['flash_message'] = "Your account is pending approval. Please login after admin approval.";

            unset($_SESSION['account_id']);
            unset($_SESSION['role']);

            header("Location: ../login.php");
            exit();
        } else {
            // Normal update: stay on DonorEditProfile.php and show success message
            $_SESSION['flash_message'] = "Profile updated successfully!";
            header("Location: DonorEditProfile.php");
            exit();
        }

    } else {
        header("Location: DonorEditProfile.php?error=Update failed");
        exit();
    }
}
