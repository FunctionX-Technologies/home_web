<?php
namespace App\Models;
use CodeIgniter\Model;

class TaskTimeLogModel extends Model
{
    protected $table = 'task_time_logs';
    protected $primaryKey = 'id';
    protected $allowedFields = ['task_id', 'user_id', 'estimated_hours', 'actual_hours', 'created_at'];
}
