<?php
namespace App\Models;
use CodeIgniter\Model;

class TaskCommentModel extends Model
{
    protected $table = 'task_comments';
    protected $primaryKey = 'id';
    protected $allowedFields = ['task_id', 'user_id', 'comment', 'created_at'];
}
