let selectedCategory = null;

// Format currency input for COP
document.getElementById("amount").addEventListener("input", function (e) {
  let value = e.target.value.replace(/\D/g, "");
  if (value.length > 0) {
    value = parseInt(value).toLocaleString("es-CO");
    e.target.value = value;
  } else {
    e.target.value = "";
  }
});

// Remove formatting on focus
document.getElementById("amount").addEventListener("focus", function (e) {
  e.target.value = e.target.value.replace(/\./g, "");
});

// Add formatting on blur
document.getElementById("amount").addEventListener("blur", function (e) {
  let value = e.target.value.replace(/\D/g, "");
  if (value) {
    e.target.value = parseInt(value).toLocaleString("es-CO");
  }
});

const descriptionInput = document.getElementById("description");
let debounceTimeout;

descriptionInput.addEventListener("input", () => {
  clearTimeout(debounceTimeout);
  debounceTimeout = setTimeout(() => {
    const description = descriptionInput.value;
    if (description.length > 3) {
      sugerirCategoria(description);
    }
  }, 500); // Espera 500ms después de que el usuario deja de escribir
});

// =================================================================
// --- SELECTORES---
// =================================================================
const budgetWarningModal = document.getElementById("budget-warning-modal");
const budgetModalMessage = document.getElementById("budget-modal-message");
const cancelBudgetWarningBtn = document.getElementById("cancel-budget-warning");
const confirmBudgetWarningBtn = document.getElementById(
  "confirm-budget-warning"
);
const categoryGrid = document.querySelector(".category-grid");
const addNewCategoryBtn = document.getElementById("add-new-category-btn");
const newCategoryModal = document.getElementById("newCategoryModal");
const newCategoryForm = document.getElementById("newCategoryForm");
const cancelNewCategoryBtn = document.getElementById("cancelNewCategoryBtn");
const amountInput = document.getElementById("amount");
const budgetHelper = document.getElementById("budget-status-helper");
let currentBudget = null;

// =================================================================
// ---  LÓGICA PARA CARGAR CATEGORÍAS (GLOBALES + USUARIO) ---
// =================================================================
async function loadCategories() {
  try {
    const response = await fetch(
      "../models/categorias_controller.php?action=obtener_todas"
    );
    const result = await response.json();
    if (!result.success) throw new Error(result.message);

    document
      .querySelectorAll(".category-card")
      .forEach((card) => card.remove());

    result.data.forEach((category) => {
      const categoryCard = document.createElement("button");
      categoryCard.type = "button";
      categoryCard.className =
        "category-card flex flex-col items-center justify-center p-4 border-2 border-gray-200 rounded-lg transition-all duration-200";
      categoryCard.dataset.categoryName = category.nombre;
      categoryCard.dataset.categoryId = category.id;

      // Lógica para el ícono: usa la ruta del ícono de la BD o una por defecto
      const iconPath = category.icono || "assets/icons/default.svg"; // Necesitarás un ícono default.svg
      const iconHTML = `<img src="${iconPath}" alt="${category.nombre}" class="w-8 h-8 mb-2">`;

      categoryCard.innerHTML = `
                ${iconHTML}
                <span class="text-sm font-medium">${category.nombre}</span>
            `;

      categoryCard.onclick = () => selectCategory(categoryCard);

      const addNewBtn = document.getElementById("add-new-category-btn");
      addNewBtn.parentNode.insertBefore(categoryCard, addNewBtn);
    });
  } catch (error) {
    showErrorMessage("Error al cargar las categorías: " + error.message);
  }
}

function hideNewCategoryModal() {
  // Animación de salida
  newCategoryModal.querySelector(".transform").classList.add("scale-95");
  setTimeout(() => {
    newCategoryModal.classList.add("hidden");
    newCategoryForm.reset();
    // Limpiar la selección de íconos
    newCategoryIconInput.value = "";
    if (iconSelector) {
      iconSelector.querySelectorAll(".selected-icon").forEach((el) => {
        el.classList.remove(
          "selected-icon",
          "border-indigo-500",
          "bg-indigo-100"
        );
      });
    }
  }, 300);
}

