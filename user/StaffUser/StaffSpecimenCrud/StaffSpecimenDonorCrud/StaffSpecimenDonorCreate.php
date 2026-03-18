<?php 
session_start();
include('../../../../includes/config.php');
include('../../../../includes/head.php');
include('../../../../includes/staff_header.php');


// STAFF access only
if (!isset($_SESSION['account_id']) || $_SESSION['role'] !== 'staff') {
    header("Location: ../../../unauthorized.php");
    exit();
}

// Fetch donors
$donors_result = mysqli_query($conn, "SELECT donor_id, first_name, last_name FROM donors_users ORDER BY first_name ASC");
$donors = [];
while ($row = mysqli_fetch_assoc($donors_result)) {
    $donors[] = $row;
}
?>

<div class="max-w-7xl mx-auto py-10 px-4">
    <div class="mb-6">
        <a href="../StaffSpecimenIndex.php" class="text-sm font-bold text-green-700 hover:text-green-800 transition flex items-center gap-1">
            ← Back to Specimen Dashboard
        </a>
    </div>

    <div class="max-w-4xl mx-auto">
        <div class="bg-white border border-green-100 rounded-2xl shadow-xl shadow-green-100/20 overflow-hidden">
            
            <div class="bg-green-50/50 border-b border-green-100 px-8 py-6">
                <h2 class="text-2xl font-bold text-green-900">Add Donor Specimen</h2>
                <p class="text-green-600 text-sm mt-1 font-medium">Record new specimen inventory and link it to a donor profile.</p>
            </div>

            <div class="p-8">
                <?php if (isset($_SESSION['error'])): ?>
                    <div class="mb-6 p-4 text-sm text-red-700 bg-red-50 border border-red-100 rounded-lg font-medium">
                        <?= $_SESSION['error']; ?>
                    </div>
                    <?php unset($_SESSION['error']); ?>
                <?php endif; ?>

                <?php if (isset($_SESSION['success'])): ?>
                    <div class="mb-6 p-4 text-sm text-emerald-700 bg-emerald-50 border border-emerald-100 rounded-lg font-medium">
                        <?= $_SESSION['success']; ?>
                    </div>
                    <?php unset($_SESSION['success']); ?>
                <?php endif; ?>

                <form action="StaffSpecimenDonorStore.php" method="POST" autocomplete="off" class="space-y-8">
                    <input type="hidden" name="action" value="create_donor_specimen">

                    <div>
                        <h3 class="text-sm font-black text-green-900 uppercase tracking-widest mb-4 flex items-center gap-2">
                            <span class="w-2 h-2 bg-green-500 rounded-full"></span> Donor Linkage
                        </h3>
                        <div class="relative">
                            <label class="block text-[10px] font-bold uppercase tracking-wider text-green-800 mb-2">Search Donor Name</label>
                            <input type="text" id="donor_search_input" placeholder="Type name to search donors..." required
                                class="w-full px-4 py-3 border border-green-100 rounded-xl focus:ring-2 focus:ring-green-500 outline-none transition bg-green-50/10">
                            <input type="hidden" name="donor_id" id="donor_id_hidden" required>
                            
                            <div id="search-results" class="absolute z-50 w-full mt-1 bg-white border border-green-100 rounded-xl shadow-lg max-h-60 overflow-y-auto hidden">
                                </div>
                        </div>
                    </div>

                    <div class="pt-6 border-t border-green-50">
                        <h3 class="text-sm font-black text-green-900 uppercase tracking-widest mb-4 flex items-center gap-2">
                            <span class="w-2 h-2 bg-green-500 rounded-full"></span> Specimen Details
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="md:col-span-2">
                                <label class="block text-[10px] font-bold uppercase tracking-wider text-green-800 mb-2">Unique Specimen Code</label>
                                <input type="text" name="unique_code" required placeholder="e.g. SPEC-2024-001"
                                    class="w-full px-4 py-3 border border-green-100 rounded-xl focus:ring-2 focus:ring-green-500 outline-none transition">
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold uppercase tracking-wider text-green-800 mb-2">Quantity</label>
                                <input type="number" name="quantity" min="1" required placeholder="1"
                                    class="w-full px-4 py-3 border border-green-100 rounded-xl focus:ring-2 focus:ring-green-500 outline-none transition">
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold uppercase tracking-wider text-green-800 mb-2">Price ($)</label>
                                <input type="number" name="price" step="0.01" min="0" required placeholder="0.00"
                                    class="w-full px-4 py-3 border border-green-100 rounded-xl focus:ring-2 focus:ring-green-500 outline-none transition">
                            </div>
                        </div>
                    </div>

                    <div class="pt-6 border-t border-green-50">
                        <h3 class="text-sm font-black text-green-900 uppercase tracking-widest mb-4 flex items-center gap-2">
                            <span class="w-2 h-2 bg-green-500 rounded-full"></span> Logistics & Safety
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-[10px] font-bold uppercase tracking-wider text-green-800 mb-2">Storage Location</label>
                                <input type="text" name="storage_location" required placeholder="Cryo Tank Alpha"
                                    class="w-full px-4 py-3 border border-green-100 rounded-xl focus:ring-2 focus:ring-green-500 outline-none transition">
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold uppercase tracking-wider text-green-800 mb-2">Expiration Date</label>
                                <input type="date" name="expiration_date" required
                                    class="w-full px-4 py-3 border border-green-100 rounded-xl focus:ring-2 focus:ring-green-500 outline-none transition">
                            </div>
                        </div>
                    </div>

                    <div class="pt-8 border-t border-green-50">
                        <button type="submit" 
                            class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-4 rounded-xl transition duration-200 shadow-lg shadow-green-100 flex items-center justify-center gap-2">
                            <span>Register Specimen</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
const donors = <?php echo json_encode($donors); ?>;
const searchInput = document.getElementById('donor_search_input');
const resultsDiv = document.getElementById('search-results');
const hiddenIdInput = document.getElementById('donor_id_hidden');

searchInput.addEventListener('input', function() {
    const query = this.value.toLowerCase();
    resultsDiv.innerHTML = '';
    
    if (query.length > 0) {
        const matches = donors.filter(d => 
            (d.first_name + ' ' + d.last_name).toLowerCase().includes(query)
        );

        if (matches.length > 0) {
            resultsDiv.classList.remove('hidden');
            matches.forEach(match => {
                const div = document.createElement('div');
                div.className = 'px-4 py-3 cursor-pointer border-b border-green-50 last:border-0 hover:bg-green-50 transition text-sm text-green-900';
                div.textContent = match.first_name + ' ' + match.last_name;
                div.onclick = function() {
                    searchInput.value = match.first_name + ' ' + match.last_name;
                    hiddenIdInput.value = match.donor_id;
                    resultsDiv.classList.add('hidden');
                };
                resultsDiv.appendChild(div);
            });
        } else {
            resultsDiv.classList.add('hidden');
        }
    } else {
        resultsDiv.classList.add('hidden');
    }
});

document.addEventListener('click', function(e) {
    if (e.target !== searchInput) {
        resultsDiv.classList.add('hidden');
    }
});
</script>

<?php include('../../../../includes/footer.php'); ?>