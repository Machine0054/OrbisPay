<?php 
    session_start();

    if (!isset($_SESSION['usuario'])) {
    echo "No existe usuario logueado";
    header("Location: index.php");
    exit;
}
?>



<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Finanzas Personales</title>
    <link rel="stylesheet" href="../assets/vendor/css/core.css" />
    <script src="../assets/vendor/js/bootstrap.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>

    <style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');

    * {
        font-family: 'Inter', sans-serif;
    }

    .glass-effect {
        background: rgba(255, 255, 255, 0.1);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.2);
    }

    .gradient-bg {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }

    .card-hover {
        transition: all 0.3s ease;
    }

    .card-hover:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
    }

    .sidebar-item {
        transition: all 0.3s ease;
        position: relative;
    }

    .sidebar-item:hover {
        background: rgba(255, 255, 255, 0.1);
        transform: translateX(5px);
    }

    .sidebar-item.active {
        background: rgba(255, 255, 255, 0.2);
        border-left: 4px solid #fbbf24;
    }

    .pulse-animation {
        animation: pulse 2s infinite;
    }

    @keyframes pulse {

        0%,
        100% {
            opacity: 1;
        }

        50% {
            opacity: 0.5;
        }
    }

    .floating-elements {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        pointer-events: none;
        z-index: -1;
    }

    .floating-circle {
        position: absolute;
        border-radius: 50%;
        background: linear-gradient(45deg, rgba(102, 126, 234, 0.1), rgba(118, 75, 162, 0.1));
        animation: float 6s ease-in-out infinite;
    }

    @keyframes float {

        0%,
        100% {
            transform: translateY(0px);
        }

        50% {
            transform: translateY(-20px);
        }
    }

    /* Responsive sidebar improvements */
    .sidebar-hidden {
        transform: translateX(-100%);
    }

    .sidebar-overlay {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.5);
        z-index: 40;
        opacity: 0;
        visibility: hidden;
        transition: all 0.3s ease;
    }

    .sidebar-overlay.active {
        opacity: 1;
        visibility: visible;
    }

    /* Mobile optimizations */
    @media (max-width: 640px) {
        .floating-circle {
            display: none;
        }

        .card-hover:hover {
            transform: none;
        }

        .sidebar-item:hover {
            transform: none;
        }
    }

    /* Tablet optimizations */
    @media (min-width: 641px) and (max-width: 1024px) {
        .floating-circle {
            opacity: 0.5;
        }
    }

    /* Custom scrollbar for sidebar */
    .sidebar-scroll::-webkit-scrollbar {
        width: 4px;
    }

    .sidebar-scroll::-webkit-scrollbar-track {
        background: rgba(255, 255, 255, 0.1);
    }

    .sidebar-scroll::-webkit-scrollbar-thumb {
        background: rgba(255, 255, 255, 0.3);
        border-radius: 2px;
    }

    /* Improved button styles for mobile */
    .action-button {
        min-height: 44px;
        /* iOS recommended touch target */
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
    }

    /* Better focus states for accessibility */
    .focus-ring:focus {
        outline: 2px solid #3b82f6;
        outline-offset: 2px;
    }

    /* Responsive text scaling */
    @media (max-width: 640px) {
        .responsive-text-xl {
            font-size: 1.25rem;
            line-height: 1.75rem;
        }

        .responsive-text-2xl {
            font-size: 1.5rem;
            line-height: 2rem;
        }

        .responsive-text-3xl {
            font-size: 1.875rem;
            line-height: 2.25rem;
        }
    }

    .dropdown-enter {
        opacity: 0;
        transform: translateY(-10px);
    }

    .dropdown-enter-active {
        opacity: 1;
        transform: translateY(0);
        transition: opacity 200ms ease, transform 200ms ease;
    }

    .dropdown-exit {
        opacity: 1;
    }

    .dropdown-exit-active {
        opacity: 0;
        transform: translateY(-10px);
        transition: opacity 150ms ease, transform 150ms ease;
    }

    .focus-ring:focus {
        outline: none;
        ring: 2px;
        ring-color: #3b82f6;
        ring-offset: 2px;
    }
    </style>
</head>

