<?php
session_start();

// Handle login
if ($_POST['action'] ?? '' === 'login') {
    $password = $_POST['password'] ?? '';
    if ($password === 'admin123') {
        $_SESSION['admin_logged_in'] = true;
        header('Location: admin.php');
        exit;
    } else {
        $error = 'Incorrect password!';
    }
}

// Handle logout
if ($_GET['logout'] ?? '' === '1') {
    unset($_SESSION['admin_logged_in']);
    header('Location: admin.php');
    exit;
}

// Handle batch removal
if ($_GET['remove_batch'] ?? '' !== '') {
    $batchIdToRemove = $_GET['remove_batch'];
    $enrolledBatches = [];
    if (file_exists('../batches.json')) {
        $enrolledBatches = json_decode(file_get_contents('../batches.json'), true) ?: [];
    }
    
    foreach ($enrolledBatches as $key => $batch) {
        if ($batch['_id'] === $batchIdToRemove) {
            unset($enrolledBatches[$key]);
            $enrolledBatches = array_values($enrolledBatches); // Re-index array
            file_put_contents('../batches.json', json_encode($enrolledBatches, JSON_PRETTY_PRINT));
            $success = 'Batch removed successfully!';
            break;
        }
    }
    
    header('Location: admin.php');
    exit;
}

// Handle manual batch addition
if ($_POST['action'] ?? '' === 'add_manual_batch') {
    // Load current enrolled batches from JSON file
    $enrolledBatches = [];
    if (file_exists('../batches.json')) {
        $enrolledBatches = json_decode(file_get_contents('../batches.json'), true) ?: [];
    }
    
    // Get form data
    $batchName = $_POST['batch_name'] ?? '';
    $batchLanguage = $_POST['batch_language'] ?? '';
    $batchExam = $_POST['batch_exam'] ?? '';
    $batchClass = $_POST['batch_class'] ?? '';
    $batchImage = $_POST['batch_image'] ?? '';
    
    if ($batchName && $batchLanguage && $batchExam && $batchClass) {
        // Create new batch object
        $newBatch = [
            '_id' => 'manual_' . time() . '_' . rand(1000, 9999),
            'name' => $batchName,
            'language' => $batchLanguage,
            'exam' => $batchExam,
            'class' => $batchClass,
            'previewImage' => $batchImage ?: 'https://via.placeholder.com/400x200?text=' . urlencode($batchName)
        ];
        
        // Add to enrolled batches
        $enrolledBatches[] = $newBatch;
        file_put_contents('../batches.json', json_encode($enrolledBatches, JSON_PRETTY_PRINT));
        $success = 'Batch added successfully!';
    } else {
        $error = 'Please fill all required fields!';
    }
}

