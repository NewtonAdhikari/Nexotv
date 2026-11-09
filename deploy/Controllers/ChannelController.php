<?php
declare(strict_types=1);

namespace IPTV\Controllers;

use IPTV\Models\Channel;
use IPTV\Config\Categories;

class ChannelController
{
    private Channel $channelModel;

    public function __construct()
    {
        $this->channelModel = new Channel();
    }

    public function getCategories(): void
    {
        $categories = Categories::getAll();
        $counts     = $this->channelModel->getCategoryCounts();

        $result = [];
        foreach ($categories as $slug => $displayName) {
            $result[] = [
                'slug'  => $slug,
                'name'  => $displayName,
                'count' => $counts[$slug] ?? 0,
            ];
        }

        $this->sendResponse(200, 'Categories retrieved successfully', $result);
    }

    public function getChannelsByCategory(): void
    {
        $category = $_GET['category'] ?? '';

        if (empty($category)) {
            $this->sendResponse(400, 'Missing required parameter: category');
            return;
        }

        $category = strtolower(trim($category));

        if (!Categories::isValid($category)) {
            $this->sendResponse(400, 'Invalid category. Available categories can be fetched from /api/categories');
            return;
        }

        $channels = $this->channelModel->getAllByCategory($category);

        if (empty($channels)) {
            $this->sendResponse(200, 'No channels found in this category', []);
            return;
        }

        $this->sendResponse(200, 'Channels retrieved successfully', $channels);
    }

    public function getChannelById(): void
    {
        $category = $_GET['category'] ?? '';
        $id       = $_GET['id'] ?? '';

        if (empty($category) || empty($id)) {
            $this->sendResponse(400, 'Missing required parameters: category and id');
            return;
        }

        $category = strtolower(trim($category));
        $id       = (int)$id;

        if ($id <= 0) {
            $this->sendResponse(400, 'Invalid channel ID');
            return;
        }

        if (!Categories::isValid($category)) {
            $this->sendResponse(400, 'Invalid category.');
            return;
        }

        $channel = $this->channelModel->getById($category, $id);

        if ($channel === null) {
            $this->sendResponse(404, 'Channel not found');
            return;
        }

        $channel['category'] = $category;
        $channel['category_display'] = Categories::getDisplayName($category);

        $this->sendResponse(200, 'Channel retrieved successfully', $channel);
    }

    public function addChannel(): void
    {
        $input = json_decode(file_get_contents('php://input'), true);

        if (!$input) {
            $this->sendResponse(400, 'Invalid request body. Send JSON with: name, category, stream_url');
            return;
        }

        $name      = isset($input['name']) ? trim($input['name']) : '';
        $category  = isset($input['category']) ? strtolower(trim($input['category'])) : '';
        $streamUrl = isset($input['stream_url']) ? trim($input['stream_url']) : '';
        $logo      = isset($input['logo']) ? trim($input['logo']) : '';

        if (empty($name) || empty($category) || empty($streamUrl)) {
            $this->sendResponse(400, 'Missing required fields: name, category, stream_url');
            return;
        }

        if (!Categories::isValid($category)) {
            $this->sendResponse(400, 'Invalid category. Valid categories: ' . implode(', ', array_keys(Categories::getAll())));
            return;
        }

        $channelModel = new Channel();
        $result = $channelModel->insert($category, $name, $logo, $streamUrl);

        if ($result) {
            $this->sendResponse(201, 'Channel added successfully');
        } else {
            $this->sendResponse(409, 'Channel already exists or failed to add (duplicate stream_url)');
        }
    }

    public function deleteChannel(): void
    {
        $input = json_decode(file_get_contents('php://input'), true);

        if (!$input) {
            $this->sendResponse(400, 'Invalid request body. Send JSON with: category, id');
            return;
        }

        $category = isset($input['category']) ? strtolower(trim($input['category'])) : '';
        $id       = isset($input['id']) ? (int)$input['id'] : 0;

        if (empty($category) || $id <= 0) {
            $this->sendResponse(400, 'Missing required fields: category, id');
            return;
        }

        if (!Categories::isValid($category)) {
            $this->sendResponse(400, 'Invalid category');
            return;
        }

        $channelModel = new Channel();
        $deleted = $channelModel->deleteById($category, $id);

        if ($deleted) {
            $this->sendResponse(200, 'Channel deleted successfully');
        } else {
            $this->sendResponse(404, 'Channel not found');
        }
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
