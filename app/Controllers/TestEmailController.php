<?php
namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\Config\Services;

class TestEmailController extends Controller
{
    public function index()
    {
        $email = Services::email();

        $email->setFrom('rahulrai37792@gmail.com', 'FunctionX Test');
        $email->setTo('mcafw2303@glbitm.ac.in');  // send to yourself
        // dd($email->printDebugger(['headers', 'subject', 'body']));


        $email->setSubject('CI4 Email Test');
        $email->setMessage('<h3>This is a test email from CodeIgniter 4</h3>');

        if ($email->send()) {
            return $this->response->setJSON(['status' => 'success', 'message' => 'Email sent successfully']);
        } else {
            return $this->response->setJSON([
                'status' => 'error',
                'debug' => $email->printDebugger(['headers'])
            ]);
        }
    }
}
