<?php
session_start();

// Get parameters from URL
$batchId = $_GET['batch'] ?? '';
$subjectId = $_GET['subject'] ?? '';

if (!$batchId || !$subjectId) {
    header('Location: index.php');
    exit;
}

// Load subject info
$subject = null;
try {
    $response = file_get_contents("https://api.tejtimes.live/api/pw/details/subject.php?batch_id={$batchId}");
    $data = json_decode($response, true);
    
    if ($data && isset($data['subjects'])) {
        foreach ($data['subjects'] as $s) {
            if ($s['subject_id'] === $subjectId) {
                $subject = $s;
                break;
            }
        }
    }
} catch (Exception $e) {
    $subjectError = 'Failed to load subject information';
}

// Load chapters
$chapters = [];
try {
    $response = file_get_contents("https://pwxavengers-proxy.pw-avengers.workers.dev/api/batch/{$batchId}/subject/{$subjectId}/topics");
    $data = json_decode($response, true);
    
    if ($data && isset($data['data'])) {
        $chapters = $data['data'];
    }
} catch (Exception $e) {
    $chaptersError = 'Failed to load chapters';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subject - Studymaxer</title>
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
        .chapter-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
            margin-bottom: 20px;
            cursor: pointer;
        }
        .chapter-card:hover {
            transform: translateY(-5px);
        }
        .chapter-stats {
            display: flex;
            gap: 15px;
            margin-top: 10px;
        }
        .stat-item {
            display: flex;
            align-items: center;
            gap: 5px;
            color: #666;
            font-size: 0.9rem;
        }
        .stat-icon {
            width: 20px;
            text-align: center;
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
                <!-- Subject Info -->
                <div class="mb-4">
                    <?php if (isset($subjectError)): ?>
                        <div class="error-message">
                            <i class="fas fa-exclamation-triangle me-2"></i><?php echo htmlspecialchars($subjectError); ?>
                        </div>
                    <?php elseif ($subject): ?>
                        <div class="card">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col-md-3">
                                        <img src="<?php echo htmlspecialchars($subject['subject_image']); ?>" 
                                             class="img-fluid rounded" alt="<?php echo htmlspecialchars($subject['subject_name']); ?>">
                                    </div>
                                    <div class="col-md-9">
                                        <h2><?php echo htmlspecialchars($subject['subject_name']); ?></h2>
                                        <p class="text-muted">Explore all chapters and topics in this subject</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Chapters Section -->
                <div class="mb-5">
                    <h3 class="section-title">
                        <i class="fas fa-list me-2"></i>Chapters
                    </h3>
                    <?php if (isset($chaptersError)): ?>
                        <div class="error-message">
                            <i class="fas fa-exclamation-triangle me-2"></i><?php echo htmlspecialchars($chaptersError); ?>
                        </div>
                    <?php elseif (empty($chapters)): ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>No chapters available for this subject.
                        </div>
                    <?php else: ?>
                        <div class="row">
                            <?php foreach ($chapters as $chapter): ?>
                                <div class="col-md-6 col-lg-4">
                                    <div class="card chapter-card" onclick="openChapter('<?php echo $chapter['_id']; ?>')">
                                        <div class="card-body">
                                            <h5 class="card-title"><?php echo htmlspecialchars($chapter['name']); ?></h5>
                                            <div class="chapter-stats">
                                                <div class="stat-item">
                                                    <i class="fas fa-video stat-icon"></i>
                                                    <span><?php echo $chapter['videos'] ?? 0; ?> Videos</span>
                                                </div>
                                                <div class="stat-item">
                                                    <i class="fas fa-file-alt stat-icon"></i>
                                                    <span><?php echo $chapter['notes'] ?? 0; ?> Notes</span>
                                                </div>
                                                <div class="stat-item">
                                                    <i class="fas fa-tasks stat-icon"></i>
                                                    <span><?php echo $chapter['exercises'] ?? 0; ?> Exercises</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Open chapter
        function openChapter(topicId) {
            window.location.href = `chapter.php?batch=<?php echo $batchId; ?>&subject=<?php echo $subjectId; ?>&topic=${topicId}`;
        }

        // Go back
        function goBack() {
            window.history.back();
        }
    </script>
</body>
</html>