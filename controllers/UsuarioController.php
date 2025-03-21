<?php
require_once __DIR__ . '/../models/Usuario.php';

class UsuarioController {
    private $usuario;

    public function __construct() {
        $this->usuario = new Usuario();
    }

    public function getUsuarios() {
        try {
            return $this->usuario->getAll();
        } catch (Exception $e) {
            error_log("Error al obtener usuarios: " . $e->getMessage());
            return null;
        }
    }

    public function handleRequest() {
        session_start();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_POST['action'] ?? '';
            $response = ['success' => false, 'message' => ''];

            switch ($action) {
                case 'login':
                    try {
                        $username = $_POST['username'];
                        $password = $_POST['password'];
                        
                        $user = $this->usuario->getByUsername($username);
                        
                        if ($user && password_verify($password, $user['usuario_password'])) {
                            // Obtener el nombre del rol
                            $rol = $this->usuario->getRolById($user['usuario_rol_id']);
                            $rolNombre = $rol ? $rol['rol_nombre'] : 'Usuario';

                            // Crear el nombre completo
                            $nombreCompleto = trim($user['usuario_nombre'] . ' ' . $user['usuario_apellido']);
                            
                            $_SESSION['user'] = [
                                'id' => $user['id'],
                                'nombre' => $user['usuario_nombre'],
                                'apellido' => $user['usuario_apellido'],
                                'nombre_completo' => $nombreCompleto,
                                'usuario' => $user['usuario_usuario'],
                                'rol_id' => $user['usuario_rol_id'],
                                'rol_nombre' => $rolNombre,
                                'departamento' => $user['usuario_departamento']
                            ];
                            header("Location: ../views/home.php");
                            exit();
                        } else {
                            header("Location: ../views/login.php?error=1");
                            exit();
                        }
                    } catch (Exception $e) {
                        error_log("Error en login: " . $e->getMessage());
                        header("Location: ../views/login.php?error=1");
                        exit();
                    }
                    break;

                case 'logout':
                    // Limpiar y destruir la sesión
                    $_SESSION = array();
                    if (isset($_COOKIE[session_name()])) {
                        setcookie(session_name(), '', time()-42000, '/');
                    }
                    session_destroy();
                    
                    // Redireccionar al login
                    header("Location: ../views/login.php");
                    exit();
                    break;

                case 'create':
                    try {
                        $this->usuario->usuario_usuario = $_POST['usuario'] ?? '';
                        $this->usuario->usuario_nombre = $_POST['nombre'] ?? '';
                        $this->usuario->usuario_apellido = $_POST['apellido'] ?? '';
                        $this->usuario->usuario_password = password_hash($_POST['password'], PASSWORD_DEFAULT);
                        $this->usuario->usuario_rol_id = $_POST['rol_id'] ?? '';
                        $this->usuario->usuario_departamento = $_POST['departamento'] ?? '';
                        $this->usuario->estado = 'Activo';

                        if ($this->usuario->create()) {
                            $response = ['success' => true, 'message' => 'Usuario creado exitosamente'];
                        } else {
                            $response = ['success' => false, 'message' => 'Error al crear el usuario'];
                        }
                    } catch (Exception $e) {
                        error_log("Error al crear usuario: " . $e->getMessage());
                        $response = ['success' => false, 'message' => 'Error al crear el usuario'];
                    }
                    break;

                case 'update':
                    try {
                        $this->usuario->id = $_POST['id'] ?? '';
                        $this->usuario->usuario_usuario = $_POST['usuario'] ?? '';
                        $this->usuario->usuario_nombre = $_POST['nombre'] ?? '';
                        $this->usuario->usuario_apellido = $_POST['apellido'] ?? '';
                        $this->usuario->usuario_rol_id = $_POST['rol_id'] ?? '';
                        $this->usuario->usuario_departamento = $_POST['departamento'] ?? '';
                        
                        if (!empty($_POST['password'])) {
                            $this->usuario->usuario_password = password_hash($_POST['password'], PASSWORD_DEFAULT);
                        }

                        if ($this->usuario->update()) {
                            $response = ['success' => true, 'message' => 'Usuario actualizado exitosamente'];
                        } else {
                            $response = ['success' => false, 'message' => 'Error al actualizar el usuario'];
                        }
                    } catch (Exception $e) {
                        error_log("Error al actualizar usuario: " . $e->getMessage());
                        $response = ['success' => false, 'message' => 'Error al actualizar el usuario'];
                    }
                    break;

                case 'toggleStatus':
                    try {
                        $this->usuario->id = $_POST['id'] ?? '';
                        $this->usuario->estado = $_POST['estado'] === 'Activo' ? 'Inactivo' : 'Activo';

                        if ($this->usuario->toggleStatus()) {
                            $response = ['success' => true, 'message' => 'Estado actualizado exitosamente'];
                        } else {
                            $response = ['success' => false, 'message' => 'Error al actualizar el estado'];
                        }
                    } catch (Exception $e) {
                        error_log("Error al cambiar estado: " . $e->getMessage());
                        $response = ['success' => false, 'message' => 'Error al cambiar el estado'];
                    }
                    break;

                default:
                    $response = ['success' => false, 'message' => 'Acción no válida'];
                    break;
            }

            header('Content-Type: application/json');
            echo json_encode($response);
            exit();
        }
    }
}

// Si se hace una llamada directa al controlador
if(basename($_SERVER['PHP_SELF']) == basename(__FILE__)) {
    $controller = new UsuarioController();
    $controller->handleRequest();
}
