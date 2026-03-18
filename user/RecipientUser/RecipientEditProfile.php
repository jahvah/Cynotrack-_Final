<?php
session_start();
include("../../includes/header.php");
include("../../includes/config.php");

if (isset($_SESSION['flash_message'])) {
    $flash = $_SESSION['flash_message'];
    unset($_SESSION['flash_message']);
}

if (!isset($_SESSION['account_id'])) {
    header("Location: ../login.php");
    exit();
}

$account_id = $_SESSION['account_id'];

$stmt = $conn->prepare("
    SELECT recipient_id, first_name, last_name, profile_image, preferences
    FROM recipients_users WHERE account_id=?
");
$stmt->bind_param("i", $account_id);
$stmt->execute();
$recipient = $stmt->get_result()->fetch_assoc();

if (!$recipient) {
    die("<div class='text-center p-10 text-red-600 font-bold'>Profile not found. Please contact support.</div>");
}
?>

<style>
  @import url('https://fonts.googleapis.com/css2?family=Playfair+Display:wght@500;700&family=Nunito:wght@300;400;500;600;700;800&display=swap');

  .edit-wrap { font-family: 'Nunito', sans-serif; }
  .display-font { font-family: 'Playfair Display', serif; }

  .fade-up { opacity:0; transform:translateY(18px); animation: fu .5s ease forwards; }
  @keyframes fu { to { opacity:1; transform:translateY(0); } }
  .delay-1 { animation-delay:.07s; }
  .delay-2 { animation-delay:.14s; }
  .delay-3 { animation-delay:.21s; }
  .delay-4 { animation-delay:.28s; }

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

  textarea.form-input { resize: vertical; min-height: 100px; }

  /* Avatar upload zone */
  .avatar-upload { cursor: pointer; }
  .avatar-upload:hover .avatar-overlay { opacity: 1; }
  .avatar-overlay {
    position: absolute; inset: 0; border-radius: 50%;
    background: rgba(21,128,61,.65);
    display: flex; align-items: center; justify-content: center;
    opacity: 0; transition: opacity .2s;
  }

  .save-btn {
    background: linear-gradient(135deg, #16a34a, #15803d);
    color: white; font-weight: 800; font-size: .9rem;
    padding: .9rem 2rem; border-radius: 14px; border: none;
    cursor: pointer; width: 100%;
    box-shadow: 0 4px 16px rgba(21,128,61,.28);
    transition: all .2s ease;
    font-family: 'Nunito', sans-serif;
  }
  .save-btn:hover {
    box-shadow: 0 6px 24px rgba(21,128,61,.38);
    transform: translateY(-1px);
  }
</style>

<div class="edit-wrap min-h-screen py-10 px-4"
     style="background: linear-gradient(180deg, #f0fdf4 0%, #fff 60%);">

  <div class="max-w-xl mx-auto">

    <!-- Back link -->
    <div class="fade-up mb-6">
      <a href="RecipientDashboard.php"
         class="inline-flex items-center gap-2 text-sm font-bold text-green-700 hover:text-green-900 transition">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
        Back to Dashboard
      </a>
    </div>

    <!-- Card -->
    <div class="fade-up delay-1 bg-white border border-green-100 rounded-2xl shadow-xl shadow-green-100/20 overflow-hidden">

      <!-- Header -->
      <div class="bg-gradient-to-r from-green-800 to-emerald-700 px-8 py-6 relative overflow-hidden">
        <div class="absolute inset-0 opacity-10"
             style="background-image: radial-gradient(circle at 10% 50%, white 1px, transparent 1px),
                                      radial-gradient(circle at 90% 30%, white 1px, transparent 1px);
                    background-size: 36px 36px;"></div>
        <div class="relative">
          <p class="text-green-200 text-[10px] font-bold uppercase tracking-widest mb-1">Recipient Portal</p>
          <h2 class="display-font text-white text-2xl">Edit Your Profile</h2>
        </div>
      </div>

      <!-- Alerts -->
      <div class="px-8 pt-6">
        <?php if (isset($flash)): ?>
          <div class="mb-4 p-4 text-sm text-emerald-700 bg-emerald-50 border border-emerald-100 rounded-xl font-semibold">
            <?= htmlspecialchars($flash); ?>
          </div>
        <?php endif; ?>

        <?php if (isset($_GET['updated'])): ?>
          <div class="mb-4 p-4 text-sm text-emerald-700 bg-emerald-50 border border-emerald-100 rounded-xl font-semibold flex items-center gap-2">
            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
            Profile updated successfully!
          </div>
        <?php endif; ?>

        <?php if (isset($_GET['error'])): ?>
          <div class="mb-4 p-4 text-sm text-red-700 bg-red-50 border border-red-100 rounded-xl font-semibold flex items-center gap-2">
            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
            <?= htmlspecialchars($_GET['error']); ?>
          </div>
        <?php endif; ?>
      </div>

      <!-- Form -->
      <form method="POST" enctype="multipart/form-data" action="RecipientStore.php" class="px-8 pb-8 space-y-6">
        <input type="hidden" name="action" value="update_profile">

        <!-- Avatar Upload -->
        <div class="fade-up delay-2 flex flex-col items-center pt-2">
          <label class="avatar-upload relative inline-block" for="avatarInput">
            <?php
              $imgSrc = !empty($recipient['profile_image'])
                ? '../../uploads/' . $recipient['profile_image']
                : '../../uploads/default.png';
            ?>
            <img src="<?= $imgSrc; ?>" id="avatarPreview"
                 class="w-24 h-24 rounded-full object-cover border-2 border-green-200 shadow-lg"
                 alt="Profile Photo">
            <div class="avatar-overlay">
              <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
              </svg>
            </div>
            <input type="file" name="profile_image" id="avatarInput" class="hidden" accept="image/*"
                   onchange="previewAvatar(this)">
          </label>
          <p class="text-[10px] text-green-600 font-bold uppercase tracking-wider mt-2">Click to change photo</p>
        </div>

        <!-- Name Fields -->
        <div class="fade-up delay-3">
          <h3 class="text-[10px] font-black uppercase tracking-widest text-green-700 mb-3 flex items-center gap-2">
            <span class="w-1.5 h-1.5 bg-green-500 rounded-full"></span> Personal Information
          </h3>
          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block text-[10px] font-bold uppercase tracking-wider text-green-800 mb-1.5">First Name</label>
              <input type="text" name="first_name" class="form-input"
                     placeholder="<?= htmlspecialchars($recipient['first_name'] ?? ''); ?>"
                     value="">
            </div>
            <div>
              <label class="block text-[10px] font-bold uppercase tracking-wider text-green-800 mb-1.5">Last Name</label>
              <input type="text" name="last_name" class="form-input"
                     placeholder="<?= htmlspecialchars($recipient['last_name'] ?? ''); ?>"
                     value="">
            </div>
          </div>
        </div>

        <!-- Preferences -->
        <div class="fade-up delay-4">
          <h3 class="text-[10px] font-black uppercase tracking-widest text-green-700 mb-3 flex items-center gap-2">
            <span class="w-1.5 h-1.5 bg-green-500 rounded-full"></span> Preferences
          </h3>
          <label class="block text-[10px] font-bold uppercase tracking-wider text-green-800 mb-1.5">Your Preferences</label>
          <textarea name="preferences" class="form-input"
                    placeholder="Describe what you're looking for…"><?= htmlspecialchars($recipient['preferences'] ?? ''); ?></textarea>
          <p class="text-[10px] text-gray-400 mt-1.5 font-medium">Leave any field empty to keep the current value.</p>
        </div>

        <!-- Submit -->
        <div class="fade-up delay-4 pt-2">
          <button type="submit" class="save-btn">Save Changes</button>
        </div>

      </form>
    </div>
  </div>
</div>

<script>
  function previewAvatar(input) {
    if (input.files && input.files[0]) {
      const reader = new FileReader();
      reader.onload = e => document.getElementById('avatarPreview').src = e.target.result;
      reader.readAsDataURL(input.files[0]);
    }
  }
</script>

<?php include("../../includes/footer.php"); ?>
