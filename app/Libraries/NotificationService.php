<?php
namespace App\Libraries;

use App\Models\NotificationModel;
use CodeIgniter\Config\Services;

class NotificationService
{
    protected $notifModel;
    protected $email;

    public function __construct()
    {
        $this->notifModel = new NotificationModel();
        $this->email = Services::email();
    }

    // Main notification function
    public function notify($userId, $type, $title, $message, $url = null, $sendEmail = true)
    {
        // 1️⃣ Save to notifications table
        $this->notifModel->insert([
            'user_id' => $userId,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'url' => $url,
            'is_read' => 0
        ]);

        // 2️⃣ Send email (optional)
        if ($sendEmail && $userId) {
            $db = db_connect();
            $user = $db->table('users')->where('id', $userId)->get()->getRowArray();

            if ($user && !empty($user['email'])) {
                $this->email->setTo($user['email']);
                $this->email->setSubject($title);
                $this->email->setMessage($message);
                $this->email->send();
            }
        }
    }
}
