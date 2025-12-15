/**
 * Sistema de Gestión de Ingresos - Versión Refactorizada
 * Mejoras: Modularización, ES6+, mejor manejo de errores, optimización
 */

// ==================== CONFIGURACIÓN Y CONSTANTES ====================
const CONFIG = {
  endpoints: {
    registerIncome: "../models/registro_ingresos.php",
    getStats: "../models/obtener_estadisticas.php",
    getRecentIncomes: "../models/obtener_ingresos_recientes.php",
    deleteIncome: "../models/eliminar_ingreso.php",
    transactions: "../models/Historial.php",
  },
  categories: {
    salario: { icon: "💼", color: "emerald" },
    freelance: { icon: "💻", color: "indigo" },
    inversion: { icon: "📈", color: "purple" },
    negocio: { icon: "🏢", color: "blue" },
    regalo: { icon: "🎁", color: "pink" },
    otros: { icon: "📋", color: "gray" },
  },
  notifications: {
    duration: {
      error: 5000,
      success: 3000,
    },
  },
};

// ==================== CLASE PRINCIPAL ====================
class IncomeManager {
  constructor() {
    this.isLoading = false;
    this.categorySelect = null;
    this.init();
  }

  init() {
    this.bindEvents();
    this.setDefaultDate();
    this.initializeCategorySelector();
    this.loadInitialData();
    this.loadCategoriesIntoSelect();
  }

  // ==================== EVENTOS ====================
  bindEvents() {
    // Form submission
    $("#btnGuardarIngreso").on("click", (e) => this.handleFormSubmission(e));

    $("#btn-add-category").on("click", () => this.openAddCategoryModal());
    $("#btn-cancel-add-category").on("click", () =>
      this.closeAddCategoryModal()
    );
    $("#btn-save-category").on("click", () => this.saveNewCategory());

    // Amount formatting
    $("#amount")
      .on("input", this.formatAmountInput.bind(this))
      .on("blur", this.formatAmountBlur.bind(this));

    // Modal close
    $(document).on("click", ".close-modal", () => this.closeModal());

    // ESC key to close modals
    $(document).on("keydown", (e) => {
      if (e.key === "Escape") this.closeModal();
    });
  }

  // ==================== UTILIDADES DE FECHA ====================
  setDefaultDate() {
    const today = new Date().toISOString().split("T")[0];
    $("#date").val(today);

    // Para compatibilidad con vanilla JS
    const dateElement = document.getElementById("date");
    if (dateElement) {
      dateElement.valueAsDate = new Date();
    }
  }

  async initializeCategorySelector() {
    try {
      // 1. Obtener el input del DOM
      const input = document.querySelector("#category-input"); // Asegúrate que el ID es correcto
      if (!input) {
        console.error("❌ No se encontró el input para Tagify.");
        return;
      }

      // 2. Pedir las categorías existentes para la lista de sugerencias
      const suggestions = await this.makeRequest(
        "../models/obtener_cat_ingresos.php",
        {}
      );
      if (!Array.isArray(suggestions)) {
        console.warn(
          "⚠️ No se pudieron cargar las sugerencias, pero Tagify funcionará igual."
        );
        suggestions = [];
      }

      if (this.categorySelect) {
        this.categorySelect.destroy();
      }

      this.categorySelect = new Tagify(input, {
        whitelist: suggestions, // Lista de sugerencias
        maxTags: 1, // Solo una categoría
        dropdown: {
          enabled: 0, // El desplegable aparece al escribir
          closeOnSelect: true,
          position: "text",
          maxItems: 10,
        },
        // No se necesita nada más para la creación. ¡Simplemente funciona!
      });
    } catch (error) {
      console.error("🔥 Error inicializando Tagify:", error);
      this.showError("Error al cargar el campo de categorías.");
    }
  }

  async loadCategoriesIntoSelect(selectAfterAdding = null) {
    try {
      const categories = await this.makeRequest(
        "../models/obtener_cat_ingresos.php",
        {}
      );
      const select = $("#category-select");
      select
        .empty()
        .append('<option value="">Selecciona una categoría...</option>'); // Limpiar y añadir opción por defecto

      categories.forEach((cat) => {
        const option = new Option(cat, cat); // new Option(texto, valor)
        select.append(option);
      });

      // Si venimos de añadir una nueva, la seleccionamos
      if (selectAfterAdding) {
        select.val(selectAfterAdding);
      }
    } catch (error) {
      console.error("Error cargando categorías en el select:", error);
    }
  }

  openAddCategoryModal() {
    $("#add-category-modal").removeClass("hidden");
    $("#new-category-name").focus();
  }

