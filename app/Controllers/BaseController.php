<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\CLIRequest;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

use App\Models\UserModel;
use App\Models\UserLoginSessionModel;

/**
 * Class BaseController
 *
 * BaseController provides a convenient place for loading components
 * and performing functions that are needed by all your controllers.
 * Extend this class in any new controllers:
 *     class Home extends BaseController
 *
 * For security be sure to declare any new methods as protected or private.
 */
abstract class BaseController extends Controller
{
    /**
     * Instance of the main Request object.
     *
     * @var CLIRequest|IncomingRequest
     */
    protected $request;

    /**
     * An array of helpers to be loaded automatically upon
     * class instantiation. These helpers will be available
     * to all other controllers that extend BaseController.
     *
     * @var list<string>
     */
    protected $helpers = [];

    protected $session;
    protected $user;
    protected $flashStatus;

    /**
     * Be sure to declare properties for any property fetch you initialized.
     * The creation of dynamic property is deprecated in PHP 8.2.
     */
    // protected $session;

    /**
     * @return void
     */
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        // Do Not Edit This Line
        parent::initController($request, $response, $logger);

        // Preload any models, libraries, etc, here.
        $this->session = \Config\Services::session();

        $this->flashStatus = $this->session->getFlashdata('status');

        $userModel = new UserModel();

        // Check if user is logged in
        if ($this->session->has('user_id')) {
            $userId = $this->session->get('user_id');
            $user = $userModel->find($userId);

            if ($user) {
                $this->user = $user;
            }
            else {
                $this->session->remove('user_id');
            }
        }
        else {
            $rememberToken = $this->request->getCookie('remember_me');

            if ($rememberToken) {
                $user = $userModel->where('remember_token', $rememberToken)->first();

                if ($user) {
                    $this->user = $user;
                    $this->session->set('user_id', $this->user['id']);
                }
            }
        }
        
        if ($this->user && $this->user['user_type'] == 'student') {
            $userLoginSessionModel = new UserLoginSessionModel();
            $userLoginSession = $userLoginSessionModel->where('user_id', $this->user['id'])->first();

            if ($userLoginSession == NULL || $userLoginSession['session_id'] != session_id()) {
                $this->session->destroy();

                $this->user = NULL;
            }
        }
    }
}
