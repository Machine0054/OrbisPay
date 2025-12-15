<?php 
    session_start();

    if (!isset($_SESSION['usuario'])) {
    echo "No existe usuario logueado";
    header("Location: index.php");
    exit;
}

    $pagina_actual = 'configuracion'; 

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

        <!-- Settings Content -->
        <main class="flex-1 p-4 md:p-6 space-y-6">
            <!-- User Profile -->
            <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-xl p-4 md:p-6 border border-white/20">
                <h3 class="text-lg font-semibold text-gray-800 mb-6">Perfil de Usuario</h3>
                <div class="profile-section flex flex-col md:flex-row md:space-x-6 space-y-6 md:space-y-0">
                    <div class="profile-avatar flex flex-col items-center">
                        <div class="relative mb-4">
                            <img src="../assets/icons/Avatar.webp" alt="Avatar"
                                class="w-20 h-20 md:w-24 md:h-24 rounded-full ring-4 ring-white shadow-lg">
                            <button onclick="document.getElementById('avatarInput').click()"
                                class="absolute bottom-0 right-0 bg-indigo-500 text-white p-2 rounded-full shadow-lg hover:bg-indigo-600 transition-colors">
                                <svg class="w-3 h-3 md:w-4 md:h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path
                                        d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                                </svg>
                            </button>
                            <input type="file" id="avatarInput" class="hidden" accept="image/*">
                        </div>
                        <button onclick="changeAvatar()"
                            class="px-4 py-2 bg-indigo-500 text-white rounded-lg text-sm font-medium hover:bg-indigo-600 transition-colors">
                            Cambiar foto
                        </button>
                    </div>
                    <form class="flex-1 space-y-4" id="profileForm">
                        <div class="form-grid grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="profileNombre"
                                    class="block text-sm font-medium text-gray-700 mb-1">Nombre</label>
                                <input type="text" id="profileNombre" name="nombre"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                            </div>
                            <div>
                                <label for="profileApellido"
                                    class="block text-sm font-medium text-gray-700 mb-1">Apellido</label>
                                <input type="text" id="profileApellido" name="apellido"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                            </div>
                        </div>
                        <div>
                            <label for="profileEmail" class="block text-sm font-medium text-gray-700 mb-1">Correo
                                electrónico</label>
                            <input type="email" id="profileEmail" name="email"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                        <div class="form-grid grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="profileTelefono"
                                    class="block text-sm font-medium text-gray-700 mb-1">Teléfono</label>
                                <input type="tel" id="profileTelefono" name="telefono"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                            </div>
                            <div>
                                <label for="profileMoneda" class="block text-sm font-medium text-gray-700 mb-1">Moneda
                                    predeterminada</label>
                                <select id="profileMoneda" name="moneda"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                                    <option value="COP">Peso Colombiano (COP)</option>
                                    <option value="USD">Dólar Estadounidense (USD)</option>
                                    <option value="EUR">Euro (EUR)</option>
                                </select>
                            </div>
                        </div>
                        <div class="pt-4">
                            <button type="submit"
                                class="w-full md:w-auto px-6 py-2 bg-indigo-600 text-white rounded-lg font-medium hover:bg-indigo-700 transition-colors">
                                Guardar cambios
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Application Settings -->
            <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-xl p-4 md:p-6 border border-white/20">
                <h3 class="text-lg font-semibold text-gray-800 mb-6">Configuración de la Aplicación</h3>
                <div class="space-y-6">
                    <!-- Notifications -->
                    <div class="settings-card p-4 bg-white rounded-lg border border-gray-200">
                        <div class="flex justify-between items-center">
                            <div>
                                <h4 class="font-medium text-gray-800">Notificaciones</h4>
                                <p class="text-sm text-gray-600">Recibe alertas sobre tus finanzas</p>
                            </div>
                            <label class="switch">
                                <input type="checkbox" checked>
                                <span class="slider"></span>
                            </label>
                        </div>
                        <div class="mt-4 space-y-3 pl-2">
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-700">Alertas de presupuesto</span>
                                <label class="switch">
                                    <input type="checkbox" checked>
                                    <span class="slider"></span>
                                </label>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-700">Recordatorios de pago</span>
                                <label class="switch">
                                    <input type="checkbox">
                                    <span class="slider"></span>
                                </label>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-700">Resumen semanal</span>
                                <label class="switch">
                                    <input type="checkbox" checked>
                                    <span class="slider"></span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Security -->
                    <div class="settings-card p-4 bg-white rounded-lg border border-gray-200">
                        <div class="flex justify-between items-center">
                            <div>
                                <h4 class="font-medium text-gray-800">Seguridad</h4>
                                <p class="text-sm text-gray-600">Configuración de seguridad de tu cuenta</p>
                            </div>
                        </div>
                        <div class="mt-4 space-y-4">
                            <div>
                                <button id="showChangePasswordModalBtn"
                                    class="settings-button w-full flex justify-between items-center p-3 bg-gray-50 hover:bg-gray-100 rounded-lg transition-colors">
                                    <span class="text-sm font-medium text-gray-800">Cambiar contraseña</span>
                                    <svg class="w-5 h-5 text-gray-500" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                                            clip-rule="evenodd" />
                                    </svg>
                                </button>
                            </div>
                            <div>
                                <button onclick="showTwoFactorModal()"
                                    class="settings-button w-full flex justify-between items-center p-3 bg-gray-50 hover:bg-gray-100 rounded-lg transition-colors">
                                    <div class="text-left">
                                        <span class="text-sm font-medium text-gray-800">Autenticación de dos
                                            factores</span>
                                        <p class="text-xs text-gray-500">No activado</p>
                                    </div>
                                    <svg class="w-5 h-5 text-gray-500" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                                            clip-rule="evenodd" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Data Management -->
                    <div class="settings-card p-4 bg-white rounded-lg border border-gray-200">
                        <div class="flex justify-between items-center">
                            <div>
                                <h4 class="font-medium text-gray-800">Gestión de Datos</h4>
                                <p class="text-sm text-gray-600">Controla tu información</p>
                            </div>
                        </div>
                        <div class="mt-4 space-y-4">
                            <div>
                                <button onclick="exportAllData()"
                                    class="settings-button w-full flex justify-between items-center p-3 bg-gray-50 hover:bg-gray-100 rounded-lg transition-colors">
                                    <span class="text-sm font-medium text-gray-800">Exportar todos mis datos</span>
                                    <svg class="w-5 h-5 text-gray-500" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z"
                                            clip-rule="evenodd" />
                                    </svg>
                                </button>
                            </div>
                            <div>
                                <button onclick="showDeleteAccountModal()"
                                    class="settings-button w-full flex justify-between items-center p-3 bg-red-50 hover:bg-red-100 rounded-lg transition-colors">
                                    <span class="text-sm font-medium text-red-800">Eliminar mi cuenta</span>
                                    <svg class="w-5 h-5 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z"
                                            clip-rule="evenodd" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>
