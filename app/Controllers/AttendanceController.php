<?php
namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\AttendanceModel;
use App\Libraries\NotificationService;

class AttendanceController extends BaseController
{
    // public function punchIn()
    // {
    //     helper('current_user');
    //     $userId = current_user_id();

    //     if (! $userId) {
    //         return $this->response->setJSON(['error' => 'User not authenticated'])->setStatusCode(401);
    //     }

    //     $model = new AttendanceModel();

    //     // Check if already punched in today
    //     $today = date('Y-m-d');
    //     $existing = $model
    //         ->where('user_id', $userId)
    //         ->where('DATE(punch_in_time)', $today)
    //         ->first();

    //     if ($existing) {
    //         return $this->response->setJSON([
    //             'status' => 'error',
    //             'message' => 'You already punched in today.'
    //         ]);
    //     }

    //     $device = $this->request->getUserAgent();
    //     $model->insert([
    //         'user_id' => $userId,
    //         'punch_in_time' => date('Y-m-d H:i:s'),
    //         'device_info' => $device->getBrowser() . ' on ' . $device->getPlatform(),
    //         'status' => 'present'
    //     ]);

    //     return $this->response->setJSON([
    //         'status' => 'success',
    //         'message' => 'Punched in successfully.',
    //         'time' => date('H:i:s')
    //     ]);
    // }
    public function punchIn()
    {
        helper('current_user');
        $userId = current_user_id();

        if (! $userId) {
            return $this->response->setJSON(['error' => 'User not authenticated'])->setStatusCode(401);
        }

        $model = new AttendanceModel();
        $today = date('Y-m-d');
        $existing = $model
            ->where('user_id', $userId)
            ->where('DATE(punch_in_time)', $today)
            ->first();

        if ($existing) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'You already punched in today.'
            ]);
        }

        $device = $this->request->getUserAgent();
        $punchInTime = date('Y-m-d H:i:s');

        $model->insert([
            'user_id' => $userId,
            'punch_in_time' => $punchInTime,
            'device_info' => $device->getBrowser() . ' on ' . $device->getPlatform(),
            'status' => 'present'
        ]);

        // ✅ Send notification + email
        $notify = new NotificationService();
        $title = 'Punch In Successful';
        $message = "You punched in successfully at <b>" . date('H:i:s', strtotime($punchInTime)) . "</b> on " . date('d M Y') . ".";

        $notify->notify($userId, 'attendance', $title, $message, null, true);

        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'Punched in successfully. Email & notification sent.',
            'time' => date('H:i:s')
        ]);
    }


    // public function punchOut()
    // {
    //     helper('current_user');
    //     $userId = current_user_id();

    //     if (! $userId) {
    //         return $this->response->setJSON(['error' => 'User not authenticated'])->setStatusCode(401);
    //     }

    //     $model = new AttendanceModel();
    //     $today = date('Y-m-d');

    //     // Find today's punch-in record
    //     $record = $model
    //         ->where('user_id', $userId)
    //         ->where('DATE(punch_in_time)', $today)
    //         ->where('punch_out_time', null)
    //         ->first();

    //     if (! $record) {
    //         return $this->response->setJSON([
    //             'status' => 'error',
    //             'message' => 'You haven’t punched in yet or already punched out.'
    //         ]);
    //     }

    //     $punchOutTime = date('Y-m-d H:i:s');
    //     $duration = (strtotime($punchOutTime) - strtotime($record['punch_in_time'])) / 3600; // hours

    //     $status = ($duration < 4) ? 'half-day' : 'present';

    //     $model->update($record['id'], [
    //         'punch_out_time' => $punchOutTime,
    //         'total_duration' => round($duration, 2),
    //         'status' => $status
    //     ]);

    //     return $this->response->setJSON([
    //         'status' => 'success',
    //         'message' => 'Punched out successfully.',
    //         'total_hours' => round($duration, 2),
    //         'status_today' => $status
    //     ]);
    // }

    public function punchOut()
    {
        helper('current_user');
        $userId = current_user_id();

        if (! $userId) {
            return $this->response->setJSON(['error' => 'User not authenticated'])->setStatusCode(401);
        }

        $model = new AttendanceModel();
        $today = date('Y-m-d');

        $record = $model
            ->where('user_id', $userId)
            ->where('DATE(punch_in_time)', $today)
            ->where('punch_out_time', null)
            ->first();

        if (! $record) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'You haven’t punched in yet or already punched out.'
            ]);
        }

        $punchOutTime = date('Y-m-d H:i:s');
        $duration = (strtotime($punchOutTime) - strtotime($record['punch_in_time'])) / 3600;
        $status = ($duration < 4) ? 'half-day' : 'present';

        $model->update($record['id'], [
            'punch_out_time' => $punchOutTime,
            'total_duration' => round($duration, 2),
            'status' => $status
        ]);

        // ✅ Send notification + email
        $notify = new NotificationService();
        $title = 'Punch Out Recorded';
        $message = "You punched out at <b>" . date('H:i:s', strtotime($punchOutTime)) . "</b>.<br>Total working hours: <b>" . round($duration, 2) . " hrs</b>.";

        $notify->notify($userId, 'attendance', $title, $message, null, true);

        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'Punched out successfully. Email & notification sent.',
            'total_hours' => round($duration, 2),
            'status_today' => $status
        ]);
    }


    public function status($userId = null)
    {
        helper('current_user');
        $loggedUserId = current_user_id();

        if (! $loggedUserId) {
            return $this->response->setJSON(['error' => 'User not authenticated'])->setStatusCode(401);
        }

        // If no userId passed, show own status
        $targetUserId = $userId ?? $loggedUserId;

        $model = new AttendanceModel();
        $today = date('Y-m-d');

        $record = $model
            ->where('user_id', $targetUserId)
            ->where('DATE(punch_in_time)', $today)
            ->first();

        if (! $record) {
            return $this->response->setJSON(['status' => 'absent', 'message' => 'No attendance record today.']);
        }

        return $this->response->setJSON(['status' => 'success', 'attendance' => $record]);
    }

    public function report()
    {
        helper('current_user');
        $user = current_user();

        if (! $user || $user->role !== 'admin') {
            return $this->response->setJSON(['error' => 'Access denied'])->setStatusCode(403);
        }

        $model = new AttendanceModel();
        $data = $model->orderBy('created_at', 'DESC')->findAll();

        return $this->response->setJSON(['status' => 'success', 'records' => $data]);
    }
}
