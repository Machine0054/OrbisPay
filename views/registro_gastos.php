<?php 
    session_start();

    if (!isset($_SESSION['usuario'])) {
    echo "No existe usuario logueado";
    header("Location: index.php");
    exit;
}

    $pagina_actual = 'registro_gastos';

    require_once '../models/templates/header.php';
    require_once '../models/templates/sidebar.php';

?>

<!-- ========= INICIO: PANEL DESPLEGABLE DE NOTIFICACIONES ========= -->
<!-- <div id="notification-panel"
    class="fixed top-16 right-5 z-50 my-4 w-full max-w-sm text-base list-none bg-white rounded-xl divide-y divide-gray-100 shadow-lg hidden"
    aria-labelledby="notification-bell-btn">
    <div class="block px-4 py-2 text-base font-medium text-center text-gray-700 bg-gray-50 rounded-t-xl">
        Notificaciones
    </div>
    <div id="notification-list-container" class="divide-y divide-gray-100  max-h-96 overflow-y-auto">
    </div>
    <a href="#" id="mark-all-as-read-btn"
        class="block py-2 text-sm font-medium text-center text-gray-900 rounded-b-xl bg-gray-50">
        Marcar todas como leídas
    </a>
</div> -->
<!-- ========= FIN: PANEL DESPLEGABLE DE NOTIFICACIONES ========= -->

