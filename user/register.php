<?php 
session_start(); 
include("../includes/config.php");
include("../includes/alert.php");

// Fetch roles for dropdown
$roles = $conn->query("SELECT role_id, role_name FROM roles");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account | Registration</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap');
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-green-50 flex items-center justify-center min-h-screen py-12">

    <div class="w-full max-w-md p-8 bg-white shadow-xl shadow-green-100/50 border border-green-100 rounded-2xl space-y-6">
        <div class="text-center">
            <h1 class="text-3xl font-bold text-green-900">Create account</h1>
            <p class="text-green-600 mt-2 font-medium">Join us by entering your details below.</p>
        </div>

        <div class="space-y-2">
            <?php
            if(isset($_GET['error'])){
                $msg = "";
                switch($_GET['error']){
                    case 'invalid_email': $msg = "Only Gmail addresses are allowed."; break;
                    case 'username_exists': $msg = "Username already taken."; break;
                    case 'email_exists': $msg = "Email already registered."; break;
                    case 'both_exist': $msg = "Both username and email are already taken."; break;
                    case 'system_error': $msg = "System error. Try again."; break;
                    case 'role_error': $msg = "Role selection error."; break;
                }
                if($msg) echo "<div class='p-3 text-sm text-red-700 bg-red-50 border border-red-100 rounded-lg text-center font-medium'>$msg</div>";
            }
            ?>
        </div>

        <form action="store.php" method="POST" class="space-y-4">
            <input type="hidden" name="action" value="register">

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-green-800 mb-1">First Name</label>
                    <input type="text" name="first_name" placeholder="John" required
                        class="w-full px-4 py-2.5 border border-green-200 rounded-lg focus:ring-2 focus:ring-green-500 outline-none transition bg-white">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-green-800 mb-1">Last Name</label>
                    <input type="text" name="last_name" placeholder="Doe" required
                        class="w-full px-4 py-2.5 border border-green-200 rounded-lg focus:ring-2 focus:ring-green-500 outline-none transition bg-white">
                </div>
            </div>

            <div>
                <label class="block text-sm font-semibold text-green-800 mb-1">Username</label>
                <input type="text" name="username" placeholder="johndoe123" required
                    class="w-full px-4 py-2.5 border border-green-200 rounded-lg focus:ring-2 focus:ring-green-500 outline-none transition bg-white">
            </div>

            <div>
                <label class="block text-sm font-semibold text-green-800 mb-1">Email</label>
                <input type="email" name="email" placeholder="name@gmail.com" required
                    class="w-full px-4 py-2.5 border border-green-200 rounded-lg focus:ring-2 focus:ring-green-500 outline-none transition bg-white">
            </div>

            <div>
                <label class="block text-sm font-semibold text-green-800 mb-1">Password</label>
                <input type="password" name="password" placeholder="••••••••" required
                    class="w-full px-4 py-2.5 border border-green-200 rounded-lg focus:ring-2 focus:ring-green-500 outline-none transition bg-white">
            </div>

            <div>
<label class="block text-sm font-semibold text-green-800 mb-1">Role</label>
<select name="role_id" required
    class="w-full px-4 py-2.5 border border-green-200 rounded-lg focus:ring-2 focus:ring-green-500 outline-none bg-white transition cursor-pointer text-gray-700">
    <option value="">-- Select Role --</option>
    <?php while($role = $roles->fetch_assoc()): ?>
        <?php if (in_array($role['role_name'], ['donor', 'recipient'])): ?>
            <option value="<?= $role['role_id']; ?>">
                <?= ucfirst($role['role_name']); ?>
            </option>
        <?php endif; ?>
    <?php endwhile; ?>
</select>

            <button type="submit" 
                class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-3 rounded-lg mt-2 transition duration-200 shadow-lg shadow-green-100">
                Register
            </button>
        </form>

        <p class="text-center text-sm text-gray-600">
            Already have an account? 
            <a href="login.php" class="font-bold text-green-700 hover:text-green-800 hover:underline">Log in</a>
        </p>
    </div>

</body>
</html>