document.addEventListener("DOMContentLoaded", function () {
  const editModal = document.getElementById("edit-category-modal");
  const editCategoryNameInput = document.getElementById("edit-category-name");
  const editCategoryIdInput = document.getElementById("edit-category-id");
  const btnCancelEdit = document.getElementById("btn-cancel-edit");
  const btnSaveEdit = document.getElementById("btn-save-edit");

  const categoryListContainer = document.getElementById("category-list");
  const loadingState = document.getElementById("loading-state");
  const modal = document.getElementById("delete-category-modal");
  const deletingCategoryNameSpan = document.getElementById(
    "deleting-category-name"
  );
  const incomeCountSpan = document.getElementById("income-count");
  const reassignSelect = document.getElementById("reassign-category-select");
  const btnCancelDelete = document.getElementById("btn-cancel-delete");
  const btnConfirmDelete = document.getElementById("btn-confirm-delete");

  let categoryIdToDelete = null;
  async function loadCategories() {
    loadingState.style.display = "block";
    categoryListContainer.innerHTML = "";
    try {
      const response = await makeRequest("obtener_todas");
      if (response.success) {
        renderCategories(response.data);
      } else {
        showError(response.message || "No se pudieron cargar las categorías.");
      }
    } catch (error) {
      console.error("Error en loadCategories:", error);
      showError("Hubo un problema de conexión al cargar tus categorías.");
    } finally {
      loadingState.style.display = "none";
    }
  }
  async function prepareToDelete(categoryId, categoryName) {
    categoryIdToDelete = categoryId;

    const allCategoryElements = document.querySelectorAll(".btn-delete");
    if (allCategoryElements.length <= 1) {
      showNotification({
        type: "error",
        title: "Acción no permitida",
        message:
          "No puedes eliminar tu única categoría. Crea otra primero para poder reasignar los ingresos.",
      });
      return;
    }

    try {
      const response = await makeRequest("preparar_eliminacion", {
        category_id: categoryId,
      });
      if (!response.success) {
        throw new Error(response.message);
      }
      if (response.income_count === 0) {
        const confirmed = await showConfirmationModal({
          title: "Eliminar Categoría",
          message: `¿Estás seguro de que quieres eliminar la categoría "${categoryName}"? Esta acción no se puede deshacer.`,
          confirmText: "Sí, Eliminar",
          icon: "danger",
        });

        if (confirmed) {
          const deleteResponse = await makeRequest(
            "ejecutar_eliminacion_simple",
            {
              category_id: categoryId,
            }
          );

          if (deleteResponse.success) {
            showNotification({
              type: "success",
              title: "¡Eliminada!",
              message: "La categoría ha sido eliminada correctamente.",
            });
            await loadCategories();
          } else {
            showNotification({
              type: "error",
              title: "Error",
              message:
                deleteResponse.message || "No se pudo eliminar la categoría.",
            });
          }
        }
        return;
      }
      deletingCategoryNameSpan.textContent = categoryName;
      incomeCountSpan.textContent = response.income_count;

      reassignSelect.innerHTML = "";
      if (response.reassign_options.length === 0) {
        showNotification({
          type: "error",
          title: "Acción Requerida",
          message:
            "No hay otras categorías a las que reasignar. Por favor, crea una nueva categoría primero.",
        });
        return;
      }
      response.reassign_options.forEach((cat) => {
        reassignSelect.add(new Option(cat.nombre_categoria, cat.id));
      });

      modal.classList.remove("hidden");
    } catch (error) {
      console.error("Error en prepareToDelete:", error);
      showNotification({
        type: "error",
        title: "Error de Comunicación",
        message:
          "No se pudo obtener la información de la categoría. Revisa tu conexión.",
      });
    }
  }
  async function confirmDelete() {
    const reassignToId = reassignSelect.value;
    if (!reassignToId) {
      showNotification({
        type: "error",
        title: "Oops... Algo Salió Mal",
        message: "Por favor, selecciona una categoría para la reasignación.",
      });
      return;
    }

    btnConfirmDelete.disabled = true;
    btnConfirmDelete.textContent = "Procesando...";

    try {
      const response = await makeRequest("ejecutar_eliminacion", {
        category_to_delete: categoryIdToDelete,
        reassign_to: reassignToId,
      });

      if (response.success) {
        closeModal();
        await loadCategories();
        showNotification({
          type: "success",
          title: "¡Operación Completada!",
          message:
            "¡Operación completada! La categoría ha sido eliminada y los ingresos reasignados.",
        });
      } else {
        showNotification({
          type: "error",
          title: "Oops... Algo Salió Mal",
          message: response.message || "No se pudo completar la operación.",
        });
      }
    } catch (error) {
      console.error("Error en confirmDelete:", error);
      showNotification({
        type: "error",
        title: "Oops... Algo Salió Mal",
        message: "Error de conexión al eliminar la categoría.",
      });
    } finally {
      btnConfirmDelete.disabled = false;
      btnConfirmDelete.textContent = "Reasignar y Eliminar";
    }
  }

  function closeModal() {
    modal.classList.add("hidden");
  }

  function renderCategories(categories) {
    if (categories.length === 0) {
      categoryListContainer.innerHTML = `<div class="text-center py-10"><p class="text-gray-500">Aún no has creado ninguna categoría de ingreso.</p></div>`;
      return;
    }
    categoryListContainer.innerHTML = categories
      .map(
        (category) => `
            <div class="flex items-center justify-between p-4 bg-white rounded-xl shadow-sm hover:shadow-lg transition-shadow duration-300">
                <div class="flex items-center gap-4">
                    <span class="text-2xl text-emerald-600 w-8 text-center"><i class="${
                      category.icono || "fas fa-tag"
                    }"></i></span>
                    <span class="font-medium text-gray-700">${escapeHtml(
                      category.nombre_categoria
                    )}</span>
                </div>
                <div class="flex items-center gap-2">
                    <button class="btn-edit p-2 text-gray-400 hover:text-blue-600 transition-colors" data-id="${
                      category.id
                    }" data-name="${escapeHtml(
          category.nombre_categoria
        )}" title="Editar Categoría">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.5L14.732 3.732z"></path></svg>
                    </button>
                    <button class="btn-delete p-2 text-gray-400 hover:text-red-600 transition-colors" data-id="${
                      category.id
                    }" data-name="${escapeHtml(
          category.nombre_categoria
        )}" title="Eliminar Categoría">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                    </button>
                </div>
            </div>
        `
      )
      .join("");
  }

  function showError(message) {
    categoryListContainer.innerHTML = `<div class="text-center py-10 text-red-500"><p><strong>Oops!</strong> ${escapeHtml(
      message
    )}</p></div>`;
  }

  function escapeHtml(str) {
    const div = document.createElement("div");
    div.appendChild(document.createTextNode(str || ""));
    return div.innerHTML;
  }
  async function makeRequest(action, data = {}) {
    const params = { action: action, ...data };
    try {
      const response = await fetch("../models/gestionar_categorias.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: new URLSearchParams(params),
      });
      if (!response.ok) {
        throw new Error(`Error de red o del servidor: ${response.statusText}`);
      }
      return response.json();
    } catch (error) {
      console.error(`Error en makeRequest para la acción "${action}":`, error);
      throw error;
    }
  }

  function openEditModal(categoryId, categoryName) {
    editCategoryIdInput.value = categoryId;
    editCategoryNameInput.value = categoryName;

    // Mostrar el modal
    editModal.classList.remove("hidden");
    editCategoryNameInput.focus(); // Poner el foco en el input
  }

  async function saveEdit() {
    const categoryId = editCategoryIdInput.value;
    const newName = editCategoryNameInput.value.trim();

    if (newName.length < 3) {
      showNotification({
        type: "error",
        title: "Oops... Algo Salió Mal",
        message: "El nombre debe tener al menos 3 caracteres.",
      });
      return;
    }

    btnSaveEdit.disabled = true;
    btnSaveEdit.textContent = "Guardando...";

    try {
      const response = await makeRequest("ejecutar_edicion", {
        category_id: categoryId,
        new_name: newName,
      });

      if (response.success) {
        closeEditModal();
        await loadCategories(); // Recargar la lista para ver los cambios
        showNotification({
          type: "success",
          title: "¡Operación Completada!",
          message: "¡Categoría actualizada con éxito!",
        });
      } else {
        showNotification({
          type: "error",
          title: "Oops... Algo Salió Mal",
          message: response.message || "No se pudo actualizar la categoría.",
        });
      }
    } catch (error) {
      showNotification({
        type: "error",
        title: "Oops... Algo Salió Mal",
        message: "Error de conexión al guardar los cambios.",
      });
    } finally {
      btnSaveEdit.disabled = false;
      btnSaveEdit.textContent = "Guardar Cambios";
    }
  }

  function closeEditModal() {
    editModal.classList.add("hidden");
  }

  /* FUNCION PARA LA ALERTA, ES REUTILIZABLE */

  function showNotification({ type = "success", title, message }) {
    const modal = document.getElementById("notification-modal");
    const modalBox = document.getElementById("notification-modal-box");
    const iconContainer = document.getElementById(
      "notification-icon-container"
    );
    const titleEl = document.getElementById("notification-title");
    const messageEl = document.getElementById("notification-message");
    const closeBtn = document.getElementById("notification-close-btn");

    if (!modal || !iconContainer || !titleEl || !messageEl || !closeBtn) {
      console.error("Faltan elementos del modal de notificación en el DOM.");
      alert(`${title}: ${message}`);
      return;
    }
    const notificationTypes = {
      success: {
        iconBg: "bg-emerald-100",
        iconSvg: `<svg class="w-8 h-8 text-emerald-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" /></svg>`,
        buttonBg: "bg-emerald-500 hover:bg-emerald-600",
      },
      error: {
        iconBg: "bg-red-100",
        iconSvg: `<svg class="w-8 h-8 text-red-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" /></svg>`,
        buttonBg: "bg-red-500 hover:bg-red-600",
      },
    };

    const config = notificationTypes[type];
    iconContainer.className = `w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4 ${config.iconBg}`;
    iconContainer.innerHTML = config.iconSvg;
    titleEl.textContent = title;
    messageEl.textContent = message;
    closeBtn.className = `text-white font-medium py-2 px-6 rounded-lg transition-all duration-300 ${config.buttonBg}`;

    const closeModal = () => {
      modalBox.classList.add("scale-95", "opacity-0");
      setTimeout(() => modal.classList.add("hidden"), 300);
    };

    closeBtn.addEventListener("click", closeModal, { once: true });
    modal.classList.remove("hidden");
    setTimeout(() => {
      modalBox.classList.remove("scale-95", "opacity-0");
    }, 10);
  }

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

  categoryListContainer.addEventListener("click", function (e) {
    const deleteButton = e.target.closest(".btn-delete");
    if (deleteButton) {
      const categoryId = deleteButton.dataset.id;
      const categoryName = deleteButton.dataset.name;
      prepareToDelete(categoryId, categoryName);
    }

    const editButton = e.target.closest(".btn-edit");
    if (editButton) {
      const categoryId = editButton.dataset.id;
      const categoryName = editButton.dataset.name;
      openEditModal(categoryId, categoryName);
    }
  });

  btnCancelDelete.addEventListener("click", closeModal);
  btnConfirmDelete.addEventListener("click", confirmDelete);
  btnCancelEdit.addEventListener("click", closeEditModal);
  btnSaveEdit.addEventListener("click", saveEdit);
  loadCategories();
});
