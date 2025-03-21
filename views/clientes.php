<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit();
}

require_once '../controllers/ClienteController.php';
$clienteController = new ClienteController();
$clientes = $clienteController->getClientes() ?? [];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MINLEX | Clientes</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../css/style_main.css">
</head>
<body>
    <?php include '../components/menu_lateral.php'; ?>
    
    <main>
        <div class="titulo-vista">
            <h1><strong>Gestión de Clientes</strong></h1>
            <button type="button" class="btn btn-dark" data-bs-toggle="modal" data-bs-target="#clienteModal">
                <i class="fas fa-plus"></i> Nuevo Cliente
            </button>
        </div>

        <div class="container-fluid">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Empresa</th>
                            <th>Contacto</th>
                            <th>Teléfono</th>
                            <th>Email</th>
                            <th>Estado</th>
                            <th>Opciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($clientes as $cliente): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($cliente['id']); ?></td>
                                <td><?php echo htmlspecialchars($cliente['cliente_empresa']); ?></td>
                                <td><?php echo htmlspecialchars($cliente['cliente_contacto']); ?></td>
                                <td><?php echo htmlspecialchars($cliente['cliente_telefono']); ?></td>
                                <td><?php echo htmlspecialchars($cliente['cliente_email']); ?></td>
                                <td>
                                    <span class="badge <?php echo $cliente['estado'] === 'Activo' ? 'bg-success' : 'bg-danger'; ?>">
                                        <?php echo htmlspecialchars($cliente['estado']); ?>
                                    </span>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-light edit-cliente" 
                                            data-id="<?php echo $cliente['id']; ?>"
                                            data-empresa="<?php echo htmlspecialchars($cliente['cliente_empresa']); ?>"
                                            data-contacto="<?php echo htmlspecialchars($cliente['cliente_contacto']); ?>"
                                            data-telefono="<?php echo htmlspecialchars($cliente['cliente_telefono']); ?>"
                                            data-email="<?php echo htmlspecialchars($cliente['cliente_email']); ?>"
                                            data-bs-toggle="modal" 
                                            data-bs-target="#clienteModal">
                                        <svg fill="none" height="24" viewBox="0 0 24 24" width="24" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M14 6L16.2929 3.70711C16.6834 3.31658 17.3166 3.31658 17.7071 3.70711L20.2929 6.29289C20.6834 6.68342 20.6834 7.31658 20.2929 7.70711L18 10M14 6L4.29289 15.7071C4.10536 15.8946 4 16.149 4 16.4142V19C4 19.5523 4.44772 20 5 20H7.58579C7.851 20 8.10536 19.8946 8.29289 19.7071L18 10M14 6L18 10" 
                                                  stroke="black" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/>
                                        </svg>
                                    </button>
                                    <button class="btn btn-light toggle-status"
                                            data-id="<?php echo $cliente['id']; ?>"
                                            data-estado="<?php echo $cliente['estado'] === 'Activo' ? 'Inactivo' : 'Activo'; ?>"
                                            data-bs-toggle="modal" 
                                            data-bs-target="#confirmStatusModal">
                                        <svg fill="none" height="24" viewBox="0 0 24 24" width="24" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M12 4V20M4 12H20" stroke="black" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/>
                                        </svg>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Modal de Cliente -->
        <div class="modal fade" id="clienteModal" tabindex="-1" aria-labelledby="clienteModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="clienteModalLabel">Nuevo Cliente</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="clienteForm">
                            <input type="hidden" id="clienteId" name="id">
                            <input type="hidden" id="formAction" name="action" value="create">

                            <div class="mb-3">
                                <label for="empresa" class="form-label">Empresa*</label>
                                <input type="text" class="form-control" id="empresa" name="empresa" required>
                            </div>

                            <div class="mb-3">
                                <label for="contacto" class="form-label">Contacto*</label>
                                <input type="text" class="form-control" id="contacto" name="contacto" required>
                            </div>

                            <div class="mb-3">
                                <label for="telefono" class="form-label">Teléfono*</label>
                                <input type="tel" class="form-control" id="telefono" name="telefono" required>
                            </div>

                            <div class="mb-3">
                                <label for="email" class="form-label">Email*</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" form="clienteForm" class="btn btn-primary">Guardar</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal de Confirmación de Estado -->
        <div class="modal fade" id="confirmStatusModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Confirmar Cambio de Estado</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p>¿Está seguro que desea cambiar el estado de este cliente?</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="button" class="btn btn-warning" id="confirmToggleStatus">Confirmar</button>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Bootstrap 5 Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JS -->
    <script src="../js/menu_lateral.js"></script>
    <script src="../js/main.js"></script>
</body>
</html>
