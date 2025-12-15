<?php 
    session_start();
    if (!isset($_SESSION['usuario'])) {
    echo "No existe usuario logueado";
    header("Location: index.php");
    exit;
}

    $pagina_actual = 'deudas'; 
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
        <main class="flex-1 p-3 sm:p-4 lg:p-6 space-y-6">
            <!-- ========= CABECERA DE LA SECCIÓN ========= -->
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <!-- <h2 class="text-2xl font-bold text-gray-800">Gestión de Deudas y Préstamos</h2> -->
                <button id="openNewDebtModalBtn"
                    class="px-3 py-2 bg-indigo-600 text-white rounded-lg font-medium hover:bg-indigo-700 transition-colors flex items-center space-x-2 shadow-lg">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                        xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    <span>Registrar Nuevo</span>
                </button>
            </div>
            <!-- ========= CONTENEDOR PRINCIPAL CON DOS COLUMNAS ========= -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">

                <!-- 1. COLUMNA DE DEUDAS (Dinero que debo ) -->
                <div class="space-y-4">
                    <h3 class="text-xl font-semibold text-gray-700">Mis Deudas (Pasivos)</h3>
                    <!-- Este contenedor será llenado por deudas.js -->
                    <div id="deudas-container" class="grid grid-cols-1 xl:grid-cols-2 gap-4">
                        <!-- JavaScript insertará las tarjetas de deudas aquí -->
                    </div>
                </div>
                <!-- 2. COLUMNA DE PRÉSTAMOS (Dinero que me deben) -->
                <div class="space-y-4">
                    <h3 class="text-xl font-semibold text-gray-700">Mis Préstamos (Activos)</h3>
                    <!-- Este contenedor será llenado por deudas.js -->
                    <div id="prestamos-container" class="grid grid-cols-1 xl:grid-cols-2 gap-4">
                        <!-- JavaScript insertará las tarjetas de préstamos aquí -->
                    </div>
                </div>

            </div>
        </main>
    </div>
</div>
<!-- ========= MODAL PARA REGISTRAR NUEVA DEUDA O PRÉSTAMO ========= -->
<div id="newDebtModal"
    class="fixed inset-0 bg-gray-900 bg-opacity-60 backdrop-blur-sm flex items-center justify-center z-50 hidden">
    <div class="bg-white  rounded-2xl shadow-2xl w-full max-w-md mx-4">
        <div class="flex justify-between items-center p-5 border-b border-gray-200">
            <h3 class="text-xl font-semibold text-gray-800">Registrar Deuda o Préstamo</h3>
            <button id="closeNewDebtModalBtn" class="text-gray-400 hover:text-gray-600 transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                    </path>
                </svg>
            </button>
        </div>
        <form id="newDebtForm" class="p-6 space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Tipo de
                    Registro</label>
                <select name="tipo"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg  focus:ring-indigo-500 focus:border-indigo-500"
                    required>
                    <option value="Deuda">Es una Deuda (Yo debo dinero)</option>
                    <option value="Préstamo">Es un Préstamo (Me deben dinero)</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700  mb-1">Descripción</label>
                <input type="text" name="descripcion"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg  focus:ring-indigo-500 focus:border-indigo-500"
                    placeholder="Ej: Préstamo para el computador" required>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700  mb-1">Persona o Entidad
                    (Opcional)</label>
                <input type="text" name="acreedor_deudor"
                    class="w-full px-4 py-2 border border-gray-300  rounded-lg  focus:ring-indigo-500 focus:border-indigo-500"
                    placeholder="Ej: Banco de Bogotá">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Monto Total
                    Inicial</label>
                <input type="number" step="0.01" name="monto_inicial"
                    class="w-full px-4 py-2 border border-gray-300  rounded-lg  focus:ring-indigo-500 focus:border-indigo-500"
                    placeholder="" required>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700  mb-1">Fecha de
                    Origen</label>
                <input type="date" name="fecha_creacion"
                    class="w-full px-4 py-2 border border-gray-300  rounded-lg focus:ring-indigo-500 focus:border-indigo-500"
                    required>
            </div>
            <div class="pt-4 flex justify-end space-x-3">
                <button type="button" id="cancelNewDebtBtn"
                    class="px-4 py-2 border border-gray-300  rounded-lg text-gray-700  hover:bg-gray-50">Cancelar</button>
                <button type="submit"
                    class="px-4 py-2 bg-indigo-600 text-white rounded-lg font-medium hover:bg-indigo-700">Guardar</button>
            </div>
        </form>
    </div>
