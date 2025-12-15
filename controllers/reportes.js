$(document).ready(function () {
  loadDashboardData();
  let currentPeriod = "month";
  let categoryChart, monthlyTrendChart;
  let transactionsCurrentPage = 1; // <-- NUEVO: Estado para la página actual

  cargarGastosRecurrentes();
  // Cargar jQuery, Chart.js y SheetJS
  $.getScript("https://code.jquery.com/jquery-3.6.0.min.js", function () {
    $.getScript("https://cdn.jsdelivr.net/npm/chart.js", function () {
      $.getScript(
        "https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.16.9/xlsx.full.min.js",
        function () {
          initializeCharts();
          cargarDatos();
        }
      );
    });
  });

  // BONUS: Función auxiliar para verificar los datos recibidos
  function debugGastosRecurrentes(data) {
    console.log("=== DEBUG GASTOS RECURRENTES ===");
    console.log("Mensuales:", data.mensuales);
    console.log("Semanales:", data.semanales);

    data.mensuales.forEach((g, i) => {
      console.log(`Mensual ${i}:`, {
        descripcion: g.descripcion_base,
        monto: g.monto_promedio,
        repeticiones: g.repeticiones,
        tipo_repeticiones: typeof g.repeticiones,
      });
    });
  }

  // Inicializar gráficos
  function initializeCharts() {
    const categoryCtx = $("#categoryChart")[0].getContext("2d");
    categoryChart = new Chart(categoryCtx, {
      type: "pie",
      data: {
        labels: [],
        datasets: [
          {
            data: [],
            backgroundColor: [
              "#ef4444",
              "#f97316",
              "#eab308",
              "#22c55e",
              "#3b82f6",
              "#a855f7",
            ],
            borderWidth: 1,
          },
        ],
      },
      options: {
        responsive: true,
        plugins: {
          legend: { position: "right" },
          tooltip: {
            callbacks: {
              label: function (context) {
                let label = context.label || "";
                if (label) label += ": ";
                label += "$" + context.raw.toLocaleString("es-CO");
                label +=
                  " (" +
                  Math.round(
                    (context.parsed * 100) /
                      context.dataset.data.reduce((a, b) => a + b, 0)
                  ) +
                  "%)";
                return label;
              },
            },
          },
        },
      },
    });

    const trendCtx = $("#monthlyTrendChart")[0].getContext("2d");
    monthlyTrendChart = new Chart(trendCtx, {
      type: "line",
      data: {
        labels: [],
        datasets: [
          {
            label: "Ingresos",
            data: [],
            borderColor: "#10b981",
            backgroundColor: "rgba(16, 185, 129, 0.1)",
            tension: 0.3,
            fill: true,
          },
          {
            label: "Gastos",
            data: [],
            borderColor: "#ef4444",
            backgroundColor: "rgba(239, 68, 68, 0.1)",
            tension: 0.3,
            fill: true,
          },
        ],
      },
      options: {
        responsive: true,
        plugins: {
          tooltip: {
            callbacks: {
              label: function (context) {
                let label = context.dataset.label || "";
                if (label) label += ": ";
                label += "$" + context.raw.toLocaleString("es-CO");
                return label;
              },
            },
          },
        },
        scales: {
          y: {
            beginAtZero: true,
            ticks: {
              callback: function (value) {
                return "$" + value.toLocaleString("es-CO");
              },
            },
          },
        },
      },
    });

    // Cambiar tipo de gráfico
    $("#categoryChartType").change(function () {
      categoryChart.config.type = $(this).val();
      categoryChart.update();
    });
  }

  // Cargar datos iniciales
  function cargarDatos() {
    actualizarResumen();
    actualizarTransacciones();
    actualizarGastosPorCategoria();
    actualizarTendenciaMensual();
  }

  // Actualizar resumen
  function actualizarResumen() {
    $.ajax({
      url: "../models/reportes.php",
      method: "POST",
      data: {
        action: "obtener_resumen",
        periodo: currentPeriod,
        start_date: $("#startDate").val(),
        end_date: $("#endDate").val(),
      },
      dataType: "json",
      success: function (response) {
        if (response.success) {
          const resumen = response.data;
          $("#ingresosTotales").text(
            `$${resumen.ingresos.toLocaleString("es-CO")}`
          );
          $("#gastosTotales").text(
            `$${resumen.gastos.toLocaleString("es-CO")}`
          );
          $("#balanceNeto").text(`$${resumen.balance.toLocaleString("es-CO")}`);
          const ahorro = resumen.ingresos
            ? ((resumen.balance / resumen.ingresos) * 100).toFixed(1)
            : 0;
          $("#ahorroPorcentaje").text(`${ahorro}% de ahorro`);
        } else {
          alert("Error al cargar el resumen: " + response.message);
        }
      },
      error: function () {
        alert("Error en la solicitud al cargar el resumen");
      },
    });
  }

  // Actualizar tabla de transacciones

  function actualizarTransacciones() {
    const tbody = $("table tbody");
    tbody.html(
      '<tr><td colspan="5" class="text-center p-4">Cargando transacciones...</td></tr>'
    ); // Estado de carga

    $.ajax({
      url: "../models/reportes.php",
      method: "POST",
      data: {
        action: "obtener_transacciones",
        periodo: currentPeriod,
        start_date: $("#startDate").val(),
        end_date: $("#endDate").val(),
        page: transactionsCurrentPage, // <-- Enviar página actual
        limit: 8, // Coincide con el límite del backend
      },
      dataType: "json",
      success: function (response) {
        if (response.success) {
          tbody.empty();
          if (response.data.length === 0) {
            tbody.html(
              '<tr><td colspan="5" class="text-center p-4">No se encontraron transacciones.</td></tr>'
            );
          } else {
            response.data.forEach((t) => {
              const row = `
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${
                          t.fecha
                        }</td>
                        <td class="px-6 py-4 text-sm text-gray-900">${
                          t.descripcion
                        }</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${
                          t.categoria
                        }</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${
                              t.tipo === "Ingreso"
                                ? "bg-green-100 text-green-800"
                                : "bg-red-100 text-red-800"
                            }">
                                ${t.tipo}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-right ${
                          t.tipo === "Ingreso"
                            ? "text-emerald-600"
                            : "text-red-600"
                        }">
                            ${
                              t.tipo === "Ingreso" ? "+" : "-"
                            }$${t.monto.toLocaleString("es-CO")}
                            <button onclick="eliminarTransaccion(${t.id}, '${
                t.tipo
              }')" class="ml-2 text-red-600 hover:text-red-800">Eliminar</button>
                        </td>
                    </tr>`;
              tbody.append(row);
            });
          }
          // Renderizar la paginación
          renderTransactionsPagination(response.pagination);
        } else {
          tbody.html(
            `<tr><td colspan="5" class="text-center p-4 text-red-500">Error: ${response.message}</td></tr>`
          );
        }
      },
      error: function () {
        tbody.html(
          '<tr><td colspan="5" class="text-center p-4 text-red-500">Error en la solicitud al servidor.</td></tr>'
        );
      },
    });
  }

  function renderTransactionsPagination(pagination) {
    const { currentPage, totalPages, totalRows } = pagination;
    const paginationContainer = $("#paginationContainer");
    paginationContainer.empty();

    if (totalRows === 0) return;

    const from = (currentPage - 1) * pagination.limit + 1;
    const to = Math.min(from + pagination.limit - 1, totalRows);

    let paginationHTML = `
        <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
          <div>
            <p class="text-sm text-gray-700">
              Mostrando <span class="font-medium">${from}</span> a <span class="font-medium">${to}</span> de
              <span class="font-medium">${totalRows}</span> resultados
            </p>
          </div>
          <div>
            <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
              <button data-page="${currentPage - 1}" ${
      currentPage === 1 ? "disabled" : ""
    } class="pagination-btn relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 disabled:opacity-50">
                Anterior
              </button>
              <button data-page="${currentPage + 1}" ${
      currentPage >= totalPages ? "disabled" : ""
    } class="pagination-btn relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 disabled:opacity-50">
                Siguiente
              </button>
            </nav>
          </div>
        </div>`;
    paginationContainer.html(paginationHTML);
  }
  // Actualizar gastos por categoría
  function actualizarGastosPorCategoria() {
    $.ajax({
      url: "../models/reportes.php",
      method: "POST",
      data: {
        action: "obtener_gastos_por_categoria",
        periodo: currentPeriod,
        start_date: $("#startDate").val(),
        end_date: $("#endDate").val(),
      },
      dataType: "json",
      success: function (response) {
        //console.log(response);
        if (response.success) {
          const categorias = response.data;
          const labels = categorias.map((c) => c.categoria);
          const data = categorias.map((c) => c.total);
          categoryChart.data.labels = labels;
          categoryChart.data.datasets[0].data = data;
          categoryChart.update();

          // Actualizar desglose de gastos
          const desglose = $(".space-y-4");
          desglose.empty();
          const total = data.reduce((sum, val) => sum + parseFloat(val), 0);
          categorias.forEach((c) => {
            const porcentaje = total ? ((c.total / total) * 100).toFixed(1) : 0;
            const div = `
                                <div>
                                    <div class="flex justify-between items-center mb-1">
                                        <span class="text-sm font-medium text-gray-700">${
                                          c.categoria
                                        }</span>
                                        <span class="text-sm font-semibold text-red-600">$${c.total.toLocaleString(
                                          "es-CO"
                                        )} (${porcentaje}%)</span>
                                    </div>
                                    <div class="progress-bar">
                                        <div class="progress-fill bg-red-500" style="width: ${porcentaje}%"></div>
                                    </div>
                                </div>`;
            desglose.append(div);
          });
        } else {
          alert("Error al cargar gastos por categoría: " + response.message);
        }
      },
      error: function () {
        alert("Error en la solicitud al cargar gastos por categoría");
      },
    });
  }

  // Actualizar tendencia mensual
  function actualizarTendenciaMensual() {
    $.ajax({
      url: "../models/reportes.php",
      method: "GET",
      data: { action: "obtener_tendencia_mensual" },
      dataType: "json",
      success: function (response) {
        console.log(response);
        if (response.success) {
          const tendencia = response.data;
          const labels = tendencia.map((t) => t.mes);
          const ingresos = tendencia.map((t) => t.ingresos);
          const gastos = tendencia.map((t) => t.gastos);
          monthlyTrendChart.data.labels = labels;
          monthlyTrendChart.data.datasets[0].data = ingresos;
          monthlyTrendChart.data.datasets[1].data = gastos;
          monthlyTrendChart.update();
        } else {
          alert("Error al cargar tendencia mensual: " + response.message);
        }
      },
      error: function () {
        alert("Error en la solicitud al cargar tendencia mensual");
      },
    });
  }
  // Función principal para cargar todos los datos en los cards
  async function loadDashboardData() {
    try {
      const response = await fetch("../models/dashboard.php"); // Ajusta la ruta si es necesario
      if (!response.ok)
        throw new Error("La respuesta de la red no fue correcta.");

      const result = await response.json();
      if (!result.success) throw new Error(result.message);

      // Llamar a las funciones para actualizar cada sección con los datos recibidos
      updateSummaryCards(result.data.cards);
      renderMonthlyChart(result.data.monthlyTrend);
      renderRecentTransactions(result.data.recentTransactions);
    } catch (error) {
      console.error("Error al cargar los datos del dashboard:", error);
      // Aquí mostrar un mensaje de error en la UI
    }
  }

  // Función para actualizar las 3 tarjetas de resumen
  function updateSummaryCards(cards) {
    const formatCurrency = (value) =>
      `$${new Intl.NumberFormat("es-CO").format(value)}`;

    document.getElementById("ingresosMes").textContent = formatCurrency(
      cards.ingresosMes
    );
    document.getElementById("gastosMes").textContent = formatCurrency(
      cards.gastosMes
    );
    document.getElementById("balanceMes").textContent = formatCurrency(
      cards.balanceMes
    );

    const pIngresos = document.getElementById("ingresosMesAnterior");
    pIngresos.textContent = `${cards.porcentajeIngresos.toFixed(
      1
    )}% vs mes anterior`;
    pIngresos.className = `text-xs sm:text-sm mt-1 ${
      cards.porcentajeIngresos >= 0 ? "text-emerald-500" : "text-red-500"
    }`;

    const pGastos = document.getElementById("gastosMesAnterior");
    pGastos.textContent = `${cards.porcentajeGastos.toFixed(
      1
    )}% vs mes anterior`;
    pGastos.className = `text-xs sm:text-sm mt-1 ${
      cards.porcentajeGastos >= 0 ? "text-red-500" : "text-emerald-500"
    }`;

    document.getElementById(
      "porcentajeAhorro"
    ).textContent = `${cards.porcentajeAhorro.toFixed(1)}% de ahorro`;
  }

  // Listener para los botones de paginación (delegación de eventos)
  $(document).on("click", "#paginationContainer .pagination-btn", function () {
    if ($(this).is(":disabled")) return;
    transactionsCurrentPage = parseInt($(this).data("page"));
    actualizarTransacciones();
  });

  // Agregar transacción
  $("#formTransaccion").submit(function (e) {
    e.preventDefault();
    const fecha = $("#fecha").val();
    const descripcion = $("#descripcion").val();
    const categoria = $("#categoria").val();
    const tipo = $("#tipo").val();
    const monto = parseFloat($("#monto").val());

    // Validaciones
    if (!fecha) {
      alert("La fecha es obligatoria");
      return;
    }
    const today = new Date().toISOString().split("T")[0];
    if (fecha > today) {
      alert("La fecha no puede ser futura");
      return;
    }
    if (!descripcion || descripcion.length < 3) {
      alert("La descripción debe tener al menos 3 caracteres");
      return;
    }
    if (!categoria) {
      alert("La categoría es obligatoria");
      return;
    }
    if (!tipo || !["Ingreso", "Gasto"].includes(tipo)) {
      alert("El tipo de transacción es inválido");
      return;
    }
    if (!monto || monto <= 0) {
      alert("El monto debe ser mayor a 0");
      return;
    }

    $.ajax({
      url: "../models/reportes.php",
      method: "POST",
      data: {
        action: "agregar_transaccion", //Aun no esta disponible
        fecha,
        descripcion,
        categoria,
        tipo,
        monto,
      },
      dataType: "json",
      success: function (response) {
        if (response.success) {
          $("#formTransaccion").trigger("reset");
          $("#success-modal").removeClass("hidden");
          cargarDatos();
        } else {
          alert("Error al agregar transacción: " + response.message);
        }
      },
      error: function () {
        alert("Error en la solicitud al agregar transacción");
      },
    });
  });

  // Eliminar transacción
  window.eliminarTransaccion = function (id) {
    if (confirm("¿Estás seguro de eliminar esta transacción?")) {
      $.ajax({
        url: "../models/reportes.php",
        method: "POST",
        data: { action: "eliminar_transaccion", id: id }, //aun no esta disponible
        dataType: "json",
        success: function (response) {
          if (response.success) {
            alert(response.message);
            cargarDatos();
          } else {
            alert("Error al eliminar transacción: " + response.message);
          }
        },
        error: function () {
          alert("Error en la solicitud al eliminar transacción");
        },
      });
    }
  };

  // Cambiar período
  window.changePeriod = function (period) {
    currentPeriod = period;
    transactionsCurrentPage = 1;
    $(".period-btn")
      .removeClass("active bg-indigo-500 text-white")
      .addClass("bg-white border border-gray-300");
    $(`button[onclick="changePeriod('${period}')"]`)
      .removeClass("bg-white border border-gray-300")
      .addClass("active bg-indigo-500 text-white");
    cargarDatos();
  };

  // Mostrar modal
  window.showCustomDateModal = function () {
    $("#customDateModal").removeClass("hidden");
  };

  // Ocultar modal
  window.hideCustomDateModal = function () {
    $("#customDateModal").addClass("hidden");
  };

  // Aplicar fechas personalizadas
  window.applyCustomDate = function () {
    const startDate = $("#startDate").val();
    const endDate = $("#endDate").val();

    if (!startDate || !endDate) {
      alert("Por favor selecciona ambas fechas");
      return;
    }
    if (startDate > endDate) {
      alert("La fecha de inicio no puede ser posterior a la fecha de fin");
      return;
    }

    currentPeriod = "custom";
    $(".period-btn")
      .removeClass("active bg-indigo-500 text-white")
      .addClass("bg-white border border-gray-300");
    cargarDatos();
    hideCustomDateModal();
  };

  // Exportar a Excel

  window.exportToExcel = function () {
    // Muestra un feedback visual al usuario
    const originalButtonText = "<span>Exportar</span>";
    const exportButton = $('button[onclick="exportToExcel()"]');
    exportButton.prop("disabled", true).html(`
        <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        <span>Exportando...</span>`);

    $.ajax({
      url: "../models/reportes.php",
      method: "POST", // Cambiado a POST para consistencia
      data: {
        action: "obtener_transacciones",
        periodo: currentPeriod,
        start_date: $("#startDate").val(),
        end_date: $("#endDate").val(),
        limit: -1,
      },
      dataType: "json",
      success: function (response) {
        console.log("Registros recibidos para exportar:", response.data.length);
        if (response.success && response.data.length > 0) {
          // Mapear los datos para que tengan nombres de columna más amigables
          const dataToExport = response.data.map((t) => ({
            Fecha: t.fecha,
            Descripción: t.descripcion,
            Categoría: t.categoria,
            Tipo: t.tipo,
            Monto: t.monto,
          }));

          const ws = XLSX.utils.json_to_sheet(dataToExport);
          const wb = XLSX.utils.book_new();
          XLSX.utils.book_append_sheet(wb, ws, "Transacciones");
          XLSX.writeFile(
            wb,
            `Reporte_Transacciones_${
              new Date().toISOString().split("T")[0]
            }.xlsx`
          );
        } else if (response.data.length === 0) {
          alert("No hay datos para exportar con los filtros seleccionados.");
        } else {
          alert(
            "Error al obtener los datos para exportar: " + response.message
          );
        }
      },
      error: function () {
        alert("Error en la solicitud al servidor para exportar los datos.");
      },
      complete: function () {
        // Restaura el botón a su estado original
        exportButton.prop("disabled", false).html(originalButtonText);
      },
    });
  };
});
let activeTab = "confirmados";
let gastosData = null;
// Función principal que se debe llamar al cargar la página de reportes
async function cargarGastosRecurrentes() {
  console.log("Iniciando detección inteligente...");

  const loaderEl = document.getElementById("recurrent-loader");
  const summaryEl = document.getElementById("recurrent-summary");
  const tabsEl = document.getElementById("tabs-container");

  try {
    const response = await fetch(
      "../models/reportes.php?action=detectar_gastos_recurrentes"
    );

    if (!response.ok) throw new Error("Error de red");
    const result = await response.json();

    if (!result.success) throw new Error(result.message);

    gastosData = result.data;

    // Actualizar resumen
    actualizarResumen(gastosData.resumen);

    // Actualizar contadores en tabs
    document.getElementById(
      "count-confirmados"
    ).textContent = `(${gastosData.resumen.cantidad_confirmados})`;
    document.getElementById(
      "count-probables"
    ).textContent = `(${gastosData.resumen.cantidad_probables})`;
    document.getElementById(
      "count-por-confirmar"
    ).textContent = `(${gastosData.resumen.cantidad_por_confirmar})`;

    // Mostrar elementos
    summaryEl.classList.remove("hidden");
    tabsEl.classList.remove("hidden");
    loaderEl.style.display = "none";

    // Mostrar tab por defecto
    cambiarTab("confirmados");
  } catch (err) {
    console.error(err);
    loaderEl.textContent = "Error al analizar";
    loaderEl.classList.add("text-red-500");
  }
}

