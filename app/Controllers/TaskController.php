<?php

namespace App\Controllers;

use App\Models\TaskModel;
use CodeIgniter\RESTful\ResourceController;

class TaskController extends ResourceController
{
    protected $format = 'json';

    // ✅ Get all tasks (admin or PM view)
    public function index()
    {
        $taskModel = new TaskModel();
        $tasks = $taskModel
            ->select('tasks.*, projects.name as project_name, users.name as developer_name')
            ->join('projects', 'projects.id = tasks.project_id', 'left')
            ->join('users', 'users.id = tasks.assigned_to', 'left')
            ->orderBy('tasks.id', 'DESC')
            ->findAll();

        return $this->respond([
            'status' => 'success',
            'tasks' => $tasks
        ]);
    }

    // ✅ Create and assign task
    public function create()
    {
        $taskModel = new TaskModel();
        $data = $this->request->getJSON(true);

        if (empty($data['project_id']) || empty($data['title']) || empty($data['assigned_to'])) {
            return $this->failValidationErrors('project_id, title, and assigned_to are required');
        }

        $taskModel->insert($data);

        return $this->respondCreated([
            'status' => 'success',
            'message' => 'Task created successfully'
        ]);
    }

    // ✅ View single task
    public function show($id = null)
    {
        $taskModel = new TaskModel();
        $task = $taskModel
            ->select('tasks.*, projects.name as project_name, users.name as developer_name')
            ->join('projects', 'projects.id = tasks.project_id', 'left')
            ->join('users', 'users.id = tasks.assigned_to', 'left')
            ->where('tasks.id', $id)
            ->first();

        if (!$task) {
            return $this->failNotFound('Task not found');
        }

        return $this->respond(['status' => 'success', 'task' => $task]);
    }

    // ✅ Update task details or status
    public function update($id = null)
    {
        $taskModel = new TaskModel();
        $data = $this->request->getJSON(true);

        if (!$taskModel->find($id)) {
            return $this->failNotFound('Task not found');
        }

        $taskModel->update($id, $data);

        // 🔁 Auto-update project progress after updating status
        $this->updateProjectProgress($taskModel->find($id)['project_id']);

        return $this->respond(['status' => 'success', 'message' => 'Task updated successfully']);
    }

    // ✅ Delete a task
    public function delete($id = null)
    {
        $taskModel = new TaskModel();

        if (!$taskModel->find($id)) {
            return $this->failNotFound('Task not found');
        }

        $taskModel->delete($id);
        return $this->respondDeleted(['status' => 'success', 'message' => 'Task deleted successfully']);
    }

    // ✅ Get tasks by developer (for developer dashboard)
    public function getByDeveloper($developerId = null)
    {
        $taskModel = new TaskModel();
        $tasks = $taskModel
            ->select('tasks.*, projects.name as project_name')
            ->join('projects', 'projects.id = tasks.project_id', 'left')
            ->where('assigned_to', $developerId)
            ->orderBy('tasks.id', 'DESC')
            ->findAll();

        return $this->respond(['status' => 'success', 'tasks' => $tasks]);
    }

    // ✅ Helper: update project progress
    // private function updateProjectProgress($projectId)
    // {
    //     $db = db_connect();
    //     $total = $db->table('tasks')->where('project_id', $projectId)->countAllResults(false);
    //     $completed = $db->table('tasks')->where('project_id', $projectId)->where('status', 'completed')->countAllResults();

    //     $progress = $total > 0 ? ($completed / $total) * 100 : 0;
    //     $db->table('projects')->where('id', $projectId)->update(['progress' => $progress]);
    // }
// here also autocalculate status based on progress when progress 100 then status of project will be inprogress
    private function updateProjectProgress($projectId)
{
    $db = db_connect();
    $tasksTable = $db->table('tasks')->where('project_id', $projectId);

    $total = $tasksTable->countAllResults(false);
    $completed = $tasksTable->where('status', 'completed')->countAllResults();

    $progress = $total > 0 ? ($completed / $total) * 100 : 0;

    $status = ($progress == 100) ? 'completed' : 'in_progress';

    $db->table('projects')
       ->where('id', $projectId)
       ->update(['progress' => $progress, 'status' => $status]);
}

}
