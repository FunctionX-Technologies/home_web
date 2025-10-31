<?php
namespace App\Controllers;

use App\Models\TaskCommentModel;
use CodeIgniter\RESTful\ResourceController;

class TaskCommentController extends ResourceController
{
    protected $format = 'json';

    public function create()
    {
        $data = $this->request->getJSON(true);
        $model = new TaskCommentModel();
        $model->insert($data);

        return $this->respond(['status' => 'success', 'message' => 'Comment added.']);
    }

    public function getByTask($taskId)
    {
        $model = new TaskCommentModel();
        $comments = $model
            ->select('task_comments.*, users.name as commenter')
            ->join('users', 'users.id = task_comments.user_id', 'left')
            ->where('task_id', $taskId)
            ->orderBy('created_at', 'DESC')
            ->findAll();

        return $this->respond(['status' => 'success', 'comments' => $comments]);
    }
}
