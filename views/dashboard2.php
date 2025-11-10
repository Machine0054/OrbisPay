<?php 
    session_start();
    if (!isset($_SESSION['usuario'])) {
    echo "No existe usuario logueado";
    header("Location: index.php");
    exit;
}

    $pagina_actual = 'dashboard2'; 
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
        <main class="flex-1 p-3 sm:p-4 lg:p-6 space-y-4 sm:space-y-6 overflow-x-hidden">
            <!-- Summary Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 items-stretch">
                <div
                    class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-xl p-4 sm:p-5 card-hover border border-white/20 flex flex-col">
                    <div class="flex items-center justify-between">
                        <div class="min-w-0 flex-1">
                            <h2 class="text-xs sm:text-sm font-medium text-gray-600 uppercase tracking-wide">
                                Ingresos
                                del mes</h2>
                            <p id="ingresosMes"
                                class="text-xl sm:text-2xl lg:text-3xl font-bold text-emerald-600 mt-2 responsive-text-2xl">
                                $0</p>
                            <p id="ingresosMesAnterior" class="text-xs sm:text-sm text-emerald-500 mt-1">--% vs mes
                                anterior</p>
                        </div>
                        <div class="bg-emerald-100 p-2 sm:p-3 rounded-full flex-shrink-0">
                            <svg class="w-6 h-6 sm:w-8 sm:h-8 text-emerald-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-8.293l-3-3a1 1 0 00-1.414 0l-3 3a1 1 0 001.414 1.414L9 9.414V13a1 1 0 102 0V9.414l1.293 1.293a1 1 0 001.414-1.414z"
                                    clip-rule="evenodd" />
                            </svg>
                        </div>
                    </div>
                </div>
                <!-- ========= WIDGET DE PROYECCIÓN DE SALDO ========= -->
                <div id="proyeccion-widget"
                    class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-xl p-4 sm:p-5 card-hover border border-white/20 flex flex-col">

                    <!-- Cabecera del Widget -->
                    <div class="flex justify-between items-center mb-3">
                        <h2 class="text-xs sm:text-sm font-medium text-gray-600 uppercase tracking-wide">Proyección
                            a Fin de Mes</h2>
                        <span id="proyeccion-loader"
                            class="text-xl sm:text-2xl lg:text-3xl font-bold text-emerald-600 mt-2 responsive-text-2xl"></span>
                        <span id="proyeccion-status-icon"
                            class="hidden w-5 h-5 rounded-full flex items-center justify-center"></span>
                    </div>

                    <!-- Cuerpo del Widget -->
                    <div class="text-center">
                        <!-- El valor principal de la proyección -->
                        <p id="proyeccion-valor" class="text-3xl font-bold text-gray-300">$ -</p>
                        <p id="proyeccion-consejo" class="text-sm text-gray-400 mt-1">Análisis en
                            progreso...</p>
                    </div>

                    <!-- (Opcional) Detalles adicionales que se pueden mostrar al hacer clic -->
                    <div id="proyeccion-detalles"
                        class="text-xs text-gray-500 dark:text-gray-400 mt-4 pt-3 border-t border-gray-200 dark:border-gray-700 space-y-1 hidden">
                        <div class="flex justify-between"><span>Saldo Actual:</span> <span
                                id="detalle-saldo-actual"></span></div>
                        <div class="flex justify-between"><span>Gastos Proyectados:</span> <span
                                id="detalle-gastos-proyectados"></span></div>
                        <div class="flex justify-between"><span>Ingresos Restantes:</span> <span
                                id="detalle-ingresos-restantes"></span></div>
                    </div>
                </div>
                <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-xl p-6 card-hover border border-white/20">
                    <div class="flex items-center justify-between">
                        <div class="min-w-0 flex-1">
                            <h2 class="text-xs sm:text-sm font-medium text-gray-600 uppercase tracking-wide">Gastos
                                del
                                mes</h2>
                            <p id="gastosMes"
                                class="text-xl sm:text-2xl lg:text-3xl font-bold text-red-600 mt-2 responsive-text-2xl">
                                $0</p>
                            <p id="gastosMesAnterior" class="text-xs sm:text-sm text-red-500 mt-1">--% vs mes
                                anterior</p>
                        </div>
                        <div class="bg-red-100 p-2 sm:p-3 rounded-full flex-shrink-0">
                            <svg class="w-6 h-6 sm:w-8 sm:h-8 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v3.586L7.707 9.293a1 1 0 00-1.414 1.414l3 3a1 1 0 001.414 0l3-3a1 1 0 00-1.414-1.414L11 10.586V7z"
                                    clip-rule="evenodd" />
                            </svg>
                        </div>
                    </div>
                </div>
                <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-xl p-6 card-hover border border-white/20">
                    <div class="flex items-center justify-between">
                        <div class="min-w-0 flex-1">
                            <h2 class="text-xs sm:text-sm font-medium text-gray-600 uppercase tracking-wide">Balance
                            </h2>
                            <p id="balanceMes"
                                class="text-xl sm:text-2xl lg:text-3xl font-bold text-indigo-600 mt-2 responsive-text-2xl">
                                $0</p>
                            <p id="porcentajeAhorro" class="text-xs sm:text-sm text-indigo-500 mt-1">--% de ahorro
                            </p>
                        </div>
                        <div class="bg-indigo-100 p-2 sm:p-3 rounded-full flex-shrink-0">
                            <svg class="w-6 h-6 sm:w-8 sm:h-8 text-indigo-600" fill="currentColor" viewBox="0 0 20 20">
                                <path
                                    d="M8.433 7.418c.155-.103.346-.196.567-.267v1.698a2.305 2.305 0 01-.567-.267C8.07 8.34 8 8.114 8 8c0-.114.07-.34.433-.582zM11 12.849v-1.698c.22.071.412.164.567.267.364.243.433.468.433.582 0 .114-.07.34-.433.582a2.305 2.305 0 01-.567.267z" />
                                <path fill-rule="evenodd"
                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-13a1 1 0 10-2 0v.092a4.535 4.535 0 00-1.676.662C6.602 6.234 6 7.009 6 8c0 .99.602 1.765 1.324 2.246.48.32 1.054.545 1.676.662v1.941c-.391-.127-.68-.317-.843-.504a1 1 0 10-1.51 1.31c.562.649 1.413 1.076 2.353 1.253V15a1 1 0 102 0v-.092a4.535 4.535 0 001.676-.662C13.398 13.766 14 12.991 14 12c0-.99-.602-1.765-1.324-2.246A4.535 4.535 0 0011 9.092V7.151c.391.127.68.317.843.504a1 1 0 101.511-1.31c-.563-.649-1.413-1.076-2.354-1.253V5z"
                                    clip-rule="evenodd" />
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts and Analysis -->
            <div class="grid grid-cols-1 xl:grid-cols-2 gap-4 sm:gap-6">
                <!-- Monthly Trend Chart -->
                <div
                    class="bg-white/80 backdrop-blur-sm rounded-xl sm:rounded-2xl shadow-xl p-4 sm:p-6 border border-white/20">
                    <h3 class="text-base sm:text-lg font-semibold text-gray-800 mb-4 responsive-text-xl">Tendencia
                        Mensual</h3>
                    <div class="h-48 sm:h-64 lg:h-72">
                        <canvas id="monthlyChart" class="w-full h-full"></canvas>
                    </div>
                </div>

                <!-- Transaciones Recientes -->
                <div
                    class="bg-white/80 backdrop-blur-sm rounded-xl sm:rounded-2xl shadow-xl p-4 sm:p-6 border border-white/20">
                    <h3 class="text-base sm:text-lg font-semibold text-gray-800 mb-4 responsive-text-xl">
                        Transacciones Recientes</h3>
                    <!-- Contenedor para las transacciones con altura y scroll -->
                    <div id="recentTransactionsContainer"
                        class="space-y-3 sm:space-y-4 max-h-64 sm:max-h-72 lg:max-h-80 overflow-y-auto pr-2">
                        <!-- Las transacciones se insertarán aquí con JS -->
                        <div class="text-center py-16 text-gray-500">Cargando...</div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>
<?php
// 3. Incluir el Footer
require_once '../models/templates/footer.php';

?>