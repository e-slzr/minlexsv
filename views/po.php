<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MINLEX | Purchase Orders</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/style_main.css">
</head>
<body>
    <?php include '../components/menu_lateral.php'; ?>
    
    <main>
        <div style="width: 100%;" class="border-bottom border-secondary titulo-vista">
            <h1><strong>PO (Purchase Orders)</strong></h1><br>
            <button type="button" class="btn btn-dark">Crear nueva PO</button>
        </div>
        
        <div class="filtrar">
            <input type="text" class="form-control" placeholder="# PO">
            <input type="text" class="form-control" placeholder="Aprobacion">
            <input type="text" class="form-control" placeholder="Fecha creacion">
            <input type="text" class="form-control" placeholder="Fecha completada">
            <input type="text" class="form-control" placeholder="Estado">
            <input type="text" class="form-control" placeholder="Cliente">
            <input type="text" class="form-control" placeholder="Proceso actual">
            <button type="button" class="btn btn-dark">Filtrar</button>
        </div>
        <table class="table table-striped table-hover">
            <tr> 
                <th>ID</th>
                <th>PO</th>
                <th>Aprobacion</th>
                <th>Fecha Creacion</th>
                <th>Fecha completada estimada</th>
                <th>Estado</th>
                <th>Cliente</th>
                <th>Usuario de ingreso</th>
                <th>Proceso actual</th>
                <th>Completado</th>
                <th>Opciones</th>
            </tr>
            <tr>
                <td>1</td>
                <td>PO-001</td>
                <td>Aprobada</td>
                <td>01-01-2025</td>
                <td>31-03-2025</td>
                <td>En proceso</td>
                <td>Cliente A</td>
                <td>Gerente de Produccion</td>
                <td>Costura</td>
                <td>75%</td>
                <td>
                    <button type="button" class="btn btn-light"><svg fill="none" height="24" viewBox="0 0 24 24" width="24" xmlns="http://www.w3.org/2000/svg"><path d="M10 4H6C4.89543 4 4 4.89543 4 6V18C4 19.1046 4.89543 20 6 20H18C19.1046 20 20 19.1046 20 18V14M12 12L20 4M20 4V9M20 4H15" stroke="black" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg></button>
                    <button type="button" class="btn btn-success"><svg fill="none" height="24" viewBox="0 0 24 24" width="24" xmlns="http://www.w3.org/2000/svg"><path d="M14 6L16.2929 3.70711C16.6834 3.31658 17.3166 3.31658 17.7071 3.70711L20.2929 6.29289C20.6834 6.68342 20.6834 7.31658 20.2929 7.70711L18 10M14 6L4.29289 15.7071C4.10536 15.8946 4 16.149 4 16.4142V19C4 19.5523 4.44772 20 5 20H7.58579C7.851 20 8.10536 19.8946 8.29289 19.7071L18 10M14 6L18 10" stroke="white" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg></button>
                    <button type="button" class="btn btn-danger"><svg fill="none" height="24" viewBox="0 0 24 24" width="24" xmlns="http://www.w3.org/2000/svg"><path d="M9 7V7C9 5.34315 10.3431 4 12 4V4C13.6569 4 15 5.34315 15 7V7M9 7H15M9 7H6M15 7H18M20 7H18M4 7H6M6 7V18C6 19.1046 6.89543 20 8 20H16C17.1046 20 18 19.1046 18 18V7" stroke="white" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg></button>
                </td>
            </tr>
            <tr>
            <td>2</td>
            <td>PO-002</td>
            <td>Rechazada</td>
            <td>15-02-2025</td>
            <td>30-04-2025</td>
            <td>En espera</td>
            <td>Cliente B</td>
            <td>Usuario 2</td>
            <td>Corte</td>
            <td>25%</td>
            <td>
                <button type="button" class="btn btn-light"><svg fill="none" height="24" viewBox="0 0 24 24" width="24" xmlns="http://www.w3.org/2000/svg"><path d="M10 4H6C4.89543 4 4 4.89543 4 6V18C4 19.1046 4.89543 20 6 20H18C19.1046 20 20 19.1046 20 18V14M12 12L20 4M20 4V9M20 4H15" stroke="black" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg></button>
                <button type="button" class="btn btn-success"><svg fill="none" height="24" viewBox="0 0 24 24" width="24" xmlns="http://www.w3.org/2000/svg"><path d="M14 6L16.2929 3.70711C16.6834 3.31658 17.3166 3.31658 17.7071 3.70711L20.2929 6.29289C20.6834 6.68342 20.6834 7.31658 20.2929 7.70711L18 10M14 6L4.29289 15.7071C4.10536 15.8946 4 16.149 4 16.4142V19C4 19.5523 4.44772 20 5 20H7.58579C7.851 20 8.10536 19.8946 8.29289 19.7071L18 10M14 6L18 10" stroke="white" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg></button>
                <button type="button" class="btn btn-danger"><svg fill="none" height="24" viewBox="0 0 24 24" width="24" xmlns="http://www.w3.org/2000/svg"><path d="M9 7V7C9 5.34315 10.3431 4 12 4V4C13.6569 4 15 5.34315 15 7V7M9 7H15M9 7H6M15 7H18M20 7H18M4 7H6M6 7V18C6 19.1046 6.89543 20 8 20H16C17.1046 20 18 19.1046 18 18V7" stroke="white" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg></button>
            </td>
        </tr>
        <tr>
            <td>3</td>
            <td>PO-003</td>
            <td>Rechazada</td>
            <td>10-03-2025</td>
            <td>20-05-2025</td>
            <td>Cancelada</td>
            <td>Cliente C</td>
            <td>Usuario 1</td>
            <td>Estampado</td>
            <td>0%</td>
            <td>
                <button type="button" class="btn btn-light"><svg fill="none" height="24" viewBox="0 0 24 24" width="24" xmlns="http://www.w3.org/2000/svg"><path d="M10 4H6C4.89543 4 4 4.89543 4 6V18C4 19.1046 4.89543 20 6 20H18C19.1046 20 20 19.1046 20 18V14M12 12L20 4M20 4V9M20 4H15" stroke="black" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg></button>
                <button type="button" class="btn btn-success"><svg fill="none" height="24" viewBox="0 0 24 24" width="24" xmlns="http://www.w3.org/2000/svg"><path d="M14 6L16.2929 3.70711C16.6834 3.31658 17.3166 3.31658 17.7071 3.70711L20.2929 6.29289C20.6834 6.68342 20.6834 7.31658 20.2929 7.70711L18 10M14 6L4.29289 15.7071C4.10536 15.8946 4 16.149 4 16.4142V19C4 19.5523 4.44772 20 5 20H7.58579C7.851 20 8.10536 19.8946 8.29289 19.7071L18 10M14 6L18 10" stroke="white" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg></button>
                <button type="button" class="btn btn-danger"><svg fill="none" height="24" viewBox="0 0 24 24" width="24" xmlns="http://www.w3.org/2000/svg"><path d="M9 7V7C9 5.34315 10.3431 4 12 4V4C13.6569 4 15 5.34315 15 7V7M9 7H15M9 7H6M15 7H18M20 7H18M4 7H6M6 7V18C6 19.1046 6.89543 20 8 20H16C17.1046 20 18 19.1046 18 18V7" stroke="white" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg></button>
            </td>
        </tr>
        <tr>
            <td>4</td>
            <td>PO-004</td>
            <td>Aprobada</td>
            <td>05-04-2025</td>
            <td>15-06-2025</td>
            <td>Completada</td>
            <td>Cliente D</td>
            <td>Gerente de Produccion</td>
            <td>Teñido</td>
            <td>100%</td>
            <td>
                <button type="button" class="btn btn-light"><svg fill="none" height="24" viewBox="0 0 24 24" width="24" xmlns="http://www.w3.org/2000/svg"><path d="M10 4H6C4.89543 4 4 4.89543 4 6V18C4 19.1046 4.89543 20 6 20H18C19.1046 20 20 19.1046 20 18V14M12 12L20 4M20 4V9M20 4H15" stroke="black" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg></button>
                <button type="button" class="btn btn-success"><svg fill="none" height="24" viewBox="0 0 24 24" width="24" xmlns="http://www.w3.org/2000/svg"><path d="M14 6L16.2929 3.70711C16.6834 3.31658 17.3166 3.31658 17.7071 3.70711L20.2929 6.29289C20.6834 6.68342 20.6834 7.31658 20.2929 7.70711L18 10M14 6L4.29289 15.7071C4.10536 15.8946 4 16.149 4 16.4142V19C4 19.5523 4.44772 20 5 20H7.58579C7.851 20 8.10536 19.8946 8.29289 19.7071L18 10M14 6L18 10" stroke="white" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg></button>
                <button type="button" class="btn btn-danger"><svg fill="none" height="24" viewBox="0 0 24 24" width="24" xmlns="http://www.w3.org/2000/svg"><path d="M9 7V7C9 5.34315 10.3431 4 12 4V4C13.6569 4 15 5.34315 15 7V7M9 7H15M9 7H6M15 7H18M20 7H18M4 7H6M6 7V18C6 19.1046 6.89543 20 8 20H16C17.1046 20 18 19.1046 18 18V7" stroke="white" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg></button>
            </td>
        </tr>
        <tr>
            <td>5</td>
            <td>PO-005</td>
            <td>Aprobada</td>
            <td>20-05-2025</td>
            <td>10-07-2025</td>
            <td>En proceso</td>
            <td>Cliente E</td>
            <td>Gerente General</td>
            <td>Bordado</td>
            <td>50%</td>
            <td>
                <button type="button" class="btn btn-light"><svg fill="none" height="24" viewBox="0 0 24 24" width="24" xmlns="http://www.w3.org/2000/svg"><path d="M10 4H6C4.89543 4 4 4.89543 4 6V18C4 19.1046 4.89543 20 6 20H18C19.1046 20 20 19.1046 20 18V14M12 12L20 4M20 4V9M20 4H15" stroke="black" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg></button>
                <button type="button" class="btn btn-success"><svg fill="none" height="24" viewBox="0 0 24 24" width="24" xmlns="http://www.w3.org/2000/svg"><path d="M14 6L16.2929 3.70711C16.6834 3.31658 17.3166 3.31658 17.7071 3.70711L20.2929 6.29289C20.6834 6.68342 20.6834 7.31658 20.2929 7.70711L18 10M14 6L4.29289 15.7071C4.10536 15.8946 4 16.149 4 16.4142V19C4 19.5523 4.44772 20 5 20H7.58579C7.851 20 8.10536 19.8946 8.29289 19.7071L18 10M14 6L18 10" stroke="white" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg></button>
                <button type="button" class="btn btn-danger"><svg fill="none" height="24" viewBox="0 0 24 24" width="24" xmlns="http://www.w3.org/2000/svg"><path d="M9 7V7C9 5.34315 10.3431 4 12 4V4C13.6569 4 15 5.34315 15 7V7M9 7H15M9 7H6M15 7H18M20 7H18M4 7H6M6 7V18C6 19.1046 6.89543 20 8 20H16C17.1046 20 18 19.1046 18 18V7" stroke="white" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg></button>
            </td>
        </tr>
        <tr>
            <td>6</td>
            <td>PO-006</td>
            <td>Aprobada</td>
            <td>01-06-2025</td>
            <td>30-08-2025</td>
            <td>En espera</td>
            <td>Cliente F</td> 
            <td>Usuario 2</td>
            <td>Planchado</td>
            <td>10%</td>
            <td>
                <button type="button" class="btn btn-light"><svg fill="none" height="24" viewBox="0 0 24 24" width="24" xmlns="http://www.w3.org/2000/svg"><path d="M10 4H6C4.89543 4 4 4.89543 4 6V18C4 19.1046 4.89543 20 6 20H18C19.1046 20 20 19.1046 20 18V14M12 12L20 4M20 4V9M20 4H15" stroke="black" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg></button>
                <button type="button" class="btn btn-success"><svg fill="none" height="24" viewBox="0 0 24 24" width="24" xmlns="http://www.w3.org/2000/svg"><path d="M14 6L16.2929 3.70711C16.6834 3.31658 17.3166 3.31658 17.7071 3.70711L20.2929 6.29289C20.6834 6.68342 20.6834 7.31658 20.2929 7.70711L18 10M14 6L4.29289 15.7071C4.10536 15.8946 4 16.149 4 16.4142V19C4 19.5523 4.44772 20 5 20H7.58579C7.851 20 8.10536 19.8946 8.29289 19.7071L18 10M14 6L18 10" stroke="white" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg></button>
                <button type="button" class="btn btn-danger"><svg fill="none" height="24" viewBox="0 0 24 24" width="24" xmlns="http://www.w3.org/2000/svg"><path d="M9 7V7C9 5.34315 10.3431 4 12 4V4C13.6569 4 15 5.34315 15 7V7M9 7H15M9 7H6M15 7H18M20 7H18M4 7H6M6 7V18C6 19.1046 6.89543 20 8 20H16C17.1046 20 18 19.1046 18 18V7" stroke="white" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg></button>
            </td>
        </tr>
        </table>
    </main>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="../js/main.js"></script>
</body>
<?php include '../components/footer.php'; ?>
</html>