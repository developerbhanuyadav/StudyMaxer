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
        .lecture-card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
            margin-bottom: 15px;
            cursor: pointer;
        }
        .lecture-card:hover {
            transform: translateY(-2px);
        }
        .lecture-time {
            background: linear-gradient(45deg, #667eea, #764ba2);
            color: white;
            padding: 8px 15px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: bold;
        }
        .lecture-status {
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 0.7rem;
            font-weight: bold;
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
        .loading {
            text-align: center;
            padding: 50px;
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
                <div id="batch-info" class="mb-4">
                    <!-- Batch info will be loaded here -->
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
                    <div id="todays-classes">
                        <div class="loading">
                            <i class="fas fa-spinner fa-spin fa-2x mb-3"></i>
                            <p>Loading today's classes...</p>
                        </div>
                    </div>
                </div>

                <!-- Subjects Section -->
                <div class="mb-5">
                    <h3 class="section-title">
                        <i class="fas fa-book me-2"></i>Subjects
                    </h3>
                    <div id="subjects">
                        <div class="loading">
                            <i class="fas fa-spinner fa-spin fa-2x mb-3"></i>
                            <p>Loading subjects...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Get batch ID from URL
        const urlParams = new URLSearchParams(window.location.search);
        const batchId = urlParams.get('batch');

        if (!batchId) {
            alert('Batch ID not found!');
            window.location.href = 'index.php';
        }

        // Load batch info
        function loadBatchInfo() {
            const enrolledBatches = JSON.parse(localStorage.getItem('enrolledBatches') || '[]');
            const batch = enrolledBatches.find(b => b._id === batchId);
            
            if (batch) {
                document.getElementById('batch-info').innerHTML = `
                    <div class="card">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-md-3">
                                    <img src="${batch.previewImage || 'https://via.placeholder.com/300x200?text=No+Image'}" 
                                         class="img-fluid rounded" alt="${batch.name}">
                                </div>
                                <div class="col-md-9">
                                    <h2>${batch.name}</h2>
                                    <p class="text-muted">${batch.byName || ''}</p>
                                    <div class="row">
                                        <div class="col-md-4">
                                            <strong><i class="fas fa-language me-2"></i>Language:</strong> ${batch.language}
                                        </div>
                                        <div class="col-md-4">
                                            <strong><i class="fas fa-calendar me-2"></i>Exam:</strong> ${batch.exam}
                                        </div>
                                        <div class="col-md-4">
                                            <strong><i class="fas fa-user-graduate me-2"></i>Class:</strong> ${batch.class}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            }
        }

        // Load today's classes
        async function loadTodaysClasses() {
            try {
                const response = await fetch(`https://pwxavengers-proxy.pw-avengers.workers.dev/api/batch/${batchId}/todays-schedule`);
                const data = await response.json();
                
                if (data.success) {
                    // Filter only lectures (exclude PDFs)
                    const lectures = data.data.filter(item => 
                        item.lectureType === 'LIVE' || 
                        (item.videoDetails && item.videoDetails.status === 'Ready')
                    );
                    
                    displayTodaysClasses(lectures);
                } else {
                    throw new Error('Failed to load today\'s classes');
                }
            } catch (error) {
                console.error('Error loading today\'s classes:', error);
                document.getElementById('todays-classes').innerHTML = `
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        No classes scheduled for today.
                    </div>
                `;
            }
        }

        // Display today's classes
        function displayTodaysClasses(lectures) {
            const container = document.getElementById('todays-classes');
            
            if (lectures.length === 0) {
                container.innerHTML = `
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        No classes scheduled for today.
                    </div>
                `;
                return;
            }

            container.innerHTML = `
                <div class="subjects-scroll">
                    ${lectures.map(lecture => {
                        const startTime = new Date(lecture.startTime);
                        const timeString = startTime.toLocaleTimeString('en-US', { 
                            hour: '2-digit', 
                            minute: '2-digit',
                            hour12: true 
                        });
                        
                        let statusClass = 'status-pending';
                        let statusText = 'Pending';
                        
                        if (lecture.status === 'LIVE') {
                            statusClass = 'status-live';
                            statusText = 'LIVE';
                        } else if (lecture.status === 'COMPLETED') {
                            statusClass = 'status-completed';
                            statusText = 'Completed';
                        }
                        
                        return `
                            <div class="card lecture-card" onclick="openLecture('${lecture._id}')">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <span class="lecture-time">${timeString}</span>
                                        <span class="lecture-status ${statusClass}">${statusText}</span>
                                    </div>
                                    <h6 class="card-title">${lecture.topic}</h6>
                                    <p class="card-text text-muted">
                                        <i class="fas fa-user me-2"></i>${lecture.subjectId?.name || 'Subject'}
                                    </p>
                                </div>
                            </div>
                        `;
                    }).join('')}
                </div>
            `;
        }

        // Load subjects
        async function loadSubjects() {
            try {
                const response = await fetch(`https://api.tejtimes.live/api/pw/details/subject.php?batch_id=${batchId}`);
                const data = await response.json();
                
                if (data.subjects) {
                    displaySubjects(data.subjects);
                } else {
                    throw new Error('Failed to load subjects');
                }
            } catch (error) {
                console.error('Error loading subjects:', error);
                document.getElementById('subjects').innerHTML = `
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Error loading subjects. Please try again.
                    </div>
                `;
            }
        }

        // Display subjects
        function displaySubjects(subjects) {
            const container = document.getElementById('subjects');
            
            container.innerHTML = `
                <div class="subjects-scroll">
                    ${subjects.map(subject => `
                        <div class="card subject-card" onclick="openSubject('${subject.subject_id}')">
                            <img src="${subject.subject_image}" class="subject-image" alt="${subject.subject_name}">
                            <div class="subject-name">${subject.subject_name}</div>
                        </div>
                    `).join('')}
                </div>
            `;
        }

        // Open lecture
        async function openLecture(scheduleId) {
            try {
                const response = await fetch(`https://pwxavengers-proxy.pw-avengers.workers.dev/api/url?batch_id=${batchId}&schedule_id=${scheduleId}`);
                const data = await response.json();
                
                if (data.success) {
                    window.location.href = `play.php?encrypted=${data.signed_url}&v=${data.video_id}`;
                } else {
                    alert('Unable to load lecture. Please try again.');
                }
            } catch (error) {
                console.error('Error opening lecture:', error);
                alert('Error opening lecture. Please try again.');
            }
        }

        // Open subject
        function openSubject(subjectId) {
            window.location.href = `subject.php?batch=${batchId}&subject=${subjectId}`;
        }

        // Start studying (redirect to first subject or show message)
        function startStudying() {
            // For now, just show a message
            alert('Choose a subject or today\'s class to start studying!');
        }

        // Load everything on page load
        document.addEventListener('DOMContentLoaded', function() {
            loadBatchInfo();
            loadTodaysClasses();
            loadSubjects();
        });
    </script>
</body>
</html>