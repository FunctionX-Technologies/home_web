<?php
namespace App\Models;

use CodeIgniter\Model;

class AttendanceModel extends Model
{
    protected $table = 'attendance_logs';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'user_id', 'punch_in_time', 'punch_out_time',
        'total_duration', 'status', 'device_info', 'created_at'
    ];
}
