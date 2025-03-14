<?php
require_once __DIR__ . '/../models/Usuario.php';

class UsuarioController {
    private $usuario;

    public function __construct() {
        $this->usuario = new Usuario();
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
            case 'login':
                $this->login();
                break;
            default:
                $this->list();
                break;
        }
    }

    private function login() {
        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = isset($_POST['username']) ? $_POST['username'] : '';
            $password = isset($_POST['password']) ? $_POST['password'] : '';

            $result = $this->usuario->login($username, $password);
            
            if($result) {
                session_start();
                $_SESSION['user'] = $result;
                $_SESSION['user_id'] = $result['id'];
                $_SESSION['rol'] = $result['rol_nombre'];
                
                header('Location: ../views/home.php');
                exit();
            } else {
                header('Location: ../index.php?error=1');
                exit();
            }
        }
    }

    private function create() {
        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->usuario->usuario_nombre = $_POST['nombre'];
            $this->usuario->usuario_apellido = $_POST['apellido'];
            $this->usuario->usuario_password = $_POST['password'];
            $this->usuario->usuario_rol_id = $_POST['rol_id'];
            $this->usuario->usuario_departamento = $_POST['departamento'];

            if($this->usuario->create()) {
                header('Location: ../views/users.php?success=1');
            } else {
                header('Location: ../views/users.php?error=1');
            }
            exit();
        }
    }

    private function update() {
        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->usuario->id = $_POST['id'];
            $this->usuario->usuario_nombre = $_POST['nombre'];
            $this->usuario->usuario_apellido = $_POST['apellido'];
            $this->usuario->usuario_password = !empty($_POST['password']) ? $_POST['password'] : "";
            $this->usuario->usuario_rol_id = $_POST['rol_id'];
            $this->usuario->usuario_departamento = $_POST['departamento'];

            if($this->usuario->update()) {
                header('Location: ../views/users.php?success=2');
            } else {
                header('Location: ../views/users.php?error=2');
            }
            exit();
        }
    }

    private function delete() {
        if(isset($_GET['id'])) {
            $this->usuario->id = $_GET['id'];
            if($this->usuario->delete()) {
                header('Location: ../views/users.php?success=3');
            } else {
                header('Location: ../views/users.php?error=3');
            }
            exit();
        }
    }

    private function list() {
        $result = $this->usuario->read();
        $usuarios = $result->fetchAll(PDO::FETCH_ASSOC);
        return $usuarios;
    }

    public function getUsuarios() {
        return $this->list();
    }
}

// Si se hace una llamada directa al controlador
if(basename($_SERVER['PHP_SELF']) == basename(__FILE__)) {
    $controller = new UsuarioController();
    $controller->handleRequest();
}
?>