<body class="flex bg-gradient-to-br from-slate-100 to-slate-200 min-h-screen overflow-x-hidden">
    <!-- Floating Background Elements -->
    <div class="floating-elements">
        <div class="floating-circle w-20 h-20 sm:w-32 sm:h-32 top-20 left-10 sm:left-20" style="animation-delay: 0s;">
        </div>
        <div class="floating-circle w-16 h-16 sm:w-24 sm:h-24 top-40 right-16 sm:right-32" style="animation-delay: 2s;">
        </div>
        <div class="floating-circle w-12 h-12 sm:w-16 sm:h-16 bottom-32 left-1/4 sm:left-1/3"
            style="animation-delay: 4s;"></div>
    </div>

    <!-- Mobile Sidebar Overlay -->
    <div id="sidebar-overlay" class="sidebar-overlay lg:hidden" onclick="toggleSidebar()"></div>

    <!-- Sidebar -->
    <aside id="sidebar"
        class="fixed lg:relative w-64 sm:w-72 lg:w-64 gradient-bg text-white flex flex-col shadow-2xl z-50 h-full lg:h-auto sidebar-hidden lg:transform-none transition-transform duration-300 sidebar-scroll overflow-y-auto">
        <div class="px-4 sm:px-6 py-4 sm:py-6 text-xl sm:text-2xl font-bold border-b border-white/20 flex-shrink-0">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-2">
                    <div class="w-8 h-8 bg-amber-400 rounded-lg flex items-center justify-center">
                        <span class="text-purple-800 font-bold text-sm sm:text-base">₱</span>
                    </div>
                    <span class="text-base sm:text-xl">Finanzas</span>
                </div>
                <!-- Close button for mobile -->
                <button class="lg:hidden text-white hover:text-gray-300 focus-ring rounded p-1"
                    onclick="toggleSidebar()" aria-label="Cerrar menú">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                        </path>
                    </svg>
                </button>
            </div>
        </div>

        <nav class="flex-1 px-3 sm:px-4 py-4 sm:py-6 space-y-1 sm:space-y-2 overflow-y-auto">
            <a href="dashboard.php"
                class="sidebar-item active flex items-center space-x-3 rounded-lg px-3 sm:px-4 py-3 focus-ring text-sm sm:text-base"
                onclick="setActiveItem(this)">
                <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path
                        d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z" />
                </svg>
                <span>Dashboard</span>
            </a>

            <a href="registro_ingresos.php"
                class="sidebar-item flex items-center space-x-3 rounded-lg px-3 sm:px-4 py-3 focus-ring text-sm sm:text-base"
                onclick="setActiveItem(this)">
                <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd"
                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v2H7a1 1 0 100 2h2v2a1 1 0 102 0v-2h2a1 1 0 100-2h-2V7z"
                        clip-rule="evenodd" />
                </svg>
                <span>Registrar Ingreso</span>
            </a>

            <a href="registro_gastos.php"
                class="sidebar-item flex items-center space-x-3 rounded-lg px-3 sm:px-4 py-3 focus-ring text-sm sm:text-base"
                onclick="setActiveItem(this)">
                <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM7 9a1 1 0 000 2h6a1 1 0 100-2H7z"
                        clip-rule="evenodd" />
                </svg>
                <span>Registrar Gasto</span>
            </a>

            <a href="reportes.php"
                class="sidebar-item flex items-center space-x-3 rounded-lg px-3 sm:px-4 py-3 focus-ring text-sm sm:text-base"
                onclick="setActiveItem(this)">
                <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path
                        d="M2 11a1 1 0 011-1h2a1 1 0 011 1v5a1 1 0 01-1 1H3a1 1 0 01-1-1v-5zM8 7a1 1 0 011-1h2a1 1 0 011 1v9a1 1 0 01-1 1H9a1 1 0 01-1-1V7zM14 4a1 1 0 011-1h2a1 1 0 011 1v12a1 1 0 01-1 1h-2a1 1 0 01-1-1V4z" />
                </svg>
                <span>Reportes</span>
            </a>
            <a href="Historial.php" class="sidebar-item flex items-center space-x-3 rounded-lg px-4 py-3"
                onclick="setActiveItem(this)">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path
                        d="M2 11a1 1 0 011-1h2a1 1 0 011 1v5a1 1 0 01-1 1H3a1 1 0 01-1-1v-5zM8 7a1 1 0 011-1h2a1 1 0 011 1v9a1 1 0 01-1 1H9a1 1 0 01-1-1V7zM14 4a1 1 0 011-1h2a1 1 0 011 1v12a1 1 0 01-1 1h-2a1 1 0 01-1-1V4z" />
                </svg>
                <span>Historial</span>
            </a>
            <a href="presupuesto.php"
                class="sidebar-item flex items-center space-x-3 rounded-lg px-3 sm:px-4 py-3 focus-ring text-sm sm:text-base"
                onclick="setActiveItem(this)">
                <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd"
                        d="M4 4a2 2 0 00-2 2v4a2 2 0 002 2V6h10a2 2 0 00-2-2H4zm2 6a2 2 0 012-2h8a2 2 0 012 2v4a2 2 0 01-2 2H8a2 2 0 01-2-2v-4zm6 4a2 2 0 100-4 2 2 0 000 4z"
                        clip-rule="evenodd" />
                </svg>
                <span>Presupuesto</span>
            </a>

            <a href="configuracion.php"
                class="sidebar-item flex items-center space-x-3 rounded-lg px-3 sm:px-4 py-3 focus-ring text-sm sm:text-base"
                onclick="setActiveItem(this)">
                <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd"
                        d="M11.49 3.17c-.38-1.56-2.6-1.56-2.98 0a1.532 1.532 0 01-2.286.948c-1.372-.836-2.942.734-2.106 2.106.54.886.061 2.042-.947 2.287-1.561.379-1.561 2.6 0 2.978a1.532 1.532 0 01.947 2.287c-.836 1.372.734 2.942 2.106 2.106a1.532 1.532 0 012.287.947c.379 1.561 2.6 1.561 2.978 0a1.533 1.533 0 012.287-.947c1.372.836 2.942-.734 2.106-2.106a1.533 1.533 0 01.947-2.287c1.561-.379 1.561-2.6 0-2.978a1.532 1.532 0 01-.947-2.287c.836-1.372-.734-2.942-2.106-2.106a1.532 1.532 0 01-2.287-.947zM10 13a3 3 0 100-6 3 3 0 000 6z"
                        clip-rule="evenodd" />
                </svg>
                <span>Configuración</span>
            </a>
        </nav>
        <!-- 
        <div class="px-3 sm:px-4 py-3 sm:py-4 border-t border-white/20 flex-shrink-0">
            <a href="../models/cerrar_sesiones.php"
                class="sidebar-item flex items-center space-x-3 rounded-lg px-3 sm:px-4 py-3 text-red-200 hover:bg-red-500/20 focus-ring text-sm sm:text-base">
                <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd"
                        d="M3 3a1 1 0 00-1 1v12a1 1 0 102 0V4a1 1 0 00-1-1zm10.293 9.293a1 1 0 001.414 1.414l3-3a1 1 0 000-1.414l-3-3a1 1 0 10-1.414 1.414L14.586 9H7a1 1 0 100 2h7.586l-1.293 1.293z"
                        clip-rule="evenodd" />
                </svg>
                <span>Cerrar sesión</span>
            </a>
        </div> -->
    </aside>

    <!-- Main Content -->
    <div class="flex-1 flex flex-col min-h-screen w-full lg:w-auto">
        <!-- Header -->
        <nav class="layout-navbar container-xxl navbar-detached navbar navbar-expand-xl align-items-center bg-navbar-theme"
            id="layout-navbar">
            <div class="layout-menu-toggle navbar-nav align-items-xl-center me-4 me-xl-0   d-xl-none ">
                <a class="nav-item nav-link px-0 me-xl-6" href="javascript:void(0)">
                    <i class="icon-base ri ri-menu-line icon-md"></i>
                </a>
            </div>
            <div class="navbar-nav-right d-flex align-items-center justify-content-end" id="navbar-collapse">
                <ul class="navbar-nav flex-row align-items-center ms-md-auto">

                    <!-- User -->
                    <li class="nav-item navbar-dropdown dropdown-user dropdown">
                        <a class="nav-link dropdown-toggle hide-arrow p-0" href="javascript:void(0);"
                            data-bs-toggle="dropdown">
                            <div class="avatar avatar-online">
                                <img src="../assets/img/avatars/1.png" alt="alt" class="rounded-circle" />
                            </div>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end mt-3 py-2">
                            <li>
                                <a class="dropdown-item" href="pages-account-settings-account.html">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-shrink-0 me-2">
                                            <div class="avatar avatar-online">
                                                <img src="../assets/img/avatars/1.png" alt="alt"
                                                    class="w-px-40 h-auto rounded-circle" />
                                            </div>
                                        </div>
                                        <div class="flex-grow-1">
                                            <h6 class="mb-0 small">
                                                <?=  strtoupper(htmlspecialchars($_SESSION['nombre'])); ?>
                                            </h6>
                                            <small class="text-body-secondary">
                                                <?= strtoupper(htmlspecialchars($_SESSION['rol'])); ?>
                                            </small>
                                        </div>
                                    </div>
                                </a>
                            </li>
                            <li>
                                <div class="dropdown-divider"></div>
                            </li>
                            <li>
                                <div class="dropdown-divider"></div>
                            </li>
                            <li>
                                <div class="d-grid px-4 pt-2 pb-1">
                                    <a href="../models/cerrar_sesiones.php" class="btn btn-danger d-flex">
                                        <small class="align-middle">Cerrar Sesión</small>
                                        <i class="icon-base ri ri-logout-box-r-line ms-2 icon-16px"></i>
                                    </a>
                                </div>
                            </li>
                        </ul>
                    </li>
                    <!--/ User -->
                </ul>
            </div>
        </nav>
        <!-- Dashboard Content -->
        <main class="flex-1 p-3 sm:p-4 lg:p-6 space-y-4 sm:space-y-6 overflow-x-hidden">

            <!-- Summary Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-3 sm:gap-4 lg:gap-6">
                <div
                    class="bg-white/80 backdrop-blur-sm rounded-xl sm:rounded-2xl shadow-xl p-4 sm:p-6 card-hover border border-white/20">
                    <div class="flex items-center justify-between">
                        <div class="min-w-0 flex-1">
                            <h2 class="text-xs sm:text-sm font-medium text-gray-600 uppercase tracking-wide">Ingresos
                                del mes</h2>
                            <p
                                class="text-xl sm:text-2xl lg:text-3xl font-bold text-emerald-600 mt-2 responsive-text-2xl">
                                $1,500,000</p>
                            <p class="text-xs sm:text-sm text-emerald-500 mt-1">+12% vs mes anterior</p>
                        </div>
                        <div class="bg-emerald-100 p-2 sm:p-3 rounded-full flex-shrink-0">
                            <svg class="w-6 h-6 sm:w-8 sm:h-8 text-emerald-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-8.293l-3-3a1 1 0 00-1.414 0l-3 3a1 1 0 001.414 1.414L9 9.414V13a1 1 0 102 0V9.414l1.293 1.293a1 1 0 001.414-1.414z"
                                    clip-rule="evenodd" />
                            </svg>
                        </div>
                    </div>
                </div>

                <div
                    class="bg-white/80 backdrop-blur-sm rounded-xl sm:rounded-2xl shadow-xl p-4 sm:p-6 card-hover border border-white/20">
                    <div class="flex items-center justify-between">
                        <div class="min-w-0 flex-1">
                            <h2 class="text-xs sm:text-sm font-medium text-gray-600 uppercase tracking-wide">Gastos del
                                mes</h2>
                            <p class="text-xl sm:text-2xl lg:text-3xl font-bold text-red-600 mt-2 responsive-text-2xl">
                                $950,000</p>
                            <p class="text-xs sm:text-sm text-red-500 mt-1">-5% vs mes anterior</p>
                        </div>
                        <div class="bg-red-100 p-2 sm:p-3 rounded-full flex-shrink-0">
                            <svg class="w-6 h-6 sm:w-8 sm:h-8 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v3.586L7.707 9.293a1 1 0 00-1.414 1.414l3 3a1 1 0 001.414 0l3-3a1 1 0 00-1.414-1.414L11 10.586V7z"
                                    clip-rule="evenodd" />
                            </svg>
                        </div>
                    </div>
                </div>

                <div
                    class="bg-white/80 backdrop-blur-sm rounded-xl sm:rounded-2xl shadow-xl p-4 sm:p-6 card-hover border border-white/20 sm:col-span-2 xl:col-span-1">
                    <div class="flex items-center justify-between">
                        <div class="min-w-0 flex-1">
                            <h2 class="text-xs sm:text-sm font-medium text-gray-600 uppercase tracking-wide">Balance
                            </h2>
                            <p
                                class="text-xl sm:text-2xl lg:text-3xl font-bold text-indigo-600 mt-2 responsive-text-2xl">
                                $550,000</p>
                            <p class="text-xs sm:text-sm text-indigo-500 mt-1">36.7% de ahorro</p>
                        </div>
                        <div class="bg-indigo-100 p-2 sm:p-3 rounded-full flex-shrink-0">
                            <svg class="w-6 h-6 sm:w-8 sm:h-8 text-indigo-600" fill="currentColor" viewBox="0 0 20 20">
                                <path
                                    d="M8.433 7.418c.155-.103.346-.196.567-.267v1.698a2.305 2.305 0 01-.567-.267C8.07 8.34 8 8.114 8 8c0-.114.07-.34.433-.582zM11 12.849v-1.698c.22.071.412.164.567.267.364.243.433.468.433.582 0 .114-.07.34-.433.582a2.305 2.305 0 01-.567.267z" />
                                <path fill-rule="evenodd"
                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-13a1 1 0 10-2 0v.092a4.535 4.535 0 00-1.676.662C6.602 6.234 6 7.009 6 8c0 .99.602 1.765 1.324 2.246.48.32 1.054.545 1.676.662v1.941c-.391-.127-.68-.317-.843-.504a1 1 0 10-1.51 1.31c.562.649 1.413 1.076 2.353 1.253V15a1 1 0 102 0v-.092a4.535 4.535 0 001.676-.662C13.398 13.766 14 12.991 14 12c0-.99-.602-1.765-1.324-2.246A4.535 4.535 0 0011 9.092V7.151c.391.127.68.317.843.504a1 1 0 101.511-1.31c-.563-.649-1.413-1.076-2.354-1.253V5z"
                                    clip-rule="evenodd" />
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts and Analysis -->
            <div class="grid grid-cols-1 xl:grid-cols-2 gap-4 sm:gap-6">
                <!-- Monthly Trend Chart -->
                <div
                    class="bg-white/80 backdrop-blur-sm rounded-xl sm:rounded-2xl shadow-xl p-4 sm:p-6 border border-white/20">
                    <h3 class="text-base sm:text-lg font-semibold text-gray-800 mb-4 responsive-text-xl">Tendencia
                        Mensual</h3>
                    <div class="h-48 sm:h-64 lg:h-72">
                        <canvas id="monthlyChart" class="w-full h-full"></canvas>
                    </div>
                </div>

                <!-- Recent Transactions -->
                <div
                    class="bg-white/80 backdrop-blur-sm rounded-xl sm:rounded-2xl shadow-xl p-4 sm:p-6 border border-white/20">
                    <h3 class="text-base sm:text-lg font-semibold text-gray-800 mb-4 responsive-text-xl">Transacciones
                        Recientes</h3>
                    <div class="space-y-3 sm:space-y-4 max-h-64 sm:max-h-72 lg:max-h-80 overflow-y-auto">
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <div class="flex items-center space-x-3 min-w-0 flex-1">
                                <div
                                    class="w-8 h-8 sm:w-10 sm:h-10 bg-emerald-100 rounded-full flex items-center justify-center flex-shrink-0">
                                    <svg class="w-4 h-4 sm:w-5 sm:h-5 text-emerald-600" fill="currentColor"
                                        viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M4 4a2 2 0 00-2 2v4a2 2 0 002 2V6h10a2 2 0 00-2-2H4zm2 6a2 2 0 012-2h8a2 2 0 012 2v4a2 2 0 01-2 2H8a2 2 0 01-2-2v-4zm6 4a2 2 0 100-4 2 2 0 000 4z"
                                            clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="font-medium text-gray-800 text-sm sm:text-base truncate">Salario</p>
                                    <p class="text-xs sm:text-sm text-gray-600">Hoy</p>
                                </div>
                            </div>
                            <p class="font-semibold text-emerald-600 text-sm sm:text-base flex-shrink-0">+$1,200,000</p>
                        </div>

                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <div class="flex items-center space-x-3 min-w-0 flex-1">
                                <div
                                    class="w-8 h-8 sm:w-10 sm:h-10 bg-red-100 rounded-full flex items-center justify-center flex-shrink-0">
                                    <svg class="w-4 h-4 sm:w-5 sm:h-5 text-red-600" fill="currentColor"
                                        viewBox="0 0 20 20">
                                        <path
                                            d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z" />
                                    </svg>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="font-medium text-gray-800 text-sm sm:text-base truncate">Supermercado</p>
                                    <p class="text-xs sm:text-sm text-gray-600">Ayer</p>
                                </div>
                            </div>
                            <p class="font-semibold text-red-600 text-sm sm:text-base flex-shrink-0">-$180,000</p>
                        </div>

                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <div class="flex items-center space-x-3 min-w-0 flex-1">
                                <div
                                    class="w-8 h-8 sm:w-10 sm:h-10 bg-yellow-100 rounded-full flex items-center justify-center flex-shrink-0">
                                    <svg class="w-4 h-4 sm:w-5 sm:h-5 text-yellow-600" fill="currentColor"
                                        viewBox="0 0 20 20">
                                        <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="font-medium text-gray-800 text-sm sm:text-base truncate">Servicios</p>
                                    <p class="text-xs sm:text-sm text-gray-600">2 días</p>
                                </div>
                            </div>
                            <p class="font-semibold text-red-600 text-sm sm:text-base flex-shrink-0">-$320,000</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div
                class="bg-white/80 backdrop-blur-sm rounded-xl sm:rounded-2xl shadow-xl p-4 sm:p-6 border border-white/20">
                <h3 class="text-base sm:text-lg font-semibold text-gray-800 mb-4 responsive-text-xl">Acciones Rápidas
                </h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
                    <button
                        class="action-button bg-emerald-500 hover:bg-emerald-600 text-white px-4 sm:px-6 py-3 rounded-xl font-medium transition-all duration-300 transform hover:scale-105 shadow-lg text-sm sm:text-base focus-ring">
                        <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v2H7a1 1 0 100 2h2v2a1 1 0 102 0v-2h2a1 1 0 100-2h-2V7z"
                                clip-rule="evenodd" />
                        </svg>
                        <span>Agregar Ingreso</span>
                    </button>

                    <button
                        class="action-button bg-red-500 hover:bg-red-600 text-white px-4 sm:px-6 py-3 rounded-xl font-medium transition-all duration-300 transform hover:scale-105 shadow-lg text-sm sm:text-base focus-ring">
                        <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M10 18a8 8 0 100-16 8 8 0 000 16zM7 9a1 1 0 000 2h6a1 1 0 100-2H7z"
                                clip-rule="evenodd" />
                        </svg>
                        <span>Agregar Gasto</span>
                    </button>

                    <button
                        class="action-button bg-indigo-500 hover:bg-indigo-600 text-white px-4 sm:px-6 py-3 rounded-xl font-medium transition-all duration-300 transform hover:scale-105 shadow-lg text-sm sm:text-base focus-ring">
                        <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path
                                d="M2 11a1 1 0 011-1h2a1 1 0 011 1v5a1 1 0 01-1 1H3a1 1 0 01-1-1v-5zM8 7a1 1 0 011-1h2a1 1 0 011 1v9a1 1 0 01-1 1H9a1 1 0 01-1-1V7zM14 4a1 1 0 011-1h2a1 1 0 011 1v12a1 1 0 01-1 1h-2a1 1 0 01-1-1V4z" />
                        </svg>
                        <span>Ver Reportes</span>
                    </button>

                    <button
                        class="action-button bg-purple-500 hover:bg-purple-600 text-white px-4 sm:px-6 py-3 rounded-xl font-medium transition-all duration-300 transform hover:scale-105 shadow-lg text-sm sm:text-base focus-ring">
                        <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M4 4a2 2 0 00-2 2v4a2 2 0 002 2V6h10a2 2 0 00-2-2H4zm2 6a2 2 0 012-2h8a2 2 0 012 2v4a2 2 0 01-2 2H8a2 2 0 01-2-2v-4zm6 4a2 2 0 100-4 2 2 0 000 4z"
                                clip-rule="evenodd" />
                        </svg>
                        <span>Presupuesto</span>
                    </button>
                </div>
            </div>
        </main>
    </div>
    <script src="../assets/vendor/js/helpers.js"></script>
    <script src="../controllers/dashboard.js"></script>
</body>

</html>