<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Video Player - Studymaxer</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 1rem 0;
        }
        .video-container {
            position: relative;
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
            background: #000;
            border-radius: 10px;
            overflow: hidden;
        }
        .video-player {
            width: 100%;
            height: 0;
            padding-bottom: 56.25%; /* 16:9 aspect ratio */
            position: relative;
        }
        .video-player iframe {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            border: none;
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
        .loading {
            text-align: center;
            padding: 100px;
            color: #666;
        }
        .error-message {
            text-align: center;
            padding: 50px;
            color: #dc3545;
        }
        .video-info {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin-top: 20px;
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
                <!-- Video Player -->
                <div id="video-section">
                    <div class="loading">
                        <i class="fas fa-spinner fa-spin fa-3x mb-3"></i>
                        <h4>Loading Video...</h4>
                        <p>Please wait while we prepare your lecture.</p>
                    </div>
                </div>

                <!-- Video Info -->
                <div id="video-info" class="video-info" style="display: none;">
                    <h4><i class="fas fa-info-circle me-2"></i>Lecture Information</h4>
                    <div id="lecture-details">
                        <!-- Lecture details will be loaded here -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Get parameters from URL
        const urlParams = new URLSearchParams(window.location.search);
        const encrypted = urlParams.get('encrypted');
        const videoId = urlParams.get('v');

        if (!encrypted || !videoId) {
            showError('Video parameters not found!');
        } else {
            loadVideo();
        }

        // Load video
        function loadVideo() {
            try {
                // Decode the encrypted URL (this is a simplified version)
                // In a real implementation, you would need proper decryption
                const videoUrl = decodeURIComponent(encrypted);
                
                // Create video player
                const videoSection = document.getElementById('video-section');
                videoSection.innerHTML = `
                    <div class="video-container">
                        <div class="video-player">
                            <iframe 
                                src="${videoUrl}" 
                                allowfullscreen 
                                allow="autoplay; fullscreen">
                            </iframe>
                        </div>
                    </div>
                `;

                // Show video info
                document.getElementById('video-info').style.display = 'block';
                loadVideoInfo();
                
            } catch (error) {
                console.error('Error loading video:', error);
                showError('Unable to load video. Please try again.');
            }
        }

        // Load video information
        function loadVideoInfo() {
            // This would typically load from the lecture data
            // For now, we'll show a placeholder
            document.getElementById('lecture-details').innerHTML = `
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Status:</strong> <span class="badge bg-success">Ready</span></p>
                        <p><strong>Video ID:</strong> ${videoId}</p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Player:</strong> Encrypted Video Player</p>
                        <p><strong>Quality:</strong> Auto</p>
                    </div>
                </div>
            `;
        }

        // Show error message
        function showError(message) {
            document.getElementById('video-section').innerHTML = `
                <div class="error-message">
                    <i class="fas fa-exclamation-triangle fa-3x mb-3"></i>
                    <h4>Error</h4>
                    <p>${message}</p>
                    <button class="btn btn-primary" onclick="goBack()">
                        <i class="fas fa-arrow-left me-2"></i>Go Back
                    </button>
                </div>
            `;
        }

        // Go back
        function goBack() {
            window.history.back();
        }

        // Handle video player errors
        window.addEventListener('error', function(e) {
            if (e.target.tagName === 'IFRAME') {
                showError('Video failed to load. Please check your connection and try again.');
            }
        });

        // Add keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                goBack();
            }
        });
    </script>
</body>
</html>