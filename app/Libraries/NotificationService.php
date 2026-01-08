<?php
namespace App\Libraries;

use App\Models\NotificationModel;
use App\Models\UserModel;
use CodeIgniter\Config\Services;

/**
 * ---------------------------------------------------------
 * NotificationService
 * ---------------------------------------------------------
 * Central service to manage notifications:
 * - Saves in database (for in-app view)
 * - Sends email (for external alert)
 * ---------------------------------------------------------
 */
class NotificationService
{
    protected $notificationModel;
    protected $userModel;

    public function __construct()
    {
        $this->notificationModel = new NotificationModel();
        $this->userModel = new UserModel();
    }

    /**
     * -----------------------------------------------------
     * Send Notification
     * -----------------------------------------------------
     * @param int    $userId       → recipient user ID
     * @param string $type         → 'task_assigned', 'task_updated', 'reminder', etc.
     * @param string $title        → short title for UI/email subject
     * @param string $message      → main body text
     * @param string $url          → optional link (e.g., task detail page)
     * @param bool   $sendEmail    → true = also send email
     * -----------------------------------------------------
     */
    public function notify($userId, $type, $title, $message, $url = null, $sendEmail = false)
    {
        // 🔹 1. Save to database (in-app notification)
        $this->notificationModel->insert([
            'user_id'   => $userId,
            'type'      => $type,
            'title'     => $title,
            'message'   => $message,
            'url'       => $url,
            'is_read'   => 0,
            'created_at'=> date('Y-m-d H:i:s'),
        ]);

        // 🔹 2. Send email (optional)
        if ($sendEmail) {
            $this->sendEmailNotification($userId, $title, $message);
        }
    }

    /**
     * -----------------------------------------------------
     * Send Email Notification
     * -----------------------------------------------------
     * Reads SMTP credentials from .env (no hard-coding!)
     * Uses CI4's Email service
     * -----------------------------------------------------
     */
    protected function sendEmailNotification($userId, $title, $message)
    {
        // Get user email
        $user = $this->userModel->find($userId);
        if (!$user || empty($user['email'])) {
            log_message('error', "❌ NotificationService: User {$userId} has no email");
            return false;
        }

        // Initialize email service
        $email = Services::email();

        $fromEmail = getenv('email.fromEmail') ?: 'noreply@functionx.com';
        $fromName  = getenv('email.fromName')  ?: 'FunctionX Notification System';

        $email->setFrom($fromEmail, $fromName);
        $email->setTo($user['email']);
        $email->setSubject($title);

        // HTML message layout
        $htmlBody = "
            <div style='font-family:Arial,sans-serif;padding:15px;border:1px solid #ddd;border-radius:8px;background:#f9f9f9'>
                <h2 style='color:#333;'>$title</h2>
                <p style='color:#555;font-size:15px;'>$message</p>
                <hr>
                <small style='color:#888;'>This is an automated message from FunctionX system.</small>
            </div>
        ";

        $email->setMessage($htmlBody);
        $email->setMailType('html');

        if (!$email->send()) {
            log_message('error', '❌ Email send failed: ' . $email->printDebugger(['headers']));
            return false;
        }

        log_message('info', "✅ Email sent to {$user['email']} - {$title}");
        return true;
    }

    /**
     * -----------------------------------------------------
     * Helper for reminders (CRON)
     * -----------------------------------------------------
     * Call this in your TaskReminder command.
     * Example: $notification->sendTaskReminder($task);
     * -----------------------------------------------------
     */
    public function sendTaskReminder($task)
    {
        $userId = $task['assigned_to'];
        if (!$userId) return;

        $title = "Reminder: Task Deadline Approaching";
        $message = "Your task '{$task['title']}' is due on {$task['end_date']}. Please ensure it’s completed on time.";
        $url = base_url("/tasks/{$task['id']}");

        $this->notify($userId, 'task_reminder', $title, $message, $url, true);
    }
}
