<?php
namespace App\Controllers;


use CodeIgniter\RESTful\ResourceController;
use App\Models\ProjectModel;


class ProjectController extends ResourceController
{
protected $modelName = ProjectModel::class;
protected $format = 'json';


public function index()
{
// GET /api/projects - list with basic pagination & filters
$page = (int) $this->request->getGet('page') ?: 1;
$limit = (int) $this->request->getGet('limit') ?: 20;
$status = $this->request->getGet('status');
$priority = $this->request->getGet('priority');


$builder = $this->model;


if ($status) {
$builder = $builder->where('status', $status);
}
if ($priority) {
$builder = $builder->where('priority', $priority);
}


$data = $builder->orderBy('created_at', 'DESC')->paginate($limit, 'default', $page);
$pager = $this->model->pager;


return $this->respond([
'status' => 'success',
'data' => $data,
'meta' => [
'page' => $page,
'limit' => $limit,
'total' => $pager->getTotal()
]
]);
}


public function show($id = null)
{
// GET /api/projects/{id}
$project = $this->model->find($id);
if (!$project) return $this->failNotFound('Project not found');


return $this->respond(['status' => 'success', 'data' => $project]);
}


public function create()
{
// POST /api/projects
$input = $this->request->getJSON(true) ?: $this->request->getPost();


// If using JSON body
$data = [
'name' => $input['name'] ?? null,
'description' => $input['description'] ?? null,
'start_date' => $input['start_date'] ?? null,
'end_date' => $input['end_date'] ?? null,
'priority' => $input['priority'] ?? 'medium',
'status' => $input['status'] ?? 'not_started',
'created_by' => $input['created_by'] ?? null,
'progress' => $input['progress'] ?? 0
];


if (!$this->model->insert($data)) {
return $this->failValidationErrors($this->model->errors());
}


$id = $this->model->getInsertID();
$project = $this->model->find($id);


return $this->respondCreated(['status' => 'success', 'data' => $project]);
}


public function update($id = null)
{
// PUT /api/projects/{id}
$project = $this->model->find($id);
if (!$project) return $this->failNotFound('Project not found');


$input = $this->request->getJSON(true) ?: $this->request->getRawInput();


$data = [];
foreach (['name','description','start_date','end_date','priority','status','progress'] as $field) {
if (array_key_exists($field, $input)) $data[$field] = $input[$field];
}


if (!$this->model->update($id, $data)) {
return $this->failValidationErrors($this->model->errors());
}


$project = $this->model->find($id);
return $this->respond(['status' => 'success', 'data' => $project]);
}


public function delete($id = null)
{
// DELETE /api/projects/{id}
$project = $this->model->find($id);
if (!$project) return $this->failNotFound('Project not found');


$this->model->delete($id);
return $this->respondDeleted(['status' => 'success', 'message' => 'Project deleted']);
}





//update project priorities here
public function updatePriority($id = null)
{
    $data = $this->request->getJSON(true);
    $priority = $data['priority'] ?? null;

    if (!in_array($priority, ['low','medium','high'])) {
        return $this->failValidationErrors('Invalid priority level.');
    }

    $db = db_connect();
    $db->table('projects')->where('id', $id)->update(['priority' => $priority]);

    return $this->respond(['status'=>'success','message'=>'Priority updated']);
}

public function getByPriority($priority = null)
{
    $db = db_connect();
    $projects = $db->table('projects')->where('priority', $priority)->get()->getResult();

    return $this->respond(['status'=>'success','projects'=>$projects]);
}

}