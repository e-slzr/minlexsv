<?php
require_once __DIR__ . '/../models/Rol.php';

class RolController {
    private $rol;

    public function __construct() {
        $this->rol = new Rol();
    }

    public function handleRequest() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_POST['action'] ?? '';
            $response = ['success' => false, 'message' => ''];

            switch ($action) {
                case 'create':
                    try {
                        error_log("=== Creando nuevo rol ===");
                        error_log("Nombre: " . $_POST['nombre']);
                        
                        $this->rol->rol_nombre = $_POST['nombre'];
                        $this->rol->rol_descripcion = $_POST['descripcion'];

                        if ($this->rol->create()) {
                            error_log("Rol creado exitosamente");
                            $response = ['success' => true, 'message' => 'Rol creado exitosamente'];
                        } else {
                            error_log("Error al crear rol");
                            $response = ['success' => false, 'message' => 'Error al crear el rol'];
                        }
                    } catch (Exception $e) {
                        error_log("Error al crear rol: " . $e->getMessage());
                        $response = ['success' => false, 'message' => 'Error al crear el rol'];
                    }
                    break;

                case 'update':
                    try {
                        error_log("=== Actualizando rol ===");
                        error_log("ID: " . $_POST['id']);
                        error_log("Nombre: " . $_POST['nombre']);
                        
                        $this->rol->id = $_POST['id'];
                        $this->rol->rol_nombre = $_POST['nombre'];
                        $this->rol->rol_descripcion = $_POST['descripcion'];
                        $this->rol->estado = $_POST['estado'];
                        
                        if ($this->rol->update()) {
                            error_log("Rol actualizado exitosamente");
                            $response = ['success' => true, 'message' => 'Rol actualizado exitosamente'];
                        } else {
                            error_log("Error al actualizar rol");
                            $response = ['success' => false, 'message' => 'Error al actualizar el rol'];
                        }
                    } catch (Exception $e) {
                        error_log("Error al actualizar rol: " . $e->getMessage());
                        $response = ['success' => false, 'message' => 'Error al actualizar el rol'];
                    }
                    break;

                case 'toggleStatus':
                    try {
                        error_log("=== Cambiando estado de rol ===");
                        error_log("ID: " . $_POST['id']);
                        
                        $this->rol->id = $_POST['id'];
                        
                        // Verificar si el rol está en uso antes de deshabilitarlo
                        if ($this->rol->isInUse($_POST['id'])) {
                            error_log("No se puede deshabilitar el rol porque está en uso");
                            $response = ['success' => false, 'message' => 'No se puede deshabilitar el rol porque está siendo utilizado por uno o más usuarios'];
                        } else if ($this->rol->toggleStatus()) {
                            error_log("Estado de rol actualizado exitosamente");
                            $response = ['success' => true, 'message' => 'Estado de rol actualizado exitosamente'];
                        } else {
                            error_log("Error al actualizar estado de rol");
                            $response = ['success' => false, 'message' => 'Error al actualizar el estado del rol'];
                        }
                    } catch (Exception $e) {
                        error_log("Error al actualizar estado de rol: " . $e->getMessage());
                        $response = ['success' => false, 'message' => 'Error al actualizar el estado del rol: ' . $e->getMessage()];
                    }
                    break;
            }
            
            header('Content-Type: application/json');
            echo json_encode($response);
            exit;
        } 
        // else if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action'])) {
        //     if ($_GET['action'] === 'delete' && isset($_GET['id'])) {
        //         try {
        //             error_log("=== Eliminando rol ===");
        //             error_log("ID: " . $_GET['id']);
                    
        //             $response = ['success' => false, 'message' => ''];
        //             $this->rol->id = $_GET['id'];
                    
        //             if ($this->rol->delete()) {
        //                 error_log("Rol eliminado exitosamente");
        //                 header('Location: ../views/roles.php?success=3');
        //             } else {
        //                 error_log("Error al eliminar rol");
        //                 header('Location: ../views/roles.php?error=3');
        //             }
        //         } catch (Exception $e) {
        //             error_log("Error al eliminar rol: " . $e->getMessage());
        //             header('Location: ../views/roles.php?error=3');
        //         }
        //         exit;
        //     }
        // }
    }

    public function getRoles() {
        $result = $this->rol->read();
        return $result->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getActiveRoles() {
        $result = $this->rol->getActiveRoles();
        return $result->fetchAll(PDO::FETCH_ASSOC);
    }
}

// Si se hace una llamada directa al controlador
if(basename($_SERVER['PHP_SELF']) == basename(__FILE__)) {
    $controller = new RolController();
    $controller->handleRequest();
}
?>
