<?php
/**
 * ============================================================
 * Database Configuration & Connection Class
 * ============================================================
 *
 * Supports TWO SEPARATE MySQL databases with independent
 * credentials for each:
 *   - IPTV database (categories/channels)
 *   - Support database (support messages)
 *
 * Uses environment variables or inline defaults.
 *
 * @package IPTV Platform
 * @version 2.0.0
 */

declare(strict_types=1);

namespace IPTV\Config;

use PDO;
use PDOException;

class Database
{
    private static ?Database $instance = null;
    private ?PDO $pdoIptv = null;
    private ?PDO $pdoSupport = null;

    /**
     * Default IPTV database credentials.
     * Override with env vars: DB_IPTV_HOST, DB_IPTV_PORT, DB_IPTV_USER, DB_IPTV_PASS, DB_IPTV_NAME
     */
    private const DEFAULT_IPTV = [
        'host'     => 'localhost',
        'port'     => 3306,
        'user'     => 'root',
        'pass'     => '',
        'name'     => 'iptv_db',
    ];

    /**
     * Default Support database credentials.
     * Override with env vars: DB_SUPPORT_HOST, DB_SUPPORT_PORT, DB_SUPPORT_USER, DB_SUPPORT_PASS, DB_SUPPORT_NAME
     */
    private const DEFAULT_SUPPORT = [
        'host'     => 'localhost',
        'port'     => 3306,
        'user'     => 'root',
        'pass'     => '',
        'name'     => 'support_db',
    ];

    private function __construct()
    {
        $charset = 'utf8mb4';

        // IPTV database credentials
        $iptvHost  = getenv('DB_IPTV_HOST') ?: self::DEFAULT_IPTV['host'];
        $iptvPort  = getenv('DB_IPTV_PORT') ?: self::DEFAULT_IPTV['port'];
        $iptvUser  = getenv('DB_IPTV_USER') ?: self::DEFAULT_IPTV['user'];
        $iptvPass  = getenv('DB_IPTV_PASS') ?: self::DEFAULT_IPTV['pass'];
        $iptvName  = getenv('DB_IPTV_NAME') ?: self::DEFAULT_IPTV['name'];

        // Support database credentials
        $suppHost  = getenv('DB_SUPPORT_HOST') ?: self::DEFAULT_SUPPORT['host'];
        $suppPort  = getenv('DB_SUPPORT_PORT') ?: self::DEFAULT_SUPPORT['port'];
        $suppUser  = getenv('DB_SUPPORT_USER') ?: self::DEFAULT_SUPPORT['user'];
        $suppPass  = getenv('DB_SUPPORT_PASS') ?: self::DEFAULT_SUPPORT['pass'];
        $suppName  = getenv('DB_SUPPORT_NAME') ?: self::DEFAULT_SUPPORT['name'];

        $dsnIptv = "mysql:host={$iptvHost};port={$iptvPort};dbname={$iptvName};charset={$charset}";
        $dsnSupp = "mysql:host={$suppHost};port={$suppPort};dbname={$suppName};charset={$charset}";

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci",
        ];

        try {
            $this->pdoIptv = new PDO($dsnIptv, $iptvUser, $iptvPass, $options);
        } catch (PDOException $e) {
            error_log("IPTV Database connection failed: " . $e->getMessage());
            throw new \RuntimeException("Database connection to iptv_db failed: " . $e->getMessage());
        }

        try {
            $this->pdoSupport = new PDO($dsnSupp, $suppUser, $suppPass, $options);
        } catch (PDOException $e) {
            error_log("Support Database connection failed: " . $e->getMessage());
            throw new \RuntimeException("Database connection to support_db failed: " . $e->getMessage());
        }
    }

    private function __clone() {}

    public function __wakeup()
    {
        throw new \RuntimeException("Cannot unserialize a singleton.");
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getIptvConnection(): PDO
    {
        return $this->pdoIptv;
    }

    public function getSupportConnection(): PDO
    {
        return $this->pdoSupport;
    }
}
