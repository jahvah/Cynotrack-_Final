<?php 
session_start();
include('../../../../includes/config.php');
include('../../../../includes/header.php');

// STAFF access only
if (!isset($_SESSION['account_id']) || $_SESSION['role'] !== 'staff') {
    header("Location: ../../../unauthorized.php");
    exit();
}

// Fetch self storage users
$storage_result = mysqli_query($conn, "
    SELECT storage_user_id, first_name, last_name 
    FROM self_storage_users 
    ORDER BY first_name ASC
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
button {
    padding: 10px 15px;
    background: green;
    color: white;
    border: none;
    cursor: pointer;
}
.message { padding: 12px; margin-bottom: 15px; border-radius: 5px; }
.error { background:#f8d7da; color:#721c24; }
.success { background:#d4edda; color:#155724; }

.back-btn {
    display: inline-block;
    padding: 8px 15px;
    background: #555;
    color: white;
    text-decoration: none;
    border-radius: 5px;
    margin-bottom: 15px;
}
.back-btn:hover { background: #333; }

/* Search Results Styling */
.search-container { position: relative; }
#search-results {
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
.search-item {
    padding: 10px;
    cursor: pointer;
    border-bottom: 1px solid #eee;
}
.search-item:hover { background: #f0f0f0; }
</style>

<div class="container">
    <a href="../StaffSpecimenIndex.php" class="back-btn">← Back to Specimen Dashboard</a>
    <h2>Add Self Storage Specimen</h2>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="message error"><?= $_SESSION['error']; ?></div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="message success"><?= $_SESSION['success']; ?></div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <form action="StaffSpecimenSelfStorageStore.php" method="POST" autocomplete="off">
        <input type="hidden" name="action" value="create_self_storage_specimen">

        <label>Self Storage User Search</label>
        <div class="search-container">
            <input type="text" id="storage_search_input" placeholder="Type name to search users..." required>
            <input type="hidden" name="storage_user_id" id="storage_user_id_hidden" required>
            <div id="search-results"></div>
        </div>

        <label>Unique Code</label>
        <input type="text" name="unique_code" required>

        <label>Quantity</label>
        <input type="number" name="quantity" min="1" required>

        <label>Price</label>
        <input type="number" name="price" step="0.01" min="0" required>

        <label>Storage Location</label>
        <input type="text" name="storage_location" required>

        <label>Expiration Date</label>
        <input type="date" name="expiration_date" required>

        <button type="submit">Add Self Storage Specimen</button>
    </form>
</div>

<script>
// Pass PHP array to JavaScript
const storageUsers = <?php echo json_encode($storage_users); ?>;

const searchInput = document.getElementById('storage_search_input');
const resultsDiv = document.getElementById('search-results');
const hiddenIdInput = document.getElementById('storage_user_id_hidden');

searchInput.addEventListener('input', function() {
    const query = this.value.toLowerCase();
    resultsDiv.innerHTML = '';
    
    if (query.length > 0) {
        const matches = storageUsers.filter(u => 
            (u.first_name + ' ' + u.last_name).toLowerCase().includes(query)
        );

        if (matches.length > 0) {
            resultsDiv.style.display = 'block';
            matches.forEach(match => {
                const div = document.createElement('div');
                div.classList.add('search-item');
                div.textContent = match.first_name + ' ' + match.last_name;
                div.onclick = function() {
                    searchInput.value = match.first_name + ' ' + match.last_name;
                    hiddenIdInput.value = match.storage_user_id;
                    resultsDiv.style.display = 'none';
                };
                resultsDiv.appendChild(div);
            });
        } else {
            resultsDiv.style.display = 'none';
        }
    } else {
        resultsDiv.style.display = 'none';
    }
});

// Close list if user clicks outside
document.addEventListener('click', function(e) {
    if (e.target !== searchInput) {
        resultsDiv.style.display = 'none';
    }
});
</script>

<?php include('../../../../includes/footer.php'); ?>