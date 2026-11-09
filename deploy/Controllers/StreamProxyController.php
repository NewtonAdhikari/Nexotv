<?php
/**
 * ============================================================
 * Stream Proxy Controller (v4 - FINAL FIX)
 * ============================================================
 *
 * Critical fixes for shared hosting:
 *   1. Completely disables ALL output buffering layers
 *   2. Sends data in raw binary (no encoding corruption)
 *   3. Proper Content-Type for each format
 *   4. Handles cURL correctly for streaming mode
 *
 * Compatible with PHP 7.2+ (no PHP 8+ functions used).
 *
 * @package IPTV Platform
 * @version 4.0.0
 */

namespace IPTV\Controllers;

class StreamProxyController
{
    /**
     * Proxy a stream URL to the browser.
     * GET /api/proxy?url=<encoded_url>
     */
    public function proxy()
    {
        // CRITICAL: Disable ALL output buffering on shared hosting
        // This is the #1 reason streams fail - data gets stuck in PHP's buffer
        @ini_set('output_buffering', 'off');
        @ini_set('zlib.output_compression', 'off');
        @ini_set('implicit_flush', '1');
        while (ob_get_level() > 0) {
            @ob_end_clean();
        }
        @ob_implicit_flush(1);

        @set_time_limit(0);

        $url = isset($_GET['url']) ? $_GET['url'] : '';

        if (empty($url)) {
            http_response_code(400);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(array('success' => false, 'message' => 'Missing url parameter'));
            return;
        }

        $url = urldecode(trim($url));

        if (!$this->isValidStreamUrl($url)) {
            http_response_code(403);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(array('success' => false, 'message' => 'Invalid or blocked stream URL'));
            return;
        }

        // Send CORS headers
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET');
        header('Access-Control-Allow-Headers: Content-Type, Range');
        header('Access-Control-Expose-Headers: Content-Length, Content-Type');
        header('X-Content-Type-Options: nosniff');

        // Detect and set correct Content-Type from URL extension
        $contentType = $this->detectContentType($url);
        header('Content-Type: ' . $contentType);

        // Disable zlib output compression (some hosts enable it globally)
        if (function_exists('apache_setenv')) {
            @apache_setenv('no-gzip', '1');
        }

        // Fetch the stream
        if (function_exists('curl_init')) {
            $this->fetchWithCurl($url);
        } elseif (ini_get('allow_url_fopen')) {
            $this->fetchWithFileGetContents($url);
        } else {
            http_response_code(500);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(array(
                'success' => false,
                'message' => 'No HTTP client available. cURL is disabled and allow_url_fopen is Off.'
            ));
        }
    }

    /**
     * Validate stream URL.
     */
    private function isValidStreamUrl($url)
    {
        if (preg_match('#^https?://#i', $url) !== 1) return false;

        $parsed = parse_url($url);
        if (!$parsed || empty($parsed['host'])) return false;

        $host = strtolower($parsed['host']);
        $blockedHosts = array('localhost', '127.0.0.1', '::1', '0.0.0.0');
        if (in_array($host, $blockedHosts, true)) return false;

        if (preg_match('/^(10\.|172\.(1[6-9]|2[0-9]|3[01])\.|192\.168\.|0\.)/', $host)) return false;

        $path = isset($parsed['path']) ? $parsed['path'] : '';
        return strlen($path) > 1;
    }

    /**
     * Fetch using cURL - PROPERLY configured for binary streaming.
     *
     * Key fixes:
     * - CURLOPT_BINARYTRANSFER ensures raw binary output
     * - No encoding (CURLOPT_ENCODING => 'identity') prevents corruption
     * - Write function manually flushes after each chunk
     */
    private function fetchWithCurl($url)
    {
        $ch = curl_init($url);
        if ($ch === false) {
            http_response_code(500);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(array('success' => false, 'message' => 'Failed to initialize cURL'));
            return;
        }

        curl_setopt_array($ch, array(
            // Binary transfer - CRITICAL for .ts segments
            CURLOPT_BINARYTRANSFER => true,
            // Do NOT buffer in memory - stream directly
            CURLOPT_RETURNTRANSFER => false,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS      => 5,
            CURLOPT_CONNECTTIMEOUT => 15,
            CURLOPT_TIMEOUT        => 0,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_USERAGENT      => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
            // CRITICAL: Do NOT accept encoding - raw binary only
            // Using 'identity' prevents cURL from auto-decompressing gzip streams
            CURLOPT_ENCODING       => 'identity',

            // Override content-type if remote sends a different one
            CURLOPT_HEADERFUNCTION => function ($ch, $header) {
                $len = strlen($header);
                $h = strtolower(trim($header));
                // Only forward Content-Type if remote sends one
                if (strpos($h, 'content-type:') === 0) {
                    @header(rtrim($header), true);
                }
                return $len;
            },

            // Write binary data directly to output - with proper flushing
            CURLOPT_WRITEFUNCTION => function ($ch, $data) {
                echo $data;
                // Force-flush: first clear PHP's output buffer, then flush to web server
                if (ob_get_level() > 0) {
                    ob_flush();
                }
                flush();
                return strlen($data);
            },
        ));

        $execResult = curl_exec($ch);

        // Check for errors (curl_exec returns true/false in streaming mode)
        if ($execResult === false) {
            $errno = curl_errno($ch);
            $curlError = curl_error($ch);

            if (!headers_sent()) {
                http_response_code(502);
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(array(
                    'success' => false,
                    'message' => 'cURL error: ' . $curlError . ' (' . $errno . ')'
                ));
            }
        }

        curl_close($ch);
    }

