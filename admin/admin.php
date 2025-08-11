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
                </div>
            </div>
        </div>
    </header>

    <!-- Login Form -->
    <div id="login-section" class="container">
        <div class="login-container">
            <h3 class="text-center mb-4"><i class="fas fa-lock me-2"></i>Admin Login</h3>
            <form id="login-form">
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control" id="password" required>
                </div>
                <button type="submit" class="btn btn-primary w-100">Login</button>
            </form>
        </div>
    </div>

    <!-- Admin Panel Content -->
    <div id="admin-content" class="container mt-4" style="display: none;">
        <div class="row">
            <div class="col-12">
                <h2 class="mb-4">Add Batches</h2>
                
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

                <!-- Loading -->
                <div id="loading" class="loading">
                    <i class="fas fa-spinner fa-spin fa-2x mb-3"></i>
                    <p>Loading batches...</p>
                </div>

                <!-- Batches Grid -->
                <div id="batches-grid" class="row" style="display: none;">
                    <!-- Batches will be loaded here -->
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const ADMIN_PASSWORD = 'admin123'; // You can change this password
        
        // Login functionality
        document.getElementById('login-form').addEventListener('submit', function(e) {
            e.preventDefault();
            const password = document.getElementById('password').value;
            
            if (password === ADMIN_PASSWORD) {
                document.getElementById('login-section').style.display = 'none';
                document.getElementById('admin-content').style.display = 'block';
                loadBatches();
            } else {
                alert('Incorrect password!');
            }
        });

        // Load batches from API
        async function loadBatches() {
            try {
                const response = await fetch('https://pwxavengers-proxy.pw-avengers.workers.dev/api/batches?page=1&limit=3000');
                const data = await response.json();
                
                if (data.success) {
                    // Store all batches in localStorage
                    localStorage.setItem('allBatches', JSON.stringify(data.batches));
                    displayBatches(data.batches);
                } else {
                    throw new Error('Failed to load batches');
                }
            } catch (error) {
                console.error('Error loading batches:', error);
                document.getElementById('loading').innerHTML = `
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Error loading batches. Please try again.
                    </div>
                `;
            }
        }

        // Display batches in grid
        function displayBatches(batches) {
            document.getElementById('loading').style.display = 'none';
            document.getElementById('batches-grid').style.display = 'block';
            
            const grid = document.getElementById('batches-grid');
            grid.innerHTML = batches.map(batch => `
                <div class="col-md-6 col-lg-4">
                    <div class="card batch-card">
                        <img src="${batch.previewImage || 'https://via.placeholder.com/400x200?text=No+Image'}" 
                             class="batch-image" alt="${batch.name}">
                        <div class="batch-info">
                            <h6 class="batch-title">${batch.name}</h6>
                            <div class="batch-meta">
                                <div><i class="fas fa-language me-2"></i>${batch.language}</div>
                                <div><i class="fas fa-calendar me-2"></i>${batch.exam}</div>
                                <div><i class="fas fa-user-graduate me-2"></i>${batch.class}</div>
                            </div>
                            <button class="btn btn-add w-100" onclick="addBatch('${batch._id}')">
                                <i class="fas fa-plus me-2"></i>Add Batch
                            </button>
                        </div>
                    </div>
                </div>
            `).join('');
        }

        // Add single batch
        function addBatch(batchId) {
            const allBatches = JSON.parse(localStorage.getItem('allBatches') || '[]');
            const batch = allBatches.find(b => b._id === batchId);
            
            if (batch) {
                const enrolledBatches = JSON.parse(localStorage.getItem('enrolledBatches') || '[]');
                
                if (!enrolledBatches.find(b => b._id === batchId)) {
                    enrolledBatches.push(batch);
                    localStorage.setItem('enrolledBatches', JSON.stringify(enrolledBatches));
                    alert('Batch added successfully!');
                } else {
                    alert('Batch is already added!');
                }
            }
        }

        // Add selected batches
        function addSelectedBatches() {
            const checkboxes = document.querySelectorAll('.batch-checkbox:checked');
            const allBatches = JSON.parse(localStorage.getItem('allBatches') || '[]');
            const enrolledBatches = JSON.parse(localStorage.getItem('enrolledBatches') || '[]');
            
            let addedCount = 0;
            
            checkboxes.forEach(checkbox => {
                const batchId = checkbox.value;
                const batch = allBatches.find(b => b._id === batchId);
                
                if (batch && !enrolledBatches.find(b => b._id === batchId)) {
                    enrolledBatches.push(batch);
                    addedCount++;
                }
            });
            
            if (addedCount > 0) {
                localStorage.setItem('enrolledBatches', JSON.stringify(enrolledBatches));
                alert(`${addedCount} batches added successfully!`);
                
                // Uncheck all checkboxes
                document.querySelectorAll('.batch-checkbox').forEach(cb => cb.checked = false);
                document.getElementById('select-all').checked = false;
            } else {
                alert('No new batches to add!');
            }
        }

        // Select all functionality
        document.getElementById('select-all').addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('.batch-checkbox');
            checkboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
        });

        // Update select all when individual checkboxes change
        function updateSelectAll() {
            const checkboxes = document.querySelectorAll('.batch-checkbox');
            const selectAll = document.getElementById('select-all');
            const checkedCount = document.querySelectorAll('.batch-checkbox:checked').length;
            
            selectAll.checked = checkedCount === checkboxes.length;
            selectAll.indeterminate = checkedCount > 0 && checkedCount < checkboxes.length;
        }

        // Modified displayBatches function to include checkboxes
        function displayBatches(batches) {
            document.getElementById('loading').style.display = 'none';
            document.getElementById('batches-grid').style.display = 'block';
            
            const grid = document.getElementById('batches-grid');
            grid.innerHTML = batches.map(batch => `
                <div class="col-md-6 col-lg-4">
                    <div class="card batch-card">
                        <div class="card-header bg-transparent">
                            <div class="form-check">
                                <input class="form-check-input batch-checkbox" type="checkbox" 
                                       value="${batch._id}" onchange="updateSelectAll()">
                                <label class="form-check-label">Select</label>
                            </div>
                        </div>
                        <img src="${batch.previewImage || 'https://via.placeholder.com/400x200?text=No+Image'}" 
                             class="batch-image" alt="${batch.name}">
                        <div class="batch-info">
                            <h6 class="batch-title">${batch.name}</h6>
                            <div class="batch-meta">
                                <div><i class="fas fa-language me-2"></i>${batch.language}</div>
                                <div><i class="fas fa-calendar me-2"></i>${batch.exam}</div>
                                <div><i class="fas fa-user-graduate me-2"></i>${batch.class}</div>
                            </div>
                            <button class="btn btn-add w-100" onclick="addBatch('${batch._id}')">
                                <i class="fas fa-plus me-2"></i>Add Batch
                            </button>
                        </div>
                    </div>
                </div>
            `).join('');
        }
    </script>
</body>
</html>