<?php
require_once __DIR__ . '/../models/Usuario.php';

class UsuarioController {
    private $usuario;

    public function __construct() {
        $this->usuario = new Usuario();
    }

    public function getUsuarios() {
        $result = $this->usuario->read();
        return $result->fetchAll(PDO::FETCH_ASSOC);
    }

    public function handleRequest() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_POST['action'] ?? '';
            $response = ['success' => false, 'message' => ''];

            switch ($action) {
                case 'create':
                    try {
                        error_log("=== Creando nuevo usuario ===");
                        error_log("Alias: " . $_POST['alias']);
                        
                        $this->usuario->usuario_alias = $_POST['alias'];
                        $this->usuario->usuario_nombre = $_POST['nombre'];
                        $this->usuario->usuario_apellido = $_POST['apellido'];
                        $this->usuario->usuario_rol_id = $_POST['rol_id'];
                        $this->usuario->usuario_departamento = $_POST['departamento'];

                        $hashedPassword = password_hash($_POST['password'], PASSWORD_DEFAULT);
                        if ($this->usuario->create($hashedPassword)) {
                            error_log("Usuario creado exitosamente");
                            $response = ['success' => true, 'message' => 'Usuario creado exitosamente'];
                        } else {
                            error_log("Error al crear usuario");
                            $response = ['success' => false, 'message' => 'Error al crear el usuario'];
                        }
                    } catch (Exception $e) {
                        error_log("Error al crear usuario: " . $e->getMessage());
                        $response = ['success' => false, 'message' => 'Error al crear el usuario'];
                    }
                    break;

                case 'update':
                    try {
                        error_log("=== Actualizando usuario ===");
                        error_log("ID: " . $_POST['id']);
                        error_log("Alias: " . $_POST['alias']);
                        
                        $this->usuario->id = $_POST['id'];
                        $this->usuario->usuario_alias = $_POST['alias'];
                        $this->usuario->usuario_nombre = $_POST['nombre'];
                        $this->usuario->usuario_apellido = $_POST['apellido'];
                        $this->usuario->usuario_rol_id = $_POST['rol_id'];
                        $this->usuario->usuario_departamento = $_POST['departamento'];
                        
                        if (!empty($_POST['password'])) {
                            error_log("Actualizando contraseña");
                            $hashedPassword = password_hash($_POST['password'], PASSWORD_DEFAULT);
                            if ($this->usuario->update($hashedPassword)) {
                                error_log("Usuario actualizado exitosamente");
                                $response = ['success' => true, 'message' => 'Usuario actualizado exitosamente'];
                            } else {
                                error_log("Error al actualizar usuario");
                                $response = ['success' => false, 'message' => 'Error al actualizar el usuario'];
                            }
                        } else {
                            if ($this->usuario->update()) {
                                error_log("Usuario actualizado exitosamente");
                                $response = ['success' => true, 'message' => 'Usuario actualizado exitosamente'];
                            } else {
                                error_log("Error al actualizar usuario");
                                $response = ['success' => false, 'message' => 'Error al actualizar el usuario'];
                            }
                        }
                    } catch (Exception $e) {
                        error_log("Error al actualizar usuario: " . $e->getMessage());
                        $response = ['success' => false, 'message' => 'Error al actualizar el usuario'];
                    }
                    break;

                case 'login':
                    if (isset($_POST['username']) && isset($_POST['password'])) {
                        try {
                            error_log("=== Inicio de intento de login ===");
                            error_log("Usuario: " . $_POST['username']);
                            error_log("Contraseña recibida (longitud): " . strlen($_POST['password']));
                            
                            $user = $this->usuario->login($_POST['username'], $_POST['password']);
                            
                            if ($user) {
                                session_start();
                                $_SESSION['user'] = $user;
                                $_SESSION['user_id'] = $user['id'];
                                $_SESSION['rol'] = $user['rol_nombre'];
                                $_SESSION['nombre_completo'] = $user['usuario_nombre'] . ' ' . $user['usuario_apellido'];
                                error_log("Login exitoso para usuario: " . $_POST['username']);
                                header("Location: ../views/home.php");
                                exit();
                            } else {
                                error_log("Login fallido para usuario: " . $_POST['username']);
                                header("Location: ../views/login.php?error=1");
                                exit();
                            }
                        } catch (Exception $e) {
                            error_log("Error en login: " . $e->getMessage());
                            header("Location: ../views/login.php?error=2");
                            exit();
                        }
                    }
                    break;
            }
            
            if (!headers_sent()) {
                header('Content-Type: application/json');
                echo json_encode($response);
            }
            exit;
        } else if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action'])) {
            if ($_GET['action'] === 'delete' && isset($_GET['id'])) {
                try {
                    error_log("=== Eliminando usuario ===");
                    error_log("ID: " . $_GET['id']);
                    
                    $response = ['success' => false, 'message' => ''];
                    $this->usuario->id = $_GET['id'];
                    
                    if ($this->usuario->delete()) {
                        error_log("Usuario eliminado exitosamente");
                        $response = ['success' => true, 'message' => 'Usuario eliminado exitosamente'];
                    } else {
                        error_log("Error al eliminar usuario");
                        $response = ['success' => false, 'message' => 'Error al eliminar el usuario'];
                    }
                    header('Content-Type: application/json');
                    echo json_encode($response);
                } catch (Exception $e) {
                    error_log("Error al eliminar usuario: " . $e->getMessage());
                    $response = ['success' => false, 'message' => 'Error al eliminar el usuario'];
                    header('Content-Type: application/json');
                    echo json_encode($response);
                }
                exit;
            } else if ($_GET['action'] === 'logout') {
                session_start();
                $_SESSION = array();
                session_destroy();
                session_write_close();
                setcookie(session_name(),'',0,'/');
                session_regenerate_id(true);
                header("Location: ../views/login.php");
                exit();
            }
        }
    }

    private function login() {
        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = isset($_POST['username']) ? trim($_POST['username']) : '';
            $password = isset($_POST['password']) ? trim($_POST['password']) : '';

            if(empty($username) || empty($password)) {
                header('Location: ../views/login.php?error=1');
                exit();
            }

            $result = $this->usuario->login($username, $password);
            
            if($result) {
                session_start();
                $_SESSION['user'] = $result;
                $_SESSION['user_id'] = $result['id'];
                $_SESSION['rol'] = $result['rol_nombre'];
                $_SESSION['nombre_completo'] = $result['usuario_nombre'] . ' ' . $result['usuario_apellido'];
                
                header('Location: ../views/home.php');
                exit();
            } else {
                header('Location: ../views/login.php?error=1');
                exit();
            }
        }
    }

    private function logout() {
        session_start();
        session_destroy();
        header('Location: ../views/login.php');
        exit();
    }

    private function list() {
        $result = $this->usuario->read();
        $usuarios = $result->fetchAll(PDO::FETCH_ASSOC);
        return $usuarios;
    }
}

// Si se hace una llamada directa al controlador
if(basename($_SERVER['PHP_SELF']) == basename(__FILE__)) {
    $controller = new UsuarioController();
    $controller->handleRequest();
}
?>
