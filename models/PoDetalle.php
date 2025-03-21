<?php
class PoDetalle {
    private $conn;
    private $table_name = "po_detalle";

    // Propiedades del objeto
    public $id;
    public $pd_id_po;
    public $pd_item;
    public $pd_cant_piezas_total;
    public $pd_pcs_carton;
    public $pd_pcs_poly;
    public $pd_estado;
    public $pd_precio_unitario;

    public function __construct($db = null) {
        if ($db === null) {
            require_once __DIR__ . '/../config/Database.php';
            $database = new Database();
            $this->conn = $database->connect();
        } else {
            $this->conn = $db;
        }
    }

    // Crear un nuevo detalle de PO
    public function create($data) {
        try {
            $query = "INSERT INTO " . $this->table_name . "
                    (pd_id_po, pd_item, pd_cant_piezas_total,
                     pd_pcs_carton, pd_pcs_poly, pd_estado,
                     pd_precio_unitario)
                    VALUES
                    (:pd_id_po, :pd_item, :pd_cant_piezas_total,
                     :pd_pcs_carton, :pd_pcs_poly, :pd_estado,
                     :pd_precio_unitario)";

            $stmt = $this->conn->prepare($query);

            // Bind values
            $stmt->bindParam(":pd_id_po", $data['pd_id_po']);
            $stmt->bindParam(":pd_item", $data['pd_item']);
            $stmt->bindParam(":pd_cant_piezas_total", $data['pd_cant_piezas_total']);
            $stmt->bindParam(":pd_pcs_carton", $data['pd_pcs_carton']);
            $stmt->bindParam(":pd_pcs_poly", $data['pd_pcs_poly']);
            $stmt->bindParam(":pd_estado", $data['pd_estado']);
            $stmt->bindParam(":pd_precio_unitario", $data['pd_precio_unitario']);

            if ($stmt->execute()) {
                return $this->conn->lastInsertId();
            }
            return false;
        } catch (PDOException $e) {
            throw new Exception("Error al crear detalle de PO: " . $e->getMessage());
        }
    }

    // Leer todos los detalles de una PO específica
    public function readByPo($poId) {
        $query = "SELECT 
                    pd.*,
                    i.item_numero,
                    i.item_nombre,
                    i.item_descripcion,
                    i.item_talla,
                    COALESCE(
                        ROUND(
                            (SUM(op.op_cantidad_completada) / NULLIF(SUM(op.op_cantidad_asignada), 0)) * 100
                        ),
                        0
                    ) as progreso
                FROM " . $this->table_name . " pd
                LEFT JOIN items i ON pd.pd_item = i.id
                LEFT JOIN ordenes_produccion op ON op.op_id_pd = pd.id
                WHERE pd.pd_id_po = :po_id
                GROUP BY pd.id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":po_id", $poId);

        try {
            $stmt->execute();
            return $stmt;
        } catch(PDOException $e) {
            error_log("Error en PoDetalle::readByPo(): " . $e->getMessage());
            throw $e;
        }
    }

    // Leer un detalle específico
    public function readOne() {
        $query = "SELECT 
                    pd.*,
                    i.item_numero,
                    i.item_nombre,
                    i.item_descripcion,
                    i.item_talla
                FROM " . $this->table_name . " pd
                LEFT JOIN items i ON pd.pd_item = i.id
                WHERE pd.id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $this->id);

        try {
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            error_log("Error en PoDetalle::readOne(): " . $e->getMessage());
            throw $e;
        }
    }

    // Actualizar detalle de PO
    public function update() {
        $query = "UPDATE " . $this->table_name . "
                SET
                    pd_cant_piezas_total = :pd_cant_piezas_total,
                    pd_pcs_carton = :pd_pcs_carton,
                    pd_pcs_poly = :pd_pcs_poly,
                    pd_estado = :pd_estado,
                    pd_precio_unitario = :pd_precio_unitario
                WHERE 
                    id = :id";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":pd_cant_piezas_total", $this->pd_cant_piezas_total);
        $stmt->bindParam(":pd_pcs_carton", $this->pd_pcs_carton);
        $stmt->bindParam(":pd_pcs_poly", $this->pd_pcs_poly);
        $stmt->bindParam(":pd_estado", $this->pd_estado);
        $stmt->bindParam(":pd_precio_unitario", $this->pd_precio_unitario);
        $stmt->bindParam(":id", $this->id);

        try {
            return $stmt->execute();
        } catch(PDOException $e) {
            error_log("Error en PoDetalle::update(): " . $e->getMessage());
            throw $e;
        }
    }

    // Eliminar detalle de PO
    public function delete() {
        // Primero verificar si hay órdenes de producción asociadas
        $query = "SELECT COUNT(*) as count FROM ordenes_produccion WHERE op_id_pd = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $this->id);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result['count'] > 0) {
            throw new Exception("No se puede eliminar el detalle porque tiene órdenes de producción asociadas");
        }

        // Si no hay órdenes de producción, proceder con la eliminación
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $this->id);

        try {
            return $stmt->execute();
        } catch(PDOException $e) {
            error_log("Error en PoDetalle::delete(): " . $e->getMessage());
            throw $e;
        }
    }

    // Calcular el total de una PO
    public function calculatePoTotal($poId) {
        $query = "SELECT 
                    SUM(pd_cant_piezas_total * pd_precio_unitario) as total
                FROM " . $this->table_name . "
                WHERE pd_id_po = :po_id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":po_id", $poId);

        try {
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['total'] ?: 0;
        } catch(PDOException $e) {
            error_log("Error en PoDetalle::calculatePoTotal(): " . $e->getMessage());
            throw $e;
        }
    }

    // Obtener el progreso de un detalle específico
    public function getProgress() {
        $query = "SELECT 
                    COALESCE(
                        ROUND(
                            (SUM(op.op_cantidad_completada) / NULLIF(SUM(op.op_cantidad_asignada), 0)) * 100
                        ),
                        0
                    ) as progreso
                FROM ordenes_produccion op
                WHERE op.op_id_pd = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $this->id);

        try {
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['progreso'];
        } catch(PDOException $e) {
            error_log("Error en PoDetalle::getProgress(): " . $e->getMessage());
            throw $e;
        }
    }

    // Verificar si una PO tiene detalles asociados
    public function hasDetails($poId) {
        $query = "SELECT COUNT(*) as count FROM " . $this->table_name . " WHERE pd_id_po = :po_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":po_id", $poId);
        
        try {
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row['count'] > 0;
        } catch(PDOException $e) {
            error_log("Error en PoDetalle::hasDetails(): " . $e->getMessage());
            throw $e;
        }
    }
}
?>
