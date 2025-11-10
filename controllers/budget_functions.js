// Variables globales
let budgets = [];
let budgetChart = null; // Variable para almacenar la instancia del gráfico
let expenses = {}; // Variable para almacenar gastos por categoría

// Función para mostrar el modal de agregar presupuesto
function showAddBudgetModal() {
  const modal = document.getElementById("addBudgetModal");
  modal.classList.remove("hidden");

  // Establecer fecha de inicio por defecto (hoy)
  const today = new Date().toISOString().split("T")[0];
  document.getElementById("newBudgetStartDate").value = today;

  // Establecer fecha de fin por defecto (fin del mes)
  const endOfMonth = new Date();
  endOfMonth.setMonth(endOfMonth.getMonth() + 1);
  endOfMonth.setDate(0);
  document.getElementById("newBudgetEndDate").value = endOfMonth
    .toISOString()
    .split("T")[0];
}

// Función para ocultar el modal de agregar presupuesto
function hideAddBudgetModal() {
  const modal = document.getElementById("addBudgetModal");
  modal.classList.add("hidden");

  // Limpiar el formulario
  document.getElementById("addBudgetForm").reset();
}

// Función para formatear números como moneda colombiana
function formatCurrency(amount) {
  return new Intl.NumberFormat("es-CO", {
    style: "currency",
    currency: "COP",
    minimumFractionDigits: 0,
    maximumFractionDigits: 0,
  }).format(amount);
}

// Función para formatear input de moneda en tiempo real
function formatCurrencyInput(input) {
  let value = input.value.replace(/[^\d]/g, "");
  if (value) {
    value = parseInt(value).toLocaleString("es-CO");
    input.value = value;
  }
}

// Función para obtener el valor numérico de un input formateado
function getNumericValue(formattedValue) {
  return parseInt(formattedValue.replace(/[^\d]/g, "")) || 0;
}

// Función para agregar una nueva categoría de presupuesto
function addBudgetCategory(event) {
  event.preventDefault();

  // Obtener valores del formulario
  const category = document.getElementById("newBudgetCategory").value;
  const amountInput = document.getElementById("newBudgetAmount").value;
  const period = document.getElementById("newBudgetPeriod").value;
  const startDate = document.getElementById("newBudgetStartDate").value;
  const endDate = document.getElementById("newBudgetEndDate").value;

  // Validaciones básicas
  if (!category || !amountInput || !period || !startDate || !endDate) {
    showAlert("Por favor, completa todos los campos", "error");
    return;
  }

  const amount = getNumericValue(amountInput);
  if (amount <= 0) {
    showAlert("El monto debe ser mayor a 0", "error");
    return;
  }

  if (new Date(startDate) >= new Date(endDate)) {
    showAlert(
      "La fecha de fin debe ser posterior a la fecha de inicio",
      "error"
    );
    return;
  }

  // Mostrar indicador de carga
  const submitButton = event.target.querySelector('button[type="submit"]');
  const originalText = submitButton.textContent;
  submitButton.textContent = "Agregando...";
  submitButton.disabled = true;

  // Preparar datos para enviar
  const formData = new FormData();
  formData.append("action", "add_budget");
  formData.append("category", category);
  formData.append("amount", amount);
  formData.append("period", period);
  formData.append("start_date", startDate);
  formData.append("end_date", endDate);

  // Enviar datos via AJAX
  fetch("../models/budget_operations.php", {
    method: "POST",
    body: formData,
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        showAlert("Presupuesto agregado exitosamente", "success");
        hideAddBudgetModal();
        loadBudgets(); // Recargar la lista de presupuestos
      } else {
        showAlert(data.message || "Error al agregar el presupuesto", "error");
      }
    })
    .catch((error) => {
      console.error("Error:", error);
      showAlert("Error de conexión. Inténtalo de nuevo.", "error");
    })
    .finally(() => {
      // Restaurar botón
      submitButton.textContent = originalText;
      submitButton.disabled = false;
    });
}

