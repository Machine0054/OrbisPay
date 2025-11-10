<?php 
session_start(); 
if (!isset($_SESSION['usuario'])) { echo "No existe usuario logueado, por favor vuelva a iniciar sessión" ; 
    header("Location:index.php"); 
    exit; 
} 
    $pagina_actual = 'registro_ingresos'; 
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
        <!-- Main Content -->
        <main class="flex-1 p-6">
            <!-- Income Registration Section -->
            <div id="income-section" class="space-y-6">
                <!-- Quick Stats -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                    <div
                        class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-xl p-6 card-hover border border-white/20">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-sm font-medium text-gray-600 uppercase tracking-wide">Total
                                    Ingresos
                                </h3>
                                <p class="text-2xl font-bold text-emerald-600 mt-2"></p>
                            </div>
                            <div class="bg-emerald-100 p-3 rounded-full">
                                <svg class="w-6 h-6 text-emerald-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-8.293l-3-3a1 1 0 00-1.414 0l-3 3a1 1 0 001.414 1.414L9 9.414V13a1 1 0 102 0V9.414l1.293 1.293a1 1 0 001.414-1.414z"
                                        clip-rule="evenodd" />
                                </svg>
                            </div>
                        </div>
                    </div>
                    <div
                        class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-xl p-6 card-hover border border-white/20">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-sm font-medium text-gray-600 uppercase tracking-wide">Este Mes
                                </h3>
                                <p class="text-2xl font-bold text-indigo-600 mt-2"></p>
                            </div>
                            <div class="bg-indigo-100 p-3 rounded-full">
                                <svg class="w-6 h-6 text-indigo-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path
                                        d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" />
                                </svg>
                            </div>
                        </div>
                    </div>
                    <div
                        class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-xl p-6 card-hover border border-white/20">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-sm font-medium text-gray-600 uppercase tracking-wide">
                                    Transacciones</h3>
                                <p class="text-2xl font-bold text-purple-600 mt-2"></p>
                            </div>
                            <div class="bg-purple-100 p-3 rounded-full">
                                <svg class="w-6 h-6 text-purple-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z" />
                                    <path fill-rule="evenodd"
                                        d="M4 5a2 2 0 012-2h8a2 2 0 012 2v6a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 1a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z"
                                        clip-rule="evenodd" />
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Income Form -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-xl p-8 border border-white/20">
                        <h3 class="text-2xl font-bold text-gray-800 mb-6 flex items-center">
                            <svg class="w-8 h-8 text-emerald-600 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v2H7a1 1 0 100 2h2v2a1 1 0 102 0v-2h2a1 1 0 100-2h-2V7z"
                                    clip-rule="evenodd" />
                            </svg>
                            Nuevo Ingreso
                        </h3>
                        <form id="income-form" class="space-y-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Concepto</label>
                                <input type="text" id="concept" name="concept"
                                    class="form-input w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent bg-white/50"
                                    placeholder="Ej: Salario, Freelance, Inversión..." required>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Monto</label>
                                <div class="relative flex items-center">
                                    <span class="absolute left-3 text-red-500 text-xl font-bold">
                                        $
                                    </span>
                                    <input type="number" id="amount" name="amount"
                                        class="form-input w-full pl-8 pr-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent bg-white/50"
                                        placeholder="0" required>
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Categoría</label>
                                <div class="flex items-center space-x-2">
                                    <select id="category-select" name="category"
                                        class="custom-select form-select w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent bg-white/50">
                                        <!-- Las opciones se cargarán aquí con JavaScript -->
                                        <option value="">Selecciona una categoría...</option>
                                    </select>
                                    <button type="button" id="btn-add-category"
                                        class="flex-shrink-0 bg-emerald-500 hover:bg-emerald-600 text-white font-bold p-3 rounded-xl transition-all duration-300 transform hover:scale-105 shadow-lg">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                            xmlns="http://www.w3.org/2000/svg">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Fecha</label>
                                <input type="date" id="date" name="date"
                                    class="form-input w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent bg-white/50"
                                    required>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Notas
                                    (opcional)</label>
                                <textarea id="notes" rows="3" name="notes"
                                    class="form-input w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent bg-white/50"
                                    placeholder="Información adicional..."></textarea>
                            </div>
                            <div class="flex space-x-4">
                                <button type="button" id="btnGuardarIngreso"
                                    class="flex-1 bg-emerald-500 hover:bg-emerald-600 text-white font-medium py-3 px-6 rounded-xl transition-all duration-300 transform hover:scale-105 shadow-lg flex items-center justify-center">
                                    <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.236 4.53L8.077 10.5a.75.75 0 00-1.06 1.06l2.47 2.47a.75.75 0 001.137-.089l3.857-5.401z"
                                            clip-rule="evenodd" />
                                    </svg>
                                    Registrar Ingreso
                                </button>
                                <button type="reset"
                                    class="bg-gray-500 hover:bg-gray-600 text-white font-medium py-3 px-6 rounded-xl transition-all duration-300 hover:scale-105 flex items-center justify-center">
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
                    <!-- Recent Incomes -->
                    <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-xl p-8 border border-white/20">
                        <h3 class="text-2xl font-bold text-gray-800 mb-6">Ingresos Recientes</h3>
                        <div id="recent-incomes" class="space-y-4">
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>
<!-- Success Modal -->
<div id="success-modal" class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-2xl p-8 max-w-md w-full mx-4 shadow-2xl">
        <div class="text-center">
            <div class="w-16 h-16 bg-emerald-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-emerald-600" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd"
                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                        clip-rule="evenodd" />
                </svg>
            </div>
            <h3 class="text-xl font-bold text-gray-800 mb-2">¡Ingreso Registrado!</h3>
            <p class="text-gray-600 mb-6">El ingreso se ha guardado exitosamente en tu balance.</p>
            <button onclick="closeModal()"
                class="bg-emerald-500 hover:bg-emerald-600 text-white font-medium py-2 px-6 rounded-lg transition-all duration-300">
                Continuar
            </button>
        </div>
    </div>
