<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Cuenta - OrbisPay</title>
    <!-- Preload de Tailwind -->
    <link href="../assets/css/output.css" rel="stylesheet">
    <style>
    .error {
        border-color: #ef4444 !important;
    }

    .success {
        border-color: #10b981 !important;
    }

    .checking {
        border-color: #6b7280 !important;
    }

    .exists {
        border-color: #ef4444 !important;
    }

    .spin {
        animation: spin 1s linear infinite;
    }

    @keyframes spin {
        from {
            transform: rotate(0deg);
        }

        to {
            transform: rotate(360deg);
        }
    }

    .field-validation-message {
        transition: all 0.3s ease;
        font-size: 0.875rem;
        margin-top: 0.25rem;
        padding: 0.25rem 0;
    }

    .password-strength.weak {
        color: #ef4444;
    }

    .password-strength.medium {
        color: #f59e0b;
    }

    .password-strength.strong {
        color: #10b981;
    }

    .error-message {
        background-color: #fef2f2;
        color: #ef4444;
        padding: 0.75rem;
        border-radius: 0.5rem;
        margin-bottom: 1rem;
        border: 1px solid #fecaca;
    }

    .success-message {
        background-color: #f0fdf4;
        color: #10b981;
        padding: 0.75rem;
        border-radius: 0.5rem;
        margin-bottom: 1rem;
        border: 1px solid #bbf7d0;
    }

    .input-field {
        transition: border-color 0.3s ease;
    }

    /* Animaciones para modales y notificaciones */
    .modal-fade-in {
        animation: fadeIn 0.3s ease-out;
    }

    .notification-slide-in {
        animation: slideIn 0.3s ease-out;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
        }

        to {
            opacity: 1;
        }
    }

    @keyframes slideIn {
        from {
            transform: translateX(100%);
            opacity: 0;
        }

        to {
            transform: translateX(0);
            opacity: 1;
        }
    }

    @keyframes confettiFall {
        to {
            transform: translateY(100vh) rotate(360deg);
            opacity: 0;
        }
    }
    </style>
</head>

