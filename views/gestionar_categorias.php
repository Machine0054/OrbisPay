<?php 
    session_start();
    if (!isset($_SESSION['usuario'])) {
    echo "No existe usuario logueado";
    header("Location: index.php");
    exit;
}

    $pagina_actual = 'gestionar_categorias'; 
    require_once '../models/templates/header.php'; 
    require_once '../models/templates/sidebar.php';
?>
<!-- ========= INICIO: PANEL DESPLEGABLE DE NOTIFICACIONES ========= -->
<div id="notification-panel"
    class="fixed top-16 right-5 z-50 my-4 w-full max-w-sm text-base list-none bg-white rounded-xl divide-y divide-gray-100 shadow-lg hidden"
    aria-labelledby="notification-bell-btn">

    <!-- Cabecera del Panel -->
    <div class="block px-4 py-2 text-base font-medium text-center text-gray-700 bg-gray-50 rounded-t-xl">
        Notificaciones
    </div>

    <!-- Contenedor de Notificaciones (aquí se insertará el contenido con JS) -->
    <div id="notification-list-container" class="divide-y divide-gray-100  max-h-96 overflow-y-auto">
    </div>

    <!-- Pie del Panel -->
    <a href="#" id="mark-all-as-read-btn"
        class="block py-2 text-sm font-medium text-center text-gray-900 rounded-b-xl bg-gray-50">
        Marcar todas como leídas
    </a>
</div>
<!-- ========= FIN: PANEL DESPLEGABLE DE NOTIFICACIONES ========= -->


<div class="p-4 sm:ml-64">
    <div class="p-4 mt-14">

        <!-- Encabezado de la sección -->
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-gray-800">Mis Categorías de Ingresos</h2>
            <!-- Podríamos añadir un botón "Crear Categoría" aquí, pero ya lo tenemos en el formulario de ingresos, así que es opcional -->
        </div>

        <!-- Contenedor donde se cargará la lista de categorías -->
        <div id="category-list-container"
            class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-xl p-6 md:p-8 border border-white/20">

            <!-- Estado de carga inicial -->
            <div id="loading-state" class="text-center py-10">
                <svg class="animate-spin h-8 w-8 text-emerald-500 mx-auto" xmlns="http://www.w3.org/2000/svg"
                    fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor"
                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                    </path>
                </svg>
                <p class="mt-4 text-gray-600">Cargando tus categorías...</p>
            </div>

            <!-- La lista se insertará aquí -->
            <div id="category-list" class="space-y-4"></div>

        </div>
    </div>
</div>

<div id="delete-category-modal"
    class="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-2xl p-8 max-w-lg w-full mx-4 shadow-2xl">
        <h3 class="text-xl font-bold text-gray-800">Eliminar Categoría "<span id="deleting-category-name"
                class="text-red-600"></span>"</h3>

        <p class="text-gray-600 mt-4">
            Hemos detectado que <strong id="income-count" class="text-gray-800">0 ingresos</strong> están usando esta
            categoría.
            Por favor, elige una nueva categoría para reasignar estos ingresos antes de eliminarla.
        </p>
        <div class="mt-6">
            <label for="reassign-category-select" class="block text-sm font-medium text-gray-700 mb-2">Reasignar
                ingresos a:</label>
            <select id="reassign-category-select"
                class="form-select w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500">
                <!-- Opciones de reasignación se cargarán aquí -->
            </select>
        </div>
        <div class="flex justify-end space-x-4 mt-8">
            <button type="button" id="btn-cancel-delete"
                class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium py-2 px-6 rounded-lg">
                Cancelar
            </button>
            <button type="button" id="btn-confirm-delete"
                class="bg-red-600 hover:bg-red-700 text-white font-medium py-2 px-6 rounded-lg">
                Reasignar y Eliminar
            </button>
        </div>
    </div>
</div>

<div id="edit-category-modal"
    class="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-2xl p-8 max-w-md w-full mx-4 shadow-2xl">
        <h3 class="text-xl font-bold text-gray-800 mb-6">Editar Categoría</h3>
        <div class="space-y-4">
            <div>
                <label for="edit-category-name" class="block text-sm font-medium text-gray-700 mb-2">Nuevo
                    Nombre</label>
                <input type="text" id="edit-category-name"
                    class="form-input w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500">
                <input type="hidden" id="edit-category-id">
            </div>
            <div class="flex justify-end space-x-4 pt-4">
                <button type="button" id="btn-cancel-edit"
                    class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium py-2 px-6 rounded-lg">
                    Cancelar
                </button>
                <button type="button" id="btn-save-edit"
                    class="bg-emerald-500 hover:bg-emerald-600 text-white font-medium py-2 px-6 rounded-lg">
                    Guardar Cambios
                </button>
            </div>
        </div>
    </div>
</div>

<div id="notification-modal"
    class="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-2xl p-8 max-w-md w-full mx-4 shadow-2xl transform scale-95 opacity-0 transition-all duration-300"
        id="notification-modal-box">
        <div class="text-center">
            <div id="notification-icon-container"
                class="w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
            </div>
            <h3 id="notification-title" class="text-xl font-bold text-gray-800 mb-2">
            </h3>
            <p id="notification-message" class="text-gray-600 mb-6">
            </p>
            <button id="notification-close-btn"
                class="text-white font-medium py-2 px-6 rounded-lg transition-all duration-300">
                Continuar
            </button>
        </div>
    </div>
</div>

<!-- MODAL PARA CONFIRMACION, REUTILIZABLE -->
<div id="confirmationModal" class="fixed inset-0 z-50 hidden opacity-0 transition-opacity duration-300" role="dialog"
    aria-modal="true" aria-labelledby="confirmationModalTitle" aria-describedby="confirmationModalMessage">

    <!-- Overlay -->
    <div class="absolute inset-0 bg-black/60 backdrop-blur-sm"></div>

    <!-- Wrapper centrado -->
    <div class="relative flex h-full items-center justify-center p-4">
        <!-- Caja del modal -->
        <div id="confirmationModalBox"
            class="relative w-full max-w-md transform rounded-2xl bg-white p-6 shadow-2xl transition-all duration-300 scale-95 focus:outline-none"
            tabindex="-1">
            <!-- Contenido principal centrado -->
            <div class="text-center">
                <!-- Icono centrado -->
                <div id="confirmationModalIconContainer"
                    class="flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-red-100">
                    <!-- SVG dinámico -->
                </div>

                <!-- Texto centrado -->
                <div class="mb-6">
                    <h3 id="confirmationModalTitle" class="text-lg font-semibold leading-6 text-gray-900 mb-2">
                        <!-- Título -->
                    </h3>
                    <p id="confirmationModalMessage" class="text-sm text-gray-600 leading-relaxed">
                        <!-- Mensaje -->
                    </p>
                </div>


                <!-- Botones centrados -->
                <div class="flex gap-3 justify-center">
                    <button id="confirmationModalConfirmBtn" type="button"
                        class="px-6 py-2.5 text-sm font-medium text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500/30 rounded-lg transition-colors">
                        <!-- Texto dinámico -->
                    </button>
                    <button id="confirmationModalCancelBtn" type="button"
                        class="px-6 py-2.5 text-sm font-medium text-gray-300 bg-gray-600 hover:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-gray-400/30 rounded-lg transition-colors">
                        <!-- Texto dinámico -->
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// 3. Incluir el Footer
require_once '../models/templates/footer.php';

?>
<script src="../controllers/gestionar_categorias.js"></script>