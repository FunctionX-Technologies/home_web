<?php
namespace App\Controllers;

use App\Models\TaskTimeLogModel;
use CodeIgniter\RESTful\ResourceController;

class TaskTimeLogController extends ResourceController
{
    protected $format = 'json';

    public function logTime()
    {
        $data = $this->request->getJSON(true);
        $model = new TaskTimeLogModel();

        $existing = $model->where('task_id', $data['task_id'])->where('user_id', $data['user_id'])->first();
        if ($existing) {
            $model->update($existing['id'], $data);
        } else {
            $model->insert($data);
        }

        return $this->respond(['status' => 'success', 'message' => 'Time logged successfully.']);
    }

    public function getByTask($taskId)
    {
        $model = new TaskTimeLogModel();
        $logs = $model->where('task_id', $taskId)->findAll();
        return $this->respond(['status' => 'success', 'logs' => $logs]);
    }
}