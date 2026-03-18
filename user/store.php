<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

include("../includes/config.php");

/* 🚫 Block direct access */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: login.php");
    exit;
}

/* =======================================
   ACCOUNT REGISTRATION FUNCTION
======================================= */
function registerUser($conn, $username, $email, $password, $role_id, $first_name, $last_name) {

    /* 📧 CHECK IF EMAIL IS GMAIL */
    if (!preg_match("/^[a-zA-Z0-9._%+-]+@gmail\.com$/", $email)) {
        header("Location: register.php?error=invalid_email");
        exit;
    }


/* 🔍 CHECK FOR EXISTING USERNAME OR EMAIL */
$stmt = $conn->prepare("SELECT username, email FROM accounts WHERE username = ? OR email = ?");
$stmt->bind_param("ss", $username, $email);
$stmt->execute();
$result = $stmt->get_result();

$usernameExists = false;
$emailExists = false;

while ($row = $result->fetch_assoc()) {
    if ($row['username'] === $username) {
        $usernameExists = true;
    }
    if ($row['email'] === $email) {
        $emailExists = true;
    }
}

if ($usernameExists && $emailExists) {
    header("Location: register.php?error=both_exist");
    exit;
} elseif ($usernameExists) {
    header("Location: register.php?error=username_exists");
    exit;
} elseif ($emailExists) {
    header("Location: register.php?error=email_exists");
    exit;
}

    /* 🔐 HASH PASSWORD */
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    /* 🧾 INSERT INTO ACCOUNTS */
    $stmt = $conn->prepare("INSERT INTO accounts (username, email, password_hash, role_id) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("sssi", $username, $email, $hashed_password, $role_id);

    if (!$stmt->execute()) {
        header("Location: register.php?error=system_error");
        exit;
    }

    $account_id = $stmt->insert_id;
    $_SESSION['account_id'] = $account_id;

    /* 🎭 GET ROLE NAME */
    $stmt = $conn->prepare("SELECT role_name FROM roles WHERE role_id = ?");
    $stmt->bind_param("i", $role_id);
    $stmt->execute();
    $role = strtolower($stmt->get_result()->fetch_assoc()['role_name']);
    $_SESSION['role'] = $role;

    /* 👤 INSERT INTO ROLE TABLE */
    switch ($role) {
        case 'donor':
            $stmt = $conn->prepare("INSERT INTO donors_users (account_id, first_name, last_name) VALUES (?, ?, ?)");
            $stmt->bind_param("iss", $account_id, $first_name, $last_name);
            $stmt->execute();
            $_SESSION['role_user_id'] = $conn->insert_id;
            header("Location: DonorUser/DonorProfile.php");
            exit;

        case 'recipient':
            $stmt = $conn->prepare("INSERT INTO recipients_users (account_id, first_name, last_name) VALUES (?, ?, ?)");
            $stmt->bind_param("iss", $account_id, $first_name, $last_name);
            $stmt->execute();
            $_SESSION['role_user_id'] = $conn->insert_id;
            header("Location: RecipientUser/RecipientProfile.php");
            exit;

        case 'self-storage':
            $stmt = $conn->prepare("INSERT INTO self_storage_users (account_id, first_name, last_name) VALUES (?, ?, ?)");
            $stmt->bind_param("iss", $account_id, $first_name, $last_name);
            $stmt->execute();
            $_SESSION['role_user_id'] = $conn->insert_id;
            header("Location: SelfStorageUser/SelfStorageProfile.php");
            exit;

        default:
            header("Location: register.php?error=role_error");
            exit;
    }
}

/* =======================================
   LOGIN FUNCTION
======================================= */
function loginUser($conn, $email, $password) {

    $stmt = $conn->prepare("SELECT * FROM accounts WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();

    if (!$user || !password_verify($password, $user['password_hash'])) {
        header("Location: login.php?error=invalid_credentials");
        exit;
    }

    if ($user['status'] === 'inactive') {
        header("Location: login.php?error=inactive");
        exit;
    }

    if ($user['status'] === 'pending') {
        header("Location: login.php?error=pending");
        exit;
    }

    $_SESSION['account_id'] = $user['account_id'];
    $_SESSION['role_id']    = $user['role_id'];
    $_SESSION['email']      = $user['email'];

    $stmt = $conn->prepare("SELECT role_name FROM roles WHERE role_id = ?");
    $stmt->bind_param("i", $user['role_id']);
    $stmt->execute();
    $role = strtolower($stmt->get_result()->fetch_assoc()['role_name']);
    $_SESSION['role'] = $role;

    switch ($role) {
        case 'donor':
            $stmt = $conn->prepare("SELECT donor_id FROM donors_users WHERE account_id = ?");
            $stmt->bind_param("i", $user['account_id']);
            $stmt->execute();
            $_SESSION['role_user_id'] = $stmt->get_result()->fetch_assoc()['donor_id'];
            header("Location: DonorUser/DonorDashboard.php");
            exit;

        case 'recipient':
            $stmt = $conn->prepare("SELECT recipient_id FROM recipients_users WHERE account_id = ?");
            $stmt->bind_param("i", $user['account_id']);
            $stmt->execute();
            $_SESSION['role_user_id'] = $stmt->get_result()->fetch_assoc()['recipient_id'];
            header("Location: RecipientUser/RecipientDashboard.php");
            exit;

        case 'self-storage':
            $stmt = $conn->prepare("SELECT storage_user_id FROM self_storage_users WHERE account_id = ?");
            $stmt->bind_param("i", $user['account_id']);
            $stmt->execute();
            $_SESSION['role_user_id'] = $stmt->get_result()->fetch_assoc()['storage_user_id'];
            header("Location: SelfStorageUser/SelfStorageDashboard.php");
            exit;

        case 'admin':
            $_SESSION['role_user_id'] = $user['account_id'];
            header("Location: AdminUser/AdminDashboard.php");
            exit;

        case 'staff':
            $_SESSION['role_user_id'] = $user['account_id'];
            header("Location: StaffUser/StaffDashboard.php");
            exit;

        default:
            header("Location: login.php?error=role_not_found");
            exit;
    }
}

/* =======================================
   HANDLE POST ACTIONS
======================================= */
if (isset($_POST['action'])) {

    if ($_POST['action'] === 'register') {
        registerUser(
            $conn,
            trim($_POST['username']),
            trim($_POST['email']),
            $_POST['password'],
            $_POST['role_id'],
            trim($_POST['first_name']),
            trim($_POST['last_name'])
        );
    }

    if ($_POST['action'] === 'login') {
        loginUser(
            $conn,
            trim($_POST['email']),
            $_POST['password']
        );
    }
}
?>