<?php
require_once __DIR__ . '/HomeController.php';
require_once __DIR__ . '/DashboardController.php';
require_once __DIR__ . '/TaskController.php';

class Controller {
    public function handleRequest() {
        $action = isset($_GET['action']) ? $_GET['action'] : 'index';
        $method = $action . 'Action';

        if (method_exists($this, $method)) {
            $this->$method();
        } else {
            $this->indexAction();
        }
    }

    public function indexAction() {
        $homeController = new HomeController();
        $homeController->index();
    }

    public function dashboardAction() {
        $dashboardController = new DashboardController();
        $dashboardController->index();
    }

    public function taskAction() {
        $tasksController = new TaskController();
        $tasksController->index();
    }

    public function addStudentAction() {
        $homeController = new HomeController();
        $homeController->addStudent();
    }
}