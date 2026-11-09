<?php
/**
 * ============================================================
 * IPTV Platform - Main Entry Point
 * ============================================================
 *
 * Handles API routing, standalone player page, diagnostics,
 * and serving the Vue SPA.
 *
 * Auto-detects subdirectory installation path.
 * No separate index.html needed — HTML is embedded here.
 *
 * @package IPTV Platform
 * @version 3.0.0
 */

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('log_errors', '1');

// ============================================================
// Auto-detect base path for subdirectory installs
// ============================================================
$scriptName = isset($_SERVER['SCRIPT_NAME']) ? $_SERVER['SCRIPT_NAME'] : '/index.php';
$basePath = rtrim(dirname($scriptName), '/');

if ($basePath === '' || $basePath === '\\' || $basePath === '/') {
    $basePath = '';
} else {
    $basePath = '/' . ltrim($basePath, '/');
}

$apiBase = rtrim($basePath . '/api', '/');
$routerBase = $basePath ? $basePath : '/';

// ============================================================
// Route: Parse the request
// ============================================================
$requestUri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '/';
$requestPath = parse_url($requestUri, PHP_URL_PATH);
$method = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'GET';

// Strip base path from request
$relativePath = $requestPath;
if ($basePath !== '' && strpos($requestPath, $basePath) === 0) {
    $relativePath = substr($requestPath, strlen($basePath));
}
$relativePath = '/' . ltrim($relativePath, '/');

// Check if this is an API request
$isApiRequest = (strpos($relativePath, '/api') === 0);

