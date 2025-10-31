<?php
namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table      = 'users';
    protected $primaryKey = 'id';
    protected $allowedFields = ['name','email','password','role','is_active','created_at','updated_at'];
    protected $useTimestamps = true;
    protected $returnType = 'array';

    public function findByEmail(string $email)
    {
        return $this->where('email', $email)->first();
    }
}
