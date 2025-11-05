<?php
namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\NotificationModel;

class NotificationController extends BaseController
{
    public function index()
    {
        helper('current_user'); // if not autoloaded
        $userId = current_user_id();

        if (! $userId) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'User ID not found in token'])->setStatusCode(401);
        }

        $model = new NotificationModel();

        $notifications = $model->where('user_id', $userId)
            ->orderBy('created_at', 'DESC')
            ->findAll();

        return $this->response->setJSON(['status' => 'success', 'notifications' => $notifications]);
    }

    public function unreadCount()
    {
        helper('current_user');
        $userId = current_user_id();

        if (! $userId) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'User ID not found in token'])->setStatusCode(401);
        }

        $model = new NotificationModel();
        $count = $model->where('user_id', $userId)->where('is_read', 0)->countAllResults();

        return $this->response->setJSON(['unread_count' => $count]);
    }

    public function markRead($id = null)
    {
        helper('current_user');
        $userId = current_user_id();

        if (! $userId) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'User ID not found in token'])->setStatusCode(401);
        }

        $model = new NotificationModel();
        $model->where('user_id', $userId)->where('id', $id)->set(['is_read' => 1])->update();

        return $this->response->setJSON(['status' => 'success', 'message' => 'Marked as read']);
    }
}
