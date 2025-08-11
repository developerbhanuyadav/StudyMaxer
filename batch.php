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

        .lecture-container {
            overflow-x: auto;
            white-space: nowrap;
            padding: 15px 0;
            scrollbar-width: thin;
            scrollbar-color: #667eea #f1f1f1;
        }

        .lecture-container::-webkit-scrollbar {
            height: 8px;
        }

        .lecture-container::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 4px;
        }

        .lecture-container::-webkit-scrollbar-thumb {
            background: var(--accent-gradient);
            border-radius: 4px;
        }

        .lecture-card {
            display: inline-block;
            width: 300px;
            height: 220px;
            margin-right: 20px;
            border: none;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-light);
            transition: var(--transition);
            cursor: pointer;
            background: white;
            position: relative;
            overflow: hidden;
        }

        .lecture-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--accent-gradient);
        }

        .lecture-card:hover {
            transform: translateY(-8px);
            box-shadow: var(--shadow-medium);
        }

        .lecture-content {
            padding: 20px;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .lecture-time {
            background: var(--accent-gradient);
            color: white;
            padding: 8px 15px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            display: inline-block;
            box-shadow: 0 2px 10px rgba(102, 126, 234, 0.3);
        }

        .lecture-status {
            padding: 6px 12px;
            border-radius: 15px;
            font-size: 0.75rem;
            font-weight: 600;
            position: absolute;
            top: 15px;
            right: 15px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .status-live {
            background: linear-gradient(45deg, #ff6b6b, #ee5a24);
            color: white;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% { box-shadow: 0 0 0 0 rgba(255, 107, 107, 0.7); }
            70% { box-shadow: 0 0 0 10px rgba(255, 107, 107, 0); }
            100% { box-shadow: 0 0 0 0 rgba(255, 107, 107, 0); }
        }

        .status-pending {
            background: linear-gradient(45deg, #ffa726, #ff9800);
            color: white;
        }

        .status-completed {
            background: linear-gradient(45deg, #66bb6a, #4caf50);
            color: white;
        }

        .lecture-title {
            font-size: 1rem;
            font-weight: 600;
            color: #2c3e50;
            margin: 15px 0;
            line-height: 1.4;
            overflow: hidden;
            text-overflow: ellipsis;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
        }

        .subjects-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
            padding: 15px 0;
        }

        .subject-card {
            border: none;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-light);
            transition: var(--transition);
            cursor: pointer;
            background: white;
            overflow: hidden;
            position: relative;
        }

        .subject-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--secondary-gradient);
        }

        .subject-card:hover {
            transform: translateY(-8px);
            box-shadow: var(--shadow-medium);
        }

        .subject-image {
            height: 140px;
            object-fit: cover;
            width: 100%;
            transition: var(--transition);
        }

        .subject-card:hover .subject-image {
            transform: scale(1.05);
        }

        .subject-name {
            padding: 15px;
            text-align: center;
            font-weight: 600;
            color: #2c3e50;
            font-size: 1.1rem;
        }

        .lets-study-btn {
            background: var(--secondary-gradient);
            border: none;
            color: white;
            border-radius: 30px;
            padding: 15px 40px;
            font-weight: 600;
            font-size: 1.1rem;
            box-shadow: 0 4px 20px rgba(240, 147, 251, 0.4);
            transition: var(--transition);
        }

        .lets-study-btn:hover {
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 8px 30px rgba(240, 147, 251, 0.6);
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

        .card {
            border: none;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-light);
            background: white;
        }

        .alert {
            border: none;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-light);
        }

        .btn-outline-light {
            border: 2px solid rgba(255,255,255,0.3);
            border-radius: 25px;
            padding: 10px 25px;
            font-weight: 500;
            transition: var(--transition);
        }

        .btn-outline-light:hover {
            background: rgba(255,255,255,0.1);
            border-color: rgba(255,255,255,0.5);
            transform: translateY(-1px);
        }

        .text-muted {
            color: #7f8c8d !important;
        }

        .img-fluid {
            border-radius: var(--border-radius);
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
                <!-- Let's Study Button -->
                <div class="text-center mb-5">
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
                                // Fix the time format - convert to proper timezone and format
                                $startTime = new DateTime($lecture['startTime']);
                                $startTime->setTimezone(new DateTimeZone('Asia/Kolkata')); // Set to Indian timezone
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
                        <div class="subjects-grid">
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