// Handle batch addition
if ($_POST['action'] ?? '' === 'add_batch') {
    // Load current enrolled batches from JSON file
    $enrolledBatches = [];
    if (file_exists('../batches.json')) {
        $enrolledBatches = json_decode(file_get_contents('../batches.json'), true) ?: [];
    }
    
    // Handle single batch addition
    $batchId = $_POST['batch_id'] ?? '';
    if ($batchId) {
        // Load all batches to find the one to add
        $allBatches = [];
        try {
            $response = file_get_contents('https://pwxavengers-proxy.pw-avengers.workers.dev/api/batches?page=1&limit=3000');
            $data = json_decode($response, true);
            if ($data && isset($data['batches'])) {
                $allBatches = $data['batches'];
            }
        } catch (Exception $e) {
            $error = 'Failed to load batches: ' . $e->getMessage();
        }
        
        // Find and add the batch
        foreach ($allBatches as $batch) {
            if ($batch['_id'] === $batchId) {
                // Check if not already enrolled
                $alreadyEnrolled = false;
                foreach ($enrolledBatches as $enrolled) {
                    if ($enrolled['_id'] === $batchId) {
                        $alreadyEnrolled = true;
                        break;
                    }
                }
                
                if (!$alreadyEnrolled) {
                    $enrolledBatches[] = $batch;
                    file_put_contents('../batches.json', json_encode($enrolledBatches, JSON_PRETTY_PRINT));
                    $success = 'Batch added successfully!';
                } else {
                    $error = 'Batch is already added!';
                }
                break;
            }
        }
    }
    
    // Handle multiple batch addition
    $batchIds = $_POST['batch_ids'] ?? [];
    if (!empty($batchIds)) {
        // Load all batches to find the ones to add
        $allBatches = [];
        try {
            $response = file_get_contents('https://pwxavengers-proxy.pw-avengers.workers.dev/api/batches?page=1&limit=3000');
            $data = json_decode($response, true);
            if ($data && isset($data['batches'])) {
                $allBatches = $data['batches'];
            }
        } catch (Exception $e) {
            $error = 'Failed to load batches: ' . $e->getMessage();
        }
        
        $addedCount = 0;
        $alreadyAddedCount = 0;
        
        foreach ($batchIds as $batchId) {
            // Find the batch
            foreach ($allBatches as $batch) {
                if ($batch['_id'] === $batchId) {
                    // Check if not already enrolled
                    $alreadyEnrolled = false;
                    foreach ($enrolledBatches as $enrolled) {
                        if ($enrolled['_id'] === $batchId) {
                            $alreadyEnrolled = true;
                            break;
                        }
                    }
                    
                    if (!$alreadyEnrolled) {
                        $enrolledBatches[] = $batch;
                        $addedCount++;
                    } else {
                        $alreadyAddedCount++;
                    }
                    break;
                }
            }
        }
        
        if ($addedCount > 0) {
            file_put_contents('../batches.json', json_encode($enrolledBatches, JSON_PRETTY_PRINT));
            $success = "Successfully added $addedCount batch(es)!";
            if ($alreadyAddedCount > 0) {
                $success .= " $alreadyAddedCount batch(es) were already added.";
            }
        } else {
            $error = "No new batches were added. $alreadyAddedCount batch(es) were already added.";
        }
    }
}

