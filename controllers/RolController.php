<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user'])) {
    header('Location: ../views/login.php');
    exit();
}

require_once __DIR__ . '/../models/Rol.php';

class RolController {
    private $rol;

    public function __construct() {
        $this->rol = new Rol();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            header('Content-Type: application/json');
            
            if (isset($_POST['action'])) {
                switch ($_POST['action']) {
                    case 'create':
                        $this->createRol();
                        break;
                    case 'update':
                        $this->updateRol();
                        break;
                    case 'toggleStatus':
                        $this->toggleStatus();
                        break;
                }
            }
        }
    }

    public function getRoles() {
        return $this->rol->getAll();
    }

    public function getActiveRoles() {
        return $this->rol->getActiveRoles();
    }

    private function createRol() {
        try {
            if (empty($_POST['nombre']) || empty($_POST['descripcion'])) {
                throw new Exception("Nombre y descripción son requeridos");
            }

            $data = [
                'rol_nombre' => $_POST['nombre'],
                'rol_descripcion' => $_POST['descripcion'],
                'estado' => 'Activo'
            ];

            if ($this->rol->create($data)) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Rol creado exitosamente'
                ]);
            } else {
                throw new Exception("Error al crear el rol");
            }
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
        exit;
    }

    private function updateRol() {
        try {
            if (empty($_POST['id']) || empty($_POST['nombre']) || empty($_POST['descripcion'])) {
                throw new Exception("Todos los campos son requeridos");
            }

            $data = [
                'id' => $_POST['id'],
                'rol_nombre' => $_POST['nombre'],
                'rol_descripcion' => $_POST['descripcion']
            ];

            if ($this->rol->update($data)) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Rol actualizado exitosamente'
                ]);
            } else {
                throw new Exception("Error al actualizar el rol");
            }
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
        exit;
    }

    private function toggleStatus() {
        try {
            if (empty($_POST['id']) || !isset($_POST['estado'])) {
                throw new Exception("ID y estado son requeridos");
            }

            if ($this->rol->toggleStatus($_POST['id'], $_POST['estado'])) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Estado del rol actualizado exitosamente'
                ]);
            } else {
                throw new Exception("Error al actualizar el estado del rol");
            }
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
        exit;
    }
}

// Si se hace una llamada directa al controlador
if(basename($_SERVER['PHP_SELF']) == basename(__FILE__)) {
    $controller = new RolController();
}
?>
