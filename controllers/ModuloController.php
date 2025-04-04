<?php
require_once '../models/Modulo.php';

class ModuloController {
    private $moduloModel;

    public function __construct() {
        $this->moduloModel = new Modulo();
    }

    /**
     * Obtiene todos los módulos.
     * @return array Lista de todos los módulos.
     */
    public function getModulos() {
        // El modelo ahora devuelve un array directamente.
        return $this->moduloModel->read() ?: []; // Asegurar que siempre devuelva array
    }

    /**
     * Obtiene módulos filtrados por tipo y estado Activo.
     * @param string $tipo El tipo de módulo ('Costura', 'Corte', etc.).
     * @return array Lista de módulos filtrados.
     */
    public function getModulosPorTipo($tipo) {
        // El modelo ahora devuelve un array directamente.
        return $this->moduloModel->readByType($tipo) ?: []; // Asegurar que siempre devuelva array
    }

    /**
     * Obtiene todos los módulos activos.
     * Utilizado para llenar selectores donde solo se deben mostrar módulos operativos.
     * 
     * @return array Lista de módulos activos o array vacío si no hay.
     */
    public function getModulosActivos() {
        try {
            // findByEstado devuelve directamente el array de resultados
            $modulos = $this->moduloModel->findByEstado('Activo'); 
            // No necesitamos fetchAll aquí, ya es un array
            return $modulos ?? []; // Devolver el array o uno vacío si falla
        } catch (Exception $e) {
            error_log("Error en ModuloController::getModulosActivos(): " . $e->getMessage());
            return []; 
        }
    }

    public function crearModulo($datos) {
        if (isset($datos['action']) && $datos['action'] === 'crear') {
            $jsonData = file_get_contents('php://input');
            $datos = json_decode($jsonData, true);
        }

        if (empty($datos['modulo_codigo']) || empty($datos['modulo_tipo'])) {
            return ['success' => false, 'message' => 'El código y tipo son requeridos'];
        }

        if (!preg_match('/^[CT]-\d{2}$/', $datos['modulo_codigo'])) {
            return ['success' => false, 'message' => 'El código debe tener el formato C-XX o T-XX'];
        }

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

        $resultado = $this->moduloModel->create($moduloData);
        
        if ($resultado) {
            return ['success' => true, 'message' => 'Módulo creado exitosamente'];
        }
        return ['success' => false, 'message' => 'Error al crear el módulo'];
    }

    public function actualizarModulo($datos) {
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

        $resultado = $this->moduloModel->update($moduloData);
        
        if ($resultado) {
            return ['success' => true, 'message' => 'Módulo actualizado exitosamente'];
        }
        return ['success' => false, 'message' => 'Error al actualizar el módulo'];
    }

    public function cambiarEstado($id) {
        if (empty($id)) {
            return ['success' => false, 'message' => 'ID de módulo no proporcionado'];
        }

        $resultado = $this->moduloModel->toggleStatus($id);
        
        if ($resultado) {
            return ['success' => true, 'message' => 'Estado del módulo actualizado exitosamente'];
        }
        return ['success' => false, 'message' => 'Error al actualizar el estado del módulo'];
    }

    /**
     * Maneja las solicitudes AJAX para crear, actualizar o cambiar estado.
     * @param array $datos Datos recibidos (generalmente de JSON decodificado).
     * @return array Resultado de la operación en formato JSON.
     */
    public function handleRequest($datos) {
        header('Content-Type: application/json'); 
        
        try {
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

if (basename($_SERVER['PHP_SELF']) == 'ModuloController.php') {
    $controller = new ModuloController();
    $jsonData = file_get_contents('php://input');
    $datos = json_decode($jsonData, true);

    // Validar si $datos es null después de json_decode (puede pasar si el input está vacío o malformado)
    if ($datos === null && json_last_error() !== JSON_ERROR_NONE) {
        echo json_encode(['success' => false, 'message' => 'Error al decodificar JSON: ' . json_last_error_msg()]);
    } elseif ($datos === null) {
        // Si el input estaba vacío pero era JSON válido (ej. ""), $datos será null
        // O si no se envió cuerpo (ej. GET), $jsonData será false y $datos null.
        // Manejar como 'acción no especificada' o un error específico.
        echo json_encode(['success' => false, 'message' => 'Solicitud inválida o acción no especificada.']);
    } else {
        echo json_encode($controller->handleRequest($datos));
    }
    exit(); 
}