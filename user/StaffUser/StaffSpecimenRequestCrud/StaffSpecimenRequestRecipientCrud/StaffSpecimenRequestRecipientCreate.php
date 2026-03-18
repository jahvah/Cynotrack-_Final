<?php
session_start();
include('../../../../includes/config.php');
include('../../../../includes/head.php'); 
include('../../../../includes/staff_header.php');

// STAFF access only
if (!isset($_SESSION['account_id']) || $_SESSION['role'] !== 'staff') {
    header("Location: ../../../../unauthorized.php");
    exit();
}

// Fetch recipients
$recipient_result = mysqli_query($conn, "SELECT recipient_id, first_name, last_name FROM recipients_users ORDER BY first_name ASC");
$recipients = [];
while ($row = mysqli_fetch_assoc($recipient_result)) {
    $recipients[] = $row;
}

// Fetch donors
$donor_result = mysqli_query($conn, "SELECT donor_id, first_name, last_name FROM donors_users ORDER BY first_name ASC");
$donors = [];
while ($row = mysqli_fetch_assoc($donor_result)) {
    $donors[] = $row;
}
?>

<div class="max-w-7xl mx-auto py-10 px-4">
    <div class="mb-6">
        <a href="../StaffSpecimenRequestIndex.php" class="text-sm font-bold text-green-700 hover:text-green-800 transition flex items-center gap-1">
            ← Back to Request Dashboard
        </a>
    </div>

    <div class="max-w-2xl mx-auto">
        <div class="bg-white border border-green-100 rounded-2xl shadow-xl shadow-green-100/20 overflow-hidden">
            
            <div class="bg-green-50/50 border-b border-green-100 px-8 py-6">
                <h2 class="text-2xl font-bold text-green-900">Create Specimen Request</h2>
                <p class="text-green-600 text-sm mt-1 font-medium">Initiate a new transfer request for a recipient.</p>
            </div>

            <div class="p-8">
                <?php if (isset($_SESSION['error'])): ?>
                    <div class="mb-6 p-4 text-sm text-red-700 bg-red-50 border border-red-100 rounded-lg font-medium">
                        <?= $_SESSION['error']; unset($_SESSION['error']); ?>
                    </div>
                <?php endif; ?>

                <form action="StaffSpecimenRequestRecipientStore.php" method="POST" autocomplete="off" enctype="multipart/form-data" class="space-y-6">
                    <input type="hidden" name="action" value="create_specimen_request">

                    <div class="relative">
                        <label class="block text-[10px] font-bold uppercase tracking-wider text-green-800 mb-2">Search Recipient</label>
                        <input type="text" id="recipient_search_input" placeholder="Start typing name..." required
                            class="w-full px-4 py-3 border border-green-100 rounded-xl focus:ring-2 focus:ring-green-500 outline-none transition">
                        <input type="hidden" name="recipient_id" id="recipient_id_hidden" required>
                        <div id="recipient_search_results" class="absolute z-50 w-full mt-1 bg-white border border-green-100 rounded-xl shadow-xl max-h-48 overflow-y-auto hidden"></div>
                    </div>

                    <div class="relative">
                        <label class="block text-[10px] font-bold uppercase tracking-wider text-green-800 mb-2">Search Donor</label>
                        <input type="text" id="donor_search_input" placeholder="Start typing name..." required
                            class="w-full px-4 py-3 border border-green-100 rounded-xl focus:ring-2 focus:ring-green-500 outline-none transition">
                        <input type="hidden" name="donor_id" id="donor_id_hidden" required>
                        <div id="donor_search_results" class="absolute z-50 w-full mt-1 bg-white border border-green-100 rounded-xl shadow-xl max-h-48 overflow-y-auto hidden"></div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-[10px] font-bold uppercase tracking-wider text-green-800 mb-2">Select Specimen</label>
                            <select name="specimen_id" id="specimenSelect" required
                                class="w-full px-4 py-3 border border-green-100 rounded-xl focus:ring-2 focus:ring-green-500 outline-none transition bg-white cursor-pointer">
                                <option value="">-- Select Donor First --</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-[10px] font-bold uppercase tracking-wider text-green-800 mb-2">Requested Quantity</label>
                            <input type="number" name="requested_quantity" min="1" required
                                class="w-full px-4 py-3 border border-green-100 rounded-xl focus:ring-2 focus:ring-green-500 outline-none transition">
                        </div>
                    </div>

                    <div class="p-4 bg-green-50/30 border border-dashed border-green-200 rounded-xl">
                        <label class="block text-[10px] font-bold uppercase tracking-wider text-green-800 mb-2">Upload Proof of Payment / Receipt</label>
                        <input type="file" name="receipt_image" accept="image/*" required
                            class="block w-full text-xs text-green-900 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-bold file:bg-green-100 file:text-green-700 hover:file:bg-green-200 cursor-pointer">
                    </div>

                    <div class="pt-6 border-t border-green-50 flex gap-4">
                        <button type="submit" class="flex-1 bg-green-600 hover:bg-green-700 text-white font-bold py-4 rounded-xl transition duration-200 shadow-lg shadow-green-100">
                            Create Request
                        </button>
                        <a href="../StaffSpecimenRequestIndex.php" class="px-8 py-4 border border-green-200 text-green-700 font-bold rounded-xl hover:bg-green-50 transition text-center flex items-center justify-center">
                            Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
const recipients = <?php echo json_encode($recipients); ?>;
const donors = <?php echo json_encode($donors); ?>;

function setupSearch(inputId, resultsId, hiddenId, dataArray, onSelect = null) {
    const input = document.getElementById(inputId);
    const results = document.getElementById(resultsId);
    const hidden = document.getElementById(hiddenId);

    input.addEventListener('input', function() {
        const query = this.value.toLowerCase();
        results.innerHTML = '';
        if (query.length > 0) {
            const matches = dataArray.filter(item => (item.first_name + ' ' + item.last_name).toLowerCase().includes(query));
            if (matches.length > 0) {
                results.classList.remove('hidden');
                matches.forEach(item => {
                    const div = document.createElement('div');
                    div.className = 'px-4 py-3 text-sm text-gray-700 hover:bg-green-50 cursor-pointer border-b border-gray-50 last:border-0 transition';
                    div.textContent = item.first_name + ' ' + item.last_name;
                    div.onclick = () => {
                        input.value = item.first_name + ' ' + item.last_name;
                        hidden.value = item.recipient_id || item.donor_id;
                        results.classList.add('hidden');
                        if (onSelect) onSelect(hidden.value);
                    };
                    results.appendChild(div);
                });
            } else results.classList.add('hidden');
        } else results.classList.add('hidden');
    });
}

// Initialize Recipient Search
setupSearch('recipient_search_input', 'recipient_search_results', 'recipient_id_hidden', recipients);

// Initialize Donor Search
setupSearch('donor_search_input', 'donor_search_results', 'donor_id_hidden', donors, (id) => {
    loadSpecimens(id);
});

// Load Specimens via AJAX
function loadSpecimens(donorId) {
    const specimenSelect = document.getElementById("specimenSelect");
    specimenSelect.innerHTML = "<option>Loading specimens...</option>";
    fetch("GetSpecimenByDonor.php?donor_id=" + donorId)
        .then(res => res.text())
        .then(data => { specimenSelect.innerHTML = data; });
}

// Click outside to close
document.addEventListener('click', e => {
    if (!e.target.closest('.relative')) {
        document.getElementById('recipient_search_results').classList.add('hidden');
        document.getElementById('donor_search_results').classList.add('hidden');
    }
});
</script>

<?php include('../../../../includes/footer.php'); ?>