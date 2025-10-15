<?php
namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use App\Models\UserModel;

class AuthController extends ResourceController
{
    protected $modelName = UserModel::class;
    protected $format = 'json';

    public function __construct()
    {
        // load jwt helper
        helper('jwt');
    }

    /**
     * POST /auth/login
     * body: { "email": "...", "password": "..." }
     */
    public function login()
    {
        $json = $this->request->getJSON(true);
        $email = $json['email'] ?? null;
        $password = $json['password'] ?? null;

        if (! $email || ! $password) {
            return $this->respond(['error' => 'Email and password required'], 400);
        }

        $user = $this->model->findByEmail($email);
        if (! $user) {
            return $this->respond(['error' => 'Invalid credentials'], 401);
        }

        if (! password_verify($password, $user['password'])) {
            return $this->respond(['error' => 'Invalid credentials'], 401);
        }

        if ((int)$user['is_active'] === 0) {
            return $this->respond(['error' => 'User is deactivated'], 403);
        }

        // create token payload (you can add minimal fields)
        $payload = [
            'id'   => (int)$user['id'],
            'email'=> $user['email'],
            'role' => $user['role'],
        ];

        $token = jwt_create_token($payload);

        return $this->respond([
            'access_token' => $token,
            'token_type'   => 'Bearer',
            'expires_in'   => (int)getenv('JWT_EXPIRE_SECONDS')
        ]);
    }

    /**
     * POST /auth/register
     * (Optional: use only for dev or admin-created users)
     * body: { name, email, password, role }
     */
    public function register()
    { 
        console.log("hii");
        $json = $this->request->getJSON(true);
        $name = $json['name'] ?? null;
        $email = $json['email'] ?? null;
        $password = $json['password'] ?? null;
        $role = $json['role'] ?? 'developer';

        if (! $name || ! $email || ! $password) {
            return $this->respond(['error' => 'name, email and password required'], 400);
        }

        // check for duplicate email
        try {
            if ($this->model->findByEmail($email)) {
                return $this->respond(['error' => 'Email already exists'], 409);
            }

            $hash = password_hash($password, PASSWORD_DEFAULT);
            $userId = $this->model->insert([
                'name' => $name,
                'email' => $email,
                'password' => $hash,
                'role' => $role
            ]);

            if ($userId === false) {
                // Insert failed, try to get errors from model
                $errors = method_exists($this->model, 'errors') ? $this->model->errors() : null;
                return $this->respond(['error' => 'Failed to create user', 'details' => $errors], 500);
            }

            return $this->respondCreated(['message' => 'User created', 'id' => $userId]);
        } catch (\Throwable $e) {
            // Return exception message to help debugging in development
            log_message('error', 'Register error: ' . $e->getMessage());
            return $this->respond(['error' => 'Server error', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Example: validate token / get profile
     * GET /auth/me
     * (protected route, uses AuthFilter)
     */
    public function me()
    {
        // AuthFilter attaches user data to request attribute 'jwt_user'
        $jwtUser = request()->getAttribute('jwt_user');
        if (! $jwtUser) {
            return $this->respond(['error' => 'Not authenticated'], 401);
        }

        // return user info (minimal)
        return $this->respond([
            'id' => $jwtUser->id,
            'email' => $jwtUser->email,
            'role' => $jwtUser->role
        ]);
    }
}
