<?php
require_once __DIR__ . '/../models/Cliente.php';

class ClienteController {
    private $cliente;

    public function __construct() {
        $this->cliente = new Cliente();
    }

    public function getClientes() {
        try {
            return $this->cliente->getAll();
        } catch (Exception $e) {
            error_log("Error al obtener clientes: " . $e->getMessage());
            return null;
        }
    }

    public function handleRequest() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_POST['action'] ?? '';
            $response = ['success' => false, 'message' => ''];

            switch ($action) {
                case 'create':
                    try {
                        $this->cliente->cliente_empresa = $_POST['cliente_empresa'];
                        $this->cliente->cliente_nombre = $_POST['cliente_nombre'];
                        $this->cliente->cliente_apellido = $_POST['cliente_apellido'];
                        $this->cliente->cliente_direccion = $_POST['cliente_direccion'];
                        $this->cliente->cliente_telefono = $_POST['cliente_telefono'];
                        $this->cliente->cliente_correo = $_POST['cliente_correo'];
                        $this->cliente->estado = 'Activo';

                        if ($this->cliente->create()) {
                            $response = ['success' => true, 'message' => 'Cliente creado exitosamente'];
                        } else {
                            $response = ['success' => false, 'message' => 'Error al crear el cliente'];
                        }
                    } catch (Exception $e) {
                        error_log("Error al crear cliente: " . $e->getMessage());
                        $response = ['success' => false, 'message' => 'Error al crear el cliente'];
                    }
                    break;

                case 'update':
                    try {
                        $this->cliente->id = $_POST['id'];
                        $this->cliente->cliente_empresa = $_POST['cliente_empresa'];
                        $this->cliente->cliente_nombre = $_POST['cliente_nombre'];
                        $this->cliente->cliente_apellido = $_POST['cliente_apellido'];
                        $this->cliente->cliente_direccion = $_POST['cliente_direccion'];
                        $this->cliente->cliente_telefono = $_POST['cliente_telefono'];
                        $this->cliente->cliente_correo = $_POST['cliente_correo'];

                        if ($this->cliente->update()) {
                            $response = ['success' => true, 'message' => 'Cliente actualizado exitosamente'];
                        } else {
                            $response = ['success' => false, 'message' => 'Error al actualizar el cliente'];
                        }
                    } catch (Exception $e) {
                        error_log("Error al actualizar cliente: " . $e->getMessage());
                        $response = ['success' => false, 'message' => 'Error al actualizar el cliente'];
                    }
                    break;

                case 'toggleStatus':
                    try {
                        $this->cliente->id = $_POST['id'];
                        $this->cliente->estado = $_POST['estado'];
                        
                        if ($this->cliente->toggleStatus()) {
                            $response = ['success' => true, 'message' => 'Estado de cliente actualizado exitosamente'];
                        } else {
                            $response = ['success' => false, 'message' => 'Error al actualizar el estado del cliente'];
                        }
                    } catch (Exception $e) {
                        error_log("Error al actualizar estado de cliente: " . $e->getMessage());
                        $response = ['success' => false, 'message' => 'Error al actualizar el estado del cliente'];
                    }
                    break;

                case 'list':
                    try {
                        $clientes = $this->getClientes();
                        $response = ['success' => true, 'data' => $clientes];
                    } catch (Exception $e) {
                        error_log("Error al listar clientes: " . $e->getMessage());
                        $response = ['success' => false, 'message' => 'Error al obtener la lista de clientes'];
                    }
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
    $controller = new ClienteController();
    $controller->handleRequest();
}
