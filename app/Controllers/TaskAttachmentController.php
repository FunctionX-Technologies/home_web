<?php
namespace App\Controllers;

use App\Models\TaskAttachmentModel;
use CodeIgniter\RESTful\ResourceController;

class TaskAttachmentController extends ResourceController
{
    protected $format = 'json';

    public function upload()
    {
        $task_id = $this->request->getPost('task_id');
        $uploaded_by = $this->request->getPost('uploaded_by');
        $file = $this->request->getFile('file');

        if (!$file->isValid()) {
            return $this->fail('Invalid file upload.');
        }

        $newName = $file->getRandomName();
        $file->move(FCPATH . 'uploads/tasks', $newName);

        $model = new TaskAttachmentModel();
        $model->insert([
            'task_id' => $task_id,
            'file_path' => 'uploads/tasks/' . $newName,
            'uploaded_by' => $uploaded_by,
        ]);

        return $this->respond(['status' => 'success', 'message' => 'File uploaded successfully.']);
    }

    public function getByTask($taskId)
    {
        $model = new TaskAttachmentModel();
        $attachments = $model->where('task_id', $taskId)->findAll();
        return $this->respond(['status' => 'success', 'attachments' => $attachments]);
    }
}