  closeAddCategoryModal() {
    $("#add-category-modal").addClass("hidden");
    $("#new-category-name").val(""); // Limpiar el input
  }

  async saveNewCategory() {
    const newCategoryName = $("#new-category-name").val().trim();
    if (newCategoryName.length < 3) {
      this.showError(
        "El nombre de la categoría debe tener al menos 3 caracteres."
      );
      return;
    }

    try {
      // Necesitaremos un nuevo endpoint PHP para esto
      const response = await this.makeRequest(
        "../models/crear_categoria_ingreso.php",
        { name: newCategoryName }
      );

      if (response.success) {
        this.showSuccess("¡Categoría creada!");
        this.closeAddCategoryModal();
        // Recargar el select y seleccionar la nueva categoría
        this.loadCategoriesIntoSelect(newCategoryName);
      } else {
        this.showError(response.message || "No se pudo crear la categoría.");
      }
    } catch (error) {
      this.showError("Error de conexión al crear la categoría.");
    }
  }

  getCategoryIcon(categoryName) {
    const name = categoryName.toLowerCase();
    if (name.includes("salario") || name.includes("sueldo")) return "💼";
    if (name.includes("freelance") || name.includes("proyecto")) return "💻";
    if (name.includes("inversi")) return "📈";
    if (name.includes("negocio") || name.includes("venta")) return "🏢";
    if (name.includes("regalo") || name.includes("dono")) return "🎁";
    return "📁"; // Un ícono por defecto
  }

  // ==================== CARGA DE DATOS ====================
  async loadInitialData() {
    try {
      await Promise.all([this.loadStats(), this.loadRecentIncomes()]);
    } catch (error) {
      this.showError("Error al cargar los datos iniciales");
      console.error("Error loading initial data:", error);
    }
  }

  async loadStats() {
    try {
      const response = await this.makeRequest(CONFIG.endpoints.getStats, {});
      if (response.success) {
        this.updateStats(response.data);
      }
    } catch (error) {
      console.error("Error loading stats:", error);
    }
  }

  async loadRecentIncomes() {
    try {
      const response = await this.makeRequest(
        CONFIG.endpoints.getRecentIncomes,
        {}
      );
      if (response.success) {
        this.displayRecentIncomes(response.data);
      }
    } catch (error) {
      console.error("Error loading recent incomes:", error);
    }
  }

  // ==================== MANEJO DE FORMULARIO ====================
  async handleFormSubmission(e) {
    e?.preventDefault();

    if (this.isLoading) return;

    const formData = this.getFormData();

    if (!this.validateForm(formData)) {
      return;
    }

    await this.submitForm(formData);
  }

  // En controllers/registro_ingresos.js, dentro de la clase IncomeManager

  getFormData() {
    const categoryValue = $("#category-select").val();
    return {
      concept: $("#concept").val().trim(),
      amount: parseFloat($("#amount").val()),
      category: categoryValue, // <-- Ahora sí tendrá el valor correcto (ej: "Ingresos Extras")
      date: $("#date").val(),
      notes: $("#notes").val().trim(),
    };
  }

  validateForm(data) {
    const validations = [
      {
        condition: data.concept.length < 3,
        message: "El concepto debe tener al menos 3 caracteres",
        focusElement: "#concept",
      },
      {
        condition: isNaN(data.amount) || data.amount <= 0,
        message: "El monto debe ser un número mayor a 0",
        focusElement: "#amount",
      },
      {
        condition: !data.category,
        message: "Debes seleccionar una categoría",
        focusElement: "#category",
      },
      {
        condition: !data.date,
        message: "Debes seleccionar una fecha",
        focusElement: "#date",
      },
      {
        condition: this.isFutureDate(data.date),
        message: "La fecha no puede ser futura",
        focusElement: "#date",
      },
    ];

    const failedValidation = validations.find(
      (validation) => validation.condition
    );

    if (failedValidation) {
      this.showError(failedValidation.message);
      $(failedValidation.focusElement).focus();
      return false;
    }

    return true;
  }

  isFutureDate(dateString) {
    const selectedDate = new Date(dateString);
    const today = new Date();
    today.setHours(23, 59, 59, 999); // End of today
    return selectedDate > today;
  }

  async submitForm(formData) {
    try {
      this.setLoadingState(true);

      const response = await this.makeRequest(
        CONFIG.endpoints.registerIncome,
        formData
      );

      if (response.success) {
        this.handleSuccessfulSubmission();
      } else {
        this.showError(response.message || "Error al guardar el ingreso");
      }
    } catch (error) {
      this.showError(`Error de conexión: ${error.message}`);
    } finally {
      this.setLoadingState(false);
    }
  }