</div>
<!-- ========= MODAL PARA REGISTRAR UN ABONO (DISEÑO MEJORADO) ========= -->
<div id="addPaymentModal"
    class="fixed inset-0 bg-gray-900 bg-opacity-60 backdrop-blur-sm flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm mx-4">
        <div class="flex justify-between items-center p-5 border-b border-gray-200">
            <h3 class="text-xl font-semibold text-gray-800 ">Registrar Abono</h3>
            <button id="closeAddPaymentModalBtn" class="text-gray-400 hover:text-gray-600 transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                    </path>
                </svg>
            </button>
        </div>
        <form id="addPaymentForm" class="p-6 space-y-4">
            <input type="hidden" name="id_deuda">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Monto del
                    Abono</label>
                <input type="number" name="monto_abono"
                    class="w-full px-4 py-2 border rounded-lg  focus:ring-indigo-500 focus:border-indigo-500" required>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700  mb-1">Fecha del
                    Abono</label>
                <input type="date" name="fecha_abono" class="w-full px-4 py-2 border rounded-lg" required>
            </div>
            <div class="pt-4 flex justify-end space-x-3">
                <button type="button" id="cancelPaymentBtn"
                    class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700  hover:bg-gray-50 ">Cancelar</button>
                <button type="submit"
                    class="px-4 py-2 bg-indigo-600 text-white rounded-lg font-medium hover:bg-indigo-700">Confirmar
                    Abono</button>
            </div>
        </form>
    </div>
</div>

<!-- ========= MODAL PARA VER HISTORIAL DE ABONOS ========= -->
<div id="historyModal"
    class="fixed inset-0 bg-gray-900 bg-opacity-60 backdrop-blur-sm flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg mx-4">
        <div class="flex justify-between items-center p-5 border-b border-gray-200">
            <div>
                <h3 class="text-xl font-semibold text-gray-800">Historial de Abonos</h3>
                <p id="historyModalTitle" class="text-sm text-gray-500"></p>
            </div>
            <button id="closeHistoryModalBtn" class="text-gray-400 hover:text-gray-600 transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                    </path>
                </svg>
            </button>
        </div>
        <div id="historyModalBody" class="p-6 max-h-[60vh] overflow-y-auto">

        </div>
    </div>
</div>
<!-- ========= MODAL DE FELICITACIÓN POR DEUDA PAGADA ========= -->
<div id="celebration-modal"
    class="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-2xl p-8 max-w-md w-full mx-4 shadow-2xl text-center transform scale-95 opacity-0 transition-all duration-300"
        id="celebration-modal-box">

        <!-- El lienzo para el confeti se posiciona sobre todo el modal -->
        <canvas id="celebration-canvas" class="absolute top-0 left-0 w-full h-full pointer-events-none z-10"></canvas>
        <div class="relative z-0">
            <!-- Contenido del modal -->
            <div class="flex items-center justify-center mb-5">

                <img src="../assets/icons/TROFEO.png" alt="Trofeo" class="w-20 h-20 object-contain">
            </div>
            <h3 class="text-2xl font-bold text-gray-800 mb-2">¡Felicitaciones!</h3>
            <p class="text-gray-600 mb-6">Has saldado completamente una deuda. ¡Un paso más hacia tu libertad
                financiera!</p>
            <button id="celebration-close-btn"
                class="bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-2 px-5 rounded-lg transition-all duration-300">
                ¡Genial!
            </button>

        </div>
    </div>
