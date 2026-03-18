<?php
session_start();
include("../../../includes/config.php");
include("../../../includes/header.php");

// Ensure recipient is logged in
if (!isset($_SESSION['account_id']) || $_SESSION['role'] !== 'recipient') {
    header("Location: ../../../login.php");
    exit;
}

$recipient_id = $_SESSION['role_user_id'];
$donor_id = $_GET['id'] ?? 0;

if (!$donor_id) {
    echo "<p class='text-red-500 text-center mt-10'>Invalid donor.</p>";
    exit;
}

// Fetch donor info
$stmt = $conn->prepare("
    SELECT 
        first_name, last_name, profile_image, medical_history,
        height_cm, weight_kg, eye_color, hair_color, blood_type, ethnicity
    FROM donors_users
    WHERE donor_id = ?
");
$stmt->bind_param("i", $donor_id);
$stmt->execute();
$result = $stmt->get_result();

// Fetch available quantity & price
$stmt2 = $conn->prepare("
    SELECT SUM(quantity) AS available_quantity, MAX(price) AS unit_price
    FROM specimens
    WHERE specimen_owner_type = 'donor'
    AND specimen_owner_id = ?
    AND status = 'stored'
");
$stmt2->bind_param("i", $donor_id);
$stmt2->execute();
$result2 = $stmt2->get_result();
$stockData = $result2->fetch_assoc();

$available_quantity = $stockData['available_quantity'] ?? 0;
$unit_price         = $stockData['unit_price'] ?? 0;

if ($result->num_rows === 0) {
    echo "<p class='text-red-500 text-center mt-10'>Donor not found.</p>";
    exit;
}

$donor = $result->fetch_assoc();
?>

<style>
  @import url('https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=DM+Sans:wght@300;400;500;600;700&display=swap');

  .page-wrap { font-family: 'DM Sans', sans-serif; }
  .display-font { font-family: 'DM Serif Display', serif; }

  .fade-up {
    opacity: 0;
    transform: translateY(18px);
    animation: fadeUp 0.55s ease forwards;
  }
  @keyframes fadeUp {
    to { opacity: 1; transform: translateY(0); }
  }
  .fade-up:nth-child(1) { animation-delay: 0.05s; }
  .fade-up:nth-child(2) { animation-delay: 0.13s; }
  .fade-up:nth-child(3) { animation-delay: 0.21s; }
  .fade-up:nth-child(4) { animation-delay: 0.29s; }

  .stat-card {
    background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
    border: 1px solid #bbf7d0;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
  }
  .stat-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 24px rgba(21, 128, 61, 0.12);
  }

  .qty-btn {
    width: 40px; height: 40px;
    border-radius: 10px;
    border: 1.5px solid #bbf7d0;
    background: white;
    color: #16a34a;
    font-size: 1.25rem;
    font-weight: 700;
    cursor: pointer;
    display: flex; align-items: center; justify-content: center;
    transition: all 0.15s ease;
  }
  .qty-btn:hover { background: #16a34a; color: white; border-color: #16a34a; }
  .qty-btn:disabled { opacity: 0.3; cursor: not-allowed; }

  .receipt-drop {
    border: 2px dashed #86efac;
    border-radius: 16px;
    background: #f0fdf4;
    transition: border-color 0.2s, background 0.2s;
  }
  .receipt-drop:hover { border-color: #16a34a; background: #dcfce7; }

  .submit-btn {
    background: linear-gradient(135deg, #16a34a, #15803d);
    transition: all 0.2s ease;
    box-shadow: 0 4px 16px rgba(21, 128, 61, 0.3);
  }
  .submit-btn:hover {
    background: linear-gradient(135deg, #15803d, #166534);
    box-shadow: 0 6px 24px rgba(21, 128, 61, 0.4);
    transform: translateY(-1px);
  }

  .price-display {
    background: linear-gradient(135deg, #14532d, #15803d);
    color: white;
    border-radius: 16px;
  }

  input[type="number"]::-webkit-inner-spin-button,
  input[type="number"]::-webkit-outer-spin-button { -webkit-appearance: none; }
</style>

<div class="page-wrap min-h-screen bg-gradient-to-br from-green-50/60 via-white to-emerald-50/40 py-10 px-4">
  <div class="max-w-5xl mx-auto space-y-6">

    <!-- Back Link -->
    <div class="fade-up">
      <a href="../RecipientDashboard.php"
         class="inline-flex items-center gap-2 text-sm font-semibold text-green-700 hover:text-green-900 transition">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
        Back to Dashboard
      </a>
    </div>

    <!-- Error / Success -->
    <?php if (isset($_SESSION['error'])): ?>
      <div class="fade-up p-4 text-sm text-red-700 bg-red-50 border border-red-200 rounded-xl font-medium flex items-center gap-2">
        <svg class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
        <?= $_SESSION['error']; unset($_SESSION['error']); ?>
      </div>
    <?php endif; ?>
    <?php if (isset($_SESSION['success'])): ?>
      <div class="fade-up p-4 text-sm text-emerald-700 bg-emerald-50 border border-emerald-200 rounded-xl font-medium flex items-center gap-2">
        <svg class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
        <?= $_SESSION['success']; unset($_SESSION['success']); ?>
      </div>
    <?php endif; ?>

    <!-- Donor Profile Card -->
    <div class="fade-up bg-white rounded-2xl shadow-lg shadow-green-100/30 border border-green-100 overflow-hidden">

      <!-- Header Banner -->
      <div class="relative bg-gradient-to-r from-green-800 to-emerald-700 px-8 py-6">
        <div class="absolute inset-0 opacity-10"
             style="background-image: radial-gradient(circle at 20% 50%, white 1px, transparent 1px),
                                      radial-gradient(circle at 80% 20%, white 1px, transparent 1px);
                    background-size: 40px 40px;"></div>
        <div class="relative flex items-center gap-5">
          <!-- Avatar -->
          <?php if (!empty($donor['profile_image'])): ?>
            <img src="../../../uploads/<?= htmlspecialchars($donor['profile_image']); ?>"
                 class="w-20 h-20 rounded-2xl object-cover border-2 border-white/40 shadow-xl">
          <?php else: ?>
            <div class="w-20 h-20 rounded-2xl bg-white/20 border-2 border-white/30 flex items-center justify-center text-white font-bold text-2xl shadow-xl display-font">
              <?= strtoupper(substr($donor['first_name'], 0, 1) . substr($donor['last_name'], 0, 1)); ?>
            </div>
          <?php endif; ?>

          <div>
            <p class="text-green-200 text-xs font-bold uppercase tracking-widest mb-1">Donor Profile</p>
            <h1 class="display-font text-white text-2xl leading-tight">
              <?= htmlspecialchars($donor['first_name'] . ' ' . $donor['last_name']); ?>
            </h1>
            <div class="mt-2 inline-flex items-center gap-1.5 px-3 py-1 bg-white/15 rounded-full text-white text-xs font-semibold">
              <span class="w-1.5 h-1.5 rounded-full bg-emerald-300"></span>
              <?= $available_quantity > 0 ? $available_quantity . ' specimens available' : 'No specimens available'; ?>
            </div>
          </div>
        </div>
      </div>

      <!-- Body: Stats + Details -->
      <div class="p-8">

        <!-- Quick Stats Row -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-8">
          <?php
            $stats = [
              ['label' => 'Height', 'value' => ($donor['height_cm'] ?? '—') . ' cm', 'icon' => '📏'],
              ['label' => 'Weight', 'value' => ($donor['weight_kg'] ?? '—') . ' kg', 'icon' => '⚖️'],
              ['label' => 'Blood Type', 'value' => $donor['blood_type'] ?? '—', 'icon' => '🩸'],
              ['label' => 'Ethnicity', 'value' => $donor['ethnicity'] ?? '—', 'icon' => '🌍'],
            ];
            foreach ($stats as $stat):
          ?>
            <div class="stat-card rounded-xl p-4 text-center">
              <div class="text-xl mb-1"><?= $stat['icon']; ?></div>
              <div class="text-[10px] font-bold uppercase tracking-widest text-green-600 mb-0.5"><?= $stat['label']; ?></div>
              <div class="text-sm font-bold text-green-900"><?= htmlspecialchars($stat['value']); ?></div>
            </div>
          <?php endforeach; ?>
        </div>

        <!-- Two-column details -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">

          <!-- Physical Traits -->
          <div class="bg-green-50/50 border border-green-100 rounded-xl p-5">
            <h3 class="text-xs font-black uppercase tracking-widest text-green-700 mb-4 flex items-center gap-2">
              <span class="w-2 h-2 bg-green-500 rounded-full"></span> Physical Traits
            </h3>
            <div class="space-y-3">
              <div class="flex justify-between items-center py-2 border-b border-green-100/80">
                <span class="text-xs font-semibold text-green-700">Eye Color</span>
                <span class="text-sm font-bold text-gray-800"><?= htmlspecialchars($donor['eye_color'] ?? 'N/A'); ?></span>
              </div>
              <div class="flex justify-between items-center py-2 border-b border-green-100/80">
                <span class="text-xs font-semibold text-green-700">Hair Color</span>
                <span class="text-sm font-bold text-gray-800"><?= htmlspecialchars($donor['hair_color'] ?? 'N/A'); ?></span>
              </div>
              <div class="flex justify-between items-center py-2">
                <span class="text-xs font-semibold text-green-700">Blood Type</span>
                <span class="text-sm font-bold text-gray-800"><?= htmlspecialchars($donor['blood_type'] ?? 'N/A'); ?></span>
              </div>
            </div>
          </div>

          <!-- Medical History -->
          <div class="bg-green-50/50 border border-green-100 rounded-xl p-5">
            <h3 class="text-xs font-black uppercase tracking-widest text-green-700 mb-4 flex items-center gap-2">
              <span class="w-2 h-2 bg-green-500 rounded-full"></span> Medical History
            </h3>
            <p class="text-sm text-gray-700 leading-relaxed">
              <?= nl2br(htmlspecialchars($donor['medical_history'] ?? 'No medical history on file.')); ?>
            </p>
          </div>
        </div>

        <!-- Specimen Availability Banner -->
        <?php if ($available_quantity > 0): ?>
          <div class="price-display p-5 mb-8 flex items-center justify-between flex-wrap gap-4">
            <div>
              <p class="text-green-200 text-xs font-bold uppercase tracking-widest mb-1">Available Stock</p>
              <p class="text-white text-2xl font-bold display-font"><?= $available_quantity; ?> specimens</p>
            </div>
            <?php if ($unit_price > 0): ?>
              <div class="text-right">
                <p class="text-green-200 text-xs font-bold uppercase tracking-widest mb-1">Price per unit</p>
                <p class="text-white text-2xl font-bold display-font">₱<?= number_format($unit_price, 2); ?></p>
              </div>
            <?php endif; ?>
          </div>
        <?php else: ?>
          <div class="bg-red-50 border border-red-200 rounded-xl p-5 mb-8 text-center">
            <p class="text-red-700 font-bold text-sm">No specimens currently available from this donor.</p>
          </div>
        <?php endif; ?>

        <!-- Request Form -->
        <?php if ($available_quantity > 0): ?>
          <div class="bg-green-50/40 border border-green-100 rounded-2xl p-6">
            <h3 class="text-xs font-black uppercase tracking-widest text-green-800 mb-6 flex items-center gap-2">
              <span class="w-2 h-2 bg-green-500 rounded-full"></span> Request Specimen
            </h3>

            <form method="POST" action="RecipientDonorRequestStore.php" enctype="multipart/form-data" id="requestForm">
              <input type="hidden" name="recipient_id" value="<?= $recipient_id ?>">
              <input type="hidden" name="donor_id" value="<?= $donor_id ?>">

              <!-- Quantity Selector -->
              <div class="mb-6">
                <label class="block text-[10px] font-bold uppercase tracking-wider text-green-800 mb-3">Quantity</label>
                <div class="flex items-center gap-4">
                  <button type="button" class="qty-btn" id="decreaseBtn" onclick="changeQty(-1)">−</button>
                  <input type="number" id="quantity" name="quantity" value="1" min="1" max="<?= $available_quantity ?>" readonly
                         class="w-20 text-center text-xl font-bold text-green-900 bg-white border border-green-200 rounded-xl py-2 outline-none">
                  <button type="button" class="qty-btn" id="increaseBtn" onclick="changeQty(1)">+</button>
                  <span class="text-xs text-green-600 font-medium">of <?= $available_quantity; ?> available</span>
                </div>
              </div>

              <!-- Dynamic Total Price -->
              <?php if ($unit_price > 0): ?>
                <div class="mb-6 bg-white border border-green-100 rounded-xl px-5 py-4 flex justify-between items-center">
                  <span class="text-sm font-semibold text-green-700">Estimated Total</span>
                  <span class="text-xl font-bold text-green-900 display-font" id="totalPrice">
                    ₱<?= number_format($unit_price, 2); ?>
                  </span>
                </div>
              <?php endif; ?>

              <!-- Receipt Upload (hidden until Request clicked) -->
              <div id="receiptSection" class="mb-6 hidden">
                <label class="block text-[10px] font-bold uppercase tracking-wider text-green-800 mb-3">Upload Payment Receipt</label>
                <label class="receipt-drop flex flex-col items-center justify-center gap-3 py-8 cursor-pointer">
                  <svg class="w-10 h-10 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                          d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                  </svg>
                  <div class="text-center">
                    <p class="text-sm font-bold text-green-700">Drop your receipt here</p>
                    <p class="text-xs text-green-500 mt-0.5">JPG, PNG, or PDF accepted</p>
                  </div>
                  <input type="file" name="receipt" id="receiptInput" accept="image/*,.pdf"
                         class="hidden" onchange="previewFile(this)">
                </label>
                <div id="filePreview" class="mt-2 text-xs text-green-700 font-semibold hidden"></div>
              </div>

              <!-- Action Buttons -->
              <div class="flex flex-col sm:flex-row gap-3" id="actionButtons">
                <button type="button" onclick="showReceipt()"
                        id="requestBtn"
                        class="submit-btn flex-1 text-white font-bold py-3.5 rounded-xl text-sm">
                  Proceed to Payment Upload
                </button>
                <button type="submit" id="submitBtn"
                        class="hidden flex-1 bg-emerald-800 hover:bg-emerald-900 text-white font-bold py-3.5 rounded-xl text-sm transition">
                  Confirm &amp; Submit Request
                </button>
                <a href="../RecipientDashboard.php"
                   class="flex-none px-6 py-3.5 border border-green-200 text-green-700 font-bold rounded-xl hover:bg-green-50 transition text-sm text-center">
                  Cancel
                </a>
              </div>

            </form>
          </div>
        <?php else: ?>
          <div class="text-center">
            <a href="../RecipientDashboard.php"
               class="inline-block px-8 py-3 bg-green-600 text-white font-bold rounded-xl hover:bg-green-700 transition text-sm">
              ← Back to Dashboard
            </a>
          </div>
        <?php endif; ?>

      </div><!-- /card body -->
    </div><!-- /card -->
  </div><!-- /max-w -->
</div>

<script>
  const maxQty    = <?= $available_quantity ?>;
  const unitPrice = <?= $unit_price ?? 0 ?>;

  function changeQty(delta) {
    const input = document.getElementById('quantity');
    let val = parseInt(input.value) + delta;
    val = Math.max(1, Math.min(maxQty, val));
    input.value = val;

    const totalEl = document.getElementById('totalPrice');
    if (totalEl) {
      totalEl.textContent = '₱' + (unitPrice * val).toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    document.getElementById('decreaseBtn').disabled = val <= 1;
    document.getElementById('increaseBtn').disabled = val >= maxQty;
  }

  function showReceipt() {
    document.getElementById('receiptSection').classList.remove('hidden');
    document.getElementById('requestBtn').classList.add('hidden');
    document.getElementById('submitBtn').classList.remove('hidden');
    document.getElementById('receiptSection').scrollIntoView({ behavior: 'smooth', block: 'center' });
  }

  function previewFile(input) {
    const preview = document.getElementById('filePreview');
    if (input.files && input.files[0]) {
      preview.textContent = '✓ Selected: ' + input.files[0].name;
      preview.classList.remove('hidden');
    }
  }

  // Init button states
  document.getElementById('decreaseBtn').disabled = true;
</script>

<?php include("../../../includes/footer.php"); ?>
