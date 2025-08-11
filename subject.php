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
        .loading {
            text-align: center;
            padding: 50px;
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
                <div id="subject-info" class="mb-4">
                    <!-- Subject info will be loaded here -->
                </div>

                <!-- Chapters Section -->
                <div class="mb-5">
                    <h3 class="section-title">
                        <i class="fas fa-list me-2"></i>Chapters
                    </h3>
                    <div id="chapters">
                        <div class="loading">
                            <i class="fas fa-spinner fa-spin fa-2x mb-3"></i>
                            <p>Loading chapters...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Get parameters from URL
        const urlParams = new URLSearchParams(window.location.search);
        const batchId = urlParams.get('batch');
        const subjectId = urlParams.get('subject');

        if (!batchId || !subjectId) {
            alert('Batch ID or Subject ID not found!');
            window.location.href = 'index.php';
        }

        // Load subject info
        async function loadSubjectInfo() {
            try {
                const response = await fetch(`https://api.tejtimes.live/api/pw/details/subject.php?batch_id=${batchId}`);
                const data = await response.json();
                
                if (data.subjects) {
                    const subject = data.subjects.find(s => s.subject_id === subjectId);
                    if (subject) {
                        document.getElementById('subject-info').innerHTML = `
                            <div class="card">
                                <div class="card-body">
                                    <div class="row align-items-center">
                                        <div class="col-md-3">
                                            <img src="${subject.subject_image}" 
                                                 class="img-fluid rounded" alt="${subject.subject_name}">
                                        </div>
                                        <div class="col-md-9">
                                            <h2>${subject.subject_name}</h2>
                                            <p class="text-muted">Explore all chapters and topics in this subject</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        `;
                    }
                }
            } catch (error) {
                console.error('Error loading subject info:', error);
            }
        }

        // Load chapters
        async function loadChapters() {
            try {
                const response = await fetch(`https://pwxavengers-proxy.pw-avengers.workers.dev/api/batch/${batchId}/subject/${subjectId}/topics`);
                const data = await response.json();
                
                if (data.success) {
                    displayChapters(data.data);
                } else {
                    throw new Error('Failed to load chapters');
                }
            } catch (error) {
                console.error('Error loading chapters:', error);
                document.getElementById('chapters').innerHTML = `
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Error loading chapters. Please try again.
                    </div>
                `;
            }
        }

        // Display chapters
        function displayChapters(chapters) {
            const container = document.getElementById('chapters');
            
            if (chapters.length === 0) {
                container.innerHTML = `
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        No chapters available for this subject.
                    </div>
                `;
                return;
            }

            container.innerHTML = `
                <div class="row">
                    ${chapters.map(chapter => `
                        <div class="col-md-6 col-lg-4">
                            <div class="card chapter-card" onclick="openChapter('${chapter._id}')">
                                <div class="card-body">
                                    <h5 class="card-title">${chapter.name}</h5>
                                    <div class="chapter-stats">
                                        <div class="stat-item">
                                            <i class="fas fa-video stat-icon"></i>
                                            <span>${chapter.videos || 0} Videos</span>
                                        </div>
                                        <div class="stat-item">
                                            <i class="fas fa-file-alt stat-icon"></i>
                                            <span>${chapter.notes || 0} Notes</span>
                                        </div>
                                        <div class="stat-item">
                                            <i class="fas fa-tasks stat-icon"></i>
                                            <span>${chapter.exercises || 0} Exercises</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `).join('')}
                </div>
            `;
        }

        // Open chapter
        function openChapter(topicId) {
            window.location.href = `chapter.php?batch=${batchId}&subject=${subjectId}&topic=${topicId}`;
        }

        // Go back
        function goBack() {
            window.history.back();
        }

        // Load everything on page load
        document.addEventListener('DOMContentLoaded', function() {
            loadSubjectInfo();
            loadChapters();
        });
    </script>
</body>
</html>