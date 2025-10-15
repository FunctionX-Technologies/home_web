<?php
namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use App\Models\UserModel;

class Health extends ResourceController
{
    public function db()
    {
        try {
            $model = new UserModel();
            // countAllResults without calling reset? use simple findAll with limit
            $count = $model->countAllResults();
            return $this->respond(['db_ok' => true, 'users_count' => $count]);
        } catch (\Throwable $e) {
            return $this->respond(['db_ok' => false, 'error' => $e->getMessage()], 500);
        }
    }
}
<?php
namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use App\Models\UserModel;

class Health extends ResourceController
{
    public function db()
    {
        try {
            $model = new UserModel();
            $count = $model->countAllResults(); // quick operation
            return $this->respond(['db_ok' => true, 'users_count' => $count]);
        } catch (\Throwable $e) {
            return $this->respond(['db_ok' => false, 'error' => $e->getMessage()], 500);
        }
    }
}