<!-- Change Password Modal -->
<div id="changePasswordModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
    <div class="modal-content bg-white rounded-2xl shadow-2xl p-6 w-full max-w-md">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-xl font-semibold text-gray-800">Cambiar contraseña</h3>
            <button id="hideChangePasswordModalBtn" class="text-gray-500 hover:text-gray-700">
                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd"
                        d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                        clip-rule="evenodd" />
                </svg>
            </button>
        </div>
        <form class="space-y-4" id="changePasswordForm">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Contraseña actual</label>
                <input type="password" name="current_password"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500"
                    required>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nueva contraseña</label>
                <input type="password" name="new_password"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500"
                    required>
                <p class="text-xs text-gray-500 mt-1">Mínimo 8 caracteres, incluyendo números y símbolos</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Confirmar nueva contraseña</label>
                <input type="password" name="confirm_password"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500"
                    required>
            </div>
            <div class="mt-6 flex flex-col sm:flex-row justify-end space-y-2 sm:space-y-0 sm:space-x-3">
                <button type="button" id="cancelChangePasswordBtn"
                    class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 font-medium">Cancelar</button>
                <button type="submit"
                    class="px-4 py-2 bg-indigo-600 text-white rounded-lg font-medium hover:bg-indigo-700">Actualizar</button>
            </div>
        </form>
    </div>
