<?php
header('Content-Type: application/json');

// Get parameters
$batchId = $_GET['batch_id'] ?? '';
$scheduleId = $_GET['schedule_id'] ?? '';

if (!$batchId || !$scheduleId) {
    echo json_encode(['success' => false, 'message' => 'Missing parameters']);
    exit;
}

try {
    // First, get the schedule details to check if it's started
    $scheduleResponse = file_get_contents("https://pwxavengers-proxy.pw-avengers.workers.dev/api/batch/{$batchId}/todays-schedule");
    $scheduleData = json_decode($scheduleResponse, true);
    
    if (!$scheduleData || !isset($scheduleData['data'])) {
        echo json_encode(['success' => false, 'message' => 'Failed to load schedule']);
        exit;
    }
    
    // Find the specific lecture
    $lecture = null;
    foreach ($scheduleData['data'] as $item) {
        if ($item['_id'] === $scheduleId) {
            $lecture = $item;
            break;
        }
    }
    
    if (!$lecture) {
        echo json_encode(['success' => false, 'message' => 'Lecture not found']);
        exit;
    }
    
    // Check if lecture has started
    $now = new DateTime();
    $startTime = new DateTime($lecture['startTime']);
    
    if ($now < $startTime && $lecture['status'] !== 'LIVE' && $lecture['status'] !== 'COMPLETED') {
        echo json_encode([
            'success' => false, 
            'message' => 'This class has not started yet. It will start at ' . $startTime->format('h:i A')
        ]);
        exit;
    }
    
    // Get video URL
    $videoResponse = file_get_contents("https://pwxavengers-proxy.pw-avengers.workers.dev/api/url?batch_id={$batchId}&schedule_id={$scheduleId}");
    $videoData = json_decode($videoResponse, true);
    
    if ($videoData && isset($videoData['success']) && $videoData['success']) {
        echo json_encode([
            'success' => true,
            'signed_url' => $videoData['signed_url'],
            'video_id' => $videoData['video_id']
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to get video URL']);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>