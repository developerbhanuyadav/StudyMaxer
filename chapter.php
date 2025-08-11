<?php
session_start();

// Get parameters from URL
$batchId = $_GET['batch'] ?? '';
$subjectId = $_GET['subject'] ?? '';
$chapterId = $_GET['chapter'] ?? '';

if (!$batchId || !$subjectId || !$chapterId) {
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
            if ($c['_id'] === $chapterId) {
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
    $response = file_get_contents("https://pwxavengers-proxy.pw-avengers.workers.dev/api/batch/{$batchId}/subject/{$subjectId}/topic/{$chapterId}/lectures");
    $data = json_decode($response, true);
    
    if ($data && isset($data['data'])) {
        $lectures = $data['data'];
    }
} catch (Exception $e) {
    $lecturesError = 'Failed to load lectures';
}

// Load notes
$notes = [];
try {
    $response = file_get_contents("https://pwxavengers-proxy.pw-avengers.workers.dev/api/batch/{$batchId}/subject/{$subjectId}/topic/{$chapterId}/notes");
    $data = json_decode($response, true);
    
    if ($data && isset($data['data'])) {
        $notes = $data['data'];
    }
} catch (Exception $e) {
    $notesError = 'Failed to load notes';
}

// Load DPP
$dpp = [];
try {
    $response = file_get_contents("https://pwxavengers-proxy.pw-avengers.workers.dev/api/batch/{$batchId}/subject/{$subjectId}/topic/{$chapterId}/dpp");
    $data = json_decode($response, true);
    
    if ($data && isset($data['data'])) {
        $dpp = $data['data'];
    }
} catch (Exception $e) {
    $dppError = 'Failed to load DPP';
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

        .nav-tabs {
            border: none;
            background: white;
            border-radius: var(--border-radius);
            padding: 5px;
            box-shadow: var(--shadow-light);
            margin-bottom: 30px;
        }

        .nav-tabs .nav-link {
            border: none;
            color: #7f8c8d;
            font-weight: 600;
            padding: 15px 25px;
            border-radius: 20px;
            transition: var(--transition);
            margin: 0 5px;
        }

        .nav-tabs .nav-link.active {
            background: var(--accent-gradient);
            color: white;
            box-shadow: 0 4px 20px rgba(102, 126, 234, 0.3);
        }

        .nav-tabs .nav-link:hover {
            border: none;
            color: #667eea;
            transform: translateY(-2px);
        }

        .lecture-card, .note-card, .dpp-card {
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

        .lecture-card::before, .note-card::before, .dpp-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
        }

        .lecture-card::before {
            background: linear-gradient(45deg, #667eea, #764ba2);
        }

        .note-card::before {
            background: linear-gradient(45deg, #66bb6a, #4caf50);
        }

        .dpp-card::before {
            background: linear-gradient(45deg, #ffa726, #ff9800);
        }

        .lecture-card:hover, .note-card:hover, .dpp-card:hover {
            transform: translateY(-8px);
            box-shadow: var(--shadow-medium);
        }

        .lecture-date, .note-date, .dpp-date {
            color: #7f8c8d;
            font-size: 0.9rem;
            margin-bottom: 10px;
            font-weight: 500;
        }

        .lecture-title, .note-title, .dpp-title {
            color: #2c3e50;
            font-weight: 600;
            font-size: 1.1rem;
            margin-bottom: 10px;
            line-height: 1.4;
        }

        .lecture-duration {
            color: #7f8c8d;
            font-size: 0.9rem;
            font-weight: 500;
        }

        .coming-soon {
            text-align: center;
            padding: 50px;
            color: #7f8c8d;
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-light);
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
                                        
                                        $duration = '';
                                        if (isset($lecture['duration'])) {
                                            $minutes = round($lecture['duration'] / 60);
                                            $duration = $minutes . ' min';
                                        }
                                        ?>
                                        <div class="col-md-6 col-lg-4">
                                            <div class="card lecture-card" onclick="openLecture('<?php echo $lecture['_id']; ?>')">
                                                <div class="card-body">
                                                    <div class="lecture-date">
                                                        <i class="fas fa-calendar me-1"></i><?php echo $dateString; ?>
                                                    </div>
                                                    <div class="lecture-title"><?php echo htmlspecialchars($lecture['title']); ?></div>
                                                    <?php if ($duration): ?>
                                                        <div class="lecture-duration">
                                                            <i class="fas fa-clock me-1"></i><?php echo $duration; ?>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Notes Tab -->
                        <div class="tab-pane fade" id="notes" role="tabpanel">
                            <?php if (isset($notesError)): ?>
                                <div class="error-message">
                                    <i class="fas fa-exclamation-triangle me-2"></i><?php echo htmlspecialchars($notesError); ?>
                                </div>
                            <?php elseif (empty($notes)): ?>
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle me-2"></i>No notes available for this chapter.
                                </div>
                            <?php else: ?>
                                <div class="row">
                                    <?php foreach ($notes as $note): ?>
                                        <?php
                                        $date = new DateTime($note['date']);
                                        $dateString = $date->format('F j, Y');
                                        ?>
                                        <div class="col-md-6 col-lg-4">
                                            <div class="card note-card" onclick="openNote('<?php echo $note['_id']; ?>')">
                                                <div class="card-body">
                                                    <div class="note-date">
                                                        <i class="fas fa-calendar me-1"></i><?php echo $dateString; ?>
                                                    </div>
                                                    <div class="note-title"><?php echo htmlspecialchars($note['title']); ?></div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- DPP Tab -->
                        <div class="tab-pane fade" id="dpp" role="tabpanel">
                            <?php if (isset($dppError)): ?>
                                <div class="error-message">
                                    <i class="fas fa-exclamation-triangle me-2"></i><?php echo htmlspecialchars($dppError); ?>
                                </div>
                            <?php elseif (empty($dpp)): ?>
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle me-2"></i>No DPP available for this chapter.
                                </div>
                            <?php else: ?>
                                <div class="row">
                                    <?php foreach ($dpp as $d): ?>
                                        <?php
                                        $date = new DateTime($d['date']);
                                        $dateString = $date->format('F j, Y');
                                        ?>
                                        <div class="col-md-6 col-lg-4">
                                            <div class="card dpp-card" onclick="openDPP('<?php echo $d['_id']; ?>')">
                                                <div class="card-body">
                                                    <div class="dpp-date">
                                                        <i class="fas fa-calendar me-1"></i><?php echo $dateString; ?>
                                                    </div>
                                                    <div class="dpp-title"><?php echo htmlspecialchars($d['title']); ?></div>
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
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function goBack() {
            window.history.back();
        }

        function openLecture(lectureId) {
            // Implement lecture opening logic
            console.log('Opening lecture:', lectureId);
        }

        function openNote(noteId) {
            // Implement note opening logic
            console.log('Opening note:', noteId);
        }

        function openDPP(dppId) {
            // Implement DPP opening logic
            console.log('Opening DPP:', dppId);
        }
    </script>
</body>
</html>