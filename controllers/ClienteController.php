<?php
require_once __DIR__ . '/../models/Cliente.php';

class ClienteController {
    private $cliente;

    public function __construct() {
        $this->cliente = new Cliente();
    }

    public function handleRequest() {
        // Determina qué acción realizar basado en el parámetro 'action' de la URL
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
            case 'view':
                $this->view();
                break;
            case 'search':
                $this->search();
                break;
            default:
                $this->list();
                break;
        }
    }

    // Crear un nuevo cliente
    private function create() {
        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Recibimos los datos del formulario
            $this->cliente->cliente_empresa = $_POST['empresa'];
            $this->cliente->cliente_nombre = $_POST['nombre'];
            $this->cliente->cliente_apellido = $_POST['apellido'];
            $this->cliente->cliente_direccion = $_POST['direccion'];
            $this->cliente->cliente_telefono = $_POST['telefono'];
            $this->cliente->cliente_correo = $_POST['correo'];

            if($this->cliente->create()) {
                // Si se creó exitosamente, redirigimos con mensaje de éxito
                header('Location: ../views/clientes.php?success=1');
            } else {
                // Si hubo error, redirigimos con mensaje de error
                header('Location: ../views/clientes.php?error=1');
            }
            exit();
        }
    }

    // Actualizar un cliente existente
    private function update() {
        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->cliente->id = $_POST['id'];
            $this->cliente->cliente_empresa = $_POST['empresa'];
            $this->cliente->cliente_nombre = $_POST['nombre'];
            $this->cliente->cliente_apellido = $_POST['apellido'];
            $this->cliente->cliente_direccion = $_POST['direccion'];
            $this->cliente->cliente_telefono = $_POST['telefono'];
            $this->cliente->cliente_correo = $_POST['correo'];

            if($this->cliente->update()) {
                header('Location: ../views/clientes.php?success=2');
            } else {
                header('Location: ../views/clientes.php?error=2');
            }
            exit();
        }
    }

    // Eliminar un cliente
    private function delete() {
        if(isset($_GET['id'])) {
            $this->cliente->id = $_GET['id'];
            if($this->cliente->delete()) {
                header('Location: ../views/clientes.php?success=3');
            } else {
                header('Location: ../views/clientes.php?error=3');
            }
            exit();
        }
    }

    // Ver detalles de un cliente
    private function view() {
        if(isset($_GET['id'])) {
            $this->cliente->id = $_GET['id'];
            if($this->cliente->readOne()) {
                return [
                    'id' => $this->cliente->id,
                    'empresa' => $this->cliente->cliente_empresa,
                    'nombre' => $this->cliente->cliente_nombre,
                    'apellido' => $this->cliente->cliente_apellido,
                    'direccion' => $this->cliente->cliente_direccion,
                    'telefono' => $this->cliente->cliente_telefono,
                    'correo' => $this->cliente->cliente_correo
                ];
            }
        }
        return null;
    }

    // Listar todos los clientes
    private function list() {
        $result = $this->cliente->read();
        return $result->fetchAll(PDO::FETCH_ASSOC);
    }

    // Buscar clientes
    private function search() {
        if(isset($_GET['keyword'])) {
            $result = $this->cliente->search($_GET['keyword']);
            return $result->fetchAll(PDO::FETCH_ASSOC);
        }
        return [];
    }

    // Métodos públicos para usar desde las vistas
    public function getClientes() {
        return $this->list();
    }

    public function getCliente($id) {
        $this->cliente->id = $id;
        return $this->cliente->readOne() ? [
            'id' => $this->cliente->id,
            'empresa' => $this->cliente->cliente_empresa,
            'nombre' => $this->cliente->cliente_nombre,
            'apellido' => $this->cliente->cliente_apellido,
            'direccion' => $this->cliente->cliente_direccion,
            'telefono' => $this->cliente->cliente_telefono,
            'correo' => $this->cliente->cliente_correo
        ] : null;
    }

    public function buscarClientes($keyword) {
        return $this->cliente->search($keyword)->fetchAll(PDO::FETCH_ASSOC);
    }
}

// Si se hace una llamada directa al controlador
if(basename($_SERVER['PHP_SELF']) == basename(__FILE__)) {
    $controller = new ClienteController();
    $controller->handleRequest();
}
?>
