// --- ELEMENTOS DEL MODAL ---
const modal = document.getElementById("custom-modal");
const modalContent = document.getElementById("modal-content");
const modalIconContainer = document.getElementById("modal-icon-container");
const modalTitle = document.getElementById("modal-title");
const modalMessage = document.getElementById("modal-message");
const modalButtons = document.getElementById("modal-buttons");

// Botón de cierre en la esquina (Agrégalo a tu HTML si no lo tienes, o genéralo con JS)
// Si no quieres tocar el HTML, podemos omitir este bloque, pero es buena práctica de UX.

// --- FUNCIÓN PARA MOSTRAR EL MODAL ---
function showModal() {
  if (!modal) return;
  modal.classList.remove("hidden");
  // Pequeña animación de entrada
  setTimeout(() => {
    modalContent.classList.remove("scale-95", "opacity-0");
    modalContent.classList.add("scale-100", "opacity-100");
  }, 10);
}

// --- FUNCIÓN PARA OCULTAR EL MODAL ---
function hideModal() {
  if (!modal) return;
  modalContent.classList.remove("scale-100", "opacity-100");
  modalContent.classList.add("scale-95", "opacity-0");
  setTimeout(() => {
    modal.classList.add("hidden");
  }, 300);
}

// --- ICONOS PREDEFINIDOS ---
const ICONS = {
  success: `
        <div class="bg-green-100 w-16 h-16 mx-auto flex items-center justify-center rounded-full">
            <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
        </div>`,
  warning: `
        <div class="bg-red-100 w-16 h-16 mx-auto flex items-center justify-center rounded-full">
            <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
        </div>`,
};

/**
 * Muestra el modal de éxito
 * @param {string} title - Título del modal
 * @param {string} message - Mensaje descriptivo
 * @param {function} onCloseCallback - (Opcional) Función a ejecutar al cerrar
 */
function showSuccessModal(title, message, onCloseCallback = null) {
  modalIconContainer.innerHTML = ICONS.success;
  modalTitle.textContent = title;
  modalMessage.textContent = message;

  // Botón mejorado
  modalButtons.innerHTML = `
        <button id="modal-success-continue" class="w-full sm:w-auto bg-green-600 text-white font-bold py-2 px-6 rounded-lg hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition-all">
            Continuar
        </button>
    `;

  showModal();

  const continueBtn = document.getElementById("modal-success-continue");

  // Manejador del clic
  continueBtn.onclick = () => {
    hideModal();
    if (onCloseCallback) {
      // Ejecutamos la función que nos pasaron (ej: recargar tabla)
      setTimeout(onCloseCallback, 300);
    }
  };

  // Focus en el botón para accesibilidad (permite dar Enter)
  continueBtn.focus();
}

function showConfirmationModal(title, message, onConfirm) {
  modalIconContainer.innerHTML = ICONS.warning;
  modalTitle.textContent = title;
  modalMessage.textContent = message;

  modalButtons.innerHTML = `
        <div class="flex justify-center space-x-3 w-full">
            <button id="modal-confirm-cancel" class="px-6 py-2 bg-white border border-gray-300 text-gray-700 font-semibold rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-200">
                Cancelar
            </button>
            <button id="modal-confirm-action" class="px-6 py-2 bg-red-600 text-white font-semibold rounded-lg hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">
                Sí, eliminar
            </button>
        </div>
    `;

  showModal();

  document.getElementById("modal-confirm-cancel").onclick = hideModal;

  document.getElementById("modal-confirm-action").onclick = () => {
    hideModal();
    if (onConfirm) onConfirm();
  };
}

// FUNCIÓN DE UTILIDAD: MOSTRAR ALERTA DE ERROR
function showFailureModal(title, message) {
  modalIconContainer.innerHTML = ICONS.warning;
  modalTitle.textContent = title;
  modalMessage.textContent = message;

  modalButtons.innerHTML = `
        <button id="modal-error-close" class="w-full sm:w-auto bg-red-600 text-white font-bold py-2 px-6 rounded-lg hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition-all">
            Cerrar
        </button>
    `;

  showModal();

  document.getElementById("modal-error-close").onclick = hideModal;
  document.getElementById("modal-error-close").focus();
}

function resetNewGoalModal() {
    console.log("Reseteando el modal de nueva meta a su estado por defecto.");

    if (newGoalForm) {
        newGoalForm.reset();
    }

    const goalIdInput = document.getElementById('id_meta');
    if (goalIdInput) {
        goalIdInput.value = '';
    }

    const modalTitle = document.getElementById('newGoalModalLabel');
    const submitButton = document.getElementById('submitGoalBtn');
    if (modalTitle) modalTitle.textContent = 'Crear Nueva Meta';
    if (submitButton) submitButton.textContent = 'Crear Meta';

    if (automaticSavingCheck) {
        automaticSavingCheck.checked = false;
        updateAutomaticSavingState(); 
    }


    if (flatpickrInstance) {
        flatpickrInstance.destroy();
        flatpickrInstance = null; // Importante para evitar errores
    }

    const errorDiv = document.getElementById('goalFormError');
    if (errorDiv) {
        errorDiv.classList.add('hidden');
        errorDiv.textContent = '';
    }
}
