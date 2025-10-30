<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;

class RoleModuleController extends ResourceController
{
    protected $format = 'json';

    // ✅ GET /api/modules
    public function modules()
    {
        $db = db_connect();
        $modules = $db->table('modules')->get()->getResult();

        return $this->respond([
            'status' => 'success',
            'modules' => $modules
        ]);
    }

    // ✅ GET /api/role-modules/{role}
    // (Returns module names instead of IDs)
    public function getByRole($role = null)
    {
        if (!$role) {
            return $this->fail('Role is required.');
        }

        $db = db_connect();

        // ✅ Join role_modules and modules table to get names
        $query = $db->query("
            SELECT m.id, m.name 
            FROM role_modules rm
            JOIN modules m ON rm.module_id = m.id
            WHERE rm.role = ?
        ", [$role]);

        $modules = $query->getResultArray();
        $moduleNames = array_column($modules, 'name');

        return $this->respond([
            'status' => 'success',
            'role' => $role,
            'modules' => $moduleNames  // ✅ returns module names
        ]);
    }

    // ✅ POST /api/role-modules/update
    // Example body:
    // {
    //   "role": "developer",
    //   "modules": [1,2,3]
    // }
    public function updateRoleModules()
    {
        $data = $this->request->getJSON(true);
        $role = $data['role'] ?? null;
        $modules = $data['modules'] ?? [];

        if (!$role) {
            return $this->fail('Role is required.');
        }

        $db = db_connect();
        $builder = $db->table('role_modules');

        // ✅ Delete existing permissions for this role
        $builder->where('role', $role)->delete();

        // ✅ Insert new permissions
        foreach ($modules as $module_id) {
            $builder->insert([
                'role' => $role,
                'module_id' => $module_id
            ]);
        }

        // ✅ Fetch module names to return in response
        if (!empty($modules)) {
            $query = $db->table('modules')
                ->select('name')
                ->whereIn('id', $modules)
                ->get();

            $moduleNames = array_column($query->getResultArray(), 'name');
        } else {
            $moduleNames = [];
        }

        return $this->respond([
            'status' => 'success',
            'message' => 'Modules updated successfully.',
            'role' => $role,
            'modules' => $moduleNames  // ✅ return module names instead of IDs
        ]);
    }
}
