<?php
session_start();
header('Content-Type: application/json');

// Get POST data
$input = json_decode(file_get_contents('php://input'), true);
$batchId = $input['batchId'] ?? null;

if (!$batchId) {
    echo json_encode(['success' => false, 'message' => 'Batch ID is required']);
    exit;
}

// Load all batches from API
$allBatches = [];
try {
    $response = file_get_contents('https://pwxavengers-proxy.pw-avengers.workers.dev/api/batches?page=1&limit=3000');
    $data = json_decode($response, true);
    if ($data && isset($data['batches'])) {
        $allBatches = $data['batches'];
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Failed to load batches']);
    exit;
}

// Find the batch
$batch = null;
foreach ($allBatches as $b) {
    if ($b['_id'] === $batchId) {
        $batch = $b;
        break;
    }
}

if (!$batch) {
    echo json_encode(['success' => false, 'message' => 'Batch not found']);
    exit;
}

// Get current enrolled batches from JSON file
$enrolledBatches = [];
if (file_exists('batches.json')) {
    $enrolledBatches = json_decode(file_get_contents('batches.json'), true) ?: [];
}

// Check if already enrolled
foreach ($enrolledBatches as $enrolled) {
    if ($enrolled['_id'] === $batchId) {
        echo json_encode(['success' => false, 'message' => 'You are already enrolled in this batch']);
        exit;
    }
}

// Add to enrolled batches
$enrolledBatches[] = $batch;
file_put_contents('batches.json', json_encode($enrolledBatches, JSON_PRETTY_PRINT));

echo json_encode(['success' => true, 'message' => 'Successfully enrolled in batch']);
?>