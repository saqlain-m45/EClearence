<?php
require_once 'config/db.php';

echo "<h1>System Setup</h1>";

try {
    // 1. Run Schema
    $schemaUser = file_get_contents('../database/schema.sql');
    if ($schemaUser) {
        // Split by semicolon to run multiple queries
        $queries = explode(';', $schemaUser);
        foreach ($queries as $query) {
            $query = trim($query);
            if (!empty($query)) {
                $conn->exec($query);
            }
        }
        echo "<p style='color:green'>✅ Database Schema Imported Successfully.</p>";
    } else {
        echo "<p style='color:red'>❌ Error: schema.sql not found.</p>";
    }

    // 2. Run Seeder
    // Capture output of seed.php
    ob_start();
    include 'seed.php';
    $seedOutput = ob_get_clean();
    
    echo "<p style='color:green'>✅ " . nl2br($seedOutput) . "</p>";

    echo "<h3>Setup Completed! <a href='../frontend/index.html'>Go to Login</a></h3>";

} catch (PDOException $e) {
    echo "<p style='color:red'>❌ Error: " . $e->getMessage() . "</p>";
}
?>
