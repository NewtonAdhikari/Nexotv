<?php
/**
 * ============================================================
 * Standalone Stream Player (v3 - DIRECT PLAYBACK)
 * ============================================================
 *
 * KEY INSIGHT: The hosting server blocks outbound connections
 * to streaming ports. So the PHP proxy CANNOT work.
 *
 * Solution: Play streams DIRECTLY in the browser.
 * The browser makes its own connection to the stream server,
 * bypassing the hosting firewall entirely.
 *
 * Fallback chain:
 *   1. Direct playback (browser → stream server)
 *   2. If CORS blocks it → show "Open in New Tab" button
 *   3. If hosting firewall also blocks → user sees clear message
 *
 * GET /api/play?url=<encoded_url>&name=<encoded_name>
 *
 * @package IPTV Platform
 * @version 3.0.0
 */

$streamUrl = isset($_GET['url']) ? $_GET['url'] : '';
$channelName = isset($_GET['name']) ? $_GET['name'] : 'Stream';
$streamUrl = urldecode($streamUrl);
$channelName = urldecode($channelName);

if (empty($streamUrl) || preg_match('#^https?://#i', $streamUrl) !== 1) {
    http_response_code(400);
    echo '<html><body style="background:#000;color:#fff;font-family:sans-serif;text-align:center;padding:50px"><h2>Invalid stream URL</h2></body></html>';
    exit;
}

// Detect format - use PATH only (strip query string) for extension checks
$parsed = parse_url($streamUrl);
$pathOnly = isset($parsed['path']) ? strtolower($parsed['path']) : strtolower($streamUrl);
$fullLower = strtolower($streamUrl);

$isHls = (substr($pathOnly, -5) === '.m3u8');
$isHtml = (substr($pathOnly, -5) === '.html' || substr($pathOnly, -4) === '.htm');
$isVideo = preg_match('/\.(mp4|webm|ogg|ogv|mov|mkv|avi|3gp|flv|wmv|m4v)$/', $pathOnly);

// HTML embeds and YouTube/Vimeo → simple iframe
if ($isHtml || preg_match('#(youtube\.com|youtu\.be|vimeo\.com)#i', $fullLower)) {
    header('Content-Type: text/html; charset=utf-8');
    $embedUrl = htmlspecialchars($streamUrl, ENT_QUOTES, 'UTF-8');
    $safeName = htmlspecialchars($channelName, ENT_QUOTES, 'UTF-8');
    echo '<!DOCTYPE html><html><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0"><title>' . $safeName . '</title><style>*{margin:0;padding:0}body{background:#000}iframe{width:100%;height:100vh;border:none}</style></head><body><iframe src="' . $embedUrl . '" allowfullscreen allow="autoplay;encrypted-media;fullscreen;picture-in-picture"></iframe></body></html>';
    exit;
}