// ============================================================
// API Request Handler
// ============================================================
if ($isApiRequest) {
    $allowedOrigins = [
        'http://localhost:5173',
        'http://localhost:3000',
        'http://127.0.0.1:5173',
        'http://127.0.0.1:3000',
    ];

    $origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';
    if (in_array($origin, $allowedOrigins, true)) {
        header("Access-Control-Allow-Origin: " . $origin);
    }

    header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
    header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Max-Age: 86400');
    header('X-Content-Type-Options: nosniff');

    if ($method === 'OPTIONS') {
        http_response_code(204);
        exit;
    }

    $path = preg_replace('#^/api/?#', '', $relativePath);
    $path = rtrim($path, '/');

    $segments = explode('/', $path);
    $segments = array_filter($segments, function($s) { return $s !== ''; });
    $segments = array_values($segments);

    // PSR-4 Autoloader
    spl_autoload_register(function ($class) {
        $prefix = 'IPTV\\';
        $baseDir = __DIR__ . '/';
        $len = strlen($prefix);
        if (strncmp($prefix, $class, $len) !== 0) {
            return;
        }
        $relativeClass = substr($class, $len);
        $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
        if (file_exists($file)) {
            require_once $file;
        }
    });

    try {
        switch (isset($segments[0]) ? $segments[0] : '') {
            case 'categories':
                $controller = new \IPTV\Controllers\ChannelController();
                $controller->getCategories();
                break;

            case 'channels':
                if ($method !== 'GET') {
                    http_response_code(405);
                    echo json_encode(array('success' => false, 'message' => 'Method not allowed'));
                    break;
                }
                $controller = new \IPTV\Controllers\ChannelController();
                $controller->getChannelsByCategory();
                break;

            case 'channel':
                if ($method !== 'GET') {
                    http_response_code(405);
                    echo json_encode(array('success' => false, 'message' => 'Method not allowed'));
                    break;
                }
                $controller = new \IPTV\Controllers\ChannelController();
                $controller->getChannelById();
                break;

            case 'search':
                if ($method !== 'GET') {
                    http_response_code(405);
                    echo json_encode(array('success' => false, 'message' => 'Method not allowed'));
                    break;
                }
                $controller = new \IPTV\Controllers\SearchController();
                $controller->search();
                break;

            case 'support':
                $controller = new \IPTV\Controllers\SupportController();
                $controller->submit();
                break;

            case 'add-channel':
                if ($method !== 'POST') {
                    http_response_code(405);
                    echo json_encode(array('success' => false, 'message' => 'Method not allowed'));
                    break;
                }
                $controller = new \IPTV\Controllers\ChannelController();
                $controller->addChannel();
                break;

            case 'delete-channel':
                if ($method !== 'POST') {
                    http_response_code(405);
                    echo json_encode(array('success' => false, 'message' => 'Method not allowed'));
                    break;
                }
                $controller = new \IPTV\Controllers\ChannelController();
                $controller->deleteChannel();
                break;

            case 'proxy':
                if ($method !== 'GET') {
                    http_response_code(405);
                    echo json_encode(array('success' => false, 'message' => 'Method not allowed'));
                    break;
                }
                $controller = new \IPTV\Controllers\StreamProxyController();
                $controller->proxy();
                break;

            case 'play':
                // Standalone player page (returns HTML, not JSON)
                header('Content-Type: text/html; charset=utf-8');
                $controller = new \IPTV\Controllers\PlayController();
                $controller->play();
                break;

            case 'diag':
                // Full server diagnostics
                header('Content-Type: application/json; charset=utf-8');
                $controller = new \IPTV\Controllers\StreamProxyController();
                $controller->diagnostics();
                break;

            case 'proxy-test':
                // Stream connectivity test
                header('Content-Type: application/json; charset=utf-8');
                $testUrl = $_GET['url'] ?? '';
                $results = array(
                    'curl_available' => function_exists('curl_init'),
                    'allow_url_fopen' => ini_get('allow_url_fopen'),
                    'php_version' => phpversion(),
                    'test_url' => $testUrl,
                );

                if (empty($testUrl)) {
                    $results['status'] = 'no_url';
                    $results['message'] = 'Provide ?url= parameter to test';
                    echo json_encode($results, JSON_UNESCAPED_UNICODE);
                    break;
                }

                $testUrl = urldecode($testUrl);

                // Try cURL
                if (function_exists('curl_init')) {
                    $ch = curl_init($testUrl);
                    curl_setopt_array($ch, array(
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_FOLLOWLOCATION => false,
                        CURLOPT_CONNECTTIMEOUT => 5,
                        CURLOPT_TIMEOUT => 10,
                        CURLOPT_SSL_VERIFYPEER => false,
                        CURLOPT_SSL_VERIFYHOST => 0,
                        CURLOPT_USERAGENT => 'Mozilla/5.0',
                        CURLOPT_HEADER => true,
                        CURLOPT_NOBODY => true,
                    ));
                    curl_exec($ch);
                    $results['curl_http_code'] = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                    $results['curl_error'] = curl_error($ch);
                    $results['curl_content_type'] = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
                    curl_close($ch);
                }

                // Try file_get_contents
                if (ini_get('allow_url_fopen')) {
                    $ctx = stream_context_create(array(
                        'http' => array(
                            'method' => 'GET',
                            'timeout' => 10,
                            'header' => "User-Agent: Mozilla/5.0\r\n",
                        ),
                        'ssl' => array(
                            'verify_peer' => false,
                            'verify_peer_name' => false,
                        ),
                    ));
                    $headers = @get_headers($testUrl, 0, $ctx);
                    $results['fgc_headers'] = $headers ? $headers[0] : 'failed';
                }

                echo json_encode($results, JSON_UNESCAPED_UNICODE);
                break;

            default:
                http_response_code(404);
                echo json_encode(array('success' => false, 'message' => 'Endpoint not found'));
                break;
        }
    } catch (\Throwable $e) {
        error_log("API Error: " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine());
        http_response_code(500);
        echo json_encode(array(
            'success' => false,
            'message' => 'An internal server error occurred. Please try again later.',
        ));
    }
    exit;
}

// ============================================================
// SPA: Serve Vue frontend (HTML embedded directly)
// ============================================================
header('Content-Type: text/html; charset=utf-8');
header('X-Content-Type-Options: nosniff');

// Build the base path JS
$jsApiBase = str_replace("'", "\'", $apiBase);
$jsRouterBase = str_replace("'", "\'", $routerBase);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <link rel="icon" type="image/svg+xml" href="<?php echo $basePath ? $basePath . '/' : '/'; ?>favicon.svg" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="description" content="NexoTV - Live Streaming Platform. Watch live channels across 32+ categories." />
    <meta name="theme-color" content="#141414" />
    <title>NexoTV - Live Streaming</title>
    <script>
        window.IPTV_API_BASE = '<?php echo $jsApiBase; ?>';
        window.IPTV_ROUTER_BASE = '<?php echo $jsRouterBase; ?>';
    </script>
    <link rel="stylesheet" crossorigin href="<?php echo $basePath ? $basePath . '/' : '/'; ?>assets/index-Bdk7Z3u6.css">
</head>
<body>
    <div id="app"></div>
    <script type="module" crossorigin src="<?php echo $basePath ? $basePath . '/' : '/'; ?>assets/index-DC4evAGJ.js"></script>
</body>
</html>
