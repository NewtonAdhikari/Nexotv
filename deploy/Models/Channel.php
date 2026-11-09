<?php
/**
 * ============================================================
 * Channel Model
 * ============================================================
 *
 * Data access layer for channel operations across all category
 * tables. Every query uses prepared statements to prevent
 * SQL injection. Table names are validated against the
 * Categories whitelist before use.
 *
 * @package IPTV Platform
 * @version 1.0.0
 */

declare(strict_types=1);

namespace IPTV\Models;

use IPTV\Config\Database;
use IPTV\Config\Categories;
use PDO;
use PDOException;

class Channel
{
    /**
     * @var PDO IPTV database connection
     */
    private PDO $db;

    /**
     * Constructor - initializes the database connection.
     */
    public function __construct()
    {
        $this->db = Database::getInstance()->getIptvConnection();
    }

    /**
     * Fetch all channels from a specific category table.
     *
     * @param string $category The validated category table name
     * @return array<int, array{id: int, name: string, logo: string|null, stream_url: string, created_at: string}>
     */
    public function getAllByCategory(string $category): array
    {
        $category = strtolower(trim($category));

        if (!Categories::isValid($category)) {
            return [];
        }

        // Table name is validated against whitelist - safe to use in query
        $sql = "SELECT id, name, logo, stream_url, created_at FROM `{$category}` ORDER BY name ASC";

        try {
            $stmt = $this->db->query($sql);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Failed to fetch channels from {$category}: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Fetch a single channel by ID from a specific category table.
     *
     * @param string $category The validated category table name
     * @param int    $id       The channel ID
     * @return array{id: int, name: string, logo: string|null, stream_url: string, created_at: string}|null
     */
    public function getById(string $category, int $id): ?array
    {
        $category = strtolower(trim($category));

        if (!Categories::isValid($category)) {
            return null;
        }

        // Table name is validated against whitelist - safe to use in query
        $sql = "SELECT id, name, logo, stream_url, created_at FROM `{$category}` WHERE id = :id LIMIT 1";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetch();

            return $result ?: null;
        } catch (PDOException $e) {
            error_log("Failed to fetch channel {$id} from {$category}: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Search for channels by keyword across a specific category table.
     * Searches both the name and matches against a LIKE query.
     *
     * @param string $category The validated category table name
     * @param string $keyword  The search keyword (sanitized)
     * @return array<int, array{id: int, name: string, logo: string|null, stream_url: string, created_at: string, _category: string}>
     */
    public function search(string $category, string $keyword): array
    {
        $category = strtolower(trim($category));
        $keyword  = trim($keyword);

        if (!Categories::isValid($category) || empty($keyword)) {
            return [];
        }

        // Table name is validated against whitelist - safe to use in query
        $sql = "SELECT id, name, logo, stream_url, created_at FROM `{$category}` 
                WHERE name LIKE :keyword 
                ORDER BY name ASC 
                LIMIT 50";

        try {
            $stmt = $this->db->prepare($sql);
            $searchTerm = "%{$keyword}%";
            $stmt->bindValue(':keyword', $searchTerm, PDO::PARAM_STR);
            $stmt->execute();
            $results = $stmt->fetchAll();

            // Tag each result with its source category
            foreach ($results as &$row) {
                $row['_category'] = $category;
            }

            return $results;
        } catch (PDOException $e) {
            error_log("Search failed in {$category}: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Search across ALL category tables for a given keyword.
     * Iterates through every whitelisted table and aggregates results.
     *
     * @param string $keyword The search keyword
     * @return array<int, array{id: int, name: string, logo: string|null, stream_url: string, created_at: string, _category: string}>
     */
    public function searchAll(string $keyword): array
    {
        $keyword = trim($keyword);

        if (empty($keyword)) {
            return [];
        }

        $allResults = [];
        $categories = Categories::getTableNames();

        foreach ($categories as $cat) {
            $results = $this->search($cat, $keyword);
            if (!empty($results)) {
                $allResults = array_merge($allResults, $results);
            }
        }

        return $allResults;
    }

    /**
     * Get the count of channels in each category.
     *
     * @return array<string, int> Associative array: category => count
     */
    public function getCategoryCounts(): array
    {
        $counts = [];
        $categories = Categories::getTableNames();

        foreach ($categories as $cat) {
            try {
                $sql = "SELECT COUNT(*) as total FROM `{$cat}`";
                $stmt = $this->db->query($sql);
                $row = $stmt->fetch();
                $counts[$cat] = (int)($row['total'] ?? 0);
            } catch (PDOException $e) {
                $counts[$cat] = 0;
            }
        }

        return $counts;
    }

    /**
     * Insert a single channel into a category table.
     * Checks for duplicates by stream_url before inserting.
     *
     * @param string $category   The validated category table name
     * @param string $name       Channel name
     * @param string $logo       Channel logo URL
     * @param string $streamUrl  Stream URL
     * @return bool True on success, false on failure or duplicate
     */
    public function insert(string $category, string $name, string $logo, string $streamUrl): bool
    {
        $category  = strtolower(trim($category));
        $name      = trim($name);
        $logo      = trim($logo);
        $streamUrl = trim($streamUrl);

        if (!Categories::isValid($category) || empty($name) || empty($streamUrl)) {
            return false;
        }

        // Check for duplicate by stream_url
        $checkSql = "SELECT id FROM `{$category}` WHERE stream_url = :stream_url LIMIT 1";
        $checkStmt = $this->db->prepare($checkSql);
        $checkStmt->bindValue(':stream_url', $streamUrl, PDO::PARAM_STR);
        $checkStmt->execute();

        if ($checkStmt->fetch()) {
            return false; // Duplicate found
        }

        $sql = "INSERT INTO `{$category}` (name, logo, stream_url) VALUES (:name, :logo, :stream_url)";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':name', $name, PDO::PARAM_STR);
            $stmt->bindValue(':logo', $logo ?: null, PDO::PARAM_STR);
            $stmt->bindValue(':stream_url', $streamUrl, PDO::PARAM_STR);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Failed to insert channel into {$category}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Delete a channel by ID from a category table.
     *
     * @param string $category The validated category table name
     * @param int    $id       The channel ID
     * @return bool True on success, false if not found
     */
    public function deleteById(string $category, int $id): bool
    {
        $category = strtolower(trim($category));

        if (!Categories::isValid($category)) {
            return false;
        }

        $sql = "DELETE FROM `{$category}` WHERE id = :id LIMIT 1";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("Failed to delete channel {$id} from {$category}: " . $e->getMessage());
            return false;
        }
    }
}
