// Archivo: deudas.js
// Corresponde a la versión final del HTML de deudas.php

document.addEventListener('DOMContentLoaded', function() {
    console.log("deudas.js cargado y listo.");

    // --- SELECCIÓN DE ELEMENTOS DEL DOM ---
    const deudasContainer = document.getElementById('deudas-container');
    const prestamosContainer = document.getElementById('prestamos-container');
    
    // Modal para crear nuevo registro
    const newDebtModal = document.getElementById('newDebtModal');
    const openNewDebtModalBtn = document.getElementById('openNewDebtModalBtn');
    const closeNewDebtModalBtn = document.getElementById('closeNewDebtModalBtn');
    const cancelNewDebtBtn = document.getElementById('cancelNewDebtBtn');
    const newDebtForm = document.getElementById('newDebtForm');

    // Modal para añadir un abono
    const addPaymentModal = document.getElementById('addPaymentModal');
    const closeAddPaymentModalBtn = document.getElementById('closeAddPaymentModalBtn');
    const cancelPaymentBtn = document.getElementById('cancelPaymentBtn');
    const addPaymentForm = document.getElementById('addPaymentForm');

    const historyModal = document.getElementById('historyModal');
    const closeHistoryModalBtn = document.getElementById('closeHistoryModalBtn');
    const historyModalTitle = document.getElementById('historyModalTitle');
    const historyModalBody = document.getElementById('historyModalBody');
    // Al cargar la página, obtenemos y mostramos los datos.
    fetchAndRenderDebts();

    // =================================================================
    // --- 1. OBTENER Y RENDERIZAR DATOS ---
    // =================================================================

    async function fetchAndRenderDebts() {
        // Mostramos un estado de carga mientras se obtienen los datos.
        if (deudasContainer) deudasContainer.innerHTML = '<p class="text-center text-gray-400 py-4">Cargando deudas...</p>';
        if (prestamosContainer) prestamosContainer.innerHTML = '<p class="text-center text-gray-400 py-4">Cargando préstamos...</p>';

        try {
            const response = await fetch('../models/api_deudas.php?action=obtener_deudas_prestamos');
            if (!response.ok) throw new Error('Error de red al obtener los datos.');

            const result = await response.json();

            if (result.success) {
                renderItems(result.data.deudas, deudasContainer, 'Deuda');
                renderItems(result.data.prestamos, prestamosContainer, 'Préstamo');
            } else {
                throw new Error(result.message || 'Error al procesar la solicitud.');
            }
        } catch (error) {
            console.error('Error en fetchAndRenderDebts:', error);
            const errorMessage = `<p class="text-center text-red-500 py-4">Error al cargar: ${error.message}</p>`;
            if (deudasContainer) deudasContainer.innerHTML = errorMessage;
            if (prestamosContainer) prestamosContainer.innerHTML = errorMessage;
        }
    }

    function renderItems(items, container, type) {
        container.innerHTML = ''; // Limpiamos el contenedor
        if (items.length > 0) {
            items.forEach(item => {
                const cardHTML = createCardHTML(item, type);
                container.insertAdjacentHTML('beforeend', cardHTML);
            });
        } else {
            container.innerHTML = `<p class="text-center text-gray-500 dark:text-gray-400 py-4">No tienes ${type.toLowerCase()}s activos.</p>`;
        }
    }

function createCardHTML(item, type) {
    const isDeuda = type === 'Deuda';
    const borderColor = isDeuda ? 'border-red-500' : 'border-green-500';
    const textColor = isDeuda ? 'text-red-600 ' : 'text-green-600 ';
    const bgColor = isDeuda ? 'bg-red-500' : 'bg-green-500';
    const statusColor = isDeuda ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800';
    
    const progressPercentage = item.monto_inicial > 0 ? ((item.monto_inicial - item.saldo_actual) / item.monto_inicial) * 100 : 0;
    const formattedSaldo = new Intl.NumberFormat('es-CO', { style: 'currency', currency: 'COP', minimumFractionDigits: 0 }).format(item.saldo_actual);
    const formattedInicial = new Intl.NumberFormat('es-CO', { style: 'currency', currency: 'COP', minimumFractionDigits: 0 }).format(item.monto_inicial);

    // --- INICIO DE LA NUEVA PLANTILLA HTML OPTIMIZADA ---
    return `
        <div class="bg-white rounded-xl shadow-md p-1 border-l-4 ${borderColor}" data-id="${item.id_deuda}">
            
            <!-- SECCIÓN SUPERIOR: Título y Estado -->
            <div class="flex justify-between items-center mb-1">
                <div>
                    <p class="font-bold text-gray-800  truncate">${item.descripcion}</p>
                    <p class="text-xs text-gray-500">${isDeuda ? 'A:' : 'De:'} ${item.acreedor_deudor || 'N/A'}</p>
                </div>
                <span class="text-xs font-semibold ${statusColor} px-2 py-0.5 rounded-full flex-shrink-0 ml-2">${item.estado}</span>
            </div>

            <!-- SECCIÓN MEDIA: Saldo y Barra de Progreso -->
            <div class="space-y-1">
                <div class="flex justify-between items-baseline">
                    <p class="text-sm text-gray-600 ">${isDeuda ? 'Saldo Pendiente' : 'Saldo por Cobrar'}</p>
                    <p class="text-xs text-gray-500 ">de ${formattedInicial}</p>
                </div>
                <p class="text-xl font-bold ${textColor}">${formattedSaldo}</p>
                <div class="w-full bg-gray-200  rounded-full h-1.5">
                    <div class="${bgColor} h-1.5 rounded-full" style="width: ${progressPercentage.toFixed(2)}%;"></div>
                </div>
            </div>

            <!-- SECCIÓN INFERIOR: Botones de Acción -->
            <div class="mt-2 pt-2 border-t border-gray-100  flex justify-end space-x-3">
                <button class="view-history-btn text-xs text-gray-600 hover:underline" data-id="${item.id_deuda}">Ver Historial</button>
                <button class="add-payment-btn text-xs font-semibold text-indigo-600 hover:underline" data-id="${item.id_deuda}">${isDeuda ? 'Hacer Abono' : 'Registrar Pago'}</button>
            </div>
        </div>
    `;
    // --- FIN DE LA NUEVA PLANTILLA HTML ---
}

    // =================================================================
    // --- 2. LÓGICA PARA MANEJAR MODALES ---
    // =================================================================

    // --- Modal de Nuevo Registro ---
    function showNewDebtModal() { if (newDebtModal) newDebtModal.classList.remove('hidden'); }
    function hideNewDebtModal() { if (newDebtModal) newDebtModal.classList.add('hidden'); }

    if (openNewDebtModalBtn) openNewDebtModalBtn.addEventListener('click', showNewDebtModal);
    if (closeNewDebtModalBtn) closeNewDebtModalBtn.addEventListener('click', hideNewDebtModal);
    if (cancelNewDebtBtn) cancelNewDebtBtn.addEventListener('click', hideNewDebtModal);
    if (newDebtModal) newDebtModal.addEventListener('click', e => { if (e.target === newDebtModal) hideNewDebtModal(); });

    // --- Modal de Añadir Abono ---
    function showAddPaymentModal() { if (addPaymentModal) addPaymentModal.classList.remove('hidden'); }
    function hideAddPaymentModal() { if (addPaymentModal) addPaymentModal.classList.add('hidden'); }

    if (closeAddPaymentModalBtn) closeAddPaymentModalBtn.addEventListener('click', hideAddPaymentModal);
    if (cancelPaymentBtn) cancelPaymentBtn.addEventListener('click', hideAddPaymentModal);
    if (addPaymentModal) addPaymentModal.addEventListener('click', e => { if (e.target === addPaymentModal) hideAddPaymentModal(); });


    function showHistoryModal() { if (historyModal) historyModal.classList.remove('hidden'); }
    function hideHistoryModal() { if (historyModal) historyModal.classList.add('hidden'); }

    if (closeHistoryModalBtn) closeHistoryModalBtn.addEventListener('click', hideHistoryModal);
    if (historyModal) historyModal.addEventListener('click', e => { if (e.target === historyModal) hideHistoryModal(); });

    // =================================================================
    // --- 3. LÓGICA DE ENVÍO DE FORMULARIOS ---
    // =================================================================

    // --- Formulario para Crear Nuevo Registro ---
    if (newDebtForm) {
        newDebtForm.addEventListener('submit', async function(event) {
            event.preventDefault();
            const formData = new FormData(newDebtForm);
            const data = Object.fromEntries(formData.entries());
            data.action = 'crear_deuda_prestamo';

            try {
                const response = await fetch('../models/api_deudas.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(data)
                });

                const result = await response.json();

                if (result.success) {
                    alert(result.message);
                    newDebtForm.reset();
                    hideNewDebtModal();
                    fetchAndRenderDebts(); // Refrescamos la lista para mostrar el nuevo ítem
                } else {
                    throw new Error(result.message);
                }
            } catch (error) {
                console.error('Error al crear el registro:', error);
                alert(`Error: ${error.message}`);
            }
        });
    }

    // =================================================================
    //  4. LÓGICA PARA REGISTRAR ABONOS 
    // =================================================================

        // Usamos delegación de eventos para manejar los clics en los botones de las tarjetas
        document.body.addEventListener('click', function(event) {
            // Verificamos si el clic fue en un botón de "Hacer Abono"
            if (event.target.classList.contains('add-payment-btn')) {
                const card = event.target.closest('.bg-white'); // Busca la tarjeta padre
                const idDeuda = card.dataset.id;
                
                // Guardamos el ID en el campo oculto del formulario de abono
                const idDeudaInput = addPaymentForm.querySelector('input[name="id_deuda"]');
                idDeudaInput.value = idDeuda;

                // Ponemos la fecha de hoy por defecto
                const fechaAbonoInput = addPaymentForm.querySelector('input[name="fecha_abono"]');
                fechaAbonoInput.valueAsDate = new Date();
                
                // Mostramos el modal
                showAddPaymentModal();
            }
        });

        // Manejo del envío del formulario de abono
        if (addPaymentForm) {
            addPaymentForm.addEventListener('submit', async function(event) {
                event.preventDefault();
                const formData = new FormData(addPaymentForm);
                const data = Object.fromEntries(formData.entries());
                data.action = 'registrar_abono';

                try {
                    const response = await fetch('../models/api_deudas.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify(data)
                    });

                    const result = await response.json();

                    if (result.success) {
                        alert(result.message);
                        addPaymentForm.reset();
                        hideAddPaymentModal();
                        // ¡Refrescamos toda la lista para ver los cambios al instante!
                        fetchAndRenderDebts(); 
                    } else {
                        throw new Error(result.message);
                    }
                } catch (error) {
                    console.error('Error al registrar el abono:', error);
                    alert(`Error: ${error.message}`);
                }
            });
        }


        document.body.addEventListener('click', async function(event) {
        // Verificamos si el clic fue en un botón de "Ver Historial"
        if (event.target.classList.contains('view-history-btn')) {
        const card = event.target.closest('.bg-white');
        const idDeuda = card.dataset.id;

        // Mostramos un estado de carga en el modal
        historyModalBody.innerHTML = '<p class="text-center text-gray-400 py-4">Cargando historial...</p>';
        historyModalTitle.textContent = '';
        showHistoryModal();

                try {
                    const response = await fetch(`../models/api_deudas.php?action=obtener_historial_abonos&id_deuda=${idDeuda}`);
                    if (!response.ok) throw new Error('Error de red.');

                    const result = await response.json();

                    if (result.success) {
                        // Actualizamos el título del modal
                        historyModalTitle.textContent = result.descripcion_deuda;
                        
                        // Renderizamos el historial
                        renderHistory(result.historial);
                    } else {
                        throw new Error(result.message);
                    }
                } catch (error) {
                    historyModalBody.innerHTML = `<p class="text-center text-red-500 py-4">${error.message}</p>`;
                }
            }
        });

        function renderHistory(historial) {
            historyModalBody.innerHTML = ''; // Limpiamos el cuerpo del modal

            if (historial.length === 0) {
                historyModalBody.innerHTML = '<p class="text-center text-gray-500 py-4">No se han registrado abonos para este ítem.</p>';
                return;
            }

            const listContainer = document.createElement('div');
            listContainer.className = 'space-y-3';

            historial.forEach(abono => {
                const formattedMonto = new Intl.NumberFormat('es-CO', { style: 'currency', currency: 'COP', minimumFractionDigits: 0 }).format(abono.monto_abono);
                const formattedFecha = new Date(abono.fecha_abono + 'T00:00:00').toLocaleDateString('es-CO', { year: 'numeric', month: 'long', day: 'numeric' });

                const abonoHTML = `
                    <div class="flex justify-between items-center bg-gray-50 dark:bg-gray-700/50 p-3 rounded-lg">
                        <div>
                            <p class="font-medium text-gray-800 dark:text-gray-200">${formattedMonto}</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">${formattedFecha}</p>
                        </div>
                        <!-- Podríamos añadir un botón para eliminar el abono en el futuro -->
                    </div>
                `;
                listContainer.insertAdjacentHTML('beforeend', abonoHTML);
            });

            historyModalBody.appendChild(listContainer);
        }

});
