<?php
namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use App\Models\TaskModel;
use App\Libraries\NotificationService;

class TaskReminder extends BaseCommand
{
    protected $group = 'FunctionX';
    protected $name = 'reminder:tasks';
    protected $description = 'Send reminders for tasks nearing their deadlines.';

    public function run(array $params)
    {
        $taskModel = new TaskModel();
        $notification = new NotificationService();

        $today = date('Y-m-d');
        $tomorrow = date('Y-m-d', strtotime('+1 day'));

        $tasks = $taskModel->whereIn('end_date', [$today, $tomorrow])->findAll();

        foreach ($tasks as $task) {
            $notification->sendTaskReminder($task);
        }

        echo "✅ Task reminders sent successfully.\n";
    }
}