</div>

<div id="editDebtModal"
    class="fixed inset-0 bg-gray-900 bg-opacity-60 backdrop-blur-sm flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md mx-4">
        <div class="flex justify-between items-center p-5 border-b border-gray-200">
            <h3 class="text-xl font-semibold text-gray-800">Editar Registro</h3>
            <button id="closeEditDebtModalBtn" class="text-gray-400 hover:text-gray-600 transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                    </path>
                </svg>
            </button>
        </div>
        <form id="editDebtForm" class="p-6 space-y-4">
            <!-- Campo oculto para guardar el ID de la deuda que se está editando -->
            <input type="hidden" name="id_deuda" id="edit_id_deuda">


            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Tipo de Registro</label>
                <select name="tipo" id="edit_tipo"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500"
                    required>
                    <option value="Deuda">Es una Deuda (Yo debo dinero)</option>
                    <option value="Préstamo">Es un Préstamo (Me deben dinero)</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Descripción</label>
                <input type="text" name="descripcion" id="edit_descripcion"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500"
                    placeholder="Ej: Préstamo para el computador" required>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Persona o Entidad (Opcional)</label>
                <input type="text" name="acreedor_deudor" id="edit_acreedor_deudor"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500"
                    placeholder="Ej: Banco de Bogotá">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Monto Total Inicial</label>
                <input type="number" step="0.01" name="monto_inicial" id="edit_monto_inicial"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500"
                    placeholder="" required>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Fecha de Origen</label>
                <input type="date" name="fecha_creacion" id="edit_fecha_creacion"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500"
                    required>
            </div>
            <div class="pt-4 flex justify-end space-x-3">
                <button type="button" id="cancelEditDebtBtn"
                    class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">Cancelar</button>
                <button type="submit"
                    class="px-4 py-2 bg-indigo-600 text-white rounded-lg font-medium hover:bg-indigo-700">Guardar
                    Cambios</button>
            </div>
        </form>
    </div>
</div>
<!-- =================================================================
MODAL DE CONFIRMACIÓN REUTILIZABLE (El que falta en tu página)
================================================================== -->
<!-- =================================================================
MODAL DE CONFIRMACIÓN REUTILIZABLE (VERSIÓN CORREGIDA Y CENTRADA)
================================================================== -->
<div id="confirmationModal"
    class="fixed inset-0 z-50 hidden flex items-center justify-center bg-gray-900 bg-opacity-60 backdrop-blur-sm">
    <!-- El resto del contenido del modal se queda exactamente igual -->
    <div class="relative w-full max-w-md p-4">
        <div class="relative rounded-2xl bg-white p-6 text-center shadow-2xl sm:p-8">

            <!-- Contenedor del Ícono -->
            <div id="confirmationModalIconContainer"
                class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-red-100">
                <!-- El SVG del ícono se insertará aquí -->
            </div>

            <!-- Título -->
            <h3 id="confirmationModalTitle" class="text-xl font-semibold text-gray-900">
                Eliminar Registro
            </h3>

            <!-- Mensaje -->
            <div class="mt-2">
                <p id="confirmationModalMessage" class="text-sm text-gray-500">
                    ¿Estás seguro? Esta acción no se puede deshacer.
                </p>
            </div>

            <!-- Botones de Acción -->
            <div class="mt-8 flex justify-center gap-4">
                <button id="confirmationModalCancelBtn" type="button"
                    class="rounded-lg px-6 py-2.5 font-medium text-gray-700 bg-gray-200 hover:bg-gray-300 focus:outline-none">
                    Cancelar
                </button>
                <button id="confirmationModalConfirmBtn" type="button"
                    class="rounded-lg px-6 py-2.5 font-medium text-white focus:outline-none">
                    Sí, Eliminar
                </button>
            </div>
        </div>
    </div>
</div>



<?php 
        require_once '../models/templates/footer.php';
    
    ?>
<script src="../controllers/deudas.js"></script>