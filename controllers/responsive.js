// En tu archivo responsive.js (REEMPLAZA TODO)

document.addEventListener("DOMContentLoaded", () => {
  const sidebar = document.getElementById("logo-sidebar");
  const mainContent = document.querySelector(".sm\\:ml-64");
  const drawerToggleBtn = document.querySelector(
    '[data-drawer-toggle="logo-sidebar"]'
  );

  if (!sidebar || !mainContent || !drawerToggleBtn) {
    console.warn(
      "Faltan elementos esenciales para el control del sidebar (sidebar, mainContent, o drawerToggleBtn)."
    );
    return;
  }

  /**
   * Función que ajusta la visibilidad del sidebar Y DEL BOTÓN DE HAMBURGUESA.
   */
  const adjustLayoutForScreen = () => {
    const isDesktop = window.innerWidth >= 1024; // Límite 'lg' de Tailwind

    if (isDesktop) {
      // --- VISTA ESCRITORIO (>= 1024px) ---
      // 1. Aseguramos que el sidebar esté visible.
      sidebar.classList.add("sm:translate-x-0");
      sidebar.classList.remove("-translate-x-full");

      // 2. Aseguramos que el contenido tenga su margen.
      mainContent.classList.add("sm:ml-64");

      // 3. ¡LA CLAVE! Ocultamos el botón de hamburguesa porque no se necesita.
      drawerToggleBtn.classList.add("hidden");
    } else {
      // --- VISTA TABLET/MÓVIL (< 1024px) ---
      // 1. Forzamos al sidebar a ocultarse.
      sidebar.classList.remove("sm:translate-x-0");
      sidebar.classList.add("-translate-x-full");

      // 2. Quitamos el margen del contenido.
      mainContent.classList.remove("sm:ml-64");

      // 3. ¡LA OTRA CLAVE! Mostramos el botón de hamburguesa para poder abrir el menú.
      drawerToggleBtn.classList.remove("hidden");
    }
  };

  // Ejecutamos la función al cargar la página.
  adjustLayoutForScreen();

  // Y la volvemos a ejecutar si el usuario cambia el tamaño de la ventana.
  window.addEventListener("resize", adjustLayoutForScreen);
});
