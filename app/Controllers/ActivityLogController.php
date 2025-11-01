<?php
namespace App\Controllers;

use App\Models\ActivityLogModel;
use CodeIgniter\RESTful\ResourceController;

class ActivityLogController extends ResourceController
{
    protected $format = 'json';

    public function index()
    {
        $model = new ActivityLogModel();
        $logs = $model->orderBy('created_at', 'DESC')->findAll();
        return $this->respond(['status' => 'success', 'logs' => $logs]);
    }

    public function getByTask($taskId)
    {
        $model = new ActivityLogModel();
        $logs = $model->where('task_id', $taskId)
                      ->orderBy('created_at', 'DESC')
                      ->findAll();

        return $this->respond(['status' => 'success', 'logs' => $logs]);
    }
}
