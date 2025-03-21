/**
 * Procesos de Producción - JavaScript
 * 
 * Este archivo contiene todas las funciones y eventos necesarios para la gestión de procesos de producción.
 */

// Variables globales
let procesos = [];
let paginaActual = 1;
const registrosPorPagina = 10;
let ordenColumna = 'pp_nombre';
let ordenDireccion = 'ASC';

// Inicialización
$(document).ready(function() {
    // Cargar procesos iniciales
    cargarProcesos();
    
    // Configurar eventos
    configurarEventos();
});

// Función para cargar procesos desde el servidor
function cargarProcesos(filtros = {}) {
    mostrarLoader();
    
    // Preparar parámetros
    let params = {
        action: 'search',
        nombre: $('#filtro-nombre').val(),
        costo_min: $('#filtro-costo-min').val(),
        costo_max: $('#filtro-costo-max').val(),
        order_column: ordenColumna,
        order_dir: ordenDireccion
    };
    
    // Añadir filtros adicionales
    $.extend(params, filtros);
    
    // Realizar petición AJAX
    $.ajax({
        url: '../controllers/ProcesoController.php',
        method: 'GET',
        data: params,
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                procesos = response.procesos;
                actualizarTabla();
            } else {
                mostrarAlerta('error', response.message);
            }
        },
        error: function(xhr, status, error) {
            console.error("Error en la petición:", error);
            mostrarAlerta('error', 'Error al cargar los procesos');
        },
        complete: function() {
            ocultarLoader();
        }
    });
}

// Función para actualizar la tabla con los datos actuales
function actualizarTabla() {
    const $tbody = $('#procesos-body');
    const $sinResultados = $('#sin-resultados');
    $tbody.empty();
    
    if (procesos.length === 0) {
        $sinResultados.show();
        $('#registros-mostrados, #registros-totales').text(0);
        $('#paginacion').empty();
        return;
    }
    
    $sinResultados.hide();
    
    // Calcular índices para paginación
    const inicio = (paginaActual - 1) * registrosPorPagina;
    const fin = Math.min(inicio + registrosPorPagina, procesos.length);
    const procesosPaginados = procesos.slice(inicio, fin);
    
    // Actualizar información de paginación
    $('#registros-mostrados').text(procesosPaginados.length);
    $('#registros-totales').text(procesos.length);
    
    // Generar filas de la tabla
    procesosPaginados.forEach(function(proceso) {
        const row = `
            <tr data-id="${proceso.id}">
                <td>${proceso.id}</td>
                <td>${proceso.pp_nombre}</td>
                <td>${proceso.pp_descripcion || '-'}</td>
                <td>$${parseFloat(proceso.pp_costo).toFixed(2)}</td>
                <td>
                    <button class="btn btn-sm btn-primary editar-proceso" title="Editar">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn btn-sm btn-danger eliminar-proceso" title="Eliminar">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            </tr>
        `;
        $tbody.append(row);
    });
    
    // Actualizar paginación
    actualizarPaginacion();
}

// Función para actualizar los controles de paginación
function actualizarPaginacion() {
    const $paginacion = $('#paginacion');
    $paginacion.empty();
    
    if (procesos.length === 0) return;
    
    const totalPaginas = Math.ceil(procesos.length / registrosPorPagina);
    
    // Botón Anterior
    const $anterior = $(`
        <li class="page-item ${paginaActual === 1 ? 'disabled' : ''}">
            <a class="page-link" href="#" data-pagina="${paginaActual - 1}">Anterior</a>
        </li>
    `);
    $paginacion.append($anterior);
    
    // Páginas
    const inicio = Math.max(1, paginaActual - 2);
    const fin = Math.min(totalPaginas, paginaActual + 2);
    
    for (let i = inicio; i <= fin; i++) {
        const $pagina = $(`
            <li class="page-item ${paginaActual === i ? 'active' : ''}">
                <a class="page-link" href="#" data-pagina="${i}">${i}</a>
            </li>
        `);
        $paginacion.append($pagina);
    }
    
    // Botón Siguiente
    const $siguiente = $(`
        <li class="page-item ${paginaActual === totalPaginas ? 'disabled' : ''}">
            <a class="page-link" href="#" data-pagina="${paginaActual + 1}">Siguiente</a>
        </li>
    `);
    $paginacion.append($siguiente);
    
    // Configurar evento para cambiar de página
    $('.page-link').click(function(e) {
        e.preventDefault();
        const nuevaPagina = parseInt($(this).data('pagina'));
        if (nuevaPagina >= 1 && nuevaPagina <= totalPaginas) {
            paginaActual = nuevaPagina;
            actualizarTabla();
        }
    });
}

