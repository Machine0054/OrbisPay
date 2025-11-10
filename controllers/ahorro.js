document.addEventListener('DOMContentLoaded', function () {
    console.log("ahorro.js cargado y listo.");

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

    fetchAndRenderGoals(); 


    // =================================================================
    // --- LÓGICA PARA CREAR Y EDITAR (MODAL UNIFICADO) ---
    // =================================================================
    function showNewGoalModal(isEditMode = false, goalData = null) {
        newGoalForm.reset();
        if (isEditMode && goalData) {
            goalModalTitle.textContent = 'Editar Meta';
            goalSubmitBtn.textContent = 'Guardar Cambios';
            goalIdToEditInput.value = goalData.id;
            document.getElementById('goalName').value = goalData.nombre;
            document.getElementById('goalAmount').value = goalData.monto;
            document.getElementById('goalDate').value = goalData.fecha_limite; // Asumiendo que tienes este campo
        } else {
            goalModalTitle.textContent = 'Crear Nueva Meta';
            goalSubmitBtn.textContent = 'Crear Meta';
            goalIdToEditInput.value = '';
        }
        newGoalModal.classList.remove('opacity-0', 'pointer-events-none');
        newGoalModal.classList.add('opacity-100');
        newGoalModal.querySelector('.modal-content').classList.remove('scale-95');
        newGoalModal.querySelector('.modal-content').classList.add('scale-100');
    }

    function hideNewGoalModal() {
        newGoalModal.classList.remove('opacity-100');
        newGoalModal.classList.add('opacity-0', 'pointer-events-none');
        newGoalModal.querySelector('.modal-content').classList.remove('scale-100');
        newGoalModal.querySelector('.modal-content').classList.add('scale-95');
    }

    // Event Listeners para el modal de Crear/Editar
    if (openNewGoalModalBtn) {
        openNewGoalModalBtn.addEventListener('click', () => showNewGoalModal(false));
    }
    if (closeNewGoalModalBtn) {
        closeNewGoalModalBtn.addEventListener('click', hideNewGoalModal);
    }
    if (cancelNewGoalBtn) {
        cancelNewGoalBtn.addEventListener('click', hideNewGoalModal);
    }
    if (newGoalModal) {
        newGoalModal.addEventListener('click', e => {
            if (e.target === newGoalModal) hideNewGoalModal();
        });
    }

    if (newGoalForm) {
        newGoalForm.addEventListener('submit', function(event) {
            event.preventDefault();
            const formData = new FormData(newGoalForm);
            const goalId = formData.get('id_meta');
            const action = goalId && goalId !== '' ? 'editar_meta' : 'crear_meta';
            
            const goalData = { action };
            formData.forEach((value, key) => { goalData[key] = value; });

            fetch('../models/ahorro_controller.php', {
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

       async function fetchAndRenderGoals() {
        // Muestra un estado de carga mientras se obtienen los datos
        if (goalsContainer) {
            goalsContainer.innerHTML = '<p class="text-center text-gray-500 col-span-full">Cargando tus metas de ahorro...</p>';
        }

        try {
            const response = await fetch('../models/ahorro_controller.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'obtener_metas' }) // La acción que espera tu PHP
            });

            const result = await response.json();

            if (!result.success) {
                throw new Error(result.message);
            }

            renderGoals(result.data);

        } catch (error) {
            console.error('Error al obtener las metas:', error);
            if (goalsContainer) {
                goalsContainer.innerHTML = `<p class="text-center text-red-500 col-span-full">Error al cargar las metas: ${error.message}</p>`;
            }
        }
    }
        function renderGoals(goals) {
        if (!goalsContainer) return;
        goalsContainer.innerHTML = ''; // Limpia el contenedor

        if (goals.length === 0) {
            goalsContainer.innerHTML = `
                <div class="col-span-full text-center bg-white/80 p-8 rounded-xl shadow-sm border border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-700">¡Aún no tienes metas!</h3>
                    <p class="text-gray-500 mt-2">Crea tu primera meta de ahorro para empezar a cumplir tus sueños.</p>
                </div>
            `;
            return;
        }

        goals.forEach(goal => {
            const progress = goal.monto_objetivo > 0 ? (goal.monto_actual / goal.monto_objetivo) * 100 : 0;
            const formattedAmount = goal.monto_actual.toLocaleString('es-CO', { style: 'currency', currency: 'COP', minimumFractionDigits: 0 });
            const formattedTarget = goal.monto_objetivo.toLocaleString('es-CO', { style: 'currency', currency: 'COP', minimumFractionDigits: 0 });

            const goalCardHTML = `
                <div class="bg-white/90 backdrop-blur-sm rounded-2xl shadow-lg border border-white/20 overflow-hidden transform hover:-translate-y-1 transition-transform duration-300">
                    <div class="p-6">
                        <div class="flex justify-between items-start">
                            <h3 class="text-xl font-bold text-gray-800">${goal.nombre_ahorro}</h3>
                            <!-- Aquí puedes agregar un menú de opciones (editar, eliminar) si lo deseas -->
                        </div>
                        <p class="text-green-600 font-semibold text-2xl mt-2">${formattedAmount}</p>
                        <p class="text-sm text-gray-500">de ${formattedTarget}</p>
                        
                        <div class="mt-4">
                            <div class="w-full bg-gray-200 rounded-full h-2.5">
                                <div class="bg-indigo-600 h-2.5 rounded-full" style="width: ${progress.toFixed(2)}%"></div>
                            </div>
                            <p class="text-right text-sm font-medium text-indigo-600 mt-1">${progress.toFixed(1)}%</p>
                        </div>
                    </div>
                    <div class="bg-gray-50/50 px-6 py-3 flex justify-end">
                        <button class="font-semibold text-indigo-600 hover:text-indigo-800 transition-colors">Abonar</button>
                    </div>
                </div>
            `;
            goalsContainer.insertAdjacentHTML('beforeend', goalCardHTML);
        });
    }
});