function actualizarResumen(resumen) {
  const formatCurrency = (amount) => {
    return new Intl.NumberFormat("es-CO", {
      style: "currency",
      currency: "COP",
      minimumFractionDigits: 0,
    }).format(amount);
  };

  document.getElementById("total-mensual").textContent = formatCurrency(
    resumen.total_mensual_confirmado
  );
  document.getElementById(
    "cantidad-activos"
  ).textContent = `${resumen.cantidad_confirmados} pagos activos`;
  document.getElementById("total-anual").textContent = formatCurrency(
    resumen.total_anual_proyectado
  );
  document.getElementById("ahorro-potencial").textContent = formatCurrency(
    resumen.ahorro_potencial
  );
  document.getElementById("cantidad-por-confirmar").textContent =
    resumen.cantidad_por_confirmar;
}

function cambiarTab(tab) {
  activeTab = tab;

  // Actualizar estilos de tabs
  ["confirmados", "probables", "por-confirmar"].forEach((t) => {
    const tabEl = document.getElementById(`tab-${t}`);
    const contentEl = document.getElementById(`content-${t}`);

    if (t === tab) {
      tabEl.classList.add("bg-white", "text-indigo-700", "shadow-sm");
      tabEl.classList.remove("text-gray-600", "hover:bg-gray-100");
      contentEl.classList.remove("hidden");
    } else {
      tabEl.classList.remove("bg-white", "text-indigo-700", "shadow-sm");
      tabEl.classList.add("text-gray-600", "hover:bg-gray-100");
      contentEl.classList.add("hidden");
    }
  });

  // Renderizar contenido
  if (tab === "confirmados") {
    renderGastos(gastosData.confirmados, "content-confirmados", "confirmados");
  } else if (tab === "probables") {
    renderGastos(gastosData.probables, "content-probables", "probables");
  } else if (tab === "por-confirmar") {
    renderGastos(
      gastosData.por_confirmar,
      "content-por-confirmar",
      "por-confirmar"
    );
  }
}