  handleSuccessfulSubmission() {
    this.showSuccessModal();
    this.resetForm();
    this.loadStats();
    this.loadRecentIncomes();
  }

  resetForm() {
    $("#income-form")[0].reset();
    if (this.categorySelect) {
      this.categorySelect.clear();
    }
    this.setDefaultDate();
  }

  // ==================== FORMATEO DE NÚMEROS ====================
  formatAmountInput(e) {
    let value = $(e.target).val();
    if (value) {
      // Solo números y punto decimal
      value = value.replace(/[^0-9.]/g, "");
      // Evitar múltiples puntos decimales
      const parts = value.split(".");
      if (parts.length > 2) {
        value = parts[0] + "." + parts.slice(1).join("");
      }
      $(e.target).val(value);
    }
  }

  formatAmountBlur(e) {
    const value = parseFloat($(e.target).val());
    if (!isNaN(value)) {
      $(e.target).val(value.toFixed(2));
    }
  }

  // ==================== ACTUALIZACIÓN DE UI ====================
  updateStats(stats) {
    const updates = [
      {
        selector: ".text-emerald-600",
        value: `$${this.formatNumber(stats.total_ingresos)}`,
      },
      {
        selector: ".text-indigo-600",
        value: `$${this.formatNumber(stats.mes_actual)}`,
      },
      { selector: ".text-purple-600", value: stats.total_transacciones },
    ];

    updates.forEach((update) => {
      $(update.selector).first().text(update.value);
    });
  }

  displayRecentIncomes(incomes) {
    const container = $("#recent-incomes");
    container.empty();

    if (incomes.length === 0) {
      container.html(this.getEmptyStateHTML());
      return;
    }

    const incomesHTML = incomes
      .map((income) => this.createIncomeHTML(income))
      .join("");
    container.html(incomesHTML);
  }

  getEmptyStateHTML() {
    return `
            <div class="text-center py-8">
                <div class="text-gray-400 text-6xl mb-4">📊</div>
                <p class="text-gray-500 text-lg">No hay ingresos recientes</p>
                <p class="text-gray-400 text-sm">Los nuevos ingresos aparecerán aquí</p>
            </div>
        `;
  }

  createIncomeHTML(income) {
    const categoryInfo = this.getCategoryInfo(income.categoria);
    const timeAgo = this.getTimeAgo(income.fecha);
    return `
        <div class="p-4 bg-${categoryInfo.color}-50 rounded-xl border border-${
      categoryInfo.color
    }-200 hover:shadow-md transition-shadow">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-center space-x-3">
                    <div class="flex-shrink-0 w-12 h-12 bg-${
                      categoryInfo.color
                    }-500 rounded-full flex items-center justify-center shadow-lg">
                        <span class="text-white text-xl">${
                          categoryInfo.icon
                        }</span>
                    </div>
                    <div class="min-w-0">
                        <p class="font-semibold text-gray-800 truncate">${this.escapeHtml(
                          income.concepto
                        )}</p>
                        <p class="text-sm text-gray-600">${timeAgo}</p>
                        ${
                          income.notas
                            ? `<p class="text-xs text-gray-500 mt-1 truncate">${this.escapeHtml(
                                income.notas
                              )}</p>`
                            : ""
                        }
                    </div>
                </div>
                <div class="flex items-center justify-between mt-3 sm:mt-0 sm:flex-col sm:items-end">
                    <p class="text-lg sm:text-xl font-bold text-${
                      categoryInfo.color
                    }-600">+$${this.formatNumber(income.monto)}</p>
                    <button onclick="incomeManager.deleteIncome(${income.id})" 
                            class="text-red-500 hover:text-red-700 text-sm sm:mt-1 transition-colors">
                        🗑️ Eliminar
                    </button>
                </div>

            </div>
        </div>
    `;
  }

  // ==================== ELIMINACIÓN DE INGRESOS ====================
  async deleteIncome(id) {
    try {
      const confirmed = await this.showConfirmationModal({
        title: "Eliminar Ingreso",
        message:
          "¿Estás seguro de que quieres eliminar este ingreso? Esta acción no se puede deshacer.",
        confirmText: "Sí, Eliminar",
        icon: "danger",
      });

      if (!confirmed) return;

      const response = await this.makeRequest(CONFIG.endpoints.deleteIncome, {
        id,
      });

      if (response.success) {
        await this.loadInitialData();
        this.showSuccess("Ingreso eliminado correctamente");
      } else {
        this.showError(response.message || "Error al eliminar el ingreso");
      }
    } catch (error) {
      this.showError(`Error de conexión: ${error.message}`);
    }
  }

