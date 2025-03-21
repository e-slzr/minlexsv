-- Agregar columna estado a la tabla clientes
ALTER TABLE `clientes` 
ADD COLUMN `estado` enum('Activo','Inactivo') NOT NULL DEFAULT 'Activo' 
AFTER `cliente_correo`;
