document.addEventListener("DOMContentLoaded", function () {
  console.log("metas.js cargado y optimizado.");

  // =================================================================
  // --- 1. SELECCIÓN DE ELEMENTOS DEL DOM (ÚNICA VEZ) ---
  // =================================================================
  const goalsContainer = document.getElementById("goals-container");
  const newGoalModal = document.getElementById("newGoalModal");
  const openNewGoalModalBtn = document.getElementById("openNewGoalModalBtn");
  const closeNewGoalModalBtn = document.getElementById("closeNewGoalModalBtn");
  const cancelNewGoalBtn = document.getElementById("cancelNewGoalBtn");
  const newGoalForm = document.getElementById("newGoalForm");
  const goalModalTitle = document.getElementById("goalModalTitle");
  const goalSubmitBtn = document.getElementById("goalSubmitBtn");
  const goalIdToEditInput = document.getElementById("goalIdToEdit");
  const automaticSavingCheck = document.getElementById("automaticSavingCheck");
  const automaticSavingOptions = document.getElementById(
    "automaticSavingOptions"
  );
  const toggleVisual = document.getElementById("toggleVisual");
  const toggleButton = document.getElementById("toggleButton");
  const savingFrequencySelect = document.getElementById("savingFrequency");
  const addFundsModal = document.getElementById("addFundsModal");
  const closeAddFundsModalBtn = document.getElementById(
    "closeAddFundsModalBtn"
  );
  const cancelAddFundsBtn = document.getElementById("cancelAddFundsBtn");
  const addFundsForm = document.getElementById("addFundsForm");
  const goalIdToFundInput = document.getElementById("goalIdToFund");
  const goalOptionsMenu = document.getElementById("goalOptionsMenu");
  const deleteGoalOption = document.getElementById("deleteGoalOption");
  const celebrationModal = document.getElementById("goalCelebrationModal");
  const celebrationTitle = document.getElementById("celebrationTitle");
  const celebrationMessage = document.getElementById("celebrationMessage");
  const celebrationCloseBtn = document.getElementById("celebrationCloseBtn");
  const celebrationNewGoalBtn = document.getElementById(
    "celebrationNewGoalBtn"
  );

  // =================================================================
  // --- 2. VARIABLES DE ESTADO GLOBAL ---
  // =================================================================
  let allGoalsData = []; // Guardaremos todas las metas aquí para un acceso rápido
  let activeGoalData = null; // Guardará el OBJETO completo de la meta activa
  let flatpickrInstance = null;

  // =================================================================
  // --- 3. FUNCIONES PRINCIPALES ---
  // =================================================================

  /**
   * Obtiene las metas del servidor y las renderiza en la página.
   */
  function fetchAndRenderGoals() {
    if (!goalsContainer) return;
    goalsContainer.innerHTML = createLoadingSpinnerHTML();

    fetch("../models/metas_controller.php?action=obtener_metas")
      .then((response) => response.json())
      .then((result) => {
        if (result.success) {
          allGoalsData = result.data; // ¡Guardamos los datos globalmente!
          goalsContainer.innerHTML = "";
          if (allGoalsData.length > 0) {
            allGoalsData.forEach((goal) => {
              const goalCardHTML = createGoalCard(goal);
              goalsContainer.insertAdjacentHTML("beforeend", goalCardHTML);
            });
          } else {
            goalsContainer.innerHTML = `
              <div class='col-span-full text-center py-20'>
                <h3 class='text-2xl font-bold text-gray-700'>Aún no tienes metas. ¡Crea la primera!</h3>
                <img src="../assets/icons/404.webp" alt="404" class="mt-8 mx-auto w-48 object-contain">
              </div>`;
          }
        } else {
          goalsContainer.innerHTML = `<p class="text-red-500">Error: ${result.message}</p>`;
        }
      })
      .catch((error) => {
        console.error("Error al obtener las metas:", error);
        goalsContainer.innerHTML = `<p class="text-red-500">Ocurrió un error de red al cargar las metas.</p>`;
      });
  }

  /**
   * Crea el HTML para una tarjeta de meta.
   */
  function createGoalCard(goal) {
    const formattedObjective = formatCurrency(goal.monto_objetivo);
    const formattedCurrent = formatCurrency(goal.monto_actual);
    const isCompleted = goal.estado_meta === "completada";

    if (isCompleted) {
      const completionDate = new Date(
        goal.fecha_completada + "T00:00:00"
      ).toLocaleDateString("es-CO", {
        year: "numeric",
        month: "long",
        day: "numeric",
      });
      return `
        <div class="goal-card bg-gray-50 rounded-xl shadow-md p-5 flex flex-col border-l-4 border-emerald-500 opacity-90" data-goal-id="${goal.id_meta}">
          <div class="flex items-start justify-between space-x-4 mb-3">
            <div class="flex items-center space-x-4 min-w-0">
              <div class="w-12 h-12 rounded-lg bg-emerald-100 flex items-center justify-center text-2xl flex-shrink-0">🏆</div>
              <div class="min-w-0">
                <h3 class="font-bold text-lg text-gray-800 truncate">${goal.nombre_meta}</h3>
                <p class="text-sm text-gray-500">Completada el ${completionDate}</p>
              </div>
            </div>
            <button class="delete-goal-btn flex items-center justify-center w-12 h-12 text-gray-400 hover:text-red-600 rounded-full transition-colors" data-goal-id="${goal.id_meta}">
              <img src="../assets/icons/delete2.png" alt="Eliminar" class="w-5 h-5 object-contain">
            </button>
          </div>
          <div class="mt-auto text-center">
            <p class="text-sm text-gray-600">¡Lograste ahorrar!</p>
            <p class="text-2xl font-bold text-emerald-600">${formattedObjective}</p>
          </div>
        </div>`;
    }

    const progressPercentage =
      goal.monto_objetivo > 0
        ? (goal.monto_actual / goal.monto_objetivo) * 100
        : 0;
    const visualProgress = Math.min(progressPercentage, 100);

    return `
      <div class="goal-card bg-white rounded-xl shadow-lg p-5 flex flex-col text-bold" data-goal-id="${
        goal.id_meta
      }">
        <div class="flex items-start justify-between space-x-4 mb-4">
          <div class="flex items-center space-x-4 min-w-0">
            <div class="w-12 h-12 rounded-lg bg-gray-100 flex items-center justify-center text-xs text-gray-400 flex-shrink-0">
              <img src="${
                goal.imagen_url || "../assets/icons/metas.png"
              }" alt="Metas" class="${goal.imagen_url ? "w-full h-full object-cover rounded-lg" : "w-10 h-10 object-contain"}">
            </div>
            <div class="min-w-0">
              <h3 class="font-bold text-lg truncate">${goal.nombre_meta}</h3>
              <p class="text-sm text-gray-500">Objetivo: ${formattedObjective}</p>
            </div>
          </div>
          <button class="delete-goal-btn flex items-center justify-center w-12 h-12 text-gray-400 hover:text-red-600 rounded-full transition-colors" data-goal-id="${
            goal.id_meta
          }">
            <img src="../assets/icons/delete2.png" alt="Eliminar" class="w-5 h-5 object-contain">
          </button>
        </div>
        <div class="w-full bg-gray-200 rounded-full h-2.5 mb-2">
          <div class="bg-indigo-600 h-2.5 rounded-full" style="width: ${visualProgress}%"></div>
        </div>
        <div class="flex justify-between text-sm font-medium text-gray-600 mb-4">
          <span>${formattedCurrent}</span>
          <span>${progressPercentage.toFixed(0)}%</span>
        </div>
        <div class="mt-auto">
          <button class="add-funds-btn w-full px-3 py-2 text-white text-sm font-semibold rounded-lg bg-indigo-600 hover:bg-indigo-700">Abonar</button>
        </div>
      </div>`;
  }

  /**
   * Abre el modal de creación/edición, reseteándolo o llenándolo según el modo.
   */
  function showNewGoalModal(isEditMode = false, goalData = null) {
    if (!isEditMode) {
      console.log("Reseteando modal para CREAR.");
      if (newGoalForm) newGoalForm.reset();
      if (automaticSavingCheck) automaticSavingCheck.checked = false;
      updateAutomaticSavingState();
      if (flatpickrInstance) {
        flatpickrInstance.destroy();
        flatpickrInstance = null;
      }
      const errorDiv = document.getElementById("goalFormError");
      if (errorDiv) {
        errorDiv.classList.add("hidden");
        errorDiv.textContent = "";
      }
      goalModalTitle.textContent = "Crear Nueva Meta";
      goalSubmitBtn.textContent = "Crear Meta";
      if (goalIdToEditInput) goalIdToEditInput.value = "";
    } else if (isEditMode && goalData) {
      console.log("Configurando modal para EDICIÓN.", goalData);
      goalModalTitle.textContent = "Editar Meta";
      goalSubmitBtn.textContent = "Guardar Cambios";
      if (goalIdToEditInput) goalIdToEditInput.value = goalData.id_meta;
      document.getElementById("goalName").value = goalData.nombre_meta;
      document.getElementById("goalAmount").value = goalData.monto_objetivo;
      document.getElementById("goalDate").value = goalData.fecha_limite || "";
      if (automaticSavingCheck) {
        automaticSavingCheck.checked = goalData.ahorro_automatico == 1;
        updateAutomaticSavingState();
        // Aquí se podría añadir lógica para rellenar la frecuencia y fecha si existen
      }
    }
    if (newGoalModal) newGoalModal.classList.remove("hidden");
  }

  /**
   * Actualiza el selector de fecha (flatpickr) según la frecuencia de ahorro.
   */
  function updateDaySelector(frequency) {
    const wrapper = document.getElementById("dateSelectorWrapper");
    const dateLabel = document.getElementById("savingDateLabel");
    const dateSelector = document.getElementById("savingDateSelector");
    const hiddenDayInput = document.getElementById("dia_seleccionado");

    if (!wrapper || !dateLabel || !dateSelector || !hiddenDayInput) return;
    if (flatpickrInstance) flatpickrInstance.destroy();

    wrapper.classList.add("hidden");
    hiddenDayInput.value = "";

    const config = {
      locale: {
        firstDayOfWeek: 1,
        weekdays: {
          shorthand: ["Dom", "Lun", "Mar", "Mié", "Jue", "Vie", "Sáb"],
          longhand: [
            "Domingo",
            "Lunes",
            "Martes",
            "Miércoles",
            "Jueves",
            "Viernes",
            "Sábado",
          ],
        },
        months: {
          shorthand: [
            "Ene",
            "Feb",
            "Mar",
            "Abr",
            "May",
            "Jun",
            "Jul",
            "Ago",
            "Sep",
            "Oct",
            "Nov",
            "Dic",
          ],
          longhand: [
            "Enero",
            "Febrero",
            "Marzo",
            "Abril",
            "Mayo",
            "Junio",
            "Julio",
            "Agosto",
            "Septiembre",
            "Octubre",
            "Noviembre",
            "Diciembre",
          ],
        },
      },
      minDate: "today",
    };

    switch (frequency) {
      case "semanal":
        wrapper.classList.remove("hidden");
        dateLabel.textContent = "Selecciona el día de la semana";
        config.dateFormat = "l";
        config.onChange = function (selectedDates, dateStr, instance) {
          if (selectedDates.length > 0) {
            hiddenDayInput.value =
              selectedDates[0].getDay() === 0 ? 7 : selectedDates[0].getDay();
          }
        };
        break;
      case "quincenal":
        wrapper.classList.remove("hidden");
        dateLabel.textContent =
          "Selecciona el primer día (el segundo se calculará automáticamente)";
        config.mode = "multiple";
        config.dateFormat = "d";
        config.conjunction = ", ";
        config.onChange = function (selectedDates, dateStr, instance) {
          if (selectedDates.length === 1) {
            const firstDate = selectedDates[0];
            const secondDate = new Date(firstDate);
            secondDate.setDate(firstDate.getDate() + 15);
            instance.setDate([firstDate, secondDate], false);
            const firstDay = firstDate.getDate();
            const secondDay = secondDate.getDate();
            hiddenDayInput.value = `${firstDay},${secondDay}`;
            return;
          }
          if (selectedDates.length >= 1) {
            hiddenDayInput.value = selectedDates
              .map((d) => d.getDate())
              .join(",");
          } else {
            hiddenDayInput.value = "";
          }
        };
        break;
      case "mensual":
        wrapper.classList.remove("hidden");
        dateLabel.textContent = "Selecciona el día del mes (1-28)";
        config.dateFormat = "d";
        config.maxDate = new Date().fp_incr(27);
        config.onChange = function (selectedDates, dateStr, instance) {
          if (selectedDates.length > 0) {
            hiddenDayInput.value = selectedDates[0].getDate();
          }
        };
        break;
    }
    if (frequency !== "diario") {
      flatpickrInstance = flatpickr(dateSelector, config);
    }
  }

  /**
   * Actualiza el estado visual del toggle de ahorro automático y muestra/oculta las opciones.
   */
  function updateAutomaticSavingState() {
    if (
      !automaticSavingCheck ||
      !automaticSavingOptions ||
      !toggleVisual ||
      !toggleButton
    )
      return;
    const isChecked = automaticSavingCheck.checked;
    automaticSavingOptions.classList.toggle("hidden", !isChecked);
    if (isChecked) {
      toggleVisual.classList.remove("bg-gray-200");
      toggleVisual.classList.add("bg-indigo-600");
      toggleButton.style.transform = "translateX(20px)";
      if (savingFrequencySelect) updateDaySelector(savingFrequencySelect.value);
    } else {
      toggleVisual.classList.remove("bg-indigo-600");
      toggleVisual.classList.add("bg-gray-200");
      toggleButton.style.transform = "translateX(0)";
    }
  }

  /**
   * Muestra el modal de abono y guarda los datos de la meta activa.
   */
  function showAddFundsModal(goalData) {
    activeGoalData = goalData; // ¡GUARDAMOS LOS DATOS DE LA META ACTIVA!
    if (goalIdToFundInput) goalIdToFundInput.value = goalData.id_meta;
    if (addFundsModal) addFundsModal.classList.remove("hidden");
  }

  /**
   * Lanza el confeti y muestra el modal de celebración.
   */
  function celebrateGoalCompletion(userName, serverMessage) {
    if (!celebrationModal) return;
    celebrationTitle.textContent = `¡FELICITACIONES, ${userName.toUpperCase()}!`;
    celebrationMessage.textContent = serverMessage;
    celebrationModal.classList.remove("hidden");
    setTimeout(() => {
      celebrationModal
        .querySelector(".modal-content")
        .classList.remove("scale-95", "opacity-0");
    }, 10);

    const canvas = document.getElementById("celebration-canvas");
    if (!canvas || !confetti) return;
    const myConfetti = confetti.create(canvas, {
      resize: true,
      useWorker: true,
    });
    myConfetti({ particleCount: 100, spread: 70, origin: { y: 0.6, x: 0 } });
    myConfetti({ particleCount: 100, spread: 70, origin: { y: 0.6, x: 1 } });

    const duration = 3 * 1000;
    const end = Date.now() + duration;
    (function frame() {
      myConfetti({ particleCount: 2, angle: 60, spread: 55, origin: { x: 0 } });
      myConfetti({
        particleCount: 2,
        angle: 120,
        spread: 55,
        origin: { x: 1 },
      });
      if (Date.now() < end) requestAnimationFrame(frame);
    })();
    fetchAndRenderGoals();
  }

  // =================================================================
  // --- 4. EVENT LISTENERS ---
  // =================================================================

  // --- Listeners para el contenedor principal de metas (Delegación de eventos) ---
  if (goalsContainer) {
    goalsContainer.addEventListener("click", function (event) {
      const card = event.target.closest(".goal-card");
      if (!card) return;

      const goalId = card.dataset.goalId;
      const goalData = allGoalsData.find((g) => g.id_meta == goalId);
      if (!goalData) return;

      // Botón de Eliminar (en cualquier tarjeta)
      if (event.target.closest(".delete-goal-btn")) {
        openDeleteGoalModal(goalId);
        return;
      }

      // Si la tarjeta es de una meta completada, no hacemos nada más.
      if (goalData.estado_meta === "completada") return;

      // Botón de Abonar (solo en tarjetas activas)
      if (event.target.closest(".add-funds-btn")) {
        showAddFundsModal(goalData); // ¡Le pasamos los datos de la meta!
        return;
      }

      // Clic en la tarjeta (para editar)
      showNewGoalModal(true, goalData);
    });
  }

  // --- Listeners para el modal de Crear/Editar Meta ---
  if (openNewGoalModalBtn)
    openNewGoalModalBtn.addEventListener("click", () =>
      showNewGoalModal(false)
    );
  if (closeNewGoalModalBtn)
    closeNewGoalModalBtn.addEventListener("click", () =>
      newGoalModal.classList.add("hidden")
    );
  if (cancelNewGoalBtn)
    cancelNewGoalBtn.addEventListener("click", () =>
      newGoalModal.classList.add("hidden")
    );
  if (newGoalModal)
    newGoalModal.addEventListener("click", (e) => {
      if (e.target === newGoalModal) newGoalModal.classList.add("hidden");
    });

  if (newGoalForm) {
    newGoalForm.addEventListener("submit", function (event) {
      event.preventDefault();
      const originalBtnText = goalSubmitBtn.textContent;
      goalSubmitBtn.textContent = "Guardando...";
      goalSubmitBtn.disabled = true;

      const formData = new FormData(newGoalForm);

      const goalData = {
        action: formData.get("id_meta") ? "editar_meta" : "crear_meta",
        id_meta: formData.get("id_meta"),
        nombre_meta: formData.get("nombre_meta"),
        monto_objetivo: formData.get("monto_objetivo"),
        fecha_limite: formData.get("fecha_limite"),
        ahorro_automatico: document.getElementById("automaticSavingCheck")
          .checked,
      };

      if (goalData.ahorro_automatico) {
        goalData.frecuencia_ahorro = formData.get("frecuencia_ahorro");
        goalData.monto_ahorro_programado = formData.get(
          "monto_ahorro_programado"
        );

        const diaSeleccionado =
          document.getElementById("dia_seleccionado").value;
        if (!diaSeleccionado && goalData.frecuencia_ahorro !== "diario") {
          alert(
            "Por favor, selecciona una fecha o día en el calendario para continuar."
          );
          goalSubmitBtn.textContent = originalBtnText;
          goalSubmitBtn.disabled = false;
          return;
        }
        if (goalData.frecuencia_ahorro !== "diario") {
          goalData.dia_seleccionado = diaSeleccionado;
        }
      }

      fetch("../models/metas_controller.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(goalData),
      })
        .then((response) => response.json())
        .then((data) => {
          if (data.success) {
            showSuccessModal("¡Felicitaciones!", data.message);
            newGoalModal.classList.add("hidden");
            fetchAndRenderGoals();
          } else {
            alert("Error: " + data.message);
          }
        })
        .catch((error) => {
          console.error("Error en la petición:", error);
          alert("Ocurrió un error al intentar guardar la meta.");
        })
        .finally(() => {
          goalSubmitBtn.textContent = originalBtnText;
          goalSubmitBtn.disabled = false;
        });
    });
  }

  // --- Listeners para Ahorro Automático ---
  if (automaticSavingCheck)
    automaticSavingCheck.addEventListener("change", updateAutomaticSavingState);
  if (savingFrequencySelect)
    savingFrequencySelect.addEventListener("change", () =>
      updateDaySelector(savingFrequencySelect.value)
    );

  // --- Listeners para el modal de Abonar ---
  if (addFundsForm) {
    addFundsForm.addEventListener("submit", function (event) {
      event.preventDefault();
      const formData = new FormData(addFundsForm);
      const amountToFund = parseFloat(formData.get("monto_abono"));
      const goalId = formData.get("id_meta");

      if (isNaN(amountToFund) || amountToFund <= 0) {
        showFailureModal(
          "Monto Inválido",
          "El monto a abonar debe ser un número mayor que cero."
        );
        return;
      }
      if (!activeGoalData) {
        showFailureModal(
          "Error de Datos",
          "No se pudo identificar la meta. Por favor, cierra el modal y vuelve a intentarlo."
        );
        return;
      }
      const amountNeeded = parseFloat(
        (activeGoalData.monto_objetivo - activeGoalData.monto_actual).toFixed(2)
      );
      if (amountToFund > amountNeeded) {
        showFailureModal(
          "Monto Excedido",
          `¡Ya casi lo logras! Solo te faltan ${formatCurrency(
            amountNeeded
          )} para completar esta meta.`
        );
        return;
      }

      const fundData = {
        action: "abonar_meta",
        id_meta: goalId,
        monto_abono: amountToFund,
      };
      fetch("../models/metas_controller.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(fundData),
      })
        .then((response) => response.json())
        .then((data) => {
          if (data.success) {
            addFundsModal.classList.add("hidden");
            if (data.meta_completada) {
              celebrateGoalCompletion(data.nombre_usuario, data.message);
            } else {
              showSuccessModal("¡Abono Exitoso!", data.message);
              fetchAndRenderGoals();
            }
          } else {
            showFailureModal("Error al Abonar", data.message);
          }
        })
        .catch((error) => {
          console.error("Error al abonar:", error);
          showFailureModal(
            "Error de Conexión",
            "No se pudo contactar al servidor."
          );
        });
    });
  }
  if (closeAddFundsModalBtn)
    closeAddFundsModalBtn.addEventListener("click", () =>
      addFundsModal.classList.add("hidden")
    );
  if (cancelAddFundsBtn)
    cancelAddFundsBtn.addEventListener("click", () =>
      addFundsModal.classList.add("hidden")
    );
  if (addFundsModal)
    addFundsModal.addEventListener("click", (e) => {
      if (e.target === addFundsModal) addFundsModal.classList.add("hidden");
    });

  // --- Listeners para el modal de Eliminar y Celebración ---
  function openDeleteGoalModal(goalId) {
    showConfirmationModal(
      "Eliminar Meta",
      "¿Estás seguro de que deseas eliminar esta meta? Esta acción no se puede deshacer.",
      () => {
        fetch("../models/metas_controller.php", {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify({ action: "eliminar_meta", id_meta: goalId }),
        })
          .then((response) => {
            if (!response.ok) {
              return response.json().then((err) => {
                throw new Error(err.message || "Error del servidor");
              });
            }
            return response.json();
          })
          .then((data) => {
            if (data.success) {
              fetchAndRenderGoals();
              showSuccessModal("Meta Eliminada", data.message);
            } else {
              showFailureModal("Error al Eliminar", data.message);
            }
          })
          .catch((err) => {
            console.error("Error en el proceso de eliminación:", err);
            showFailureModal(
              "Error de Conexión",
              err.message ||
                "No se pudo contactar al servidor para eliminar la meta."
            );
          });
      }
    );
  }
  if (celebrationCloseBtn)
    celebrationCloseBtn.addEventListener("click", () =>
      celebrationModal.classList.add("hidden")
    );
  if (celebrationNewGoalBtn) {
    celebrationNewGoalBtn.addEventListener("click", () => {
      celebrationModal.classList.add("hidden");

      showNewGoalModal(false);
    });
  }

  // =================================================================
  // --- 5. FUNCIONES AUXILIARES Y EJECUCIÓN INICIAL ---
  // =================================================================

  function formatCurrency(value) {
    return new Intl.NumberFormat("es-CO", {
      style: "currency",
      currency: "COP",
      minimumFractionDigits: 0,
    }).format(value);
  }

  function createLoadingSpinnerHTML() {
    return `
      <div class="col-span-full grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 opacity-70">
          <div class="bg-white rounded-xl shadow p-5 w-full mx-auto"><div class="animate-pulse flex space-x-4"><div class="rounded-lg bg-gray-200 h-12 w-12"></div><div class="flex-1 space-y-4 py-1"><div class="h-4 bg-gray-200 rounded w-3/4"></div><div class="h-3 bg-gray-200 rounded w-1/2"></div></div></div><div class="space-y-2 mt-6"><div class="h-3 bg-gray-200 rounded"></div><div class="h-3 bg-gray-200 rounded w-5/6"></div></div></div>
          <div class="bg-white rounded-xl shadow p-5 w-full mx-auto hidden md:block"><div class="animate-pulse flex space-x-4"><div class="rounded-lg bg-gray-200 h-12 w-12"></div><div class="flex-1 space-y-4 py-1"><div class="h-4 bg-gray-200 rounded w-3/4"></div><div class="h-3 bg-gray-200 rounded w-1/2"></div></div></div><div class="space-y-2 mt-6"><div class="h-3 bg-gray-200 rounded"></div><div class="h-3 bg-gray-200 rounded w-5/6"></div></div></div>
          <div class="bg-white rounded-xl shadow p-5 w-full mx-auto hidden lg:block"><div class="animate-pulse flex space-x-4"><div class="rounded-lg bg-gray-200 h-12 w-12"></div><div class="flex-1 space-y-4 py-1"><div class="h-4 bg-gray-200 rounded w-3/4"></div><div class="h-3 bg-gray-200 rounded w-1/2"></div></div></div><div class="space-y-2 mt-6"><div class="h-3 bg-gray-200 rounded"></div><div class="h-3 bg-gray-200 rounded w-5/6"></div></div></div>
      </div>`;
  }

  // --- Llamadas iniciales al cargar la página ---
  fetchAndRenderGoals();
  updateAutomaticSavingState();
});