// Función para cargar presupuestos existentes con gastos
function loadBudgets() {
  fetch("../models/budget_operations.php?action=get_budgets")
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        budgets = data.budgets;
        updateBudgetDisplay(data.totals);
      } else {
        console.error("Error al cargar presupuestos:", data.message);
      }
    })
    .catch((error) => {
      console.error("Error:", error);
    });
}

// Función para actualizar la visualización de presupuestos con gastos
function updateBudgetDisplay(totals) {
  // Actualizar resumen general
  updateBudgetSummary(totals);

  // Actualizar categorías individuales
  updateBudgetCategories();

  // Actualizar gráfico
  updateBudgetChart();
}

// Función para actualizar el resumen general de presupuestos
function updateBudgetSummary(totals) {
  if (!totals) return;

  const totalBudget = totals.total_presupuesto || 0;
  const totalUsed = totals.total_gastado || 0;
  const totalRemaining = totals.total_restante || 0;

  // Actualizar elementos del DOM
  const totalBudgetElements = document.querySelectorAll(
    ".budget-card .text-3xl.text-indigo-600"
  );
  const totalUsedElements = document.querySelectorAll(
    ".budget-card .text-3xl.text-red-600"
  );
  const totalRemainingElements = document.querySelectorAll(
    ".budget-card .text-3xl.text-emerald-600"
  );

  if (totalBudgetElements.length > 0) {
    totalBudgetElements[0].textContent = formatCurrency(totalBudget);
  }
  if (totalUsedElements.length > 0) {
    totalUsedElements[0].textContent = formatCurrency(totalUsed);
  }
  if (totalRemainingElements.length > 0) {
    totalRemainingElements[0].textContent = formatCurrency(totalRemaining);
  }

  // Actualizar barra de progreso general si existe
  updateProgressBars(totals);
}

// Función para actualizar barras de progreso generales
function updateProgressBars(totals) {
  const totalBudget = totals.total_presupuesto || 0;
  const totalUsed = totals.total_gastado || 0;
  const totalRemaining = totals.total_restante || 0;

  // Actualizar elementos de progreso si existen
  const progressTotalBudget = document.getElementById("progressTotalBudget");
  const progressUsedBudget = document.getElementById("progressUsedBudget");
  const progressRemainingBudget = document.getElementById(
    "progressRemainingBudget"
  );

  const progressTotalBar = document.getElementById("progressTotalBar");
  const progressUsedBar = document.getElementById("progressUsedBar");
  const progressRemainingBar = document.getElementById("progressRemainingBar");

  if (progressTotalBudget)
    progressTotalBudget.textContent = formatCurrency(totalBudget);
  if (progressUsedBudget)
    progressUsedBudget.textContent = formatCurrency(totalUsed);
  if (progressRemainingBudget)
    progressRemainingBudget.textContent = formatCurrency(totalRemaining);

  if (totalBudget > 0) {
    const usedPercentage = (totalUsed / totalBudget) * 100;
    const remainingPercentage = (totalRemaining / totalBudget) * 100;

    if (progressUsedBar)
      progressUsedBar.style.width = `${Math.min(usedPercentage, 100)}%`;
    if (progressRemainingBar)
      progressRemainingBar.style.width = `${Math.min(
        remainingPercentage,
        100
      )}%`;
  }
}

