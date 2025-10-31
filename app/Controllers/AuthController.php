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
    // public function login()
    // {
    //     $json = $this->request->getJSON(true);
    //     $email = $json['email'] ?? null;
    //     $password = $json['password'] ?? null;

    //     if (! $email || ! $password) {
    //         return $this->respond(['error' => 'Email and password required'], 400);
    //     }

    //     $user = $this->model->findByEmail($email);
    //     if (! $user) {
    //         return $this->respond(['error' => 'Invalid credentials'], 401);
    //     }

    //     if (! password_verify($password, $user['password'])) {
    //         return $this->respond(['error' => 'Invalid credentials'], 401);
    //     }

    //     if ((int)$user['is_active'] === 0) {
    //         return $this->respond(['error' => 'User is deactivated'], 403);
    //     }

    //     // create token payload (you can add minimal fields)
    //     $payload = [
    //         'id'   => (int)$user['id'],
    //         'email'=> $user['email'],
    //         'role' => $user['role'],
    //     ];

    //     $token = jwt_create_token($payload);

    //     return $this->respond([
    //         'access_token' => $token,
    //         'token_type'   => 'Bearer',
    //         'expires_in'   => (int)getenv('JWT_EXPIRE_SECONDS')
    //     ]);
    // }

    // here add for testing for role based access above correct old previous login function
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

    // ✅ Create token payload
    $payload = [
        'id'   => (int)$user['id'],
        'email'=> $user['email'],
        'role' => $user['role'],
    ];

    $token = jwt_create_token($payload);

    // ✅ Fetch allowed modules based on role
    $db = \Config\Database::connect();
    $role = $user['role'];

    if ($role === 'admin') {
        // admin gets all modules
        $modules = $db->table('modules')->select('id, name, slug')->get()->getResultArray();
    } else {
        // fetch from role_modules mapping
        $modules = $db->query("
            SELECT m.id, m.name, m.slug
            FROM role_modules rm
            JOIN modules m ON rm.module_id = m.id
            WHERE rm.role = ?
        ", [$role])->getResultArray();
    }

    // ✅ Final response
    return $this->respond([
        'access_token' => $token,
        'token_type'   => 'Bearer',
        'expires_in'   => (int)getenv('JWT_EXPIRE_SECONDS'),
        'user' => [
            'id' => $user['id'],
            'name' => $user['name'],
            'email' => $user['email'],
            'role' => $role,
            'modules' => $modules
        ]
    ]);
}


    /**
     * POST /auth/register
     * (Optional: use only for dev or admin-created users)
     * body: { name, email, password, role }
     */
    public function register()
    { 
        // console.log("hii");
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

            // Validate role against allowed values (matches DB ENUM)
            $allowedRoles = ['admin', 'project_manager', 'developer'];
            if (! in_array($role, $allowedRoles, true)) {
                // normalize common spelling mistakes or default to developer
                if (strtolower($role) === 'devloper' || strtolower($role) === 'developer') {
                    $role = 'developer';
                } else {
                    $role = 'developer';
                }
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
        // Read Authorization header and decode token again to get user data.
        // We re-decode here because the IncomingRequest may not support
        // setting attributes in filters on all environments.
        $authHeader = $this->request->getServer('HTTP_AUTHORIZATION') ?? $this->request->getHeaderLine('Authorization');
        if (! $authHeader || strpos($authHeader, 'Bearer ') !== 0) {
            return $this->respond(['error' => 'Not authenticated'], 401);
        }

        $token = substr($authHeader, 7);
        try {
            $decoded = jwt_decode_token($token);
        } catch (\Firebase\JWT\ExpiredException $e) {
            return $this->respond(['error' => 'Token expired'], 401);
        } catch (\Exception $e) {
            return $this->respond(['error' => 'Invalid token', 'msg' => $e->getMessage()], 401);
        }

        $jwtUser = (object) $decoded->data;

        return $this->respond([
            'id' => $jwtUser->id,
            'email' => $jwtUser->email,
            'role' => $jwtUser->role
        ]);
    }

    /**
     * POST /auth/forgot
     * body: { email }
     */
    public function forgot()
    {
        $json = $this->request->getJSON(true);
        $email = $json['email'] ?? null;
        if (! $email) {
            return $this->respond(['error' => 'email required'], 400);
        }

        $user = $this->model->findByEmail($email);
        // Always return success to avoid user enumeration
        if (! $user) {
            return $this->respond(['message' => 'If the email is registered, an OTP has been sent']);
        }

        $otp = strval(random_int(100000, 999999));
        $expires = date('Y-m-d H:i:s', time() + 600); // 10 minutes

        $db = \Config\Database::connect();
        $db->table('password_resets')->insert([
            'email' => $email,
            'otp' => $otp,
            'expires_at' => $expires,
            'used' => 0
        ]);

        // send email (best-effort)
        try {
            $emailer = \Config\Services::email();
            $emailer->setTo($email);
            $emailer->setFrom('no-reply@functionx.test', 'FunctionX');
            $emailer->setSubject('Your password reset OTP');
            $emailer->setMessage("Your OTP: {$otp} (valid 10 minutes)");
            $emailer->send();
        } catch (\Throwable $e) {
            // swallow email errors in dev; still return success message
            log_message('error', 'OTP email send failed: ' . $e->getMessage());
        }

        if (ENVIRONMENT === 'development') {
            return $this->respond(['message' => 'OTP sent (dev)', 'debug_otp' => $otp]);
        }

        return $this->respond(['message' => 'If the email is registered, an OTP has been sent']);
    }

    /**
     * POST /auth/verify-otp
     * body: { email, otp }
     * returns reset_token
     */
    public function verifyOtp()
    {
        $json = $this->request->getJSON(true);
        $email = $json['email'] ?? null;
        $otp = $json['otp'] ?? null;
        if (! $email || ! $otp) {
            return $this->respond(['error' => 'email and otp required'], 400);
        }

        $db = \Config\Database::connect();
        $row = $db->table('password_resets')
            ->where('email', $email)
            ->where('otp', $otp)
            ->where('used', 0)
            ->orderBy('created_at', 'DESC')
            ->get()
            ->getRow();

        if (! $row) {
            return $this->respond(['error' => 'Invalid OTP'], 400);
        }
        if (strtotime($row->expires_at) < time()) {
            return $this->respond(['error' => 'OTP expired'], 400);
        }

        $resetToken = bin2hex(random_bytes(32));
        $resetExpires = date('Y-m-d H:i:s', time() + 900); // 15 minutes

        $db->table('password_resets')
            ->where('id', $row->id)
            ->update(['reset_token' => $resetToken, 'expires_at' => $resetExpires]);

        return $this->respond(['reset_token' => $resetToken, 'expires_in' => 900]);
    }

    /**
     * POST /auth/reset
     * body: { email, reset_token, password }
     */
    public function reset()
    {
        $json = $this->request->getJSON(true);
        $email = $json['email'] ?? null;
        $token = $json['reset_token'] ?? null;
        $password = $json['password'] ?? null;

        if (! $email || ! $token || ! $password) {
            return $this->respond(['error' => 'email, reset_token and password required'], 400);
        }

        $db = \Config\Database::connect();
        $row = $db->table('password_resets')
            ->where('email', $email)
            ->where('reset_token', $token)
            ->where('used', 0)
            ->get()
            ->getRow();

        if (! $row) {
            return $this->respond(['error' => 'Invalid reset token'], 400);
        }
        if (strtotime($row->expires_at) < time()) {
            return $this->respond(['error' => 'Token expired'], 400);
        }

        $hash = password_hash($password, PASSWORD_DEFAULT);
        $this->model->where('email', $email)->set(['password' => $hash])->update();

        $db->table('password_resets')->where('id', $row->id)->update(['used' => 1]);

        return $this->respond(['message' => 'Password updated']);
    }
}
