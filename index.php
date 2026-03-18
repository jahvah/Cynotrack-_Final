<?php
// 1. Load config for ROOT_URL
include('includes/config.php'); 
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CynoTrack | Efficient Resource Management</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { 
            font-family: 'Plus Jakarta Sans', sans-serif; 
            scroll-behavior: smooth; 
            position: relative;
            background-color: #f8fafc; /* Subtle base off-white */
        }

        /* The Ghost Background Image */
        body::before {
            content: "";
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: url('includes/index_bg.jpeg');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            opacity: 0.06; 
            z-index: -1;
            pointer-events: none;
        }

        .glass-panel {
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
        }
    </style>
</head>
<body class="text-slate-900">

    <nav class="fixed w-full z-50 bg-white/70 border-b border-emerald-100 glass-panel">
        <div class="max-w-7xl mx-auto px-4 h-20 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <div class="w-10 h-10 bg-emerald-600 rounded-xl flex items-center justify-center text-white font-bold text-xl shadow-lg shadow-emerald-200">C</div>
                <span class="text-2xl font-black text-emerald-950 tracking-tight">CynoTrack</span>
            </div>

            <div class="flex items-center gap-3">
                <a href="user/register.php" class="hidden sm:block text-emerald-700 hover:text-emerald-800 px-5 py-2.5 rounded-full font-bold transition border border-emerald-200 hover:bg-emerald-50">
                    Create Account
                </a>
                <a href="user/login.php" class="bg-emerald-600 hover:bg-emerald-700 text-white px-6 py-2.5 rounded-full font-bold transition transform hover:scale-105 active:scale-95 shadow-md shadow-emerald-200">
                    Sign In
                </a>
            </div>
        </div>
    </nav>

    <header class="pt-40 pb-20 px-4">
        <div class="max-w-5xl mx-auto text-center">
            <span class="inline-block px-4 py-1.5 mb-6 text-sm font-bold text-emerald-700 bg-emerald-100/80 rounded-full uppercase tracking-widest">Bridging Resources & Needs</span>
            <h1 class="text-5xl md:text-7xl font-black text-emerald-950 mb-6 leading-tight">
                Empowering Communities Through <span class="text-emerald-600">Smart Tracking.</span>
            </h1>
            <p class="text-lg text-emerald-800/70 mb-10 max-w-2xl mx-auto leading-relaxed font-medium">
                CynoTrack is a streamlined platform connecting donors, staff, and recipients. We simplify the logistics of giving, storing, and receiving to ensure help gets where it's needed most.
            </p>
            <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                <a href="user/register.php" class="w-full sm:w-auto px-10 py-4 bg-emerald-900 text-white font-bold rounded-2xl hover:bg-emerald-800 transition shadow-xl">
                    Get Started Now
                </a>
                <a href="#how-it-works" class="w-full sm:w-auto px-10 py-4 bg-white/60 border border-emerald-200 text-emerald-800 font-bold rounded-2xl hover:bg-emerald-50 transition glass-panel">
                    View Workflow
                </a>
            </div>
        </div>
    </header>

    <section id="how-it-works" class="py-24 bg-emerald-50/40 glass-panel border-y border-emerald-100/50">
        <div class="max-w-7xl mx-auto px-4">
            <div class="text-center mb-16">
                <h2 class="text-3xl font-black text-emerald-900">How CynoTrack Works</h2>
                <p class="text-emerald-700/70 mt-2 font-medium">A simple 4-step process to ensure efficiency.</p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 relative">
                <div class="text-center p-8 bg-white/50 rounded-3xl border border-emerald-100 shadow-sm glass-panel hover:bg-white/80 transition-all">
                    <div class="w-12 h-12 bg-emerald-600 shadow-lg shadow-emerald-200 rounded-full flex items-center justify-center mx-auto mb-4 text-white font-bold">1</div>
                    <h4 class="font-bold mb-2 text-emerald-900">Registration</h4>
                    <p class="text-sm text-emerald-800/60 font-medium">Users sign up and choose their role in the ecosystem.</p>
                </div>
                <div class="text-center p-8 bg-white/50 rounded-3xl border border-emerald-100 shadow-sm glass-panel hover:bg-white/80 transition-all">
                    <div class="w-12 h-12 bg-emerald-600 shadow-lg shadow-emerald-200 rounded-full flex items-center justify-center mx-auto mb-4 text-white font-bold">2</div>
                    <h4 class="font-bold mb-2 text-emerald-900">Verification</h4>
                    <p class="text-sm text-emerald-800/60 font-medium">Admins review and approve accounts to ensure security.</p>
                </div>
                <div class="text-center p-8 bg-white/50 rounded-3xl border border-emerald-100 shadow-sm glass-panel hover:bg-white/80 transition-all">
                    <div class="w-12 h-12 bg-emerald-600 shadow-lg shadow-emerald-200 rounded-full flex items-center justify-center mx-auto mb-4 text-white font-bold">3</div>
                    <h4 class="font-bold mb-2 text-emerald-900">Allocation</h4>
                    <p class="text-sm text-emerald-800/60 font-medium">Resources are listed, stored, and matched to specific needs.</p>
                </div>
                <div class="text-center p-8 bg-white/50 rounded-3xl border border-emerald-100 shadow-sm glass-panel hover:bg-white/80 transition-all">
                    <div class="w-12 h-12 bg-emerald-600 shadow-lg shadow-emerald-200 rounded-full flex items-center justify-center mx-auto mb-4 text-white font-bold">4</div>
                    <h4 class="font-bold mb-2 text-emerald-900">Distribution</h4>
                    <p class="text-sm text-emerald-800/60 font-medium">Real-time tracking ensures items reach recipients safely.</p>
                </div>
            </div>
        </div>
    </section>

    <section id="about" class="py-24 bg-emerald-100/20 glass-panel">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex flex-col md:flex-row items-end justify-between mb-12 gap-4">
                <div class="max-w-xl">
                    <h2 class="text-4xl font-black text-emerald-950 leading-tight">Tailored Experience for Every User.</h2>
                </div>
                <p class="text-emerald-700/70 font-medium max-w-sm">Choose the path that fits your contribution to the community.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="group p-8 rounded-3xl bg-white/70 border border-emerald-50 shadow-sm hover:shadow-xl hover:border-emerald-300 transition-all duration-300 glass-panel">
                    <div class="w-14 h-14 bg-emerald-100 rounded-2xl flex items-center justify-center text-2xl mb-6 group-hover:scale-110 transition-transform">🤝</div>
                    <h3 class="text-xl font-bold text-emerald-900 mb-3">Donors</h3>
                    <ul class="text-emerald-800/60 text-sm space-y-3 font-medium">
                        <li class="flex items-center gap-2"> <span class="text-emerald-500 font-black">✓</span> List available resources</li>
                        <li class="flex items-center gap-2"> <span class="text-emerald-500 font-black">✓</span> Track donation history</li>
                        <li class="flex items-center gap-2"> <span class="text-emerald-500 font-black">✓</span> Receive impact reports</li>
                    </ul>
                </div>
                <div class="group p-8 rounded-3xl bg-white/70 border border-emerald-50 shadow-sm hover:shadow-xl hover:border-emerald-300 transition-all duration-300 glass-panel">
                    <div class="w-14 h-14 bg-emerald-50 rounded-2xl flex items-center justify-center text-2xl mb-6 group-hover:scale-110 transition-transform">📦</div>
                    <h3 class="text-xl font-bold text-emerald-900 mb-3">Self-Storage</h3>
                    <ul class="text-emerald-800/60 text-sm space-y-3 font-medium">
                        <li class="flex items-center gap-2"> <span class="text-emerald-500 font-black">✓</span> Inventory management</li>
                        <li class="flex items-center gap-2"> <span class="text-emerald-500 font-black">✓</span> Space allocation tracking</li>
                        <li class="flex items-center gap-2"> <span class="text-emerald-500 font-black">✓</span> Secure intake logs</li>
                    </ul>
                </div>
                <div class="group p-8 rounded-3xl bg-white/70 border border-emerald-50 shadow-sm hover:shadow-xl hover:border-emerald-300 transition-all duration-300 glass-panel">
                    <div class="w-14 h-14 bg-teal-50 rounded-2xl flex items-center justify-center text-2xl mb-6 group-hover:scale-110 transition-transform">🏘️</div>
                    <h3 class="text-xl font-bold text-emerald-900 mb-3">Recipients</h3>
                    <ul class="text-emerald-800/60 text-sm space-y-3 font-medium">
                        <li class="flex items-center gap-2"> <span class="text-emerald-500 font-black">✓</span> Request specific aid</li>
                        <li class="flex items-center gap-2"> <span class="text-emerald-500 font-black">✓</span> Real-time status updates</li>
                        <li class="flex items-center gap-2"> <span class="text-emerald-500 font-black">✓</span> Simplified pickup process</li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <section class="py-20 border-y border-emerald-100 bg-emerald-50/60 glass-panel">
        <div class="max-w-7xl mx-auto px-4">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-8">
                <div class="text-center">
                    <p class="text-4xl font-black text-emerald-600 mb-1">100%</p>
                    <p class="text-xs uppercase font-bold text-emerald-800/40 tracking-widest">Transparency</p>
                </div>
                <div class="text-center">
                    <p class="text-4xl font-black text-emerald-600 mb-1">24/7</p>
                    <p class="text-xs uppercase font-bold text-emerald-800/40 tracking-widest">Live Monitoring</p>
                </div>
                <div class="text-center">
                    <p class="text-4xl font-black text-emerald-600 mb-1">Fast</p>
                    <p class="text-xs uppercase font-bold text-emerald-800/40 tracking-widest">Verification</p>
                </div>
                <div class="text-center">
                    <p class="text-4xl font-black text-emerald-600 mb-1">Secure</p>
                    <p class="text-xs uppercase font-bold text-emerald-800/40 tracking-widest">Data Encryption</p>
                </div>
            </div>
        </div>
    </section>

    <section class="py-24 bg-emerald-950 relative overflow-hidden">
        <div class="absolute top-0 left-0 w-full h-full opacity-10">
            <div class="absolute -top-24 -left-24 w-96 h-96 bg-emerald-400 rounded-full blur-3xl"></div>
            <div class="absolute -bottom-24 -right-24 w-96 h-96 bg-emerald-500 rounded-full blur-3xl"></div>
        </div>
        
        <div class="max-w-4xl mx-auto px-4 text-center relative z-10">
            <h2 class="text-4xl font-bold text-white mb-6">Ready to make a difference?</h2>
            <p class="text-emerald-100/80 text-lg mb-10 font-medium">
                Join our growing network of donors and volunteers. Create your account today and start your journey with CynoTrack.
            </p>
            <div class="flex flex-col sm:flex-row justify-center gap-4">
                <a href="user/register.php" class="px-12 py-4 bg-emerald-500 hover:bg-emerald-400 text-emerald-950 font-black rounded-2xl transition shadow-lg">
                    CREATE ACCOUNT
                </a>
                <a href="user/login.php" class="px-12 py-4 bg-white/10 hover:bg-white/20 text-white font-black rounded-2xl border border-white/20 transition">
                    SIGN IN
                </a>
            </div>
        </div>
    </section>

    <footer class="py-10 border-t border-emerald-100 bg-white/60 glass-panel">
        <div class="max-w-7xl mx-auto px-4 flex flex-col md:flex-row justify-between items-center gap-6">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 bg-emerald-600 rounded-lg flex items-center justify-center text-white font-bold text-sm">C</div>
                <span class="text-lg font-black text-emerald-900 tracking-tight">CynoTrack</span>
            </div>
            <p class="text-emerald-800/40 text-sm font-medium">
                &copy; <?= date('Y'); ?> CynoTrack Management System.
            </p>
            <div class="flex gap-6 text-sm font-bold text-emerald-700">
                <a href="user/login.php" class="hover:text-emerald-950">Admin Login</a>
                <a href="#" class="hover:text-emerald-950">Privacy Policy</a>
            </div>
        </div>
    </footer>

</body>
</html>