
// Handle social login
function handleSocialLogin(provider) {
  Swal.fire({
    title: `Iniciando sesión con ${provider}`,
    text: "Por favor espera...",
    icon: "info",
    showConfirmButton: false,
    timer: 2000,
    timerProgressBar: true,
  });

  // Aquí integrarías con las APIs de social login
  console.log(`Social login with ${provider}`);
}



function showNotification(message, type = 'info') {
    const container = document.getElementById('notification-container');
    if (!container) {
        console.error('El contenedor de notificaciones #notification-container no se encontró en el DOM.');
        return;
    }

    // 1. Definir estilos y el icono según el tipo de notificación
    const styles = {
        success: {
            icon: `<svg class="w-6 h-6 text-green-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>`,
            bg: 'bg-white',
            border: 'border-green-400',
            text: 'text-green-700'
        },
        error: {
            icon: `<svg class="w-6 h-6 text-red-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path></svg>`,
            bg: 'bg-white',
            border: 'border-red-400',
            text: 'text-red-700'
        },
        info: {
            icon: `<svg class="w-6 h-6 text-blue-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path></svg>`,
            bg: 'bg-white',
            border: 'border-blue-400',
            text: 'text-blue-700'
        }
    };
    const style = styles[type] || styles.info;

    // 2. Crear el elemento de la notificación
    const notificationEl = document.createElement('div');
    notificationEl.className = `w-80 max-w-sm ${style.bg} rounded-xl shadow-lg border-l-4 ${style.border} p-4 transform transition-all duration-300 opacity-0 translate-x-10`;
    notificationEl.innerHTML = `
        <div class="flex items-start">
            <div class="flex-shrink-0">${style.icon}</div>
            <div class="ml-3 w-0 flex-1 pt-0.5">
                <p class="text-sm font-medium text-slate-800">Notificación</p>
                <p class="mt-1 text-sm ${style.text}">${message}</p>
            </div>
            <div class="ml-4 flex-shrink-0 flex">
                <button class="close-notification-btn inline-flex text-slate-400 hover:text-slate-600">
                    <span class="sr-only">Cerrar</span>
                    <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path d="M6.28 5.22a.75.75 0 00-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 101.06 1.06L10 11.06l3.72 3.72a.75.75 0 101.06-1.06L11.06 10l3.72-3.72a.75.75 0 00-1.06-1.06L10 8.94 6.28 5.22z"></path></svg>
                </button>
            </div>
        </div>
    `;

    // 3. Añadirla al contenedor y animar la entrada
    container.appendChild(notificationEl);
    setTimeout(() => {
        notificationEl.classList.remove('opacity-0', 'translate-x-10');
    }, 10);

    // 4. Función para eliminar la notificación
    const removeNotification = () => {
        notificationEl.classList.add('opacity-0', 'translate-x-10');
        setTimeout(() => notificationEl.remove(), 300);
    };

    // 5. Hacer que desaparezca sola después de 5 segundos
    const autoRemoveTimeout = setTimeout(removeNotification, 5000);

    // 6. Permitir que el usuario la cierre haciendo clic en la 'X'
    notificationEl.querySelector('.close-notification-btn').addEventListener('click', () => {
        clearTimeout(autoRemoveTimeout);
        removeNotification();
    });
}




