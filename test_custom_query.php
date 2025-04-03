<?php
require_once __DIR__ . '/config/Database.php';
require_once __DIR__ . '/models/Po.php';

// Function to log results to a file
function logToFile($message) {
    $logFile = __DIR__ . '/custom_query_test.log';
    file_put_contents($logFile, $message . PHP_EOL, FILE_APPEND);
}

// Clear log file
file_put_contents(__DIR__ . '/custom_query_test.log', "=== Custom Query Test Results ===\n" . date('Y-m-d H:i:s') . "\n\n");

// Test the custom_query method
try {
    // Initialize database connection and Po model
    $db = new Database();
    $conn = $db->getConnection();
    $po = new Po($conn);
    
    // Simple test query to get all POs
    $query = "SELECT * FROM po LIMIT 5";
    logToFile("Running query: " . $query);
    $result = $po->custom_query($query);
    
    logToFile("Test custom_query method - Result count: " . count($result));
    logToFile(print_r($result, true));
    
    // Test with parameters
    $query = "SELECT * FROM po WHERE id = :id";
    $params = [':id' => 1]; // Assuming there's a PO with ID 1
    logToFile("\nRunning query with parameters: " . $query);
    logToFile("Parameters: " . print_r($params, true));
    $result = $po->custom_query($query, $params);
    
    logToFile("Test custom_query method with parameters - Result count: " . count($result));
    logToFile(print_r($result, true));
    
    logToFile("\nTest completed successfully!");
    echo "Test completed successfully! Check custom_query_test.log for results.";
} catch (Exception $e) {
    logToFile("\nError: " . $e->getMessage());
    echo "Error: " . $e->getMessage() . ". Check custom_query_test.log for details.";
}
?>
