<?php
require_once __DIR__ . '/config/Database.php';

$database = new Database();
$conn = $database->getConnection();

// First, check if admin user already exists
$checkQuery = "SELECT id FROM usuarios WHERE usuario_alias = 'admin'";
$stmt = $conn->prepare($checkQuery);
$stmt->execute();

if ($stmt->rowCount() === 0) {
    // Insert admin user
    $query = "INSERT INTO usuarios (usuario_alias, usuario_nombre, usuario_apellido, usuario_password, usuario_rol_id, estado) 
              VALUES (:alias, :nombre, :apellido, :password, :rol_id, 'Activo')";
    
    $stmt = $conn->prepare($query);
    
    $alias = 'admin';
    $nombre = 'Administrador';
    $apellido = 'Sistema';
    $password = password_hash('admin', PASSWORD_DEFAULT);
    $rol_id = 1; // Assuming 1 is the ID for admin role
    
    $stmt->bindParam(':alias', $alias);
    $stmt->bindParam(':nombre', $nombre);
    $stmt->bindParam(':apellido', $apellido);
    $stmt->bindParam(':password', $password);
    $stmt->bindParam(':rol_id', $rol_id);
    
    if ($stmt->execute()) {
        echo "Admin user created successfully\n";
    } else {
        echo "Error creating admin user\n";
    }
} else {
    echo "Admin user already exists\n";
}