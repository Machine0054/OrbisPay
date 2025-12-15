<?php
$grupos_sidebar = [
    'transacciones' => ['registro_ingresos', 'registro_gastos'],
    'gestion' => ['metas', 'gestionar_categorias', 'deudas', 'presupuesto'],
    'analisis' => ['reportes', 'Historial'],
    'sistema' => ['configuracion']
];

// 2. Encontramos el grupo de la página actual.
$grupo_activo = '';
// La variable $pagina_actual ya la tienes definida en tus archivos principales (ej: dashboard2.php, deudas.php, etc.)
if (isset($pagina_actual)) {
    foreach ($grupos_sidebar as $grupo => $paginas) {
        if (in_array($pagina_actual, $paginas)) {
            $grupo_activo = $grupo;
            break;
        }
    }
}
?>

<aside id="logo-sidebar"
    class="fixed top-0 left-0 z-40 w-64 h-screen pt-20 transition-transform -translate-x-full bg-white border-r border-gray-200 sm:translate-x-0 dark:bg-gray-800 dark:border-gray-700"
    aria-label="Sidebar">
    <!-- fixed top-0 left-0 z-40 w-64 h-screen transition-transform -translate-x-full sm:translate-x-0 -->
    <div class="h-full px-3 pb-4 overflow-y-auto bg-white dark:bg-gray-800">

        <div class="mb-6">
            <div class="px-3 mb-3">
                <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Principal</span>
            </div>
            <ul class="space-y-2 font-medium">
                <li>
                    <a href="dashboard2.php"
                        class="flex items-center sidebar-item p-2 text-gray-900 rounded-lg dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 group <?php echo ($pagina_actual === 'dashboard2') ? 'active' : ''; ?>">
                        <svg class="w-5 h-5 text-gray-500 transition duration-75 dark:text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white"
                            aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor"
                            viewBox="0 0 22 21">
                            <path
                                d="M16.975 11H10V4.025a1 1 0 0 0-1.066-.998 8.5 8.5 0 1 0 9.039 9.039.999.999 0 0 0-1-1.066h.002Z" />
                            <path
                                d="M12.5 0c-.157 0-.311.01-.565.027A1 1 0 0 0 11 1.02V10h8.975a1 1 0 0 0 1-.935c.013-.188.028-.374.028-.565A8.51 8.51 0 0 0 12.5 0Z" />
                        </svg>
                        <span class="ms-3">Dashboard</span>
                    </a>
                </li>
            </ul>
        </div>

        <!-- Divider -->
        <div class="border-t border-gray-100 dark:border-gray-700 my-6 mx-2"></div>
        <div id="accordion-sidebar" data-accordion="collapse">
            <!-- Transacciones -->
            <div class="mb-2">
                <!-- Reducimos el margen inferior -->
                <button type="button"
                    class="flex items-center w-full p-2 text-xs font-semibold text-gray-400 uppercase tracking-wider transition duration-75 rounded-lg group hover:bg-gray-100"
                    data-accordion-target="#accordion-transacciones" aria-expanded="true"
                    aria-controls="accordion-transacciones">

                    <span>Transacciones</span>
                    <svg data-accordion-icon class="w-3 h-3 ml-auto rotate-180 shrink-0" aria-hidden="true"
                        xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 10 6">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 5 5 1 1 5" />
                    </svg>
                </button>
                <ul id="accordion-transacciones" class="py-2 space-y-2 font-medium hidden"
                    aria-labelledby="accordion-transacciones-heading">
                    <!-- aria-labelledby="accordion-gestion-heading" -->
                    <li>
                        <a href="registro_ingresos.php"
                            class="flex items-center sidebar-item p-2 pl-11 text-gray-900 rounded-lg dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 group <?php echo ($pagina_actual === 'registro_ingresos') ? 'active' : ''; ?>">
                            <svg class="shrink-0 w-5 h-5 text-gray-500 transition duration-75 dark:text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white"
                                aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor"
                                viewBox="0 0 18 18">
                                <path
                                    d="M6.143 0H1.857A1.857 1.857 0 0 0 0 1.857v4.286C0 7.169.831 8 1.857 8h4.286A1.857 1.857 0 0 0 8 6.143V1.857A1.857 1.857 0 0 0 6.143 0Zm10 0h-4.286A1.857 1.857 0 0 0 10 1.857v4.286C10 7.169 10.831 8 11.857 8h4.286A1.857 1.857 0 0 0 18 6.143V1.857A1.857 1.857 0 0 0 16.143 0Zm-10 10H1.857A1.857 1.857 0 0 0 0 11.857v4.286C0 17.169.831 18 1.857 18h4.286A1.857 1.857 0 0 0 8 16.143v-4.286A1.857 1.857 0 0 0 6.143 10Zm10 0h-4.286A1.857 1.857 0 0 0 10 11.857v4.286c0 1.026.831 1.857 1.857 1.857h4.286A1.857 1.857 0 0 0 18 16.143v-4.286A1.857 1.857 0 0 0 16.143 10Z" />
                            </svg>
                            <span class="flex-1 ms-3 whitespace-nowrap">Registrar Ingreso</span>
                            <span
                                class="inline-flex items-center justify-center px-2 ms-3 text-sm font-medium text-gray-800 bg-gray-100 rounded-full dark:bg-gray-700 dark:text-gray-300"></span>
                        </a>
                    </li>
                    <li>
                        <a href="registro_gastos.php"
                            class="flex items-center sidebar-item p-2 pl-11 text-gray-900 rounded-lg dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 group <?php echo ($pagina_actual === 'registro_gastos') ? 'active' : ''; ?>">
                            <svg class="shrink-0 w-5 h-5 text-gray-500 transition duration-75 dark:text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white"
                                aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor"
                                viewBox="0 0 20 20">
                                <path
                                    d="m17.418 3.623-.018-.008a6.713 6.713 0 0 0-2.4-.569V2h1a1 1 0 1 0 0-2h-2a1 1 0 0 0-1 1v2H9.89A6.977 6.977 0 0 1 12 8v5h-2V8A5 5 0 1 0 0 8v6a1 1 0 0 0 1 1h8v4a1 1 0 0 0 1 1h2a1 1 0 0 0 1-1v-4h6a1 1 0 0 0 1-1V8a5 5 0 0 0-2.582-4.377ZM6 12H4a1 1 0 0 1 0-2h2a1 1 0 0 1 0 2Z" />
                            </svg>
                            <span class="flex-1 ms-3 whitespace-nowrap">Registrar Gasto</span>
                            <span
                                class="inline-flex items-center justify-center px-2 ms-3 text-sm font-medium text-gray-800 bg-gray-100 rounded-full dark:bg-gray-700 dark:text-gray-300"></span>
                        </a>
                    </li>
                </ul>
            </div>

            <!-- Divider -->
            <div class="border-t border-gray-100 dark:border-gray-700 my-6 mx-2"></div>

            <!-- Gestión -->
            <div class="mb-2">
                <!-- Reducimos el margen inferior -->
                <button type="button"
                    class="flex items-center w-full p-2 text-xs font-semibold text-gray-400 uppercase tracking-wider transition duration-75 rounded-lg group hover:bg-gray-100"
                    data-accordion-target="#accordion-gestion" aria-expanded="true" aria-controls="accordion-gestion">
                    <span>Gestión</span>
                    <svg data-accordion-icon class="w-3 h-3 ml-auto rotate-180 shrink-0" aria-hidden="true"
                        xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 10 6">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 5 5 1 1 5" />
                    </svg>
                </button>
                <ul id="accordion-gestion" class="py-2 space-y-2 font-medium hidden"
                    aria-labelledby="accordion-gestion-heading">
                    <li>
                        <a href="metas.php"
                            class="flex items-center sidebar-item p-2 text-gray-900 rounded-lg dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 group <?php echo ($pagina_actual === 'metas') ? 'active' : ''; ?>">
                            <svg class="shrink-0 w-5 h-5 text-gray-500 transition duration-75 
               dark:text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white"
                                xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                                <path
                                    d="M5 11C5 7.7 7.7 5 11 5h2c3.3 0 6 2.7 6 6v1c0 3.3-2.7 6-6 6H9c-2.2 0-4-1.8-4-4v-2z" />
                                <path d="M8 6l-1-2" />
                                <path d="M18 12c1.5 0 2 .5 2 1.5s-.5 1.5-2 1.5" />
                                <line x1="9" y1="18" x2="9" y2="20" />
                                <line x1="15" y1="18" x2="15" y2="20" />
                                <circle cx="13.5" cy="11" r="0.5" fill="currentColor" />
                            </svg>
                            <span class="flex-1 ms-3 whitespace-nowrap">Metas</span>
                        </a>
                    </li>
                    <li>
                        <a href="gestionar_categorias.php"
                            class="flex items-center sidebar-item p-2 text-gray-900 rounded-lg dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 group <?php echo ($pagina_actual === 'gestionar_categorias') ? 'active' : ''; ?>">
                            <svg class="shrink-0 w-5 h-5 text-gray-500 transition duration-75 
                dark:text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white" aria-hidden="true"
                                xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="2" y="5" width="20" height="14" rx="2" ry="2"></rect>
                                <line x1="2" y1="9" x2="22" y2="9"></line>
                                <path d="M12 13c-1.5 0-2.5-.5-2.5-1.5S10.5 10 12 10s2.5-.5 2.5-1.5S13.5 7 12 7"></path>
                                <line x1="12" y1="6" x2="12" y2="18"></line>
                            </svg>
                            <span class="flex-1 ms-3 whitespace-nowrap">Gestionar Categorias</span>
                        </a>
                    </li>
                    <li>
                        <a href="deudas.php"
                            class="flex items-center sidebar-item p-2 text-gray-900 rounded-lg dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 group <?php echo ($pagina_actual === 'deudas') ? 'active' : ''; ?>">
                            <svg class="shrink-0 w-5 h-5 text-gray-500 transition duration-75 
                dark:text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white" aria-hidden="true"
                                xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="2" y="5" width="20" height="14" rx="2" ry="2"></rect>
                                <line x1="2" y1="9" x2="22" y2="9"></line>
                                <path d="M12 13c-1.5 0-2.5-.5-2.5-1.5S10.5 10 12 10s2.5-.5 2.5-1.5S13.5 7 12 7"></path>
                                <line x1="12" y1="6" x2="12" y2="18"></line>
                            </svg>
                            <span class="flex-1 ms-3 whitespace-nowrap">Deudas</span>
                        </a>
                    </li>
                    <li>
                        <a href="presupuesto.php"
                            class="flex items-center sidebar-item p-2 text-gray-900 rounded-lg dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 group <?php echo ($pagina_actual === 'presupuesto') ? 'active' : ''; ?>">
                            <svg class="shrink-0 w-5 h-5 text-gray-500 transition duration-75 dark:text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white"
                                aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 18 16">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M1 8h11m0 0L8 4m4 4-4 4m4-11h3a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2h-3" />
                            </svg>
                            <span class="flex-1 ms-3 whitespace-nowrap">Presupuesto</span>
                        </a>
                    </li>
                </ul>
            </div>
            <!-- Divider -->
            <div class="border-t border-gray-100 dark:border-gray-700 my-6 mx-2"></div>

            <!-- Análisis -->
            <div class="mb-2">
                <!-- Reducimos el margen inferior -->
                <button type="button"
                    class="flex items-center w-full p-2 text-xs font-semibold text-gray-400 uppercase tracking-wider transition duration-75 rounded-lg group hover:bg-gray-100"
                    data-accordion-target="#accordion-analisis" aria-expanded="true" aria-controls="accordion-analisis">
                    <span>Reportes</span>
                    <svg data-accordion-icon class="w-3 h-3 ml-auto rotate-180 shrink-0" aria-hidden="true"
                        xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 10 6">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 5 5 1 1 5" />
                    </svg>
                </button>
                <ul id="accordion-analisis" class="py-2 space-y-2 font-medium hidden"
                    aria-labelledby="accordion-analisis-heading">
                    <li>
                        <a href="reportes.php"
                            class="flex items-center sidebar-item p-2 text-gray-900 rounded-lg dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 group <?php echo ($pagina_actual === 'reportes') ? 'active' : ''; ?>">
                            <svg class="shrink-0 w-5 h-5 text-gray-500 transition duration-75 dark:text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white"
                                aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor"
                                viewBox="0 0 20 18">
                                <path
                                    d="M14 2a3.963 3.963 0 0 0-1.4.267 6.439 6.439 0 0 1-1.331 6.638A4 4 0 1 0 14 2Zm1 9h-1.264A6.957 6.957 0 0 1 15 15v2a2.97 2.97 0 0 1-.184 1H19a1 1 0 0 0 1-1v-1a5.006 5.006 0 0 0-5-5ZM6.5 9a4.5 4.5 0 1 0 0-9 4.5 4.5 0 0 0 0 9ZM8 10H5a5.006 5.006 0 0 0-5 5v2a1 1 0 0 0 1 1h11a1 1 0 0 0 1-1v-2a5.006 5.006 0 0 0-5-5Z" />
                            </svg>
                            <span class="flex-1 ms-3 whitespace-nowrap">Reportes</span>
                        </a>
                    </li>
                    <li>
                        <a href="Historial.php"
                            class="flex items-center sidebar-item p-2 text-gray-900 rounded-lg dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 group <?php echo ($pagina_actual === 'Historial') ? 'active' : ''; ?>">
                            <svg class="shrink-0 w-5 h-5 text-gray-500 transition duration-75 dark:text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white"
                                aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor"
                                viewBox="0 0 18 20">
                                <path fill-rule="evenodd"
                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z"
                                    clip-rule="evenodd" />
                            </svg>
                            <span class="flex-1 ms-3 whitespace-nowrap">Historial</span>
                        </a>
                    </li>
                </ul>
            </div>
            <!-- Divider -->
            <div class="border-t border-gray-100 dark:border-gray-700 my-6 mx-2"></div>

            <!-- Sistema -->
            <div class="mb-2">
                <!-- Reducimos el margen inferior -->
                <button type="button"
                    class="flex items-center w-full p-2 text-xs font-semibold text-gray-400 uppercase tracking-wider transition duration-75 rounded-lg group hover:bg-gray-100"
                    data-accordion-target="#accordion-configuracion" aria-expanded="true"
                    aria-controls="accordion-configuracion">
                    <span>Sistema</span>
                    <svg data-accordion-icon class="w-3 h-3 ml-auto rotate-180 shrink-0" aria-hidden="true"
                        xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 10 6">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 5 5 1 1 5" />
                    </svg>
                </button>
                <ul id="accordion-configuracion" class="py-2 space-y-2 font-medium hidden"
                    aria-labelledby="accordion-configuracion-heading">
                    <li class="disabled:opacity-50 disabled:cursor-not-allowed">
                        <a href="configuracion.php"
                            class="flex items-center sidebar-item p-2 text-gray-900 rounded-lg dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 group <?php echo ($pagina_actual === 'configuracion') ? 'active' : ''; ?>">
                            <svg class="shrink-0 w-5 h-5 text-gray-500 transition duration-75 dark:text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white"
                                aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor"
                                viewBox="0 0 20 20">
                                <path d="M5 5V.13a2.96 2.96 0 0 0-1.293.749L.879 3.707A2.96 2.96 0 0 0 .13 5H5Z" />
                                <path
                                    d="M6.737 11.061a2.961 2.961 0 0 1 .81-1.515l6.117-6.116A4.839 4.839 0 0 1 16 2.141V2a1.97 1.97 0 0 0-1.933-2H7v5a2 2 0 0 1-2 2H0v11a1 1 0 0 0 1.933 2h12.134A1.97 1.97 0 0 0 16 18v-3.093l-1.546 1.546c-.413.413-.94.695-1.513.81l-3.4.679a2.947 2.947 0 0 1-1.85-.227 2.96 2.96 0 0 1-1.635-3.257l.681-3.397Z" />
                                <path
                                    d="M8.961 16a.93.93 0 0 0 .189-.019l3.4-.679a.961.961 0 0 0 .49-.263l6.118-6.117a2.884 2.884 0 0 0-4.079-4.078l-6.117 6.117a.96.96 0 0 0-.263.491l-.679 3.4A.961.961 0 0 0 8.961 16Zm7.477-9.8a.958.958 0 0 1 .68-.281.961.961 0 0 1 .682 1.644l-.315.315-1.36-1.36.313-.318Zm-5.911 5.911 4.236-4.236 1.359 1.359-4.236 4.237-1.7.339.341-1.699Z" />
                            </svg>
                            <span class="flex-1 ms-3 whitespace-nowrap">Configuración</span>
                        </a>
                    </li>
                </ul>
            </div>

        </div>

        <!-- <div
            class="mt-8 mx-2 p-4 rounded-xl bg-gradient-to-br from-blue-50 to-indigo-50 dark:from-gray-700 dark:to-gray-800 border border-blue-100 dark:border-gray-600 shadow-sm hover:shadow-md transition-shadow duration-300">
            <div class="flex items-start gap-3">
                <div class="flex-shrink-0">
                    <div class="">
                        <img src="../assets/icons/IDEA.png" alt="Idea" class="w-10 h-10 object-contain">
                    </div>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 mb-1">
                        <h4 class="text-sm font-bold text-gray-800 dark:text-white">Consejo Financiero</h4>
                        <span
                            class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200">
                            Tip
                        </span>
                    </div>
                    <p class="text-xs text-gray-600 dark:text-gray-300 leading-relaxed">
                        Revisa tus gastos cada semana para mantener un mejor control de tus finanzas.</p>
                </div>
            </div>
            <div class="mt-3 pt-3 border-t border-blue-100 dark:border-gray-600">
                <div class="flex items-center justify-between text-xs text-gray-500 dark:text-gray-400">
                    <span class="flex items-center gap-1">
                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z"
                                clip-rule="evenodd" />
                        </svg>
                        Actualizado hoy
                    </span>
                </div>
            </div>
        </div> -->
    </div>
</aside>