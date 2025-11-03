<?php
namespace App\Controllers;
use App\Controllers\BaseController;
use App\Models\NotificationModel;

class NotificationController extends BaseController
{
    public function index()
    {
        $userId = $this->request->user->id ?? null; // from JWT filter
        $model = new NotificationModel();

        $notifications = $model->where('user_id', $userId)
            ->orderBy('created_at', 'DESC')
            ->findAll();

        return $this->response->setJSON(['status' => 'success', 'notifications' => $notifications]);
    }

    public function unreadCount()
    {
        $userId = $this->request->user->id ?? null;
        $model = new NotificationModel();
        $count = $model->where('user_id', $userId)->where('is_read', 0)->countAllResults();

        return $this->response->setJSON(['unread_count' => $count]);
    }

    public function markRead($id = null)
    {
        $userId = $this->request->user->id ?? null;
        $model = new NotificationModel();
        $model->where('user_id', $userId)->where('id', $id)->set(['is_read' => 1])->update();

        return $this->response->setJSON(['status' => 'success', 'message' => 'Marked as read']);
    }
}
