<?php
namespace App\Models;


use CodeIgniter\Model;


class ProjectModel extends Model
{
protected $table = 'projects';
protected $primaryKey = 'id';
protected $allowedFields = [
'name', 'description', 'start_date', 'end_date', 'priority', 'status', 'created_by', 'progress'
];


// Let CI manage timestamps if you prefer (optional)
protected $useTimestamps = false;


// Return type as array
protected $returnType = 'array';


// Validation can be defined here or in controller
protected $validationRules = [
'name' => 'required|min_length[3]|max_length[191]',
'start_date' => 'permit_empty|valid_date[Y-m-d]',
'end_date' => 'permit_empty|valid_date[Y-m-d]',
'priority' => 'permit_empty|in_list[low,medium,high]',
'status' => 'permit_empty|in_list[not_started,in_progress,completed,on_hold,cancelled]',
'progress' => 'permit_empty|numeric|greater_than_equal_to[0]|less_than_equal_to[100]'
];

protected $validationMessages = [];
}