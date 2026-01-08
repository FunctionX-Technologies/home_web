<?php
namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\MonitorModel;

class MonitorController extends BaseController
{
    /**
     * POST /api/monitor/screenshot/upload
     * Upload screenshot with metadata
     */
    public function upload()
    {
        helper(['form', 'current_user']);

        $model = new MonitorModel();
        $file  = $this->request->getFile('screenshot');
        $userId = current_user_id() ?? $this->request->getPost('user_id');

        if (!$userId) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Missing user_id'])->setStatusCode(400);
        }

        if (!$file || !$file->isValid()) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Invalid file'])->setStatusCode(400);
        }

        // Create folder
        $folderPath = FCPATH . "uploads/screenshots/user_{$userId}/" . date('Y-m-d') . "/";
        if (!is_dir($folderPath)) mkdir($folderPath, 0777, true);

        // Save file
        $newName = $file->getRandomName();
        $file->move($folderPath, $newName);

        $dbPath = "uploads/screenshots/user_{$userId}/" . date('Y-m-d') . "/" . $newName;

        // Save DB record
        $model->insert([
            'user_id' => $userId,
            'image_path' => $dbPath,
            'active_window' => $this->request->getPost('active_window'),
            'idle_minutes' => (int) $this->request->getPost('idle_minutes'),
            'total_active_time' => (float) $this->request->getPost('total_active_time'),
            'captured_at' => date('Y-m-d H:i:s'),
        ]);

        // Return public URL
        $fullUrl = base_url($dbPath);

        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'Screenshot uploaded',
            'image_url' => $fullUrl
        ]);
    }

    /**
     * POST /api/monitor/activity-log
     * Logs activity without screenshot
     */
    public function logActivity()
    {
        $model = new MonitorModel();
        $json = $this->request->getJSON(true);

        $model->insert([
            'user_id' => $json['user_id'],
            'active_window' => $json['active_window'] ?? 'unknown',
            'idle_minutes' => (int)($json['idle_minutes'] ?? 0),
            'total_active_time' => (float)($json['total_active_time'] ?? 0),
            'captured_at' => date('Y-m-d H:i:s')
        ]);

        return $this->response->setJSON(['status' => 'success', 'message' => 'Activity logged']);
    }

    /**
     * GET /api/monitor/reports/{user_id}
     * Retrieve activity logs for a user
     */
    public function getReports($userId = null)
    {
        $model = new MonitorModel();
        $logs = $model->where('user_id', $userId)->orderBy('captured_at', 'DESC')->findAll();

        foreach ($logs as &$log) {
            $log['image_url'] = $log['image_path'] ? base_url($log['image_path']) : null;
        }

        return $this->response->setJSON(['status' => 'success', 'data' => $logs]);
    }

    /**
     * GET /api/monitor/summary/{date}
     * View summary of all users for a date
     */
    public function summary($date = null)
    {
        $date = $date ?? date('Y-m-d');
        $db = \Config\Database::connect();

        $query = $db->query("
            SELECT user_id,
                   COUNT(*) AS screenshots,
                   SUM(idle_minutes) AS total_idle,
                   SUM(total_active_time) AS total_active
            FROM monitor_logs
            WHERE DATE(captured_at) = ?
            GROUP BY user_id
        ", [$date]);

        return $this->response->setJSON([
            'status' => 'success',
            'date' => $date,
            'summary' => $query->getResultArray()
        ]);
    }
}
