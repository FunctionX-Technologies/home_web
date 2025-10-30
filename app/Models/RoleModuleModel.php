<?php

namespace App\Models;

use CodeIgniter\Model;

class RoleModuleModel extends Model
{
    protected $table = 'role_modules';
    protected $primaryKey = 'id';
    protected $allowedFields = ['role', 'module_id'];
}
