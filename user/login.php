<?php 
session_start(); 
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Welcome Back</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap');
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-green-50 flex items-center justify-center min-h-screen">

    <div class="w-full max-w-md p-8 bg-white shadow-xl shadow-green-100/50 rounded-2xl space-y-6">
        <div class="text-center">
            <h1 class="text-4xl font-bold text-green-900">Welcome back</h1>
            <p class="text-green-600 mt-2 font-medium">Please enter your details to continue.</p>
        </div>

        <div class="space-y-2">
            <?php
            if (isset($_SESSION['flash_message'])) {
                echo "<div class='p-3 text-sm text-blue-700 bg-blue-50 border border-blue-100 rounded-lg'>" . htmlspecialchars($_SESSION['flash_message']) . "</div>";
                unset($_SESSION['flash_message']);
            }

            if(isset($_GET['error'])){
                $error_msg = "";
                switch($_GET['error']){
                    case 'invalid_credentials': $error_msg = "Invalid email or password"; break;
                    case 'inactive': $error_msg = "Account inactive. Contact admin."; break;
                    case 'pending': $error_msg = "Account pending approval."; break;
                    case 'role_not_found': $error_msg = "Role error. Contact admin."; break;
                }
                if($error_msg) echo "<div class='p-3 text-sm text-red-700 bg-red-50 border border-red-100 rounded-lg text-center font-medium'>$error_msg</div>";
            }
            ?>
        </div>

        <form method="POST" action="store.php" class="space-y-5">
            <input type="hidden" name="action" value="login">

            <div>
                <label class="block text-sm font-semibold text-green-800 mb-1">Email</label>
                <input type="email" name="email" placeholder="Enter your email" required
                    class="w-full px-4 py-3 border border-green-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent outline-none transition bg-white">
            </div>

            <div>
                <label class="block text-sm font-semibold text-green-800 mb-1">Password</label>
                <input type="password" name="password" placeholder="Password" required
                    class="w-full px-4 py-3 border border-green-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent outline-none transition bg-white">
            </div>

            <button type="submit" 
                class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-3 rounded-lg transition duration-200 shadow-lg shadow-green-200">
                Login
            </button>
        </form>

        <p class="text-center text-sm text-gray-600">
            Don't have an account? 
            <a href="register.php" class="font-bold text-green-700 hover:text-green-800 hover:underline">Sign up for free</a>
        </p>
    </div>

</body>
</html>