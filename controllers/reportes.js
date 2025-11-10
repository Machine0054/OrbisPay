
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

      // Función principal que se debe llamar al cargar la página de reportes
    async function cargarGastosRecurrentes() {
        console.log("Iniciando detección de gastos recurrentes...");

        // 1. Seleccionar los elementos del widget
        const loaderEl = document.getElementById('recurrent-loader');
        const summaryEl = document.getElementById('recurrent-summary');
        const countEl = document.getElementById('recurrent-count');
        const totalEl = document.getElementById('recurrent-total');
        const listContainerEl = document.getElementById('recurrent-list-container');


        if (!loaderEl) return; // Si el widget no está en la página, no hacemos nada

        try {
            // 2. Llamar al backend
            const response = await fetch('../models/reportes.php?action=detectar_gastos_recurrentes'); // Asegúrate que la ruta es correcta
            if (!response.ok) throw new Error('Error de red al detectar gastos.');

            const result = await response.json();
            if (!result.success) throw new Error(result.message);

            const data = result.data;
            console.log("Gastos recurrentes detectados:", data);

            // 3. Actualizar el resumen principal
            countEl.textContent = data.cantidad_detectada;
            totalEl.textContent = new Intl.NumberFormat('es-CO', { style: 'currency', currency: 'COP', minimumFractionDigits: 0 }).format(data.total_mensual_recurrente);
            
            // 4. Renderizar la lista detallada
            renderRecurrentList(data.gastos_detectados, listContainerEl);

            // 5. Mostrar el resumen y ocultar el loader
            summaryEl.classList.remove('hidden');
            loaderEl.style.display = 'none';

        } catch (error) {
            console.error("Error en cargarGastosRecurrentes:", error);
            loaderEl.textContent = 'Error al analizar.';
            loaderEl.classList.add('text-red-500');
        }
    }


      function renderRecurrentList(gastos, container) {
        container.innerHTML = "";

        if (!gastos || gastos.length === 0) {
          container.innerHTML = `
            <div class="text-center font-semibold text-gray-500 dark:text-gray-400 py-6">
              ¡Buenas noticias! No detectamos gastos recurrentes automáticos.
            </div>`;
          return;
        }

        gastos.forEach(gasto => {
          const formattedMonto = new Intl.NumberFormat("es-CO", {
            style: "currency",
            currency: "COP",
            minimumFractionDigits: 0
          }).format(gasto.monto_promedio);

          const ultimoPago = new Date(gasto.fecha_ultimo_pago + "T00:00:00")
            .toLocaleDateString("es-CO", { month: "long", day: "numeric" });

          // Colores de “estado” (más reciente = verde)
          const meses = Number(gasto.meses_detectado || 0);
          const estadoColor =
            meses <= 2 ? "bg-emerald-100 text-emerald-700 ring-emerald-200"
            : meses <= 4 ? "bg-amber-100 text-amber-700 ring-amber-200"
            : "bg-rose-100 text-rose-700 ring-rose-200";

          const itemHTML = `
            <button type="button"
              class="group w-full text-left relative overflow-hidden
                    rounded-xl border border-gray-200 bg-white/90 backdrop-blur
                    shadow-sm hover:shadow-md transition-all duration-200
                    ring-1 ring-black/5 hover:-translate-y-0.5
                    dark:bg-slate-800/70 dark:border-white/10 dark:ring-white/10">
              
              <!-- Franja decorativa -->
              <span class="absolute inset-y-0 left-0 w-1 bg-gradient-to-b from-indigo-500 to-purple-500"></span>

              <div class="flex items-center justify-between gap-4 p-4 pl-5">
                <div class="min-w-0">
                  <p class="truncate font-medium text-slate-900 dark:text-slate-100">
                    ${gasto.descripcion_base}
                  </p>
                  <p class="text-sm text-slate-500 dark:text-slate-400">
                    Último pago: ${ultimoPago}
                  </p>
                </div>

                <div class="text-right shrink-0">
                  <p class="font-semibold text-slate-900 dark:text-slate-100">
                    ${formattedMonto}
                  </p>
                  <span class="inline-flex items-center px-2 py-0.5 mt-1 text-xs font-medium
                              rounded-full ring-1 ${estadoColor}">
                    Detectado ${meses} ${meses === 1 ? "mes" : "meses"}
                  </span>
                </div>
              </div>

              <!-- Hover overlay sutil -->
              <div class="pointer-events-none absolute inset-0 opacity-0
                          group-hover:opacity-100 transition-opacity
                          bg-gradient-to-r from-indigo-50/40 to-transparent
                          dark:from-indigo-400/10"></div>
            </button>
          `;
          container.insertAdjacentHTML("beforeend", itemHTML);
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
        tbody.html('<tr><td colspan="5" class="text-center p-4">Cargando transacciones...</td></tr>'); // Estado de carga

        $.ajax({
          url: "../models/reportes.php",
          method: "POST",
          data: {
            action: "obtener_transacciones",
            periodo: currentPeriod,
            start_date: $("#startDate").val(),
            end_date: $("#endDate").val(),
            page: transactionsCurrentPage, // <-- Enviar página actual
            limit: 8 // Coincide con el límite del backend
          },
          dataType: "json",
          success: function (response) {
            if (response.success) {
              tbody.empty();
              if (response.data.length === 0) {
                tbody.html('<tr><td colspan="5" class="text-center p-4">No se encontraron transacciones.</td></tr>');
              } else {
                response.data.forEach((t) => {
                  const row = `
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${t.fecha}</td>
                        <td class="px-6 py-4 text-sm text-gray-900">${t.descripcion}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${t.categoria}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${t.tipo === "Ingreso" ? "bg-green-100 text-green-800" : "bg-red-100 text-red-800"}">
                                ${t.tipo}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-right ${t.tipo === "Ingreso" ? "text-emerald-600" : "text-red-600"}">
                            ${t.tipo === "Ingreso" ? "+" : "-"}$${t.monto.toLocaleString("es-CO")}
                            <button onclick="eliminarTransaccion(${t.id}, '${t.tipo}')" class="ml-2 text-red-600 hover:text-red-800">Eliminar</button>
                        </td>
                    </tr>`;
                  tbody.append(row);
                });
              }
              // Renderizar la paginación
              renderTransactionsPagination(response.pagination);
            } else {
              tbody.html(`<tr><td colspan="5" class="text-center p-4 text-red-500">Error: ${response.message}</td></tr>`);
            }
          },
          error: function () {
            tbody.html('<tr><td colspan="5" class="text-center p-4 text-red-500">Error en la solicitud al servidor.</td></tr>');
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
              <button data-page="${currentPage - 1}" ${currentPage === 1 ? 'disabled' : ''} class="pagination-btn relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 disabled:opacity-50">
                Anterior
              </button>
              <button data-page="${currentPage + 1}" ${currentPage >= totalPages ? 'disabled' : ''} class="pagination-btn relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 disabled:opacity-50">
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
            const response = await fetch('../models/dashboard.php'); // Ajusta la ruta si es necesario
            if (!response.ok) throw new Error('La respuesta de la red no fue correcta.');
            
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
        const formatCurrency = (value) => `$${new Intl.NumberFormat('es-CO').format(value)}`;

        document.getElementById('ingresosMes').textContent = formatCurrency(cards.ingresosMes);
        document.getElementById('gastosMes').textContent = formatCurrency(cards.gastosMes);
        document.getElementById('balanceMes').textContent = formatCurrency(cards.balanceMes);

        const pIngresos = document.getElementById('ingresosMesAnterior');
        pIngresos.textContent = `${cards.porcentajeIngresos.toFixed(1)}% vs mes anterior`;
        pIngresos.className = `text-xs sm:text-sm mt-1 ${cards.porcentajeIngresos >= 0 ? 'text-emerald-500' : 'text-red-500'}`;

        const pGastos = document.getElementById('gastosMesAnterior');
        pGastos.textContent = `${cards.porcentajeGastos.toFixed(1)}% vs mes anterior`;
        pGastos.className = `text-xs sm:text-sm mt-1 ${cards.porcentajeGastos >= 0 ? 'text-red-500' : 'text-emerald-500'}`;
        
        document.getElementById('porcentajeAhorro').textContent = `${cards.porcentajeAhorro.toFixed(1)}% de ahorro`;
    }


    // Listener para los botones de paginación (delegación de eventos)
    $(document).on('click', '#paginationContainer .pagination-btn', function() {
      if ($(this).is(':disabled')) return;
      transactionsCurrentPage = parseInt($(this).data('page'));
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
    const originalButtonText = '<span>Exportar</span>';
    const exportButton = $('button[onclick="exportToExcel()"]');
    exportButton.prop('disabled', true).html(`
        <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        <span>Exportando...</span>`
     );

    $.ajax({
      url: "../models/reportes.php",
      method: "POST", // Cambiado a POST para consistencia
      data: {
        action: "obtener_transacciones",
        periodo: currentPeriod,
        start_date: $("#startDate").val(),
        end_date: $("#endDate").val(),
        limit: -1, // <-- ¡LA CLAVE! Le decimos al backend que queremos todos los registros.
      },
      dataType: "json",
      success: function (response) {
        if (response.success && response.data.length > 0) {
          // Mapear los datos para que tengan nombres de columna más amigables
          const dataToExport = response.data.map(t => ({
              Fecha: t.fecha,
              Descripción: t.descripcion,
              Categoría: t.categoria,
              Tipo: t.tipo,
              Monto: t.monto
          }));

          const ws = XLSX.utils.json_to_sheet(dataToExport);
          const wb = XLSX.utils.book_new();
          XLSX.utils.book_append_sheet(wb, ws, "Transacciones");
          XLSX.writeFile(wb, `Reporte_Transacciones_${new Date().toISOString().split('T')[0]}.xlsx`);
        } else if (response.data.length === 0) {
            alert("No hay datos para exportar con los filtros seleccionados.");
        } else {
          alert("Error al obtener los datos para exportar: " + response.message);
        }
      },
      error: function () {
        alert("Error en la solicitud al servidor para exportar los datos.");
      },
      complete: function() {
        // Restaura el botón a su estado original
        exportButton.prop('disabled', false).html(originalButtonText);
      }
    });
  };
});
