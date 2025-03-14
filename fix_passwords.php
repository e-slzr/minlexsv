<?php
require_once 'config/database.php';

try {
    $database = new Database();
    $conn = $database->getConnection();
    
    // Obtener todos los usuarios
    $stmt = $conn->query("SELECT id, usuario_alias, usuario_password FROM usuarios");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($users as $user) {
        // Verificar si la contraseña ya está hasheada correctamente
        if (!password_verify($user['usuario_alias'], $user['usuario_password'])) {
            // Si no está hasheada correctamente, crear un nuevo hash usando el alias como contraseña
            $hash = password_hash($user['usuario_alias'], PASSWORD_DEFAULT);
            
            // Actualizar la contraseña
            $update = $conn->prepare("UPDATE usuarios SET usuario_password = :password WHERE id = :id");
            $update->bindParam(':password', $hash);
            $update->bindParam(':id', $user['id']);
            
            if ($update->execute()) {
                echo "Contraseña actualizada para usuario: " . $user['usuario_alias'] . "\n";
                echo "Nueva contraseña: " . $user['usuario_alias'] . "\n";
            } else {
                echo "Error al actualizar contraseña para: " . $user['usuario_alias'] . "\n";
            }
        } else {
            echo "La contraseña ya está correcta para: " . $user['usuario_alias'] . "\n";
        }
    }
    
    echo "\nProceso completado. Ahora todos los usuarios pueden iniciar sesión usando su alias como contraseña.\n";
    echo "Por ejemplo, si el alias es 'juan', la contraseña también será 'juan'.\n";
    echo "Se recomienda que los usuarios cambien su contraseña después del primer inicio de sesión.\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
