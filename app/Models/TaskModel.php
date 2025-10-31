<?php

namespace App\Models;
use CodeIgniter\Model;

class TaskModel extends Model
{
    protected $table = 'tasks';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'project_id',
        'title',
        'description',
        'status',
        'start_date',
        'end_date',
        'assigned_to'
    ];

    protected $useTimestamps = false;
}