function renderGastos(gastos, containerId, tipo) {
  const container = document.getElementById(containerId);
  container.innerHTML = "";

  if (!gastos || gastos.length === 0) {
    container.innerHTML = `
            <div class="text-center py-8 text-gray-500">
                <svg class="w-12 h-12 mx-auto mb-2 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <p class="font-medium">No se detectaron gastos en esta categoría</p>
            </div>
        `;
    return;
  }

  gastos.forEach((gasto) => {
    const card = crearCardGasto(gasto, tipo);
    container.insertAdjacentHTML("beforeend", card);
  });
}

function crearCardGasto(gasto, tipo) {
  const formatCurrency = (amount) => {
    return new Intl.NumberFormat("es-CO", {
      style: "currency",
      currency: "COP",
      minimumFractionDigits: 0,
    }).format(amount || 0);
  };

  // -------------------------------
  // 1. Manejo SEGURO de valores
  // -------------------------------
  const descripcion = gasto.descripcion_base || "Sin descripción";
  const repeticiones = gasto.repeticiones ?? 0;
  const montoPromedio = gasto.monto_promedio ?? 0;
  const variacion = gasto.variacion_porcentaje ?? 0; // 👈 FIX IMPORTANTE

  // Último pago (puede ser null)
  let fechaUltimoPago = "No disponible";
  if (gasto.fecha_ultimo_pago) {
    fechaUltimoPago = new Date(
      gasto.fecha_ultimo_pago + "T00:00:00"
    ).toLocaleDateString("es-CO", {
      month: "long",
      day: "numeric",
    });
  }

  // -------------------------------
  // 2. Badge de CONFIANZA
  // -------------------------------
  let confidenceBadge = "";
  const confianza = gasto.confianza ?? 0;

  if (confianza >= 95) {
    confidenceBadge = `
      <span class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-medium rounded-full bg-emerald-100 text-emerald-700">
        ✓ Confirmado
      </span>`;
  } else if (confianza >= 70) {
    confidenceBadge = `
      <span class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-medium rounded-full bg-amber-100 text-amber-700">
        ¡Probable!
      </span>`;
  } else {
    confidenceBadge = `
      <span class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-medium rounded-full bg-blue-100 text-blue-700">
        Por confirmar
      </span>`;
  }

  // -------------------------------
  // 3. Badge de PATRÓN
  // -------------------------------
  const patronBadge =
    gasto.patron_monto === "FIJO"
      ? `<span class="inline-flex items-center px-2.5 py-1 text-xs font-medium rounded-full bg-purple-100 text-purple-700">Monto fijo</span>`
      : `<span class="inline-flex items-center px-2.5 py-1 text-xs font-medium rounded-full bg-orange-100 text-orange-700">Monto variable ±${variacion}%</span>`;

  // -------------------------------
  // 4. Próximo pago (solo confirmados + probables)
  // -------------------------------
  let proximoPagoHTML = "";

  if (tipo !== "por-confirmar" && gasto.proximo_pago_estimado) {
    const diasRestantes = gasto.dias_hasta_proximo ?? 0;

    const color =
      diasRestantes <= 3
        ? "text-red-600 font-bold"
        : diasRestantes <= 7
        ? "text-orange-600 font-semibold"
        : "text-gray-600";

    const diasTexto =
      diasRestantes === 0
        ? "¡Hoy!"
        : diasRestantes === 1
        ? "Mañana"
        : `En ${diasRestantes} días`;

    const fechaProxima = new Date(
      gasto.proximo_pago_estimado + "T00:00:00"
    ).toLocaleDateString("es-CO", {
      day: "numeric",
      month: "long",
    });

    proximoPagoHTML = `
      <div class="bg-indigo-50 rounded-lg p-3 mb-3">
        <div class="flex items-center justify-between">
          <div class="flex items-center gap-2 text-sm font-medium text-gray-700">
            📅 Próximo pago
          </div>
          <div class="text-right">
            <p class="text-sm font-semibold">${fechaProxima}</p>
            <p class="text-xs ${color}">${diasTexto}</p>
          </div>
        </div>
      </div>`;
  }

  // -------------------------------
  // 5. Botones por-confirmar
  // -------------------------------
  let botonesHTML = "";
  if (tipo === "por-confirmar") {
    botonesHTML = `
      <div class="bg-amber-50 rounded-lg p-3 mb-3">
        <p class="text-xs text-amber-800 mb-2">
          ${gasto.razon || "¿Es un pago recurrente?"}
        </p>
        <div class="flex gap-2">
          <button onclick="confirmarGasto(${gasto.id || 0})"
            class="flex-1 px-3 py-1.5 bg-sky-400 text-white text-xs font-medium rounded-lg hover:bg-sky-500">
            ✓ Confirmar
          </button>

          <button onclick="descartarGasto(${gasto.id || 0})"
            class="flex-1 px-3 py-1.5 bg-gray-200 text-gray-700 text-xs font-medium rounded-lg hover:bg-gray-300">
            ✗ Descartar
          </button>
        </div>
      </div>`;
  }

  // -------------------------------
  // 6. CARD FINAL
  // -------------------------------
  return `
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm hover:shadow-md transition-all duration-200 overflow-hidden">
      <span class="block h-1 bg-gradient-to-r from-indigo-500 to-purple-500"></span>

      <div class="p-5">
        <div class="flex items-start justify-between mb-3">
          <div class="flex-1">
            <h4 class="font-semibold text-gray-900 text-lg capitalize">${descripcion}</h4>
            <p class="text-sm text-gray-500 mt-0.5">Último pago: ${fechaUltimoPago}</p>
          </div>
          <div class="text-right">
            <p class="font-bold text-gray-900 text-xl">${formatCurrency(
              montoPromedio
            )}</p>
          </div>
        </div>

        <div class="flex flex-wrap gap-2 mb-3">
          ${confidenceBadge}
          ${patronBadge}
          <span class="inline-flex items-center px-2.5 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-700">
            Detectado ${repeticiones} ${repeticiones === 1 ? "vez" : "veces"}
          </span>
        </div>

        ${proximoPagoHTML}
        ${botonesHTML}
      </div>
    </div>`;
}

function confirmarGasto(id) {
  console.log("Confirmando gasto:", id);
  // Aquí implementarías la lógica para confirmar el gasto
  // Por ejemplo, hacer un POST a tu API PHP
  alert("Gasto confirmado. Implementa la lógica en el backend.");
}

function descartarGasto(id) {
  console.log("Descartando gasto:", id);
  // Aquí implementarías la lógica para descartar el gasto
  alert("Gasto descartado. Implementa la lógica en el backend.");
}
