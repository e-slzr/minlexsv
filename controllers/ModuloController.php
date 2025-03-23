<?php
require_once '../models/Modulo.php';

class ModuloController {
    private $modelo;

    public function __construct() {
        $this->modelo = new Modulo();
    }

    public function getModulos() {
        return $this->modelo->read();
    }

    public function getModulosPorTipo($tipo) {
        return $this->modelo->readByType($tipo);
    }

    public function crearModulo($datos) {
        // Decodificar JSON si los datos vienen en ese formato
        if (isset($datos['action']) && $datos['action'] === 'crear') {
            $jsonData = file_get_contents('php://input');
            $datos = json_decode($jsonData, true);
        }

        // Validar datos requeridos
        if (empty($datos['modulo_codigo']) || empty($datos['modulo_tipo'])) {
            return ['success' => false, 'message' => 'El código y tipo son requeridos'];
        }

        // Validar formato del código
        if (!preg_match('/^[CT]-\d{2}$/', $datos['modulo_codigo'])) {
            return ['success' => false, 'message' => 'El código debe tener el formato C-XX o T-XX'];
        }

        // Validar que el tipo coincida con el prefijo del código
        $prefijo = substr($datos['modulo_codigo'], 0, 1);
        $tipoEsperado = ($prefijo === 'C') ? 'Costura' : 'Corte';
        if ($datos['modulo_tipo'] !== $tipoEsperado) {
            return ['success' => false, 'message' => 'El tipo de módulo no coincide con el prefijo del código'];
        }

        $moduloData = [
            'modulo_codigo' => $datos['modulo_codigo'],
            'modulo_tipo' => $datos['modulo_tipo'],
            'modulo_descripcion' => $datos['modulo_descripcion'] ?? null
        ];

        $resultado = $this->modelo->create($moduloData);
        
        if ($resultado) {
            return ['success' => true, 'message' => 'Módulo creado exitosamente'];
        }
        return ['success' => false, 'message' => 'Error al crear el módulo'];
    }

    public function actualizarModulo($datos) {
        // Decodificar JSON si los datos vienen en ese formato
        if (isset($datos['action']) && $datos['action'] === 'actualizar') {
            $jsonData = file_get_contents('php://input');
            $datos = json_decode($jsonData, true);
        }

        if (empty($datos['id']) || empty($datos['modulo_codigo']) || empty($datos['modulo_tipo'])) {
            return ['success' => false, 'message' => 'Todos los campos requeridos deben estar completos'];
        }

        if (!preg_match('/^[CT]-\d{2}$/', $datos['modulo_codigo'])) {
            return ['success' => false, 'message' => 'El código debe tener el formato C-XX o T-XX'];
        }

        // Validar que el tipo coincida con el prefijo del código
        $prefijo = substr($datos['modulo_codigo'], 0, 1);
        $tipoEsperado = ($prefijo === 'C') ? 'Costura' : 'Corte';
        if ($datos['modulo_tipo'] !== $tipoEsperado) {
            return ['success' => false, 'message' => 'El tipo de módulo no coincide con el prefijo del código'];
        }

        $moduloData = [
            'id' => $datos['id'],
            'modulo_codigo' => $datos['modulo_codigo'],
            'modulo_tipo' => $datos['modulo_tipo'],
            'modulo_descripcion' => $datos['modulo_descripcion'] ?? null
        ];

        $resultado = $this->modelo->update($moduloData);
        
        if ($resultado) {
            return ['success' => true, 'message' => 'Módulo actualizado exitosamente'];
        }
        return ['success' => false, 'message' => 'Error al actualizar el módulo'];
    }

    public function cambiarEstado($id) {
        if (empty($id)) {
            return ['success' => false, 'message' => 'ID de módulo no proporcionado'];
        }

        $resultado = $this->modelo->toggleStatus($id);
        
        if ($resultado) {
            return ['success' => true, 'message' => 'Estado del módulo actualizado exitosamente'];
        }
        return ['success' => false, 'message' => 'Error al actualizar el estado del módulo'];
    }

    public function handleRequest() {
        header('Content-Type: application/json'); // Aseguramos que la respuesta sea JSON
        
        try {
            // Obtener el contenido JSON raw
            $jsonData = file_get_contents('php://input');
            $datos = json_decode($jsonData, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception('Error al decodificar JSON: ' . json_last_error_msg());
            }

            if (!isset($datos['action'])) {
                throw new Exception('Acción no especificada');
            }

            switch ($datos['action']) {
                case 'crear':
                    return $this->crearModulo($datos);
                case 'actualizar':
                    return $this->actualizarModulo($datos);
                case 'toggle_status':
                    return $this->cambiarEstado($datos['id']);
                default:
                    throw new Exception('Acción no válida');
            }
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}

// Asegurarnos de que solo se procese si se accede directamente al controlador
if (basename($_SERVER['PHP_SELF']) == 'ModuloController.php') {
    $controller = new ModuloController();
    echo json_encode($controller->handleRequest());
    exit(); // Asegurarnos de que no se envíe nada más
} 