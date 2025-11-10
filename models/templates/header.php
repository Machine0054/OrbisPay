<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>OrbisPay</title>

    <!-- Preload de Tailwind -->
    <link href="../assets/css/output.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/@yaireo/tagify/dist/tagify.css" rel="stylesheet" type="text/css" />
    <link href="https://cdn.jsdelivr.net/npm/flowbite@3.1.2/dist/flowbite.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/litepicker@2.0.12/dist/css/litepicker.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <!-- <link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.css" rel="stylesheet"> -->


    <!-- Estilos personalizados -->

    <style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap');

    * {
        font-family: 'Inter', sans-serif;
    }

    .sidebar-item {
        position: relative;
        transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .sidebar-item::before {
        content: '';
        position: absolute;
        left: 0;
        top: 50%;
        transform: translateY(-50%) scaleY(0);
        width: 3px;
        height: 0%;
        background: linear-gradient(180deg, #6366f1 0%, #8b5cf6 100%);
        border-radius: 0 4px 4px 0;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .sidebar-item:hover::before {
        height: 70%;
        transform: translateY(-50%) scaleY(1);
    }

    .sidebar-item:hover {
        background: rgba(99, 102, 241, 0.04);
        transform: translateX(2px);
    }

    .sidebar-item.active {
        background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
        color: white;
        box-shadow: 0 4px 12px rgba(99, 102, 241, 0.25);
    }

    .sidebar-item.active::before {
        display: none;
    }

    .sidebar-item.active .icon-container {
        background: rgba(255, 255, 255, 0.2);
        backdrop-filter: blur(10px);
    }

    .sidebar-item.active svg {
        color: white;
    }

    .sidebar-item.active .badge {
        background: white;
        color: #6366f1;
    }

    .custom-select {
        -webkit-appearance: none;
        -moz-appearance: none;
        appearance: none;
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
        background-position: right 0.5rem center;
        background-repeat: no-repeat;
        background-size: 1.5em 1.5em;
        padding-right: 2.5rem;

    }
    </style>
</head>

<body class="bg-gray-100 min-h-screen flex flex-col">

    <nav class="fixed top-0 z-50 w-full bg-white border-b border-gray-200 dark:bg-gray-800 dark:border-gray-700">

        <div class="px-3 py-3 lg:px-5 lg:pl-3">
            <div class="flex items-center justify-between">
                <div class="flex items-center justify-start rtl:justify-end">
                    <button data-drawer-target="logo-sidebar" data-drawer-toggle="logo-sidebar"
                        aria-controls="logo-sidebar" type="button"
                        class="inline-flex items-center p-2 text-sm text-gray-500 rounded-lg sm:hidden hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-gray-200">
                        <span class="sr-only">Open sidebar</span>
                        <svg class="w-6 h-6" aria-hidden="true" fill="currentColor" viewBox="0 0 20 20"
                            xmlns="http://www.w3.org/2000/svg">
                            <path clip-rule="evenodd" fill-rule="evenodd"
                                d="M2 4.75A.75.75 0 012.75 4h14.5a.75.75 0 010 1.5H2.75A.75.75 0 012 4.75zm0 10.5a.75.75 0 01.75-.75h7.5a.75.75 0 010 1.5h-7.5a.75.75 0 01-.75-.75zM2 10a.75.75 0 01.75-.75h14.5a.75.75 0 010 1.5H2.75A.75.75 0 012 10z">
                            </path>
                        </svg>
                    </button>
                    <!--                    <a href="dashboard2.php" class="flex ms-2 md:me-24">
                        <svg class="w-8 h-8 mr-2 text-white" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                            <path d="M4 20V6a2 2 0 0 1 2-2h7.5a4.5 4.5 0 1 1 0 9H8v7H4zM8 9h5.5a2.5 2.5 0 1 0 0-5H8v5z">
                            </path>
                        </svg>
                        <span
                            class="self-center text-xl font-semibold sm:text-2xl whitespace-nowrap dark:text-white">Plutus</span>
                    </a>-->
                    <a href="dashboard2.php" class="flex items-center ms-2 md:me-24">
                        <!-- Isotipo OP -->
                        <div
                            class="flex items-center justify-center w-10 h-10 rounded-xl bg-gradient-to-r from-indigo-500 to-emerald-500 shadow-md">
                            <!-- Isotipo OP adaptado al sidebar -->
                            <svg class="w-10 h-10 rounded-xl shadow-md" viewBox="0 0 32 32" role="img"
                                aria-hidden="true">
                                <defs>
                                    <linearGradient id="opGradSidebar" x1="0" y1="0" x2="1" y2="1">
                                        <stop offset="0" stop-color="#06B6D4" /> <!-- cyan-500 -->
                                        <stop offset="1" stop-color="#10B981" /> <!-- emerald-500 -->
                                    </linearGradient>
                                </defs>
                                <!-- Fondo -->
                                <rect x="0" y="0" width="32" height="32" rx="8" fill="url(#opGradSidebar)" />
                                <!-- Monograma O + P en negativo -->
                                <g fill="#fff">
                                    <!-- O como anillo -->
                                    <path fill-rule="evenodd"
                                        d="M16 6a10 10 0 1 1 0 20 10 10 0 0 1 0-20zm0 4a6 6 0 1 0 0 12 6 6 0 0 0 0-12z" />
                                    <!-- P (asta) -->
                                    <rect x="14.5" y="10" width="3" height="12" rx="1.5" />
                                    <!-- P (panza) -->
                                    <path d="M17 10.5h2.8a4.2 4.2 0 0 1 0 8.4H17v-3h2.6a1.2 1.2 0 0 0 0-2.4H17z" />
                                </g>
                            </svg>

                        </div>

                        <!-- Nombre -->
                        <span class="ml-3 text-xl font-bold tracking-tight text-emerald-400">
                            Orbis<span class="text-emerald-400">Pay</span>
                        </span>
                    </a>
                </div>
                <div class="flex items-center">
                    <!-- ========= INICIO: NUEVO BOTÓN DE NOTIFICACIONES ========= -->
                    <div class="relative mr-4">
                        <button id="notification-bell-btn" type="button"
                            class="p-2 text-gray-500 rounded-lg hover:text-gray-900 hover:bg-gray-100">
                            <span class="sr-only">Ver notificaciones</span>
                            <!-- Icono de Campana -->
                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20"
                                xmlns="http://www.w3.org/2000/svg">
                                <path
                                    d="M10 2a6 6 0 00-6 6v3.586l-.707.707A1 1 0 004 14h12a1 1 0 00.707-1.707L16 11.586V8a6 6 0 00-6-6zM10 18a3 3 0 01-3-3h6a3 3 0 01-3 3z">
                                </path>
                            </svg>
                            <!-- Punto rojo para notificaciones no leídas (inicialmente oculto ) -->
                            <div id="notification-unread-indicator"
                                class="absolute top-1 right-1 w-3 h-3 bg-red-500 rounded-full border-2 border-white">
                            </div>
                        </button>
                    </div>
                    <!-- ========= FIN: NUEVO BOTÓN DE NOTIFICACIONES ========= -->

                    <div class="flex items-center ms-3">
                        <div>
                            <button type="button" id="menu_desplegable"
                                class="flex text-sm bg-gray-800 rounded-full focus:ring-4 focus:ring-gray-300 dark:focus:ring-gray-600"
                                aria-expanded="false" data-dropdown-toggle="dropdown-user">
                                <span class="sr-only">ABRIR MENU DE USUARIO</span>
                                <img class="w-8 h-8 rounded-full" src="../assets/img/avatars/1.png" alt="user photo">
                            </button>
                        </div>
                        <div class="z-50 hidden my-4 text-base list-none bg-white divide-y divide-gray-100 rounded-sm shadow-sm dark:bg-gray-700 dark:divide-gray-600"
                            id="dropdown-user">
                            <div class="px-4 py-3" role="none">
                                <p class="text-sm text-gray-900 dark:text-white" role="none">
                                    <?= strtoupper(htmlspecialchars($_SESSION['nombre'] . ' ' . $_SESSION['apellido'])); ?>
                                </p>
                                <p class="text-sm font-medium text-gray-900 truncate dark:text-gray-300" role="none">
                                    <?=  ($_SESSION['correo']); ?>
                                </p>
                            </div>
                            <ul class="py-1" role="none">
                                <li>
                                    <a href="dashboard2.php"
                                        class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-600 dark:hover:text-white"
                                        role="menuitem">Dashboard</a>
                                </li>
                                <li>
                                    <a href="configuracion.php"
                                        class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-600 dark:hover:text-white"
                                        role="menuitem">Configuración</a>
                                </li>
                                <li>
                                    <a href="../models/cerrar_sesiones.php"
                                        class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-600 dark:hover:text-white">
                                        Cerrar Sesión
                                        <i class="icon-base ri ri-logout-box-r-line ms-2 icon-16px"></i>
                                    </a>

                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </nav>