// Load batches for display
$batches = [];
if (isset($_SESSION['admin_logged_in'])) {
    try {
        $response = file_get_contents('https://pwxavengers-proxy.pw-avengers.workers.dev/api/batches?page=1&limit=3000');
        $data = json_decode($response, true);
        if ($data && isset($data['batches'])) {
            $batches = $data['batches'];
        }
    } catch (Exception $e) {
        $error = 'Failed to load batches: ' . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Studymaxer</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 1rem 0;
        }
        .header h1 {
            font-size: 1.8rem;
            margin-bottom: 0;
        }
        .batch-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
            margin-bottom: 20px;
            height: 100%;
        }
        .batch-card:hover {
            transform: translateY(-5px);
        }
        .batch-image {
            height: 150px;
            object-fit: cover;
            border-radius: 15px 15px 0 0;
        }
        .btn-add {
            background: linear-gradient(45deg, #667eea, #764ba2);
            border: none;
            color: white;
            border-radius: 25px;
            padding: 8px 15px;
            font-size: 0.85rem;
        }
        .btn-add:hover {
            color: white;
            transform: scale(1.05);
        }
        .batch-info {
            padding: 15px;
            display: flex;
            flex-direction: column;
            height: 100%;
        }
        .batch-title {
            font-weight: bold;
            color: #333;
            margin-bottom: 10px;
            font-size: 0.9rem;
            line-height: 1.3;
        }
        .batch-meta {
            color: #666;
            font-size: 0.8rem;
            margin-bottom: 15px;
            flex-grow: 1;
        }
        .batch-meta div {
            margin-bottom: 3px;
        }
        .login-container {
            max-width: 400px;
            margin: 100px auto;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        .select-all-container {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        .loading {
            text-align: center;
            padding: 50px;
        }
        
        /* Mobile Responsive */
        @media (max-width: 768px) {
            .header h1 {
                font-size: 1.5rem;
            }
            .batch-image {
                height: 120px;
            }
            .batch-title {
                font-size: 0.85rem;
            }
            .batch-meta {
                font-size: 0.75rem;
            }
            .btn-add {
                padding: 6px 12px;
                font-size: 0.8rem;
            }
            .container {
                padding-left: 15px;
                padding-right: 15px;
            }
            .row {
                margin-left: -10px;
                margin-right: -10px;
            }
            .col-md-6, .col-lg-4 {
                padding-left: 10px;
                padding-right: 10px;
            }
            .login-container {
                margin: 50px auto;
                padding: 20px;
            }
        }
        
        @media (max-width: 576px) {
            .header {
                padding: 0.5rem 0;
            }
            .header h1 {
                font-size: 1.3rem;
            }
            .batch-image {
                height: 100px;
            }
            .batch-info {
                padding: 10px;
            }
            .batch-title {
                font-size: 0.8rem;
            }
            .batch-meta {
                font-size: 0.7rem;
            }
            .btn-add {
                padding: 5px 10px;
                font-size: 0.75rem;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-12 text-center">
                    <h1 class="mb-0"><i class="fas fa-graduation-cap me-2"></i>Studymaxer Admin</h1>
                    <div class="mt-2">
                        <a href="../index.php" class="btn btn-outline-light btn-sm">
                            <i class="fas fa-home me-1"></i>Home
                        </a>
                        <?php if (isset($_SESSION['admin_logged_in'])): ?>
                        <a href="?logout=1" class="btn btn-outline-light btn-sm ms-2">
                            <i class="fas fa-sign-out-alt me-1"></i>Logout
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <?php if (isset($error)): ?>
    <div class="container mt-3">
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-triangle me-2"></i><?php echo htmlspecialchars($error); ?>
        </div>
    </div>
    <?php endif; ?>

    <?php if (isset($success)): ?>
    <div class="container mt-3">
        <div class="alert alert-success">
            <i class="fas fa-check-circle me-2"></i><?php echo htmlspecialchars($success); ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Login Form -->
    <?php if (!isset($_SESSION['admin_logged_in'])): ?>
    <div class="container">
        <div class="login-container">
            <h3 class="text-center mb-4"><i class="fas fa-lock me-2"></i>Admin Login</h3>
            <form method="POST">
                <input type="hidden" name="action" value="login">
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>
                <button type="submit" class="btn btn-primary w-100">Login</button>
            </form>
        </div>
    </div>
    <?php else: ?>

    <!-- Admin Panel Content -->
    <div class="container mt-4">
        <div class="row">
            <div class="col-12">
                <h2 class="mb-4">Add Batches</h2>
                
                <!-- Manual Batch Addition Form -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-plus me-2"></i>Add New Batch Manually</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <input type="hidden" name="action" value="add_manual_batch">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="batch_name" class="form-label">Batch Name</label>
                                    <input type="text" class="form-control" id="batch_name" name="batch_name" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="batch_language" class="form-label">Language</label>
                                    <select class="form-control" id="batch_language" name="batch_language" required>
                                        <option value="">Select Language</option>
                                        <option value="Hindi">Hindi</option>
                                        <option value="English">English</option>
                                        <option value="Hinglish">Hinglish</option>
                                    </select>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="batch_exam" class="form-label">Exam</label>
                                    <input type="text" class="form-control" id="batch_exam" name="batch_exam" placeholder="e.g., JEE Main 2024, NEET 2024" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="batch_class" class="form-label">Class</label>
                                    <select class="form-control" id="batch_class" name="batch_class" required>
                                        <option value="">Select Class</option>
                                        <option value="Class 9">Class 9</option>
                                        <option value="Class 10">Class 10</option>
                                        <option value="Class 11">Class 11</option>
                                        <option value="Class 12">Class 12</option>
                                    </select>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="batch_image" class="form-label">Preview Image URL</label>
                                <input type="url" class="form-control" id="batch_image" name="batch_image" placeholder="https://example.com/image.jpg">
                                <small class="text-muted">Leave empty to use default placeholder image</small>
                            </div>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-plus me-1"></i>Add Batch
                            </button>
                        </form>
                    </div>
                </div>
                
                <!-- Current Enrolled Batches -->
                <?php 
                $currentEnrolled = [];
                if (file_exists('../batches.json')) {
                    $currentEnrolled = json_decode(file_get_contents('../batches.json'), true) ?: [];
                }
                if (!empty($currentEnrolled)): 
                ?>
                <div class="alert alert-info">
                    <h5><i class="fas fa-info-circle me-2"></i>Currently Enrolled Batches (<?php echo count($currentEnrolled); ?>)</h5>
                    <div class="row">
                        <?php foreach (array_slice($currentEnrolled, 0, 5) as $batch): ?>
                        <div class="col-12 col-sm-6 col-lg-4 mb-2">
                            <div class="d-flex justify-content-between align-items-center">
                                <small class="flex-grow-1 me-2"><strong><?php echo htmlspecialchars($batch['name']); ?></strong></small>
                                <a href="?remove_batch=<?php echo urlencode($batch['_id']); ?>" 
                                   class="btn btn-sm btn-outline-danger flex-shrink-0" 
                                   onclick="return confirm('Remove this batch?')">
                                    <i class="fas fa-times"></i>
                                </a>
                            </div>
                        </div>
                        <?php endforeach; ?>
                        <?php if (count($currentEnrolled) > 5): ?>
                        <div class="col-12 mt-2">
                            <small class="text-muted">... and <?php echo count($currentEnrolled) - 5; ?> more</small>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Divider -->
                <hr class="my-4">
                
                <!-- Available Batches from API -->
                <h3 class="mb-4"><i class="fas fa-database me-2"></i>Available Batches from API</h3>
                
                <?php if (empty($batches)): ?>
                <div class="loading">
                    <i class="fas fa-exclamation-triangle fa-2x mb-3 text-danger"></i>
                    <p>Error loading batches. Please try again.</p>
                </div>
                <?php else: ?>
                
                <!-- Select All Section -->
                <div class="select-all-container">
                    <div class="row align-items-center">
                        <div class="col-12 col-md-6 mb-2 mb-md-0">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="select-all">
                                <label class="form-check-label" for="select-all">
                                    <strong>Select All Batches</strong>
                                </label>
                            </div>
                        </div>
                        <div class="col-12 col-md-6 text-center text-md-end">
                            <button class="btn btn-success" onclick="addSelectedBatches()">
                                <i class="fas fa-plus me-1"></i>Add Selected Batches
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Batches Grid -->
                <div class="row">
                    <?php foreach ($batches as $batch): ?>
                    <div class="col-12 col-sm-6 col-lg-4 mb-3">
                        <div class="card batch-card">
                            <div class="card-header bg-transparent py-2">
                                <div class="form-check">
                                    <input class="form-check-input batch-checkbox" type="checkbox" 
                                           value="<?php echo htmlspecialchars($batch['_id']); ?>">
                                    <label class="form-check-label small">Select</label>
                                </div>
                            </div>
                            <img src="<?php echo htmlspecialchars($batch['previewImage'] ?? 'https://via.placeholder.com/400x200?text=No+Image'); ?>" 
                                 class="batch-image" alt="<?php echo htmlspecialchars($batch['name']); ?>">
                            <div class="batch-info">
                                <h6 class="batch-title"><?php echo htmlspecialchars($batch['name']); ?></h6>
                                <div class="batch-meta">
                                    <div><i class="fas fa-language me-1"></i><?php echo htmlspecialchars($batch['language']); ?></div>
                                    <div><i class="fas fa-calendar me-1"></i><?php echo htmlspecialchars($batch['exam']); ?></div>
                                    <div><i class="fas fa-user-graduate me-1"></i><?php echo htmlspecialchars($batch['class']); ?></div>
                                </div>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="action" value="add_batch">
                                    <input type="hidden" name="batch_id" value="<?php echo htmlspecialchars($batch['_id']); ?>">
                                    <button type="submit" class="btn btn-add w-100 mt-auto">
                                        <i class="fas fa-plus me-1"></i>Add Batch
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Select all functionality
        document.getElementById('select-all')?.addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('.batch-checkbox');
            checkboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
        });

        // Update select all when individual checkboxes change
        document.querySelectorAll('.batch-checkbox').forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                updateSelectAll();
            });
        });

        function updateSelectAll() {
            const checkboxes = document.querySelectorAll('.batch-checkbox');
            const selectAll = document.getElementById('select-all');
            const checkedCount = document.querySelectorAll('.batch-checkbox:checked').length;
            
            if (selectAll) {
                selectAll.checked = checkedCount === checkboxes.length;
                selectAll.indeterminate = checkedCount > 0 && checkedCount < checkboxes.length;
            }
        }

        // Add selected batches
        function addSelectedBatches() {
            const checkboxes = document.querySelectorAll('.batch-checkbox:checked');
            if (checkboxes.length === 0) {
                alert('Please select at least one batch!');
                return;
            }

            // Create form and submit
            const form = document.createElement('form');
            form.method = 'POST';
            form.innerHTML = '<input type="hidden" name="action" value="add_batch">';
            
            checkboxes.forEach(checkbox => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'batch_ids[]';
                input.value = checkbox.value;
                form.appendChild(input);
            });
            
            document.body.appendChild(form);
            form.submit();
        }
    </script>
</body>
</html>