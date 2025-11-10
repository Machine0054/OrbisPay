<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Control de Gastos</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="script.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style>
    .progress-bar {
        height: 8px;
        background-color: #e5e7eb;
        border-radius: 9999px;
        overflow: hidden;
    }

    .progress-fill {
        height: 100%;
        border-radius: 9999px;
        transition: width 0.3s ease-in-out;
    }

    .input-currency {
        position: relative;
    }

    .input-currency span {
        position: absolute;
        left: 12px;
        top: 50%;
        transform: translateY(-50%);
        color: #6b7280;
    }

    .input-currency input {
        padding-left: 24px;
    }

    #sidebar {
        transition: transform 0.3s ease-in-out;
    }

    #sidebar.active {
        transform: translateX(0);
    }

    #sidebarOverlay {
        transition: opacity 0.3s ease-in-out;
    }

    #sidebarOverlay.active {
        opacity: 1;
        pointer-events: auto;
    }

    @media (min-width: 769px) {
        #sidebar.collapsed {
            transform: translateX(-100%);
        }
    }
    </style>
</head>

<body class="bg-gray-100 flex">
    <!-- Sidebar -->
    <div id="sidebar"
        class="fixed inset-y-0 left-0 w-64 bg-white shadow-lg transform -translate-x-full md:translate-x-0 transition-transform duration-300 ease-in-out">
        <div class="p-4">
            <h2 class="text-xl font-bold text-gray-800">Menú</h2>
        </div>
        <nav class="mt-4">
            <a href="#"
                class="sidebar-item block px-4 py-2 text-gray-600 hover:bg-indigo-100 hover:text-indigo-600 active"
                onclick="setActiveItem(this)">Reportes Financieros</a>
            <a href="#" class="sidebar-item block px-4 py-2 text-gray-600 hover:bg-indigo-100 hover:text-indigo-600"
                onclick="setActiveItem(this)">Otra Sección</a>
        </nav>
    </div>
    <div id="sidebarOverlay"
        class="fixed inset-0 bg-black bg-opacity-50 hidden md:hidden transition-opacity duration-300 ease-in-out"></div>

    <!-- Main Content -->
    <div class="flex-1 flex flex-col">
        <!-- Header -->
        <header class="bg-white/80 backdrop-blur-sm shadow-lg px-6 py-4 flex justify-between items-center">
            <div class="flex items-center space-x-4">
                <button id="sidebarToggle" class="text-gray-600 hover:text-gray-800 md:hidden">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                </button>
                <div>
                    <h1 class="text-3xl font-bold text-gray-800">Reportes Financieros</h1>
                    <p class="text-gray-600 mt-1">Análisis detallado de tus finanzas personales</p>
                </div>
            </div>
            <div class="flex items-center space-x-6">
                <div class="flex items-center space-x-2 bg-emerald-50 px-4 py-2 rounded-full">
                    <div class="w-3 h-3 bg-emerald-500 rounded-full pulse-animation"></div>
                    <span class="text-emerald-700 font-medium">En línea</span>
                </div>
                <div class="text-right">
                    <p class="text-gray-600">Bienvenido,</p>
                    <p class="font-semibold text-gray-800">
                        <?php echo strtoupper(htmlspecialchars($_SESSION['nombre'])); ?>
                    </p>
                </div>
                <div class="relative">
                    <img src="https://i.pravatar.cc/40" alt="Avatar" class="rounded-full ring-4 ring-white shadow-lg">
                    <div class="absolute -top-1 -right-1 w-4 h-4 bg-emerald-500 rounded-full border-2 border-white">
                    </div>
                </div>
            </div>
        </header>

        <!-- Budget Content -->
        <main class="flex-1 p-6 space-y-6">
            <!-- Formulario para agregar gastos -->
            <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-xl p-6 border border-white/20">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Agregar Gasto</h3>
                <form id="addExpenseForm" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Categoría</label>
                        <select id="expenseCategory"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">Selecciona una categoría</option>
                            <option value="alimentacion">🍽️ Alimentación</option>
                            <option value="transporte">🚗 Transporte</option>
                            <option value="servicios">💡 Servicios</option>
                            <option value="entretenimiento">🎬 Entretenimiento</option>
                            <option value="salud">🏥 Salud</option>
                            <option value="otros">📦 Otros</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Monto (COP)</label>
                        <div class="input-currency">
                            <span>$</span>
                            <input type="text" id="expenseAmount"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500"
                                placeholder="0">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Descripción</label>
                        <input type="text" id="expenseDescription"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500"
                            placeholder="Descripción del gasto">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Fecha</label>
                        <input type="date" id="expenseDate"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    <div class="mt-6 flex justify-end">
                        <button type="submit"
                            class="px-4 py-2 bg-indigo-600 text-white rounded-lg font-medium hover:bg-indigo-700">Agregar
                            Gasto</button>
                    </div>
                </form>
            </div>

            <!-- Budget Overview -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-xl p-6 budget-card border border-white/20">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-sm font-medium text-gray-600 uppercase tracking-wide">Presupuesto total</h2>
                            <p class="text-3xl font-bold text-indigo-600 mt-2">$0</p>
                            <p class="text-sm text-gray-500 mt-1">Para el período actual</p>
                        </div>
                        <div class="bg-indigo-100 p-3 rounded-full">
                            <svg class="w-8 h-8 text-indigo-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M4 4a2 2 0 00-2 2v4a2 2 0 002 2V6h10a2 2 0 00-2-2H4zm2 6a2 2 0 012-2h8a2 2 0 012 2v4a2 2 0 01-2 2H8a2 2 0 01-2-2v-4zm6 4a2 2 0 100-4 2 2 0 000 4z"
                                    clip-rule="evenodd" />
                            </svg>
                        </div>
                    </div>
                </div>
                <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-xl p-6 budget-card border border-white/20">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-sm font-medium text-gray-600 uppercase tracking-wide">Presupuesto usado</h2>
                            <p class="text-3xl font-bold text-red-600 mt-2">$0</p>
                            <p class="text-sm text-gray-500 mt-1">0% del total</p>
                        </div>
                        <div class="bg-red-100 p-3 rounded-full">
                            <svg class="w-8 h-8 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zM7 9a1 1 0 000 2h6a1 1 0 100-2H7z"
                                    clip-rule="evenodd" />
                            </svg>
                        </div>
                    </div>
                </div>
                <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-xl p-6 budget-card border border-white/20">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-sm font-medium text-gray-600 uppercase tracking-wide">Presupuesto restante
                            </h2>
                            <p class="text-3xl font-bold text-emerald-600 mt-2">$0</p>
                            <p class="text-sm text-gray-500 mt-1">0% del total</p>
                        </div>
                        <div class="bg-emerald-100 p-3 rounded-full">
                            <svg class="w-8 h-8 text-emerald-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-8.293l-3-3a1 1 0 00-1.414 0l-3 3a1 1 0 001.414 1.414L9 9.414V13a1 1 0 102 0V9.414l1.293 1.293a1 1 0 001.414-1.414z"
                                    clip-rule="evenodd" />
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Budget Progress -->
            <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-xl p-6 border border-white/20">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Progreso del presupuesto</h3>
                <div class="space-y-4">
                    <div>
                        <div class="flex justify-between items-center mb-1">
                            <span class="text-sm font-medium text-gray-700">Presupuesto total</span>
                            <span class="text-sm font-semibold text-indigo-600">$0</span>
                        </div>
                        <div class="progress-bar">
                            <div class="progress-fill bg-indigo-500" style="width: 0%"></div>
                        </div>
                    </div>
                    <div>
                        <div class="flex justify-between items-center mb-1">
                            <span class="text-sm font-medium text-gray-700">Presupuesto utilizado</span>
                            <span class="text-sm font-semibold text-red-600">$0 (0%)</span>
                        </div>
                        <div class="progress-bar">
                            <div class="progress-fill bg-red-500" style="width: 0%"></div>
                        </div>
                    </div>
                    <div>
                        <div class="flex justify-between items-center mb-1">
                            <span class="text-sm font-medium text-gray-700">Presupuesto restante</span>
                            <span class="text-sm font-semibold text-emerald-600">$0 (0%)</span>
                        </div>
                        <div class="progress-bar">
                            <div class="progress-fill bg-emerald-500" style="width: 0%"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Budget by Category -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-xl p-6 border border-white/20">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold text-gray-800">Asignación por categoría</h3>
                        <button onclick="showAddBudgetModal()"
                            class="flex items-center space-x-1 bg-indigo-500 hover:bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z"
                                    clip-rule="evenodd" />
                            </svg>
                            <span>Agregar</span>
                        </button>
                    </div>
                    <div class="space-y-4"></div>
                </div>
                <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-xl p-6 border border-white/20">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold text-gray-800">Distribución del presupuesto</h3>
                        <div class="relative">
                            <select id="budgetChartType"
                                class="appearance-none bg-white border border-gray-300 rounded-lg pl-3 pr-8 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                <option value="doughnut">Gráfico de dona</option>
                                <option value="pie">Gráfico de torta</option>
                                <option value="bar">Gráfico de barras</option>
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                        clip-rule="evenodd" />
                                </svg>
                            </div>
                        </div>
                    </div>
                    <canvas id="budgetChart" height="300"></canvas>
                </div>
            </div>

            <!-- Budget Alerts -->
            <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-xl p-6 border border-white/20">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Alertas de presupuesto</h3>
                <div class="space-y-3"></div>
            </div>
        </main>
    </div>

    <!-- Add Budget Modal -->
    <div id="addBudgetModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
        <div class="bg-white rounded-2xl shadow-2xl p-6 w-full max-w-md">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-xl font-semibold text-gray-800">Agregar presupuesto</h3>
                <button onclick="hideAddBudgetModal()" class="text-gray-500 hover:text-gray-700">
                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                            clip-rule="evenodd" />
                    </svg>
                </button>
            </div>
            <form id="addBudgetForm" class="space-y-4" onsubmit="addBudgetCategory(event)">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Monto presupuestado (COP)</label>
                    <div class="input-currency">
                        <span>$</span>
                        <input type="text" id="newBudgetAmount"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500"
                            placeholder="0">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Período</label>
                    <select id="newBudgetPeriod"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="mensual">Mensual</option>
                        <option value="quincenal">Quincenal</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Fecha de inicio</label>
                    <input type="date" id="newBudgetStartDate"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Fecha de fin</label>
                    <input type="date" id="newBudgetEndDate"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div class="mt-6 flex justify-end space-x-3">
                    <button type="button" onclick="hideAddBudgetModal()"
                        class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 font-medium">Cancelar</button>
                    <button type="submit"
                        class="px-4 py-2 bg-indigo-600 text-white rounded-lg font-medium hover:bg-indigo-700">Agregar</button>
                </div>
            </form>
        </div>
    </div>
</body>

</html>