if (cancelNewCategoryBtn) {
  cancelNewCategoryBtn.addEventListener("click", hideNewCategoryModal);
}

// Lista de íconos.
const availableIcons = [
  { label: "Comida", path: "../assets/icons/food.png" },
  { label: "Transporte", path: "../assets/icons/motorcycle.webp" },
  { label: "Servicios", path: "../assets/icons/servicios.webp" },
  { label: "Entretenimiento", path: "../assets/icons/entretenimiento.png" },
  { label: "Salud", path: "../assets/icons/salud.webp" },
  { label: "Compras", path: "../assets/icons/compras.webp" },
  { label: "Hogar", path: "../assets/icons/hogar.webp" },
  { label: "Educación", path: "../assets/icons/educacion.webp" },
  { label: "Mascotas", path: "../assets/icons/mascotas.png" },
  { label: "Ropa", path: "../assets/icons/ropa.webp" },
  { label: "Regalos", path: "../assets/icons/regalos.png" },
  { label: "Viajes", path: "../assets/icons/viajes.png" },
];

const iconSelector = document.getElementById("icon-selector");
const newCategoryIconInput = document.getElementById("newCategoryIcon");

// --- Lógica para poblar el selector de íconos ---
if (iconSelector) {
  availableIcons.forEach((iconClass) => {
    const iconWrapper = document.createElement("div");
    iconWrapper.className =
      "p-2 border-2 border-gray-200 rounded-md flex justify-center items-center cursor-pointer hover:bg-indigo-100 hover:border-indigo-400";
    iconWrapper.dataset.iconClass = iconClass;
    iconWrapper.innerHTML = `<i class="${iconClass} text-2xl"></i>`;

    iconWrapper.addEventListener("click", () => {
      // Quita la selección del ícono anterior
      iconSelector
        .querySelectorAll(".selected-icon")
        .forEach((el) =>
          el.classList.remove(
            "selected-icon",
            "border-indigo-500",
            "bg-indigo-100"
          )
        );
      // Resalta el nuevo
      iconWrapper.classList.add(
        "selected-icon",
        "border-indigo-500",
        "bg-indigo-100"
      );
      // Guarda la clase del ícono en el input oculto
      newCategoryIconInput.value = iconClass;
    });

    iconSelector.appendChild(iconWrapper);
  });
}

if (newCategoryForm) {
  newCategoryForm.addEventListener("submit", async (e) => {
    e.preventDefault();
    const categoryNameInput = document.getElementById("newCategoryName");
    const categoryName = categoryNameInput.value.trim();
    const categoryIcon = newCategoryIconInput.value;

    if (!categoryName) {
      showErrorMessage("El nombre no puede estar vacío.", categoryNameInput);
      return;
    }
    if (!categoryIcon) {
      showErrorMessage("Por favor, selecciona un ícono.");
      return;
    }

    try {
      const response = await fetch("../models/categorias_controller.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
          action: "crear_categoria_usuario",
          nombre_categoria: categoryName,
          icono: categoryIcon,
        }),
      });
      const result = await response.json();

      if (result.success) {
        hideNewCategoryModal();
        await loadCategories(); // Recargamos la lista de categorías

        // Seleccionamos automáticamente la categoría recién creada
        const newCard = categoryGrid.querySelector(
          `button[data-category-name="${categoryName}"]`
        );
        if (newCard) {
          selectCategory(newCard);
          newCard.scrollIntoView({ behavior: "smooth", block: "center" });
        }
      } else {
        throw new Error(result.message);
      }
    } catch (error) {
      showErrorMessage(`Error: ${error.message}`);
    }
  });
}

