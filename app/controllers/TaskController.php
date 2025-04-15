<?php
class TaskController {
    public function index() {
        // Load the tasks view
        require_once __DIR__ . '/../views/Task/task.php';
    }
}