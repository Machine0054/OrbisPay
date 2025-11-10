<?php 
    session_start();

    if (!isset($_SESSION['usuario'])) {
    echo "No existe usuario logueado";
    header("Location: index.php");
    exit;
}

    $pagina_actual = 'presupuesto';

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
            <!-- Budget Overview -->
            <div class="budget-overview-grid grid grid-cols-1 md:grid-cols-3 gap-4 md:gap-6">
                <!-- Total Budget -->
                <div
                    class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-xl p-4 md:p-6 budget-card border border-white/20">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-xs md:text-sm font-medium text-gray-600 uppercase tracking-wide">
                                Presupuesto
                                total</h2>
                            <p class="text-2xl md:text-3xl font-bold text-indigo-600 mt-2" id="totalBudgetAmount">$0
                            </p>
                            <p class="text-xs md:text-sm text-gray-500 mt-1">Presupuesto asignado</p>
                        </div>
                        <div class="bg-indigo-100 p-2 md:p-3 rounded-full">
                            <svg class="w-6 h-6 md:w-8 md:h-8 text-indigo-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M4 4a2 2 0 00-2 2v4a2 2 0 002 2V6h10a2 2 0 00-2-2H4zm2 6a2 2 0 012-2h8a2 2 0 012 2v4a2 2 0 01-2 2H8a2 2 0 01-2-2v-4zm6 4a2 2 0 100-4 2 2 0 000 4z"
                                    clip-rule="evenodd" />
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Budget Used -->
                <div
                    class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-xl p-4 md:p-6 budget-card border border-white/20">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-xs md:text-sm font-medium text-gray-600 uppercase tracking-wide">
                                Presupuesto
                                usado</h2>
                            <p class="text-2xl md:text-3xl font-bold text-red-600 mt-2" id="totalUsedAmount">$0</p>
                            <p class="text-xs md:text-sm text-gray-500 mt-1">Gastos realizados</p>
                        </div>
                        <div class="bg-red-100 p-2 md:p-3 rounded-full">
                            <svg class="w-6 h-6 md:w-8 md:h-8 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zM7 9a1 1 0 000 2h6a1 1 0 100-2H7z"
                                    clip-rule="evenodd" />
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Budget Remaining -->
                <div
                    class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-xl p-4 md:p-6 budget-card border border-white/20">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-xs md:text-sm font-medium text-gray-600 uppercase tracking-wide">
                                Presupuesto
                                restante</h2>
                            <p class="text-2xl md:text-3xl font-bold text-emerald-600 mt-2" id="totalRemainingAmount">$0
                            </p>
                            <p class="text-xs md:text-sm text-gray-500 mt-1">Disponible para gastar</p>
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
            </div>

            <!-- Budget by Category -->
            <div class="budget-content-grid grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Budget Allocation -->
                <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-xl p-4 md:p-6 border border-white/20">
                    <div
                        class="flex flex-col sm:flex-row sm:justify-between sm:items-center mb-4 space-y-2 sm:space-y-0">
                        <h3 class="text-lg font-semibold text-gray-800">Asignación por categoría</h3>
                        <div
                            class="budget-category-actions flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-2">
                            <button id="showAddBudgetModalBtn"
                                class="flex items-center justify-center space-x-1 bg-indigo-500 hover:bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z"
                                        clip-rule="evenodd" />
                                </svg>
                                <span>Agregar</span>
                            </button>
                        </div>
                    </div>
                    <div class="space-y-4" id="budgetCategoriesContainer">
                        <!-- Alimentación -->
                        <!-- <div class="budget-category-item p-4 bg-white rounded-lg border border-gray-200"
                            data-category="alimentacion">
                            <div class="budget-category-header flex justify-between items-center mb-1">
                                <span class="font-medium text-gray-800">🍽️ Alimentación</span>
                                <div class="budget-category-stats flex items-center space-x-2">
                                    <span class="text-sm font-semibold text-red-600">$0</span>
                                    <span class="text-xs bg-gray-100 text-gray-800 px-2 py-1 rounded-full">0%</span>
                                </div>
                            </div>
                            <div class="progress-bar">
                                <div class="progress-fill bg-red-500" style="width: 0%"></div>
                            </div>
                            <div class="flex justify-between items-center mt-2">
                                <span class="text-xs text-gray-500">Gastado: $0</span>
                                <span class="text-xs text-gray-500">Presupuesto: $0</span>
                            </div>
                        </div> -->
                        <!-- Transporte -->
                        <!-- <div class="budget-category-item p-4 bg-white rounded-lg border border-gray-200"
                            data-category="transporte">
                            <div class="budget-category-header flex justify-between items-center mb-1">
                                <span class="font-medium text-gray-800">🚗 Transporte</span>
                                <div class="budget-category-stats flex items-center space-x-2">
                                    <span class="text-sm font-semibold text-orange-600">$0</span>
                                    <span class="text-xs bg-gray-100 text-gray-800 px-2 py-1 rounded-full">0%</span>
                                </div>
                            </div>
                            <div class="progress-bar">
                                <div class="progress-fill bg-orange-500" style="width: 0%"></div>
                            </div>
                            <div class="flex justify-between items-center mt-2">
                                <span class="text-xs text-gray-500">Gastado: $0</span>
                                <span class="text-xs text-gray-500">Presupuesto: $0</span>
                            </div>
                        </div> -->
                        <!-- Servicios -->
                        <!-- <div class="budget-category-item p-4 bg-white rounded-lg border border-gray-200"
                            data-category="servicios">
                            <div class="budget-category-header flex justify-between items-center mb-1">
                                <span class="font-medium text-gray-800">💡 Servicios</span>
                                <div class="budget-category-stats flex items-center space-x-2">
                                    <span class="text-sm font-semibold text-yellow-600">$0</span>
                                    <span class="text-xs bg-gray-100 text-gray-800 px-2 py-1 rounded-full">0%</span>
                                </div>
                            </div>
                            <div class="progress-bar">
                                <div class="progress-fill bg-yellow-500" style="width: 0%"></div>
                            </div>
                            <div class="flex justify-between items-center mt-2">
                                <span class="text-xs text-gray-500">Gastado: $0</span>
                                <span class="text-xs text-gray-500">Presupuesto: $0</span>
                            </div>
                        </div> -->
                    </div>
                </div>

                <!-- Budget Chart -->
                <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-xl p-4 md:p-6 border border-white/20">
                    <div
                        class="flex flex-col sm:flex-row sm:justify-between sm:items-center mb-4 space-y-2 sm:space-y-0">
                        <h3 class="text-lg font-semibold text-gray-800">Distribución del presupuesto</h3>
                        <div class="relative">
                            <select id="budgetChartType"
                                class="appearance-none bg-white border border-gray-300 rounded-lg pl-3 pr-8 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 w-full sm:w-auto">
                                <option value="doughnut">Gráfico de dona</option>
                                <option value="pie">Gráfico de torta</option>
                                <option value="bar">Gráfico de barras</option>
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
                    <div class="relative h-64">
                        <canvas id="budgetChart"></canvas>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>
