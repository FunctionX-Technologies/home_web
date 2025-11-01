<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;

class DashboardController extends ResourceController
{
    protected $format = 'json';

    // ✅ Dashboard Overview
    public function overview()
    {
        $db = db_connect();

        // Total Projects
        $totalProjects = $db->table('projects')->countAllResults();

        // Tasks in Progress
        $tasksInProgress = $db->table('tasks')
                              ->where('status', 'in_progress')
                              ->countAllResults();

        // Overdue Tasks (end_date < today and not completed)
        $overdueTasks = $db->table('tasks')
                           ->where('end_date <', date('Y-m-d'))
                           ->where('status !=', 'completed')
                           ->countAllResults();

        return $this->respond([
            'status' => 'success',
            'data' => [
                'total_projects' => $totalProjects,
                'tasks_in_progress' => $tasksInProgress,
                'overdue_tasks' => $overdueTasks,
            ]
        ]);
    }

        /**
     * ⚙️ 2. Productivity Graph API
     * Shows total actual hours worked by each developer.
     */
    public function productivityGraph()
    {
        $db = db_connect();

        $builder = $db->table('task_time_logs as ttl');
        $builder->select('u.name as developer_name, SUM(ttl.actual_hours) as total_hours');
        $builder->join('users u', 'u.id = ttl.user_id', 'left');
        $builder->groupBy('ttl.user_id');
        $builder->orderBy('total_hours', 'DESC');

        $result = $builder->get()->getResult();

        return $this->respond([
            'status' => 'success',
            'data' => $result
        ]);
    }

    /**
     * 📈 3. Project Progress Graph API
     * Shows each project’s completion percentage.
     */
      public function projectProgressGraph()
    {
        $db = db_connect();

        $builder = $db->table('projects');
        $builder->select('name as project_name, progress');
        $builder->orderBy('progress', 'DESC');

        $result = $builder->get()->getResult();

        return $this->respond([
            'status' => 'success',
            'data' => $result
        ]);
    }
}