    /**
     * Fallback: fetch with file_get_contents.
     */
    private function fetchWithFileGetContents($url)
    {
        $context = stream_context_create(array(
            'http' => array(
                'method'          => 'GET',
                'follow_location' => true,
                'max_redirects'   => 5,
                'timeout'         => 60,
                'header'          => "User-Agent: Mozilla/5.0\r\n",
                'ignore_errors'   => true,
            ),
            'ssl' => array(
                'verify_peer'      => false,
                'verify_peer_name' => false,
            ),
        ));

        $content = @file_get_contents($url, false, $context);

        if ($content === false) {
            http_response_code(502);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(array('success' => false, 'message' => 'Fetch failed. Remote server did not respond.'));
            return;
        }

        echo $content;
        flush();
    }

    /**
     * Detect content type from URL extension.
     */
    private function detectContentType($url)
    {
        $path = parse_url($url, PHP_URL_PATH);
        if ($path === null) $path = '';
        $lower = strtolower($path);

        $types = array(
            '.m3u8' => 'application/vnd.apple.mpegurl',
            '.m3u'  => 'audio/x-mpegurl',
            '.ts'   => 'video/mp2t',
            '.m4s'  => 'video/iso.segment',
            '.mp4'  => 'video/mp4',
            '.m4v'  => 'video/mp4',
            '.webm' => 'video/webm',
            '.ogg'  => 'video/ogg',
            '.ogv'  => 'video/ogg',
            '.mp3'  => 'audio/mpeg',
            '.aac'  => 'audio/aac',
            '.flv'  => 'video/x-flv',
            '.mkv'  => 'video/x-matroska',
            '.avi'  => 'video/x-msvideo',
            '.mov'  => 'video/quicktime',
            '.mpd'  => 'application/dash+xml',
        );

        foreach ($types as $ext => $mime) {
            if (substr($lower, -strlen($ext)) === $ext) {
                return $mime;
            }
        }

        return 'application/octet-stream';
    }

    /**
     * Server diagnostics.
     * GET /api/diag[?test=<stream_url>]
     */
    public function diagnostics()
    {
        $results = array(
            'php_version' => phpversion(),
            'curl_available' => function_exists('curl_init'),
            'allow_url_fopen' => ini_get('allow_url_fopen'),
            'output_buffering' => ini_get('output_buffering'),
            'zlib_compression' => ini_get('zlib.output_compression'),
            'memory_limit' => ini_get('memory_limit'),
            'max_execution_time' => ini_get('max_execution_time'),
            'server_software' => isset($_SERVER['SERVER_SOFTWARE']) ? $_SERVER['SERVER_SOFTWARE'] : 'unknown',
            'base_path' => isset($_SERVER['SCRIPT_NAME']) ? $_SERVER['SCRIPT_NAME'] : 'unknown',
        );

        // Test database
        try {
            $configFile = __DIR__ . '/../Config/Database.php';
            if (file_exists($configFile)) {
                require_once $configFile;
                if (class_exists('IPTV\Config\Database')) {
                    $db = \IPTV\Config\Database::getInstance();
                    $conn = $db->getIptvConnection();
                    $conn->query("SELECT 1");
                    $results['db_connected'] = true;
                } else {
                    $results['db_connected'] = false;
                    $results['db_error'] = 'Database class not found';
                }
            } else {
                $results['db_connected'] = false;
                $results['db_error'] = 'Database.php not found at ' . $configFile;
            }
        } catch (\Exception $e) {
            $results['db_connected'] = false;
            $results['db_error'] = $e->getMessage();
        }

        // Count channels
        if (!empty($results['db_connected'])) {
            try {
                $conn = \IPTV\Config\Database::getInstance()->getIptvConnection();
                $tables = array('entertainment', 'news', 'kids', 'movies', 'sports', 'music', 'general');
                $results['channel_counts'] = array();
                foreach ($tables as $table) {
                    try {
                        $stmt = $conn->query("SELECT COUNT(*) FROM `" . $table . "`");
                        $results['channel_counts'][$table] = (int)$stmt->fetchColumn();
                    } catch (\Exception $e) {
                        $results['channel_counts'][$table] = 'table_missing';
                    }
                }
            } catch (\Exception $e) {
                $results['channel_counts_error'] = $e->getMessage();
            }
        }

        // Test a stream URL if provided
        if (function_exists('curl_init')) {
            $testUrl = isset($_GET['test']) ? urldecode($_GET['test']) : '';
            if (!empty($testUrl)) {
                $ch = curl_init($testUrl);
                curl_setopt_array($ch, array(
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_CONNECTTIMEOUT => 5,
                    CURLOPT_TIMEOUT => 10,
                    CURLOPT_SSL_VERIFYPEER => false,
                    CURLOPT_SSL_VERIFYHOST => 0,
                    CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                    CURLOPT_HEADER => true,
                    CURLOPT_NOBODY => false,
                    CURLOPT_ENCODING => 'identity',
                ));
                $response = curl_exec($ch);
                $results['proxy_test'] = array(
                    'url' => $testUrl,
                    'http_code' => curl_getinfo($ch, CURLINFO_HTTP_CODE),
                    'content_type' => curl_getinfo($ch, CURLINFO_CONTENT_TYPE),
                    'error' => curl_error($ch),
                    'reachable' => curl_getinfo($ch, CURLINFO_HTTP_CODE) >= 200 && curl_getinfo($ch, CURLINFO_HTTP_CODE) < 400,
                    'response_size' => strlen($response),
                    'response_preview' => strlen($response) > 200 ? substr($response, 0, 200) : $response,
                );
                curl_close($ch);
            }
        }

        $results['status'] = 'ok';
        echo json_encode($results, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }
}
