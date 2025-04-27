<?php
require_once __DIR__ . '/HomeController.php';
require_once __DIR__ . '/DashboardController.php';
require_once __DIR__ . '/TaskController.php';
require_once __DIR__ . '/AuthController.php';

class Controller {
    public function handleRequest() {
        $action = isset($_GET['action']) ? $_GET['action'] : 'index';
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $action)) {
             $action = 'index';
        }
        $method = $action . 'Action';

        if (method_exists($this, $method)) {
            $this->$method();
        } else {
             error_log("Action '{$action}' not found, defaulting to index.");
            $this->indexAction();
        }
    }

    public function indexAction() {
        $homeController = new HomeController();
        $homeController->index();
    }

    public function dashboardAction() {
        // Ensure user is logged in to access dashboard
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . URL_ROOT . '/public/index.php?action=login');
            exit;
        }
        $dashboardController = new DashboardController();
        $dashboardController->index();
    }

    public function taskAction() {
         // Ensure user is logged in to access tasks
         if (!isset($_SESSION['user_id'])) {
             header('Location: ' . URL_ROOT . '/public/index.php?action=login');
             exit;
         }
        $tasksController = new TaskController();
        $tasksController->index();
    }

    public function addStudentAction() {
        // Permission check is handled within HomeController->addStudent
        $homeController = new HomeController();
        $homeController->addStudent();
    }

     public function updateStudentAction() {
         // Permission check is handled within HomeController->updateStudent
         $homeController = new HomeController();
         $homeController->updateStudent();
     }

     public function deleteStudentAction() {
         // Permission check is handled within HomeController->deleteStudent
         $homeController = new HomeController();
         $homeController->deleteStudent();
     }

    // Add login action
    public function loginAction() {
        $authController = new AuthController();
        $authController->login();
    }

    // Add logout action
    public function logoutAction() {
        $authController = new AuthController();
        $authController->logout();
    }
}