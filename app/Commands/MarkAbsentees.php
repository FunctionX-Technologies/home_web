<?php
namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Models\UserModel;
use App\Models\AttendanceModel;
use App\Libraries\NotificationService;

class MarkAbsentees extends BaseCommand
{
    protected $group = 'Attendance';
    protected $name = 'attendance:mark-absentees';
    protected $description = 'Marks users as absent if they have not punched in today.';

    public function run(array $params)
    {
        $today = date('Y-m-d');
        $userModel = new UserModel();
        $attendanceModel = new AttendanceModel();
        $notify = new NotificationService();

        $users = $userModel->findAll();
        $count = 0;

        foreach ($users as $user) {
            $hasAttendance = $attendanceModel
                ->where('user_id', $user['id'])
                ->where('DATE(punch_in_time)', $today)
                ->first();

            if (! $hasAttendance) {
                $attendanceModel->insert([
                    'user_id' => $user['id'],
                    'status' => 'absent'
                ]);

                $count++;
                // ✅ Notify user (in-app + email)
                $title = 'Marked Absent Today';
                $message = "You were marked <b>absent</b> for " . date('d M Y') . " as no punch-in record was found.";
                $notify->notify($user['id'], 'attendance', $title, $message, null, true);
            }
        }

        CLI::write("✅ Total absentees marked: {$count}");
    }
}