// Document ready
$(document).ready(function () {
  // Event listener para el botón de login
  $("#loginBtn").on("click", function (e) {
    e.preventDefault();
    iniciarSesion();
  });

  // Event listener para el formulario
  $("#loginForm").on("submit", function (e) {
    e.preventDefault();
    iniciarSesion();
  });

  // Enter key support para navegación entre campos
  $("#correo").on("keypress", function (e) {
    if (e.which === 13) {
      // Enter key
      $("#password").focus();
    }
  });

  $("#password").on("keypress", function (e) {
    if (e.which === 13) {
      // Enter key
      iniciarSesion();
    }
  });

      // --- Seleccionar elementos del DOM ---
    const loginForm = document.getElementById('loginForm');
    const forgotPasswordForm = document.getElementById('forgotPasswordForm');
    const resetPasswordForm = document.getElementById('resetPasswordForm');
    
    const forgotPasswordLink = document.getElementById('forgotPasswordLink');
    const backToLoginLink = document.getElementById('backToLoginLink');
    const resetEmailInput = document.getElementById('resetEmail');

    // --- Función para cambiar entre paneles ---
    function showPanel(panelToShow) {
        // Ocultar todos los paneles primero
        loginForm.classList.add('hidden');
        forgotPasswordForm.classList.add('hidden');
        resetPasswordForm.classList.add('hidden');
        
        // Mostrar solo el panel deseado
        if (panelToShow) {
            panelToShow.classList.remove('hidden');
        }
    }

    // --- Lógica de Eventos ---

    // Cuando el usuario hace clic en "¿Olvidaste tu contraseña?"
    if (forgotPasswordLink) {
        forgotPasswordLink.addEventListener('click', (e) => {
            e.preventDefault();
            showPanel(forgotPasswordForm);
        });
    }

    // Cuando el usuario hace clic en "Volver a Iniciar Sesión"
    if (backToLoginLink) {
        backToLoginLink.addEventListener('click', (e) => {
            e.preventDefault();
            showPanel(loginForm);
        });
    }

    // --- Lógica de Envío de Formularios ---

    // 1. Formulario de Login
    if (loginForm) {
        loginForm.addEventListener('submit', (e) => {
            e.preventDefault();
            console.log('Enviando formulario de login...');
            // Aquí iría tu lógica actual para iniciar sesión con fetch()
            // Por ejemplo: iniciarSesion();
        });
    }

    // 2. Formulario de "Olvidé mi contraseña"
    if (forgotPasswordForm) {
        forgotPasswordForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const submitBtn = forgotPasswordForm.querySelector('button[type="submit"]');

            const email = (resetEmailInput?.value || '').trim();
            if (!email) {
                showNotification('Por favor, ingresa tu correo electrónico.', 'error');
                resetEmailInput?.focus();
                return;
            }

            submitBtn.disabled = true;
            submitBtn.textContent = 'Enviando...';

            try {
                const resp = await fetch('/models/solicitar_restablecimiento.php', { // Asegúrate que esta ruta es correcta
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: new URLSearchParams({ email })
                });

                const data = await resp.json();

                if (data.success) {
                    showNotification(data.message, 'success');
                    // Opcional: Ocultar el formulario de olvido y mostrar un mensaje de "revisa tu correo"
                } else {
                    showNotification(data.message || 'Ocurrió un error desconocido.', 'error');
                }

            } catch (err) {
                console.error('❌ Error en fetch:', err);
                showNotification('No se pudo conectar con el servidor. Inténtalo de nuevo.', 'error');
            } finally {
                submitBtn.disabled = false;
                submitBtn.textContent = 'Enviar Enlace';
            }
        });
    }
    
    // 3. Formulario para Restablecer la Contraseña (cuando el usuario viene del email)
    if (resetPasswordForm) {
        resetPasswordForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            // Aquí iría la lógica para enviar el token y la nueva contraseña al backend
            console.log('Enviando formulario para establecer nueva contraseña...');
        });
    }

    // --- Lógica para manejar el token en la URL ---
    const urlParams = new URLSearchParams(window.location.search);
    const token = urlParams.get('token');

    if (token) {
        // Si la URL contiene un token, mostramos el panel para restablecer la contraseña
        const resetTokenInput = document.getElementById('resetToken');
        if (resetTokenInput) {
            resetTokenInput.value = token;
        }
        showPanel(resetPasswordForm);
    }

    async function iniciarSesion() {
    const usuario = $("#usuario").val().trim();
    const password = $("#password").val().trim();
    const recordar = $("#recordar").is(":checked");

    if (!usuario || !password) {
    
        await showAlertModal({
            title: "Campos requeridos",
            text: "Por favor, completa todos los campos.",
            icon: "warning",
            confirmButtonText: "Entendido",
        });
        return;
    }

    const submitBtn = $("#loginBtn");
    const btnText = submitBtn.find("#buttonText");
    const loadingSpinner = $("#loadingSpinner");
    const originalText = btnText.html();

    submitBtn.prop("disabled", true);
    btnText.html("Ingresando...");
    loadingSpinner.removeClass("hidden");

    $.ajax({
        url: "../models/login.php",
        type: "POST",
        dataType: "json",
        data: { usuario, password, recordar },
        success: async function (data) { // <-- 'async' aquí también
            if (data.success) {
                window.location.href = "../views/dashboard2.php";
            } else {
                // REEMPLAZO 2
                await showAlertModal({
                    title: "Error de autenticación",
                    text: data.message || "Credenciales incorrectas.",
                    icon: "error",
                    confirmButtonText: "Intentar de nuevo",
                });
            }
        },
        error: async function (xhr, status, error) { // <-- 'async' aquí
            console.error("Error AJAX:", error);
            // REEMPLAZO 3
            await showAlertModal({
                title: "Error de conexión",
                text: "No se pudo contactar con el servidor. Intenta nuevamente.",
                icon: "error",
                confirmButtonText: "Reintentar",
            });
        },
        complete: function () {
            submitBtn.prop("disabled", false);
            btnText.html(originalText);
            loadingSpinner.addClass("hidden");
        },
    });
}