<body class="bg-slate-50">
    <!-- Modal de éxito (oculto inicialmente) -->
    <div id="successModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center z-50 hidden">
        <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-md modal-fade-in">
            <div class="text-center">
                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-green-100">
                    <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                </div>
                <h3 class="mt-4 text-lg font-medium text-gray-900">¡Cuenta creada!</h3>
                <div class="mt-2">
                    <p class="text-sm text-gray-500">Tu usuario ha sido registrado exitosamente.</p>
                </div>
                <div class="mt-6">
                    <button id="modalConfirmButton" type="button"
                        class="inline-flex justify-center px-4 py-2 text-sm font-medium text-white bg-primary-600 border border-transparent rounded-md hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                        Ir al Login
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Contenedor de notificaciones -->
    <div id="notificationContainer" class="fixed top-4 right-4 z-50 max-w-sm w-full"></div>

    <div class="flex items-center justify-center min-h-screen px-4 py-12">
        <div class="w-full max-w-2xl">
            <!-- Logo y Encabezado -->
            <div class="text-center mb-8">
                <!-- Marca OrbisPay (OP monograma) -->
                <a href="index.php"
                    class="flex items-center space-x-3 mb-8  inline-flex items-center justify-center mb-4 text-2xl font-semibold text-slate-900"
                    aria-label="OrbisPay">
                    <!-- Isotipo OP -->
                    <svg class="w-10 h-10 rounded-xl shadow-md" viewBox="0 0 32 32" role="img" aria-hidden="true">
                        <defs>
                            <linearGradient id="opGrad" x1="0" y1="0" x2="1" y2="1">
                                <stop offset="0" stop-color="#4F46E5" /> <!-- indigo-600 -->
                                <stop offset="1" stop-color="#7C3AED" /> <!-- purple-600 -->
                            </linearGradient>
                        </defs>
                        <!-- Fondo -->
                        <rect x="0" y="0" width="32" height="32" rx="8" fill="url(#opGrad)" />
                        <!-- Monograma O + P en negativo -->
                        <g fill="#fff">
                            <!-- O como anillo -->
                            <path fill-rule="evenodd"
                                d="M16 6a10 10 0 1 1 0 20 10 10 0 0 1 0-20zm0 4a6 6 0 1 0 0 12 6 6 0 0 0 0-12z" />
                            <!-- P (asta) -->
                            <rect x="14.5" y="10" width="3" height="12" rx="1.5" />
                            <!-- P (panza) -->
                            <path d="M17 10.5h2.8a4.2 4.2 0 0 1 0 8.4H17v-3h2.6a1.2 1.2 0 0 0 0-2.4H17z" />
                        </g>
                    </svg>

                    <!-- Logotipo -->
                    <span class="text-2xl font-bold tracking-tight text-slate-900 dark">
                        Orbis<span class="text-indigo-600">Pay</span>
                    </span>
                </a>
                <h1 class="text-3xl font-bold text-slate-900">Crea tu cuenta</h1>
                <p class="text-slate-600 mt-2">Completa el formulario para unirte a OrbisPay.</p>
            </div>

            <!-- Card de Registro -->
            <div class="bg-white border border-slate-200 rounded-2xl shadow-lg p-6 sm:p-8">
                <!-- Barra de Progreso Mejorada -->
                <div class="mb-6">
                    <div class="flex justify-between items-center mb-2">
                        <span class="text-sm font-medium text-slate-700">Progreso</span>
                        <span id="progressText" class="text-sm font-medium text-slate-500">0/7 campos</span>
                    </div>
                    <div class="w-full bg-slate-200 rounded-full h-2">
                        <div id="progressFill" class="bg-primary-600 h-2 rounded-full transition-all duration-300"
                            style="width: 0%;"></div>
                    </div>
                </div>
                <!-- Messages Container -->
                <div id="messageContainer"></div>
                <form id="registerForm" class="space-y-4">
                    <!-- Nombre y Apellido -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="nombre" class="block mb-2 text-sm font-medium text-slate-700">Nombre</label>
                            <input type="text" id="nombre" name="nombre" placeholder="Tu nombre" required
                                class="input-field w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-slate-900 placeholder:text-slate-400 focus:outline-none focus:ring-4 focus:ring-primary-200 focus:border-primary-500">
                        </div>
                        <div>
                            <label for="apellido" class="block mb-2 text-sm font-medium text-slate-700">Apellido</label>
                            <input type="text" id="apellido" name="apellido" placeholder="Tu apellido" required
                                class="input-field w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-slate-900 placeholder:text-slate-400 focus:outline-none focus:ring-4 focus:ring-primary-200 focus:border-primary-500">
                        </div>
                    </div>

                    <!-- Usuario y Correo -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="usuario" class="block mb-2 text-sm font-medium text-slate-700">Nombre de
                                usuario</label>
                            <input type="text" id="usuario" name="usuario" placeholder="ej: juanperez" required
                                class="input-field w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-slate-900 placeholder:text-slate-400 focus:outline-none focus:ring-4 focus:ring-primary-200 focus:border-primary-500">
                        </div>
                        <div>
                            <label for="correo" class="block mb-2 text-sm font-medium text-slate-700">Correo
                                electrónico</label>
                            <input type="email" id="correo" name="correo" placeholder="nombre@dominio.com" required
                                class="input-field w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-slate-900 placeholder:text-slate-400 focus:outline-none focus:ring-4 focus:ring-primary-200 focus:border-primary-500">
                        </div>
                    </div>
                    <!-- Teléfono y Fecha de Nacimiento -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="telefono" class="block mb-2 text-sm font-medium text-slate-700">Teléfono
                                (Opcional)</label>
                            <input type="tel" id="telefono" name="telefono" placeholder="+57 123 456 7890"
                                class="input-field w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-slate-900 placeholder:text-slate-400 focus:outline-none focus:ring-4 focus:ring-primary-200 focus:border-primary-500">
                        </div>
                        <div>
                            <label for="fecha_nacimiento" class="block mb-2 text-sm font-medium text-slate-700">Fecha de
                                nacimiento</label>
                            <input type="date" id="fecha_nacimiento" name="fecha_nacimiento" required
                                class="input-field w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-slate-900 placeholder:text-slate-400 focus:outline-none focus:ring-4 focus:ring-primary-200 focus:border-primary-500">
                        </div>
                    </div>

                    <!-- Contraseña -->
                    <div>
                        <label for="password" class="block mb-2 text-sm font-medium text-slate-700">Contraseña</label>
                        <div class="relative">
                            <input type="password" id="password" name="password" placeholder="••••••••" required
                                class="input-field w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-slate-900 placeholder:text-slate-400 focus:outline-none focus:ring-4 focus:ring-primary-200 focus:border-primary-500">
                            <button type="button" onclick="togglePassword('password')"
                                class="absolute inset-y-0 right-0 flex items-center px-3 text-slate-500 hover:text-primary-600">
                                <svg id="eye-icon-password" class="h-5 w-5" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                                <svg id="eye-off-icon-password" class="h-5 w-5 hidden" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7 .946-2.933 3.38-5.236 6.19-6.333m7.688 6.333a10.02 10.02 0 01-3.364 4.49M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M3 3l18 18" />
                                </svg>
                            </button>
                        </div>
                        <div id="passwordStrength" class="mt-2 flex items-center gap-2 text-sm"></div>
                    </div>

                    <!-- Confirmar Contraseña -->
                    <div>
                        <label for="confirm_password" class="block mb-2 text-sm font-medium text-slate-700">Confirmar
                            contraseña</label>
                        <div class="relative">
                            <input type="password" id="confirm_password" name="confirm_password" placeholder="••••••••"
                                required
                                class="input-field w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-slate-900 placeholder:text-slate-400 focus:outline-none focus:ring-4 focus:ring-primary-200 focus:border-primary-500">
                            <button type="button" onclick="togglePassword('confirm_password')"
                                class="absolute inset-y-0 right-0 flex items-center px-3 text-slate-500 hover:text-primary-600">
                                <svg id="eye-icon-confirm_password" class="h-5 w-5" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                                <svg id="eye-off-icon-confirm_password" class="h-5 w-5 hidden" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7 .946-2.933 3.38-5.236 6.19-6.333m7.688 6.333a10.02 10.02 0 01-3.364 4.49M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M3 3l18 18" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Términos y Botón -->
                    <div class="pt-2 space-y-4">
                        <div class="flex items-start">
                            <input id="terminos" name="terminos" type="checkbox" required
                                class="h-4 w-4 rounded border-slate-300 text-primary-600 focus:ring-primary-500 mt-0.5">
                            <label for="terminos" class="ml-3 text-sm text-slate-600">
                                Acepto los <a href="#" class="font-medium text-primary-600 hover:underline">Términos de
                                    Servicio</a> y la <a href="#"
                                    class="font-medium text-primary-600 hover:underline">Política de Privacidad</a>.
                            </label>
                        </div>
                        <button type="submit" id="registerBtn"
                            class="w-full rounded-lg bg-primary-600 hover:bg-primary-700 text-white font-semibold px-5 py-3 shadow-sm focus:outline-none focus:ring-4 focus:ring-primary-200 flex items-center justify-center">
                            <span id="buttonText">Crear cuenta</span>
                            <svg id="loadingSpinner" class="w-5 h-5 ml-2 animate-spin hidden" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                        </button>
                    </div>
                </form>
            </div>

            <!-- Enlace para Iniciar Sesión -->
            <div class="text-center mt-6">
                <p class="text-sm text-slate-600">
                    ¿Ya tienes una cuenta?
                    <a href="index.php" class="font-medium text-primary-600 hover:underline">Inicia sesión aquí</a>
                </p>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
    // Configuración de la aplicación
    const API_ENDPOINT = "../models/registro_usuarios.php";
    const VALIDATION_ENDPOINT = "../models/validar_usuario.php";
    const LOGIN_URL = "../views/index.php";

    // Variables para control de validación
    let isEmailValid = true;
    let isUsernameValid = true;
    let validationTimeout = null;

    // Inicialización
    document.addEventListener("DOMContentLoaded", function() {
        initializeEventListeners();
        updateProgress();

        // Enfocar el primer campo
        const firstInput = document.getElementById("nombre");
        if (firstInput) {
            firstInput.focus();
        }

        // Configurar evento para el botón del modal
        document.getElementById('modalConfirmButton').addEventListener('click', function() {
            window.location.href = LOGIN_URL;
        });
    });

    // Inicializar event listeners
    function initializeEventListeners() {
        const form = document.getElementById("registerForm");
        const inputs = form.querySelectorAll(".input-field");

        // Eventos para campos de entrada
        inputs.forEach(input => {
            input.addEventListener("input", function() {
                validateField(this);
                updateProgress();
            });

            input.addEventListener("blur", function() {
                validateField(this);

                // Validación específica para usuario y correo
                if (this.id === "usuario" || this.id === "correo") {
                    validateUserExists(this);
                }
            });
        });

        // Eventos específicos para campos
        document.getElementById("password").addEventListener("input", function() {
            checkPasswordStrength(this.value);
            if (document.getElementById("confirm_password").value) {
                validatePasswordMatch();
            }
        });

        document.getElementById("confirm_password").addEventListener("input", validatePasswordMatch);
        document.getElementById("terminos").addEventListener("change", updateProgress);

        // Eventos de formato
        document.getElementById("usuario").addEventListener("input", function() {
            this.value = this.value.toLowerCase().replace(/\s/g, "");
        });

        document.getElementById("usuario").addEventListener("paste", function(e) {
            setTimeout(() => {
                this.value = this.value.toLowerCase().replace(/\s/g, "");
            }, 10);
        });

        document.getElementById("correo").addEventListener("blur", function() {
            this.value = this.value.toLowerCase().trim();
        });

        document.getElementById("nombre").addEventListener("blur", function() {
            this.value = this.value.replace(/\b\w/g, l => l.toUpperCase());
        });

        document.getElementById("apellido").addEventListener("blur", function() {
            this.value = this.value.replace(/\b\w/g, l => l.toUpperCase());
        });

        // Evento de envío del formulario
        form.addEventListener("submit", handleFormSubmit);

        // Navegación con teclado
        document.addEventListener("keydown", function(e) {
            if (e.key === "Enter" && e.target.tagName !== "BUTTON" && e.target.type !== "checkbox") {
                const inputs = form.querySelectorAll('input:not([type="checkbox"])');
                const currentIndex = Array.from(inputs).indexOf(e.target);

                if (currentIndex < inputs.length - 1) {
                    inputs[currentIndex + 1].focus();
                } else {
                    form.querySelector('button[type="submit"]').click();
                }
            }
        });
    }

    // Función para validar si usuario o correo ya existen
    function validateUserExists(field) {
        const value = field.value.trim();
        if (!value) return;

        // Debounce para evitar múltiples peticiones
        clearTimeout(validationTimeout);
        validationTimeout = setTimeout(() => {
            checkUserExists(field, value);
        }, 500);
    }

    // Función para verificar en el servidor si usuario/correo existe
    function checkUserExists(field, value) {
        const fieldType = field.id; // 'usuario' o 'correo'

        // Mostrar indicador de carga
        showFieldValidation(field, "checking");

        $.ajax({
            url: VALIDATION_ENDPOINT,
            method: "POST",
            dataType: "json",
            data: {
                [fieldType]: value,
                action: "check_exists"
            },
            success: function(response) {
                if (response.success === false) {
                    // El usuario/correo ya existe
                    showFieldValidation(field, "exists", response.message);

                    if (fieldType === "usuario") {
                        isUsernameValid = false;
                    } else if (fieldType === "correo") {
                        isEmailValid = false;
                    }
                } else {
                    // El usuario/correo está disponible
                    showFieldValidation(field, "available");

                    if (fieldType === "usuario") {
                        isUsernameValid = true;
                    } else if (fieldType === "correo") {
                        isEmailValid = true;
                    }
                }
                updateProgress();
            },
            error: function(xhr, status, error) {
                console.error("Error validando usuario:", error);
                showFieldValidation(field, "error");

                // En caso de error, permitir continuar
                if (fieldType === "usuario") {
                    isUsernameValid = true;
                } else if (fieldType === "correo") {
                    isEmailValid = true;
                }
            }
        });
    }

    // Función para mostrar el estado de validación del campo
    function showFieldValidation(field, status, message = "") {
        // Remover clases previas
        field.classList.remove("error", "success", "checking", "exists");

        // Buscar o crear el contenedor de mensaje
        let messageDiv = field.parentNode.querySelector(".field-validation-message");
        if (!messageDiv) {
            messageDiv = document.createElement("div");
            messageDiv.className = "field-validation-message";
            field.parentNode.appendChild(messageDiv);
        }

        switch (status) {
            case "checking":
                field.classList.add("checking");
                messageDiv.innerHTML = `<span style="color: #6b7280;">Verificando...</span>`;
                break;

            case "exists":
                field.classList.add("exists");
                messageDiv.innerHTML = `<span style="color: #ef4444;">${message}</span>`;
                break;

            case "available":
                field.classList.add("success");
                messageDiv.innerHTML = `<span style="color: #10b981;">Disponible</span>`;
                break;

            case "error":
                messageDiv.innerHTML = `<span style="color: #f59e0b;">Error al verificar</span>`;
                break;

            default:
                messageDiv.innerHTML = "";
        }
    }

    // Función para validar campos individuales
    function validateField(field) {
        const value = field.value.trim();
        let isValid = true;

        // Remover clases previas (excepto checking y exists)
        if (!field.classList.contains("checking") && !field.classList.contains("exists")) {
            field.classList.remove("error", "success");
        }

        switch (field.type) {
            case "email":
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                isValid = emailRegex.test(value);
                break;
            case "tel":
                if (value) {
                    const phoneRegex = /^[\+]?[0-9\s\-\(\)]{10,}$/;
                    isValid = phoneRegex.test(value);
                }
                break;
            case "password":
                isValid = value.length >= 8;
                break;
            case "date":
                if (value) {
                    const birthDate = new Date(value);
                    const today = new Date();
                    const age = today.getFullYear() - birthDate.getFullYear();
                    isValid = age >= 18 && age <= 100;
                }
                break;
            default:
                isValid = value.length >= 2;
        }

        // Validación específica para el campo de usuario
        if (field.id === "usuario") {
            const userRegex = /^[a-zA-Z0-9_]{3,20}$/;
            isValid = userRegex.test(value);
        }

        // Aplicar clase según validación (solo si no está en proceso de verificación)
        if (!field.classList.contains("checking") && !field.classList.contains("exists")) {
            if (value && isValid) {
                field.classList.add("success");
            } else if (value && !isValid) {
                field.classList.add("error");
            }
        }

        return isValid;
    }

    // Función para validar el formulario completo
    function validateForm() {
        const form = document.getElementById("registerForm");
        const inputs = form.querySelectorAll(".input-field");
        const termsCheckbox = document.getElementById("terminos");
        let isValid = true;
        let errors = [];

        // Validar campos individuales
        inputs.forEach(input => {
            if (!validateField(input) && input.value.trim()) {
                isValid = false;
                errors.push(`${input.placeholder || input.name} no es válido`);
            }
        });

        // Validar que usuario y correo estén disponibles
        if (!isUsernameValid) {
            isValid = false;
            errors.push("El nombre de usuario ya está registrado");
        }

        if (!isEmailValid) {
            isValid = false;
            errors.push("El correo electrónico ya está registrado");
        }

        // Validar coincidencia de contraseñas
        if (!validatePasswordMatch()) {
            isValid = false;
            errors.push("Las contraseñas no coinciden");
        }

        // Validar términos y condiciones
        if (!termsCheckbox.checked) {
            isValid = false;
            errors.push("Debes aceptar los términos y condiciones");
        }

        if (!isValid) {
            showMessage(errors.join("<br>"), "error");
        }

        return isValid;
    }

    // Función para validar coincidencia de contraseñas
    function validatePasswordMatch() {
        const password = document.getElementById("password").value;
        const confirmPassword = document.getElementById("confirm_password").value;
        const confirmField = document.getElementById("confirm_password");

        confirmField.classList.remove("error", "success");

        if (confirmPassword) {
            if (password === confirmPassword) {
                confirmField.classList.add("success");
                return true;
            } else {
                confirmField.classList.add("error");
                return false;
            }
        }
        return false;
    }

    // Password strength checker
    function checkPasswordStrength(password) {
        const strengthIndicator = document.getElementById("passwordStrength");
        let strength = 0;
        let strengthText = "";

        if (password.length >= 8) strength++;
        if (/[a-z]/.test(password)) strength++;
        if (/[A-Z]/.test(password)) strength++;
        if (/[0-9]/.test(password)) strength++;
        if (/[^A-Za-z0-9]/.test(password)) strength++;

        switch (strength) {
            case 0:
            case 1:
            case 2:
                strengthText = "Débil";
                strengthIndicator.className = "password-strength weak";
                break;
            case 3:
            case 4:
                strengthText = "Media";
                strengthIndicator.className = "password-strength medium";
                break;
            case 5:
                strengthText = "Fuerte";
                strengthIndicator.className = "password-strength strong";
                break;
        }

        strengthIndicator.textContent = password ? `Contraseña: ${strengthText}` : "";
        return strength;
    }

    // Función para actualizar la barra de progreso
    function updateProgress() {
        const form = document.getElementById("registerForm");
        const inputs = form.querySelectorAll(".input-field");
        const termsCheckbox = document.getElementById("terminos");
        let completedFields = 0;

        inputs.forEach(input => {
            if (input.value.trim() &&
                !input.classList.contains("error") &&
                !input.classList.contains("exists")) {
                completedFields++;
            }
        });

        if (termsCheckbox.checked) {
            completedFields++;
        }

        const progress = (completedFields / (inputs.length + 1)) * 100;
        document.getElementById("progressFill").style.width = `${progress}%`;
        document.getElementById("progressText").textContent = `${completedFields}/${inputs.length + 1} campos`;
    }

    // Función para mostrar mensajes
    function showMessage(message, type = "error") {
        const messageContainer = document.getElementById("messageContainer");
        const messageClass = type === "error" ? "error-message" : "success-message";

        messageContainer.innerHTML = `
            <div class="${messageClass}">
                ${message}
            </div>
        `;

        // Auto-hide message after 5 seconds
        setTimeout(() => {
            messageContainer.innerHTML = "";
        }, 5000);
    }

    // Función para mostrar notificaciones (reemplaza a showNotification)
    function showNotification(message, type = "info") {
        const notificationContainer = document.getElementById("notificationContainer");
        const notificationId = "notification-" + Date.now();

        let bgColor, icon;
        switch (type) {
            case "error":
                bgColor = "bg-red-100 border-red-400 text-red-700";
                icon = `<svg class="h-5 w-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>`;
                break;
            case "success":
                bgColor = "bg-green-100 border-green-400 text-green-700";
                icon = `<svg class="h-5 w-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>`;
                break;
            case "warning":
                bgColor = "bg-yellow-100 border-yellow-400 text-yellow-700";
                icon = `<svg class="h-5 w-5 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                </svg>`;
                break;
            default:
                bgColor = "bg-blue-100 border-blue-400 text-blue-700";
                icon = `<svg class="h-5 w-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>`;
        }

        const notification = document.createElement("div");
        notification.id = notificationId;
        notification.className =
            `${bgColor} border px-4 py-3 rounded relative mb-2 notification-slide-in flex items-start`;
        notification.innerHTML = `
            <div class="mr-2 mt-0.5">${icon}</div>
            <div class="flex-1">${message}</div>
            <button onclick="document.getElementById('${notificationId}').remove()" class="absolute top-0 bottom-0 right-0 px-4 py-3">
                <svg class="h-4 w-4 text-current" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        `;

        notificationContainer.appendChild(notification);

        // Auto-remove notification after 5 seconds
        setTimeout(() => {
            if (document.getElementById(notificationId)) {
                document.getElementById(notificationId).remove();
            }
        }, 5000);
    }

    // Función para mostrar modal de éxito (reemplaza a showSuccessModal)
    function showSuccessModal() {
        const modal = document.getElementById("successModal");
        modal.classList.remove("hidden");

        // Deshabilitar el botón de enviar formulario mientras el modal está visible
        document.getElementById("registerBtn").disabled = true;
    }

    // Función para obtener datos del formulario
    function getFormData() {
        const form = document.getElementById("registerForm");
        const formData = new FormData(form);
        const data = {};

        for (let [key, value] of formData.entries()) {
            data[key] = value;
        }

        return data;
    }

    // Función para manejar el envío del formulario
    function handleFormSubmit(e) {
        e.preventDefault();

        if (!validateForm()) return;

        const registerBtn = document.getElementById("registerBtn");
        const buttonText = document.getElementById("buttonText");
        const loadingSpinner = document.getElementById("loadingSpinner");

        // Cambiar estado del botón
        registerBtn.disabled = true;
        buttonText.textContent = "Creando cuenta...";
        loadingSpinner.classList.remove("hidden");

        // Recopilar datos del formulario
        const userData = getFormData();

        // Enviar al servidor vía AJAX
        $.ajax({
            url: API_ENDPOINT,
            method: "POST",
            dataType: "json",
            data: userData,
            success: function(response) {
                // Restaurar estado del botón
                registerBtn.disabled = false;
                buttonText.textContent = "Crear cuenta";
                loadingSpinner.classList.add("hidden");

                // Verificar si la respuesta es exitosa
                if (response.success === true || !response.error) {
                    showSuccessModal();
                    form.reset();
                    updateProgress();
                } else {
                    const errorMessage = response.message || response.mensaje || "Error en el registro";
                    showMessage(errorMessage, "error");
                    showNotification(errorMessage, "error");
                }
            },
            error: function(xhr, status, error) {
                console.error("Error AJAX:", status, error);

                // Restaurar estado del botón
                registerBtn.disabled = false;
                buttonText.textContent = "Crear cuenta";
                loadingSpinner.classList.add("hidden");

                const errorMessage = "Error de conexión. Intenta nuevamente.";
                showMessage(errorMessage, "error");
                showNotification(errorMessage, "error");
            }
        });
    }

    // Función para alternar visibilidad de contraseña
    function togglePassword(fieldId) {
        const field = document.getElementById(fieldId);
        const eyeIcon = document.getElementById(`eye-icon-${fieldId}`);
        const eyeOffIcon = document.getElementById(`eye-off-icon-${fieldId}`);

        if (field.type === "password") {
            field.type = "text";
            eyeIcon.classList.add("hidden");
            eyeOffIcon.classList.remove("hidden");
        } else {
            field.type = "password";
            eyeIcon.classList.remove("hidden");
            eyeOffIcon.classList.add("hidden");
        }
    }

    // Función para login social (placeholder)
    function handleSocialLogin(provider) {
        showNotification(`Funcionalidad de ${provider} próximamente...`, "info");
    }
    </script>
</body>

</html>