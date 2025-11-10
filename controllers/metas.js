document.addEventListener('DOMContentLoaded', function () {
    console.log("metas.js cargado y listo.");

    // --- SELECCIÓN DE ELEMENTOS ---
    const goalsContainer = document.getElementById('goals-container');
    
    // Modal de Crear/Editar
    const newGoalModal = document.getElementById('newGoalModal');
    const openNewGoalModalBtn = document.getElementById('openNewGoalModalBtn');
    const closeNewGoalModalBtn = document.getElementById('closeNewGoalModalBtn');
    const cancelNewGoalBtn = document.getElementById('cancelNewGoalBtn');
    const newGoalForm = document.getElementById('newGoalForm');
    const goalModalTitle = document.getElementById('goalModalTitle');
    const goalSubmitBtn = document.getElementById('goalSubmitBtn');
    const goalIdToEditInput = document.getElementById('goalIdToEdit');

    // Modal de Abonar
    const addFundsModal = document.getElementById('addFundsModal');
    const closeAddFundsModalBtn = document.getElementById('closeAddFundsModalBtn');
    const cancelAddFundsBtn = document.getElementById('cancelAddFundsBtn');
    const addFundsForm = document.getElementById('addFundsForm');
    const goalIdToFundInput = document.getElementById('goalIdToFund');

    // Modal de Eliminar
    const deleteGoalModal = document.getElementById('deleteGoalModal');
    const cancelDeleteBtn = document.getElementById('cancelDeleteBtn');
    const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
    const goalIdToDeleteInput = document.getElementById('goalIdToDelete');

    // Menú de Opciones
    const goalOptionsMenu = document.getElementById('goalOptionsMenu');
    const editGoalOption = document.getElementById('editGoalOption');
    const deleteGoalOption = document.getElementById('deleteGoalOption');

    let activeGoalId = null; // Variable para saber sobre qué meta estamos actuando

    // --- INICIO ---
    fetchAndRenderGoals();

    // =================================================================
    // --- 1. OBTENER Y RENDERIZAR METAS ---
    // =================================================================
    function fetchAndRenderGoals() {
        if (!goalsContainer) return;
        goalsContainer.innerHTML = '<p class="text-gray-400">Cargando metas...</p>';

        fetch('../models/metas_controller.php?action=obtener_metas', { method: 'GET' })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                goalsContainer.innerHTML = '';
                if (result.data.length > 0) {
                    result.data.forEach(goal => {
                        const goalCardHTML = createGoalCard(goal);
                        goalsContainer.insertAdjacentHTML('beforeend', goalCardHTML);
                    });
                } else {
                    goalsContainer.innerHTML = '<p class="text-gray-500 col-span-full text-center">Aún no tienes metas. ¡Crea la primera!</p>';
                }
            } else {
                goalsContainer.innerHTML = `<p class="text-red-500">Error: ${result.message}</p>`;
            }
        })
        .catch(error => {
            console.error('Error al obtener las metas:', error);
            goalsContainer.innerHTML = `<p class="text-red-500">Ocurrió un error de red al cargar las metas.</p>`;
        });
    }

    function createGoalCard(goal) {
        const progressPercentage = goal.monto_objetivo > 0 ? (goal.monto_actual / goal.monto_objetivo) * 100 : 0;
        const formattedObjective = new Intl.NumberFormat('es-CO').format(goal.monto_objetivo);
        const formattedCurrent = new Intl.NumberFormat('es-CO').format(goal.monto_actual);

        return `
            <div class="goal-card bg-white rounded-xl shadow-lg p-5 flex flex-col text-bold" data-goal-id="${goal.id_meta}">
                <div class="flex items-center space-x-4 mb-4">
                    <div class="w-12 h-12 rounded-lg bg-gray-700 flex items-center justify-center text-xs text-gray-400">
                        ${goal.imagen_url ? `<img src="${goal.imagen_url}" class="w-full h-full object-cover rounded-lg">` : 'Image'}
                    </div>
                    <div>
                        <h3 class="font-bold text-lg">${goal.nombre_meta}</h3>
                        <p class="text-sm text-gray-400">Objetivo: $${formattedObjective}</p>
                    </div>
                </div>
                <div class="w-full bg-gray-300 rounded-full h-2.5 mb-2">
                    <div class="bg-emerald-500 h-2.5 rounded-full" style="width: ${progressPercentage.toFixed(2)}%"></div>
                </div>
                <div class="flex justify-between text-sm font-medium text-semibold-300 mb-4">
                    <span>Progreso: ${progressPercentage.toFixed(1)}%</span>
                    <span>$${formattedCurrent}</span>
                </div>
                <div class="mt-auto flex space-x-2">
                    <button class="add-funds-btn flex-1 px-3 py-2 bg-indigo-700 text-white text-sm font-semibold rounded-lg hover:bg-indigo-600" data-goal-id="${goal.id_meta}">Abonar</button>
                    <button class="options-btn px-3 py-2 bg-gray-700 rounded-lg hover:bg-gray-600" data-goal-id="${goal.id_meta}">
                        <svg class="w-5 h-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20"><path d="M6 10a2 2 0 11-4 0 2 2 0 014 0zM12 10a2 2 0 11-4 0 2 2 0 014 0zM16 12a2 2 0 100-4 2 2 0 000 4z"></path></svg>
                    </button>
                </div>
            </div>
        `;
    }

    // =================================================================
    // --- 2. LÓGICA PARA CREAR Y EDITAR (MODAL UNIFICADO) ---
    // =================================================================
    function showNewGoalModal(isEditMode = false, goalData = null) {
        newGoalForm.reset();
        if (isEditMode && goalData) {
            goalModalTitle.textContent = 'Editar Meta';
            goalSubmitBtn.textContent = 'Guardar Cambios';
            goalIdToEditInput.value = goalData.id;
            newGoalForm.querySelector('#goalName').value = goalData.nombre;
            newGoalForm.querySelector('#goalAmount').value = goalData.monto;
        } else {
            goalModalTitle.textContent = 'Crear Nueva Meta';
            goalSubmitBtn.textContent = 'Crear Meta';
            goalIdToEditInput.value = '';
        }
        newGoalModal.classList.remove('hidden');
    }

    function hideNewGoalModal() { if (newGoalModal) newGoalModal.classList.add('hidden'); }
    if (openNewGoalModalBtn) openNewGoalModalBtn.addEventListener('click', () => showNewGoalModal(false));
    if (closeNewGoalModalBtn) closeNewGoalModalBtn.addEventListener('click', hideNewGoalModal);
    if (cancelNewGoalBtn) cancelNewGoalBtn.addEventListener('click', hideNewGoalModal);
    if (newGoalModal) newGoalModal.addEventListener('click', e => { if (e.target === newGoalModal) hideNewGoalModal(); });

    if (newGoalForm) {
        newGoalForm.addEventListener('submit', function(event) {
            event.preventDefault();
            const formData = new FormData(newGoalForm);
            const goalId = formData.get('id_meta');
            const action = goalId ? 'editar_meta' : 'crear_meta';
            
            const goalData = { action };
            formData.forEach((value, key) => { goalData[key] = value; });

            fetch('../models/metas_controller.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(goalData)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    hideNewGoalModal();
                    fetchAndRenderGoals();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => console.error('Error al guardar la meta:', error));
        });
    }

    // =================================================================
    // --- 3. LÓGICA PARA ABONAR, EDITAR Y ELIMINAR ---
    // =================================================================
    
    // --- Control del Modal de Abono ---
    function showAddFundsModal() { if (addFundsModal) addFundsModal.classList.remove('hidden'); }
    function hideAddFundsModal() { if (addFundsModal) addFundsModal.classList.add('hidden'); }
    if (closeAddFundsModalBtn) closeAddFundsModalBtn.addEventListener('click', hideAddFundsModal);
    if (cancelAddFundsBtn) cancelAddFundsBtn.addEventListener('click', hideAddFundsModal);
    if (addFundsModal) addFundsModal.addEventListener('click', e => { if (e.target === addFundsModal) hideAddFundsModal(); });

    // --- Control del Modal de Eliminar ---
    function showDeleteModal() { if (deleteGoalModal) deleteGoalModal.classList.remove('hidden'); }
    function hideDeleteModal() { if (deleteGoalModal) deleteGoalModal.classList.add('hidden'); }
    if (cancelDeleteBtn) cancelDeleteBtn.addEventListener('click', hideDeleteModal);
    if (deleteGoalModal) deleteGoalModal.addEventListener('click', e => { if (e.target === deleteGoalModal) hideDeleteModal(); });

    // --- Cierre global del menú de opciones ---
    document.addEventListener('click', function(event) {
        if (goalOptionsMenu && !goalOptionsMenu.contains(event.target) && !event.target.closest('.options-btn')) {
            goalOptionsMenu.classList.add('hidden');
        }
    });

    // --- Delegación de eventos para las tarjetas ---
    if (goalsContainer) {
        goalsContainer.addEventListener('click', function(event) {
            const abonarBtn = event.target.closest('.add-funds-btn');
            const optionsBtn = event.target.closest('.options-btn');

            if (abonarBtn) {
                const goalId = abonarBtn.dataset.goalId;
                if (goalIdToFundInput) goalIdToFundInput.value = goalId;
                showAddFundsModal();
            }

            if (optionsBtn) {
                event.preventDefault();
                event.stopPropagation();
                activeGoalId = optionsBtn.dataset.goalId;
                const btnRect = optionsBtn.getBoundingClientRect();
                goalOptionsMenu.style.top = `${btnRect.bottom + window.scrollY}px`;
                goalOptionsMenu.style.left = `${btnRect.left + window.scrollX - goalOptionsMenu.offsetWidth + btnRect.width}px`;
                goalOptionsMenu.classList.remove('hidden');
            }
        });
    }

    // --- Event Listeners para las opciones del menú ---
    if (editGoalOption) {
        editGoalOption.addEventListener('click', function(event) {
            event.preventDefault();
            goalOptionsMenu.classList.add('hidden');
            if (!activeGoalId) return;
            const card = document.querySelector(`.goal-card[data-goal-id='${activeGoalId}']`);
            if (card) {
                const goalData = {
                    id: activeGoalId,
                    nombre: card.querySelector('h3').textContent,
                    monto: card.querySelector('p').textContent.match(/[\d,.]+/)[0].replace(/[.,]/g, '')
                };
                showNewGoalModal(true, goalData);
            }
        });
    }

    if (deleteGoalOption) {
        deleteGoalOption.addEventListener('click', function(event) {
            event.preventDefault();
            goalOptionsMenu.classList.add('hidden');
            if (!activeGoalId) return;
            if (goalIdToDeleteInput) goalIdToDeleteInput.value = activeGoalId;
            showDeleteModal();
        });
    }

    // --- Envío de formularios de Abono y Eliminación ---
    if (addFundsForm) {
        addFundsForm.addEventListener('submit', function(event) {
            event.preventDefault();
            const formData = new FormData(addFundsForm);
            const fundData = {
                action: 'abonar_meta',
                id_meta: formData.get('id_meta'),
                monto_abono: formData.get('monto_abono')
            };
            fetch('../models/metas_controller.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(fundData)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    hideAddFundsModal();
                    fetchAndRenderGoals();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => console.error('Error al abonar:', error));
        });
    }

    if (confirmDeleteBtn) {
        confirmDeleteBtn.addEventListener('click', function() {
            const goalId = goalIdToDeleteInput.value;
            if (!goalId) return;
            fetch('../models/metas_controller.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'eliminar_meta', id_meta: goalId })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    hideDeleteModal();
                    fetchAndRenderGoals();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => console.error('Error al eliminar:', error));
        });
    }
});