</div>
<!-- =================================================================
MODAL DE CONFIRMACIÓN REUTILIZABLE (ESTILO CENTRADO)
================================================================== -->
<div id="confirmationModal" class="fixed inset-0 z-50 hidden transition-opacity duration-300 opacity-0">

    <!-- Overlay de fondo -->
    <div class="absolute inset-0 bg-black bg-opacity-60 backdrop-blur-sm" aria-hidden="true"></div>

    <!-- Contenedor para centrar el modal -->
    <div class="relative w-full max-w-md p-4 h-full flex items-center justify-center mx-auto">

        <!-- Contenido/Caja del Modal -->
        <div id="confirmationModalBox"
            class="relative bg-white rounded-2xl shadow-2xl p-6 md:p-8 w-full transform scale-95 transition-all duration-300">

            <!-- Contenedor principal del contenido (todo centrado) -->
            <div class="text-center">

                <!-- Icono -->
                <div id="confirmationModalIconContainer"
                    class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-red-100 mb-4">
                    <!-- El icono SVG se insertará aquí con JS -->
                </div>

                <!-- Título -->
                <h3 id="confirmationModalTitle" class="text-xl font-semibold text-gray-900">
                    <!-- Título dinámico -->
                </h3>

                <!-- Mensaje -->
                <div class="mt-2">
                    <p id="confirmationModalMessage" class="text-sm text-gray-500">
                        <!-- Mensaje dinámico -->
                    </p>
                </div>
            </div>

            <!-- Botones de Acción -->
            <div class="mt-8 flex justify-center gap-4">
                <button id="confirmationModalCancelBtn" type="button"
                    class="rounded-lg px-6 py-2 font-medium text-gray-700 bg-gray-200 hover:bg-gray-300 focus:outline-none">
                    Cancelar
                </button>
                <button id="confirmationModalConfirmBtn" type="button"
                    class="rounded-lg px-6 py-2 font-medium text-white bg-red-600 hover:bg-red-700 focus:outline-none">
                    Sí, Eliminar
                </button>
            </div>
        </div>
    </div>
</div>
<div id="add-category-modal"
    class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-2xl p-8 max-w-md w-full mx-4 shadow-2xl">
        <h3 class="text-xl font-bold text-gray-800 mb-6">Nueva Categoría de Ingreso</h3>

        <div class="space-y-4">
            <div>
                <label for="new-category-name" class="block text-sm font-medium text-gray-700 mb-2">Nombre de la
                    Categoría</label>
                <input type="text" id="new-category-name" placeholder="Ej: Inversiones, Bonos, etc."
                    class="form-input w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500">
            </div>
            <div class="flex justify-end space-x-4 pt-4">
                <button type="button" id="btn-cancel-add-category"
                    class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium py-2 px-6 rounded-lg transition-all">
                    Cancelar
                </button>
                <button type="button" id="btn-save-category"
                    class="bg-emerald-500 hover:bg-emerald-600 text-white font-medium py-2 px-6 rounded-lg transition-all">
                    Guardar
                </button>
            </div>
        </div>
    </div>
</div>
<?php 

    require_once '../models/templates/footer.php';
  
?>
<script src="../controllers/registro_ingresos.js"></script>