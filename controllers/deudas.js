document.addEventListener("DOMContentLoaded", function () {
  console.log("deudas.js cargado y listo.");

  // --- SELECCIÓN DE ELEMENTOS DEL DOM ---
  const deudasContainer = document.getElementById("deudas-container");
  const prestamosContainer = document.getElementById("prestamos-container");

  // Modal para crear nuevo registro
  const newDebtModal = document.getElementById("newDebtModal");
  const openNewDebtModalBtn = document.getElementById("openNewDebtModalBtn");
  const closeNewDebtModalBtn = document.getElementById("closeNewDebtModalBtn");
  const cancelNewDebtBtn = document.getElementById("cancelNewDebtBtn");
  const newDebtForm = document.getElementById("newDebtForm");

  // Modal para añadir un abono
  const addPaymentModal = document.getElementById("addPaymentModal");
  const closeAddPaymentModalBtn = document.getElementById(
    "closeAddPaymentModalBtn"
  );
  const cancelPaymentBtn = document.getElementById("cancelPaymentBtn");
  const addPaymentForm = document.getElementById("addPaymentForm");

  const historyModal = document.getElementById("historyModal");
  const closeHistoryModalBtn = document.getElementById("closeHistoryModalBtn");
  const historyModalTitle = document.getElementById("historyModalTitle");
  const historyModalBody = document.getElementById("historyModalBody");

  // Modal para editar un registro
  const editDebtModal = document.getElementById("editDebtModal");
  const closeEditDebtModalBtn = document.getElementById(
    "closeEditDebtModalBtn"
  );
  const cancelEditDebtBtn = document.getElementById("cancelEditDebtBtn");
  const editDebtForm = document.getElementById("editDebtForm");

  // --- Lógica para manejar el modal de Edición ---
  function showEditDebtModal() {
    if (editDebtModal) editDebtModal.classList.remove("hidden");
  }
  function hideEditDebtModal() {
    if (editDebtModal) editDebtModal.classList.add("hidden");
  }

  if (closeEditDebtModalBtn)
    closeEditDebtModalBtn.addEventListener("click", hideEditDebtModal);
  if (cancelEditDebtBtn)
    cancelEditDebtBtn.addEventListener("click", hideEditDebtModal);
  if (editDebtModal)
    editDebtModal.addEventListener("click", (e) => {
      if (e.target === editDebtModal) hideEditDebtModal();
    });

  // Al cargar la página, obtenemos y mostramos los datos.
  fetchAndRenderDebts();

  // =================================================================
  // --- 1. OBTENER Y RENDERIZAR DATOS ---
  // =================================================================

  async function fetchAndRenderDebts() {
    // Mostramos un estado de carga mientras se obtienen los datos.
    if (deudasContainer)
      deudasContainer.innerHTML =
        '<p class="text-center text-gray-400 py-4">Cargando deudas...</p>';
    if (prestamosContainer)
      prestamosContainer.innerHTML =
        '<p class="text-center text-gray-400 py-4">Cargando préstamos...</p>';

    try {
      const response = await fetch(
        "../models/api_deudas.php?action=obtener_deudas_prestamos"
      );
      if (!response.ok) throw new Error("Error de red al obtener los datos.");

      const result = await response.json();

      if (result.success) {
        renderItems(result.data.deudas, deudasContainer, "Deuda");
        renderItems(result.data.prestamos, prestamosContainer, "Préstamo");
      } else {
        throw new Error(result.message || "Error al procesar la solicitud.");
      }
    } catch (error) {
      console.error("Error en fetchAndRenderDebts:", error);
      const errorMessage = `<p class="text-center text-red-500 py-4">Error al cargar: ${error.message}</p>`;
      if (deudasContainer) deudasContainer.innerHTML = errorMessage;
      if (prestamosContainer) prestamosContainer.innerHTML = errorMessage;
    }
  }

  function renderItems(items, container, type) {
    container.innerHTML = ""; // Limpiamos el contenedor
    if (items.length > 0) {
      items.forEach((item) => {
        const cardHTML = createCardHTML(item, type);
        container.insertAdjacentHTML("beforeend", cardHTML);
      });
    } else {
      container.innerHTML = `<p class="text-center text-gray-500 dark:text-gray-400 py-4">No tienes ${type.toLowerCase()}s activos.</p>`;
    }
  }

  function createCardHTML(item, type) {
    const isDeuda = type === "Deuda";

    // Paleta de colores refinada
    const theme = {
      deuda: {
        borderColor: "border-red-500",
        textColor: "text-red-600",
        progressGradient: "from-red-400 to-red-600",
        statusColor: "bg-red-100 text-red-800",
      },
      prestamo: {
        borderColor: "border-green-500",
        textColor: "text-green-600",
        progressGradient: "from-green-400 to-green-600",
        statusColor: "bg-green-100 text-green-800",
      },
    };
    const currentTheme = isDeuda ? theme.deuda : theme.prestamo;

    const progressPercentage =
      item.monto_inicial > 0
        ? ((item.monto_inicial - item.saldo_actual) / item.monto_inicial) * 100
        : 0;
    const formattedSaldo = new Intl.NumberFormat("es-CO", {
      style: "currency",
      currency: "COP",
      minimumFractionDigits: 0,
    }).format(item.saldo_actual);

    // --- INICIO DE LA PLANTILLA HTML FINAL Y CORREGIDA ---
    return `
        <div class="bg-white rounded-xl shadow-lg p-4 border-l-4 ${
          currentTheme.borderColor
        } flex flex-col h-full transition-all duration-300 hover:shadow-2xl hover:scale-[1.02]">
            
            <!-- 1. SECCIÓN SUPERIOR: Título, Menú y Estado -->
            <div class="flex justify-between items-start mb-2">
                <div class="flex-1 min-w-0">
                    <p class="font-bold text-gray-800 truncate" title="${
                      item.descripcion
                    }">${item.descripcion}</p>
                    <p class="text-xs text-gray-500">${
                      isDeuda ? "A:" : "De:"
                    } ${item.acreedor_deudor || "N/A"}</p>
                </div>
                <div class="flex items-center space-x-1 flex-shrink-0 ml-2">
                    <span class="text-xs font-semibold ${
                      currentTheme.statusColor
                    } px-2 py-0.5 rounded-full">${item.estado}</span>
                    <div class="relative">
                        <button class="debt-menu-btn p-1 rounded-full hover:bg-gray-200 transition-colors">
                            <svg class="w-5 h-5 text-gray-500" fill="currentColor" viewBox="0 0 20 20"><path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z"></path></svg>
                        </button>
                        <div class="debt-menu-dropdown absolute right-0 mt-2 w-40 bg-white rounded-md shadow-lg z-10 hidden">
                            <a href="#" class="edit-debt-btn block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" data-id="${
                              item.id_deuda
                            }">✏️ Editar</a>
                            <a href="#" class="delete-debt-btn block px-4 py-2 text-sm text-red-600 hover:bg-gray-100" data-id="${
                              item.id_deuda
                            }" data-descripcion="${item.descripcion}">🗑️ Eliminar</a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 2. SECCIÓN MEDIA: Saldo (ocupa el espacio principal) -->
            <div class="flex-grow flex flex-col justify-center my-3">
                <p class="text-sm text-gray-600">${
                  isDeuda ? "Saldo Pendiente" : "Saldo por Cobrar"
                }</p>
                <p class="text-3xl font-extrabold ${
                  currentTheme.textColor
                }">${formattedSaldo}</p>
            </div>

            <!-- 3. SECCIÓN DE PROGRESO (AHORA SEPARADA) -->
            <div class="w-full mt-auto pt-3">
                <div class="w-full bg-gray-200 rounded-full h-2.5">
                    <div class="bg-gradient-to-r ${
                      currentTheme.progressGradient
                    } h-2.5 rounded-full transition-all duration-500 ease-out" style="width: ${progressPercentage.toFixed(2)}%;"></div>
                </div>
            </div>

            <!-- 4. SECCIÓN DE ACCIONES (BOTONES) -->
            <div class="w-full pt-3">
                <div class="flex justify-between items-center">
                    <button class="view-history-btn text-sm text-gray-600 hover:underline" data-id="${
                      item.id_deuda
                    }">Ver Historial</button>
                    <button class="add-payment-btn text-sm font-semibold bg-indigo-600 text-white px-4 py-1.5 rounded-lg hover:bg-indigo-700 transition-colors shadow-md" data-id="${
                      item.id_deuda
                    }">
                        ${isDeuda ? "Hacer Abono" : "Registrar Pago"}
                    </button>
                </div>
            </div>
        </div>
    `;
  }

  // =================================================================
  //  LÓGICA PARA MANEJAR MODALES
  // =================================================================

  // --- Modal de Nuevo Registro ---
  function showNewDebtModal() {
    if (newDebtModal) newDebtModal.classList.remove("hidden");
  }
  function hideNewDebtModal() {
    if (newDebtModal) newDebtModal.classList.add("hidden");
  }

  if (openNewDebtModalBtn)
    openNewDebtModalBtn.addEventListener("click", showNewDebtModal);
  if (closeNewDebtModalBtn)
    closeNewDebtModalBtn.addEventListener("click", hideNewDebtModal);
  if (cancelNewDebtBtn)
    cancelNewDebtBtn.addEventListener("click", hideNewDebtModal);
  if (newDebtModal)
    newDebtModal.addEventListener("click", (e) => {
      if (e.target === newDebtModal) hideNewDebtModal();
    });

  // --- Modal de Añadir Abono ---
  function showAddPaymentModal() {
    if (addPaymentModal) addPaymentModal.classList.remove("hidden");
  }
  function hideAddPaymentModal() {
    if (addPaymentModal) addPaymentModal.classList.add("hidden");
  }

  if (closeAddPaymentModalBtn)
    closeAddPaymentModalBtn.addEventListener("click", hideAddPaymentModal);
  if (cancelPaymentBtn)
    cancelPaymentBtn.addEventListener("click", hideAddPaymentModal);
  if (addPaymentModal)
    addPaymentModal.addEventListener("click", (e) => {
      if (e.target === addPaymentModal) hideAddPaymentModal();
    });

  function showHistoryModal() {
    if (historyModal) historyModal.classList.remove("hidden");
  }
  function hideHistoryModal() {
    if (historyModal) historyModal.classList.add("hidden");
  }

  if (closeHistoryModalBtn)
    closeHistoryModalBtn.addEventListener("click", hideHistoryModal);
  if (historyModal)
    historyModal.addEventListener("click", (e) => {
      if (e.target === historyModal) hideHistoryModal();
    });

  // =================================================================
  // LÓGICA DE ENVÍO DE FORMULARIOS
  // =================================================================

  // --- Formulario para Crear Nuevo Registro ---
  if (newDebtForm) {
    newDebtForm.addEventListener("submit", async function (event) {
      event.preventDefault();
      const formData = new FormData(newDebtForm);
      const data = Object.fromEntries(formData.entries());
      data.action = "crear_deuda_prestamo";

      try {
        const response = await fetch("../models/api_deudas.php", {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify(data),
        });

        const result = await response.json();

        if (result.success) {
          showSuccessMessage("¡Registro creado con éxito!");
          newDebtForm.reset();
          hideNewDebtModal();
          fetchAndRenderDebts(); // Refrescamos la lista para mostrar el nuevo ítem
        } else {
          throw new Error(result.message);
        }
      } catch (error) {
        console.error("Error al crear el registro:", error);
        showErrorMessage("Error.");
      }
    });
  }

  // =================================================================
  //  4. LÓGICA PARA REGISTRAR ABONOS
  // =================================================================

  // Usamos delegación de eventos para manejar los clics en los botones de las tarjetas
  document.body.addEventListener("click", function (event) {
    if (event.target.classList.contains("add-payment-btn")) {
      // Leemos el ID directamente del botón en el que se hizo clic.
      const idDeuda = event.target.dataset.id;

      // Guardamos el ID en el campo oculto del formulario de abono
      const idDeudaInput = addPaymentForm.querySelector(
        'input[name="id_deuda"]'
      );
      idDeudaInput.value = idDeuda;

      // Ponemos la fecha de hoy por defecto
      const fechaAbonoInput = addPaymentForm.querySelector(
        'input[name="fecha_abono"]'
      );
      fechaAbonoInput.valueAsDate = new Date();

      // Mostramos el modal
      showAddPaymentModal();
    }
  });

  // Manejo del envío del formulario de abono
  if (addPaymentForm) {
    addPaymentForm.addEventListener("submit", async function (event) {
      event.preventDefault();
      const formData = new FormData(addPaymentForm);
      const data = Object.fromEntries(formData.entries());
      data.action = "registrar_abono";

      // Deshabilitar el botón para evitar envíos múltiples
      const submitButton = addPaymentForm.querySelector(
        'button[type="submit"]'
      );
      submitButton.disabled = true;
      submitButton.textContent = "Procesando...";

      try {
        const response = await fetch("../models/api_deudas.php", {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify(data),
        });
        const result = await response.json();
        if (result.success) {
          hideAddPaymentModal();
          addPaymentForm.reset();
          if (result.nuevo_estado === "Pagada") {
            // 1. Seleccionar y mostrar el modal de celebración
            const celebrationModal =
              document.getElementById("celebration-modal");
            const celebrationModalBox = document.getElementById(
              "celebration-modal-box"
            );
            celebrationModal.classList.remove("hidden");

            // Pequeño delay para que la animación de entrada funcione
            setTimeout(() => {
              celebrationModalBox.classList.remove("scale-95", "opacity-0");
            }, 10);

            // 2. ¡Lanzar el confeti!
            launchConfetti();

            // 3. Asignar evento al botón de cierre del modal de celebración
            document.getElementById("celebration-close-btn").addEventListener(
              "click",
              () => {
                celebrationModalBox.classList.add("scale-95", "opacity-0");
                setTimeout(() => {
                  celebrationModal.classList.add("hidden");
                  fetchAndRenderDebts(); // Refrescar la lista DESPUÉS de cerrar el modal
                }, 300);
              },
              { once: true }
            ); // {once: true} es una buena práctica para que el listener se auto-elimine
          } else {
            // Si no se pagó por completo, solo mostramos una notificación simple y refrescamos
            showSuccessMessage("¡Abono registrado con éxito!");
            fetchAndRenderDebts();
          }
        } else {
          throw new Error(result.message);
        }
      } catch (error) {
        console.error("Error al registrar el abono:", error);
        showErrorMessage("Error.");
      } finally {
        // Volver a habilitar el botón
        submitButton.disabled = false;
        submitButton.textContent = "Confirmar Abono";
      }
    });
  }

  document.body.addEventListener("click", async function (event) {
    // Verificamos si el clic fue en un botón de "Ver Historial"
    if (event.target.classList.contains("view-history-btn")) {
      const idDeuda = event.target.dataset.id;

      // Mostramos un estado de carga en el modal
      historyModalBody.innerHTML =
        '<p class="text-center text-gray-400 py-4">Cargando historial...</p>';
      historyModalTitle.textContent = "";
      showHistoryModal();

      try {
        const response = await fetch(
          `../models/api_deudas.php?action=obtener_historial_abonos&id_deuda=${idDeuda}`
        );
        if (!response.ok) throw new Error("Error de red.");

        const result = await response.json();

        if (result.success) {
          // Actualizamos el título del modal
          historyModalTitle.textContent = result.descripcion_deuda;

          // Renderizamos el historial
          renderHistory(result.historial);
        } else {
          throw new Error(result.message);
        }
      } catch (error) {
        historyModalBody.innerHTML = `<p class="text-center text-red-500 py-4">${error.message}</p>`;
      }
    }
  });

  function renderHistory(historial) {
    historyModalBody.innerHTML = ""; // Limpiamos el cuerpo del modal

    if (historial.length === 0) {
      historyModalBody.innerHTML =
        '<p class="text-center text-gray-500 py-4">No se han registrado abonos para este ítem.</p>';
      return;
    }

    const listContainer = document.createElement("div");
    listContainer.className = "space-y-3";

    historial.forEach((abono) => {
      const formattedMonto = new Intl.NumberFormat("es-CO", {
        style: "currency",
        currency: "COP",
        minimumFractionDigits: 0,
      }).format(abono.monto_abono);
      const formattedFecha = new Date(
        abono.fecha_abono + "T00:00:00"
      ).toLocaleDateString("es-CO", {
        year: "numeric",
        month: "long",
        day: "numeric",
      });

      const abonoHTML = `
                    <div class="flex justify-between items-center bg-gray-50 dark:bg-gray-700/50 p-3 rounded-lg">
                        <div>
                            <p class="font-medium text-gray-800 dark:text-gray-200">${formattedMonto}</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">${formattedFecha}</p>
                        </div>
                        <!-- Podríamos añadir un botón para eliminar el abono en el futuro -->
                    </div>
                `;
      listContainer.insertAdjacentHTML("beforeend", abonoHTML);
    });

    historyModalBody.appendChild(listContainer);
  }

  function launchConfetti() {
    const canvas = document.getElementById("celebration-canvas");
    // Nos aseguramos de que el canvas y la librería confetti existan
    if (!canvas || typeof confetti === "undefined") {
      console.warn("Canvas de confeti o la librería no encontrados.");
      return;
    }

    // Creamos una instancia de confeti asociada a nuestro canvas
    const myConfetti = confetti.create(canvas, {
      resize: true, // El confeti se ajustará si la ventana cambia de tamaño
      useWorker: true, // Usa un worker para no bloquear el hilo principal (mejor rendimiento)
    });

    // Disparamos la animación
    myConfetti({
      particleCount: 150, // Cantidad de confeti
      spread: 100, // Qué tan ancho es el ángulo de dispersión
      origin: { y: 0.6 }, // Desde dónde sale el confeti (0.6 es un poco más arriba del centro)
    });
  }
  document.body.addEventListener("click", function (event) {
    const menuBtn = event.target.closest(".debt-menu-btn");
    if (menuBtn) {
      event.preventDefault();
      const dropdown = menuBtn.nextElementSibling;
      closeAllDropdowns(dropdown);
      dropdown.classList.toggle("hidden");
    } else {
      closeAllDropdowns();
    }
  });

  document.body.addEventListener("click", async function (event) {
    const editBtn = event.target.closest(".edit-debt-btn");
    const deleteBtn = event.target.closest(".delete-debt-btn");

    // --- Lógica para EDITAR ---
    if (editBtn) {
      event.preventDefault();
      const idDeuda = editBtn.dataset.id;

      try {
        const response = await fetch(
          `../models/api_deudas.php?action=obtener_deuda_para_editar&id_deuda=${idDeuda}`
        );
        const result = await response.json();

        if (result.success) {
          // Rellenamos el formulario de edición con los datos obtenidos
          document.getElementById("edit_id_deuda").value = result.data.id_deuda;
          document.getElementById("edit_tipo").value = result.data.tipo;
          document.getElementById("edit_descripcion").value =
            result.data.descripcion;
          document.getElementById("edit_acreedor_deudor").value =
            result.data.acreedor_deudor;
          document.getElementById("edit_monto_inicial").value =
            result.data.monto_inicial;
          document.getElementById("edit_fecha_creacion").value =
            result.data.fecha_creacion;

          showEditDebtModal(); // Mostramos el modal
        } else {
          throw new Error(result.message);
        }
      } catch (error) {
        showErrorMessage("Error.");
      }
    }

    // --- Lógica para ELIMINAR ---
    if (deleteBtn) {
      event.preventDefault();
      const idDeuda = deleteBtn.dataset.id;
      const descripcion = deleteBtn.dataset.descripcion;

      // Llamamos a nuestro modal personalizado y esperamos la respuesta
      const confirmed = await showConfirmationModal({
        title: "Eliminar Registro",
        message: `¿Estás seguro de que quieres eliminar "${descripcion}"? Esta acción es permanente y no se puede deshacer.`,
        confirmText: "Sí, Eliminar",
        icon: "danger",
      });

      // Si el usuario confirmó...
      if (confirmed) {
        try {
          const response = await fetch("../models/api_deudas.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({
              action: "eliminar_deuda",
              id_deuda: idDeuda,
            }),
          });
          const result = await response.json();

          if (result.success) {
            // Usamos la notificación de éxito
            showSuccessMessage("¡Registro Eliminado Correctamente!");
            fetchAndRenderDebts();
          } else {
            throw new Error(result.message);
          }
        } catch (error) {
          // Usamos la notificación de error
          showErrorMessage(error.message);
        }
      }
    }
  });

  if (editDebtForm) {
    editDebtForm.addEventListener("submit", async function (event) {
      event.preventDefault();
      const formData = new FormData(editDebtForm);
      const data = Object.fromEntries(formData.entries());
      data.action = "actualizar_deuda";
      try {
        const response = await fetch("../models/api_deudas.php", {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify(data),
        });
        const result = await response.json();

        if (result.success) {
          showSuccessMessage("¡Registro actualizado con Exito!");
          hideEditDebtModal();
          fetchAndRenderDebts();
        } else {
          throw new Error(result.message);
        }
      } catch (error) {
        showErrorMessage("Error al actualizar.");
      }
    });
  }

  function closeAllDropdowns(exceptThisOne = null) {
    const allDropdowns = document.querySelectorAll(".debt-menu-dropdown");
    allDropdowns.forEach((dropdown) => {
      if (dropdown !== exceptThisOne) {
        dropdown.classList.add("hidden");
      }
    });
  }

  async function showConfirmationModal(options = {}) {
    const modal = document.getElementById("confirmationModal");
    if (!modal) {
      console.error("El elemento #confirmationModal no se encontró en el DOM.");
      return Promise.resolve(false);
    }

    const titleEl = document.getElementById("confirmationModalTitle");
    const messageEl = document.getElementById("confirmationModalMessage");
    const confirmBtn = document.getElementById("confirmationModalConfirmBtn");
    const cancelBtn = document.getElementById("confirmationModalCancelBtn");
    const iconContainer = document.getElementById(
      "confirmationModalIconContainer"
    );

    const config = {
      title: "¿Estás seguro?",
      message: "Esta acción no se puede deshacer.",
      confirmText: "Confirmar",
      icon: "danger",
      ...options,
    };

    const themes = {
      danger: {
        iconSVG:
          '<svg class="h-8 w-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>',
        confirmClasses: "bg-red-600 hover:bg-red-700",
      },
      warning: {
        iconSVG:
          '<svg class="h-8 w-8 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h1m0-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>',
        confirmClasses: "bg-yellow-500 hover:bg-yellow-600",
      },
    };
    const theme = themes[config.icon] || themes.danger;

    titleEl.textContent = config.title;
    messageEl.textContent = config.message;
    confirmBtn.textContent = config.confirmText;
    iconContainer.innerHTML = theme.iconSVG;

    confirmBtn.className = confirmBtn.className
      .replace(/bg-\w+-\d+/g, "")
      .replace(/hover:bg-\w+-\d+/g, "");
    confirmBtn.classList.add(...theme.confirmClasses.split(" "));
    modal.classList.remove("hidden");

    return new Promise((resolve) => {
      const onConfirm = () => {
        cleanup();
        resolve(true);
      };
      const onCancel = () => {
        cleanup();
        resolve(false);
      };
      const cleanup = () => {
        modal.classList.add("hidden");
        confirmBtn.removeEventListener("click", onConfirm);
        cancelBtn.removeEventListener("click", onCancel);
      };
      confirmBtn.addEventListener("click", onConfirm, { once: true });
      cancelBtn.addEventListener("click", onCancel, { once: true });
    });
  }

  function showSuccessMessage(message, isAlert = false) {
    const bgColor = isAlert ? "bg-yellow-400" : "bg-green-500";
    const textColor = isAlert ? "text-yellow-900" : "text-white";
    const icon = isAlert
      ? `<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.21 3.03-1.742 3.03H4.42c-1.532 0-2.492-1.696-1.742-3.03l5.58-9.92zM10 13a1 1 0 110-2 1 1 0 010 2zm-1-8a1 1 0 00-1 1v3a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path></svg>`
      : `<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>`;

    const notification = document.createElement("div");
    notification.className = `fixed top-4 right-4 ${bgColor} ${textColor} px-6 py-4 rounded-xl shadow-lg z-50 transform translate-x-full transition-transform duration-500`;
    notification.innerHTML = `<div class="flex items-center space-x-3">${icon}<span>${message}</span></div>`;
    document.body.appendChild(notification);

    setTimeout(() => notification.classList.remove("translate-x-full"), 100);
    setTimeout(() => {
      notification.classList.add("translate-x-full");
      setTimeout(() => notification.remove(), 5000);
    }, 5000);
  }

  function showErrorMessage(message, element = null) {
    if (element) {
      element.classList.add("border-red-500", "ring-2", "ring-red-200");
      setTimeout(() => {
        element.classList.remove("border-red-500", "ring-2", "ring-red-200");
      }, 3000);
    }

    const notification = document.createElement("div");
    notification.className =
      "fixed top-4 right-4 bg-red-500 text-white px-6 py-4 rounded-xl shadow-lg z-50 transform translate-x-full transition-transform duration-300";
    notification.innerHTML = `
            <div class="flex items-center space-x-2">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                </svg>
                <span>${message}</span>
            </div>
        `;
    document.body.appendChild(notification);
    setTimeout(() => notification.classList.remove("translate-x-full"), 100);
    setTimeout(() => {
      notification.classList.add("translate-x-full");
      setTimeout(() => notification.remove(), 300);
    }, 3000);
  }
});
