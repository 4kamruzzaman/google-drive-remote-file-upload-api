<?php
// --- Optimized High-Speed Upload Script ---
// Optimized for 8GB RAM, 55 MB/s I/O, 4x CPU hosting

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Aggressive memory and performance settings
ini_set('memory_limit', '6G'); // Use 75% of available RAM
ini_set('max_execution_time', 0);
ini_set('max_input_time', 0);
set_time_limit(0);
ignore_user_abort(true);

// Optimize PHP for high throughput
ini_set('default_socket_timeout', 600);
ini_set('auto_detect_line_endings', true);

// Disable all buffering for real-time progress
while (ob_get_level() > 0) {
    ob_end_clean();
}
ini_set('output_buffering', 'off');
ini_set('zlib.output_compression', false);
ini_set('implicit_flush', true);

// Headers for streaming and performance
header('Content-Type: text/html; charset=utf-8');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');
header('X-Accel-Buffering: no');
header('Connection: keep-alive');

// Disable Apache/Nginx compression
if (function_exists('apache_setenv')) {
    apache_setenv('no-gzip', 1);
    apache_setenv('no-brotli', 1);
}

require_once __DIR__ . '/vendor/autoload.php';

// --- File Storage Configuration ---
define('LOCAL_FILES_DIR', __DIR__ . '/../unpub/files/');

// Create directory if it doesn't exist
if (!is_dir(LOCAL_FILES_DIR)) {
    if (!mkdir(LOCAL_FILES_DIR, 0755, true)) {
        throw new Exception("Could not create local files directory: " . LOCAL_FILES_DIR);
    }
}

// --- Optimized Progress Function ---
function sendProgressUpdate($script)
{
    echo $script;
    echo str_pad('', 4096); // Larger buffer for better streaming

    if (ob_get_level()) {
        ob_flush();
    }
    flush();

    // Reduced delay for faster updates
    usleep(5000); // 5ms instead of 10ms
}

// --- File Existence Check Function ---
function checkFileExists($url)
{
    $fileName = basename(parse_url($url, PHP_URL_PATH)) ?: 'downloaded_file_' . date('Y-m-d_H-i-s');
    $localFilePath = LOCAL_FILES_DIR . $fileName;

    if (file_exists($localFilePath) && filesize($localFilePath) > 0) {
        return [
            'exists' => true,
            'path' => $localFilePath,
            'name' => $fileName,
            'size' => filesize($localFilePath)
        ];
    }

    return [
        'exists' => false,
        'path' => $localFilePath,
        'name' => $fileName,
        'size' => 0
    ];
}

// --- Authentication (unchanged) ---
function getAuthenticatedClient()
{
    $tokenPath = 'token.json';
    $client = new Google_Client();
    $client->setApplicationName('PHP Google Drive Uploader');
    $client->setScopes(Google_Service_Drive::DRIVE_FILE);
    $client->setAuthConfig('credentials.json');
    $client->setAccessType('offline');
    $client->setPrompt('select_account consent');

    $redirect_uri = 'http://gup2461.gfxload.com';
    $client->setRedirectUri($redirect_uri);

    if (file_exists($tokenPath)) {
        $accessToken = json_decode(file_get_contents($tokenPath), true);
        $client->setAccessToken($accessToken);
    }

    if ($client->isAccessTokenExpired()) {
        if ($client->getRefreshToken()) {
            $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
            file_put_contents($tokenPath, json_encode($client->getAccessToken()));
        } else {
            if (isset($_GET['code'])) {
                $authCode = $_GET['code'];
                $accessToken = $client->fetchAccessTokenWithAuthCode($authCode);
                $client->setAccessToken($accessToken);
                if (array_key_exists('error', $accessToken)) {
                    throw new Exception(join(', ', $accessToken));
                }
                file_put_contents($tokenPath, json_encode($client->getAccessToken()));
                $urlParam = isset($_SESSION['upload_url']) ? '?url=' . urlencode($_SESSION['upload_url']) : '';
                unset($_SESSION['upload_url']);
                header('Location: ' . filter_var($redirect_uri . $urlParam, FILTER_SANITIZE_URL));
                exit;
            } else {
                if (isset($_GET['url'])) {
                    $_SESSION['upload_url'] = $_GET['url'];
                }
                return $client;
            }
        }
    }
    return $client;
}

/**
 * High-speed download with optimized cURL settings and local storage
 */
