<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Services\AuthService;
use App\Services\NotificationManager;
use App\Models\Notification as NotificationModel;

/**
 * Handles lightweight AJAX polling and notification status endpoints.
 */
class NotificationController extends Controller
{
    public function poll(): void
    {
        if (!AuthService::check()) {
            http_response_code(401);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['error' => 'Unauthorized']);
            return;
        }

        $userId = AuthService::id();
        $manager = new NotificationManager($this->config);
        $latestNotifications = $manager->latestForUser($userId, 10);
        $unreadCount = $manager->unreadCount($userId);

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'latest' => $latestNotifications,
            'unreadCount' => $unreadCount,
        ], JSON_UNESCAPED_UNICODE);
    }

    /**
     * Mark a notification as read for the current user.
     */
    public function markRead(): void
    {
        if (!AuthService::check()) {
            http_response_code(401);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['error' => 'Unauthorized']);
            return;
        }

        $userId = AuthService::id();

        // Accept JSON payload or form POST
        $payload = $_POST;
        if (empty($payload)) {
            $raw = file_get_contents('php://input');
            $data = json_decode($raw, true);
            if (is_array($data)) {
                $payload = $data;
            }
        }

        $id = isset($payload['id']) ? (int) $payload['id'] : 0;
        if ($id <= 0) {
            http_response_code(400);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['error' => 'Invalid notification id']);
            return;
        }

        $model = new NotificationModel();
        // Mark as read only for the owning user
        $model->markAsReadByUser($id, $userId);

        $unreadCount = $model->unreadCountForUser($userId);

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => true, 'unreadCount' => $unreadCount]);
    }
}
