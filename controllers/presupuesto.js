/**
 * presupuesto.js
 * Lógica completa para la página de gestión de presupuestos.
 * Versión: ES6+ sin jQuery, carga dinámica de categorías.
 */

document.addEventListener("DOMContentLoaded", () => {
  // --- SELECTORES DEL DOM ---
  // Se definen aquí para fácil acceso y mantenimiento.
  const addBudgetModal = document.getElementById("addBudgetModal");
  const addBudgetForm = document.getElementById("addBudgetForm");
  const showAddBudgetModalBtn = document.getElementById(
    "showAddBudgetModalBtn"
  );
  const hideAddBudgetModalBtn = document.getElementById(
    "hideAddBudgetModalBtn"
  );
  const cancelAddBudgetBtn = document.getElementById("cancelAddBudgetBtn");
  const budgetCategoriesContainer = document.getElementById(
    "budgetCategoriesContainer"
  );
  const newBudgetCategorySelect = document.getElementById("newBudgetCategory");
  const newBudgetAmountInput = document.getElementById("newBudgetAmount");
  const newBudgetPeriodSelect = document.getElementById("newBudgetPeriod");
  const newBudgetStartDateInput = document.getElementById("newBudgetStartDate");

  // --- INICIALIZACIÓN PRINCIPAL ---
  // 1. Carga la estructura de la UI (tarjetas y opciones del select) desde la BD.
  // 2. Una vez completado, carga los datos numéricos de los presupuestos.
  initializePage();

  // --- EVENT LISTENERS ---
  // Centralizamos todos los manejadores de eventos aquí.
  showAddBudgetModalBtn.addEventListener("click", showAddBudgetModal);
  hideAddBudgetModalBtn.addEventListener("click", hideAddBudgetModal);
  cancelAddBudgetBtn.addEventListener("click", hideAddBudgetModal);
  addBudgetForm.addEventListener("submit", addBudgetCategory);

  newBudgetAmountInput.addEventListener("input", formatCurrencyInput);
  newBudgetPeriodSelect.addEventListener("change", updateEndDate);
  newBudgetStartDateInput.addEventListener("change", updateEndDate);

  // --- FUNCIONES PRINCIPALES ---

  /**
   * Orquesta la carga inicial de la página.
   */
  async function initializePage() {
    await loadAndRenderCategories(); // Espera a que la UI se construya
    loadBudgets(); // Luego, rellena la UI con datos
  }

  /**
   * Obtiene las categorías desde el backend y las renderiza en el DOM.
   */
  async function loadAndRenderCategories() {
    try {
      const response = await fetch(
        "../models/presupuesto.php?action=getCategories"
      );
      if (!response.ok) throw new Error(`Error HTTP: ${response.status}`);

      const result = await response.json();
      if (!result.success) throw new Error(result.message);

      const categories = result.data;

      // Limpia los contenedores antes de llenarlos
      budgetCategoriesContainer.innerHTML = "";
      newBudgetCategorySelect.innerHTML =
        '<option value="">Selecciona una categoría</option>';

      if (categories.length === 0) {
        budgetCategoriesContainer.innerHTML =
          "<p>No hay categorías definidas. Ve a configuración para agregar una.</p>";
        return;
      }
      // Itera y renderiza cada categoría
      categories.forEach((category) => {
        budgetCategoriesContainer.insertAdjacentHTML(
          "beforeend",
          createCategoryCardHTML(category)
        );
        newBudgetCategorySelect.insertAdjacentHTML(
          "beforeend",
          createCategoryOptionHTML(category)
        );
      });
    } catch (error) {
      console.error("Error al renderizar categorías:", error);
      budgetCategoriesContainer.innerHTML = `<p class="text-red-500">Error al cargar categorías: ${error.message}</p>`;
    }
  }

  /**
   * Obtiene los datos de los presupuestos y actualiza la UI.
   */
  async function loadBudgets() {
    try {
      const response = await fetch("../models/presupuesto.php");
      if (!response.ok) throw new Error(`Error HTTP: ${response.status}`);

      const result = await response.json();
      if (!result.success) throw new Error(result.message);

      renderBudgetData(result.data);
    } catch (error) {
      console.error("Error al cargar presupuestos:", error);
      showNotification(
        "No se pudieron cargar los datos de presupuestos.",
        "error"
      );
    }
  }

  /**
   * Maneja el envío del formulario para agregar un nuevo presupuesto.
   */
  async function addBudgetCategory(event) {
    event.preventDefault();

    const formData = new FormData(addBudgetForm);
    const amount = formData.get("amount").replace(/\D/g, "");
    const submitBtn = addBudgetForm.querySelector('button[type="submit"]');

    // Validaciones del frontend
    if (
      !formData.get("category") ||
      !amount ||
      !formData.get("period") ||
      !formData.get("start_date") ||
      !formData.get("end_date")
    ) {
      return showNotification("Por favor, completa todos los campos", "error");
    }
    if (parseInt(amount) <= 0) {
      return showNotification("El monto debe ser mayor a 0", "error");
    }
    if (
      new Date(formData.get("start_date")) >= new Date(formData.get("end_date"))
    ) {
      return showNotification(
        "La fecha de fin debe ser posterior a la fecha de inicio",
        "error"
      );
    }

    const originalText = submitBtn.textContent;
    submitBtn.textContent = "Guardando...";
    submitBtn.disabled = true;

    try {
      const response = await fetch("../models/presupuesto.php", {
        method: "POST",
        body: new URLSearchParams({
          category: formData.get("category"),
          amount: amount,
          period: formData.get("period"),
          start_date: formData.get("start_date"),
          end_date: formData.get("end_date"),
        }),
      });

      const result = await response.json();

      if (!response.ok)
        throw new Error(result.message || `Error HTTP: ${response.status}`);

      if (result.success) {
        showNotification("Presupuesto agregado exitosamente", "success");
        hideAddBudgetModal();
        initializePage(); // Recarga toda la data para reflejar los cambios
      } else {
        showNotification(
          result.message || "Error al agregar presupuesto",
          "error"
        );
      }
    } catch (error) {
      console.error("Error AJAX:", error);
      showNotification(
        error.message || "Error de conexión. Intenta nuevamente.",
        "error"
      );
    } finally {
      submitBtn.textContent = originalText;
      submitBtn.disabled = false;
    }
  }

  // --- FUNCIONES DE RENDERIZADO Y UI ---

  /**
   * Procesa y muestra los datos numéricos de los presupuestos en las tarjetas.
   */
  function renderBudgetData(budgets) {
    let totalBudget = 0;
    let totalUsed = 0;
    const categoryData = {};

    // Inicializa los datos de las categorías que están en el DOM
    document.querySelectorAll(".budget-category-item").forEach((card) => {
      const categoryCode = card.dataset.category;
      categoryData[categoryCode] = { budgeted: 0, used: 0 };
    });

    // Agrega los datos de los presupuestos cargados
    budgets.forEach((budget) => {
      if (categoryData[budget.category]) {
        categoryData[budget.category].budgeted += parseFloat(budget.amount);
        categoryData[budget.category].used += parseFloat(
          budget.total_expenses || 0
        );
      }
    });

    // Actualiza cada tarjeta de categoría
    Object.entries(categoryData).forEach(([code, data]) => {
      updateCategoryCard(code, data);
      totalBudget += data.budgeted;
      totalUsed += data.used;
    });

    // Actualiza los totales generales
    document.getElementById("totalBudgetAmount").textContent =
      formatCurrency(totalBudget);
    document.getElementById("totalUsedAmount").textContent =
      formatCurrency(totalUsed);
    document.getElementById("totalRemainingAmount").textContent =
      formatCurrency(totalBudget - totalUsed);
  }

  /**
   * Actualiza una tarjeta de categoría individual con nuevos datos.
   */
  function updateCategoryCard(categoryCode, data) {
    const element = budgetCategoriesContainer.querySelector(
      `[data-category="${categoryCode}"]`
    );
    if (!element) return;

    const percentage =
      data.budgeted > 0 ? (data.used / data.budgeted) * 100 : 0;

    const usedEl = element.querySelector(".text-sm.font-semibold");
    const percentageEl = element.querySelector(".text-xs.px-2");
    const progressFill = element.querySelector(".progress-fill");
    const texts = element.querySelectorAll(".text-xs.text-gray-500");

    usedEl.textContent = formatCurrency(data.used);
    percentageEl.textContent = `${Math.round(percentage)}%`;
    progressFill.style.width = `${Math.min(percentage, 100)}%`;
    texts[0].textContent = `Gastado: ${formatCurrency(data.used)}`;
    texts[1].textContent = `Presupuesto: ${formatCurrency(data.budgeted)}`;

    // Lógica de colores (puedes obtener el color desde la BD en el futuro)
    let colorClass = "bg-green-500",
      badgeClass = "bg-green-100 text-green-800",
      textClass = "text-green-600";
    if (percentage >= 90)
      [colorClass, badgeClass, textClass] = [
        "bg-red-500",
        "bg-red-100 text-red-800",
        "text-red-600",
      ];
    else if (percentage >= 70)
      [colorClass, badgeClass, textClass] = [
        "bg-orange-500",
        "bg-orange-100 text-orange-800",
        "text-orange-600",
      ];
    else if (percentage >= 50)
      [colorClass, badgeClass, textClass] = [
        "bg-yellow-500",
        "bg-yellow-100 text-yellow-800",
        "text-yellow-600",
      ];

    const removeClasses = (el, prefix) =>
      el.className
        .split(" ")
        .filter((c) => !c.startsWith(prefix))
        .join(" ");

    progressFill.className =
      removeClasses(progressFill, "bg-") + ` ${colorClass}`;
    percentageEl.className =
      removeClasses(percentageEl, "bg-") + ` ${badgeClass}`;
    usedEl.className = removeClasses(usedEl, "text-") + ` ${textClass}`;
  }

  /**
   * Muestra u oculta el modal para agregar presupuesto.
   */
  function showAddBudgetModal() {
    addBudgetModal.classList.remove("hidden");
    document.getElementById("newBudgetStartDate").value = new Date()
      .toISOString()
      .split("T")[0];
    updateEndDate();
  }

  function hideAddBudgetModal() {
    addBudgetModal.classList.add("hidden");
    addBudgetForm.reset();
  }

  /**
   * Muestra una notificación tipo "toast" en la esquina de la pantalla.
   */
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

  // --- FUNCIONES AUXILIARES (Helpers) ---

  /**
   * Genera el HTML para una tarjeta de categoría.
   */
  function createCategoryCardHTML(category) {
    return `
            <div class="budget-category-item p-4 bg-white rounded-lg border border-gray-200" data-category="${
              category.id
            }">
                <div class="budget-category-header flex justify-between items-center mb-1">
                    <span class="font-medium text-gray-800 flex items-center space-x-2">
                        <span class="w-5 h-5">${category.icono || "📦"}</span>
                        <span>${category.nombre_categoria}</span>
                    </span>
                    <div class="budget-category-stats flex items-center space-x-2">
                        <span class="text-sm font-semibold text-gray-500">$0</span>
                        <span class="text-xs bg-gray-100 text-gray-800 px-2 py-1 rounded-full">0%</span>
                    </div>
                </div>
                <div class="progress-bar bg-gray-200 rounded-full h-2">
                    <div class="progress-fill bg-gray-400 h-2 rounded-full" style="width: 0%"></div>
                </div>
                <div class="flex justify-between items-center mt-2">
                    <span class="text-xs text-gray-500">Gastado: $0</span>
                    <span class="text-xs text-gray-500">Presupuesto: $0</span>
                </div>
            </div>
        `;
  }

  /**
   * Genera el HTML para una opción del select.
   */
  function createCategoryOptionHTML(category) {
    return `<option value="${category.id}">${category.nombre_categoria}</option>`;
  }

  /**
   * Calcula y actualiza la fecha de fin automáticamente.
   */
  function updateEndDate() {
    const startDateInput = document.getElementById("newBudgetStartDate");
    const period = document.getElementById("newBudgetPeriod").value;
    const endDateInput = document.getElementById("newBudgetEndDate");

    if (startDateInput.value && period) {
      const start = new Date(startDateInput.value);
      const endDate = new Date(start);
      // Ajuste para evitar problemas de zona horaria
      endDate.setMinutes(endDate.getMinutes() + endDate.getTimezoneOffset());

      switch (period) {
        case "diario":
          endDate.setDate(start.getDate() + 1);
          break;
        case "semanal":
          endDate.setDate(start.getDate() + 7);
          break;
        case "quincenal":
          endDate.setDate(start.getDate() + 15);
          break;
        case "mensual":
          endDate.setMonth(start.getMonth() + 1);
          break;
        case "anual":
          endDate.setFullYear(start.getFullYear() + 1);
          break;
      }
      endDateInput.value = endDate.toISOString().split("T")[0];
    }
  }

  /**
   * Formatea el valor de un input a formato de moneda mientras se escribe.
   */
  function formatCurrencyInput(e) {
    let value = e.target.value.replace(/\D/g, "");
    e.target.value = value ? parseInt(value).toLocaleString("es-CO") : "";
  }

  /**
   * Formatea un número a una cadena de moneda.
   */
  const formatCurrency = (value) =>
    `$${parseInt(value || 0).toLocaleString("es-CO")}`;
});
