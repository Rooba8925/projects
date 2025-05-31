<?php
require 'config.php'; // ✅ Include database connection

// ✅ Ensure $conn is set before using it
if (isset($conn) && $conn instanceof mysqli) {
    echo "Database connected successfully!";
} else {
    echo "Database connection failed.";
}
?>