<div class="p-4 sm:ml-64">
    <div class="p-4 mt-14">
        <main class="flex-1 p-4 md:p-6 space-y-6">
            <!-- Quick Stats -->
            <div class="stats-grid grid grid-cols-1 md:grid-cols-3 gap-4 md:gap-6">
                <div
                    class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-xl p-4 md:p-6 card-hover border border-white/20">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-xs md:text-sm font-medium text-gray-600 uppercase tracking-wide">Gastos
                                hoy
                                <p id="statsToday" class="text-xl md:text-2xl font-bold text-red-600 mt-2">$0</p>
                            </h2>
                            <p class="text-xl md:text-2xl font-bold text-red-600 mt-2"></p>
                        </div>
                        <div class="bg-red-100 p-2 md:p-3 rounded-full">
                            <svg class="w-5 h-5 md:w-6 md:h-6 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zM7 9a1 1 0 000 2h6a1 1 0 100-2H7z"
                                    clip-rule="evenodd" />
                            </svg>
                        </div>
                    </div>
                </div>

                <div
                    class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-xl p-4 md:p-6 card-hover border border-white/20">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-xs md:text-sm font-medium text-gray-600 uppercase tracking-wide">Gastos
                                del mes
                                <p id="statsMonth" class="text-xl md:text-2xl font-bold text-red-600 mt-2">$0</p>
                            </h2>
                            <p class="text-xl md:text-2xl font-bold text-red-600 mt-2"></p>
                        </div>
                        <div class="bg-red-100 p-2 md:p-3 rounded-full">
                            <svg class="w-5 h-5 md:w-6 md:h-6 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                                <path
                                    d="M2 11a1 1 0 011-1h2a1 1 0 011 1v5a1 1 0 01-1 1H3a1 1 0 01-1-1v-5zM8 7a1 1 0 011-1h2a1 1 0 011 1v9a1 1 0 01-1 1H9a1 1 0 01-1-1V7zM14 4a1 1 0 011-1h2a1 1 0 011 1v12a1 1 0 01-1 1h-2a1 1 0 01-1-1V4z" />
                            </svg>
                        </div>
                    </div>
                </div>

                <div
                    class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-xl p-4 md:p-6 card-hover border border-white/20">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-xs md:text-sm font-medium text-gray-600 uppercase tracking-wide">
                                Presupuesto restante
                                <p id="statsBudgetRemaining" class="text-xl md:text-2xl font-bold text-orange-600 mt-2">
                                    $0</p>
                            </h2>
                            <p class="text-xl md:text-2xl font-bold text-orange-600 mt-2"></p>

                        </div>
                        <div class="bg-orange-100 p-2 md:p-3 rounded-full">
                            <svg class="w-5 h-5 md:w-6 md:h-6 text-orange-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M1 4a1 1 0 011-1h16a1 1 0 011 1v8a1 1 0 01-1 1H2a1 1 0 01-1-1V4zm12 4a3 3 0 11-6 0 3 3 0 016 0z"
                                    clip-rule="evenodd" />
                                <path d="M4 9a1 1 0 100-2 1 1 0 000 2z" />
                                <path d="M16 9a1 1 0 100-2 1 1 0 000 2z" />
                                <path d="M6 15a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1z" />
                            </svg>
                        </div>
                    </div>
                </div>
            </div>


            <!-- Main Form -->
            <div class="form-grid grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Expense Form -->
                <div
                    class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-xl p-4 md:p-6 border border-white/20 form-animation">
                    <div class="flex items-center mb-6">
                        <div class="bg-red-100 p-2 md:p-3 rounded-full mr-4">
                            <svg class="w-5 h-5 md:w-6 md:h-6 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zM7 9a1 1 0 000 2h6a1 1 0 100-2H7z"
                                    clip-rule="evenodd" />
                            </svg>
                        </div>
                        <h3 class="text-lg md:text-xl font-semibold text-gray-800">Nuevo Gasto</h3>
                    </div>

                    <form class="space-y-6" id="formRegistroGasto" onsubmit="handleSubmit(event)">
                        <!-- Amount Input -->
                        <div>
                            <label for="amount" class="block text-sm font-medium text-gray-700 mb-2">Monto del gasto
                                (COP)</label>
                            <div class="relative flex items-center">
                                <span class="absolute left-3 text-red-500 text-xl font-bold">$</span>
                                <input type="text" id="amount" name="amount"
                                    class="w-full pl-8 pr-4 py-3 border border-gray-300 rounded-xl input-focus amount-input bg-white/50"
                                    placeholder="0" required>
                            </div>
                        </div>
                        <!-- Description -->
                        <div>
                            <label for="description"
                                class="block text-sm font-medium text-gray-700 mb-2">Descripción</label>
                            <input type="text" id="description" name="description"
                                class="w-full px-4 py-3 border border-gray-300 rounded-xl input-focus bg-white/50"
                                placeholder="Descripción del gasto" required>
                        </div>
                        <!-- Date -->
                        <div>
                            <label for="date" class="block text-sm font-medium text-gray-700 mb-2">Fecha</label>
                            <input type="date" id="date" name="date"
                                class="w-full px-4 py-3 border border-gray-300 rounded-xl input-focus bg-white/50"
                                required>
                        </div>
                        <!-- Category Selection -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-4">Categoría</label>

                            <!-- ================================================== -->
                            <!--           ESTE ES EL CONTENEDOR DINÁMICO           -->
                            <!-- ================================================== -->
                            <div class="category-grid grid grid-cols-2 md:grid-cols-3 lg:grid-cols-3 gap-3">

                                <!-- Las categorías se cargarán aquí desde JavaScript -->

                                <!-- BOTÓN "Nueva Categoría" (se queda aquí) -->
                                <button type="button" id="add-new-category-btn"
                                    class="flex flex-col items-center justify-center p-4 border-2 border-dashed border-gray-300 rounded-lg text-gray-500 hover:bg-gray-100 hover:border-indigo-400 hover:text-indigo-600 transition-all duration-200">
                                    <svg class="w-8 h-8 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                            d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                    </svg>
                                    <span class="text-sm font-medium">Nueva Categoría</span>
                                </button>

                            </div>
                            <!-- Este campo oculto guardará el NOMBRE de la categoría -->
                            <input type="hidden" id="category" name="category" required>
                        </div>
                        <!-- Submit Button -->
                        <div class="form-buttons flex flex-col md:flex-row space-y-3 md:space-y-0 md:space-x-4 mt-6">
                            <button type="submit"
                                class="flex-1 bg-red-500 hover:bg-red-600 text-white px-6 py-3 rounded-xl font-medium transition-all duration-300 transform hover:scale-105 shadow-lg flex items-center justify-center">
                                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path
                                        d="M10 2a6 6 0 00-6 6v3.586l-.707.707A1 1 0 004 14h12a1 1 0 00.707-1.707L16 11.586V8a6 6 0 00-6-6zM10 18a3 3 0 01-3-3h6a3 3 0 01-3 3z" />
                                </svg>
                                Registrar Gasto
                            </button>
                            <button type="button" onclick="clearForm()"
                                class="px-6 py-3 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-xl font-medium transition-all duration-300 transform hover:scale-105 flex items-center justify-center">
                                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M15.312 11.424a5.5 5.5 0 01-9.201 2.466l-.312-.311h2.433a.75.75 0 000-1.5H3.989a.75.75 0 00-.75.75v4.242a.75.75 0 001.5 0v-2.43l.31.31a7 7 0 0011.712-3.138.75.75 0 00-1.449-.39zm-3.068-9.307a7 7 0 00-11.712 3.139.75.75 0 001.449.39 5.5 5.5 0 019.201-2.466l.312.311H8.061a.75.75 0 000 1.5h4.243a.75.75 0 00.75-.75V2.999a.75.75 0 00-1.5 0v2.43l-.31-.31z"
                                        clip-rule="evenodd" />
                                </svg>
                                Limpiar
                            </button>
                        </div>
                    </form>

                </div>
                <!-- Mostrar gastos recientes -->
                <div
                    class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-xl p-4 md:p-6 border border-white/20 flex flex-col">
                    <!-- Cabecera con Título y Botón "Ver Todo" -->
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold text-gray-800">Gastos Recientes</h3>
                        <!-- Cuando le de ver todo me va a mandar a la tabla de Historial de gastos -->
                        <a href="Historial.php" class="text-sm font-medium text-indigo-600 hover:text-indigo-800">
                            Ver Todo &rarr;
                        </a>
                    </div>
                    <!-- Contenedor de la lista con altura máxima y scroll -->
                    <div class="space-y-4 overflow-y-auto pr-2" style="max-height: 700px;" id="recentExpenses">
                        <!-- El contenido se inserta aquí -->
                        <!-- Placeholder de carga -->
                        <div class="text-center py-16 text-gray-500">Cargando gastos...</div>
                    </div>
                </div>

            </div>
            <!-- Quick Actions -->
            <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-xl p-4 md:p-6 border border-white/20">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Acciones Rápidas</h3>
                <div class="quick-actions-grid grid grid-cols-1 md:grid-cols-4 gap-4">
                    <!-- Cuando quiero agregar una accion rapida se autocompletan los campos con estos valotes -->
                    <button onclick="quickExpense('alimentacion', 50000)"
                        class="bg-orange-500 hover:bg-orange-600 text-white px-4 py-3 rounded-xl font-medium transition-all duration-300 transform hover:scale-105 shadow-lg text-sm md:text-base flex items-center justify-center">
                        <svg class="w-4 h-4 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path
                                d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z" />
                        </svg>
                        Comida $50K
                    </button>
                    <!-- Cuando quiero agregar una accion rapida se autocompletan los campos con estos valotes -->
                    <button onclick="quickExpense('transporte', 30000)"
                        class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-3 rounded-xl font-medium transition-all duration-300 transform hover:scale-105 shadow-lg text-sm md:text-base flex items-center justify-center">
                        <svg class="w-4 h-4 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M8 2a2 2 0 00-2 2v1.133l-6 1.8A1 1 0 000 8v1a1 1 0 001 1h1v6a2 2 0 002 2h12a2 2 0 002-2v-6h1a1 1 0 001-1V8a1 1 0 00-.8-0.98L12 5.867V4a2 2 0 00-2-2H8zM7 8a1 1 0 012 0v2a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v2a1 1 0 102 0V8a1 1 0 00-1-1z"
                                clip-rule="evenodd" />
                        </svg>
                        Transporte $30K
                    </button>
                    <!-- Cuando quiero agregar una accion rapida se autocompletan los campos con estos valotes -->
                    <button onclick="quickExpense('entretenimiento', 25000)"
                        class="bg-purple-500 hover:bg-purple-600 text-white px-4 py-3 rounded-xl font-medium transition-all duration-300 transform hover:scale-105 shadow-lg text-sm md:text-base flex items-center justify-center">
                        <svg class="w-4 h-4 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm3 2h6v4H7V5zm8 8v2h1v-2h-1zm-2-2H7v4h6v-4zm2 0h1V9h-1v2zM5 9H4v2h1V9zm0 4H4v2h1v-2zM7 3V2a1 1 0 011-1h4a1 1 0 011 1v1H7z"
                                clip-rule="evenodd" />
                        </svg>
                        Entretenimiento $25K
                    </button>
                    <!-- Cuando quiero agregar una accion rapida se autocompletan los campos con estos valotes -->
                    <button onclick="location.href='dashboard2.php'"
                        class="bg-indigo-500 hover:bg-indigo-600 text-white px-4 py-3 rounded-xl font-medium transition-all duration-300 transform hover:scale-105 shadow-lg text-sm md:text-base flex items-center justify-center">
                        <svg class="w-4 h-4 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M2 10a8 8 0 018-8v8h8a8 8 0 11-16 0z" />
                            <path d="M12 2.252A8.014 8.014 0 0117.748 8H12V2.252z" />
                        </svg>
                        Ver Dashboard
                    </button>
                </div>
            </div>
        </main>
    </div>
