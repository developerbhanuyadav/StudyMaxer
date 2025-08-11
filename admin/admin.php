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

// Handle batch addition
if ($_POST['action'] ?? '' === 'add_batch') {
    $batchId = $_POST['batch_id'] ?? '';
    if ($batchId) {
        $enrolledBatches = isset($_SESSION['enrolledBatches']) ? $_SESSION['enrolledBatches'] : [];
        
        // Load all batches to find the one to add
        $allBatches = [];
        try {
            $response = file_get_contents('https://pwxavengers-proxy.pw-avengers.workers.dev/api/batches?page=1&limit=3000');
            $data = json_decode($response, true);
            if ($data && isset($data['batches'])) {
                $allBatches = $data['batches'];
            }
        } catch (Exception $e) {
            $error = 'Failed to load batches';
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
                    $_SESSION['enrolledBatches'] = $enrolledBatches;
                    $success = 'Batch added successfully!';
                } else {
                    $error = 'Batch is already added!';
                }
                break;
            }
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
        $error = 'Failed to load batches';
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
        .batch-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
            margin-bottom: 20px;
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
            padding: 8px 20px;
        }
        .btn-add:hover {
            color: white;
            transform: scale(1.05);
        }
        .batch-info {
            padding: 15px;
        }
        .batch-title {
            font-weight: bold;
            color: #333;
            margin-bottom: 10px;
            font-size: 0.9rem;
        }
        .batch-meta {
            color: #666;
            font-size: 0.8rem;
            margin-bottom: 15px;
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
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h1 class="mb-0"><i class="fas fa-graduation-cap me-2"></i>Studymaxer Admin</h1>
                </div>
                <div class="col-md-6 text-end">
                    <a href="../index.php" class="btn btn-outline-light">
                        <i class="fas fa-home me-2"></i>Back to Home
                    </a>
                    <?php if (isset($_SESSION['admin_logged_in'])): ?>
                    <a href="?logout=1" class="btn btn-outline-light ms-2">
                        <i class="fas fa-sign-out-alt me-2"></i>Logout
                    </a>
                    <?php endif; ?>
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
                
                <?php if (empty($batches)): ?>
                <div class="loading">
                    <i class="fas fa-exclamation-triangle fa-2x mb-3 text-danger"></i>
                    <p>Error loading batches. Please try again.</p>
                </div>
                <?php else: ?>
                
                <!-- Select All Section -->
                <div class="select-all-container">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="select-all">
                                <label class="form-check-label" for="select-all">
                                    <strong>Select All Batches</strong>
                                </label>
                            </div>
                        </div>
                        <div class="col-md-6 text-end">
                            <button class="btn btn-success" onclick="addSelectedBatches()">
                                <i class="fas fa-plus me-2"></i>Add Selected Batches
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Batches Grid -->
                <div class="row">
                    <?php foreach ($batches as $batch): ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="card batch-card">
                            <div class="card-header bg-transparent">
                                <div class="form-check">
                                    <input class="form-check-input batch-checkbox" type="checkbox" 
                                           value="<?php echo htmlspecialchars($batch['_id']); ?>">
                                    <label class="form-check-label">Select</label>
                                </div>
                            </div>
                            <img src="<?php echo htmlspecialchars($batch['previewImage'] ?? 'https://via.placeholder.com/400x200?text=No+Image'); ?>" 
                                 class="batch-image" alt="<?php echo htmlspecialchars($batch['name']); ?>">
                            <div class="batch-info">
                                <h6 class="batch-title"><?php echo htmlspecialchars($batch['name']); ?></h6>
                                <div class="batch-meta">
                                    <div><i class="fas fa-language me-2"></i><?php echo htmlspecialchars($batch['language']); ?></div>
                                    <div><i class="fas fa-calendar me-2"></i><?php echo htmlspecialchars($batch['exam']); ?></div>
                                    <div><i class="fas fa-user-graduate me-2"></i><?php echo htmlspecialchars($batch['class']); ?></div>
                                </div>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="action" value="add_batch">
                                    <input type="hidden" name="batch_id" value="<?php echo htmlspecialchars($batch['_id']); ?>">
                                    <button type="submit" class="btn btn-add w-100">
                                        <i class="fas fa-plus me-2"></i>Add Batch
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