async function showAlertModal(options) {
    // Seleccionar elementos del DOM del modal
    const modal = document.getElementById('alertModal');
    const modalBox = document.getElementById('alertModalBox');
    const titleEl = document.getElementById('alertModalTitle');
    const messageEl = document.getElementById('alertModalMessage');
    const confirmBtn = document.getElementById('alertModalConfirmBtn');
    const iconContainer = document.getElementById('alertModalIconContainer');

    // Configuración por defecto
    const config = {
        title: 'Notificación',
        text: '',
        icon: 'info',
        confirmButtonText: 'Entendido',
        ...options
    };

    // Estilos y SVG para cada tipo de icono
    const styles = {
        success: {
            icon: `<svg class="h-8 w-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>`,
            iconBg: 'bg-green-100',
            btnColor: 'bg-green-600 hover:bg-green-700'
        },
        error: {
            icon: `<svg class="h-8 w-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>`,
            iconBg: 'bg-red-100',
            btnColor: 'bg-red-600 hover:bg-red-700'
        },
        warning: {
            icon: `<svg class="h-8 w-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>`,
            iconBg: 'bg-yellow-100',
            btnColor: 'bg-yellow-600 hover:bg-yellow-700'
        },
        info: {
            icon: `<svg class="h-8 w-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>`,
            iconBg: 'bg-blue-100',
            btnColor: 'bg-blue-600 hover:bg-blue-700'
        }
    };
    const currentStyle = styles[config.icon] || styles.info;

    // Aplicar configuración y estilos al modal
    titleEl.textContent = config.title;
    messageEl.textContent = config.text;
    confirmBtn.textContent = config.confirmButtonText;
    iconContainer.innerHTML = currentStyle.icon;
    iconContainer.className = `mx-auto flex h-16 w-16 items-center justify-center rounded-full ${currentStyle.iconBg}`;
    confirmBtn.className = `w-full justify-center rounded-lg px-4 py-3 font-medium text-white focus:outline-none ${currentStyle.btnColor}`;

    // Mostrar el modal con animación
    modal.classList.remove('hidden');
    setTimeout(() => {
        modal.classList.remove('opacity-0');
        modalBox.classList.remove('scale-95');
    }, 10);

    // Devolver una promesa que se resuelve cuando el usuario hace clic en el botón
    return new Promise((resolve) => {
        const close = () => {
            modal.classList.add('opacity-0');
            modalBox.classList.add('scale-95');
            setTimeout(() => {
                modal.classList.add('hidden');
                confirmBtn.removeEventListener('click', closeAndResolve);
                resolve(); // Resolver la promesa
            }, 300);
        };
        
        const closeAndResolve = () => close();
        confirmBtn.addEventListener('click', closeAndResolve, { once: true });
    });
}

});
