<?php
session_start();
require_once __DIR__.'/../config/db.php';
require_once __DIR__.'/../models/Category.php';

if(!isset($_SESSION['is_admin']) || $_SESSION['is_admin']!=1){
    header('Location: ../views/login.php'); exit;
}

$category = new Category($conn);
$action = $_GET['action'] ?? 'index';

switch($action){
    case 'index':
        $categories = $category->getAll();
        include __DIR__.'/../views/categories_dashboard.php';
        break;

    case 'create':
        if($_SERVER['REQUEST_METHOD']==='POST'){
            $name = $_POST['name'] ?? '';
            if($name!='') $category->create($name);
        }
        header('Location: CategoryController.php?action=index'); exit;
        break;

    case 'edit':
        $id = $_GET['id'] ?? 0;
        if($_SERVER['REQUEST_METHOD']==='POST'){
            $name = $_POST['name'] ?? '';
            if($name!='') $category->update($id, $name);
        }
        header('Location: CategoryController.php?action=index'); exit;
        break;

    case 'delete':
        $id = $_GET['id'] ?? 0;
        $category->delete($id);
        header('Location: CategoryController.php?action=index'); exit;
        break;

    default:
        header('Location: CategoryController.php?action=index'); exit;
}