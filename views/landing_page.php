<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description"
        content="OrbisPay - Transforma tu dinero de un problema a tu mayor aliado. Visualiza, controla y alcanza tus metas financieras.">
    <title>OrbisPay - Control Total de tus Finanzas Personales</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { 'sans': ['Inter', 'sans-serif'] },
                    colors: {
                        primary: { 50: '#f0fdf4', 100: '#dcfce7', 200: '#bbf7d0', 300: '#86efac', 400: '#4ade80', 500: '#22c55e', 600: '#16a34a', 700: '#15803d', 800: '#166534', 900: '#14532d' },
                    }
                }
            }
        }
    </script>
    <style>
        .gradient-bg {
            background: linear-gradient(135deg, #22c55e 0%, #14b8a6 50%, #0ea5e9 100%);
        }

        .gradient-text {
            background: linear-gradient(135deg, #22c55e, #14b8a6);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .glass {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        /* Animations */
        @keyframes float {

            0%,
            100% {
                transform: translateY(0) rotate(0deg);
            }

            50% {
                transform: translateY(-20px) rotate(5deg);
            }
        }

        @keyframes float-reverse {

            0%,
            100% {
                transform: translateY(0) rotate(0deg);
            }

            50% {
                transform: translateY(-15px) rotate(-5deg);
            }
        }

        @keyframes pulse-glow {

            0%,
            100% {
                box-shadow: 0 0 20px rgba(34, 197, 94, 0.4);
            }

            50% {
                box-shadow: 0 0 40px rgba(34, 197, 94, 0.7);
            }
        }

        @keyframes shimmer {
            0% {
                background-position: -200% 0;
            }

            100% {
                background-position: 200% 0;
            }
        }

        @keyframes bounce-slow {

            0%,
            100% {
                transform: translateY(0);
            }

            50% {
                transform: translateY(-10px);
            }
        }

        @keyframes fade-in-up {
            from {
                opacity: 0;
                transform: translateY(30px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes scale-in {
            from {
                opacity: 0;
                transform: scale(0.9);
            }

            to {
                opacity: 1;
                transform: scale(1);
            }
        }

        @keyframes money-float {
            0% {
                transform: translateY(100vh) rotate(0deg);
                opacity: 0;
            }

            10% {
                opacity: 1;
            }

            90% {
                opacity: 1;
            }

            100% {
                transform: translateY(-100px) rotate(360deg);
                opacity: 0;
            }
        }

        .float {
            animation: float 6s ease-in-out infinite;
        }

        .float-reverse {
            animation: float-reverse 5s ease-in-out infinite;
        }

        .pulse-glow {
            animation: pulse-glow 2s ease-in-out infinite;
        }

        .bounce-slow {
            animation: bounce-slow 2s ease-in-out infinite;
        }

        .animate-fade-in {
            animation: fade-in-up 0.8s ease-out forwards;
        }

        .shimmer {
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.4), transparent);
            background-size: 200% 100%;
            animation: shimmer 2s infinite;
        }

        .feature-card {
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }

        .feature-card:hover {
            transform: translateY(-12px) scale(1.02);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.2);
        }

        /* Calculator Card */
        .calculator-card {
            background: linear-gradient(145deg, rgba(255, 255, 255, 0.95), rgba(255, 255, 255, 0.8));
            backdrop-filter: blur(20px);
        }

        /* Money particles */
        .money-particle {
            position: absolute;
            font-size: 24px;
            animation: money-float linear infinite;
            opacity: 0;
            pointer-events: none;
        }

        /* Comparison table */
        .comparison-check {
            color: #22c55e;
        }

        .comparison-x {
            color: #ef4444;
        }

        /* Scroll animations */
        .scroll-reveal {
            opacity: 0;
            transform: translateY(40px);
            transition: all 0.8s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }

        .scroll-reveal.revealed {
            opacity: 1;
            transform: translateY(0);
        }

        /* Interactive elements */
        .interactive-input:focus {
            box-shadow: 0 0 0 4px rgba(34, 197, 94, 0.2);
        }

        /* Glow effect for CTAs */
        .cta-glow {
            position: relative;
            overflow: hidden;
        }

        .cta-glow::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
            transition: left 0.5s;
        }

        .cta-glow:hover::before {
            left: 100%;
        }
    </style>
</head>

<body class="font-sans antialiased bg-slate-50 text-slate-800 overflow-x-hidden">

    <!-- Floating Money Particles -->
    <div id="particles" class="fixed inset-0 pointer-events-none z-0"></div>

    <!-- HEADER -->
    <header class="fixed w-full top-0 z-50 glass">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 flex justify-between items-center">
            <a href="#" class="text-2xl font-bold gradient-text">💰 OrbisPay</a>
            <nav class="hidden md:flex items-center gap-8">
                <a href="#calculadora"
                    class="text-slate-600 hover:text-primary-600 transition font-medium">Calculadora</a>
                <a href="#caracteristicas"
                    class="text-slate-600 hover:text-primary-600 transition font-medium">Características</a>
                <a href="#comparacion" class="text-slate-600 hover:text-primary-600 transition font-medium">¿Por qué
                    nosotros?</a>
                <a href="index.php" class="text-primary-600 font-semibold hover:text-primary-700">Iniciar Sesión</a>
                <a href="index.php"
                    class="cta-glow bg-primary-500 text-white px-6 py-3 rounded-full font-semibold hover:bg-primary-600 transition shadow-lg shadow-primary-500/30 pulse-glow">
                    Regístrate Gratis
                </a>
            </nav>
            <button id="menu-btn" class="md:hidden p-2">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
            </button>
        </div>
    </header>

    <!-- HERO -->
    <section class="relative min-h-screen flex items-center pt-20 overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-br from-primary-50 via-white to-teal-50"></div>
        <div class="absolute top-20 right-10 w-72 h-72 bg-primary-300 rounded-full filter blur-3xl opacity-30 float">
        </div>
        <div
            class="absolute bottom-20 left-10 w-96 h-96 bg-teal-300 rounded-full filter blur-3xl opacity-20 float-reverse">
        </div>
        <div class="absolute top-1/2 left-1/2 w-64 h-64 bg-blue-300 rounded-full filter blur-3xl opacity-20 float"
            style="animation-delay:2s"></div>

        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 grid lg:grid-cols-2 gap-12 items-center">
            <div class="animate-fade-in">
                <div
                    class="inline-flex items-center px-4 py-2 bg-primary-100 text-primary-700 rounded-full text-sm font-semibold mb-6 bounce-slow">
                    <span class="w-2 h-2 bg-primary-500 rounded-full mr-2 animate-pulse"></span>
                    +10,000 usuarios ya tomaron el control
                </div>
                <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold leading-tight mb-6">
                    Transforma tu Dinero de un <span class="gradient-text">Problema</span> a tu Mayor <span
                        class="gradient-text">Aliado</span>
                </h1>
                <p class="text-xl text-slate-600 mb-8 max-w-lg leading-relaxed">
                    La única plataforma que te permite <strong>visualizar, controlar y alcanzar</strong> tus metas
                    financieras con la facilidad de una red social.
                </p>
                <div class="flex flex-col sm:flex-row gap-4">
                    <a href="index.php"
                        class="cta-glow inline-flex items-center justify-center bg-primary-500 text-white text-lg font-bold px-8 py-4 rounded-full hover:bg-primary-600 transition-all shadow-xl shadow-primary-500/30 pulse-glow group">
                        ¡Empieza Gratis Ahora!
                        <svg class="w-5 h-5 ml-2 group-hover:translate-x-1 transition-transform" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 7l5 5m0 0l-5 5m5-5H6" />
                        </svg>
                    </a>
                    <a href="#calculadora"
                        class="inline-flex items-center justify-center bg-white text-slate-700 text-lg font-semibold px-8 py-4 rounded-full border-2 border-slate-200 hover:border-primary-400 hover:text-primary-600 transition-all group">
                        <span class="mr-2">🎯</span> Calcula tu Meta
                    </a>
                </div>
                <div class="mt-8 flex items-center gap-4 text-sm text-slate-500">
                    <span class="flex items-center"><svg class="w-5 h-5 text-primary-500 mr-1" fill="currentColor"
                            viewBox="0 0 20 20">
                            <path
                                d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" />
                        </svg>100% Gratis</span>
                    <span class="flex items-center"><svg class="w-5 h-5 text-primary-500 mr-1" fill="currentColor"
                            viewBox="0 0 20 20">
                            <path
                                d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" />
                        </svg>Sin tarjeta</span>
                    <span class="flex items-center"><svg class="w-5 h-5 text-primary-500 mr-1" fill="currentColor"
                            viewBox="0 0 20 20">
                            <path
                                d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" />
                        </svg>Configura en 2 min</span>
                </div>
            </div>

            <!-- Interactive Goal Card -->
            <div class="relative animate-fade-in" style="animation-delay: 0.3s">
                <div class="absolute -top-4 -right-4 w-24 h-24 bg-primary-400 rounded-full filter blur-2xl opacity-40">
                </div>
                <div class="calculator-card rounded-3xl shadow-2xl p-8 border border-white/50 relative z-10">
                    <div class="absolute top-4 right-4 flex gap-1">
                        <div class="w-3 h-3 rounded-full bg-red-400"></div>
                        <div class="w-3 h-3 rounded-full bg-yellow-400"></div>
                        <div class="w-3 h-3 rounded-full bg-green-400"></div>
                    </div>
                    <div class="text-center mb-6">
                        <div
                            class="w-20 h-20 bg-gradient-to-br from-primary-400 to-teal-400 rounded-2xl mx-auto mb-4 flex items-center justify-center shadow-lg float">
                            <span class="text-4xl">✈️</span>
                        </div>
                        <h3 class="font-bold text-xl">Mi sueño: Viaje a Europa</h3>
                    </div>
                    <div class="mb-4">
                        <div class="flex justify-between text-sm mb-2">
                            <span class="text-slate-500">Progreso</span>
                            <span class="font-bold text-primary-600">$3,250,000 / $5,000,000</span>
                        </div>
                        <div class="bg-slate-200 rounded-full h-4 overflow-hidden">
                            <div class="bg-gradient-to-r from-primary-400 via-teal-400 to-primary-500 h-full rounded-full shimmer"
                                style="width: 65%"></div>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4 mb-6">
                        <div class="bg-primary-50 rounded-xl p-4 text-center">
                            <p class="text-2xl font-bold text-primary-600">65%</p>
                            <p class="text-xs text-slate-500">Completado</p>
                        </div>
                        <div class="bg-teal-50 rounded-xl p-4 text-center">
                            <p class="text-2xl font-bold text-teal-600">5 meses</p>
                            <p class="text-xs text-slate-500">Para lograrlo</p>
                        </div>
                    </div>
                    <div class="bg-gradient-to-r from-primary-500 to-teal-500 rounded-xl p-4 text-white text-center">
                        <p class="text-sm opacity-90">Ahorra esto cada mes:</p>
                        <p class="text-3xl font-bold">$350,000</p>
                        <p class="text-xs opacity-75">¡Tú puedes! 🚀</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Scroll indicator -->
        <div class="absolute bottom-8 left-1/2 transform -translate-x-1/2 bounce-slow">
            <a href="#calculadora" class="flex flex-col items-center text-slate-400 hover:text-primary-500 transition">
                <span class="text-sm mb-2">Descubre más</span>
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M19 14l-7 7m0 0l-7-7m7 7V3" />
                </svg>
            </a>
        </div>
    </section>

    <!-- CALCULADORA INTERACTIVA -->
    <section id="calculadora" class="py-24 bg-white relative">
        <div class="max-w-4xl mx-auto px-4">
            <div class="text-center mb-12 scroll-reveal">
                <span
                    class="inline-block px-4 py-2 bg-primary-100 text-primary-700 rounded-full text-sm font-semibold mb-4">🎯
                    Herramienta Interactiva</span>
                <h2 class="text-3xl sm:text-4xl font-extrabold mb-4">Calcula Cuándo Lograrás tu Sueño</h2>
                <p class="text-lg text-slate-600">Ingresa tu meta y descubre el camino para alcanzarla</p>
            </div>

            <div class="calculator-card rounded-3xl shadow-2xl p-8 md:p-12 border border-slate-100 scroll-reveal">
                <div class="grid md:grid-cols-2 gap-8">
                    <div class="space-y-6">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">🎯 ¿Cuál es tu sueño?</label>
                            <select id="calc-dream"
                                class="interactive-input w-full px-4 py-3 rounded-xl border-2 border-slate-200 focus:border-primary-400 outline-none transition text-lg">
                                <option value="viaje">✈️ Viaje soñado</option>
                                <option value="carro">🚗 Mi primer carro</option>
                                <option value="casa">🏠 Cuota inicial casa</option>
                                <option value="negocio">💼 Mi negocio propio</option>
                                <option value="educacion">🎓 Estudios</option>
                                <option value="otro">✨ Otro sueño</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">💰 ¿Cuánto necesitas?</label>
                            <div class="relative">
                                <span
                                    class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 font-semibold">$</span>
                                <input type="number" id="calc-amount" value="5000000"
                                    class="interactive-input w-full pl-8 pr-4 py-3 rounded-xl border-2 border-slate-200 focus:border-primary-400 outline-none transition text-lg"
                                    placeholder="5,000,000">
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">📅 ¿Cuánto puedes ahorrar al
                                mes?</label>
                            <div class="relative">
                                <span
                                    class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 font-semibold">$</span>
                                <input type="number" id="calc-monthly" value="500000"
                                    class="interactive-input w-full pl-8 pr-4 py-3 rounded-xl border-2 border-slate-200 focus:border-primary-400 outline-none transition text-lg"
                                    placeholder="500,000">
                            </div>
                        </div>
                        <button onclick="calculateGoal()"
                            class="cta-glow w-full bg-primary-500 text-white font-bold py-4 rounded-xl hover:bg-primary-600 transition shadow-lg shadow-primary-500/30">
                            ✨ Calcular mi camino
                        </button>
                    </div>

                    <div id="calc-result" class="flex items-center justify-center">
                        <div class="text-center p-8 bg-gradient-to-br from-primary-50 to-teal-50 rounded-2xl w-full">
                            <div class="text-6xl mb-4">🎯</div>
                            <p class="text-slate-600">Ingresa tus datos y descubre cuándo alcanzarás tu meta</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- PROBLEMA VS SOLUCIÓN -->
    <section class="py-24 bg-slate-50">
        <div class="max-w-7xl mx-auto px-4">
            <div class="grid lg:grid-cols-2 gap-8 items-stretch">
                <div
                    class="scroll-reveal bg-gradient-to-br from-red-50 to-orange-50 rounded-3xl p-8 border border-red-100 flex flex-col">
                    <div class="w-16 h-16 bg-red-100 rounded-2xl flex items-center justify-center mb-6">
                        <span class="text-3xl">😰</span>
                    </div>
                    <h2 class="text-2xl font-bold text-red-800 mb-4">El Problema que Todos Viven</h2>
                    <p class="text-red-700 text-lg mb-6 font-medium">El 80% de las personas no sabe a dónde va su
                        dinero.</p>
                    <ul class="space-y-4 text-red-600 flex-grow">
                        <li class="flex items-start"><span class="text-red-400 mr-3 text-xl">✗</span><span>Viven al día,
                                sin un plan claro para el futuro</span></li>
                        <li class="flex items-start"><span class="text-red-400 mr-3 text-xl">✗</span><span>Las apps de
                                bancos son confusas y limitadas</span></li>
                        <li class="flex items-start"><span class="text-red-400 mr-3 text-xl">✗</span><span>Las hojas de
                                cálculo son aburridas y nadie las usa</span></li>
                        <li class="flex items-start"><span class="text-red-400 mr-3 text-xl">✗</span><span>Sus sueños
                                siempre quedan "para después"</span></li>
                    </ul>
                </div>
                <div class="scroll-reveal bg-gradient-to-br from-primary-50 to-teal-50 rounded-3xl p-8 border border-primary-100 flex flex-col"
                    style="animation-delay: 0.2s">
                    <div class="w-16 h-16 bg-primary-100 rounded-2xl flex items-center justify-center mb-6">
                        <span class="text-3xl">✨</span>
                    </div>
                    <h2 class="text-2xl font-bold text-primary-800 mb-4">La Solución: OrbisPay</h2>
                    <p class="text-primary-700 text-lg mb-6 font-medium">Te damos control total de tu dinero.</p>
                    <ul class="space-y-4 text-primary-600 flex-grow">
                        <li class="flex items-start"><span
                                class="text-primary-400 mr-3 text-xl">✓</span><span><strong>Metas Inteligentes</strong>
                                que programan tu ahorro automáticamente</span></li>
                        <li class="flex items-start"><span class="text-primary-400 mr-3 text-xl">✓</span><span>Visualiza
                                tu progreso en <strong>tiempo real</strong></span></li>
                        <li class="flex items-start"><span class="text-primary-400 mr-3 text-xl">✓</span><span>Interfaz
                                tan simple como <strong>una red social</strong></span></li>
                        <li class="flex items-start"><span class="text-primary-400 mr-3 text-xl">✓</span><span>De la
                                incertidumbre a la <strong>certeza financiera</strong></span></li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- CARACTERÍSTICAS -->
    <section id="caracteristicas" class="py-24 bg-white">
        <div class="max-w-7xl mx-auto px-4">
            <div class="text-center mb-16 scroll-reveal">
                <h2 class="text-3xl sm:text-4xl font-extrabold mb-4">Las Herramientas que Cambiarán tu Vida</h2>
                <p class="text-lg text-slate-600 max-w-2xl mx-auto">Módulos diseñados para que alcances tus metas sin
                    esfuerzo</p>
            </div>
            <div class="grid md:grid-cols-3 gap-8">
                <div class="feature-card scroll-reveal bg-white p-8 rounded-3xl shadow-lg border border-slate-100">
                    <div
                        class="w-16 h-16 bg-gradient-to-br from-primary-400 to-teal-400 rounded-2xl flex items-center justify-center mb-6 shadow-lg">
                        <span class="text-3xl">🎯</span>
                    </div>
                    <h3 class="text-xl font-bold mb-3">Módulo de Metas</h3>
                    <p class="text-slate-600 mb-4">Define tus sueños y deja que OrbisPay te diga <strong>cuánto y cuándo
                            ahorrar</strong>.</p>
                    <div class="bg-primary-50 rounded-xl p-4">
                        <p class="text-sm text-primary-700 font-medium">✨ Ahorro automático a prueba de olvidos</p>
                    </div>
                </div>
                <div class="feature-card scroll-reveal bg-white p-8 rounded-3xl shadow-lg border border-slate-100 relative"
                    style="animation-delay: 0.1s">
                    <span
                        class="absolute top-4 right-4 bg-amber-100 text-amber-700 text-xs font-semibold px-3 py-1 rounded-full">PRÓXIMAMENTE</span>
                    <div
                        class="w-16 h-16 bg-gradient-to-br from-amber-400 to-orange-400 rounded-2xl flex items-center justify-center mb-6 shadow-lg">
                        <span class="text-3xl">💰</span>
                    </div>
                    <h3 class="text-xl font-bold mb-3">Módulo de Presupuestos</h3>
                    <p class="text-slate-600 mb-4">Límites de gasto por categoría y <strong>alertas antes de
                            excederte</strong>.</p>
                    <div class="bg-amber-50 rounded-xl p-4">
                        <p class="text-sm text-amber-700 font-medium">🔔 Nunca más te preguntarás dónde se fue tu dinero
                        </p>
                    </div>
                </div>
                <div class="feature-card scroll-reveal bg-white p-8 rounded-3xl shadow-lg border border-slate-100"
                    style="animation-delay: 0.2s">
                    <div
                        class="w-16 h-16 bg-gradient-to-br from-blue-400 to-indigo-400 rounded-2xl flex items-center justify-center mb-6 shadow-lg">
                        <span class="text-3xl">📊</span>
                    </div>
                    <h3 class="text-xl font-bold mb-3">Visualización Clara</h3>
                    <p class="text-slate-600 mb-4">Gráficos intuitivos de tu salud financiera.</p>
                    <div class="bg-blue-50 rounded-xl p-4">
                        <p class="text-sm text-blue-700 font-medium">⚡ Entiende tus finanzas en 5 segundos</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- COMPARACIÓN -->
    <section id="comparacion" class="py-24 bg-slate-50">
        <div class="max-w-5xl mx-auto px-4">
            <div class="text-center mb-12 scroll-reveal">
                <h2 class="text-3xl sm:text-4xl font-extrabold mb-4">¿Por Qué Elegir OrbisPay?</h2>
                <p class="text-lg text-slate-600">Compara y decide tú mismo</p>
            </div>
            <div class="scroll-reveal overflow-x-auto">
                <table class="w-full bg-white rounded-2xl shadow-xl overflow-hidden">
                    <thead>
                        <tr class="bg-slate-100">
                            <th class="p-4 text-left font-semibold">Característica</th>
                            <th class="p-4 text-center font-semibold">Hojas de Cálculo</th>
                            <th class="p-4 text-center font-semibold">Apps de Bancos</th>
                            <th class="p-4 text-center font-semibold bg-primary-50 text-primary-700">💰 OrbisPay</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <tr>
                            <td class="p-4">Fácil de usar</td>
                            <td class="p-4 text-center comparison-x text-xl">✗</td>
                            <td class="p-4 text-center text-amber-500 text-xl">~</td>
                            <td class="p-4 text-center comparison-check text-xl bg-primary-50">✓</td>
                        </tr>
                        <tr>
                            <td class="p-4">Metas con fechas automáticas</td>
                            <td class="p-4 text-center comparison-x text-xl">✗</td>
                            <td class="p-4 text-center comparison-x text-xl">✗</td>
                            <td class="p-4 text-center comparison-check text-xl bg-primary-50">✓</td>
                        </tr>
                        <tr>
                            <td class="p-4">Gráficos bonitos e intuitivos</td>
                            <td class="p-4 text-center comparison-x text-xl">✗</td>
                            <td class="p-4 text-center text-amber-500 text-xl">~</td>
                            <td class="p-4 text-center comparison-check text-xl bg-primary-50">✓</td>
                        </tr>
                        <tr>
                            <td class="p-4">Motivación y gamificación</td>
                            <td class="p-4 text-center comparison-x text-xl">✗</td>
                            <td class="p-4 text-center comparison-x text-xl">✗</td>
                            <td class="p-4 text-center comparison-check text-xl bg-primary-50">✓</td>
                        </tr>
                        <tr>
                            <td class="p-4">100% Gratis</td>
                            <td class="p-4 text-center comparison-check text-xl">✓</td>
                            <td class="p-4 text-center comparison-check text-xl">✓</td>
                            <td class="p-4 text-center comparison-check text-xl bg-primary-50">✓</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </section>

    <!-- TESTIMONIOS -->
    <section class="py-24 bg-white">
        <div class="max-w-7xl mx-auto px-4">
            <div class="text-center mb-16 scroll-reveal">
                <h2 class="text-3xl sm:text-4xl font-extrabold mb-4">Ellos ya Tomaron el Control</h2>
            </div>
            <div class="grid md:grid-cols-3 gap-8">
                <div class="scroll-reveal bg-slate-50 p-6 rounded-2xl hover:shadow-lg transition-shadow">
                    <div class="flex items-center mb-4">
                        <div
                            class="w-12 h-12 bg-gradient-to-br from-primary-400 to-teal-400 rounded-full flex items-center justify-center text-white font-bold text-lg">
                            SM</div>
                        <div class="ml-4">
                            <p class="font-semibold">Sofía Martínez</p>
                            <p class="text-sm text-slate-500">Diseñadora</p>
                        </div>
                    </div>
                    <p class="text-slate-600 mb-4">"Por fin pude ahorrar para mi viaje a Japón. OrbisPay me mostró
                        exactamente cuánto guardar cada mes. <strong>¡Lo logré en 8 meses!</strong>"</p>
                    <div class="text-yellow-400 text-lg">★★★★★</div>
                </div>
                <div class="scroll-reveal bg-slate-50 p-6 rounded-2xl hover:shadow-lg transition-shadow"
                    style="animation-delay: 0.1s">
                    <div class="flex items-center mb-4">
                        <div
                            class="w-12 h-12 bg-gradient-to-br from-blue-400 to-indigo-400 rounded-full flex items-center justify-center text-white font-bold text-lg">
                            DR</div>
                        <div class="ml-4">
                            <p class="font-semibold">Daniel Reyes</p>
                            <p class="text-sm text-slate-500">Ingeniero</p>
                        </div>
                    </div>
                    <p class="text-slate-600 mb-4">"Siempre quise comprar mi moto, pero el dinero 'desaparecía'. Con
                        OrbisPay, en <strong>6 meses tenía el ahorro completo</strong>."</p>
                    <div class="text-yellow-400 text-lg">★★★★★</div>
                </div>
                <div class="scroll-reveal bg-slate-50 p-6 rounded-2xl hover:shadow-lg transition-shadow"
                    style="animation-delay: 0.2s">
                    <div class="flex items-center mb-4">
                        <div
                            class="w-12 h-12 bg-gradient-to-br from-purple-400 to-pink-400 rounded-full flex items-center justify-center text-white font-bold text-lg">
                            LG</div>
                        <div class="ml-4">
                            <p class="font-semibold">Laura Gómez</p>
                            <p class="text-sm text-slate-500">Emprendedora</p>
                        </div>
                    </div>
                    <p class="text-slate-600 mb-4">"Los gráficos son increíbles. <strong>Por primera vez entiendo mis
                            finanzas</strong>. Es como tener un coach financiero gratis."</p>
                    <div class="text-yellow-400 text-lg">★★★★★</div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA FINAL -->
    <section class="py-24 gradient-bg relative overflow-hidden">
        <div class="absolute inset-0 opacity-10">
            <div class="absolute top-10 left-10 text-6xl float">💰</div>
            <div class="absolute top-20 right-20 text-5xl float-reverse">🎯</div>
            <div class="absolute bottom-10 left-1/4 text-4xl float" style="animation-delay:1s">✨</div>
            <div class="absolute bottom-20 right-1/4 text-5xl float-reverse" style="animation-delay:2s">🚀</div>
        </div>
        <div class="max-w-4xl mx-auto px-4 text-center text-white relative z-10">
            <h2 class="text-3xl sm:text-5xl font-extrabold mb-6">
                Deja de Soñar con el Futuro<br>y <span class="underline decoration-4 decoration-white/50">Empieza a
                    Construirlo</span> Hoy
            </h2>
            <p class="text-xl text-white/90 mb-8 max-w-2xl mx-auto">
                Miles de personas ya están alcanzando sus metas financieras. <strong>Tu turno de tomar el
                    control.</strong>
            </p>
            <a href="index.php"
                class="cta-glow inline-flex items-center bg-white text-primary-600 text-xl font-bold px-12 py-5 rounded-full hover:bg-primary-50 transition shadow-2xl group">
                ¡Quiero el Control de mis Finanzas!
                <svg class="w-6 h-6 ml-3 group-hover:translate-x-2 transition-transform" fill="none"
                    stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M13 7l5 5m0 0l-5 5m5-5H6" />
                </svg>
            </a>
            <p class="text-sm text-white/70 mt-6">Regístrate gratis • Sin tarjeta • Cancela cuando quieras</p>
        </div>
    </section>

    <!-- FOOTER -->
    <footer class="bg-slate-900 py-12">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex flex-col md:flex-row justify-between items-center mb-8">
                <div class="mb-6 md:mb-0">
                    <h4 class="text-2xl font-bold gradient-text mb-2">💰 OrbisPay</h4>
                    <p class="text-slate-400 text-sm">Tu aliado para la libertad financiera.</p>
                </div>
                <div class="flex gap-6 text-slate-400">
                    <a href="#" class="hover:text-white transition">Términos</a>
                    <a href="#" class="hover:text-white transition">Privacidad</a>
                    <a href="#" class="hover:text-white transition">Contacto</a>
                </div>
            </div>
            <div class="border-t border-slate-800 pt-8 text-center text-slate-500 text-sm">
                <p>&copy; 2025 OrbisPay. Todos los derechos reservados.</p>
            </div>
        </div>
    </footer>

    <script>
        // Calculator
        function calculateGoal() {
            const amount = parseFloat(document.getElementById('calc-amount').value) || 0;
            const monthly = parseFloat(document.getElementById('calc-monthly').value) || 1;
            const dream = document.getElementById('calc-dream').value;

            const dreamEmojis = { viaje: '✈️', carro: '🚗', casa: '🏠', negocio: '💼', educacion: '🎓', otro: '✨' };
            const dreamNames = { viaje: 'tu viaje soñado', carro: 'tu carro', casa: 'tu casa', negocio: 'tu negocio', educacion: 'tus estudios', otro: 'tu sueño' };

            const months = Math.ceil(amount / monthly);
            const years = Math.floor(months / 12);
            const remainingMonths = months % 12;

            let timeText = '';
            if (years > 0) timeText += `${years} año${years > 1 ? 's' : ''} `;
            if (remainingMonths > 0) timeText += `${remainingMonths} mes${remainingMonths > 1 ? 'es' : ''}`;

            document.getElementById('calc-result').innerHTML = `
        <div class="text-center p-8 bg-gradient-to-br from-primary-100 to-teal-100 rounded-2xl w-full animate-fade-in">
            <div class="text-6xl mb-4">${dreamEmojis[dream]}</div>
            <h3 class="text-2xl font-bold text-primary-800 mb-2">¡Lograrás ${dreamNames[dream]}!</h3>
            <p class="text-slate-600 mb-4">Ahorrando <strong>$${monthly.toLocaleString()}</strong> al mes</p>
            <div class="bg-white rounded-xl p-4 shadow-lg mb-4">
                <p class="text-sm text-slate-500">Lo conseguirás en</p>
                <p class="text-3xl font-bold gradient-text">${timeText.trim()}</p>
            </div>
            <a href="index.php" class="inline-block bg-primary-500 text-white font-bold px-6 py-3 rounded-full hover:bg-primary-600 transition shadow-lg">
                ¡Comenzar ahora! 🚀
            </a>
        </div>
    `;
        }

        // Scroll reveal
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('revealed');
                }
            });
        }, { threshold: 0.1 });

        document.querySelectorAll('.scroll-reveal').forEach(el => observer.observe(el));

        // Money particles
        function createParticle() {
            const emojis = ['💰', '💵', '💎', '⭐', '✨'];
            const particle = document.createElement('div');
            particle.className = 'money-particle';
            particle.textContent = emojis[Math.floor(Math.random() * emojis.length)];
            particle.style.left = Math.random() * 100 + 'vw';
            particle.style.animationDuration = (Math.random() * 10 + 10) + 's';
            document.getElementById('particles').appendChild(particle);
            setTimeout(() => particle.remove(), 20000);
        }
        setInterval(createParticle, 3000);
    </script>
</body>

</html>