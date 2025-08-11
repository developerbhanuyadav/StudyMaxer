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
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --secondary-gradient: linear-gradient(45deg, #f093fb, #f5576c);
            --accent-gradient: linear-gradient(45deg, #667eea, #764ba2);
            --shadow-light: 0 4px 20px rgba(0,0,0,0.08);
            --shadow-medium: 0 8px 30px rgba(0,0,0,0.12);
            --border-radius: 15px;
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .header {
            background: var(--primary-gradient);
            color: white;
            padding: 1.5rem 0;
            box-shadow: var(--shadow-medium);
            position: relative;
            overflow: hidden;
        }

        .header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="25" cy="25" r="1" fill="white" opacity="0.1"/><circle cx="75" cy="75" r="1" fill="white" opacity="0.1"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
            opacity: 0.3;
        }

        .header .container {
            position: relative;
            z-index: 1;
        }

        .section-title {
            color: #2c3e50;
            margin-bottom: 25px;
            font-weight: 700;
            font-size: 1.8rem;
            position: relative;
            padding-left: 20px;
        }

        .section-title::before {
            content: '';
            position: absolute;
            left: 0;
            top: 50%;
            transform: translateY(-50%);
            width: 4px;
            height: 30px;
            background: var(--accent-gradient);
            border-radius: 2px;
        }

        .chapter-card {
            border: none;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-light);
            transition: var(--transition);
            margin-bottom: 20px;
            cursor: pointer;
            background: white;
            overflow: hidden;
            position: relative;
        }

        .chapter-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--secondary-gradient);
        }

        .chapter-card:hover {
            transform: translateY(-8px);
            box-shadow: var(--shadow-medium);
        }

        .chapter-stats {
            display: flex;
            gap: 15px;
            margin-top: 15px;
            flex-wrap: wrap;
        }

        .stat-item {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #7f8c8d;
            font-size: 0.9rem;
            font-weight: 500;
            padding: 8px 12px;
            background: #f8f9fa;
            border-radius: 20px;
            transition: var(--transition);
        }

        .stat-item:hover {
            background: var(--accent-gradient);
            color: white;
            transform: translateY(-2px);
        }

        .stat-icon {
            width: 20px;
            text-align: center;
            font-size: 1rem;
        }

        .back-btn {
            background: var(--accent-gradient);
            border: none;
            color: white;
            border-radius: 25px;
            padding: 12px 30px;
            font-weight: 600;
            box-shadow: 0 4px 20px rgba(102, 126, 234, 0.3);
            transition: var(--transition);
        }

        .back-btn:hover {
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 8px 30px rgba(102, 126, 234, 0.4);
        }

        .error-message {
            text-align: center;
            padding: 30px;
            color: #e74c3c;
            background: linear-gradient(45deg, #ffebee, #ffcdd2);
            border-radius: var(--border-radius);
            margin: 20px 0;
            border-left: 4px solid #e74c3c;
        }

        .alert {
            border: none;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-light);
        }

        .card-title {
            color: #2c3e50;
            font-weight: 600;
            font-size: 1.2rem;
            margin-bottom: 10px;
        }

        .text-muted {
            color: #7f8c8d !important;
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
        function goBack() {
            window.history.back();
        }

        function openChapter(chapterId) {
            window.location.href = `chapter.php?batch=<?php echo $batchId; ?>&subject=<?php echo $subjectId; ?>&chapter=${chapterId}`;
        }
    </script>
</body>
</html>