// Función para actualizar las categorías de presupuesto con gastos reales
function updateBudgetCategories() {
  const categoryIcons = {
    alimentacion: "🍽️",
    transporte: "🚗",
    servicios: "💡",
    entretenimiento: "🎬",
    salud: "🏥",
    otros: "📦",
  };

  const categoryNames = {
    alimentacion: "Alimentación",
    transporte: "Transporte",
    servicios: "Servicios",
    entretenimiento: "Entretenimiento",
    salud: "Salud",
    otros: "Otros",
  };

  // Crear un objeto con los presupuestos y gastos por categoría
  const budgetsByCategory = {};
  const expensesByCategory = {};

  budgets.forEach((budget) => {
    const category = budget.categoria;
    if (!budgetsByCategory[category]) {
      budgetsByCategory[category] = 0;
      expensesByCategory[category] = 0;
    }
    budgetsByCategory[category] += parseFloat(budget.monto);
    expensesByCategory[category] += parseFloat(budget.gastos || 0);
  });

  // Actualizar cada categoría en el DOM
  Object.keys(categoryNames).forEach((category) => {
    const budgetAmount = budgetsByCategory[category] || 0;
    const usedAmount = expensesByCategory[category] || 0;
    const percentage = budgetAmount > 0 ? (usedAmount / budgetAmount) * 100 : 0;

    // Buscar el elemento de la categoría
    const categoryElement = document.querySelector(
      `[data-category="${category}"]`
    );
    if (categoryElement) {
      // Actualizar monto gastado (el número principal)
      const amountElement = categoryElement.querySelector(
        ".text-sm.font-semibold"
      );
      if (amountElement) {
        amountElement.textContent = formatCurrency(usedAmount);
      }

      // Actualizar porcentaje
      const percentageElement =
        categoryElement.querySelector(".text-xs.px-2.py-1");
      if (percentageElement) {
        percentageElement.textContent = `${Math.round(percentage)}%`;

        // Cambiar color del badge según el porcentaje
        percentageElement.className = "text-xs px-2 py-1 rounded-full";
        if (percentage >= 100) {
          percentageElement.classList.add("bg-red-100", "text-red-800");
        } else if (percentage >= 80) {
          percentageElement.classList.add("bg-yellow-100", "text-yellow-800");
        } else if (percentage >= 50) {
          percentageElement.classList.add("bg-blue-100", "text-blue-800");
        } else {
          percentageElement.classList.add("bg-green-100", "text-green-800");
        }
      }

      // Actualizar barra de progreso
      const progressBar = categoryElement.querySelector(".progress-fill");
      if (progressBar) {
        const clampedPercentage = Math.min(percentage, 100);
        progressBar.style.width = `${clampedPercentage}%`;

        // Cambiar color de la barra según el porcentaje
        progressBar.className = "progress-fill";
        if (percentage >= 100) {
          progressBar.classList.add("bg-red-500");
        } else if (percentage >= 80) {
          progressBar.classList.add("bg-yellow-500");
        } else {
          // Mantener el color original de la categoría
          const categoryColors = {
            alimentacion: "bg-red-500",
            transporte: "bg-orange-500",
            servicios: "bg-yellow-500",
            entretenimiento: "bg-green-500",
            salud: "bg-blue-500",
            otros: "bg-purple-500",
          };
          progressBar.classList.add(categoryColors[category] || "bg-gray-500");
        }
      }

      // Actualizar información adicional
      const infoElements = categoryElement.querySelectorAll(
        ".text-xs.text-gray-500"
      );
      if (infoElements.length >= 2) {
        infoElements[0].textContent = `Gastado: ${formatCurrency(usedAmount)}`;
        infoElements[1].textContent = `Presupuesto: ${formatCurrency(
          budgetAmount
        )}`;
      }
    }
  });
}

// Función para actualizar el gráfico de presupuesto con gastos
function updateBudgetChart() {
  const canvas = document.getElementById("budgetChart");
  if (!canvas) return;

  const ctx = canvas.getContext("2d");
  const chartType =
    document.getElementById("budgetChartType")?.value || "doughnut";

  // Preparar datos para el gráfico
  const chartData = prepareBudgetChartDataWithExpenses();

  // Si no hay datos, mostrar mensaje
  if (chartData.labels.length === 0) {
    showEmptyChartMessage();
    return;
  }

  // Destruir gráfico existente si existe
  if (budgetChart) {
    budgetChart.destroy();
  }

  // Configuración del gráfico
  const config = {
    type: chartType,
    data: chartData,
    options: getChartOptionsWithExpenses(chartType),
  };

  // Crear nuevo gráfico
  budgetChart = new Chart(ctx, config);

  // Ocultar mensaje de gráfico vacío si existe
  hideEmptyChartMessage();
}

