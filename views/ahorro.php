<?php 
    session_start();
    if (!isset($_SESSION['usuario'])) {
    echo "No existe usuario logueado";
    header("Location: index.php");
    exit;
}

    $pagina_actual = 'ahorro';

    require_once '../models/templates/header.php';
    require_once '../models/templates/sidebar.php';
?>

<div class="p-4 sm:ml-64">
    <div class="p-4 mt-14">
        <main class="flex-1 p-4 md:p-6 space-y-6">
            <div class="space-y-6">

                <div class="flex justify-between items-center">
                    <h2 class="text-2xl font-bold text-gray-800">Mis Metas de Ahorro</h2>
                    <button id="openNewGoalModalBtn"
                        class="px-4 py-2 bg-indigo-600 text-white rounded-lg font-medium hover:bg-indigo-700 transition-colors flex items-center space-x-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                            xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        <span>Crear Ahorro</span>
                    </button>
                </div>

                <!-- 2. Contenedor para las Tarjetas de Metas (Aquí se cargarán con JS ) -->
                <div id="goals-container" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

                    <!-- TARJETA DE META AJUSTADA A TU DISEÑO -->

                    <!-- Fin de la tarjeta de ejemplo -->
                </div>
            </div>


            <!-- ========= MODAL PARA CREAR NUEVA META ========= -->
            <div id="newGoalModal"
                class="fixed inset-0 bg-gray-900 bg-opacity-75 flex items-center justify-center z-50 transition-opacity duration-300 ease-out opacity-0 pointer-events-none">
                <div
                    class="modal-content bg-white rounded-xl shadow-2xl p-8 w-full max-w-md transform scale-95 transition-transform duration-300 ease-out">
                    <!-- Cabecera del Modal -->
                    <div class="flex justify-between items-center mb-6">
                        <h3 id="goalModalTitle" class="text-2xl font-bold text-gray-800">Crear nueva meta de Ahorro</h3>
                        <button id="closeNewGoalModalBtn" class="text-gray-400 hover:text-gray-600 transition-colors">
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>

                    <!-- Formulario para Nueva Meta -->
                    <form id="newGoalForm" class="space-y-5">
                        <input type="hidden" id="goalIdToEdit" name="id_meta">
                        <div>
                            <label for="goalName" class="block text-sm font-medium text-gray-700 mb-2">Nombre del
                                Ahorro</label>
                            <input type="text" id="goalName" name="nombre_ahorro"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 transition-all duration-200"
                                required placeholder="Ej: Viaje a Cancún">
                        </div>
                        <div>
                            <label for="goalAmount" class="block text-sm font-medium text-gray-700 mb-2">¿Cuánto
                                vas a ahorrar?</label>
                            <input type="number" id="goalAmount" name="monto_objetivo"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 transition-all duration-200"
                                required placeholder="" min="1">
                        </div>
                        <div>
                            <label for="goalDate" class="block text-sm font-medium text-gray-700 mb-2">Fecha límite
                                (Opcional)</label>
                            <input type="date" id="goalDate" name="fecha_limite"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 transition-all duration-200">
                        </div>

                        <!-- Botones del Formulario -->
                        <div class="pt-4 flex justify-end space-x-3">
                            <button type="button" id="cancelNewGoalBtn"
                                class="px-5 py-2 border border-gray-300 rounded-lg text-gray-700 font-medium hover:bg-gray-100 transition-colors duration-200">Cancelar</button>
                            <button type="submit" id="goalSubmitBtn" form="newGoalForm"
                                class="px-5 py-2 bg-indigo-600 text-white rounded-lg font-medium hover:bg-indigo-700 transition-colors duration-200">
                                Crear Meta
                            </button>
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
<div id="deleteGoalModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
    <div class="modal-content bg-white dark:bg-gray-800 rounded-2xl shadow-2xl p-6 w-full max-w-sm">
        <!-- Icono y Cabecera -->
        <div class="text-center">
            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100 dark:bg-red-900/50">
                <svg class="h-6 w-6 text-red-600 dark:text-red-300" stroke="currentColor" fill="none"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
            </div>
            <h3 class="mt-4 text-xl font-semibold text-gray-800 dark:text-white">Eliminar Meta</h3>
            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">¿Estás seguro de que quieres eliminar esta
                meta? Esta acción no se puede deshacer.</p>
        </div>

        <!-- Campo oculto para el ID de la meta a eliminar -->
        <input type="hidden" id="goalIdToDelete">

        <!-- Botones de Acción -->
        <div class="mt-6 grid grid-cols-2 gap-3">
            <button type="button" id="cancelDeleteBtn"
                class="px-4 py-2 border border-gray-300 dark:border-gray-500 rounded-lg text-gray-700 dark:text-gray-300 font-medium hover:bg-gray-50 dark:hover:bg-gray-700">
                Cancelar
            </button>
            <button type="button" id="confirmDeleteBtn"
                class="px-4 py-2 bg-red-600 text-white rounded-lg font-medium hover:bg-red-700">
                Sí, Eliminar
            </button>
        </div>
    </div>
</div>

<?php 
        require_once '../models/templates/footer.php';
    ?>
<script src="../controllers/ahorro.js"></script>