function downloadRemoteFileOptimized($url, $targetPath)
{
    $tempFileHandle = fopen($targetPath, 'wb');
    if (!$tempFileHandle) {
        throw new Exception("Could not create file for download: " . $targetPath);
    }

    $ch = curl_init();

    // Aggressive cURL optimization for speed
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_FILE => $tempFileHandle,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 7200, // 2 hours max
        CURLOPT_CONNECTTIMEOUT => 30,

        // Speed optimizations
        CURLOPT_TCP_NODELAY => true,
        CURLOPT_TCP_KEEPALIVE => 1,
        CURLOPT_TCP_KEEPIDLE => 2,
        CURLOPT_TCP_KEEPINTVL => 2,
        CURLOPT_BUFFERSIZE => 1048576, // 1MB buffer (max allowed)

        // Connection reuse
        CURLOPT_FRESH_CONNECT => false,
        CURLOPT_FORBID_REUSE => false,

        // Modern protocols
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_2_0,
        CURLOPT_SSLVERSION => CURL_SSLVERSION_TLSv1_2,

        // Headers for speed
        CURLOPT_USERAGENT => 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
        CURLOPT_HTTPHEADER => [
            'Accept: */*',
            'Accept-Encoding: gzip, deflate, br',
            'Connection: keep-alive',
            'Cache-Control: no-cache'
        ],

        // Security (relaxed for speed)
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,

        // Progress tracking
        CURLOPT_NOPROGRESS => false,
        CURLOPT_PROGRESSFUNCTION => function ($resource, $download_size, $downloaded, $upload_size, $uploaded) {
            static $last_update = 0;
            static $start_time = null;

            if ($start_time === null) {
                $start_time = microtime(true);
            }

            $now = microtime(true);

            // Update every 0.2 seconds for smoother progress
            if ($now - $last_update > 0.2) {
                $last_update = $now;

                $elapsed = $now - $start_time;
                $speed_mbps = $elapsed > 0 ? ($downloaded / (1024 * 1024)) / $elapsed : 0;

                $megabytes = number_format($downloaded / (1024 * 1024), 2);
                $percentage = 0;
                $sizeInfo = '';
                $eta = '';

                if ($download_size > 0) {
                    $percentage = ($downloaded / $download_size) * 100;
                    $totalMB = number_format($download_size / (1024 * 1024), 2);
                    $sizeInfo = " of {$totalMB} MB";

                    // Calculate ETA
                    if ($speed_mbps > 0 && $percentage > 1) {
                        $remaining_mb = ($download_size - $downloaded) / (1024 * 1024);
                        $eta_seconds = $remaining_mb / $speed_mbps;

                        // FIX: Convert the float to an integer before using it in gmdate()
                        $eta = " • ETA: " . gmdate("H:i:s", round($eta_seconds));
                    }
                }

                $speedText = " • " . number_format($speed_mbps, 1) . " MB/s";

                $script = "<script>
                    updateDownloadProgress(" . round($percentage, 1) . ", '{$megabytes} MB{$sizeInfo}{$speedText}{$eta}', " . $download_size . ");
                </script>";
                sendProgressUpdate($script);
            }

            return 0;
        }
    ]);

    sendProgressUpdate("<script>
        updateStatus('🚀 Starting high-speed download...');
    </script>");

    $success = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $totalTime = curl_getinfo($ch, CURLINFO_TOTAL_TIME);
    $downloadSpeed = curl_getinfo($ch, CURLINFO_SPEED_DOWNLOAD);
    $curlError = curl_error($ch);

    curl_close($ch);
    fclose($tempFileHandle);

    if (!$success || $httpCode >= 400) {
        unlink($targetPath);
        throw new Exception("Download Failed - HTTP {$httpCode}: " . ($curlError ?: 'Unknown error'));
    }

    $fileSize = filesize($targetPath);
    $avgSpeed = $totalTime > 0 ? ($fileSize / (1024 * 1024)) / $totalTime : 0;

    sendProgressUpdate("<script>
        updateDownloadProgress(100, 'Complete! Avg: " . number_format($avgSpeed, 1) . " MB/s', 1);
        updateStatus('✅ Download complete! File saved locally. Preparing optimized upload...');
    </script>");

    return $targetPath;
}

/**
 * High-speed resumable upload with optimal chunk size
 */
function uploadToDriveOptimized(Google_Client $client, $filePath, $fileName)
{
    sendProgressUpdate("<script>
        updateStatus('🔧 Optimizing Google Drive upload...');
    </script>");

    $driveService = new Google_Service_Drive($client);
    $fileSize = filesize($filePath);

    // Dynamic chunk size based on file size for optimal speed
    if ($fileSize < 100 * 1024 * 1024) { // < 100MB
        $chunkSizeBytes = 8 * 1024 * 1024; // 8MB chunks
    } elseif ($fileSize < 1024 * 1024 * 1024) { // < 1GB
        $chunkSizeBytes = 32 * 1024 * 1024; // 32MB chunks
    } else { // >= 1GB
        $chunkSizeBytes = 128 * 1024 * 1024; // 128MB chunks
    }

    $client->setDefer(true);

    $fileMetadata = new Google_Service_Drive_DriveFile(['name' => $fileName]);
    $request = $driveService->files->create($fileMetadata);
    $media = new Google_Http_MediaFileUpload($client, $request, 'application/octet-stream', null, true, $chunkSizeBytes);
    $media->setFileSize($fileSize);

    $status = false;
    $handle = fopen($filePath, "rb");
    $bytesUploaded = 0;
    $startTime = microtime(true);
    $lastUpdate = 0;

    sendProgressUpdate("<script>
        updateStatus('☁️ High-speed upload to Google Drive initiated...');
    </script>");

    while (!$status && !feof($handle)) {
        $chunkStart = microtime(true);
        $chunk = fread($handle, $chunkSizeBytes);
        $chunkSize = strlen($chunk);
        $bytesUploaded += $chunkSize;

        try {
            $status = $media->nextChunk($chunk);
        } catch (Exception $e) {
            fclose($handle);
            throw new Exception("Upload failed: " . $e->getMessage());
        }

        $now = microtime(true);
        $chunkTime = $now - $chunkStart;

        // Update progress every 0.5 seconds or every chunk (whichever is less frequent)
        if ($now - $lastUpdate > 0.5) {
            $lastUpdate = $now;

            if ($fileSize > 0) {
                $percentage = ($bytesUploaded / $fileSize) * 100;
                $elapsed = $now - $startTime;
                $uploadSpeed = $elapsed > 0 ? ($bytesUploaded / (1024 * 1024)) / $elapsed : 0;

                // Calculate ETA
                $eta = '';
                if ($uploadSpeed > 0 && $percentage > 1) {
                    $remaining_mb = ($fileSize - $bytesUploaded) / (1024 * 1024);
                    $eta_seconds = $remaining_mb / $uploadSpeed;

                    // FIX: Round the float to an integer before passing to gmdate()
                    $eta = " • ETA: " . gmdate("H:i:s", round($eta_seconds));
                }

                $chunkSpeedMB = $chunkTime > 0 ? ($chunkSize / (1024 * 1024)) / $chunkTime : 0;
                $speedInfo = " • " . number_format($uploadSpeed, 1) . " MB/s avg";

                $script = "<script>
                    updateUploadProgress(" . round($percentage, 1) . ", '" . number_format($bytesUploaded / (1024 * 1024), 1) . " MB of " . number_format($fileSize / (1024 * 1024), 1) . " MB{$speedInfo}{$eta}');
                </script>";
                sendProgressUpdate($script);
            }
        }
    }

    fclose($handle);
    $client->setDefer(false);

    if (!$status) {
        throw new Exception("Upload failed - no response from Google Drive");
    }

    $totalTime = microtime(true) - $startTime;
    $avgUploadSpeed = ($fileSize / (1024 * 1024)) / $totalTime;

    sendProgressUpdate("<script>
        updateUploadProgress(100, 'Complete! Avg: " . number_format($avgUploadSpeed, 1) . " MB/s');
        updateStatus('🔒 Setting file permissions...');
    </script>");

    // Make file publicly accessible
    $permission = new Google_Service_Drive_Permission(['type' => 'anyone', 'role' => 'reader']);
    $driveService->permissions->create($status->id, $permission);

    return $driveService->files->get($status->id, ['fields' => 'id, webContentLink, webViewLink']);
}

// --- Main Script Logic ---
session_start();
$client = getAuthenticatedClient();

if (!$client->getAccessToken()) {
    $authUrl = $client->createAuthUrl();
    die("<h2>One-Time Setup Required</h2>
    <p>Please click the link below to authorize this application. After authorizing, you will be redirected back here to start the upload.</p>
    <p><a href='$authUrl'>Authorize with Google</a></p>");
}

if (!isset($_GET['url'])) {
    die("<h2>Error</h2>
    <p>URL parameter is missing. Please provide a URL to upload.</p>");
}

$url = filter_var($_GET['url'], FILTER_VALIDATE_URL);
if (!$url) {
    die("<h2>Error</h2>
    <p>Invalid URL provided.</p>");
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>High-Speed Upload to Google Drive</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            margin: 0;
            padding: 20px;
        }

        .container {
            background-color: #fff;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
            width: 90%;
            max-width: 750px;
            text-align: center;
            backdrop-filter: blur(10px);
        }

        h2 {
            color: #2d3748;
            margin-bottom: 30px;
            font-size: 2em;
        }

        #status-message {
            font-size: 1.2em;
            color: #4a5568;
            margin: 25px 0;
            min-height: 35px;
            font-weight: 600;
            padding: 20px;
            background: linear-gradient(135deg, #f7fafc, #edf2f7);
            border-radius: 12px;
            border-left: 5px solid #3182ce;
        }

        .progress-section {
            margin: 30px 0;
            text-align: left;
        }

        .progress-label {
            font-weight: 700;
            color: #2d3748;
            margin-bottom: 12px;
            display: block;
            font-size: 1.1em;
        }

        .progress-bar-container {
            width: 100%;
            background-color: #e2e8f0;
            border-radius: 12px;
            overflow: hidden;
            height: 16px;
            position: relative;
            box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .progress-bar {
            height: 100%;
            transition: width 0.2s ease-out;
            position: relative;
            border-radius: 12px;
        }

        #download-progress-bar {
            background: linear-gradient(90deg, #3182ce, #63b3ed, #90cdf4);
            background-size: 200% 100%;
            animation: shimmer 2s infinite;
        }

        #upload-progress-bar {
            background: linear-gradient(90deg, #38a169, #68d391, #9ae6b4);
            background-size: 200% 100%;
            animation: shimmer 2s infinite;
            height: 24px;
        }

        .upload-progress-container .progress-bar-container {
            height: 24px;
        }

        @keyframes shimmer {
            0% {
                background-position: -200% 0;
            }

            100% {
                background-position: 200% 0;
            }
        }

        #progress-text {
            margin-top: 12px;
            font-weight: 700;
            color: #38a169;
            font-size: 1.2em;
        }

        #download-info {
            margin-top: 12px;
            font-size: 1em;
            color: #718096;
            font-weight: 500;
        }

        #result-container {
            margin-top: 30px;
            padding: 25px;
            background: linear-gradient(135deg, #f0fff4, #c6f6d5);
            border: 3px solid #9ae6b4;
            border-radius: 15px;
            word-wrap: break-word;
            text-align: left;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
        }

        #result-container a {
            color: #2b6cb0;
            text-decoration: none;
            font-weight: 600;
        }

        #result-container a:hover {
            text-decoration: underline;
        }

        .error {
            background: linear-gradient(135deg, #fed7d7, #feb2b2);
            border-color: #fc8181;
        }

        .speed-indicator {
            display: inline-block;
            padding: 4px 8px;
            background-color: #4299e1;
            color: white;
            border-radius: 6px;
            font-size: 0.85em;
            font-weight: 600;
            margin-left: 8px;
        }

        .file-cached {
            background: linear-gradient(135deg, #fef5e7, #fed7aa);
            border-color: #f6ad55;
        }
    </style>
</head>

<body>
    <div class="container">
        <h2>⚡ High-Speed File Transfer</h2>
        <div id="status-message">🔍 Checking for existing file...</div>

        <div class="progress-section">
            <span class="progress-label">📥 High-Speed Download</span>
            <div class="progress-bar-container">
                <div id="download-progress-bar" class="progress-bar" style="width: 0%;"></div>
            </div>
            <div id="download-info">Checking local cache...</div>
        </div>

        <div class="progress-section upload-progress-container">
            <span class="progress-label">☁️ Optimized Google Drive Upload</span>
            <div class="progress-bar-container">
                <div id="upload-progress-bar" class="progress-bar" style="width: 0%;"></div>
            </div>
            <div id="progress-text">0%</div>
        </div>

        <div id="result-container" style="display:none;"></div>
    </div>
    <script>
        function updateStatus(message) {
            document.getElementById('status-message').innerHTML = message;
        }

        function updateDownloadProgress(percentage, sizeInfo, totalSize) {
            document.getElementById('download-info').innerHTML = sizeInfo;

            if (totalSize > 0 && percentage > 0) {
                document.getElementById('download-progress-bar').style.width = Math.min(100, percentage) + '%';
            }

            if (totalSize === 0 && sizeInfo.includes('MB')) {
                const mb = parseFloat(sizeInfo);
                const estimatedWidth = Math.min(95, (mb / 20) * 100);
                document.getElementById('download-progress-bar').style.width = estimatedWidth + '%';
            }
        }

        function updateUploadProgress(percentage, info = '') {
            const percent = Math.min(100, Math.max(0, percentage));
            document.getElementById('upload-progress-bar').style.width = percent + '%';
            document.getElementById('progress-text').innerHTML = Math.round(percent) + '%' + (info ? ' • ' + info : '');
        }

        function showResult(htmlContent, isError = false) {
            document.getElementById('status-message').innerHTML = isError ?
                '❌ Transfer Failed' : '✅ High-Speed Transfer Completed!';

            const resultContainer = document.getElementById('result-container');
            resultContainer.innerHTML = htmlContent;
            resultContainer.className = isError ? 'error' : '';
            resultContainer.style.display = 'block';
        }

        function markFileCached() {
            document.getElementById('download-progress-bar').style.width = '100%';
            document.getElementById('download-info').innerHTML = '📋 File found in local cache - skipping download';
            document.getElementById('result-container').classList.add('file-cached');
        }

        // Performance monitoring
        let transferStartTime = Date.now();

        // Prevent page caching
        window.addEventListener('beforeunload', function() {
            // Clear cache
        });
    </script>
</body>

</html>
<?php
sendProgressUpdate("");

try {
    // Check if file already exists locally
    $fileCheck = checkFileExists($url);
    $fileName = $fileCheck['name'];
    $filePath = $fileCheck['path'];

    if ($fileCheck['exists']) {
        // File exists, skip download
        $fileSizeMB = number_format($fileCheck['size'] / (1024 * 1024), 2);

        sendProgressUpdate("<script>
            updateStatus('📋 File found in local cache! Skipping download...');
            markFileCached();
        </script>");

        sendProgressUpdate("<script>
            updateStatus('📋 File cached locally ({$fileSizeMB} MB). Proceeding to upload...');
        </script>");
    } else {
        // File doesn't exist, proceed with download
        sendProgressUpdate("<script>
            updateStatus('📁 File not found in cache. Starting download...');
        </script>");

        $filePath = downloadRemoteFileOptimized($url, $filePath);
    }

    // High-speed upload (same for both cached and downloaded files)
    $uploadedFile = uploadToDriveOptimized($client, $filePath, $fileName);
    $googleDownloadLink = $uploadedFile->getWebContentLink();

    $fileSize = filesize($filePath);
    $fileSizeMB = number_format($fileSize / (1024 * 1024), 2);

    $cacheStatus = $fileCheck['exists'] ? ' (📋 From Cache)' : ' (🆕 Downloaded)';

    $successMessage = "<h4>🎉 High-Speed Transfer Completed!</h4>"
        . "<p><strong>📁 File Name:</strong> " . htmlspecialchars($fileName) . "</p>"
        . "<p><strong>📊 File Size:</strong> {$fileSizeMB} MB{$cacheStatus}</p>"
        . "<p><strong>🔗 Google Drive Link:</strong><br><a href='" . htmlspecialchars($googleDownloadLink) . "' target='_blank' rel='noopener noreferrer'>" . htmlspecialchars($googleDownloadLink) . "</a></p>"
        . "<p><strong>💾 Local Storage:</strong> File saved in " . htmlspecialchars(LOCAL_FILES_DIR) . "</p>"
        . "<p><small><strong>ℹ️ Note:</strong> Large files may show a virus scan warning before downloading from Google Drive. This is normal.</small></p>";

    sendProgressUpdate('<script>showResult(' . json_encode($successMessage) . ');</script>');
} catch (Exception $e) {
    $errorMessage = '<div style="color:#e53e3e;"><strong>❌ Transfer Error:</strong><br>' . htmlspecialchars($e->getMessage()) . '</div>';
    sendProgressUpdate('<script>showResult(' . json_encode($errorMessage) . ', true);</script>');
} finally {
    // Note: We don't delete the file anymore since we want to keep it cached
    // The file remains in ../unpub/files/ for future use
}
?>