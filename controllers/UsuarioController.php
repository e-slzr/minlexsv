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
        header('Content-Type: application/json');
        
        try {
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $contentType = isset($_SERVER["CONTENT_TYPE"]) ? trim($_SERVER["CONTENT_TYPE"]) : '';
                
                if ($contentType === "application/json") {
                    $jsonData = file_get_contents('php://input');
                    $data = json_decode($jsonData, true);
                    
                    if ($data === null) {
                        throw new Exception('Error al decodificar JSON');
                    }
                } else {
                    $data = $_POST;
                }

                $action = $data['action'] ?? '';
                
                switch ($action) {
                    case 'login':
                        try {
                            if (!isset($data['username']) || !isset($data['password'])) {
                                echo json_encode(['success' => false, 'message' => 'Usuario y contraseña son requeridos']);
                                exit;
                            }

                            $username = $data['username'];
                            $password = $data['password'];
                            
                            $user = $this->usuario->getByUsername($username);
                            
                            if ($user && password_verify($password, $user['usuario_password'])) {
                                session_start();
                                
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
                                
                                echo json_encode([
                                    'success' => true, 
                                    'message' => 'Inicio de sesión exitoso',
                                    'redirect' => '../views/home.php'
                                ]);
                            } else {
                                echo json_encode(['success' => false, 'message' => 'Usuario o contraseña incorrectos']);
                            }
                        } catch (Exception $e) {
                            error_log("Error en login: " . $e->getMessage());
                            echo json_encode(['success' => false, 'message' => 'Error al iniciar sesión']);
                        }
                        exit;
                        break;

                    case 'logout':
                        // Limpiar y destruir la sesión
                        $_SESSION = array();
                        if (isset($_COOKIE[session_name()])) {
                            setcookie(session_name(), '', time()-42000, '/');
                        }
                        session_destroy();
                        
                        // Redireccionar al login
                        return ['success' => true, 'message' => 'Sesión cerrada exitosamente'];
                        break;

                    case 'create':
                        try {
                            $this->usuario->usuario_usuario = $data['usuario'] ?? '';
                            $this->usuario->usuario_nombre = $data['nombre'] ?? '';
                            $this->usuario->usuario_apellido = $data['apellido'] ?? '';
                            $this->usuario->usuario_password = password_hash($data['password'], PASSWORD_DEFAULT);
                            $this->usuario->usuario_rol_id = $data['rol_id'] ?? '';
                            $this->usuario->usuario_departamento = $data['departamento'] ?? '';
                            $this->usuario->usuario_modulo_id = !empty($data['modulo_id']) ? $data['modulo_id'] : null;
                            $this->usuario->estado = 'Activo';

                            if ($this->usuario->create($data)) {
                                return ['success' => true, 'message' => 'Usuario creado exitosamente'];
                            } else {
                                return ['success' => false, 'message' => 'Error al crear el usuario'];
                            }
                        } catch (Exception $e) {
                            error_log("Error al crear usuario: " . $e->getMessage());
                            return ['success' => false, 'message' => 'Error al crear el usuario'];
                        }
                        break;

                    case 'update':
                        $this->actualizarUsuario($data);
                        break;

                    case 'toggleStatus':
                        try {
                            $this->usuario->id = $data['id'] ?? '';
                            $this->usuario->estado = $data['estado'] === 'Activo' ? 'Inactivo' : 'Activo';

                            if ($this->usuario->toggleStatus()) {
                                return ['success' => true, 'message' => 'Estado actualizado exitosamente'];
                            } else {
                                return ['success' => false, 'message' => 'Error al actualizar el estado'];
                            }
                        } catch (Exception $e) {
                            error_log("Error al cambiar estado: " . $e->getMessage());
                            return ['success' => false, 'message' => 'Error al cambiar el estado'];
                        }
                        break;

                    default:
                        return ['success' => false, 'message' => 'Acción no válida'];
                        break;
                }
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }

    private function actualizarUsuario($data) {
        try {
            if (empty($data['id'])) {
                echo json_encode(['success' => false, 'message' => 'ID de usuario no proporcionado']);
                exit;
            }

            $this->usuario->id = $data['id'];
            $this->usuario->usuario_usuario = $data['usuario'] ?? '';
            $this->usuario->usuario_nombre = $data['nombre'] ?? '';
            $this->usuario->usuario_apellido = $data['apellido'] ?? '';
            $this->usuario->usuario_rol_id = $data['rol_id'] ?? '';
            $this->usuario->usuario_departamento = $data['departamento'] ?? '';
            $this->usuario->usuario_modulo_id = !empty($data['modulo_id']) ? $data['modulo_id'] : null;
            
            if (!empty($data['password'])) {
                $this->usuario->usuario_password = password_hash($data['password'], PASSWORD_DEFAULT);
            }

            if ($this->usuario->update()) {
                echo json_encode(['success' => true, 'message' => 'Usuario actualizado exitosamente']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error al actualizar el usuario']);
            }
        } catch (Exception $e) {
            error_log("Error al actualizar usuario: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Error al actualizar el usuario: ' . $e->getMessage()]);
        }
        exit;
    }
}

// Si se hace una llamada directa al controlador
if(basename($_SERVER['PHP_SELF']) == basename(__FILE__)) {
    $controller = new UsuarioController();
    $controller->handleRequest();
}
