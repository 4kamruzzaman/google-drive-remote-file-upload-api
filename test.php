<?php
// --- Debug Script to Test Progress Streaming ---
// This will help identify what's blocking your progress updates

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Try to disable all possible buffering
while (ob_get_level() > 0) {
    ob_end_clean();
}

// Additional buffering controls
ini_set('output_buffering', 'off');
ini_set('zlib.output_compression', false);
ini_set('implicit_flush', true);

// Set headers to prevent caching and buffering
header('Content-Type: text/html; charset=utf-8');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');
header('X-Accel-Buffering: no'); // Disable nginx buffering

// Disable Apache compression
if (function_exists('apache_setenv')) {
    apache_setenv('no-gzip', 1);
    apache_setenv('no-brotli', 1);
}

set_time_limit(0);
ignore_user_abort(true);

?>
<!DOCTYPE html>
<html>

<head>
    <title>Progress Test</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
        }

        .progress-bar {
            width: 300px;
            height: 20px;
            background: #ddd;
            border-radius: 10px;
            overflow: hidden;
        }

        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #4CAF50, #45a049);
            width: 0%;
            transition: width 0.3s;
        }

        .debug-info {
            background: #f0f0f0;
            padding: 10px;
            margin: 10px 0;
            border-radius: 5px;
        }

        .test-section {
            margin: 20px 0;
            padding: 15px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
    </style>
</head>

<body>
    <h2>🔧 Progress Streaming Debug Tool</h2>

    <div class="debug-info">
        <strong>Server Info:</strong><br>
        PHP Version: <?php echo PHP_VERSION; ?><br>
        Server Software: <?php echo $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown'; ?><br>
        Output Buffering: <?php echo ini_get('output_buffering') ? 'ON' : 'OFF'; ?><br>
        Zlib Compression: <?php echo ini_get('zlib.output_compression') ? 'ON' : 'OFF'; ?><br>
        Current Buffer Level: <?php echo ob_get_level(); ?>
    </div>

    <div class="test-section">
        <h3>Test 1: Basic Progress Simulation</h3>
        <div class="progress-bar">
            <div id="progress1" class="progress-fill"></div>
        </div>
        <div id="status1">Starting test...</div>
    </div>

    <div class="test-section">
        <h3>Test 2: Large File Download Simulation</h3>
        <div class="progress-bar">
            <div id="progress2" class="progress-fill"></div>
        </div>
        <div id="status2">Waiting...</div>
    </div>

    <div class="test-section">
        <h3>Test 3: Upload Progress Simulation</h3>
        <div class="progress-bar">
            <div id="progress3" class="progress-fill"></div>
        </div>
        <div id="status3">Waiting...</div>
    </div>

    <div id="debug-log"></div>

    <script>
        function log(message) {
            const debugLog = document.getElementById('debug-log');
            debugLog.innerHTML += '<div>' + new Date().toLocaleTimeString() + ': ' + message + '</div>';
        }

        function updateProgress(testId, percentage, message) {
            document.getElementById('progress' + testId).style.width = percentage + '%';
            document.getElementById('status' + testId).textContent = message;
            log('Test ' + testId + ': ' + percentage + '% - ' + message);
        }
    </script>

    <?php
    // Force output to browser immediately
    echo str_pad('', 1024);
    flush();

    // Test 1: Basic streaming test
    echo "<script>log('Starting Test 1: Basic Progress');</script>";
    flush();

    for ($i = 0; $i <= 100; $i += 10) {
        echo "<script>updateProgress(1, $i, 'Basic test: $i%');</script>";
        echo str_pad('', 256); // Padding to ensure data is sent

        // Multiple flush attempts
        if (ob_get_level()) {
            ob_flush();
        }
        flush();

        sleep(1); // 1 second delay
    }

    echo "<script>log('Test 1 Complete!');</script>";
    flush();

    // Test 2: Simulate file download with cURL-like progress
    echo "<script>log('Starting Test 2: Download Simulation');</script>";
    flush();

    $totalSize = 50 * 1024 * 1024; // Simulate 50MB file
    $chunkSize = 5 * 1024 * 1024;  // 5MB chunks
    $downloaded = 0;

    for ($chunk = 0; $chunk < 10; $chunk++) {
        $downloaded += $chunkSize;
        $percentage = ($downloaded / $totalSize) * 100;
        $downloadedMB = round($downloaded / (1024 * 1024), 1);
        $totalMB = round($totalSize / (1024 * 1024), 1);

        echo "<script>updateProgress(2, $percentage, 'Downloaded: {$downloadedMB}MB / {$totalMB}MB');</script>";
        echo str_pad('', 512);

        if (ob_get_level()) {
            ob_flush();
        }
        flush();

        sleep(1);
    }

    echo "<script>log('Test 2 Complete!');</script>";
    flush();

    // Test 3: Simulate upload progress
    echo "<script>log('Starting Test 3: Upload Simulation');</script>";
    flush();

    $uploadSize = 100 * 1024 * 1024; // 100MB
    $uploadChunk = 10 * 1024 * 1024; // 10MB chunks
    $uploaded = 0;

    for ($chunk = 0; $chunk < 10; $chunk++) {
        $uploaded += $uploadChunk;
        $percentage = ($uploaded / $uploadSize) * 100;
        $uploadedMB = round($uploaded / (1024 * 1024), 1);
        $totalMB = round($uploadSize / (1024 * 1024), 1);

        echo "<script>updateProgress(3, $percentage, 'Uploaded: {$uploadedMB}MB / {$totalMB}MB');</script>";
        echo str_pad('', 512);

        if (ob_get_level()) {
            ob_flush();
        }
        flush();

        sleep(1);
    }

    echo "<script>log('All Tests Complete!');</script>";
    echo "<script>log('If you saw progress bars moving smoothly, streaming works!');</script>";
    echo "<script>log('If progress appeared all at once at the end, there is server buffering.');</script>";
    flush();

    // Final diagnostic
    echo "<div class='debug-info' style='margin-top: 20px;'>";
    echo "<h3>🔍 Diagnostic Results:</h3>";
    echo "<p><strong>If you saw progress bars updating smoothly:</strong> ✅ Streaming works! The issue is in your main script.</p>";
    echo "<p><strong>If progress appeared all at once:</strong> ❌ Server is buffering. Check the solutions below.</p>";
    echo "</div>";

    echo "<div class='debug-info'>";
    echo "<h3>🛠️ Common Solutions:</h3>";
    echo "<ul>";
    echo "<li><strong>Apache:</strong> Add <code>SetEnv no-gzip 1</code> to .htaccess</li>";
    echo "<li><strong>Nginx:</strong> Add <code>fastcgi_buffering off;</code> to server config</li>";
    echo "<li><strong>PHP-FPM:</strong> Check <code>output_buffering</code> in php.ini</li>";
    echo "<li><strong>Shared Hosting:</strong> May not support streaming - contact support</li>";
    echo "</ul>";
    echo "</div>";
    ?>

</body>

</html>