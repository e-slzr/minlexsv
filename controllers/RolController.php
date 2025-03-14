<?php
require_once __DIR__ . '/../models/Rol.php';

class RolController {
    private $rol;

    public function __construct() {
        $this->rol = new Rol();
    }

    public function handleRequest() {
        $action = isset($_GET['action']) ? $_GET['action'] : 'list';

        switch($action) {
            case 'create':
                $this->create();
                break;
            case 'update':
                $this->update();
                break;
            case 'delete':
                $this->delete();
                break;
            default:
                $this->list();
                break;
        }
    }

    private function create() {
        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->rol->rol_nombre = $_POST['nombre'];
            $this->rol->rol_descripcion = $_POST['descripcion'];

            if($this->rol->create()) {
                header('Location: ../views/roles.php?success=1');
            } else {
                header('Location: ../views/roles.php?error=1');
            }
            exit();
        }
    }

    private function update() {
        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->rol->id = $_POST['id'];
            $this->rol->rol_nombre = $_POST['nombre'];
            $this->rol->rol_descripcion = $_POST['descripcion'];

            if($this->rol->update()) {
                header('Location: ../views/roles.php?success=2');
            } else {
                header('Location: ../views/roles.php?error=2');
            }
            exit();
        }
    }

    private function delete() {
        if(isset($_GET['id'])) {
            $this->rol->id = $_GET['id'];
            if($this->rol->delete()) {
                header('Location: ../views/roles.php?success=3');
            } else {
                header('Location: ../views/roles.php?error=3');
            }
            exit();
        }
    }

    private function list() {
        $result = $this->rol->read();
        $roles = $result->fetchAll(PDO::FETCH_ASSOC);
        return $roles;
    }

    public function getRoles() {
        return $this->list();
    }
}

// Si se hace una llamada directa al controlador
if(basename($_SERVER['PHP_SELF']) == basename(__FILE__)) {
    $controller = new RolController();
    $controller->handleRequest();
}
?>
