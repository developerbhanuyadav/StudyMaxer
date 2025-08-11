<?php
session_start();

// Get parameters from URL
$batchId = $_GET['batch'] ?? '';
$subjectId = $_GET['subject'] ?? '';
$topicId = $_GET['topic'] ?? '';

if (!$batchId || !$subjectId || !$topicId) {
    header('Location: index.php');
    exit;
}

// Load chapter info
$chapter = null;
try {
    $response = file_get_contents("https://pwxavengers-proxy.pw-avengers.workers.dev/api/batch/{$batchId}/subject/{$subjectId}/topics");
    $data = json_decode($response, true);
    
    if ($data && isset($data['data'])) {
        foreach ($data['data'] as $c) {
            if ($c['_id'] === $topicId) {
                $chapter = $c;
                break;
            }
        }
    }
} catch (Exception $e) {
    $chapterError = 'Failed to load chapter information';
}

// Load lectures
$lectures = [];
try {
    $response = file_get_contents("https://pwxavengers-proxy.pw-avengers.workers.dev/api/batch/{$batchId}/subject/{$subjectId}/topic/{$topicId}/all-contents?type=vidoes");
    $data = json_decode($response, true);
    
    if ($data && isset($data['data'])) {
        $lectures = $data['data'];
    }
} catch (Exception $e) {
    $lecturesError = 'Failed to load lectures';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chapter - Studymaxer</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 1rem 0;
        }
        .section-title {
            color: #333;
            margin-bottom: 20px;
            font-weight: bold;
        }
        .lecture-card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
            margin-bottom: 15px;
            cursor: pointer;
        }
        .lecture-card:hover {
            transform: translateY(-2px);
        }
        .lecture-duration {
            background: linear-gradient(45deg, #667eea, #764ba2);
            color: white;
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: bold;
        }
        .lecture-status {
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 0.7rem;
            font-weight: bold;
        }
        .status-completed {
            background: #28a745;
            color: white;
        }
        .status-pending {
            background: #ffc107;
            color: #333;
        }
        .back-btn {
            background: linear-gradient(45deg, #667eea, #764ba2);
            border: none;
            color: white;
            border-radius: 25px;
            padding: 10px 25px;
        }
        .back-btn:hover {
            color: white;
            transform: scale(1.05);
        }
        .nav-tabs .nav-link {
            border: none;
            color: #666;
            font-weight: 500;
            padding: 12px 20px;
        }
        .nav-tabs .nav-link.active {
            background: linear-gradient(45deg, #667eea, #764ba2);
            color: white;
            border-radius: 25px;
        }
        .nav-tabs .nav-link:hover {
            border: none;
            color: #667eea;
        }
        .coming-soon {
            text-align: center;
            padding: 50px;
            color: #666;
        }
        .lecture-date {
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 10px;
        }
        .error-message {
            text-align: center;
            padding: 30px;
            color: #dc3545;
            background: #f8d7da;
            border-radius: 10px;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h1 class="mb-0"><i class="fas fa-graduation-cap me-2"></i>Studymaxer</h1>
                </div>
                <div class="col-md-6 text-end">
                    <button class="btn back-btn" onclick="goBack()">
                        <i class="fas fa-arrow-left me-2"></i>Back
                    </button>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <div class="container mt-4">
        <div class="row">
            <div class="col-12">
                <!-- Chapter Info -->
                <div class="mb-4">
                    <?php if (isset($chapterError)): ?>
                        <div class="error-message">
                            <i class="fas fa-exclamation-triangle me-2"></i><?php echo htmlspecialchars($chapterError); ?>
                        </div>
                    <?php elseif ($chapter): ?>
                        <div class="card">
                            <div class="card-body">
                                <h2><?php echo htmlspecialchars($chapter['name']); ?></h2>
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="text-center">
                                            <i class="fas fa-video fa-2x text-primary mb-2"></i>
                                            <div><strong><?php echo $chapter['videos'] ?? 0; ?></strong></div>
                                            <div class="text-muted">Videos</div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="text-center">
                                            <i class="fas fa-file-alt fa-2x text-success mb-2"></i>
                                            <div><strong><?php echo $chapter['notes'] ?? 0; ?></strong></div>
                                            <div class="text-muted">Notes</div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="text-center">
                                            <i class="fas fa-tasks fa-2x text-warning mb-2"></i>
                                            <div><strong><?php echo $chapter['exercises'] ?? 0; ?></strong></div>
                                            <div class="text-muted">Exercises</div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="text-center">
                                            <i class="fas fa-clock fa-2x text-info mb-2"></i>
                                            <div><strong><?php echo $chapter['lectureVideos'] ?? 0; ?></strong></div>
                                            <div class="text-muted">Lectures</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Tabs Section -->
                <div class="mb-5">
                    <ul class="nav nav-tabs" id="contentTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="lectures-tab" data-bs-toggle="tab" 
                                    data-bs-target="#lectures" type="button" role="tab">
                                <i class="fas fa-video me-2"></i>Lectures
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="notes-tab" data-bs-toggle="tab" 
                                    data-bs-target="#notes" type="button" role="tab">
                                <i class="fas fa-file-alt me-2"></i>Notes
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="dpp-tab" data-bs-toggle="tab" 
                                    data-bs-target="#dpp" type="button" role="tab">
                                <i class="fas fa-tasks me-2"></i>DPP
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content" id="contentTabsContent">
                        <!-- Lectures Tab -->
                        <div class="tab-pane fade show active" id="lectures" role="tabpanel">
                            <?php if (isset($lecturesError)): ?>
                                <div class="error-message">
                                    <i class="fas fa-exclamation-triangle me-2"></i><?php echo htmlspecialchars($lecturesError); ?>
                                </div>
                            <?php elseif (empty($lectures)): ?>
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle me-2"></i>No lectures available for this chapter.
                                </div>
                            <?php else: ?>
                                <div class="row">
                                    <?php foreach ($lectures as $lecture): ?>
                                        <?php
                                        $date = new DateTime($lecture['date']);
                                        $dateString = $date->format('F j, Y');
                                        
                                        $duration = $lecture['videoDetails']['duration'] ?? '00:00';
                                        
                                        $statusClass = 'status-pending';
                                        $statusText = 'Pending';
                                        
                                        if ($lecture['status'] === 'COMPLETED') {
                                            $statusClass = 'status-completed';
                                            $statusText = 'Completed';
                                        }
                                        ?>
                                        <div class="col-md-6 col-lg-4">
                                            <div class="card lecture-card" onclick="openLecture('<?php echo $lecture['_id']; ?>')">
                                                <div class="card-body">
                                                    <div class="lecture-date">
                                                        <i class="fas fa-calendar me-2"></i><?php echo $dateString; ?>
                                                    </div>
                                                    <h6 class="card-title"><?php echo htmlspecialchars($lecture['topic']); ?></h6>
                                                    <div class="d-flex justify-content-between align-items-center mt-3">
                                                        <span class="lecture-duration"><?php echo $duration; ?></span>
                                                        <span class="lecture-status <?php echo $statusClass; ?>"><?php echo $statusText; ?></span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Notes Tab -->
                        <div class="tab-pane fade" id="notes" role="tabpanel">
                            <div class="coming-soon">
                                <i class="fas fa-file-alt fa-3x mb-3 text-muted"></i>
                                <h4>Notes Coming Soon</h4>
                                <p>Notes section will be available soon.</p>
                            </div>
                        </div>

                        <!-- DPP Tab -->
                        <div class="tab-pane fade" id="dpp" role="tabpanel">
                            <div class="coming-soon">
                                <i class="fas fa-tasks fa-3x mb-3 text-muted"></i>
                                <h4>DPP Coming Soon</h4>
                                <p>Daily Practice Problems will be available soon.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Open lecture
        async function openLecture(scheduleId) {
            try {
                const response = await fetch(`get_video_url.php?batch_id=<?php echo $batchId; ?>&schedule_id=${scheduleId}`);
                const data = await response.json();
                
                if (data.success) {
                    window.location.href = `play.php?encrypted=${data.signed_url}&v=${data.video_id}`;
                } else {
                    if (data.message && data.message.includes('not started')) {
                        alert('This class has not started yet. Please wait for the scheduled time.');
                    } else {
                        alert('Unable to load lecture. Please try again.');
                    }
                }
            } catch (error) {
                console.error('Error opening lecture:', error);
                alert('Error opening lecture. Please try again.');
            }
        }

        // Go back
        function goBack() {
            window.history.back();
        }
    </script>
</body>
</html>