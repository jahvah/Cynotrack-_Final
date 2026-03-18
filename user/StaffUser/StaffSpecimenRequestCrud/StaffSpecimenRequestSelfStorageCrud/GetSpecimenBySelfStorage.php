<?php
include('../../../../includes/config.php');

$storage_user_id = intval($_GET['storage_user_id'] ?? 0);

if ($storage_user_id <= 0) {
    echo "<option value=''>Invalid user</option>";
    exit();
}

$query = mysqli_query($conn, "
    SELECT specimen_id, unique_code, quantity
    FROM specimens
    WHERE specimen_owner_type = 'storage'
      AND specimen_owner_id = '$storage_user_id'
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