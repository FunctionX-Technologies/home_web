<?php
namespace App\Models;
use CodeIgniter\Model;

class TaskAttachmentModel extends Model
{
    protected $table = 'task_attachments';
    protected $primaryKey = 'id';
    protected $allowedFields = ['task_id', 'file_path', 'uploaded_by', 'uploaded_at'];
}
