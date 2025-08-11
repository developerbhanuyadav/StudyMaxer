<?php
// Test script to verify batches.json reading
echo "<h2>Testing Batches Display</h2>";

// Check if batches.json exists
if (file_exists('batches.json')) {
    echo "<p>✅ batches.json file exists</p>";
    
    // Read the content
    $content = file_get_contents('batches.json');
    echo "<p>File content: " . htmlspecialchars($content) . "</p>";
    
    // Decode JSON
    $batches = json_decode($content, true);
    if ($batches === null) {
        echo "<p>❌ Error decoding JSON</p>";
        echo "<p>JSON Error: " . json_last_error_msg() . "</p>";
    } else {
        echo "<p>✅ JSON decoded successfully</p>";
        echo "<p>Number of batches: " . count($batches) . "</p>";
        
        if (!empty($batches)) {
            echo "<h3>Batches found:</h3>";
            foreach ($batches as $index => $batch) {
                echo "<div style='border: 1px solid #ccc; padding: 10px; margin: 10px 0;'>";
                echo "<strong>Batch " . ($index + 1) . ":</strong><br>";
                echo "ID: " . htmlspecialchars($batch['_id'] ?? 'N/A') . "<br>";
                echo "Name: " . htmlspecialchars($batch['name'] ?? 'N/A') . "<br>";
                echo "Language: " . htmlspecialchars($batch['language'] ?? 'N/A') . "<br>";
                echo "Exam: " . htmlspecialchars($batch['exam'] ?? 'N/A') . "<br>";
                echo "Class: " . htmlspecialchars($batch['class'] ?? 'N/A') . "<br>";
                echo "Image: " . htmlspecialchars($batch['previewImage'] ?? 'N/A') . "<br>";
                echo "</div>";
            }
        } else {
            echo "<p>No batches found in the array</p>";
        }
    }
} else {
    echo "<p>❌ batches.json file does not exist</p>";
}

// Test the same logic as index.php
echo "<h2>Testing Index.php Logic</h2>";
$enrolledBatches = [];
if (file_exists('batches.json')) {
    $enrolledBatches = json_decode(file_get_contents('batches.json'), true) ?: [];
}

echo "<p>Enrolled batches count: " . count($enrolledBatches) . "</p>";
if (empty($enrolledBatches)) {
    echo "<p>No enrolled batches found</p>";
} else {
    echo "<p>✅ Enrolled batches found!</p>";
}
?>