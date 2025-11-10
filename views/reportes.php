<?php 
    session_start();

    if (!isset($_SESSION['usuario'])) {
    echo "No existe usuario logueado";
    header("Location: index.php");
    exit;
}

    $pagina_actual = 'reportes';

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
            <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-xl p-4 md:p-6 border border-white/20">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between space-y-4 md:space-y-0">

                </div>
            </div>

            <!-- Summary Cards -->
            <div class="stats-grid grid grid-cols-1 md:grid-cols-3 gap-4 md:gap-6">
                <div
                    class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-xl p-4 md:p-6 report-card border border-white/20">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-xs md:text-sm font-medium text-gray-600 uppercase tracking-wide">
                                Ingresos
                                totales</h2>
                            <p id="ingresosMes" class="text-2xl md:text-3xl font-bold text-emerald-600 mt-2">$0
                            </p>
                            <p id="ingresosMesAnterior" class="text-xs md:text-sm text-emerald-500 mt-1">--% vs período
                                anterior</p>
                        </div>
                        <div class="bg-emerald-100 p-2 md:p-3 rounded-full">
                            <svg class="w-6 h-6 md:w-8 md:h-8 text-emerald-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-8.293l-3-3a1 1 0 00-1.414 0l-3 3a1 1 0 001.414 1.414L9 9.414V13a1 1 0 102 0V9.414l1.293 1.293a1 1 0 001.414-1.414z"
                                    clip-rule="evenodd" />
                            </svg>
                        </div>
                    </div>
                </div>
                <div
                    class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-xl p-4 md:p-6 report-card border border-white/20">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-xs md:text-sm font-medium text-gray-600 uppercase tracking-wide">Gastos
                                totales</h2>
                            <p id="gastosMes" class="text-2xl md:text-3xl font-bold text-red-600 mt-2">$0</p>
                            <p id="gastosMesAnterior" class="text-xs md:text-sm text-red-500 mt-1">--% vs período
                                anterior</p>
                        </div>
                        <div class="bg-red-100 p-2 md:p-3 rounded-full">
                            <svg class="w-6 h-6 md:w-8 md:h-8 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v3.586L7.707 9.293a1 1 0 00-1.414 1.414l3 3a1 1 0 001.414 0l3-3a1 1 0 00-1.414-1.414L11 10.586V7z"
                                    clip-rule="evenodd" />
                            </svg>
                        </div>
                    </div>
                </div>
                <div
                    class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-xl p-4 md:p-6 report-card border border-white/20">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-xs md:text-sm font-medium text-gray-600 uppercase tracking-wide">Balance
                                neto</h2>
                            <p id="balanceMes" class="text-2xl md:text-3xl font-bold text-indigo-600 mt-2">$0</p>
                            <p id="porcentajeAhorro" class="text-xs md:text-sm text-indigo-500 mt-1">--% de ahorro
                            </p>
                        </div>
                        <div class="bg-indigo-100 p-2 md:p-3 rounded-full">
                            <svg class="w-6 h-6 md:w-8 md:h-8 text-indigo-600" fill="currentColor" viewBox="0 0 20 20">
                                <path
                                    d="M8.433 7.418c.155-.103.346-.196.567-.267v1.698a2.305 2.305 0 01-.567-.267C8.07 8.34 8 8.114 8 8c0-.114.07-.34.433-.582zM11 12.849v-1.698c.22.071.412.164.567.267.364.243.433.468.433.582 0 .114-.07-.34-.433-.582a2.305 2.305 0 01-.567.267z" />
                                <path fill-rule="evenodd"
                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-13a1 1 0 10-2 0v.092a4.535 4.535 0 00-1.676.662C6.602 6.234 6 7.009 6 8c0 .99.602 1.765 1.324 2.246.48.32 1.054.545 1.676.662v1.941c-.391-.127-.68-.317-.843-.504a1 1 0 10-1.51 1.31c.562.649 1.413 1.076 2.353 1.253V15a1 1 0 102 0v-.092a4.535 4.535 0 001.676-.662C13.398 13.766 14 12.991 14 12c0-.99-.602-1.765-1.324-2.246A4.535 4.535 0 0011 9.092V7.151c.391.127.68.317.843.504a1 1 0 101.511-1.31c-.563-.649-1.413-1.076-2.354-1.253V5z"
                                    clip-rule="evenodd" />
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts Section -->
            <div class="charts-grid grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Expense by Category Chart -->
                <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-xl p-4 md:p-6 border border-white/20">
                    <div
                        class="flex flex-col sm:flex-row sm:justify-between sm:items-center mb-4 space-y-2 sm:space-y-0">
                        <h3 class="text-lg font-semibold text-gray-800">Gastos por categoría</h3>
                        <div class="relative">
                            <select id="categoryChartType"
                                class="appearance-none bg-white border border-gray-300 rounded-lg pl-3 pr-8 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 w-full sm:w-auto">
                                <option value="pie">Gráfico de torta</option>
                                <option value="bar">Gráfico de barras</option>
                                <option value="doughnut">Gráfico de dona</option>
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                        clip-rule="evenodd" />
                                </svg>
                            </div>
                        </div>
                    </div>
                    <canvas id="categoryChart" height="250"></canvas>
                </div>

                <!-- Monthly Trend Chart -->
                <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-xl p-4 md:p-6 border border-white/20">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Tendencia mensual</h3>
                    <canvas id="monthlyTrendChart" height="250"></canvas>
                </div>
            </div>


            <div class="grid grid-cols-1 gap-6">
                <!-- ========= WIDGET DE GASTOS RECURRENTES (SUSCRIPCIONES) ========= -->
                <div class="bg-white  rounded-2xl shadow-lg p-5 border border-gray-200">

                    <!-- 1. Cabecera y Resumen Principal -->
                    <div class="flex justify-between items-center">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-800">Suscripciones y Pagos
                                Recurrentes</h3>
                            <p class="text-sm text-gray-500">Gastos automáticos detectados en los
                                últimos meses.</p>
                        </div>
                        <!-- Loader que se muestra mientras se calculan los datos -->
                        <div id="recurrent-loader" class="text-sm text-gray-400">
                            Analizando...
                        </div>
                    </div>

                    <!-- 2. Resumen de Totales (se muestra después de cargar) -->
                    <div id="recurrent-summary"
                        class="hidden mt-4 p-4 bg-indigo-50 rounded-xl flex items-center justify-around text-center">
                        <div>
                            <p class="text-2xl font-bold text-indigo-600" id="recurrent-count">
                                0
                            </p>
                            <p class="text-sm font-medium text-gray-600">Pagos Detectados</p>
                        </div>
                        <div>
                            <p class="text-2xl font-bold text-indigo-60000" id="recurrent-total">
                                --
                            </p>
                            <p class="text-sm font-medium text-gray-600">Total Mensual Estimado
                            </p>
                        </div>
                    </div>

                    <!-- 3. Contenedor para la Lista Detallada de Gastos -->
                    <div id="recurrent-list-container" class="mt-4 space-y-3">
                        <!-- La lista de gastos recurrentes se cargará aquí con JS -->
                    </div>

                </div>
                <!-- ========= FIN DEL WIDGET ========= -->
                <!-- Recent Transactions -->
                <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-xl p-4 md:p-6 border border-white/20">
                    <div
                        class="flex flex-col sm:flex-row sm:justify-between sm:items-center mb-4 space-y-2 sm:space-y-0">
                        <h3 class="text-lg font-semibold text-gray-800">Transacciones recientes</h3>
                        <button onclick="exportToExcel()"
                            class="flex items-center justify-center space-x-1 bg-indigo-500 hover:bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z"
                                    clip-rule="evenodd" />
                            </svg>
                            <span>Exportar</span>
                        </button>
                    </div>
                    <div class="table-container overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col"
                                        class="px-4 md:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Fecha
                                    </th>
                                    <th scope="col"
                                        class="px-4 md:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Descripción
                                    </th>
                                    <th scope="col"
                                        class="px-4 md:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Categoría
                                    </th>
                                    <th scope="col"
                                        class="px-4 md:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Tipo
                                    </th>
                                    <th scope="col"
                                        class="px-4 md:px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Monto
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                            </tbody>
                        </table>
                    </div>
                    <nav id="paginationContainer"
                        class="bg-white/50 px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6">
                        <!-- Los botones de paginación se generarán con JS -->
                        <div class="text-sm text-gray-700">Cargando paginación...</div>
                    </nav>

                </div>
            </div>
        </main>
    </div>
</div>



<!-- Custom Date Modal -->
<div id="customDateModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
    <div class="modal-content bg-white rounded-2xl shadow-2xl p-6 w-full max-w-md">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-xl font-semibold text-gray-800">Seleccionar período personalizado</h3>
            <button onclick="hideCustomDateModal()" class="text-gray-500 hover:text-gray-700">
                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd"
                        d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                        clip-rule="evenodd" />
                </svg>
            </button>
        </div>
        <div class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Fecha de inicio</label>
                <input type="date" id="startDate"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Fecha de fin</label>
                <input type="date" id="endDate"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
            </div>
        </div>
        <div class="mt-6 flex flex-col sm:flex-row justify-end space-y-2 sm:space-y-0 sm:space-x-3">
            <button onclick="hideCustomDateModal()"
                class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 font-medium">Cancelar</button>
            <button onclick="applyCustomDate()"
                class="px-4 py-2 bg-indigo-600 text-white rounded-lg font-medium hover:bg-indigo-700">Aplicar</button>
        </div>
    </div>
</div>
<?php 
    
        require_once '../models/templates/footer.php';
    ?>
<script src="../controllers/reportes.js"></script>