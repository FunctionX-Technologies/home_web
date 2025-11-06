<?php
namespace App\Models;

use CodeIgniter\Model;

class MonitorModel extends Model
{
    protected $table = 'monitor_logs';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'user_id',
        'image_path',
        'active_window',
        'idle_minutes',
        'total_active_time',
        'captured_at',
        'created_at',
        'updated_at'
    ];

    protected $useTimestamps = true; // automatically sets created_at/updated_at
}
