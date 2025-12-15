<?php 
session_start();
if (!isset($_SESSION['usuario'])) {
echo "No existe usuario logueado";
header("Location: index.php");
exit;
}

$pagina_actual = 'metas';

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
    <div id="notification-list-container" class="divide-y divide-gray-100 max-h-96 overflow-y-auto">
    </div>


    <!-- Pie del Panel -->
    <a href="#" id="mark-all-as-read-btn"
        class="block py-2 text-sm font-medium text-center text-gray-900 rounded-b-xl bg-gray-50">
        Marcar todas como leídas
    </a>
</div>

<div class="p-4 sm:ml-64">
    <div class="p-2 sm:p-4 mt-14">
        <main class="flex-1 p-4 md:p-6 space-y-6">
            <div class="space-y-4">
                <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3">
                    <h2 class="text-xl sm:text-2xl font-bold text-gray-800 text-center sm:text-left">Mis Metas de Ahorro
                    </h2>

                    <button id="openNewGoalModalBtn"
                        class="w-full sm:w-auto px-4 py-2.5 bg-indigo-600 text-white rounded-lg font-medium hover:bg-indigo-700 transition-colors flex items-center justify-center space-x-2 shadow-sm">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        <span>Crear Meta</span>
                    </button>
                </div>

                <!-- Grid de metas -->
                <div id="goals-container" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6">
                </div>
            </div>
            <div id="newGoalModal"
                class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
                <div class="modal-content bg-white rounded-2xl shadow-2xl p-6 w-full max-w-lg">
                    <div class="flex justify-between items-center mb-4">
                        <h3 id="goalModalTitle" class="text-xl font-semibold text-gray-800">
                            Crear Nueva Meta</h3>
                        <button id="closeNewGoalModalBtn" class="text-gray-500 hover:text-gray-700">
                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                    clip-rule="evenodd"></path>
                            </svg>
                        </button>
                    </div>

                    <form id="newGoalForm" class="space-y-4">
                        <input type="hidden" id="goalIdToEdit" name="id_meta">

                        <div>
                            <label for="goalName" class="block text-sm font-medium text-gray-700 mb-1">Nombre de la
                                meta</label>
                            <input type="text" id="goalName" name="nombre_meta"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500"
                                required placeholder="Ej: Viaje a Cartagena">
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label for="goalAmount" class="block text-sm font-medium text-gray-700">Monto
                                    Objetivo</label>
                                <input type="number" id="goalAmount" name="monto_objetivo"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500"
                                    required min="1">
                            </div>
                            <div>
                                <label for="goalDate" class="block text-sm font-medium text-gray-700">Fecha
                                    Límite</label>
                                <input type="date" id="goalDate" name="fecha_limite"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500"
                                    required>
                            </div>
                        </div>


                        <!-- Checkbox para Ahorro Automático -->
                        <div class="pt-4 border-t border-gray-100">
                            <label class="relative inline-flex items-center cursor-pointer group">
                                <input type="checkbox" id="automaticSavingCheck" name="ahorro_automatico"
                                    class="sr-only peer">
                                <div id="toggleVisual"
                                    class="w-10 h-5 bg-gray-200 rounded-full peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-indigo-300 transition-colors duration-200 relative">
                                    <span id="toggleButton"
                                        class="absolute top-[2px] left-[2px] w-5 h-5 bg-white rounded-full transition-transform duration-200 shadow-md"></span>
                                </div>

                                <input type="checkbox" id="automaticSavingCheck" name="ahorro_automatico"
                                    class="sr-only peer"></input>
                                <span
                                    class="ml-3 text-sm font-medium text-gray-700 group-hover:text-indigo-600 transition-colors">
                                    Programar ahorro automático
                                </span>
                            </label>
                            <p class="text-xs text-gray-500 mt-1 ml-14">
                                Se debitará de tu saldo disponible automáticamente.
                            </p>
                        </div>


                        <!-- Contenedor para Opciones de Ahorro Automático (oculto por defecto) -->
                        <div id="automaticSavingOptions" class="hidden space-y-4 p-4 bg-gray-50 rounded-lg border">
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label for="savingFrequency"
                                        class="block text-sm font-medium text-gray-700">Frecuencia</label>
                                    <select id="savingFrequency" name="frecuencia_ahorro"
                                        class="w-full mt-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                                        <option value="diario">Diario</option>
                                        <!-- ¡OPCIÓN AÑADIDA! -->
                                        <option value="semanal">Semanal</option>
                                        <option value="quincenal">Quincenal
                                        </option>
                                        <option value="mensual">Mensual</option>
                                    </select>
                                </div>
                                <div>
                                    <label for="savingAmount" class="block text-sm font-medium text-gray-700">Monto por
                                        Cuota</label>
                                    <input type="number" id="savingAmount" name="monto_ahorro_programado"
                                        class="w-full mt-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500"
                                        min="1">
                                </div>
                            </div>


                            <!-- ¡NUEVO! Input que se convertirá en nuestro calendario inteligente -->
                            <div id="dateSelectorWrapper" class="hidden">
                                <label for="savingDateSelector" id="savingDateLabel"
                                    class="block text-sm font-medium text-gray-700">Selecciona el día</label>
                                <input type="text" id="savingDateSelector" name="saving_date"
                                    class="w-full mt-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500"
                                    placeholder="Haz clic para elegir...">

                                <!-- Input oculto para enviar el día correcto al backend -->
                                <input type="hidden" id="dia_seleccionado" name="dia_seleccionado">

                            </div>

                        </div>

                        <div class="pt-4 flex justify-end space-x-3">
                            <button type="button" id="cancelNewGoalBtn"
                                class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 font-medium hover:bg-gray-50">Cancelar</button>
                            <button type="submit" id="goalSubmitBtn"
                                class="px-4 py-2 bg-indigo-600 text-white rounded-lg font-medium hover:bg-indigo-700">Crear
                                Meta</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- ========= MENÚ DE OPCIONES PARA LAS TARJETAS (DROPDOWN) ========= -->
            <div id="goalOptionsMenu"
                class="hidden absolute z-20 w-44 bg-white dark:bg-gray-700 rounded-lg shadow-lg border dark:border-gray-600">
                <ul class="py-2 text-sm text-gray-700 dark:text-gray-200">
                    <li>
                        <a href="#" id="editGoalOption"
                            class="block px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-600">Editar Meta</a>
                    </li>
                    <li>
                        <a href="#" id="deleteGoalOption"
                            class="block px-4 py-2 text-red-600 dark:text-red-400 hover:bg-gray-100 dark:hover:bg-gray-600">Eliminar
                            Meta</a>
                    </li>
                </ul>
            </div>
        </main>
    </div>