</div>

<!-- Two Factor Auth Modal -->
<div id="twoFactorModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
    <div class="modal-content bg-white rounded-2xl shadow-2xl p-6 w-full max-w-md">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-xl font-semibold text-gray-800">Autenticación de dos factores</h3>
            <button onclick="hideTwoFactorModal()" class="text-gray-500 hover:text-gray-700">
                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd"
                        d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                        clip-rule="evenodd" />
                </svg>
            </button>
        </div>
        <div class="space-y-4">
            <div class="p-4 bg-blue-50 rounded-lg">
                <h4 class="font-medium text-blue-800 mb-2">¿Qué es la autenticación de dos factores?</h4>
                <p class="text-sm text-blue-700">Añade una capa extra de seguridad a tu cuenta. Necesitarás ingresar
                    un código único además de tu contraseña.</p>
            </div>
            <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                <div>
                    <p class="font-medium text-gray-800">Autenticación por aplicación</p>
                    <p class="text-sm text-gray-600">Usa una app como Google Authenticator</p>
                </div>
                <label class="switch">
                    <input type="checkbox">
                    <span class="slider"></span>
                </label>
            </div>
            <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                <div>
                    <p class="font-medium text-gray-800">Autenticación por SMS</p>
                    <p class="text-sm text-gray-600">Recibe códigos por mensaje de texto</p>
                </div>
                <label class="switch">
                    <input type="checkbox">
                    <span class="slider"></span>
                </label>
            </div>
        </div>
        <div class="mt-6 flex justify-end">
            <button onclick="hideTwoFactorModal()"
                class="px-4 py-2 bg-indigo-600 text-white rounded-lg font-medium hover:bg-indigo-700">Guardar</button>
        </div>
    </div>
</div>
<!-- Delete Account Modal -->
<div id="deleteAccountModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
    <div class="modal-content bg-white rounded-2xl shadow-2xl p-6 w-full max-w-md">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-xl font-semibold text-gray-800">Eliminar mi cuenta</h3>
            <button onclick="hideDeleteAccountModal()" class="text-gray-500 hover:text-gray-700">
                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd"
                        d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                        clip-rule="evenodd" />
                </svg>
            </button>
        </div>
        <div class="space-y-4">
            <div class="p-4 bg-red-50 rounded-lg">
                <h4 class="font-medium text-red-800 mb-2">¡Advertencia! Esta acción no se puede deshacer</h4>
                <p class="text-sm text-red-700">Todos tus datos serán eliminados permanentemente, incluyendo
                    transacciones, presupuestos y reportes.</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Por favor, escribe "ELIMINAR" para
                    confirmar</label>
                <input type="text" id="deleteConfirmation"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-red-500 focus:border-red-500">
            </div>
            <div class="flex items-start">
                <input type="checkbox" id="understandDelete" class="mt-1 mr-2">
                <label for="understandDelete" class="text-sm text-gray-700">Entiendo que todos mis datos serán
                    eliminados permanentemente y no podrán recuperarse.</label>
            </div>
        </div>
        <div class="mt-6 flex flex-col sm:flex-row justify-end space-y-2 sm:space-y-0 sm:space-x-3">
            <button onclick="hideDeleteAccountModal()"
                class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 font-medium">Cancelar</button>
            <button onclick="confirmAccountDeletion()"
                class="px-4 py-2 bg-red-600 text-white rounded-lg font-medium hover:bg-red-700 disabled:bg-red-300 disabled:cursor-not-allowed"
                id="deleteAccountBtn" disabled>Eliminar cuenta</button>
        </div>
    </div>
</div>
<!-- Contenedor para las notificaciones dinámicas -->
<div id="notification-container" class="fixed top-5 right-5 z-50 space-y-3">
    <!-- Aqui aparecerán las notificaciones -->
</div>

<?php 
        require_once '../models/templates/footer.php';
    ?>
<script src="../controllers/configuracion.js"></script>