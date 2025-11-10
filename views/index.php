<?php
    session_start();
    //require "../models/autenticacion.php"
	//echo var_dump($_SESSION);
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Iniciar Sesión | OrbisPay</title>
    <!-- Preload de Tailwind -->
    <link href="../assets/css/output.css" rel="stylesheet">
    <!-- Preload de jQuery -->
    <link href="https://code.jquery.com/jquery-3.6.0.min.js" as="script">
    <!-- Preload de SweetAlert2 -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11" as="script">
    <!-- Preload de imagen de fondo -->
    <link href="https://images.unsplash.com/photo-1554224155-8d04cb21cd6c?q=80&w=2070&auto=format&fit=crop" as="image">
</head>

<body class="bg-slate-50">
    <section>
        <div class="flex flex-col items-center justify-center px-6 py-12 mx-auto min-h-screen">
            <!-- Marca OrbisPay (OP monograma) -->
            <a href="index.php" class="flex items-center space-x-3 mb-8" aria-label="OrbisPay">
                <!-- Isotipo OP -->
                <svg class="w-10 h-10 rounded-xl shadow-md" viewBox="0 0 32 32" role="img" aria-hidden="true">
                    <defs>
                        <linearGradient id="opGrad" x1="0" y1="0" x2="1" y2="1">
                            <stop offset="0" stop-color="#4F46E5" /> <!-- indigo-600 -->
                            <stop offset="1" stop-color="#7C3AED" /> <!-- purple-600 -->
                        </linearGradient>
                    </defs>
                    <!-- Fondo -->
                    <rect x="0" y="0" width="32" height="32" rx="8" fill="url(#opGrad)" />
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

                <!-- Logotipo -->
                <span class="text-2xl font-bold tracking-tight text-slate-900 dark">
                    Orbis<span class="text-indigo-600">Pay</span>
                </span>
            </a>

            <!-- Card -->
            <div class="w-full rounded-2xl shadow-lg border sm:max-w-4xl bg-white border-slate-200">
                <div class="flex flex-col lg:flex-row">
                    <!-- Formulario -->
                    <div class="w-full p-6 sm:p-10 lg:w-1/2">
                        <h1 class="text-2xl font-bold text-slate-900">Bienvenido de vuelta</h1>
                        <p class="mt-1 text-sm text-slate-600">Ingresa a tu cuenta para gestionar tus finanzas.</p>
                        <!-- INICIO: Mensaje de Mantenimiento -->
                        <div class="mt-6 p-4 rounded-lg bg-amber-50 border border-amber-200">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <!-- Icono de advertencia -->
                                    <svg class="w-5 h-5 text-amber-500" xmlns="http://www.w3.org/2000/svg"
                                        viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                        <path fill-rule="evenodd"
                                            d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.17 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625L8.485 2.495zM10 6a.75.75 0 01.75.75v3.5a.75.75 0 01-1.5 0v-3.5A.75.75 0 0110 6zm0 8a1 1 0 100-2 1 1 0 000 2z"
                                            clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-sm font-semibold text-amber-800">Sitio en Mantenimiento</h3>
                                    <div class="mt-2 text-sm text-amber-700">
                                        <p>Estamos realizando mejoras en la plataforma. Algunas funciones podrían no
                                            estar disponibles o no operar correctamente. Agradecemos tu paciencia.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- FIN: Mensaje de Mantenimiento -->

                        <form class="mt-8 space-y-5" id="loginForm" method="POST" action="#">
                            <div>
                                <label for="usuario" class="block mb-2 text-sm font-medium text-slate-700">Tu
                                    usuario</label>
                                <input id="usuario" name="usuario" type="text" required
                                    class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-slate-900 placeholder:text-slate-400 focus:outline-none focus:ring-4 focus:ring-primary-200 focus:border-primary-500"
                                    placeholder="ingresa tu usuario">
                            </div>

                            <div>
                                <div class="flex items-center justify-between mb-2">
                                    <label for="password"
                                        class="block text-sm font-medium text-slate-700">Contraseña</label>
                                    <a href="#" id="forgotPasswordLink"
                                        class="text-sm font-medium text-primary-600 hover:text-primary-700">¿Olvidaste
                                        tu contraseña?</a>
                                </div>
                                <input id="password" name="password" type="password" required
                                    class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-slate-900 placeholder:text-slate-400 focus:outline-none focus:ring-4 focus:ring-primary-200 focus:border-primary-500"
                                    placeholder="••••••••">
                            </div>

                            <div class="flex items-center justify-between">
                                <label for="recordar" class="inline-flex items-center gap-2 text-sm text-slate-600">
                                    <input id="recordar" type="checkbox"
                                        class="h-4 w-4 rounded border-slate-300 text-primary-600 focus:ring-primary-500">
                                    Recordarme
                                </label>
                            </div>
                            <button type="submit"
                                class="w-full rounded-lg bg-primary-600 hover:bg-primary-700 text-white font-semibold px-5 py-2.5 shadow-sm focus:outline-none focus:ring-4 focus:ring-primary-200"
                                id="loginBtn">
                                Iniciar Sesión
                            </button>

                            <!-- divisor -->
                            <div class="relative flex items-center justify-center">
                                <div class="absolute inset-0 flex items-center">
                                    <div class="w-full border-t border-slate-200"></div>
                                </div>
                                <div class="relative px-2 text-xs uppercase tracking-wide bg-white text-slate-500">o
                                    continúa con</div>
                            </div>

                            <!-- Redes con logos a color -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">

                                <!-- Google -->
                                <a href="../controllers/google_login.php" class="inline-flex items-center justify-center w-full gap-2 rounded-lg border
                                    border-slate-300 bg-white px-4 py-2.5 text-sm font-medium text-slate-700
                                    hover:bg-slate-50">
                                    <!-- G multicolor -->
                                    <svg class="w-5 h-5" viewBox="0 0 48 48" aria-hidden="true">
                                        <path fill="#EA4335"
                                            d="M24 9.5c3.7 0 6.3 1.6 7.8 2.9l5.3-5.3C33.9 4 29.5 2 24 2 14.8 2 6.9 7.4 3.2 15l6.9 5.3C11.7 14.1 17.3 9.5 24 9.5z" />
                                        <path fill="#4285F4"
                                            d="M46.5 24.5c0-1.6-.1-2.7-.4-3.9H24v7.4h12.7c-.3 2.1-1.7 5.1-4.9 7.2l7.5 5.8c4.4-4.1 7.2-10.1 7.2-16.5z" />
                                        <path fill="#FBBC05"
                                            d="M10.1 28.3c-.6-1.8-1-3.7-1-5.8s.4-4 1-5.8L3.2 11.4C1.5 14.9.6 18.4.6 22.5s.9 7.6 2.6 11.1l6.9-5.3z" />
                                        <path fill="#34A853"
                                            d="M24 46c6.5 0 11.9-2.1 15.8-5.7l-7.5-5.8c-2 1.4-4.7 2.3-8.3 2.3-6.7 0-12.3-4.6-14.3-10.8L3.2 33C6.9 40.6 14.8 46 24 46z" />
                                    </svg>
                                    Google
                                </a>

                                <!-- X (Twitter) -->
                                <!-- <a href="#"
                                    class="inline-flex items-center justify-center w-full gap-2 rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50">
                                   
                                    <svg class="w-5 h-5" viewBox="0 0 24 24" aria-hidden="true">
                                        <path fill="#121212"
                                            d="M18.244 2H21l-6.5 7.43L22.5 22h-6.9l-4.5-6.2L5.7 22H3l7.1-8.1L1.8 2h7l4 5.6L18.244 2zM16.9 20h1.8L7.2 4H5.4L16.9 20z" />
                                    </svg>
                                    X
                                </a> -->

                                <!-- Facebook -->
                                <a href="#"
                                    class="inline-flex items-center justify-center w-full gap-2 rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50">
                                    <svg class="w-5 h-5" viewBox="0 0 24 24" aria-hidden="true">
                                        <rect width="24" height="24" rx="4" fill="#1877F2"></rect>
                                        <path fill="#fff"
                                            d="M13.5 21v-7h2.3l.3-2.7h-2.6V9.1c0-.8.2-1.3 1.3-1.3h1.4V5.3C15.7 5.2 14.8 5 13.7 5c-2.1 0-3.6 1.3-3.6 3.7v2H8v2.7h2.1V21h3.4z" />
                                    </svg>
                                    Facebook
                                </a>
                            </div>

                            <p class="text-sm text-slate-600">
                                ¿No tienes una cuenta?
                                <a href="registro_usuarios.php"
                                    class="font-medium text-primary-600 hover:text-primary-700">Crear cuenta</a>
                            </p>
                        </form>

                        <form id="forgotPasswordForm" class="hidden space-y-5" method="POST" action="#">
                            <h3 class="text-xl font-semibold text-slate-800">Restablecer Contraseña</h3>
                            <p class="text-sm text-slate-600">Ingresa el correo electrónico asociado a tu cuenta y te
                                enviaremos un enlace para restablecer tu contraseña.</p>

                            <div>
                                <label for="resetEmail" class="block mb-2 text-sm font-medium text-slate-700">Tu correo
                                    electrónico</label>
                                <input id="resetEmail" name="email" type="email" required
                                    class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-slate-900 placeholder:text-slate-400 focus:outline-none focus:ring-4 focus:ring-primary-200 focus:border-primary-500"
                                    placeholder="nombre@ejemplo.com">
                            </div>

                            <button type="submit"
                                class="w-full rounded-lg bg-primary-600 hover:bg-primary-700 text-white font-semibold px-5 py-2.5 shadow-sm focus:outline-none focus:ring-4 focus:ring-primary-200">
                                Enviar Enlace
                            </button>

                            <div class="text-center">
                                <a href="#" id="backToLoginLink"
                                    class="text-sm font-medium text-primary-600 hover:text-primary-700">&larr; Volver a
                                    Iniciar Sesión</a>
                            </div>
                        </form>

                        <form id="resetPasswordForm" class="hidden space-y-5" method="POST" action="#">
                            <h3 class="text-xl font-semibold text-slate-800">Crea tu Nueva Contraseña</h3>
                            <p class="text-sm text-slate-600">Tu nueva contraseña debe ser segura y fácil de recordar.
                            </p>

                            <input type="hidden" id="resetToken" name="token" value="">

                            <div>
                                <label for="newPassword" class="block mb-2 text-sm font-medium text-slate-700">Nueva
                                    Contraseña</label>
                                <input id="newPassword" name="newPassword" type="password" required
                                    class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-slate-900 placeholder:text-slate-400 focus:outline-none focus:ring-4 focus:ring-primary-200 focus:border-primary-500"
                                    placeholder="••••••••">
                            </div>

                            <div>
                                <label for="confirmPassword"
                                    class="block mb-2 text-sm font-medium text-slate-700">Confirmar Nueva
                                    Contraseña</label>
                                <input id="confirmPassword" name="confirmPassword" type="password" required
                                    class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-slate-900 placeholder:text-slate-400 focus:outline-none focus:ring-4 focus:ring-primary-200 focus:border-primary-500"
                                    placeholder="••••••••">
                            </div>

                            <button type="submit"
                                class="w-full rounded-lg bg-primary-600 hover:bg-primary-700 text-white font-semibold px-5 py-2.5 shadow-sm focus:outline-none focus:ring-4 focus:ring-primary-200">
                                Guardar Nueva Contraseña
                            </button>
                        </form>
                    </div>

                    <!-- Imagen Derecha -->
                    <div class="hidden lg:block lg:w-1/2 relative">
                        <div class="absolute inset-0 bg-cover bg-center rounded-r-2xl"
                            style="background-image: url('https://images.unsplash.com/photo-1554224155-8d04cb21cd6c?q=80&w=2070&auto=format&fit=crop' );">
                        </div>
                        <div class="absolute inset-0 bg-gradient-to-t from-slate-900/50 to-transparent rounded-r-2xl">
                        </div>
                        <div class="relative h-full flex flex-col justify-end p-10">
                            <h2 class="text-3xl font-bold text-white">Control total sobre tus finanzas</h2>
                            <p class="mt-2 text-slate-200">La plataforma que necesitas para tomar decisiones
                                inteligentes y alcanzar tus metas financieras.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- ================================================== -->
    <!-- CONTENEDOR PARA LAS NOTIFICACIONES DINÁMICAS A LA DERECHA-->
    <!-- ================================================== -->
    <div id="notification-container" class="fixed top-5 right-5 z-50 space-y-3">
        <!-- Aqui van las notificaciones -->
    </div>

    <!-- ================================================== -->
    <!-- MODAL DE ALERTA PERSONALIZADO-->
    <!-- ================================================== -->

    <div id="alertModal"
        class="fixed inset-0 z-50 hidden flex items-center justify-center bg-black/50 transition-opacity duration-300 opacity-0">

        <!-- Contenedor del modal con animación -->
        <div id="alertModalBox"
            class="relative w-full max-w-md transform rounded-2xl bg-white p-6 text-center shadow-xl transition-all duration-300 scale-95">

            <!-- Contenedor del Icono (se llenará con JS) -->
            <div id="alertModalIconContainer" class="mx-auto flex h-16 w-16 items-center justify-center rounded-full">
                <!-- El icono SVG se insertará aquí -->
            </div>

            <!-- Título del Modal (se llenará con JS) -->
            <h3 id="alertModalTitle" class="mt-5 text-xl font-semibold leading-6 text-slate-900"></h3>

            <!-- Mensaje del Modal (se llenará con JS) -->
            <div class="mt-2">
                <p id="alertModalMessage" class="text-sm text-slate-600"></p>
            </div>

            <!-- Botón de Acción -->
            <div class="mt-6">
                <button id="alertModalConfirmBtn" type="button"
                    class="w-full justify-center rounded-lg px-4 py-3 font-medium text-white focus:outline-none">
                    <!-- El texto del botón se llenará con JS -->
                </button>
            </div>
        </div>
    </div>




    <!-- SDK de Google Identity Services -->
    <script src="https://accounts.google.com/gsi/client" async defer></script>
    <!-- <script src="https://cdn.tailwindcss.com"></script> -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- SweetAlert2 CDN -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    </script>
    <script>

    </script>
    <script src="../controllers/login.js"></script>
</body>

</html>