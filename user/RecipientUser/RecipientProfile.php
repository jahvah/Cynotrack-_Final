<?php
session_start();
include("../../includes/config.php");
include("../../includes/header.php");
include("../../includes/alert.php");

if (!isset($_SESSION['account_id'])) {
    die("Invalid session. Please <a href='../login.php'>login</a>.");
}

$account_id = $_SESSION['account_id'];

// Fetch existing profile if any
$stmt = $conn->prepare("SELECT * FROM recipients_users WHERE account_id=?");
$stmt->bind_param("i", $account_id);
$stmt->execute();
$recipient = $stmt->get_result()->fetch_assoc();
?>

<style>
  @import url('https://fonts.googleapis.com/css2?family=Playfair+Display:wght@500;700&family=Nunito:wght@300;400;500;600;700;800&display=swap');

  .profile-wrap { font-family: 'Nunito', sans-serif; }
  .display-font { font-family: 'Playfair Display', serif; }

  .fade-up { opacity:0; transform:translateY(18px); animation: fu .5s ease forwards; }
  @keyframes fu { to { opacity:1; transform:translateY(0); } }
  .delay-1 { animation-delay:.08s; }
  .delay-2 { animation-delay:.16s; }
  .delay-3 { animation-delay:.24s; }
  .delay-4 { animation-delay:.32s; }

  .form-input {
    width: 100%;
    padding: .75rem 1rem;
    border: 1.5px solid #bbf7d0;
    border-radius: 12px;
    font-family: 'Nunito', sans-serif;
    font-size: .9rem;
    color: #1a2e1a;
    background: white;
    transition: border-color .2s, box-shadow .2s;
    outline: none;
  }
  .form-input:focus {
    border-color: #16a34a;
    box-shadow: 0 0 0 3px rgba(22,163,74,.12);
  }
  .form-input::placeholder { color: #9ca3af; }
  textarea.form-input { resize: vertical; min-height: 110px; }

  /* Drop zone */
  .img-drop-zone {
    border: 2px dashed #86efac;
    border-radius: 16px;
    background: #f0fdf4;
    cursor: pointer;
    transition: border-color .2s, background .2s;
  }
  .img-drop-zone:hover { border-color: #16a34a; background: #dcfce7; }

  .save-btn {
    background: linear-gradient(135deg, #16a34a, #15803d);
    color: white; font-weight: 800; font-size: .95rem;
    padding: 1rem 2rem; border-radius: 14px; border: none;
    cursor: pointer; width: 100%;
    box-shadow: 0 4px 16px rgba(21,128,61,.28);
    transition: all .2s ease;
    font-family: 'Nunito', sans-serif;
  }
  .save-btn:hover { transform: translateY(-1px); box-shadow: 0 6px 24px rgba(21,128,61,.38); }

  /* Step indicators */
  .step-dot {
    width: 8px; height: 8px; border-radius: 50%;
    background: rgba(255,255,255,.3);
  }
  .step-dot.active { background: white; }
</style>

<div class="profile-wrap min-h-screen flex items-center justify-center py-12 px-4"
     style="background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 40%, #fff 100%);">

  <div class="w-full max-w-lg">

    <!-- Card -->
    <div class="fade-up bg-white border border-green-100 rounded-2xl shadow-2xl shadow-green-100/30 overflow-hidden">

      <!-- Header -->
      <div class="bg-gradient-to-r from-green-900 to-emerald-700 px-8 py-7 relative overflow-hidden">
        <div class="absolute inset-0 opacity-10"
             style="background-image: radial-gradient(circle at 15% 50%, white 1px, transparent 1px),
                                      radial-gradient(circle at 80% 20%, white 1px, transparent 1px);
                    background-size: 38px 38px;"></div>
        <div class="relative">
          <div class="flex items-center gap-2 mb-4">
            <div class="step-dot active"></div>
            <div class="step-dot active"></div>
            <div class="step-dot"></div>
          </div>
          <p class="text-green-200 text-[10px] font-bold uppercase tracking-widest mb-1">Account Setup</p>
          <h2 class="display-font text-white text-2xl">Complete Your Profile</h2>
          <p class="text-green-200/80 text-sm mt-1 font-medium">Fill in your details to get started.</p>
        </div>
      </div>

      <!-- Form -->
      <form method="POST" action="RecipientStore.php" enctype="multipart/form-data"
            class="px-8 py-8 space-y-6">
        <input type="hidden" name="action" value="update_profile">

        <!-- Profile Image Upload -->
        <div class="fade-up delay-1">
          <label class="block text-[10px] font-bold uppercase tracking-wider text-green-800 mb-2">
            Profile Photo <span class="text-red-400">*</span>
          </label>

          <label class="img-drop-zone flex flex-col items-center justify-center gap-3 py-8" for="profileImgInput">
            <div id="previewWrap" class="hidden">
              <img id="previewImg" class="w-20 h-20 rounded-full object-cover border-2 border-green-300 shadow" src="" alt="">
            </div>
            <div id="uploadIcon">
              <svg class="w-10 h-10 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                      d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
              </svg>
            </div>
            <div class="text-center">
              <p class="text-sm font-bold text-green-700">Click to upload photo</p>
              <p class="text-[10px] text-green-500 mt-0.5">JPG, PNG, GIF — max 2MB</p>
            </div>
            <input type="file" name="profile_image" id="profileImgInput"
                   accept="image/*" required class="hidden"
                   onchange="previewImage(this)">
          </label>
        </div>

        <!-- Preferences -->
        <div class="fade-up delay-2">
          <label class="block text-[10px] font-bold uppercase tracking-wider text-green-800 mb-2">
            Your Preferences <span class="text-red-400">*</span>
          </label>
          <textarea name="preferences" class="form-input" required
                    placeholder="Describe what donor traits you're looking for (e.g. height, ethnicity, health background)…"><?= htmlspecialchars($recipient['preferences'] ?? ''); ?></textarea>
          <p class="text-[10px] text-gray-400 mt-1 font-medium">This helps us match you with the right donors.</p>
        </div>

        <!-- Submit -->
        <div class="fade-up delay-3 pt-2">
          <button type="submit" class="save-btn">Complete Profile Setup →</button>
        </div>

      </form>
    </div>

    <!-- Footer note -->
    <p class="fade-up delay-4 text-center text-xs text-green-600 font-medium mt-4">
      Your profile will be reviewed by our team before activation.
    </p>
  </div>
</div>

<script>
  function previewImage(input) {
    if (input.files && input.files[0]) {
      const reader = new FileReader();
      reader.onload = e => {
        document.getElementById('previewImg').src = e.target.result;
        document.getElementById('previewWrap').classList.remove('hidden');
        document.getElementById('uploadIcon').classList.add('hidden');
      };
      reader.readAsDataURL(input.files[0]);
    }
  }
</script>

<?php include("../../includes/footer.php"); ?>
