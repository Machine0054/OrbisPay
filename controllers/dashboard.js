document.addEventListener("DOMContentLoaded", () => {
  const bellBtn = document.getElementById("notification-bell-btn");
  const panel = document.getElementById("notification-panel");
  const indicator = document.getElementById("notification-unread-indicator");
  const listContainer = document.getElementById("notification-list-container");
  const markAllReadBtn = document.getElementById("mark-all-as-read-btn");

  if (!bellBtn || !panel) return; // Si no están los elementos, no hacemos nada.

  // --- Lógica para mostrar/ocultar el panel ---
  bellBtn.addEventListener("click", (event) => {
    event.stopPropagation();
    const isHidden = panel.classList.contains("hidden");

    if (isHidden) {
      panel.classList.remove("hidden");
      // Al abrir, marcamsos las notificaciones como leídas
      markNotificationsAsRead();
    } else {
      panel.classList.add("hidden");
    }
  });

  // Ocultar el panel si se hace clic fuera de él
  document.addEventListener("click", (event) => {
    if (!panel.contains(event.target) && !bellBtn.contains(event.target)) {
      panel.classList.add("hidden");
    }
  });

  // --- Lógica para cargar las notificaciones ---
  async function fetchNotifications() {
    try {
      const response = await fetch(
        "../models/api_notificaciones.php?action=obtener_notificaciones"
      );
      if (!response.ok) throw new Error("Error de red.");

      const result = await response.json();
      if (!result.success) throw new Error(result.message);

      renderNotifications(result.data);
      updateUnreadIndicator(result.unread_count);
    } catch (error) {
      console.error("Error al cargar notificaciones:", error);
      listContainer.innerHTML =
        '<p class="p-4 text-sm text-gray-500">No se pudieron cargar las notificaciones.</p>';
    }
  }

  // --- Lógica para "dibujar" las notificaciones ---
  function renderNotifications(notifications) {
    listContainer.innerHTML = ""; // Limpiar contenido anterior

    if (notifications.length === 0) {
      listContainer.innerHTML =
        '<p class="p-4 text-sm text-center text-gray-500">No tienes notificaciones.</p>';
      return;
    }

    notifications.forEach((notif) => {
      const { icon, bgColor } = getNotificationStyles(notif.tipo);
      const isUnread = !notif.leida;

      const notifElement = `
                <div class="flex px-4 py-3 ${
                  isUnread ? "bg-gray-50 dark:bg-gray-800" : ""
                } hover:bg-gray-100 dark:hover:bg-gray-200">
                    <div class="flex-shrink-0">
                        <div class="flex items-center justify-center w-10 h-10 rounded-full ${bgColor}">
                            ${icon}
                        </div>
                    </div>
                    <div class="w-full ps-3">
                        <div class="text-gray-600 text-sm mb-1.5 ">${
                          notif.mensaje
                        }</div>
                        <div class="text-xs text-blue-600 ">${formatTimeAgo(
                          notif.fecha_creacion
                        )}</div>
                    </div>
                </div>
            `;
      listContainer.insertAdjacentHTML("beforeend", notifElement);
    });
  }

  async function markNotificationsAsRead() {
    // Ocultar el indicador inmediatamente para una mejor UX
    indicator.classList.add("hidden");

    try {
      await fetch("../models/api_notificaciones.php?action=marcar_leidas", {
        method: "POST",
      });
    } catch (error) {
      console.error("Error al marcar notificaciones como leídas:", error);
    }
  }

  markAllReadBtn.addEventListener("click", (e) => {
    e.preventDefault();
    markNotificationsAsRead();
    // Opcional: recargar la lista para que se vea el cambio de estilo al instante
    fetchNotifications();
  });

  // --- Funciones de Utilidad ---
  function updateUnreadIndicator(count) {
    if (count > 0) {
      indicator.classList.remove("hidden");
    } else {
      indicator.classList.add("hidden");
    }
  }

  function getNotificationStyles(type) {
    switch (type) {
      case "alerta_gasto":
        return {
          icon: '<svg class="w-5 h-5 text-yellow-600 dark:text-yellow-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.21 3.03-1.742 3.03H4.42c-1.532 0-2.492-1.696-1.742-3.03l5.58-9.92zM10 13a1 1 0 110-2 1 1 0 010 2zm-1-8a1 1 0 00-1 1v3a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path></svg>',
          bgColor: "bg-yellow-100 dark:bg-yellow-900",
        };
      case "gasto_recurrente":
        return {
          icon: '<svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="currentColor" viewBox="0 0 20 20"><path d="M5 3a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2V5a2 2 0 00-2-2H5zM5 11a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2v-2a2 2 0 00-2-2H5zM11 5a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V5zM11 13a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>',
          bgColor: "bg-blue-100 dark:bg-blue-900",
        };
      default:
        return {
          icon: '<svg class="w-5 h-5 text-gray-600 dark:text-gray-400" fill="currentColor" viewBox="0 0 20 20"><path d="M10 2a6 6 0 00-6 6v3.586l-.707.707A1 1 0 004 14h12a1 1 0 00.707-1.707L16 11.586V8a6 6 0 00-6-6zM10 18a3 3 0 01-3-3h6a3 3 0 01-3 3z"></path></svg>',
          bgColor: "bg-gray-100 dark:bg-gray-900",
        };
    }
  }

  function formatTimeAgo(dateString) {
    const date = new Date(dateString);
    const now = new Date();
    const seconds = Math.round((now - date) / 1000);
    const minutes = Math.round(seconds / 60);
    const hours = Math.round(minutes / 60);
    const days = Math.round(hours / 24);

    if (seconds < 60) return `hace ${seconds} segundos`;
    if (minutes < 60) return `hace ${minutes} minutos`;
    if (hours < 24) return `hace ${hours} horas`;
    return `hace ${days} días`;
  }

  async function cargarProyeccionSaldo() {
    console.log("Iniciando cálculo de proyección de saldo...");

    // 1. Seleccionar los elementos del widget
    const widget = document.getElementById("proyeccion-widget");
    if (!widget) return; // Si el widget no está en la página, no hacemos nada

    const valorEl = document.getElementById("proyeccion-valor");
    const consejoEl = document.getElementById("proyeccion-consejo");
    const loaderEl = document.getElementById("proyeccion-loader");
    const statusIconEl = document.getElementById("proyeccion-status-icon");

    try {
      // 2. Llamar al backend
      const response = await fetch(
        "../models/reportes.php?action=obtener_proyeccion_saldo"
      );
      if (!response.ok)
        throw new Error("Error de red al obtener la proyección.");

      const result = await response.json();
      if (!result.success) throw new Error(result.message);

      const data = result.data;
      console.log("Datos de proyección recibidos:", data);

      // 3. Actualizar la interfaz con los datos recibidos
      actualizarWidgetUI(data);
    } catch (error) {
      console.error("Error en cargarProyeccionSaldo:", error);
      valorEl.textContent = "Error";
      consejoEl.textContent = "No se pudo calcular la proyección.";
      valorEl.classList.add("text-red-500");
    } finally {
      // Ocultamos el loader al finalizar, ya sea con éxito o error
      if (loaderEl) loaderEl.style.display = "none";
    }
  }
  // Función para actualizar la UI del widget
  function actualizarWidgetUI(data) {
    const valorEl = document.getElementById("proyeccion-valor");
    const consejoEl = document.getElementById("proyeccion-consejo");
    const statusIconEl = document.getElementById("proyeccion-status-icon");

    // Formateamos el valor final como moneda
    const formattedProyeccion = new Intl.NumberFormat("es-CO", {
      style: "currency",
      currency: "COP",
      minimumFractionDigits: 0,
    }).format(data.proyeccion_final);
    valorEl.textContent = formattedProyeccion;

    // Limpiamos clases de color anteriores
    valorEl.classList.remove(
      "text-green-600",
      "text-yellow-600",
      "text-red-600",
      "dark:text-green-400",
      "dark:text-yellow-400",
      "dark:text-red-400"
    );
    statusIconEl.innerHTML = "";
    statusIconEl.className =
      "hidden w-5 h-5 rounded-full flex items-center justify-center"; // Reset

    // 4. Lógica de colores y consejos según el resultado
    let consejo = "";
    if (
      data.proyeccion_final > data.saldo_actual * 0.5 &&
      data.proyeccion_final > 0
    ) {
      // Proyección muy positiva
      valorEl.classList.add("text-green-600", "dark:text-green-400");
      statusIconEl.innerHTML =
        '<svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>';
      statusIconEl.classList.add("bg-green-500");
      consejo = "¡Excelente! Vas por muy buen camino este mes.";
    } else if (data.proyeccion_final > 0) {
      // Proyección positiva, pero ajustada
      valorEl.classList.add("text-yellow-600", "dark:text-yellow-400");
      statusIconEl.innerHTML =
        '<svg class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.21 3.03-1.742 3.03H4.42c-1.532 0-2.492-1.696-1.742-3.03l5.58-9.92zM10 13a1 1 0 110-2 1 1 0 010 2zm-1-8a1 1 0 00-1 1v3a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path></svg>';
      statusIconEl.classList.add("bg-yellow-500");
      consejo = "Vas bien, pero mantén tus gastos bajo control.";
    } else {
      // Proyección negativa
      valorEl.classList.add("text-red-600", "dark:text-red-400");
      statusIconEl.innerHTML =
        '<svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"></path></svg>';
      statusIconEl.classList.add("bg-red-500");
      consejo = "¡Atención! A este ritmo, tu saldo será negativo.";
    }

    consejoEl.textContent = consejo;
    statusIconEl.classList.remove("hidden");

    // (Opcional) Rellenar los detalles
    document.getElementById("detalle-saldo-actual").textContent =
      new Intl.NumberFormat("es-CO", {
        style: "currency",
        currency: "COP",
        minimumFractionDigits: 0,
      }).format(data.saldo_actual);
    document.getElementById(
      "detalle-gastos-proyectados"
    ).textContent = `- ${new Intl.NumberFormat("es-CO", {
      style: "currency",
      currency: "COP",
      minimumFractionDigits: 0,
    }).format(data.gastos_proyectados)}`;
    document.getElementById(
      "detalle-ingresos-restantes"
    ).textContent = `+ ${new Intl.NumberFormat("es-CO", {
      style: "currency",
      currency: "COP",
      minimumFractionDigits: 0,
    }).format(data.ingresos_recurrentes_restantes)}`;
  }

  // Función principal para cargar todos los datos del dashboard
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
      // Aquí podrías mostrar un mensaje de error en la UI
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

    document.getElementById("saldoRestante").textContent = formatCurrency(
      cards.saldoRestante
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
  }

  // Función para renderizar el gráfico de tendencia mensual
  let monthlyChartInstance = null; // Variable global o accesible para almacenar la instancia del gráfico

  function renderMonthlyChart(chartData) {
    // 1. Destruir la instancia anterior si existe
    if (monthlyChartInstance) {
      monthlyChartInstance.destroy();
    }

    const ctx = document.getElementById("monthlyChart").getContext("2d");
    monthlyChartInstance = new Chart(ctx, {
      type: "line",
      data: {
        labels: chartData.labels,
        datasets: [
          {
            label: "Ingresos",
            data: chartData.ingresos,
            borderColor: "rgb(16, 185, 129)", // emerald-500
            backgroundColor: "rgba(16, 185, 129, 0.1)",
            fill: true,
            tension: 0.4,
          },
          {
            label: "Gastos",
            data: chartData.gastos,
            borderColor: "rgb(220, 38, 38)", // red-600
            backgroundColor: "rgba(220, 38, 38, 0.1)",
            fill: true,
            tension: 0.4,
          },
        ],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
          y: {
            beginAtZero: true,
          },
        },
      },
    });
  }

  // Función para renderizar la lista de transacciones recientes
  function renderRecentTransactions(transactions) {
    const container = document.getElementById("recentTransactionsContainer");
    container.innerHTML = ""; // Limpiar el placeholder de carga

    if (transactions.length === 0) {
      container.innerHTML =
        '<p class="text-center text-gray-500">No hay transacciones recientes.</p>';
      return;
    }

    // Dentro de tu función renderRecentTransactions, en el bucle forEach:

    transactions.forEach((tx) => {
      const isGasto = tx.tipo === "gasto";
      const amountClass = isGasto ? "text-red-600" : "text-emerald-600";
      const amountPrefix = isGasto ? "-" : "+";
      const iconBg = isGasto ? "bg-red-100" : "bg-emerald-100";

      // --- INICIO DE LA CORRECCIÓN: SVG COMPLETOS ---
      const iconSvg = isGasto
        ? `<svg class="w-5 h-5 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v3.586L7.707 9.293a1 1 0 00-1.414 1.414l3 3a1 1 0 001.414 0l3-3a1 1 0 00-1.414-1.414L11 10.586V7z" clip-rule="evenodd" />
            </svg>` // Icono de Gasto (flecha hacia abajo)
        : `<svg class="w-5 h-5 text-emerald-600" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-8.293l-3-3a1 1 0 00-1.414 0l-3 3a1 1 0 001.414 1.414L9 9.414V13a1 1 0 102 0V9.414l1.293 1.293a1 1 0 001.414-1.414z" clip-rule="evenodd" />
            </svg>`; // Icono de Ingreso (flecha hacia arriba)
      // --- FIN DE LA CORRECCIÓN ---

      const transactionEl = `
            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                <div class="flex items-center space-x-3 min-w-0 flex-1">
                    <div class="w-8 h-8 sm:w-10 sm:h-10 ${iconBg} rounded-full flex items-center justify-center flex-shrink-0">
                        ${iconSvg}
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="font-medium text-gray-800 text-sm sm:text-base truncate">${
                          tx.descripcion
                        }</p>
                        <p class="text-xs sm:text-sm text-gray-600">${new Date(
                          tx.fecha
                        ).toLocaleDateString("es-CO", {
                          month: "long",
                          day: "numeric",
                        })}</p>
                    </div>
                </div>
                <p class="font-semibold ${amountClass} text-sm sm:text-base flex-shrink-0">
                    ${amountPrefix}$${new Intl.NumberFormat("es-CO").format(
        tx.monto
      )}
                </p>
            </div>
        `;
      container.insertAdjacentHTML("beforeend", transactionEl);
    });
  }

  // Iniciar la carga de datos cuando la página esté lista
  loadDashboardData();
  cargarProyeccionSaldo();
  fetchNotifications();
});