// Función para preparar los datos del gráfico con gastos
function prepareBudgetChartDataWithExpenses() {
  const categoryNames = {
    alimentacion: "Alimentación",
    transporte: "Transporte",
    servicios: "Servicios",
    entretenimiento: "Entretenimiento",
    salud: "Salud",
    otros: "Otros",
  };

  const categoryColors = {
    alimentacion: "#EF4444",
    transporte: "#F97316",
    servicios: "#EAB308",
    entretenimiento: "#22C55E",
    salud: "#3B82F6",
    otros: "#8B5CF6",
  };

  // Agrupar presupuestos y gastos por categoría
  const budgetsByCategory = {};
  const expensesByCategory = {};

  budgets.forEach((budget) => {
    const category = budget.categoria;
    if (!budgetsByCategory[category]) {
      budgetsByCategory[category] = 0;
      expensesByCategory[category] = 0;
    }
    budgetsByCategory[category] += parseFloat(budget.monto);
    expensesByCategory[category] += parseFloat(budget.gastos || 0);
  });

  // Preparar arrays para el gráfico
  const labels = [];
  const budgetData = [];
  const expenseData = [];
  const backgroundColor = [];
  const borderColor = [];

  Object.keys(budgetsByCategory).forEach((category) => {
    if (budgetsByCategory[category] > 0) {
      labels.push(categoryNames[category] || category);
      budgetData.push(budgetsByCategory[category]);
      expenseData.push(expensesByCategory[category]);
      backgroundColor.push(categoryColors[category] || "#6B7280");
      borderColor.push("#FFFFFF");
    }
  });

  // Determinar si mostrar solo presupuesto o presupuesto vs gastos
  const showComparison = expenseData.some((expense) => expense > 0);

  if (showComparison) {
    return {
      labels: labels,
      datasets: [
        {
          label: "Presupuesto",
          data: budgetData,
          backgroundColor: backgroundColor.map((color) => color + "80"), // Más transparente
          borderColor: borderColor,
          borderWidth: 2,
        },
        {
          label: "Gastado",
          data: expenseData,
          backgroundColor: backgroundColor,
          borderColor: borderColor,
          borderWidth: 2,
        },
      ],
    };
  } else {
    return {
      labels: labels,
      datasets: [
        {
          label: "Presupuesto",
          data: budgetData,
          backgroundColor: backgroundColor,
          borderColor: borderColor,
          borderWidth: 2,
        },
      ],
    };
  }
}

// Función para obtener opciones del gráfico con gastos
function getChartOptionsWithExpenses(chartType) {
  const baseOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
      legend: {
        position: "bottom",
        labels: {
          padding: 20,
          usePointStyle: true,
          font: {
            size: 12,
          },
        },
      },
      tooltip: {
        callbacks: {
          label: function (context) {
            const value = formatCurrency(context.parsed);
            if (context.dataset.label === "Gastado") {
              // Calcular porcentaje del presupuesto usado
              const budgetDataset = context.chart.data.datasets.find(
                (d) => d.label === "Presupuesto"
              );
              if (budgetDataset) {
                const budgetValue = budgetDataset.data[context.dataIndex];
                const percentage =
                  budgetValue > 0
                    ? ((context.parsed / budgetValue) * 100).toFixed(1)
                    : 0;
                return `${context.dataset.label}: ${value} (${percentage}% del presupuesto)`;
              }
            }
            return `${context.dataset.label}: ${value}`;
          },
        },
      },
    },
  };

  if (chartType === "bar") {
    return {
      ...baseOptions,
      scales: {
        y: {
          beginAtZero: true,
          ticks: {
            callback: function (value) {
              return formatCurrency(value);
            },
          },
        },
      },
    };
  }

  return baseOptions;
}

// Función para mostrar mensaje cuando no hay datos en el gráfico
function showEmptyChartMessage() {
  const canvas = document.getElementById("budgetChart");
  if (!canvas) return;

  const container = canvas.parentElement;
  let emptyMessage = container.querySelector(".empty-chart-message");

  if (!emptyMessage) {
    emptyMessage = document.createElement("div");
    emptyMessage.className =
      "empty-chart-message h-64 flex items-center justify-center text-gray-500";
    emptyMessage.innerHTML = "<p>Agrega presupuestos para ver el gráfico</p>";
    container.appendChild(emptyMessage);
  }

  canvas.style.display = "none";
  emptyMessage.style.display = "flex";
}

