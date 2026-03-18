<?php
include('../../../../includes/config.php');

$donor_id = intval($_GET['donor_id'] ?? 0);

if ($donor_id <= 0) {
    echo "<option value=''>Invalid donor</option>";
    exit();
}

$query = mysqli_query($conn, "
    SELECT specimen_id, unique_code, quantity
    FROM specimens
    WHERE specimen_owner_type = 'donor'
      AND specimen_owner_id = '$donor_id'
      AND status = 'stored'
      AND quantity > 0
");

if (mysqli_num_rows($query) > 0) {
    while ($row = mysqli_fetch_assoc($query)) {
        echo "<option value='{$row['specimen_id']}'>
                {$row['unique_code']} (Available: {$row['quantity']})
              </option>";
    }
} else {
    echo "<option value=''>No specimens available</option>";
}
?>