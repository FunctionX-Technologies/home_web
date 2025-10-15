<?php
namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;

class AuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $authHeader = $request->getServer('HTTP_AUTHORIZATION') ?? $request->getHeaderLine('Authorization');
        if (!$authHeader) {
            return Services::response()
                ->setStatusCode(401)
                ->setJSON(['error' => 'Missing Authorization header']);
        }

        // Expect: "Bearer token"
        if (strpos($authHeader, 'Bearer ') !== 0) {
            return Services::response()
                ->setStatusCode(401)
                ->setJSON(['error' => 'Malformed Authorization header']);
        }

        $token = substr($authHeader, 7);
        try {
            $decoded = jwt_decode_token($token);
        } catch (\Firebase\JWT\ExpiredException $e) {
            return Services::response()
                ->setStatusCode(401)
                ->setJSON(['error' => 'Token expired']);
        } catch (\Exception $e) {
            return Services::response()
                ->setStatusCode(401)
                ->setJSON(['error' => 'Invalid token', 'msg' => $e->getMessage()]);
        }

        // Attach user data (data => payload data)
        // decoded->data contains the payload set when creating token
        $userData = (object) $decoded->data;

        // make available in controller: request()->getAttribute('jwt_user')
        $request->setAttribute('jwt_user', $userData);

        // continue
        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // nothing to do
    }
}
