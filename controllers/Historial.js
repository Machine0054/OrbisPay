document.addEventListener("DOMContentLoaded", () => {
  function showNotification(message, type = "success") {
    const notification = document.createElement("div");
    const isSuccess = type === "success";
    const bgColor = isSuccess ? "bg-green-500" : "bg-red-500";
    const iconPath = isSuccess
      ? "M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
      : "M10 18a8 8 0 100-16 8 8 0 000 16zm-1-9V7a1 1 0 112 0v2a1 1 0 01-2 0zm1 4a1.5 1.5 0 110-3 1.5 1.5 0 010 3z";
    notification.className = `fixed top-4 right-4 ${bgColor} text-white px-6 py-4 rounded-xl shadow-lg z-50 transform translate-x-full transition-transform duration-300`;
    notification.innerHTML = `<div class="flex items-center space-x-2"><svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="${iconPath}" clip-rule="evenodd" /></svg><span>${message}</span></div>`;
    document.body.appendChild(notification);
    setTimeout(() => notification.classList.remove("translate-x-full"), 100);
    setTimeout(() => {
      notification.classList.add("translate-x-full");
      setTimeout(() => notification.remove(), 300);
    }, 3000);
  }

  window.showNotification = showNotification;

  // --- 1. SELECTORES DEL DOM ---
  const filterDateRangeEl = document.getElementById("filterDateRange");
  const filterTypeEl = document.getElementById("filterType");
  const filterCategoryEl = document.getElementById("filterCategory");
  const filterSearchEl = document.getElementById("filterSearch");
  const tableBodyEl = document.getElementById("transactionsTableBody");
  const summaryBarEl = document.getElementById("summaryBar");
  const totalsBarEl = document.getElementById("totalsBar");
  const paginationContainerEl = document.getElementById("paginationContainer");

  // --- 2. ESTADO DE LA APLICACIÓN ---
  // Un objeto para mantener el estado actual de los filtros y la paginación.
  const state = {
    currentPage: 1,
    filters: {
      type: "todos",
      category: "todas",
      search: "",
      startDate: null,
      endDate: null,
    },
  };

  // --- 3. INICIALIZACIÓN ---
  // Inicializar el selector de rango de fechas con Litepicker
  const datepicker = new Litepicker({
    element: filterDateRangeEl,
    singleMode: false, // Permite seleccionar un rango
    format: "YYYY-MM-DD",
    setup: (picker) => {
      picker.on("selected", (date1, date2) => {
        state.filters.startDate = date1.format("YYYY-MM-DD");
        state.filters.endDate = date2.format("YYYY-MM-DD");
        fetchTransactions(); // Vuelve a cargar los datos con el nuevo rango
      });
    },
  });

  // Cargar categorías y luego las transacciones iniciales
  initializePage();

  // --- 4. EVENT LISTENERS PARA LOS FILTROS ---

  // Cuando se cambia el tipo (ingreso/gasto)
  filterTypeEl.addEventListener("change", () => {
    state.filters.type = filterTypeEl.value;
    state.currentPage = 1; // Resetea a la primera página
    fetchTransactions();
  });

  // Cuando se cambia la categoría
  filterCategoryEl.addEventListener("change", () => {
    console.log("Esta acá Jhon");
    state.filters.category = filterCategoryEl.value;
    state.currentPage = 1;
    fetchTransactions();
  });

  // Cuando el usuario escribe en la barra de búsqueda (con un pequeño retraso para no hacer peticiones en cada tecla)
  let searchTimeout;
  filterSearchEl.addEventListener("keyup", () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
      state.filters.search = filterSearchEl.value;
      state.currentPage = 1;
      fetchTransactions();
    }, 300); // Espera 300ms después de la última tecla presionada
  });

  // --- 5. FUNCIONES PRINCIPALES ---

  /**
   * Carga las categorías y luego la primera página de transacciones.
   */
  async function initializePage() {
    await loadCategoriesIntoFilter();
    await fetchTransactions();
  }

  /**
   * Carga las categorías desde la BD y las pone en el <select> de filtros.
   */
  async function loadCategoriesIntoFilter() {
    try {
      // Reutilizamos el endpoint que ya teníamos en presupuesto.php
      const response = await fetch(
        "../models/presupuesto.php?action=getCategories"
      );
      const result = await response.json();
      if (result.success) {
        filterCategoryEl.innerHTML = '<option value="todas">Todas</option>'; // Reset
        result.data.forEach((cat) => {
          //const option = new Option(cat.nombre_categoria, cat.id);
          const option = new Option(cat.nombre_categoria, cat.nombre_categoria);
          filterCategoryEl.add(option);
        });
      }
    } catch (error) {
      console.error("Error cargando categorías para el filtro:", error);
    }
  }

  /**
   * La función central: busca las transacciones basadas en el estado actual.
   */
  async function fetchTransactions() {
    showLoadingState();

    const params = new URLSearchParams({
      action: "getHistory",
      page: state.currentPage,
      limit: 10,
      ...state.filters,
    });

    try {
      const response = await fetch(
        `../models/Historial.php?${params.toString()}`
      );
      const result = await response.json();
      console.log(result);
      if (!result.success) throw new Error(result.message);

      renderTable(result.data.transactions);
      renderPagination(result.data.pagination);
      renderSummary(result.data.pagination);
      renderTotals(result.data.totals); // <-- Nueva llamada a la función de renderizado
    } catch (error) {
      console.error("Error al buscar transacciones:", error);
      tableBodyEl.innerHTML = `<tr><td colspan="5" class="px-6 py-4 text-center text-red-500">Error al cargar los datos: ${error.message}</td></tr>`;
      totalsBarEl.innerHTML = ""; // Limpiar totales en caso de error
    }
  }

  // --- 6. FUNCIONES DE RENDERIZADO ---

  /**
   * Muestra un estado de "cargando" en la tabla y el resumen.
   */
  function showLoadingState() {
    summaryBarEl.textContent = "Cargando resultados...";
    tableBodyEl.innerHTML = `<tr><td colspan="5" class="px-6 py-4 text-center text-gray-500">Aplicando filtros...</td></tr>`;
  }

  function renderTotals(totals) {
    const totalIngresos = parseFloat(totals.ingresos || 0);
    const totalGastos = parseFloat(totals.gastos || 0);
    const balance = totalIngresos - totalGastos;

    const formatCurrency = (value) => {
      const sign = value < 0 ? "-" : "";
      return `${sign}$${Math.abs(value).toLocaleString("es-CO", {
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
      })}`;
    };

    totalsBarEl.innerHTML = `
            <div>
                <span class="block text-sm font-medium text-gray-500">Total Ingresos</span>
                <span class="block text-xl font-bold text-green-600">${formatCurrency(
                  totalIngresos
                )}</span>
            </div>
            <div>
                <span class="block text-sm font-medium text-gray-500">Total Gastos</span>
                <span class="block text-xl font-bold text-red-600">${formatCurrency(
                  totalGastos
                )}</span>
            </div>
            <div>
                <span class="block text-sm font-medium text-gray-500">Balance</span>
                <span class="block text-xl font-bold ${
                  balance >= 0 ? "text-gray-800" : "text-red-700"
                }">${formatCurrency(balance)}</span>
            </div>
        `;
  }

  /**
   * Dibuja las filas de la tabla con los datos de las transacciones.
   */
  function renderTable(transactions) {
    tableBodyEl.innerHTML = "";
    if (transactions.length === 0) {
      tableBodyEl.innerHTML = `<tr><td colspan="5" class="px-6 py-4 text-center text-gray-500">No se encontraron resultados.</td></tr>`;
      return;
    }
    transactions.forEach((tx) => {
      const isGasto = tx.tipo === "gasto";
      const amountClass = isGasto ? "text-red-600" : "text-green-600";
      const amountPrefix = isGasto ? "-" : "+";

      // Formateo de texto
      const descripcionFormateada = tx.descripcion
        ? tx.descripcion.toUpperCase()
        : "SIN DESCRIPCIÓN";
      const categoriaFormateada = tx.categoria
        ? tx.categoria.charAt(0).toUpperCase() + tx.categoria.slice(1)
        : "Sin Categoría";
      //console.log('respuesta a categoria', categoriaFormateada);

      const row = `
            <tr class="hover:bg-gray-50">
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800">${
                  tx.fecha
                }</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800 font-medium">${descripcionFormateada}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${categoriaFormateada}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-right ${amountClass}">
                    ${amountPrefix}$${parseFloat(tx.monto).toLocaleString(
        "es-CO"
      )}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                    <!-- ================================================== -->
                    <!-- AÑADIMOS CLASES Y DATA-* -->
                    <!-- ================================================== -->
                    <button data-id="${tx.id}" data-type="${
        tx.tipo
      }" class="edit-btn text-indigo-600 hover:text-indigo-900" title="Editar">Editar</button>
                    <button data-id="${tx.id}" data-type="${
        tx.tipo
      }" class="delete-btn text-red-600 hover:text-red-900 ml-4" title="Eliminar">Eliminar</button>
                </td>
            </tr>`;
      tableBodyEl.insertAdjacentHTML("beforeend", row);
    });
  }

  /**
   * Dibuja la barra de resumen ("Mostrando 1 a 10 de 97...").
   */
  function renderSummary(pagination) {
    if (pagination.totalRows === 0) {
      summaryBarEl.textContent = "No se encontraron resultados.";
      return;
    }
    const from = (pagination.currentPage - 1) * pagination.limit + 1;
    const to = Math.min(from + pagination.limit - 1, pagination.totalRows);
    summaryBarEl.innerHTML = `Mostrando <span class="font-medium">${from}</span> a <span class="font-medium">${to}</span> de <span class="font-medium">${pagination.totalRows}</span> resultados`;
  }

  /**
   * Dibuja los controles de paginación.
   */
  function renderPagination(pagination) {
    // Lógica para generar los botones de paginación (simplificada por ahora)
    // Esta parte puede volverse muy compleja, por ahora solo ponemos Anterior/Siguiente
    const { currentPage, totalPages } = pagination;
    paginationContainerEl.querySelector("div.hidden").innerHTML = `
            <div>
                <p class="text-sm text-gray-700">
                    Página <span class="font-medium">${currentPage}</span> de <span class="font-medium">${totalPages}</span>
                </p>
            </div>
            <div>
                <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
                    <button ${currentPage === 1 ? "disabled" : ""} data-page="${
      currentPage - 1
    }" class="pagination-btn relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 disabled:opacity-50">
                        Anterior
                    </button>
                    <button ${
                      currentPage >= totalPages ? "disabled" : ""
                    } data-page="${
      currentPage + 1
    }" class="pagination-btn relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 disabled:opacity-50">
                        Siguiente
                    </button>
                </nav>
            </div>
        `;
  }

  // Listener para los botones de paginación (usando delegación de eventos)
  paginationContainerEl.addEventListener("click", (e) => {
    const button = e.target.closest(".pagination-btn");
    if (button && !button.disabled) {
      state.currentPage = parseInt(button.dataset.page);
      fetchTransactions();
    }
  });
  // --- Selectores para los modales ---
  const confirmationModal = document.getElementById("confirmationModal");
  const editModal = document.getElementById("editTransactionModal");
  const editForm = document.getElementById("editTransactionForm");
  const closeEditModalBtn = document.getElementById("closeEditModalBtn");
  const cancelEditBtn = document.getElementById("cancelEditBtn");

  // --- Listener principal para la tabla (Delegación de Eventos) ---
  tableBodyEl.addEventListener("click", (e) => {
    const target = e.target;
    const id = target.dataset.id;
    const type = target.dataset.type;

    if (target.classList.contains("delete-btn")) {
      handleDelete(id, type);
    }

    if (target.classList.contains("edit-btn")) {
      handleEdit(id, type);
    }
  });

  // --- Lógica de ELIMINAR ---

  /**
   * Maneja la lógica completa para eliminar una transacción, incluyendo la confirmación.
   */
  async function handleDelete(id, type) {
    const confirmed = await showConfirmationModal({
      title: "Eliminar Transacción",
      message:
        "¿Estás seguro de que quieres eliminar este movimiento? Esta acción no se puede deshacer.",
      confirmText: "Sí, Eliminar",
      icon: "danger",
    });

    if (!confirmed) return;

    try {
      const response = await fetch("../models/Historial.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: new URLSearchParams({ action: "delete", id, type }),
      });

      const result = await response.json();
      if (!result.success) throw new Error(result.message);

      showNotification("Transacción eliminada exitosamente.", "success");
      fetchTransactions(); // Recargar la tabla
    } catch (error) {
      console.error("Error al eliminar la transacción:", error);
      showNotification(`Error: ${error.message}`, "error");
    }
  }

  // --- Lógica de EDITAR ---

  /**
   * Inicia el proceso de edición: obtiene los datos y muestra el modal.
   */
  async function handleEdit(id, type) {
    // 1. ¿Qué datos estamos usando para empezar?
    console.log("Iniciando edición para:", { id, type });

    try {
      const params = new URLSearchParams({
        action: "getTransaction",
        id,
        type,
      });
      const url = `../models/Historial.php?${params.toString()}`;
      // 2. ¿La URL que vamos a llamar es correcta?

      const response = await fetch(url);
      // 3. ¿Qué respondió el servidor? (Antes de intentar convertir a JSON)
      const rawResponse = await response.text(); // Usamos .text() para ver la respuesta cruda
      // 4. Ahora intentamos convertir a JSON y vemos el objeto
      const result = JSON.parse(rawResponse); // Parseamos la respuesta cruda
      if (!result.success) throw new Error(result.message);
      populateAndShowEditModal(result.data);
    } catch (error) {
      console.error("Error en handleEdit:", error);
      showNotification(`Error: ${error.message}`, "error");
    }
  }

  /**
   * Rellena el formulario del modal de edición con los datos y lo muestra.
   */
  function populateAndShowEditModal(transactions) {
    // Poblar los campos del formulario
    document.getElementById("editTransactionId").value = transactions.id;
    document.getElementById("editTransactionType").value = transactions.tipo;
    document.getElementById("editDescription").value = transactions.descripcion;
    document.getElementById("editAmount").value = transactions.monto;
    document.getElementById("editDate").value = transactions.fecha;

    // Poblar y seleccionar la categoría correcta
    const categorySelect = document.getElementById("editCategory");
    // Reutilizamos las opciones del filtro principal para no hacer otra llamada a la BD
    categorySelect.innerHTML =
      document.getElementById("filterCategory").innerHTML;
    categorySelect.value = transactions.categoria;

    // Mostrar el modal
    editModal.classList.remove("hidden");
    setTimeout(() => editModal.classList.remove("opacity-0"), 10);
  }

  /**
   * Cierra el modal de edición.
   */
  function hideEditModal() {
    editModal.classList.add("opacity-0");
    setTimeout(() => {
      editModal.classList.add("hidden");
      editForm.reset(); // Limpiar el formulario
    }, 300);
  }

  // Listeners para cerrar el modal de edición
  closeEditModalBtn.addEventListener("click", hideEditModal);
  cancelEditBtn.addEventListener("click", hideEditModal);

  /**
   * Maneja el envío del formulario de edición.
   */
  editForm.addEventListener("submit", async (e) => {
    e.preventDefault();
    const submitBtn = editForm.querySelector('button[type="submit"]');
    submitBtn.disabled = true;
    submitBtn.textContent = "Guardando...";

    const formData = new FormData(editForm);
    const data = {
      action: "update",
      id: document.getElementById("editTransactionId").value,
      type: document.getElementById("editTransactionType").value,
      description: formData.get("description"),
      amount: formData.get("amount"),
      date: formData.get("date"),
      category: formData.get("category"),
    };

    try {
      const response = await fetch("../models/Historial.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: new URLSearchParams(data),
      });

      console.log("DEBUG payload sent:", data);
      const result = await response.json();
      console.log("DEBUG result:", result);
      if (!result.success) throw new Error(result.message);

      showNotification("Transacción actualizada exitosamente.", "success");
      hideEditModal();
      fetchTransactions(); // Recargar la tabla para ver los cambios
    } catch (error) {
      console.error("Error al actualizar la transacción:", error);
      showNotification(`Error: ${error.message}`, "error");
    } finally {
      submitBtn.disabled = false;
      submitBtn.textContent = "Guardar Cambios";
    }
  });

  // --- MODAL DE CONFIRMACIÓN REUTILIZABLE ---

  function showConfirmationModal(options) {
    const modal = document.getElementById("confirmationModal");
    const modalBox = document.getElementById("confirmationModalBox");
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
      cancelText: "Cancelar",
      icon: "danger", // 'danger' | 'warning'
      ...options,
    };

    const styles = {
      danger: {
        icon: '<svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>',
        bgColor: "bg-red-100",
        confirmCls: "bg-red-600 hover:bg-red-700 focus:ring-red-500/30",
      },
      warning: {
        icon: '<svg class="h-6 w-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>',
        bgColor: "bg-yellow-100",
        confirmCls:
          "bg-yellow-500 hover:bg-yellow-600 focus:ring-yellow-500/30",
      },
    };
    const theme = styles[config.icon] || styles.warning;

    // Pinta contenido
    titleEl.textContent = config.title;
    messageEl.textContent = config.message;
    confirmBtn.textContent = config.confirmText;
    cancelBtn.textContent = config.cancelText;

    iconContainer.innerHTML = theme.icon;
    iconContainer.className = `mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full ${theme.bgColor}`;

    // Actualiza clases del botón confirmar
    confirmBtn.className = `px-6 py-2.5 text-sm font-medium text-white focus:outline-none focus:ring-2 rounded-lg transition-colors ${theme.confirmCls}`;

    // Abrir con animación
    const prevFocused = document.activeElement;
    document.documentElement.classList.add("overflow-y-hidden");
    modal.classList.remove("hidden");
    requestAnimationFrame(() => {
      modal.classList.remove("opacity-0");
      modalBox.classList.remove("scale-95");
    });

    // Enfoque inicial
    setTimeout(() => confirmBtn.focus(), 30);

    // Cierre y limpieza
    let resolved = false;
    const cleanup = () => {
      if (resolved) return;
      resolved = true;
      modal.classList.add("opacity-0");
      modalBox.classList.add("scale-95");
      setTimeout(() => {
        modal.classList.add("hidden");
        document.documentElement.classList.remove("overflow-y-hidden");
        confirmBtn.removeEventListener("click", onConfirm);
        cancelBtn.removeEventListener("click", onCancel);
        modal.removeEventListener("click", onOverlay);
        document.removeEventListener("keydown", onKey);
        if (prevFocused && typeof prevFocused.focus === "function")
          prevFocused.focus();
      }, 200);
    };

    const onConfirm = () => {
      cleanup();
      resolver(true);
    };
    const onCancel = () => {
      cleanup();
      resolver(false);
    };
    const onOverlay = (e) => {
      if (e.target === modal) onCancel();
    };
    const onKey = (e) => {
      if (e.key === "Escape") onCancel();
      if (e.key === "Tab") {
        const focusables = [confirmBtn, cancelBtn];
        const idx = focusables.indexOf(document.activeElement);
        if (e.shiftKey && (idx === 0 || idx === -1)) {
          e.preventDefault();
          cancelBtn.focus();
        } else if (!e.shiftKey && idx === focusables.length - 1) {
          e.preventDefault();
          confirmBtn.focus();
        }
      }
    };

    confirmBtn.addEventListener("click", onConfirm);
    cancelBtn.addEventListener("click", onCancel);
    modal.addEventListener("click", onOverlay);
    document.addEventListener("keydown", onKey);

    let resolver;
    return new Promise((resolve) => {
      resolver = resolve;
    });
  }
});
