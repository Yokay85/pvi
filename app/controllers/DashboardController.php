<?php
class DashboardController {
    public function index() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . URL_ROOT . '/public/index.php?action=login');
            exit;
        }
        require_once __DIR__ . '/../views/Dashboard/dashboard.php';
    }
}
