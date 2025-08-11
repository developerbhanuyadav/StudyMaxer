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
            height: 200px;
            object-fit: cover;
            border-radius: 15px 15px 0 0;
        }
        .btn-enroll {
            background: linear-gradient(45deg, #667eea, #764ba2);
            border: none;
            color: white;
            border-radius: 25px;
            padding: 10px 25px;
        }
        .btn-explore {
            background: linear-gradient(45deg, #f093fb, #f5576c);
            border: none;
            color: white;
            border-radius: 25px;
            padding: 10px 25px;
        }
        .btn-enroll:hover, .btn-explore:hover {
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
        }
        .batch-meta {
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 15px;
        }
        .no-batches {
            text-align: center;
            padding: 50px;
            color: #666;
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
                    <a href="admin/admin.php" class="btn btn-outline-light">
                        <i class="fas fa-cog me-2"></i>Admin Panel
                    </a>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <div class="container mt-4">
        <div class="row">
            <div class="col-12">
                <h2 class="mb-4">Your Enrolled Batches</h2>
                <div id="enrolled-batches">
                    <!-- Batches will be loaded here -->
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Load enrolled batches from localStorage
        function loadEnrolledBatches() {
            const enrolledBatches = JSON.parse(localStorage.getItem('enrolledBatches') || '[]');
            const container = document.getElementById('enrolled-batches');
            
            if (enrolledBatches.length === 0) {
                container.innerHTML = `
                    <div class="no-batches">
                        <i class="fas fa-book-open fa-3x mb-3 text-muted"></i>
                        <h4>No Enrolled Batches</h4>
                        <p>You haven't enrolled in any batches yet. Visit the admin panel to add batches.</p>
                        <a href="admin/admin.php" class="btn btn-primary">Go to Admin Panel</a>
                    </div>
                `;
                return;
            }

            container.innerHTML = enrolledBatches.map(batch => `
                <div class="col-md-6 col-lg-4">
                    <div class="card batch-card">
                        <img src="${batch.previewImage || 'https://via.placeholder.com/400x200?text=No+Image'}" 
                             class="batch-image" alt="${batch.name}">
                        <div class="batch-info">
                            <h5 class="batch-title">${batch.name}</h5>
                            <div class="batch-meta">
                                <div><i class="fas fa-language me-2"></i>${batch.language}</div>
                                <div><i class="fas fa-calendar me-2"></i>${batch.exam}</div>
                                <div><i class="fas fa-user-graduate me-2"></i>${batch.class}</div>
                            </div>
                            <div class="d-flex gap-2">
                                <button class="btn btn-enroll flex-fill" onclick="enrollBatch('${batch._id}')">
                                    <i class="fas fa-user-plus me-2"></i>Enroll Now
                                </button>
                                <button class="btn btn-explore flex-fill" onclick="exploreBatch('${batch._id}')">
                                    <i class="fas fa-play me-2"></i>Explore
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            `).join('');
        }

        function enrollBatch(batchId) {
            // Add to enrolled batches if not already enrolled
            const enrolledBatches = JSON.parse(localStorage.getItem('enrolledBatches') || '[]');
            if (!enrolledBatches.find(batch => batch._id === batchId)) {
                // Get batch details from all batches
                const allBatches = JSON.parse(localStorage.getItem('allBatches') || '[]');
                const batch = allBatches.find(b => b._id === batchId);
                if (batch) {
                    enrolledBatches.push(batch);
                    localStorage.setItem('enrolledBatches', JSON.stringify(enrolledBatches));
                    alert('Successfully enrolled in batch!');
                    loadEnrolledBatches();
                }
            } else {
                alert('You are already enrolled in this batch!');
            }
        }

        function exploreBatch(batchId) {
            window.location.href = `batch.php?batch=${batchId}`;
        }

        // Load batches on page load
        document.addEventListener('DOMContentLoaded', loadEnrolledBatches);
    </script>
</body>
</html>