function initializeIconSelector() {
  if (!iconSelector) return;
  iconSelector.innerHTML = ""; // Limpiar por si acaso

  availableIcons.forEach((icon) => {
    const iconCard = document.createElement("div");
    // Clases de Tailwind para replicar el diseño del prototipo
    iconCard.className =
      "flex flex-col items-center justify-center gap-1 p-3 border-2 border-gray-200 rounded-xl cursor-pointer hover:border-indigo-500 hover:bg-indigo-50 transition-all";
    iconCard.dataset.iconPath = icon.path;

    iconCard.innerHTML = `
                <div class="w-10 h-10 flex items-center justify-center">
                    <img src="${icon.path}" alt="${icon.label}" class="w-full h-full">
                </div>
                <span class="text-xs text-gray-600 font-medium">${icon.label}</span>
            `;

    iconCard.addEventListener("click", () => {
      // Lógica de selección del prototipo, adaptada
      iconSelector.querySelectorAll(".selected-icon").forEach((el) => {
        el.classList.remove(
          "selected-icon",
          "border-indigo-500",
          "bg-indigo-100"
        );
        el.classList.add("border-gray-200");
      });
      iconCard.classList.add(
        "selected-icon",
        "border-indigo-500",
        "bg-indigo-100"
      );
      iconCard.classList.remove("border-gray-200");

      newCategoryIconInput.value = icon.path;
    });

    iconSelector.appendChild(iconCard);
  });
}

if (addNewCategoryBtn) {
  addNewCategoryBtn.addEventListener("click", () => {
    newCategoryModal.classList.remove("hidden");
    // Añadimos una pequeña animación de entrada
    setTimeout(
      () =>
        newCategoryModal
          .querySelector(".transform")
          .classList.remove("scale-95"),
      10
    );

    initializeIconSelector(); // ¡Llamamos a la función para construir los íconos!

    document.getElementById("newCategoryName").focus();
  });
}
async function selectCategory(element) {
  const categoryName = element.dataset.categoryName;
  const categoryId = element.dataset.categoryId;

  // Reseteo visual y de datos
  document.querySelectorAll(".category-card").forEach((card) => {
    card.classList.remove("selected", "border-red-500");
    card.classList.add("border-gray-200");
  });
  budgetHelper.innerHTML = "";
  currentBudget = null;

  element.classList.add("selected", "border-red-500");
  element.classList.remove("border-gray-200");

  // Guardar en el input oculto
  document.getElementById("category").value = categoryName;

  if (!categoryId) return;

  try {
    // Usamos la ruta correcta a tu API de presupuesto
    const response = await fetch(
      `../models/presupuesto.php?action=check_budget&category_id=${categoryId}`
    );
    const result = await response.json();

    if (result.success && result.data.budgeted) {
      currentBudget = result.data; // Guardamos los datos del presupuesto
      updateBudgetHelper(); // Mostramos el estado inicial
    }
  } catch (error) {
    console.error("Error al verificar el presupuesto:", error);
  }
}

function clearForm() {
  document.getElementById("amount").value = "";
  document.getElementById("description").value = "";
  document.getElementById("date").valueAsDate = new Date();
  document.getElementById("category").value = "";

  document.querySelectorAll(".category-card").forEach((card) => {
    card.classList.remove("selected", "border-red-500");
    card.classList.add("border-gray-200");
  });
  if (budgetHelper) {
    budgetHelper.innerHTML = ""; // Limpia el mensaje de texto
  }
  currentBudget = null; // Resetea la memoria del asistente
  amountInput.classList.remove("border-red-500", "ring-2", "ring-red-200"); // Quita el borde rojo del monto
}

