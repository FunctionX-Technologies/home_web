<?php
namespace App\Controllers;

use App\Models\TaskCommentModel;
use App\Models\ActivityLogModel;
use CodeIgniter\RESTful\ResourceController;

class TaskCommentController extends ResourceController
{
    protected $format = 'json';

    public function create()
    {
        $data = $this->request->getJSON(true);

        $commentModel = new TaskCommentModel();
        $logModel = new ActivityLogModel();

        $commentId = $commentModel->insert($data);

        // Log the activity
        $logModel->insert([
            'user_id' => $data['user_id'] ?? null,
            'task_id' => $data['task_id'] ?? null,
            'action'  => 'Comment Added',
            'details' => json_encode(['comment_id' => $commentId, 'content' => $data['comment']])
        ]);

        return $this->respond(['status' => 'success', 'message' => 'Comment added successfully.']);
    }

    public function getByTask($taskId)
    {
        $model = new TaskCommentModel();
        $comments = $model->getCommentsWithReplies($taskId);

        return $this->respond(['status' => 'success', 'comments' => $comments]);
    }
}