</div>
<!-- ========= MODAL PARA ABONAR A META ========= -->
<div id="addFundsModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
    <div class="modal-content bg-white rounded-2xl shadow-2xl p-6 w-full max-w-sm">

        <!-- Cabecera del Modal -->
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-xl font-semibold text-gray-800">Abonar a Meta</h3>
            <button id="closeAddFundsModalBtn" class="text-gray-500 hover:text-gray-700">
                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd"
                        d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                        clip-rule="evenodd"></path>
                </svg>
            </button>
        </div>



        <!-- Formulario para Abono -->
        <form id="addFundsForm" class="space-y-4">

            <!-- Campo oculto para guardar el ID de la meta que estamos modificando -->
            <input type="hidden" id="goalIdToFund" name="id_meta">

            <div>
                <label for="fundAmount" class="block text-sm font-medium text-gray-700 mb-1">Monto a
                    abonar</label>
                <input type="number" id="fundAmount" name="monto_abono"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 "
                    required placeholder="" min="1">
            </div>


            <!-- Botones del Formulario -->
            <div class="pt-4 flex justify-end space-x-3">
                <button type="button" id="cancelAddFundsBtn"
                    class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 font-medium hover:bg-gray-50">Cancelar</button>
                <button type="submit"
                    class="px-4 py-2 bg-indigo-600 text-white rounded-lg font-medium hover:bg-indigo-700">Confirmar
                    Abono</button>
            </div>

        </form>

    </div>
</div>

<!-- ========= MODAL PARA CONFIRMAR ELIMINACIÓN ========= -->
<div id="custom-modal"
    class="fixed inset-0 bg-gray-900 bg-opacity-60 backdrop-blur-sm flex items-center justify-center z-50 hidden">
    <div id="modal-content"
        class="bg-white rounded-2xl shadow-xl p-8 w-full max-w-md m-4 transform transition-all duration-300 scale-95 opacity-0">
        <input type="hidden" id="goalIdToDelete" value="0">
        <div id="modal-icon-container"
            class="mx-auto flex-shrink-0 flex items-center justify-center h-16 w-16 rounded-full">

            <!-- El icono se insertará aquí con JS -->

        </div>
        <div class="mt-4 text-center">
            <h3 id="modal-title" class="text-2xl font-bold text-gray-900"></h3>
            <p id="modal-message" class="mt-2 text-gray-600"></p>
        </div>

        <div id="modal-buttons" class="mt-8 flex justify-center space-x-4">

            <!-- Los botones se insertarán aquí con JS -->

        </div>

    </div>
</div>


<!-- ========= MODAL DE CELEBRACIÓN DE META CUMPLIDA ========= -->
<div id="goalCelebrationModal"
    class="fixed inset-0 bg-black bg-opacity-60 backdrop-blur-sm flex items-center justify-center z-50 hidden">
    <div
        class="modal-content bg-white rounded-2xl shadow-xl p-8 w-full max-w-lg m-4 text-center transform transition-all duration-300 scale-95 opacity-0">


        <!-- El contenedor del confeti se posicionará sobre este modal -->
        <canvas id="celebration-canvas" class="absolute top-0 left-0 w-full h-full pointer-events-none"></canvas>

        <div class="text-yellow-400 text-6xl mb-4">
            🏆
        </div>

        <h2 id="celebrationTitle" class="text-3xl font-bold text-gray-900">¡FELICITACIONES!</h2>
        <p id="celebrationMessage" class="mt-3 text-lg text-gray-600">Has completado tu meta.</p>

        <div class="mt-8 flex flex-col sm:flex-row justify-center items-center gap-4">
            <button id="celebrationCloseBtn"
                class="w-full sm:w-auto px-6 py-3 border border-gray-300 rounded-lg text-gray-700 font-medium hover:bg-gray-50">
                ¡Genial!
            </button>
            <button id="celebrationNewGoalBtn"
                class="w-full sm:w-auto px-6 py-3 bg-indigo-600 text-white rounded-lg font-bold hover:bg-indigo-700">
                Crear Nueva Meta
            </button>
        </div>

    </div>
</div>



<?php 
require_once '../models/templates/footer.php';
?>
<script src="../controllers/funciones.js"></script>
<script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.9.2/dist/confetti.browser.min.js"></script>
<script src="../controllers/metas.js"></script>