async function handleSubmit(event) {
  event.preventDefault();

  // 2. Recolectar y validar los datos (esta parte ya la tienes y está bien)
  const amountInput = document.getElementById("amount");
  const descriptionInput = document.getElementById("description");
  const dateInput = document.getElementById("date");
  const categoryInput = document.getElementById("category");
  const amountValue = amountInput.value.replace(/\./g, "");
  const amount = parseFloat(amountValue);
  const description = descriptionInput.value.trim();
  const date = dateInput.value;
  const category = categoryInput.value;

  if (!description || description.length < 3) {
    showErrorMessage(
      "La descripción debe tener al menos 3 caracteres.",
      descriptionInput
    );
    return;
  }
  if (isNaN(amount) || amount <= 0) {
    showErrorMessage("Por favor, ingresa un monto válido.", amountInput);
    return;
  }
  if (!date) {
    showErrorMessage("Por favor, selecciona una fecha.", dateInput);
    return;
  }
  if (!category) {
    showErrorMessage("Por favor, selecciona una categoría.");
    return;
  }

  const isOverBudget = currentBudget && amount > currentBudget.remaining;

  if (isOverBudget) {
    // Si se supera el presupuesto, mostramos el modal en lugar de enviar
    const overage = amount - currentBudget.remaining;
    const categoryName =
      document.querySelector(".category-card.selected")?.dataset.categoryName ||
      "esta categoría";

    budgetModalMessage.innerHTML = `Con este gasto superarás tu presupuesto para <strong>${categoryName}</strong> en <strong>${formatCurrency(
      overage
    )}</strong>.   
  
¿Estás seguro de que quieres continuar?`;

    showBudgetWarningModal();
  } else {
    // Si no se supera, procedemos a registrar el gasto directamente
    await proceedWithRegistration();
  }
}

async function proceedWithRegistration() {
  // Recolectamos los datos de nuevo (o podríamos pasarlos como parámetros)
  const amount = parseFloat(
    document.getElementById("amount").value.replace(/\./g, "")
  );
  const description = document.getElementById("description").value.trim();
  const date = document.getElementById("date").value;
  const category = document.getElementById("category").value;

  const data = {
    description: description,
    amount: amount,
    category: category,
    date: date,
  };

  try {
    const response = await fetch("../models/registro_gastos.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        Accept: "application/json",
      },
      body: JSON.stringify(data),
    });

    const result = await response.json();

    if (response.ok && result.success) {
      showSuccessMessage("¡Gasto registrado exitosamente!");
      clearForm();
      if (typeof loadExpenses === "function") {
        loadExpenses();
      }
    } else {
      throw new Error(
        result.message || "Ocurrió un error desconocido en el servidor."
      );
    }
  } catch (error) {
    console.error("Error en proceedWithRegistration:", error);
    showErrorMessage(error.message);
  } finally {
    // Nos aseguramos de que el modal se oculte si estaba abierto
    hideBudgetWarningModal();
  }
}

