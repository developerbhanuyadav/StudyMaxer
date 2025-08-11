<?php
session_start();

// Get batch ID from URL
$batchId = $_GET['batch'] ?? '';

if (!$batchId) {
    header('Location: index.php');
    exit;
}

// Load batch info
$batch = null;
$enrolledBatches = isset($_SESSION['enrolledBatches']) ? $_SESSION['enrolledBatches'] : [];
foreach ($enrolledBatches as $b) {
    if ($b['_id'] === $batchId) {
        $batch = $b;
        break;
    }
}

if (!$batch) {
    header('Location: index.php');
    exit;
}

// Load today's classes
$todaysClasses = [];
try {
    $response = file_get_contents("https://pwxavengers-proxy.pw-avengers.workers.dev/api/batch/{$batchId}/todays-schedule");
    $data = json_decode($response, true);
    if ($data && isset($data['data'])) {
        // Filter only lectures (exclude PDFs)
        $todaysClasses = array_filter($data['data'], function($item) {
            return $item['lectureType'] === 'LIVE' || 
                   (isset($item['videoDetails']) && $item['videoDetails']['status'] === 'Ready');
        });
    }
} catch (Exception $e) {
    $todaysClassesError = 'Failed to load today\'s classes';
}

// Load subjects
$subjects = [];
try {
    $response = file_get_contents("https://api.tejtimes.live/api/pw/details/subject.php?batch_id={$batchId}");
    $data = json_decode($response, true);
    if ($data && isset($data['subjects'])) {
        $subjects = $data['subjects'];
    }
} catch (Exception $e) {
    $subjectsError = 'Failed to load subjects';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Batch Details - Studymaxer</title>
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
        .lecture-container {
            overflow-x: auto;
            white-space: nowrap;
            padding: 10px 0;
        }
        .lecture-card {
            display: inline-block;
            width: 280px;
            height: 200px;
            margin-right: 15px;
            border: none;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
            cursor: pointer;
            background: white;
            position: relative;
        }
        .lecture-card:hover {
            transform: translateY(-3px);
        }
        .lecture-content {
            padding: 15px;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        .lecture-time {
            background: linear-gradient(45deg, #667eea, #764ba2);
            color: white;
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: bold;
            display: inline-block;
        }
        .lecture-status {
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 0.7rem;
            font-weight: bold;
            position: absolute;
            top: 10px;
            right: 10px;
        }
        .status-live {
            background: #dc3545;
            color: white;
        }
        .status-pending {
            background: #ffc107;
            color: #333;
        }
        .status-completed {
            background: #28a745;
            color: white;
        }
        .lecture-title {
            font-size: 0.9rem;
            font-weight: bold;
            color: #333;
            margin: 10px 0;
            line-height: 1.3;
            overflow: hidden;
            text-overflow: ellipsis;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
        }
        .subjects-scroll {
            overflow-x: auto;
            white-space: nowrap;
            padding: 10px 0;
        }
        .subject-card {
            display: inline-block;
            width: 200px;
            margin-right: 15px;
            border: none;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
            cursor: pointer;
        }
        .subject-card:hover {
            transform: translateY(-3px);
        }
        .subject-image {
            height: 120px;
            object-fit: cover;
            border-radius: 10px 10px 0 0;
        }
        .subject-name {
            padding: 10px;
            text-align: center;
            font-weight: bold;
            color: #333;
        }
        .lets-study-btn {
            background: linear-gradient(45deg, #f093fb, #f5576c);
            border: none;
            color: white;
            border-radius: 25px;
            padding: 12px 30px;
            font-weight: bold;
        }
        .lets-study-btn:hover {
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
                    <a href="index.php" class="btn btn-outline-light">
                        <i class="fas fa-home me-2"></i>Back to Home
                    </a>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <div class="container mt-4">
        <div class="row">
            <div class="col-12">
                <!-- Batch Info -->
                <div class="mb-4">
                    <div class="card">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-md-3">
                                    <img src="<?php echo htmlspecialchars($batch['previewImage'] ?? 'https://via.placeholder.com/300x200?text=No+Image'); ?>" 
                                         class="img-fluid rounded" alt="<?php echo htmlspecialchars($batch['name']); ?>">
                                </div>
                                <div class="col-md-9">
                                    <h2><?php echo htmlspecialchars($batch['name']); ?></h2>
                                    <p class="text-muted"><?php echo htmlspecialchars($batch['byName'] ?? ''); ?></p>
                                    <div class="row">
                                        <div class="col-md-4">
                                            <strong><i class="fas fa-language me-2"></i>Language:</strong> <?php echo htmlspecialchars($batch['language']); ?>
                                        </div>
                                        <div class="col-md-4">
                                            <strong><i class="fas fa-calendar me-2"></i>Exam:</strong> <?php echo htmlspecialchars($batch['exam']); ?>
                                        </div>
                                        <div class="col-md-4">
                                            <strong><i class="fas fa-user-graduate me-2"></i>Class:</strong> <?php echo htmlspecialchars($batch['class']); ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Let's Study Button -->
                <div class="text-center mb-4">
                    <button class="btn lets-study-btn" onclick="startStudying()">
                        <i class="fas fa-play me-2"></i>Let's Study
                    </button>
                </div>

                <!-- Today's Classes Section -->
                <div class="mb-5">
                    <h3 class="section-title">
                        <i class="fas fa-calendar-day me-2"></i>Today's Classes
                    </h3>
                    <?php if (isset($todaysClassesError)): ?>
                        <div class="error-message">
                            <i class="fas fa-exclamation-triangle me-2"></i><?php echo htmlspecialchars($todaysClassesError); ?>
                        </div>
                    <?php elseif (empty($todaysClasses)): ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>No classes scheduled for today.
                        </div>
                    <?php else: ?>
                        <div class="lecture-container">
                            <?php foreach ($todaysClasses as $lecture): ?>
                                <?php
                                $startTime = new DateTime($lecture['startTime']);
                                $timeString = $startTime->format('h:i A');
                                
                                $statusClass = 'status-pending';
                                $statusText = 'Pending';
                                
                                if ($lecture['status'] === 'LIVE') {
                                    $statusClass = 'status-live';
                                    $statusText = 'LIVE';
                                } elseif ($lecture['status'] === 'COMPLETED') {
                                    $statusClass = 'status-completed';
                                    $statusText = 'Completed';
                                }
                                ?>
                                <div class="lecture-card" onclick="openLecture('<?php echo $lecture['_id']; ?>')">
                                    <div class="lecture-content">
                                        <div>
                                            <span class="lecture-status <?php echo $statusClass; ?>"><?php echo $statusText; ?></span>
                                            <div class="lecture-time"><?php echo $timeString; ?></div>
                                        </div>
                                        <div class="lecture-title"><?php echo htmlspecialchars($lecture['topic']); ?></div>
                                        <div class="text-muted small">
                                            <i class="fas fa-user me-1"></i><?php echo htmlspecialchars($lecture['subjectId']['name'] ?? 'Subject'); ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Subjects Section -->
                <div class="mb-5">
                    <h3 class="section-title">
                        <i class="fas fa-book me-2"></i>Subjects
                    </h3>
                    <?php if (isset($subjectsError)): ?>
                        <div class="error-message">
                            <i class="fas fa-exclamation-triangle me-2"></i><?php echo htmlspecialchars($subjectsError); ?>
                        </div>
                    <?php elseif (empty($subjects)): ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>No subjects available for this batch.
                        </div>
                    <?php else: ?>
                        <div class="subjects-scroll">
                            <?php foreach ($subjects as $subject): ?>
                                <div class="subject-card" onclick="openSubject('<?php echo $subject['subject_id']; ?>')">
                                    <img src="<?php echo htmlspecialchars($subject['subject_image']); ?>" 
                                         class="subject-image" alt="<?php echo htmlspecialchars($subject['subject_name']); ?>">
                                    <div class="subject-name"><?php echo htmlspecialchars($subject['subject_name']); ?></div>
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

        // Open subject
        function openSubject(subjectId) {
            window.location.href = `subject.php?batch=<?php echo $batchId; ?>&subject=${subjectId}`;
        }

        // Start studying (redirect to first subject or show message)
        function startStudying() {
            <?php if (!empty($subjects)): ?>
                openSubject('<?php echo $subjects[0]['subject_id']; ?>');
            <?php else: ?>
                alert('Choose a subject or today\'s class to start studying!');
            <?php endif; ?>
        }
    </script>
</body>
</html>