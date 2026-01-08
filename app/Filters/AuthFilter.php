<?php
namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;

class AuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        // Ensure jwt helper is loaded so jwt_decode_token() is available
        helper('jwt');

        $authHeader = $request->getServer('HTTP_AUTHORIZATION') ?? $request->getHeaderLine('Authorization');
        if (!$authHeader) {
            return service('response')
                ->setStatusCode(401)
                ->setJSON(['error' => 'Missing Authorization header']);
        }

        // Expect: "Bearer token"
        if (strpos($authHeader, 'Bearer ') !== 0) {
            return service('response')
                ->setStatusCode(401)
                ->setJSON(['error' => 'Malformed Authorization header']);
        }

        $token = substr($authHeader, 7);
        try {
            $decoded = jwt_decode_token($token);
        } catch (\Firebase\JWT\ExpiredException $e) {
            return service('response')
                ->setStatusCode(401)
                ->setJSON(['error' => 'Token expired']);
        } catch (\Exception $e) {
            return service('response')
                ->setStatusCode(401)
                ->setJSON(['error' => 'Invalid token', 'msg' => $e->getMessage()]);
        }

        // We validated the token here. Controllers can re-decode the token
        // if they need the user data (keeps the filter simple and avoids
        // modifying the IncomingRequest object which doesn't support
        // setAttribute()).
        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // nothing to do
    }
}