async function sugerirCategoria(descripcion) {
  console.log(`Buscando sugerencia para: "${descripcion}"`);
  try {
    const response = await fetch(
      `../models/reportes.php?action=sugerir_categoria&descripcion=${encodeURIComponent(
        descripcion
      )}`
    );
    const result = await response.json();

    if (result.success && result.sugerencia) {
      console.log("Sugerencia recibida:", result.sugerencia);
      // Busca la tarjeta usando el ID de categoría que devuelve el backend
      const categoryId = result.sugerencia.id_categoria;
      const categoryCard = document.querySelector(
        `.category-card[data-category-id='${categoryId}']`
      );

      if (categoryCard) {
        console.log("Aplicando sugerencia...");
        // Llama a selectCategory con el elemento encontrado.
        // La función se encargará de leer el 'data-category-name' y guardarlo.
        selectCategory(categoryCard);
        categoryCard.scrollIntoView({ behavior: "smooth", block: "nearest" });
      }
    } else {
      console.log("No se encontró sugerencia.");
    }
  } catch (error) {
    console.error("Error al sugerir categoría:", error);
  }
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

async function loadExpenses() {
  try {
    const response = await fetch("../models/obtener_gastos.php", {
      method: "POST",
    });

    if (!response.ok) {
      throw new Error(`Error HTTP: ${response.status}`);
    }

    const result = await response.json();

    if (result.success) {
      if (Array.isArray(result.recent_expenses)) {
        updateRecentExpenses(result.recent_expenses);
      } else {
        console.error(
          "recent_expenses no es un arreglo:",
          result.recent_expenses
        );
      }
      if (result.stats) {
        updateStats(result.stats);
      }
    } else {
      showErrorMessage(result.message || "Error al cargar los gastos.");
    }
  } catch (error) {
    console.error("Error en loadExpenses:", error);
    showErrorMessage("Error de conexión al cargar los gastos.");
  }
}

function updateRecentExpenses(expenses) {
  const recentExpensesContainer = document.getElementById("recentExpenses");
  recentExpensesContainer.innerHTML = "";
  if (!Array.isArray(expenses) || expenses.length === 0) {
    recentExpensesContainer.innerHTML = `
      <div class="text-center py-16 text-gray-500">
        No hay gastos recientes para mostrar.
      </div>
    `;
    recentExpensesContainer.style.height = "auto";
    return;
  }
  recentExpensesContainer.style.height = "";

  expenses.forEach((expense) => {
    const date = new Date(expense.fecha_gasto);
    const today = new Date();
    const diffDays = Math.floor((today - date) / (1000 * 60 * 60 * 24));
    const dateText =
      diffDays === 0 ? "Hoy" : diffDays === 1 ? "Ayer" : `${diffDays} días`;

    const iconPath = expense.categoria_icono || "assets/icons/default.svg";
    const iconHTML = `<img src="${iconPath}" alt="${expense.categoria_nombre}" class="w-6 h-6">`;
    const categoryName = expense.categoria_nombre || "Sin Categoría";

    const newExpense = document.createElement("div");
    newExpense.className =
      "flex items-center justify-between p-4 bg-red-50 rounded-xl transform scale-95 opacity-0 transition-all duration-300";

    newExpense.innerHTML = `
      <div class="flex items-center space-x-3">
        <div class="w-10 h-10 bg-red-100 rounded-full flex items-center justify-center">
          ${iconHTML}
        </div>
        <div>
          <p class="font-medium text-gray-800">${expense.descripcion}</p>
          <p class="text-sm text-gray-600">${dateText} - ${categoryName}</p>
        </div>
      </div>
      <p class="font-semibold text-red-600">-${parseFloat(
        expense.monto
      ).toLocaleString("es-CO")}</p>
    `;

    recentExpensesContainer.appendChild(newExpense);
    setTimeout(() => {
      newExpense.classList.remove("scale-95", "opacity-0");
      newExpense.classList.add("scale-100", "opacity-100");
    }, 100);
  });
}

// registro_gastos.js

function updateStats(stats) {
  // Actualizar gastos de hoy
  const statsTodayEl = document.getElementById("statsToday");
  if (statsTodayEl) {
    statsTodayEl.textContent = `$${parseFloat(stats.today || 0).toLocaleString(
      "es-CO"
    )}`;
  }

  // Actualizar gastos del mes
  const statsMonthEl = document.getElementById("statsMonth");
  if (statsMonthEl) {
    statsMonthEl.textContent = `$${parseFloat(stats.month || 0).toLocaleString(
      "es-CO"
    )}`;
  }

  // Actualizar presupuesto restante
  const statsBudgetRemainingEl = document.getElementById(
    "statsBudgetRemaining"
  );
  if (statsBudgetRemainingEl) {
    statsBudgetRemainingEl.textContent = `$${parseFloat(
      stats.budget_remaining || 0
    ).toLocaleString("es-CO")}`;
  }

  // Actualizar barra de progreso
  const progressBarEl = document.getElementById("statsProgressBar");
  if (progressBarEl) {
    const progressBarWidth = Math.min(stats.budget_used_percent || 0, 100);
    progressBarEl.style.width = `${progressBarWidth}%`;
  }

  // Actualizar el texto del porcentaje
  const budgetPercentTextEl = document.getElementById("statsBudgetPercentText");
  if (budgetPercentTextEl) {
    budgetPercentTextEl.textContent = `${Math.round(
      stats.budget_used_percent || 0
    )}% del presupuesto utilizado`;
  }
}

function quickExpense(category, amount) {
  const categoryNames = {
    alimentacion: "Comida rápida",
    transporte: "Transporte público",
    entretenimiento: "Entretenimiento",
    otros: "Gasto rápido",
  };

  document.getElementById("amount").value = amount.toLocaleString("es-CO");
  document.getElementById("description").value = categoryNames[category];

  const categoryCards = document.querySelectorAll(".category-card");
  categoryCards.forEach((card) => {
    card.classList.remove("selected", "border-red-500");
    card.classList.add("border-gray-200");
    if (card.onclick.toString().includes(category)) {
      card.classList.add("selected", "border-red-500");
      card.classList.remove("border-gray-200");
    }
  });

  selectedCategory = category;
  document.getElementById("category").value = category;
  document.querySelector("form").scrollIntoView({ behavior: "smooth" });
}

function handleAmountChange() {
  if (!currentBudget) {
    return; // Si no hay presupuesto para esta categoría, no hacemos nada
  }
  updateBudgetHelper(); // Llama a la función para que recalcule y muestre el estado
}
function updateBudgetHelper() {
  if (!currentBudget) {
    budgetHelper.innerHTML = "";
    amountInput.classList.remove("border-red-500", "ring-2", "ring-red-200");
    return;
  }

  const amount = parseFloat(amountInput.value.replace(/\./g, "")) || 0;
  const remainingAfterExpense = currentBudget.remaining - amount;

  if (amount > currentBudget.remaining) {
    // El usuario se está pasando del presupuesto
    const overage = amount - currentBudget.remaining;
    budgetHelper.innerHTML = `
            <span class="text-red-600 font-semibold">
                ¡Ojo! Superarás el presupuesto en ${formatCurrency(overage)}.
            </span>
        `;
    amountInput.classList.add("border-red-500", "ring-2", "ring-red-200");
  } else {
    // El usuario todavía tiene presupuesto
    budgetHelper.innerHTML = `
            <span class="text-green-600">
                Te quedarían ${formatCurrency(
                  remainingAfterExpense
                )} de este presupuesto.
            </span>
        `;
    amountInput.classList.remove("border-red-500", "ring-2", "ring-red-200");
  }
}

function showBudgetWarningModal() {
  budgetWarningModal.classList.remove("hidden");
  setTimeout(
    () =>
      budgetWarningModal
        .querySelector(".transform")
        .classList.remove("scale-95"),
    10
  );
}

function hideBudgetWarningModal() {
  budgetWarningModal.querySelector(".transform").classList.add("scale-95");
  setTimeout(() => budgetWarningModal.classList.add("hidden"), 300);
}

cancelBudgetWarningBtn.addEventListener("click", hideBudgetWarningModal);

confirmBudgetWarningBtn.addEventListener("click", async () => {
  // Si el usuario confirma, llamamos a la función que realmente registra el gasto
  await proceedWithRegistration();
});

const formatCurrency = (value) => {
  return new Intl.NumberFormat("es-CO", {
    style: "currency",
    currency: "COP",
    minimumFractionDigits: 0,
    maximumFractionDigits: 0,
  }).format(value);
};

amountInput.addEventListener("input", handleAmountChange);

// Cargar gastos al iniciar
$(document).ready(function () {
  loadExpenses();
  loadCategories(); // <<---  Carga las categorías al iniciar la página
});

// Keyboard shortcuts
document.addEventListener("keydown", function (e) {
  if (e.ctrlKey && e.key === "Enter") {
    e.preventDefault();
    document.querySelector("form").dispatchEvent(new Event("submit"));
  }
  if (e.ctrlKey && e.key === "r") {
    e.preventDefault();
    clearForm();
  }
});

// Tooltips
document.querySelector('button[type="submit"]').title =
  "Ctrl + Enter para enviar";
document.querySelector('button[onclick="clearForm()"]').title =
  "Ctrl + R para limpiar";
