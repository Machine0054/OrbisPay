<?php 
    session_start();

    if (!isset($_SESSION['usuario'])) {
    echo "No existe usuario logueado";
    header("Location: index.php");
    exit;
}

    $pagina_actual = 'Historial'; 
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
        <main class="flex-1 p-4 md:p-6 space-y-6">

            <!-- Panel de Filtros -->
            <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-xl p-4 md:p-6 border border-white/20">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <!-- Filtro: Rango de Fechas -->
                    <div>
                        <label for="filterDateRange" class="block text-sm font-medium text-gray-700 mb-1">Fecha</label>
                        <input type="text" id="filterDateRange"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500"
                            placeholder="Selecciona un rango">
                    </div>
                    <!-- Filtro: Tipo de Transacción -->
                    <div>
                        <label for="filterType" class="block text-sm font-medium text-gray-700 mb-1">Tipo</label>
                        <select id="filterType"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="todos">Todos</option>
                            <option value="ingreso">Ingresos</option>
                            <option value="gasto">Gastos</option>
                        </select>
                    </div>
                    <!-- Filtro: Categoría -->
                    <div>
                        <label for="filterCategory"
                            class="block text-sm font-medium text-gray-700 mb-1">Categoría</label>
                        <select id="filterCategory"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="todas">Todas</option>
                            <!-- Opciones de categoría se cargarán con JS -->
                        </select>
                    </div>
                    <!-- Filtro: Búsqueda por Descripción -->
                    <div>
                        <label for="filterSearch" class="block text-sm font-medium text-gray-700 mb-1">Buscar</label>
                        <input type="text" id="filterSearch"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500"
                            placeholder="Ej: Salario, supermercado...">
                    </div>
                </div>
            </div>

            <!-- Panel de Resultados (Tabla) -->
            <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-xl border border-white/20 overflow-hidden">
                <div id="totalsBar"
                    class="p-4 border-b border-gray-200 grid grid-cols-1 md:grid-cols-3 gap-4 text-center">
                    <!-- Contenido se generará con JS -->
                </div>
                <!-- Resumen de Resultados -->
                <div id="summaryBar" class="p-4 border-b border-gray-200 bg-gray-50/50 text-sm text-gray-600">
                    Cargando resultados...
                </div>

                <!-- Tabla de Transacciones -->
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50/50">
                            <tr>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Fecha</th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Descripción</th>
                                <th scope="col"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Categoría</th>
                                <th scope="col"
                                    class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Monto</th>
                                <th scope="col" class="relative px-6 py-3">
                                    <span class="sr-only">Acciones</span>
                                </th>
                            </tr>
                        </thead>
                        <tbody id="transactionsTableBody" class="bg-white divide-y divide-gray-200">
                            <!-- Filas de la tabla se generarán con JS -->
                            <!-- Ejemplo de una fila de carga -->
                            <tr>
                                <td colspan="5" class="px-6 py-4 text-center text-gray-500">
                                    Aplicando filtros...
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Paginación -->
                <nav id="paginationContainer"
                    class="bg-white/50 px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6">
                    <div class="flex-1 flex justify-between sm:hidden">
                        <a href="#"
                            class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                            Anterior </a>
                        <a href="#"
                            class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                            Siguiente </a>
                    </div>
                    <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                        <div>
                            <p class="text-sm text-gray-700">
                                Mostrando <span id="paginationFrom" class="font-medium">1</span> a <span
                                    id="paginationTo" class="font-medium">10</span> de <span id="paginationTotal"
                                    class="font-medium">97</span> resultados
                            </p>
                        </div>
                        <div>
                            <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px"
                                aria-label="Pagination">
                                <!-- Los botones de paginación se generarán con JS -->
                            </nav>
                        </div>
                    </div>
                </nav>
            </div>
        </main>
    </div>
</div>
<!-- =================================================================
MODAL PARA EDITAR TRANSACCIÓN
================================================================== -->

<div id="editTransactionModal" class="fixed inset-0 z-50 hidden transition-opacity duration-300 opacity-0">

    <!-- 1. El Overlay (Fondo oscuro y desenfocado) -->
    <div class="absolute inset-0 bg-black bg-opacity-60 backdrop-blur-sm" aria-hidden="true"></div>

    <!-- 2. Contenedor para centrar el modal -->
    <div class="relative w-full max-w-md p-4 h-full md:h-auto flex items-center justify-center mx-auto">

        <!-- 3. La Caja/Contenido del Modal -->
        <div id="editModalBox"
            class="bg-white rounded-2xl shadow-2xl p-6 md:p-8 w-full transform scale-95 transition-all duration-300">

            <!-- Encabezado del Modal -->
            <div class="flex justify-between items-center mb-6 pb-4 border-b">
                <h3 class="text-xl font-semibold text-gray-800">Editar Transacción</h3>
                <button id="closeEditModalBtn" type="button"
                    class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 ml-auto inline-flex items-center"
                    aria-label="Cerrar modal">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                            clip-rule="evenodd"></path>
                    </svg>
                </button>
            </div>

            <!-- Formulario de Edición -->
            <form id="editTransactionForm" class="space-y-4">
                <!-- Campos ocultos para el ID y tipo de la transacción -->
                <input type="hidden" id="editTransactionId" name="id">
                <input type="hidden" id="editTransactionType" name="type">

                <!-- Campo: Descripción -->
                <div>
                    <label for="editDescription"
                        class="block text-sm font-medium text-gray-700 mb-1">Descripción</label>
                    <input type="text" id="editDescription" name="description" required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                </div>

                <!-- Campo: Monto -->
                <div>
                    <label for="editAmount" class="block text-sm font-medium text-gray-700 mb-1">Monto</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-500">$</span>
                        <input type="number" id="editAmount" name="amount" required step="0.01"
                            class="w-full pl-7 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500"
                            placeholder="0.00">
                    </div>
                </div>

                <!-- Campo: Fecha -->
                <div>
                    <label for="editDate" class="block text-sm font-medium text-gray-700 mb-1">Fecha</label>
                    <input type="date" id="editDate" name="date" required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                </div>

                <!-- Campo: Categoría -->
                <div>
                    <label for="editCategory" class="block text-sm font-medium text-gray-700 mb-1">Categoría</label>
                    <select id="editCategory" name="category" required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                        <!-- Las opciones se cargarán aquí con JavaScript -->
                        <option value="">Cargando...</option>
                    </select>
                </div>

                <!-- Botones de Acción -->
                <div class="mt-8 flex justify-end space-x-4 pt-4 border-t">
                    <button id="cancelEditBtn" type="button"
                        class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 font-medium hover:bg-gray-50 transition-colors">
                        Cancelar
                    </button>
                    <button type="submit"
                        class="px-6 py-2 bg-indigo-600 text-white rounded-lg font-medium hover:bg-indigo-700 transition-colors">
                        Guardar Cambios
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

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
        require_once '../models/templates/footer.php';
    ?>
<script src="../controllers/Historial.js"></script>