  // ==================== TRANSACCIONES (HISTORIAL) ====================
  async handleDelete(id, type) {
    try {
      const confirmed = await this.showConfirmationModal({
        title: "Eliminar Transacción",
        message:
          "¿Estás seguro de que quieres eliminar este movimiento? Esta acción no se puede deshacer.",
        confirmText: "Sí, Eliminar",
        icon: "danger",
      });

      if (!confirmed) return;

      const response = await fetch(CONFIG.endpoints.transactions, {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: new URLSearchParams({ action: "delete", id, type }),
      });

      const result = await response.json();
      if (!result.success) throw new Error(result.message);

      this.showSuccess("Transacción eliminada exitosamente");

      // Si existe la función fetchTransactions, la llamamos
      if (typeof fetchTransactions === "function") {
        fetchTransactions();
      }
    } catch (error) {
      console.error("Error al eliminar la transacción:", error);
      this.showError(`Error: ${error.message}`);
    }
  }

  // ==================== MODAL DE CONFIRMACIÓN ====================
  async showConfirmationModal(options = {}) {
    const config = {
      title: "¿Estás seguro?",
      message: "Esta acción no se puede deshacer.",
      confirmText: "Confirmar",
      cancelText: "Cancelar",
      icon: "danger",
      ...options,
    };

    const modal = document.getElementById("confirmationModal");
    if (!modal) {
      console.error("Modal de confirmación no encontrado");
      return false;
    }

    this.setupConfirmationModal(config);
    this.showModal(modal);

    return new Promise((resolve) => {
      this.setupConfirmationHandlers(modal, resolve);
    });
  }

  setupConfirmationModal(config) {
    const elements = {
      title: document.getElementById("confirmationModalTitle"),
      message: document.getElementById("confirmationModalMessage"),
      confirmBtn: document.getElementById("confirmationModalConfirmBtn"),
      cancelBtn: document.getElementById("confirmationModalCancelBtn"),
      iconContainer: document.getElementById("confirmationModalIconContainer"),
    };

    // Verificar que todos los elementos existan
    Object.values(elements).forEach((el) => {
      if (!el) console.error("Elemento del modal no encontrado");
    });

    const iconHTML =
      '<svg class="w-8 h-8 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>';

    elements.title.textContent = config.title;
    elements.message.textContent = config.message;
    elements.confirmBtn.textContent = config.confirmText;
    elements.cancelBtn.textContent = config.cancelText;
    elements.iconContainer.innerHTML = iconHTML;
  }

  setupConfirmationHandlers(modal, resolve) {
    const confirmBtn = document.getElementById("confirmationModalConfirmBtn");
    const cancelBtn = document.getElementById("confirmationModalCancelBtn");

    const cleanup = (result) => {
      this.hideModal(modal);
      resolve(result);
    };

    const confirmHandler = () => cleanup(true);
    const cancelHandler = () => cleanup(false);

    confirmBtn.addEventListener("click", confirmHandler, { once: true });
    cancelBtn.addEventListener("click", cancelHandler, { once: true });
  }

  // ==================== NOTIFICACIONES ====================
  showError(message) {
    this.showNotification(message, "error");
  }

  showSuccess(message) {
    this.showNotification(message, "success");
  }

  showNotification(message, type = "info") {
    const config = {
      error: {
        bg: "bg-red-500",
        icon: '<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z" clip-rule="evenodd"/></svg>',
        duration: CONFIG.notifications.duration.error,
      },
      success: {
        bg: "bg-green-500",
        icon: '<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.236 4.53L8.077 10.5a.75.75 0 00-1.06 1.06l2.47 2.47a.75.75 0 001.137-.089l3.857-5.401z" clip-rule="evenodd"/></svg>',
        duration: CONFIG.notifications.duration.success,
      },
      info: {
        bg: "bg-blue-500",
        icon: '<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a.75.75 0 000 1.5h.253a.25.25 0 01.244.304l-.459 2.066A1.75 1.75 0 0010.747 15H11a.75.75 0 000-1.5h-.253a.25.25 0 01-.244-.304l.459-2.066A1.75 1.75 0 009.253 9H9z" clip-rule="evenodd"/></svg>',
        duration: 3000,
      },
    };

    const notificationConfig = config[type] || config.info;
    const id = `notification-${Date.now()}`;

    const notificationHTML = `
            <div id="${id}" class="fixed top-4 right-4 ${
      notificationConfig.bg
    } text-white px-6 py-3 rounded-lg shadow-lg z-50 transform translate-x-full transition-transform duration-300">
                <div class="flex items-center">
                    <span class="mr-3 flex-shrink-0">${
                      notificationConfig.icon
                    }</span>
                    <span>${this.escapeHtml(message)}</span>
                    <button class="ml-4 text-white hover:text-gray-200 flex-shrink-0" onclick="incomeManager.removeNotification('${id}')">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                        </svg>
                    </button>
                </div>
            </div>
        `;

    $("body").append(notificationHTML);

    // Mostrar con animación
    setTimeout(() => {
      $(`#${id}`).removeClass("translate-x-full");
    }, 100);

    // Auto-remover
    setTimeout(() => {
      this.removeNotification(id);
    }, notificationConfig.duration);
  }