<!-- Add Budget Modal -->
<div id="addBudgetModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
    <div class="modal-content bg-white rounded-2xl shadow-2xl p-6 w-full max-w-md">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-xl font-semibold text-gray-800">Agregar presupuesto</h3>
            <button id="hideAddBudgetModalBtn" class="text-gray-500 hover:text-gray-700">
                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd"
                        d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                        clip-rule="evenodd" />
                </svg>
            </button>
        </div>

        <!-- INICIO DEL FORMULARIO-->
        <form id="addBudgetForm" class="space-y-4">
            <!-- Campo de Categoría-->
            <div>
                <label for="newBudgetCategory" class="block text-sm font-medium text-gray-700 mb-1">Categoría</label>
                <select id="newBudgetCategory" name="category" required
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                    <!-- Las opciones se cargarán aquí con JavaScript -->
                    <option value="">Cargando categorías...</option>
                </select>
            </div>

            <!-- Campo de Monto -->
            <div>
                <label for="newBudgetAmount" class="block text-sm font-medium text-gray-700 mb-1">Monto
                    presupuestado (COP)</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-500">$</span>
                    <input type="text" id="newBudgetAmount" name="amount" required
                        class="w-full pl-7 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500"
                        placeholder="0">
                </div>
            </div>

            <!-- Campo de Período -->
            <div>
                <label for="newBudgetPeriod" class="block text-sm font-medium text-gray-700 mb-1">Período</label>
                <select id="newBudgetPeriod" name="period" required
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">Selecciona un período</option>
                    <option value="diario">Diario</option>
                    <option value="semanal">Semanal</option>
                    <option value="quincenal">Quincenal</option>
                    <option value="mensual">Mensual</option>
                    <option value="anual">Anual</option>
                </select>
            </div>

            <!-- Campos de Fecha -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="newBudgetStartDate" class="block text-sm font-medium text-gray-700 mb-1">Fecha de
                        inicio</label>
                    <input type="date" id="newBudgetStartDate" name="start_date" required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div>
                    <label for="newBudgetEndDate" class="block text-sm font-medium text-gray-700 mb-1">Fecha de
                        fin</label>
                    <input type="date" id="newBudgetEndDate" name="end_date" required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                </div>
            </div>

            <!-- Botones de Acción -->
            <div class="mt-6 flex flex-col sm:flex-row justify-end space-y-2 sm:space-y-0 sm:space-x-3 pt-4 border-t">
                <button type="button" id="cancelAddBudgetBtn"
                    class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 font-medium hover:bg-gray-50 transition-colors">Cancelar</button>
                <button type="submit"
                    class="px-4 py-2 bg-indigo-600 text-white rounded-lg font-medium hover:bg-indigo-700 transition-colors">Agregar</button>
            </div>
        </form>
        <!-- FIN DEL FORMULARIO -->
    </div>
</div>
<?php 
        require_once '../models/templates/footer.php';
    ?>
<script src="../controllers/presupuesto.js"></script>