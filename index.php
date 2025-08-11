<?php
// Load enrolled batches from batches.json file
$enrolledBatches = [];
if (file_exists('batches.json')) {
    $enrolledBatches = json_decode(file_get_contents('batches.json'), true) ?: [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Studymaxer</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 1rem 0;
        }
        .header h1 {
            font-size: 2rem;
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
            height: 200px;
            object-fit: cover;
            border-radius: 15px 15px 0 0;
        }
        .btn-enroll {
            background: linear-gradient(45deg, #667eea, #764ba2);
            border: none;
            color: white;
            border-radius: 25px;
            padding: 10px 15px;
            font-size: 0.9rem;
        }
        .btn-explore {
            background: linear-gradient(45deg, #f093fb, #f5576c);
            border: none;
            color: white;
            border-radius: 25px;
            padding: 10px 15px;
            font-size: 0.9rem;
        }
        .btn-enroll:hover, .btn-explore:hover {
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
            font-size: 1.1rem;
            line-height: 1.3;
        }
        .batch-meta {
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 15px;
            flex-grow: 1;
        }
        .batch-meta div {
            margin-bottom: 5px;
        }
        .no-batches {
            text-align: center;
            padding: 50px 20px;
            color: #666;
        }
        .no-batches h4 {
            margin-bottom: 15px;
        }
        .no-batches p {
            margin-bottom: 20px;
        }
        
        /* Mobile Responsive */
        @media (max-width: 768px) {
            .header h1 {
                font-size: 1.5rem;
            }
            .batch-image {
                height: 150px;
            }
            .batch-title {
                font-size: 1rem;
            }
            .batch-meta {
                font-size: 0.8rem;
            }
            .btn-enroll, .btn-explore {
                padding: 8px 12px;
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
        }
        
        @media (max-width: 576px) {
            .header {
                padding: 0.5rem 0;
            }
            .header h1 {
                font-size: 1.3rem;
            }
            .batch-image {
                height: 120px;
            }
            .batch-info {
                padding: 10px;
            }
            .batch-title {
                font-size: 0.9rem;
            }
            .batch-meta {
                font-size: 0.75rem;
            }
            .btn-enroll, .btn-explore {
                padding: 6px 10px;
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
                    <h1 class="mb-0"><i class="fas fa-graduation-cap me-2"></i>Studymaxer</h1>
                    <?php if (!empty($enrolledBatches)): ?>
                    <div class="mt-2">
                        <span class="badge bg-light text-dark">
                            <i class="fas fa-book me-2"></i><?php echo count($enrolledBatches); ?> Enrolled Batches
                        </span>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <div class="container mt-4">
        <div class="row">
            <div class="col-12">
                <h2 class="mb-4 text-center">Your Enrolled Batches</h2>
                <div id="enrolled-batches">
                    <?php if (empty($enrolledBatches)): ?>
                        <div class="no-batches">
                            <i class="fas fa-book-open fa-3x mb-3 text-muted"></i>
                            <h4>No Enrolled Batches</h4>
                            <p>You haven't enrolled in any batches yet. Visit the admin panel to add batches.</p>
                            <a href="admin/admin.php" class="btn btn-primary">Go to Admin Panel</a>
                        </div>
                    <?php else: ?>
                        <div class="row">
                            <?php foreach ($enrolledBatches as $batch): ?>
                                <div class="col-12 col-sm-6 col-lg-4 mb-3">
                                    <div class="card batch-card">
                                        <img src="<?php echo htmlspecialchars($batch['previewImage'] ?? 'https://via.placeholder.com/400x200?text=No+Image'); ?>" 
                                             class="batch-image" alt="<?php echo htmlspecialchars($batch['name']); ?>">
                                        <div class="batch-info">
                                            <h5 class="batch-title"><?php echo htmlspecialchars($batch['name']); ?></h5>
                                            <div class="batch-meta">
                                                <div><i class="fas fa-language me-2"></i><?php echo htmlspecialchars($batch['language']); ?></div>
                                                <div><i class="fas fa-calendar me-2"></i><?php echo htmlspecialchars($batch['exam']); ?></div>
                                                <div><i class="fas fa-user-graduate me-2"></i><?php echo htmlspecialchars($batch['class']); ?></div>
                                            </div>
                                            <div class="d-flex gap-2 mt-auto">
                                                <button class="btn btn-enroll flex-fill" onclick="enrollBatch('<?php echo $batch['_id']; ?>')">
                                                    <i class="fas fa-user-plus me-1"></i>Enroll
                                                </button>
                                                <button class="btn btn-explore flex-fill" onclick="exploreBatch('<?php echo $batch['_id']; ?>')">
                                                    <i class="fas fa-play me-1"></i>Explore
                                                </button>
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
        function enrollBatch(batchId) {
            // Add to enrolled batches if not already enrolled
            fetch('enroll_batch.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ batchId: batchId })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Successfully enrolled in batch!');
                    location.reload();
                } else {
                    alert(data.message || 'Error enrolling in batch');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error enrolling in batch');
            });
        }

        function exploreBatch(batchId) {
            window.location.href = `batch.php?batch=${batchId}`;
        }
    </script>
</body>
</html>