$safeName = htmlspecialchars($channelName, ENT_QUOTES, 'UTF-8');

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $safeName; ?></title>
    <style>
        *{margin:0;padding:0;box-sizing:border-box}
        html,body{background:#000;color:#fff;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;overflow:hidden;height:100%;width:100%;margin:0}
        #wrap{width:100%;height:100%;display:flex;flex-direction:column}

        /* Top status bar */
        #topbar{display:flex;align-items:center;gap:8px;padding:6px 14px;background:rgba(0,0,0,.9);font-size:12px;border-bottom:1px solid rgba(255,255,255,.06)}
        .dot{width:8px;height:8px;border-radius:50%;flex-shrink:0}
        .dot.g{background:#555} .dot.y{background:#f59e0b;animation:p 1s infinite} .dot.gr{background:#22c55e} .dot.r{background:#e50914}
        @keyframes p{0%,100%{opacity:1}50%{opacity:.3}}
        #st{color:#aaa;flex:1;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
        #mode{padding:2px 8px;background:rgba(255,255,255,.06);color:#888;font-size:10px;font-weight:700;border-radius:3px;text-transform:uppercase;flex-shrink:0}

        /* Video */
        #va{flex:1;position:relative;background:#000;min-height:0}
        video{width:100%;height:100%;display:block;object-fit:contain}

        /* Overlays */
        .ov{position:absolute;top:0;left:0;right:0;bottom:0;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:14px;padding:30px;text-align:center;z-index:10}
        .ov.hid{display:none}
        #loader .sp{width:44px;height:44px;border:3px solid rgba(255,255,255,.1);border-top-color:#e50914;border-radius:50%;animation:spin .8s linear infinite}
        @keyframes spin{to{transform:rotate(360deg)}}
        #loader p{color:#888;font-size:13px}

        /* Error */
        #err h2{color:#e5e5e5;font-size:18px}
        #err p{color:#888;font-size:13px;max-width:480px;line-height:1.6}
        #err .info{background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);border-radius:8px;padding:12px 16px;text-align:left;width:100%;max-width:480px;font-size:11px;line-height:1.7;color:#666;word-break:break-all}
        #err .info strong{color:#999}
        .btns{display:flex;gap:10px;flex-wrap:wrap;justify-content:center;margin-top:6px}
        .b{display:inline-flex;align-items:center;gap:6px;padding:11px 20px;border-radius:6px;font-size:13px;font-weight:600;text-decoration:none;cursor:pointer;border:none;color:#fff;transition:background .2s}
        .b.r{background:#e50914}.b.r:hover{background:#c40812}
        .b.s{background:transparent;color:#b3b3b3;border:1px solid rgba(255,255,255,.15)}.b.s:hover{color:#fff;border-color:rgba(255,255,255,.3)}
        .b.blu{background:#2563eb}.b.blu:hover{background:#1d4ed8}
    </style>
</head>
<body>
<div id="wrap">
    <div id="topbar">
        <div class="dot g" id="dot"></div>
        <span id="st">Initializing...</span>
        <span id="mode">DIRECT</span>
    </div>
    <div id="va">
        <video id="v" controls autoplay playsinline></video>
        <div class="ov" id="loader">
            <div class="sp"></div>
            <p id="loadmsg">Loading stream...</p>
        </div>
        <div class="ov hid" id="err">
            <h2 id="et">Stream Error</h2>
            <p id="em">Could not load this stream.</p>
            <div class="info" id="einfo" style="display:none"></div>
            <div class="btns">
                <a id="directbtn" class="b r" href="#" target="_blank">&#9654; Open Stream Directly</a>
                <button class="b s" onclick="retry()">&#8635; Retry</button>
                <button class="b blu" onclick="tryProxy()">&#128268; Try Server Proxy</button>
            </div>
        </div>
    </div>
</div>

<?php if ($isHls): ?>
<script src="https://cdn.jsdelivr.net/npm/hls.js@1.5.7/dist/hls.min.js"></script>
<?php endif; ?>

<script>
(function(){
    var V = document.getElementById('v');
    var dot = document.getElementById('dot');
    var stEl = document.getElementById('st');
    var loadmsg = document.getElementById('loadmsg');
    var loader = document.getElementById('loader');
    var err = document.getElementById('err');
    var et = document.getElementById('et');
    var em = document.getElementById('em');
    var einfo = document.getElementById('einfo');
    var directbtn = document.getElementById('directbtn');
    var modeEl = document.getElementById('mode');

    var URL = <?php echo json_encode($streamUrl); ?>;
    var isHls = <?php echo $isHls ? 'true' : 'false'; ?>;
    var hls = null;
    var currentMode = ''; // 'direct', 'proxy'

    // Build proxy URL
    var scriptDir = '<?php echo rtrim(dirname(isset($_SERVER["SCRIPT_NAME"]) ? $_SERVER["SCRIPT_NAME"] : "/index.php"), "/"); ?>';

    directbtn.href = URL;

    function setDot(c){ dot.className = 'dot ' + c; }
    function setSt(t){ stEl.textContent = t; }
    function setMode(m){ modeEl.textContent = m; }
    function hideLoader(){ loader.classList.add('hid'); }
    function showErr(title, msg, info){
        hideLoader();
        et.textContent = title;
        em.textContent = msg;
        if(info){
            einfo.innerHTML = info;
            einfo.style.display = 'block';
        } else {
            einfo.style.display = 'none';
        }
        err.classList.remove('hid');
        setDot('r'); setSt('Error');
    }
    function hideErr(){
        err.classList.add('hid');
        einfo.style.display = 'none';
        loader.classList.remove('hid');
    }

    function destroy(){
        if(hls){ hls.destroy(); hls=null; }
        try { V.pause(); } catch(e){}
        V.removeAttribute('src');
        V.load();
    }

    // ========== PROXY URL BUILDER ==========
    function proxyUrl(url){
        return scriptDir + '/api/proxy?url=' + encodeURIComponent(url);
    }

    // ========== PLAY HLS ==========
    function playHls(useProxy){
        destroy(); hideErr();
        currentMode = useProxy ? 'proxy' : 'direct';
        setMode(useProxy ? 'PROXY' : 'DIRECT');
        setDot('y'); setSt(useProxy ? 'Connecting via server proxy...' : 'Connecting directly to stream...');
        loadmsg.textContent = useProxy ? 'Connecting via proxy...' : 'Connecting directly...';

        if(typeof Hls === 'undefined'){
            showErr('HLS.js not loaded', 'Could not load HLS.js from CDN. Check your internet connection.', '<strong>What to try:</strong><br>1. Refresh the page<br>2. Click "Open Stream Directly"<br>3. Check if your internet connection works');
            return;
        }

        if(!Hls.isSupported()){
            // Safari native HLS
            if(V.canPlayType && V.canPlayType('application/vnd.apple.mpegurl')){
                setSt('Using Safari native HLS...');
                V.src = useProxy ? proxyUrl(URL) : URL;
                V.addEventListener('loadedmetadata', function(){
                    hideLoader(); setDot('gr'); setSt('Playing');
                    V.play().catch(function(){ setSt('Tap play to start'); });
                }, {once:true});
                V.addEventListener('error', function(){
                    if(useProxy){
                        showErr('Proxy failed', 'Server proxy could not fetch this stream. Your hosting may block outbound connections.', '<strong>Stream URL:</strong> ' + URL + '<br><strong>Tip:</strong> Click "Open Stream Directly" to play in a new tab.');
                    } else {
                        showErr('Direct playback failed', 'Could not play this stream directly. The stream server may block cross-origin requests.', '<strong>Stream URL:</strong> ' + URL + '<br><strong>Tip:</strong> Click "Open Stream Directly" to play in a new tab, or try "Try Server Proxy".');
                    }
                }, {once:true});
                return;
            }
            showErr('HLS not supported', 'Your browser cannot play HLS streams.', '<strong>Stream URL:</strong> ' + URL + '<br><strong>Tip:</strong> Use Chrome, Firefox, Safari, or Edge.');
            return;
        }

        var config = {
            enableWorker: true,
            maxBufferLength: 30,
            maxMaxBufferLength: 60,
            startLevel: -1,
            fragLoadingTimeOut: 20000,
            manifestLoadingTimeOut: 15000,
            levelLoadingTimeOut: 15000
        };

        // Only use proxy xhrSetup if proxy mode
        if(useProxy){
            config.xhrSetup = function(xhr, url){
                xhr.open('GET', proxyUrl(url), true);
            };
        }

        hls = new Hls(config);

        hls.on(Hls.Events.MANIFEST_PARSED, function(){
            hideLoader();
            setDot('gr'); setSt('Buffering...');
            V.play().catch(function(){ setSt('Tap play to start'); });
        });

        hls.on(Hls.Events.FRAG_LOADED, function(){
            if(dot.className.indexOf('gr') === -1){
                hideLoader(); setDot('gr'); setSt(useProxy ? 'Playing via proxy' : 'Playing');
            }
        });

        hls.on(Hls.Events.ERROR, function(ev, data){
            console.log('[Player]', (useProxy?'PROXY':'DIRECT'), data.type, data.details, data.fatal);

            if(!data.fatal) return;

            if(data.type === Hls.ErrorTypes.MEDIA_ERROR){
                setSt('Recovering from media error...');
                hls.recoverMediaError();
                return;
            }

            if(data.type === Hls.ErrorTypes.NETWORK_ERROR){
                var detail = data.details;
                if(data.response){
                    detail += ' (HTTP ' + data.response.code + ')';
                    if(data.response.code === 0){
                        detail += ' — connection refused or timed out';
                    }
                }

                if(!useProxy){
                    // Direct mode failed → show error with option to try proxy
                    showErr('Direct connection failed',
                        'Could not connect to the stream server directly. ' + detail,
                        '<strong>Stream URL:</strong> ' + URL + '<br><br>' +
                        '<strong>Why this happens:</strong><br>' +
                        '• The stream server may block browser connections (CORS)<br>' +
                        '• The stream may be down or the URL may be wrong<br><br>' +
                        '<strong>What to try:</strong><br>' +
                        '1. Click <strong>"Open Stream Directly"</strong> to test the URL in a new tab<br>' +
                        '2. Click <strong>"Try Server Proxy"</strong> to route through your server<br>' +
                        '3. If the stream works in a new tab but not here, the server blocks cross-origin requests'
                    );
                } else {
                    // Proxy mode failed → hosting blocks outbound
                    showErr('Server proxy failed',
                        'Your hosting server cannot reach the stream. ' + detail,
                        '<strong>Stream URL:</strong> ' + URL + '<br><br>' +
                        '<strong>Why this happens:</strong><br>' +
                        '• Your hosting provider blocks outbound connections to streaming ports<br>' +
                        '• The stream server is down or unreachable from your server<br><br>' +
                        '<strong>What to try:</strong><br>' +
                        '1. Click <strong>"Open Stream Directly"</strong> — this is the best option<br>' +
                        '2. Contact your hosting provider to allow outbound connections<br>' +
                        '3. Use a VPS or dedicated server instead of shared hosting'
                    );
                }
                return;
            }

            // Other fatal errors
            showErr('Playback failed', data.details, '<strong>Mode:</strong> ' + (useProxy?'Proxy':'Direct') + '<br><strong>Detail:</strong> ' + data.details);
        });

        hls.loadSource(URL);
        hls.attachMedia(V);
    }

    // ========== PLAY NATIVE VIDEO ==========
    function playNative(useProxy){
        destroy(); hideErr();
        currentMode = useProxy ? 'proxy' : 'direct';
        setMode(useProxy ? 'PROXY' : 'DIRECT');
        setDot('y'); setSt('Loading video...');
        loadmsg.textContent = 'Loading video...';

        var src = useProxy ? proxyUrl(URL) : URL;
        V.src = src;
        V.load();

        V.addEventListener('loadeddata', function(){
            hideLoader(); setDot('gr'); setSt('Playing');
            V.play().catch(function(){});
        }, {once:true});

        V.addEventListener('canplay', function(){
            hideLoader(); setDot('gr'); setSt('Playing');
        }, {once:true});

        V.addEventListener('error', function(){
            var codes = {1:'Aborted',2:'Network error',3:'Decoding error',4:'Format not supported'};
            var msg = codes[V.error ? V.error.code : 0] || 'Unknown error';

            if(!useProxy){
                showErr('Video failed to load', msg,
                    '<strong>URL:</strong> ' + URL + '<br><br>' +
                    '<strong>Tip:</strong> Click "Open Stream Directly" to test in a new tab.');
            } else {
                showErr('Proxy video failed', msg,
                    '<strong>URL:</strong> ' + URL + '<br><br>' +
                    'Server proxy could not fetch this video. Try "Open Stream Directly".');
            }
        }, {once:true});
    }

    // ========== GLOBAL FUNCTIONS ==========
    window.retry = function(){
        if(isHls) playHls(currentMode === 'proxy');
        else playNative(currentMode === 'proxy');
    };

    window.tryProxy = function(){
        if(isHls) playHls(true);
        else playNative(true);
    };

    // ========== START ==========
    // Always start with DIRECT playback (no proxy)
    // The browser connects directly to the stream server
    if(isHls){
        playHls(false);  // false = direct, no proxy
    } else {
        playNative(false);
    }

})();
</script>
</body>
</html>