  removeNotification(id) {
    const notification = $(`#${id}`);
    notification.addClass("translate-x-full");
    setTimeout(() => notification.remove(), 300);
  }

  // ==================== MODALES ====================
  showSuccessModal() {
    $("#success-modal").removeClass("hidden");
  }

  closeModal() {
    $(".modal, #success-modal").addClass("hidden");
  }

  showModal(modal) {
    modal.classList.remove("hidden");
    setTimeout(() => {
      modal.classList.remove("opacity-0");
      const modalBox = modal.querySelector('[id*="ModalBox"]');
      if (modalBox) modalBox.classList.remove("scale-95");
    }, 10);
  }

  hideModal(modal) {
    modal.classList.add("opacity-0");
    const modalBox = modal.querySelector('[id*="ModalBox"]');
    if (modalBox) modalBox.classList.add("scale-95");
    setTimeout(() => modal.classList.add("hidden"), 300);
  }

  // ==================== UTILIDADES ====================
  async makeRequest(url, data) {
    return new Promise((resolve, reject) => {
      $.ajax({
        url,
        type: "POST",
        data,
        // dataType: "json", // <-- ¡ELIMINA O COMENTA ESTA LÍNEA!
        // jQuery intentará adivinar el tipo de dato. Esto es más flexible.
        success: (response) => {
          // Intentamos parsear como JSON, si falla, devolvemos la respuesta tal cual.
          try {
            resolve(JSON.parse(response));
          } catch (e) {
            resolve(response);
          }
        },
        error: (xhr, status, error) => reject(new Error(error)),
      });
    });
  }

  setLoadingState(isLoading) {
    this.isLoading = isLoading;
    const button = $('button[type="submit"], #btnGuardarIngreso');

    if (isLoading) {
      button.prop("disabled", true).html("⏳ Guardando...");
    } else {
      button.prop("disabled", false).html("✅ Registrar Ingreso");
    }
  }

  getCategoryInfo(category) {
    return CONFIG.categories[category] || CONFIG.categories.otros;
  }

  formatNumber(num) {
    return new Intl.NumberFormat("es-CO").format(num);
  }

  getTimeAgo(date) {
    const now = new Date();
    const incomeDate = new Date(date);
    const diffTime = Math.abs(now - incomeDate);
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));

    if (diffDays === 1) return "Hoy";
    if (diffDays === 2) return "Ayer";
    if (diffDays <= 7) return `Hace ${diffDays - 1} días`;

    return incomeDate.toLocaleDateString("es-CO");
  }

  escapeHtml(text) {
    const div = document.createElement("div");
    div.textContent = text;
    return div.innerHTML;
  }
}

// ==================== INICIALIZACIÓN ====================
let incomeManager;

$(document).ready(() => {
  incomeManager = new IncomeManager();
});

// ==================== FUNCIONES GLOBALES PARA COMPATIBILIDAD ====================
// Mantenemos estas funciones para compatibilidad con HTML existente
function closeModal() {
  incomeManager?.closeModal();
}

async function deleteIncome(id) {
  return incomeManager?.deleteIncome(id);
}

async function handleDelete(id, type) {
  return incomeManager?.handleDelete(id, type);
}

// También mantenemos la funcionalidad vanilla JS para el formulario
document.addEventListener("DOMContentLoaded", () => {
  // Set today's date as default (vanilla JS compatibility)
  const dateInput = document.getElementById("date");
  if (dateInput && !dateInput.value) {
    dateInput.valueAsDate = new Date();
  }

  // Form submission (vanilla JS compatibility)
  const form = document.getElementById("income-form");
  if (form) {
    form.addEventListener("submit", (e) => {
      e.preventDefault();
      incomeManager?.handleFormSubmission(e);
    });
  }
});
