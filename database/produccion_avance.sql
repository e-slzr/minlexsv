CREATE TABLE IF NOT EXISTS produccion_avance (
    id INT PRIMARY KEY AUTO_INCREMENT,
    orden_produccion_id INT NOT NULL,
    proceso_id INT NOT NULL,
    cantidad_completada INT NOT NULL,
    fecha_registro DATETIME NOT NULL,
    operario_id INT NOT NULL,
    observaciones TEXT,
    estado ENUM('en_proceso', 'completado') NOT NULL DEFAULT 'en_proceso',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (orden_produccion_id) REFERENCES ordenes_produccion(id),
    FOREIGN KEY (proceso_id) REFERENCES procesos_produccion(id),
    FOREIGN KEY (operario_id) REFERENCES usuarios(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