</div>

<!-- ================================================== -->
<!--      NUEVO: Modal para Crear la Categoría        -->
<!-- ================================================== -->
<div id="newCategoryModal"
    class="fixed inset-0 bg-gray-900 bg-opacity-60 backdrop-blur-sm flex items-center justify-center z-50 hidden">
    <div
        class="bg-white rounded-2xl shadow-xl p-6 md:p-8 w-full max-w-lg m-4 transform transition-all duration-300 scale-95">

        <h2 class="text-2xl font-bold text-gray-800 mb-6">Añadir Nueva Categoría</h2>

        <form id="newCategoryForm" class="space-y-6">
            <!-- Nombre de la Categoría -->
            <div>
                <label for="newCategoryName" class="block text-sm font-medium text-gray-600 mb-2">Nombre de la
                    Categoría</label>
                <input type="text" id="newCategoryName" name="nombre_categoria"
                    class="w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:ring-indigo-500 focus:border-indigo-500 transition-all"
                    placeholder="Ej: Supermercado" required>
            </div>

            <!-- Selector de Iconos -->
            <div>
                <label class="block text-sm font-medium text-gray-600 mb-2">Elige un ícono</label>
                <!-- La cuadrícula de íconos se generará aquí -->
                <div id="icon-selector" class="grid grid-cols-4 sm:grid-cols-6 gap-3">
                    <!-- JS llenará este espacio -->
                </div>
                <!-- Input oculto para guardar la RUTA del ícono seleccionado -->
                <input type="hidden" id="newCategoryIcon" name="icono_ruta" required>
            </div>

            <!-- Botones de Acción -->
            <div class="pt-6 flex justify-end space-x-4 border-t border-gray-200 mt-8">
                <button type="button" id="cancelNewCategoryBtn"
                    class="px-6 py-3 bg-gray-100 text-gray-700 rounded-xl font-semibold hover:bg-gray-200 transition-colors">
                    Cancelar
                </button>
                <button type="submit"
                    class="px-6 py-3 bg-indigo-600 text-white rounded-xl font-semibold hover:bg-indigo-700 transition-colors shadow-sm">
                    Guardar
                </button>
            </div>
        </form>
    </div>
</div>
<?php 
       require_once '../models/templates/footer.php';
    ?>

<script src="../controllers/registro_gastos.js"></script>