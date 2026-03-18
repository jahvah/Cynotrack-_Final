<?php
session_start();
include('../../../../includes/config.php');
include('../../../../includes/header.php');

// STAFF access only
if (!isset($_SESSION['account_id']) || $_SESSION['role'] !== 'staff') {
    header("Location: ../../../unauthorized.php");
    exit();
}

// Fetch donors for search
$donor_result = mysqli_query($conn, "SELECT donor_id, first_name, last_name FROM donors_users ORDER BY first_name ASC");
$donors = [];
while ($row = mysqli_fetch_assoc($donor_result)) {
    $donors[] = $row;
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
    <a href="../StaffAppointmentIndex.php" class="back-btn">← Back to Appointment Dashboard</a>
    <h2>Add Donor Appointment</h2>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="message error"><?= $_SESSION['error']; ?></div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="message success"><?= $_SESSION['success']; ?></div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <form action="StaffAppointmentDonorStore.php" method="POST" autocomplete="off">
        <input type="hidden" name="action" value="create_donor_appointment">

        <label>Search Donor</label>
        <div class="search-container">
            <input type="text" id="donor_search_input" placeholder="Type name to search donors..." required>
            <input type="hidden" name="donor_id" id="donor_id_hidden" required>
            <div id="search-results"></div>
        </div>

        <label>Appointment Date & Time</label>
        <input type="datetime-local" name="appointment_date" required>

        <!-- Appointment Type -->
<label>Appointment Type</label>
<select name="appointment_type" required>
    <option value="consultation">Consultation</option>
    <option value="donation">Donation</option>
</select>   

        <label>Status</label>
        <select name="status" required>
            <option value="scheduled">Scheduled</option>
            <option value="completed">Completed</option>
            <option value="cancelled">Cancelled</option>
        </select>

        <button type="submit">Add Donor Appointment</button>
    </form>
</div>

<script>
// Pass PHP donor array to JS
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
            resultsDiv.style.display = 'block';
            matches.forEach(match => {
                const div = document.createElement('div');
                div.classList.add('search-item');
                div.textContent = match.first_name + ' ' + match.last_name;
                div.onclick = function() {
                    searchInput.value = match.first_name + ' ' + match.last_name;
                    hiddenIdInput.value = match.donor_id;
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

// Hide results when clicking outside
document.addEventListener('click', function(e) {
    if (e.target !== searchInput) {
        resultsDiv.style.display = 'none';
    }
});
</script>

<?php include('../../../../includes/footer.php'); ?>