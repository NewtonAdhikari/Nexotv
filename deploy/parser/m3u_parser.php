<?php
/**
 * ============================================================
 * M3U File Parser & Importer
 * ============================================================
 *
 * Reads .m3u files from the m3u_files/ directory.
 * The filename (without extension) maps to a category table name.
 * Extracts channel name, logo URL (tvg-logo), and stream URL,
 * then inserts them into the corresponding database table.
 *
 * Usage:
 *   php parser/m3u_parser.php
 *   php parser/m3u_parser.php --dir=/path/to/m3u/files
 *   php parser/m3u_parser.php --verbose
 *   php parser/m3u_parser.php --dry-run
 *
 * @package IPTV Platform
 * @version 1.0.0
 */

declare(strict_types=1);

define('IPTV_APP', true);

$baseDir = dirname(__DIR__);

spl_autoload_register(function (string $class) use ($baseDir): void {
    $prefix  = 'IPTV\\';
    $base    = $baseDir . '/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relativeClass = substr($class, $len);
    $file = $base . str_replace('\\', '/', $relativeClass) . '.php';

    if (file_exists($file)) {
        require_once $file;
    }
});

use IPTV\Config\Database;
use IPTV\Config\Categories;
use IPTV\Models\Channel;

$options = [
    'dir'     => $baseDir . '/m3u_files',
    'verbose' => false,
    'dry-run' => false,
];

foreach ($argv as $arg) {
    if (str_starts_with($arg, '--dir=')) {
        $options['dir'] = trim(substr($arg, 6));
    }
    if ($arg === '--verbose' || $arg === '-v') {
        $options['verbose'] = true;
    }
    if ($arg === '--dry-run') {
        $options['dry-run'] = true;
    }
}

class M3UParser
{
    private string $m3uDir;
    private bool   $verbose;
    private bool   $dryRun;
    private int    $totalInserted = 0;
    private int    $totalSkipped  = 0;
    private int    $totalErrors   = 0;
    private array  $errors = [];

    public function __construct(string $m3uDir, bool $verbose = false, bool $dryRun = false)
    {
        $this->m3uDir = rtrim($m3uDir, '/');
        $this->verbose = $verbose;
        $this->dryRun  = $dryRun;
    }

    public function run(): void
    {
        $this->log("========================================");
        $this->log("IPTV M3U Parser v1.0.0");
        $this->log("========================================");
        $this->log("M3U Directory: {$this->m3uDir}");
        $this->log("Mode: " . ($this->dryRun ? 'DRY RUN' : 'LIVE'));
        $this->log("");

        if (!is_dir($this->m3uDir)) {
            $this->log("ERROR: Directory not found: {$this->m3uDir}");
            exit(1);
        }

        $files = glob($this->m3uDir . '/*.m3u');

        if (empty($files)) {
            $this->log("No .m3u files found in: {$this->m3uDir}");
            exit(0);
        }

        $this->log("Found " . count($files) . " M3U file(s)");
        $this->log("");

        if (!$this->dryRun) {
            try {
                Database::getInstance();
                $this->log("Database connection established.");
            } catch (\RuntimeException $e) {
                $this->log("ERROR: " . $e->getMessage());
                exit(1);
            }
        }

        foreach ($files as $filePath) {
            $this->processFile($filePath);
        }

        $this->log("");
        $this->log("========================================");
        $this->log("IMPORT SUMMARY");
        $this->log("========================================");
        $this->log("Files processed: " . count($files));
        $this->log("Channels inserted: {$this->totalInserted}");
        $this->log("Channels skipped (duplicates): {$this->totalSkipped}");
        $this->log("Errors: {$this->totalErrors}");

        if (!empty($this->errors)) {
            $this->log("");
            $this->log("ERROR DETAILS:");
            foreach ($this->errors as $error) {
                $this->log("  - {$error}");
            }
        }

        $this->log("");
        $this->log("Done!");
    }

    private function processFile(string $filePath): void
    {
        $fileName = basename($filePath);
        $category = pathinfo($fileName, PATHINFO_FILENAME);

        $this->log("----------------------------------------");
        $this->log("Processing: {$fileName}");

        if (!Categories::isValid($category)) {
            $this->log("  SKIP: '{$category}' is not a valid category name.");
            $this->log("  Valid categories: " . implode(', ', Categories::getTableNames()));
            $this->totalErrors++;
            $this->errors[] = "File '{$fileName}': Invalid category '{$category}'";
            return;
        }

        $content = @file_get_contents($filePath);
        if ($content === false) {
            $this->log("  ERROR: Cannot read file.");
            $this->totalErrors++;
            $this->errors[] = "File '{$fileName}': Cannot read file";
            return;
        }

        $channels = $this->parseM3U($content, $category);

        $this->log("  Found " . count($channels) . " channel(s)");

        if (!$this->dryRun) {
            $channelModel = new Channel();
            $inserted = 0;
            $skipped  = 0;

            foreach ($channels as $ch) {
                $result = $channelModel->insert($category, $ch['name'], $ch['logo'], $ch['stream_url']);
                if ($result) {
                    $inserted++;
                } else {
                    $skipped++;
                    if ($this->verbose) {
                        $this->log("    SKIP (duplicate/invalid): {$ch['name']}");
                    }
                }
            }

            $this->totalInserted += $inserted;
            $this->totalSkipped  += $skipped;
            $this->log("  Inserted: {$inserted}, Skipped: {$skipped}");
        } else {
            $this->totalInserted += count($channels);
            $this->log("  [DRY RUN] Would insert: " . count($channels));
        }
    }

    private function parseM3U(string $content, string $category): array
    {
        $channels = [];
        $lines    = explode("\n", $content);
        $lineCount = count($lines);

        $hasHeader = false;
        foreach ($lines as $line) {
            if (trim($line) === '#EXTM3U') {
                $hasHeader = true;
                break;
            }
        }

        if (!$hasHeader) {
            $this->log("  WARNING: No #EXTM3U header found. Attempting to parse anyway.");
        }

        for ($i = 0; $i < $lineCount; $i++) {
            $line = trim($lines[$i]);

            if (!preg_match('/^#EXTINF/i', $line)) {
                continue;
            }

            $name = '';
            $logo = '';

            if (preg_match('/tvg-logo\s*=\s*"([^"]+)"/i', $line, $logoMatch)) {
                $logo = trim($logoMatch[1]);
            }

            $commaPos = strrpos($line, ',');
            if ($commaPos !== false) {
                $name = trim(substr($line, $commaPos + 1));
            }

            if (empty($name)) {
                $this->totalErrors++;
                $this->errors[] = "Category '{$category}' line " . ($i + 1) . ": Empty channel name";
                continue;
            }

            $streamUrl = '';
            for ($j = $i + 1; $j < $lineCount; $j++) {
                $nextLine = trim($lines[$j]);

                if (empty($nextLine) || str_starts_with($nextLine, '#')) {
                    continue;
                }

                $streamUrl = $nextLine;
                $i = $j;
                break;
            }

            if (empty($streamUrl)) {
                $this->totalErrors++;
                $this->errors[] = "Category '{$category}': No stream URL for channel '{$name}'";
                continue;
            }

            $channels[] = [
                'name'       => $name,
                'logo'       => $logo,
                'stream_url' => $streamUrl,
            ];
        }

        return $channels;
    }

    private function log(string $message): void
    {
        echo $message . "\n";
    }
}

$parser = new M3UParser($options['dir'], $options['verbose'], $options['dry-run']);
$parser->run();
