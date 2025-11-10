document.addEventListener('DOMContentLoaded', () => {
    // --- 1. SELECTORES DEL DOM ---
    const profileForm = document.getElementById('profileForm');
    const nombreInput = document.getElementById('profileNombre');
    const apellidoInput = document.getElementById('profileApellido');
    const emailInput = document.getElementById('profileEmail');
    const telefonoInput = document.getElementById('profileTelefono');
    const monedaSelect = document.getElementById('profileMoneda');
    const avatarImg = document.getElementById('profileAvatarImg');
    // ... (puedes añadir más selectores para los otros campos si los tienes)


    // --- 2. FUNCIÓN PARA CARGAR DATOS DEL USUARIO ---
    async function loadUserData() {
        try {
            const response = await fetch('../models/configuracion_cuenta.php?action=getUserData');
            const result = await response.json();

          console.log(result.response);

            if (result.success) {
                const user = result.data;
                nombreInput.value = user.NOMBRE || '';
                apellidoInput.value = user.APELLIDO || '';
                emailInput.value = user.CORREO || '';
                telefonoInput.value = user.TELEFONO || ''; // Asumiendo que tienes esta columna
                monedaSelect.value = user.MONEDA_PREF || 'COP'; // Asumiendo que tienes esta columna
                if (user.AVATAR_URL) {
                    avatarImg.src = user.AVATAR_URL;
                }
            } else {
                showNotification(result.message, 'error');
            }
        } catch (error) {
            console.error('Error al cargar datos del usuario:', error);
            showNotification('No se pudieron cargar los datos del perfil.', 'error');
        }
    }

    // --- 3. FUNCIÓN PARA GUARDAR CAMBIOS ---
    profileForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const submitBtn = profileForm.querySelector('button[type="submit"]');
        const originalText = submitBtn.textContent;
        submitBtn.disabled = true;
        submitBtn.textContent = 'Guardando...';

        const formData = new FormData(profileForm);
        formData.append('action', 'updateUserData');

        try {
            const response = await fetch('../models/configuracion_cuenta.php', {
                method: 'POST',
                body: formData
            });
            const result = await response.json();

            if (result.success) {
                showNotification('Perfil actualizado exitosamente.', 'success');
            } else {
                showNotification(result.message, 'error');
            }
        } catch (error) {
            console.error('Error al guardar cambios:', error);
            showNotification('Error de conexión al guardar el perfil.', 'error');
        } finally {
            submitBtn.disabled = false;
            submitBtn.textContent = originalText;
        }
    });

    // --- INICIALIZACIÓN ---
    loadUserData();

    const changePasswordModal = document.getElementById('changePasswordModal');
    const changePasswordForm = document.getElementById('changePasswordForm');
    const showPasswordModalBtn = document.getElementById('showChangePasswordModalBtn');
    const hidePasswordModalBtn = document.getElementById('hideChangePasswordModalBtn');
    const cancelPasswordBtn = document.getElementById('cancelChangePasswordBtn');

    // --- 2. LISTENERS PARA MOSTRAR Y OCULTAR EL MODAL ---
    showPasswordModalBtn.addEventListener('click', () => {
        changePasswordModal.classList.remove('hidden');
    });

    hidePasswordModalBtn.addEventListener('click', () => {
        changePasswordModal.classList.add('hidden');
        changePasswordForm.reset(); // Limpiar el formulario al cerrar
    });

    cancelPasswordBtn.addEventListener('click', () => {
        changePasswordModal.classList.add('hidden');
        changePasswordForm.reset();
    });

    // --- 3. LISTENER PARA EL ENVÍO DEL FORMULARIO DE CAMBIO DE CONTRASEÑA ---
    changePasswordForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const submitBtn = changePasswordForm.querySelector('button[type="submit"]');
        const originalText = submitBtn.textContent;
        submitBtn.disabled = true;
        submitBtn.textContent = 'Actualizando...';

        const formData = new FormData(changePasswordForm);
        formData.append('action', 'updatePassword');

        // Validación simple en el frontend
        const newPassword = formData.get('new_password');
        const confirmPassword = formData.get('confirm_password');

        if (newPassword.length < 8) {
            showNotification('La nueva contraseña debe tener al menos 8 caracteres.', 'error');
            submitBtn.disabled = false;
            submitBtn.textContent = originalText;
            return;
        }

        if (newPassword !== confirmPassword) {
            showNotification('Las nuevas contraseñas no coinciden.', 'error');
            submitBtn.disabled = false;
            submitBtn.textContent = originalText;
            return;
        }


        try {
            const response = await fetch('../models/configuracion_cuenta.php', {
                method: 'POST',
                body: formData
            });
            const result = await response.json();

            if (result.success) {
                showNotification('Contraseña actualizada exitosamente.', 'success');
                changePasswordModal.classList.add('hidden');
                changePasswordForm.reset();
            } else {
                showNotification(result.message, 'error');
            }
        } catch (error) {
            console.error('Error al cambiar la contraseña:', error);
            showNotification('Error de conexión al cambiar la contraseña.', 'error');
        } finally {
            submitBtn.disabled = false;
            submitBtn.textContent = originalText;
        }
    });

    
});


    function showNotification(message, type = 'info') {
        // Busca el contenedor donde se añadirán las notificaciones.
        // (Asegúrate de tener <div id="notification-container" class="fixed ..."></div> en tu HTML)
        const container = document.getElementById('notification-container');
        if (!container) {
            console.error('El contenedor de notificaciones #notification-container no se encontró en el DOM.');
            return;
        }

        // 1. Definir estilos y el icono SVG para cada tipo de notificación
        const styles = {
            success: {
                icon: `<svg class="w-6 h-6 text-green-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>`,
                border: 'border-green-400',
                text: 'text-green-700'
            },
            error: {
                icon: `<svg class="w-6 h-6 text-red-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path></svg>`,
                border: 'border-red-400',
                text: 'text-red-700'
            },
            info: {
                icon: `<svg class="w-6 h-6 text-blue-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path></svg>`,
                border: 'border-blue-400',
                text: 'text-blue-700'
            }
        };
        const style = styles[type] || styles.info;

        // 2. Crear el elemento HTML de la notificación
        const notificationEl = document.createElement('div');
        notificationEl.className = `w-80 max-w-sm bg-white rounded-xl shadow-lg border-l-4 ${style.border} p-4 transform transition-all duration-300 opacity-0 translate-x-10`;
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

        // 3. Añadirla al DOM y animar su entrada
        container.appendChild(notificationEl);
        setTimeout(() => {
            notificationEl.classList.remove('opacity-0', 'translate-x-10');
        }, 10);

        // 4. Función para eliminar la notificación con animación
        const removeNotification = () => {
            notificationEl.classList.add('opacity-0', 'translate-x-10');
            setTimeout(() => notificationEl.remove(), 300);
        };

        // 5. Hacer que se elimine sola después de 5 segundos
        const autoRemoveTimeout = setTimeout(removeNotification, 5000);

        // 6. Permitir que el usuario la cierre manualmente
        notificationEl.querySelector('.close-notification-btn').addEventListener('click', () => {
            clearTimeout(autoRemoveTimeout); // Detener el auto-borrado
            removeNotification();
        });
    }