// Función para configurar todos los eventos
function configurarEventos() {
    // Filtro de búsqueda
    $('#filtro-form').submit(function(e) {
        e.preventDefault();
        paginaActual = 1;
        cargarProcesos();
    });
    
    // Limpiar filtros
    $('#btn-limpiar-filtros').click(function() {
        $('#filtro-form')[0].reset();
        paginaActual = 1;
        cargarProcesos();
    });
    
    // Ordenamiento de columnas
    $('.sortable').click(function() {
        const columna = $(this).data('column');
        
        // Si es la misma columna, cambiar dirección
        if (ordenColumna === columna) {
            ordenDireccion = ordenDireccion === 'ASC' ? 'DESC' : 'ASC';
        } else {
            ordenColumna = columna;
            ordenDireccion = 'ASC';
        }
        
        // Actualizar indicadores visuales
        $('.sortable i').removeClass('fa-sort-up fa-sort-down').addClass('fa-sort');
        const iconClass = ordenDireccion === 'ASC' ? 'fa-sort-up' : 'fa-sort-down';
        $(this).find('i').removeClass('fa-sort').addClass(iconClass);
        
        // Recargar procesos
        paginaActual = 1;
        cargarProcesos();
    });
    
    // Agregar nuevo proceso
    $('#saveProceso').click(function() {
        guardarProceso();
    });
    
    // Editar proceso
    $(document).on('click', '.editar-proceso', function() {
        const procesoId = $(this).closest('tr').data('id');
        cargarDatosProceso(procesoId);
    });
    
    // Eliminar proceso
    $(document).on('click', '.eliminar-proceso', function() {
        const procesoId = $(this).closest('tr').data('id');
        $('#deleteProcesoId').val(procesoId);
        $('#deleteProcesoModal').modal('show');
    });
    
    // Confirmar eliminación
    $('#confirmDeleteProceso').click(function() {
        eliminarProceso();
    });
    
    // Resetear modal al cerrar
    $('#procesoModal').on('hidden.bs.modal', function() {
        resetearFormulario();
    });
}

// Función para cargar datos de un proceso en el formulario
function cargarDatosProceso(id) {
    mostrarLoader();
    
    $.ajax({
        url: '../controllers/ProcesoController.php',
        method: 'GET',
        data: {
            action: 'getProcesoInfo',
            id: id
        },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                const proceso = response.proceso;
                
                // Llenar el formulario
                $('#procesoId').val(proceso.id);
                $('#pp_nombre').val(proceso.pp_nombre);
                $('#pp_descripcion').val(proceso.pp_descripcion);
                $('#pp_costo').val(proceso.pp_costo);
                
                // Cambiar el título del modal
                $('#procesoModalLabel').text('Editar Proceso de Producción');
                
                // Mostrar el modal
                $('#procesoModal').modal('show');
            } else {
                mostrarAlerta('error', response.message);
            }
        },
        error: function(xhr, status, error) {
            console.error("Error al cargar datos del proceso:", error);
            mostrarAlerta('error', 'Error al cargar datos del proceso');
        },
        complete: function() {
            ocultarLoader();
        }
    });
}

// Función para guardar un proceso (crear o actualizar)
function guardarProceso() {
    // Validar formulario
    if (!validarFormulario()) {
        return;
    }
    
    mostrarLoader();
    
    // Preparar datos
    const procesoId = $('#procesoId').val();
    const action = procesoId ? 'update' : 'create';
    
    const formData = new FormData($('#procesoForm')[0]);
    formData.append('action', action);
    
    // Realizar petición AJAX
    $.ajax({
        url: '../controllers/ProcesoController.php',
        method: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                mostrarAlerta('success', response.message);
                $('#procesoModal').modal('hide');
                cargarProcesos();
            } else {
                mostrarAlerta('error', response.message);
            }
        },
        error: function(xhr, status, error) {
            console.error("Error al guardar proceso:", error);
            mostrarAlerta('error', 'Error al guardar el proceso');
        },
        complete: function() {
            ocultarLoader();
        }
    });
}

// Función para eliminar un proceso
function eliminarProceso() {
    if (!$('#deletePassword').val()) {
        mostrarAlerta('error', 'Debe ingresar su contraseña para confirmar');
        return;
    }
    
    mostrarLoader();
    
    const formData = new FormData();
    formData.append('action', 'delete');
    formData.append('id', $('#deleteProcesoId').val());
    formData.append('password', $('#deletePassword').val());
    
    $.ajax({
        url: '../controllers/ProcesoController.php',
        method: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                mostrarAlerta('success', response.message);
                $('#deleteProcesoModal').modal('hide');
                cargarProcesos();
            } else {
                mostrarAlerta('error', response.message);
            }
        },
        error: function(xhr, status, error) {
            console.error("Error al eliminar proceso:", error);
            mostrarAlerta('error', 'Error al eliminar el proceso');
        },
        complete: function() {
            ocultarLoader();
            $('#deletePassword').val('');
        }
    });
}

// Función para validar el formulario de proceso
function validarFormulario() {
    const $form = $('#procesoForm');
    
    // Validar campos requeridos
    let valido = true;
    $form.find('[required]').each(function() {
        if (!$(this).val()) {
            $(this).addClass('is-invalid');
            valido = false;
        } else {
            $(this).removeClass('is-invalid');
        }
    });
    
    if (!valido) {
        mostrarAlerta('error', 'Complete todos los campos requeridos');
        return false;
    }
    
    return true;
}

// Función para resetear el formulario de proceso
function resetearFormulario() {
    $('#procesoForm')[0].reset();
    $('#procesoId').val('');
    $('#procesoModalLabel').text('Nuevo Proceso de Producción');
    $('#procesoForm .is-invalid').removeClass('is-invalid');
}

// Función para mostrar alerta
function mostrarAlerta(tipo, mensaje) {
    let icon = 'info';
    if (tipo === 'error') icon = 'error';
    if (tipo === 'success') icon = 'success';
    
    Swal.fire({
        icon: icon,
        title: mensaje,
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true
    });
}

// Funciones para mostrar/ocultar el loader
function mostrarLoader() {
    $('#loader').show();
}

function ocultarLoader() {
    $('#loader').hide();
}