// Función para ocultar mensaje de gráfico vacío
function hideEmptyChartMessage() {
  const canvas = document.getElementById("budgetChart");
  if (!canvas) return;

  const container = canvas.parentElement;
  const emptyMessage = container.querySelector(".empty-chart-message");

  if (emptyMessage) {
    emptyMessage.style.display = "none";
  }

  canvas.style.display = "block";
}

// Función para cambiar el tipo de gráfico
function changeBudgetChartType() {
  updateBudgetChart();
}

// Función para mostrar alertas
function showAlert(message, type = "info") {
  // Crear elemento de alerta
  const alert = document.createElement("div");
  alert.className = `fixed top-4 right-4 z-50 p-4 rounded-lg shadow-lg max-w-sm ${
    type === "success"
      ? "bg-green-100 text-green-800 border border-green-200"
      : type === "error"
      ? "bg-red-100 text-red-800 border border-red-200"
      : "bg-blue-100 text-blue-800 border border-blue-200"
  }`;

  alert.innerHTML = `
        <div class="flex items-center">
            <div class="flex-shrink-0">
                ${
                  type === "success"
                    ? '<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>'
                    : type === "error"
                    ? '<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path></svg>'
                    : '<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path></svg>'
                }
            </div>
            <div class="ml-3">
                <p class="text-sm font-medium">${message}</p>
            </div>
            <div class="ml-auto pl-3">
                <button onclick="this.parentElement.parentElement.parentElement.remove()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                    </svg>
                </button>
            </div>
        </div>
    `;

  document.body.appendChild(alert);

  // Remover automáticamente después de 5 segundos
  setTimeout(() => {
    if (alert.parentElement) {
      alert.remove();
    }
  }, 5000);
}

// Event listeners
document.addEventListener("DOMContentLoaded", function () {
  // Cargar presupuestos al cargar la página
  loadBudgets();

  // Formatear input de moneda en tiempo real
  const amountInput = document.getElementById("newBudgetAmount");
  if (amountInput) {
    amountInput.addEventListener("input", function () {
      formatCurrencyInput(this);
    });
  }

  // Cerrar modal al hacer clic fuera de él
  const modal = document.getElementById("addBudgetModal");
  if (modal) {
    modal.addEventListener("click", function (e) {
      if (e.target === modal) {
        hideAddBudgetModal();
      }
    });
  }

  // Cerrar modal con tecla Escape
  document.addEventListener("keydown", function (e) {
    if (e.key === "Escape") {
      hideAddBudgetModal();
    }
  });

  // Event listener para cambio de tipo de gráfico
  const chartTypeSelect = document.getElementById("budgetChartType");
  if (chartTypeSelect) {
    chartTypeSelect.addEventListener("change", changeBudgetChartType);
  }
});

// Función para actualizar fechas automáticamente según el período seleccionado
function updateDatesBasedOnPeriod() {
  const periodSelect = document.getElementById("newBudgetPeriod");
  const startDateInput = document.getElementById("newBudgetStartDate");
  const endDateInput = document.getElementById("newBudgetEndDate");

  if (periodSelect && startDateInput && endDateInput) {
    periodSelect.addEventListener("change", function () {
      const period = this.value;
      const startDate = new Date(startDateInput.value || new Date());
      let endDate = new Date(startDate);

      switch (period) {
        case "diario":
          endDate = new Date(startDate);
          break;
        case "semanal":
          endDate.setDate(startDate.getDate() + 6);
          break;
        case "quincenal":
          endDate.setDate(startDate.getDate() + 14);
          break;
        case "mensual":
          endDate.setMonth(startDate.getMonth() + 1);
          endDate.setDate(0); // Último día del mes
          break;
        case "anual":
          endDate.setFullYear(startDate.getFullYear() + 1);
          endDate.setDate(startDate.getDate() - 1);
          break;
      }

      endDateInput.value = endDate.toISOString().split("T")[0];
    });
  }
}

// Inicializar actualización automática de fechas
document.addEventListener("DOMContentLoaded", updateDatesBasedOnPeriod);
