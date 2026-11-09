<?php
declare(strict_types=1);

namespace IPTV\Controllers;

use IPTV\Models\Channel;
use IPTV\Config\Categories;

class SearchController
{
    private Channel $channelModel;

    public function __construct()
    {
        $this->channelModel = new Channel();
    }

    public function search(): void
    {
        $query = $_GET['q'] ?? '';

        if (empty($query)) {
            $this->sendResponse(400, 'Missing required parameter: q (search query)');
            return;
        }

        $query = trim($query);

        if (strlen($query) < 2) {
            $this->sendResponse(400, 'Search query must be at least 2 characters long');
            return;
        }

        $query = preg_replace('/[<>\\\\]/', '', $query);

        $results = $this->channelModel->searchAll($query);

        if (empty($results)) {
            $this->sendResponse(200, 'No channels found matching your search', [
                'query'   => $query,
                'results' => [],
                'total'   => 0,
            ]);
            return;
        }

        $grouped = [];
        foreach ($results as $channel) {
            $catSlug = $channel['_category'];
            if (!isset($grouped[$catSlug])) {
                $grouped[$catSlug] = [
                    'category'      => $catSlug,
                    'display_name'  => Categories::getDisplayName($catSlug) ?? $catSlug,
                    'channels'      => [],
                ];
            }
            $grouped[$catSlug]['channels'][] = $channel;
        }

        $this->sendResponse(200, 'Search completed', [
            'query'   => $query,
            'results' => array_values($grouped),
            'total'   => count($results),
        ]);
    }

    private function sendResponse(int $statusCode, string $message, $data = null): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        header('X-Content-Type-Options: nosniff');

        $response = [
            'success' => $statusCode >= 200 && $statusCode < 300,
            'message' => $message,
        ];

        if ($data !== null) {
            $response['data'] = $data;
        }

        echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
}
