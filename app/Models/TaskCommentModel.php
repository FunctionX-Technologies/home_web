<?php
namespace App\Models;
use CodeIgniter\Model;

class TaskCommentModel extends Model
{
    protected $table = 'task_comments';
    protected $primaryKey = 'id';
    protected $allowedFields = ['task_id', 'user_id', 'parent_id', 'comment', 'created_at'];

    public function getCommentsWithReplies($taskId)
    {
        $comments = $this->where('task_id', $taskId)
                         ->where('parent_id', null)
                         ->orderBy('created_at', 'DESC')
                         ->findAll();

        foreach ($comments as &$comment) {
            $comment['replies'] = $this->where('parent_id', $comment['id'])
                                       ->orderBy('created_at', 'ASC')
                                       ->findAll();
        }

        return $comments;
    }
}
