<?php
declare(strict_types=1);

namespace IPTV\Controllers;

use IPTV\Config\Database;
use PDO;

class SupportController
{
    public function submit(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->sendResponse(405, 'Method not allowed. Use POST.');
            return;
        }

        $rawBody = file_get_contents('php://input');
        $data    = json_decode($rawBody, true);

        if ($data === null && json_last_error() !== JSON_ERROR_NONE) {
            $this->sendResponse(400, 'Invalid JSON payload');
            return;
        }

        $name    = $data['name']    ?? '';
        $email   = $data['email']   ?? '';
        $message = $data['message'] ?? '';

        $errors = [];

        if (empty(trim($name))) {
            $errors[] = 'Name is required';
        } elseif (strlen($name) > 255) {
            $errors[] = 'Name must be 255 characters or less';
        }

        if (empty(trim($email))) {
            $errors[] = 'Email is required';
        } elseif (!filter_var(trim($email), FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Invalid email format';
        } elseif (strlen($email) > 255) {
            $errors[] = 'Email must be 255 characters or less';
        }

        if (empty(trim($message))) {
            $errors[] = 'Message is required';
        } elseif (strlen($message) > 10000) {
            $errors[] = 'Message must be 10,000 characters or less';
        }

        if (!empty($errors)) {
            $this->sendResponse(422, 'Validation failed', ['errors' => $errors]);
            return;
        }

        $name    = htmlspecialchars(trim($name), ENT_QUOTES, 'UTF-8');
        $email   = filter_var(trim($email), FILTER_SANITIZE_EMAIL);
        $message = htmlspecialchars(trim($message), ENT_QUOTES, 'UTF-8');

        try {
            $db  = Database::getInstance()->getSupportConnection();
            $sql = "INSERT INTO support_messages (name, email, message) VALUES (:name, :email, :message)";

            $stmt = $db->prepare($sql);
            $stmt->bindValue(':name', $name, PDO::PARAM_STR);
            $stmt->bindValue(':email', $email, PDO::PARAM_STR);
            $stmt->bindValue(':message', $message, PDO::PARAM_STR);
            $stmt->execute();

            $insertedId = (int)$db->lastInsertId();

            $this->sendResponse(201, 'Support message received. We will get back to you soon.', [
                'id' => $insertedId,
            ]);

        } catch (\PDOException $e) {
            error_log("Support message insert failed: " . $e->getMessage());
            $this->sendResponse(500, 'Failed to submit your message. Please try again later.');
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
