<?php
require_once __DIR__ . '/db/db.php';

$database = new Database();
$conn = $database->connect();

if ($conn) {
    echo "✅ Database connection successful!";
} else {
    echo "❌ Failed to connect to the database.";
}
?>
