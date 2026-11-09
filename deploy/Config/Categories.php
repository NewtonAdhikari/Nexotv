<?php
/**
 * ============================================================
 * Category Whitelist Configuration
 * ============================================================
 *
 * This file defines ALL valid category table names in the system.
 * Every API request referencing a category MUST be validated
 * against this whitelist before any database query is executed.
 *
 * SECURITY: Never allow dynamic table names from user input
 * without checking against this whitelist first.
 *
 * @package IPTV Platform
 * @version 1.0.0
 */

declare(strict_types=1);

namespace IPTV\Config;

class Categories
{
    /**
     * Complete whitelist of valid category table names.
     * Each entry maps to an actual MySQL table in iptv_db.
     *
     * The key is the URL-safe category slug,
     * the value is the human-readable display name.
     *
     * @var array<string, string>
     */
    public const WHITELIST = [
        'animation'      => 'Animation',
        'auto'           => 'Auto',
        'business'       => 'Business',
        'classic'        => 'Classic',
        'comedy'         => 'Comedy',
        'cooking'        => 'Cooking',
        'culture'        => 'Culture',
        'documentary'    => 'Documentary',
        'education'      => 'Education',
        'entertainment'  => 'Entertainment',
        'family'         => 'Family',
        'general'        => 'General',
        'interactive'    => 'Interactive',
        'kids'           => 'Kids',
        'legislative'    => 'Legislative',
        'lifestyle'      => 'Lifestyle',
        'movies'         => 'Movies',
        'music'          => 'Music',
        'news'           => 'News',
        'outdoor'        => 'Outdoor',
        'premimum'       => 'Premium',
        'public'         => 'Public',
        'relax'          => 'Relax',
        'religious'      => 'Religious',
        'science'        => 'Science',
        'series'         => 'Series',
        'shop'           => 'Shop',
        'sports'         => 'Sports',
        'travel'         => 'Travel',
        'undefined'      => 'Undefined',
        'weather'        => 'Weather',
        'xxx'            => 'XXX',
    ];

    /**
     * Validate whether a given category slug exists in the whitelist.
     *
     * @param string $category The category slug to validate
     * @return bool True if valid, false otherwise
     */
    public static function isValid(string $category): bool
    {
        return array_key_exists(strtolower(trim($category)), self::WHITELIST);
    }

    /**
     * Get the display name for a given category slug.
     *
     * @param string $category The category slug
     * @return string|null The display name, or null if invalid
     */
    public static function getDisplayName(string $category): ?string
    {
        $category = strtolower(trim($category));
        return self::WHITELIST[$category] ?? null;
    }

    /**
     * Get all categories as an associative array (slug => display name).
     *
     * @return array<string, string>
     */
    public static function getAll(): array
    {
        return self::WHITELIST;
    }

    /**
     * Get all valid table names (just the slugs).
     *
     * @return string[]
     */
    public static function getTableNames(): array
    {
        return array_keys(self::WHITELIST);
    }
}
