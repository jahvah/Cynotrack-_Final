<?php
session_start();
include('../../../../includes/config.php');
include('../../../../includes/header.php');

// STAFF access only
if (!isset($_SESSION['account_id']) || $_SESSION['role'] !== 'staff') {
    header("Location: ../../../../unauthorized.php");
    exit();
}

// Fetch self storage users
$storage_result = mysqli_query($conn, "
    SELECT s.storage_user_id, s.first_name, s.last_name
    FROM self_storage_users s
    ORDER BY s.first_name ASC
");

$storage_users = [];
while ($row = mysqli_fetch_assoc($storage_result)) {
    $storage_users[] = $row;
}
?>

<style>
.container { padding: 30px; }
form { max-width: 500px; margin: auto; }
input, select { width: 100%; padding: 10px; margin: 10px 0; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
button { padding: 10px 15px; background: green; color: white; border: none; cursor: pointer; }
.message { padding: 12px; margin-bottom: 15px; border-radius: 5px; }
.error { background:#f8d7da; color:#721c24; }
.success { background:#d4edda; color:#155724; }
.back-btn { display: inline-block; padding: 8px 15px; background: #555; color: white; text-decoration: none; border-radius: 5px; margin-bottom: 15px; }
.back-btn:hover { background: #333; }

.search-container { position: relative; }
.search-results {
    position: absolute;
    width: 100%;
    background: white;
    border: 1px solid #ccc;
    border-top: none;
    z-index: 1000;
    max-height: 200px;
    overflow-y: auto;
    display: none;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
}
.search-item { padding: 10px; cursor: pointer; border-bottom: 1px solid #eee; }
.search-item:hover { background: #f0f0f0; }
</style>

<div class="container">

<?php if (isset($_SESSION['error'])): ?>
<div class="message error"><?= $_SESSION['error']; ?></div>
<?php unset($_SESSION['error']); ?>
<?php endif; ?>

<?php if (isset($_SESSION['success'])): ?>
<div class="message success"><?= $_SESSION['success']; ?></div>
<?php unset($_SESSION['success']); ?>
<?php endif; ?>

<a href="../StaffSpecimenRequestIndex.php" class="back-btn">← Back to Request Dashboard</a>
<h2>Create Self Storage Specimen Request</h2>

<form action="StaffSpecimenRequestSelfStorageStore.php" method="POST" autocomplete="off" enctype="multipart/form-data">        <input type="hidden" name="action" value="create_specimen_request">
<input type="hidden" name="action" value="create_specimen_request">

<!-- Self Storage User Search -->
<label>Search Self Storage User</label>
<div class="search-container">
<input type="text" id="storage_search_input" placeholder="Type name to search..." required>
<input type="hidden" name="storage_user_id" id="storage_id_hidden" required>
<div id="storage_search_results" class="search-results"></div>
</div>

<!-- Specimen -->
<label>Select Specimen</label>
<select name="specimen_id" id="specimenSelect" required>
<option value="">-- Select Self Storage User First --</option>
</select>

<!-- Quantity -->
<label>Requested Quantity</label>
<input type="number" name="requested_quantity" min="1" required>

<label>Upload Receipt (optional)</label>
<input type="file" name="receipt_image" accept="image/*"required>

<button type="submit">Create Request</button>
</form>
</div>

<script>
const storageUsers = <?php echo json_encode($storage_users); ?>;

// Self Storage search
const storageInput = document.getElementById('storage_search_input');
const storageResults = document.getElementById('storage_search_results');
const storageHidden = document.getElementById('storage_id_hidden');

storageInput.addEventListener('input', function() {

const query = this.value.toLowerCase();
storageResults.innerHTML = '';

if (query.length > 0) {

const matches = storageUsers.filter(u =>
(u.first_name + ' ' + u.last_name).toLowerCase().includes(query)
);

if (matches.length > 0) {

storageResults.style.display = 'block';

matches.forEach(u => {

const div = document.createElement('div');
div.classList.add('search-item');
div.textContent = u.first_name + ' ' + u.last_name;

div.onclick = () => {

storageInput.value = u.first_name + ' ' + u.last_name;
storageHidden.value = u.storage_user_id;
storageResults.style.display = 'none';

loadSpecimens(u.storage_user_id);

};

storageResults.appendChild(div);

});

} else {
storageResults.style.display = 'none';
}

} else {
storageResults.style.display = 'none';
}

});

// hide dropdown
document.addEventListener('click', e => {
if (e.target !== storageInput) storageResults.style.display = 'none';
});

// load specimens of self storage user
function loadSpecimens(storageUserId) {

const specimenSelect = document.getElementById("specimenSelect");
specimenSelect.innerHTML = "<option>Loading...</option>";

fetch("GetSpecimenBySelfStorage.php?storage_user_id=" + storageUserId)
.then(res => res.text())
.then(data => {
specimenSelect.innerHTML = data;
});

}
</script>

<?php include('../../../../includes/footer.php'); ?>