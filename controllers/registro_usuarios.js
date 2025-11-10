// Password strength checker
function checkPasswordStrength(password) {
  const strengthIndicator = document.getElementById("passwordStrength");
  let strength = 0;
  let feedback = "";

  if (password.length >= 8) strength++;
  if (password.match(/[a-z]/)) strength++;
  if (password.match(/[A-Z]/)) strength++;
  if (password.match(/[0-9]/)) strength++;
  if (password.match(/[^a-zA-Z0-9]/)) strength++;

  switch (strength) {
    case 0:
    case 1:
      feedback = '<span class="strength-weak">Muy débil</span>';
      break;
    case 2:
      feedback = '<span class="strength-weak">Débil</span>';
      break;
    case 3:
      feedback = '<span class="strength-medium">Media</span>';
      break;
    case 4:
      feedback = '<span class="strength-strong">Fuerte</span>';
      break;
    case 5:
      feedback = '<span class="strength-strong">Muy fuerte</span>';
      break;
  }

  strengthIndicator.innerHTML = feedback;
  return strength;
}

// Progress bar update
function updateProgressBar() {
  const form = document.getElementById("registerForm");
  const inputs = form.querySelectorAll("input[required]");
  const checkbox = document.getElementById("terminos");
  let filledInputs = 0;

  inputs.forEach((input) => {
    if (input.value.trim() !== "") {
      filledInputs++;
    }
  });

  if (checkbox.checked) {
    filledInputs++;
  }

  const progress = (filledInputs / (inputs.length + 1)) * 100;
  document.getElementById("progressFill").style.width = progress + "%";
}

// Form validation
function validateForm() {
  const password = document.getElementById("password").value;
  const confirmPassword = document.getElementById("confirm_password").value;
  const email = document.getElementById("correo").value;
  const phone = document.getElementById("telefono").value;
  const birthDate = document.getElementById("fecha_nacimiento").value;

  // Clear previous messages
  document.getElementById("messageContainer").innerHTML = "";

  // Email validation
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  if (!emailRegex.test(email)) {
    showMessage("Por favor ingresa un correo electrónico válido", "error");
    return false;
  }

  // Phone validation (basic)
  const phoneRegex = /^[0-9+\-\s()]{10,}$/;
  if (!phoneRegex.test(phone)) {
    showMessage("Por favor ingresa un número de teléfono válido", "error");
    return false;
  }

  // Age validation (must be at least 18)
  const today = new Date();
  const birth = new Date(birthDate);
  const age = today.getFullYear() - birth.getFullYear();
  const monthDiff = today.getMonth() - birth.getMonth();

  if (
    age < 18 ||
    (age === 18 && monthDiff < 0) ||
    (age === 18 && monthDiff === 0 && today.getDate() < birth.getDate())
  ) {
    showMessage("Debes ser mayor de 18 años para registrarte", "error");
    return false;
  }

  // Password strength validation
  if (checkPasswordStrength(password) < 3) {
    showMessage(
      "La contraseña debe tener al menos 8 caracteres y contener letras mayúsculas, minúsculas y números",
      "error"
    );
    return false;
  }

  // Password match validation
  if (password !== confirmPassword) {
    showMessage("Las contraseñas no coinciden", "error");
    return false;
  }

  return true;
}

// Show message function
function showMessage(message, type) {
  const messageContainer = document.getElementById("messageContainer");
  const messageClass = type === "error" ? "error-message" : "success-message";

  messageContainer.innerHTML = `
                <div class="${messageClass}">
                    ${message}
                </div>
            `;

  // Auto-hide message after 5 seconds
  setTimeout(() => {
    messageContainer.innerHTML = "";
  }, 5000);
}


function handleSocialLogin(provider) {
  showMessage(`Registro con ${provider} en desarrollo`, "error");
}
// Event listeners
document.getElementById("password").addEventListener("input", function () {
  checkPasswordStrength(this.value);
  updateProgressBar();
});

document
  .getElementById("confirm_password")
  .addEventListener("input", function () {
    const password = document.getElementById("password").value;
    const confirmPassword = this.value;

    if (confirmPassword && password !== confirmPassword) {
      this.style.borderColor = "#dc2626";
    } else {
      this.style.borderColor = "#e5e7eb";
    }
  });

// Add event listeners for progress bar
document.querySelectorAll("input").forEach((input) => {
  input.addEventListener("input", updateProgressBar);
  input.addEventListener("change", updateProgressBar);
});

// Real-time email validation
document.getElementById("correo").addEventListener("blur", function () {
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  if (this.value && !emailRegex.test(this.value)) {
    this.style.borderColor = "#dc2626";
  } else {
    this.style.borderColor = "#e5e7eb";
  }
});

// Phone number formatting
document.getElementById("telefono").addEventListener("input", function () {
  let value = this.value.replace(/\D/g, "");
  if (value.length >= 10) {
    value = value.substring(0, 10);
    this.value = value.replace(/(\d{3})(\d{3})(\d{4})/, "($1) $2-$3");
  }
});

// Initialize progress bar
updateProgressBar();

// Smooth scroll for form elements
document.querySelectorAll("input").forEach((input) => {
  input.addEventListener("focus", function () {
    this.scrollIntoView({
      behavior: "smooth",
      block: "center",
    });
  });
});

//Inicio Registro de Usuarios

// Configuración de la aplicación
const API_ENDPOINT = "../models/registro_usuarios.php";
const VALIDATION_ENDPOINT = "../models/validar_usuario.php"; // Nuevo endpoint para validación
const LOGIN_URL = "../views/index.php";

// Referencias a elementos del DOM

const progressFill = document.getElementById("progressFill");
const messageContainer = document.getElementById("messageContainer");
const registerBtn = document.getElementById("registerBtn");
const buttonText = document.getElementById("buttonText");
const loadingSpinner = document.getElementById("loadingSpinner");
const successModal = document.getElementById("successModal");

// Variables para control de validación
let isEmailValid = true;
let isUsernameValid = true;
let validationTimeout = null;

// Validación en tiempo real
document.addEventListener("DOMContentLoaded", function () {
  const inputs = form.querySelectorAll(".input-field");
  const totalFields = inputs.length;

  inputs.forEach((input) => {
    input.addEventListener("input", function () {
      validateField(this);
      updateProgress();
    });

    input.addEventListener("blur", function () {
      validateField(this);

      // Validación específica para usuario y correo
      if (this.id === "usuario" || this.id === "correo") {
        validateUserExists(this);
      }
    });
  });

  // Validación especial para contraseñas
  const passwordField = document.getElementById("password");
  const confirmPasswordField = document.getElementById("confirm_password");

  passwordField.addEventListener("input", function () {
    checkPasswordStrength(this.value);
    if (confirmPasswordField.value) {
      validatePasswordMatch();
    }
  });

  confirmPasswordField.addEventListener("input", validatePasswordMatch);
});

// Función para validar si usuario o correo ya existen
function validateUserExists(field) {
  const value = field.value.trim();

  if (!value) return;

  // Debounce para evitar múltiples peticiones
  clearTimeout(validationTimeout);
  validationTimeout = setTimeout(() => {
    checkUserExists(field, value);
  }, 500);
}

// Función para verificar en el servidor si usuario/correo existe
function checkUserExists(field, value) {
  const fieldType = field.id; // 'usuario' o 'correo'

  // Mostrar indicador de carga
  showFieldValidation(field, "checking");

  $.ajax({
    url: VALIDATION_ENDPOINT,
    method: "POST",
    dataType: "json",
    data: {
      [fieldType]: value,
      action: "check_exists",
    },
    success: function (response) {
      if (response.success === false) {
        // El usuario/correo ya existe
        showFieldValidation(field, "exists", response.message);

        if (fieldType === "usuario") {
          isUsernameValid = false;
        } else if (fieldType === "correo") {
          isEmailValid = false;
        }
      } else {
        // El usuario/correo está disponible
        showFieldValidation(field, "available");

        if (fieldType === "usuario") {
          isUsernameValid = true;
        } else if (fieldType === "correo") {
          isEmailValid = true;
        }
      }
      updateProgress();
    },
    error: function (xhr, status, error) {
      console.error("Error validando usuario:", error);
      showFieldValidation(field, "error");

      // En caso de error, permitir continuar
      if (fieldType === "usuario") {
        isUsernameValid = true;
      } else if (fieldType === "correo") {
        isEmailValid = true;
      }
    },
  });
}

// Función para mostrar el estado de validación del campo
function showFieldValidation(field, status, message = "") {
  // Remover clases previas
  field.classList.remove("error", "success", "checking", "exists");

  // Buscar o crear el contenedor de mensaje
  let messageDiv = field.parentNode.querySelector(".field-validation-message");
  if (!messageDiv) {
    messageDiv = document.createElement("div");
    messageDiv.className = "field-validation-message";
    messageDiv.style.cssText = `
      font-size: 0.875rem;
      margin-top: 0.25rem;
      padding: 0.25rem 0;
    `;
    field.parentNode.appendChild(messageDiv);
  }

  switch (status) {
    case "checking":
      field.classList.add("checking");
      messageDiv.innerHTML = `
        <span style="color: #6b7280;">
          <i class="bi bi-arrow-clockwise spin"></i> Verificando...
        </span>
      `;
      break;

    case "exists":
      field.classList.add("error");
      messageDiv.innerHTML = `
        <span style="color: #ef4444;">
          <i class="bi bi-x-circle"></i> ${message}
        </span>
      `;
      break;

    case "available":
      field.classList.add("success");
      messageDiv.innerHTML = `
        <span style="color: #10b981;">
          <i class="bi bi-check-circle"></i> Disponible
        </span>
      `;
      break;

    case "error":
      messageDiv.innerHTML = `
        <span style="color: #f59e0b;">
          <i class="bi bi-exclamation-triangle"></i> Error al verificar
        </span>
      `;
      break;

    default:
      messageDiv.innerHTML = "";
  }
}

// Función para validar campos individuales (modificada)
function validateField(field) {
  const value = field.value.trim();
  let isValid = true;

  // Remover clases previas (excepto checking y exists)
  field.classList.remove("error", "success");

  switch (field.type) {
    case "email":
      const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
      isValid = emailRegex.test(value);
      break;
    case "tel":
      const phoneRegex = /^[\+]?[0-9\s\-\(\)]{10,}$/;
      isValid = phoneRegex.test(value);
      break;
    case "password":
      isValid = value.length >= 8;
      break;
    case "date":
      const birthDate = new Date(value);
      const today = new Date();
      const age = today.getFullYear() - birthDate.getFullYear();
      isValid = age >= 18 && age <= 100;
      break;
    default:
      isValid = value.length >= 2;
  }

  // Validación específica para el campo de usuario
  if (field.id === "usuario") {
    const userRegex = /^[a-zA-Z0-9_]{3,20}$/;
    isValid = userRegex.test(value);
  }

  // Aplicar clase según validación (solo si no está en proceso de verificación)
  if (
    !field.classList.contains("checking") &&
    !field.classList.contains("exists")
  ) {
    if (value && isValid) {
      field.classList.add("success");
    } else if (value && !isValid) {
      field.classList.add("error");
    }
  }

  return isValid;
}

// Función para validar el formulario completo (modificada)
function validateForm() {
  const inputs = form.querySelectorAll(".input-field");
  const termsCheckbox = document.getElementById("terminos");
  let isValid = true;
  let errors = [];

  // Validar campos individuales
  inputs.forEach((input) => {
    if (!validateField(input)) {
      isValid = false;
      errors.push(`${input.placeholder} no es válido`);
    }
  });

  // Validar que usuario y correo estén disponibles
  if (!isUsernameValid) {
    isValid = false;
    errors.push("El nombre de usuario ya está registrado");
  }

  if (!isEmailValid) {
    isValid = false;
    errors.push("El correo electrónico ya está registrado");
  }

  // Validar coincidencia de contraseñas
  if (!validatePasswordMatch()) {
    isValid = false;
    errors.push("Las contraseñas no coinciden");
  }

  // Validar términos y condiciones
  if (!termsCheckbox.checked) {
    isValid = false;
    errors.push("Debes aceptar los términos y condiciones");
  }

  if (!isValid) {
    showMessage(errors.join("<br>"), "error");
  }

  return isValid;
}

// Agregar estilos CSS para la animación de verificación
const style = document.createElement("style");
style.textContent = `
  .checking {
    border-color: #6b7280 !important;
  }
  
  .exists {
    border-color: #ef4444 !important;
  }
  
  .spin {
    animation: spin 1s linear infinite;
  }
  
  @keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
  }
  
  .field-validation-message {
    transition: all 0.3s ease;
  }
  
  @keyframes confettiFall {
    to {
      transform: translateY(100vh) rotate(360deg);
      opacity: 0;
    }
  }
`;
document.head.appendChild(style);

// [Resto de funciones que ya tienes...]
function validatePasswordMatch() {
  const password = document.getElementById("password").value;
  const confirmPassword = document.getElementById("confirm_password").value;
  const confirmField = document.getElementById("confirm_password");

  confirmField.classList.remove("error", "success");

  if (confirmPassword) {
    if (password === confirmPassword) {
      confirmField.classList.add("success");
      return true;
    } else {
      confirmField.classList.add("error");
      return false;
    }
  }
  return false;
}

function checkPasswordStrength(password) {
  const strengthIndicator = document.getElementById("passwordStrength");
  let strength = 0;
  let strengthText = "";

  if (password.length >= 8) strength++;
  if (/[a-z]/.test(password)) strength++;
  if (/[A-Z]/.test(password)) strength++;
  if (/[0-9]/.test(password)) strength++;
  if (/[^A-Za-z0-9]/.test(password)) strength++;

  switch (strength) {
    case 0:
    case 1:
    case 2:
      strengthText = "Débil";
      strengthIndicator.className = "password-strength weak";
      break;
    case 3:
    case 4:
      strengthText = "Media";
      strengthIndicator.className = "password-strength medium";
      break;
    case 5:
      strengthText = "Fuerte";
      strengthIndicator.className = "password-strength strong";
      break;
  }

  strengthIndicator.textContent = password ? `Contraseña: ${strengthText}` : "";
}

function updateProgress() {
  const inputs = form.querySelectorAll(".input-field");
  const termsCheckbox = document.getElementById("terminos");
  let completedFields = 0;

  inputs.forEach((input) => {
    if (
      input.value.trim() &&
      !input.classList.contains("error") &&
      !input.classList.contains("exists")
    ) {
      completedFields++;
    }
  });

  if (termsCheckbox.checked) {
    completedFields++;
  }

  const progress = (completedFields / (inputs.length + 1)) * 100;
  progressFill.style.width = `${progress}%`;
}


function showMessage(message, type = "error") {
  messageContainer.innerHTML = `
    <div class="message ${type}">
      ${message}
    </div>
  `;

  setTimeout(() => {
    messageContainer.innerHTML = "";
  }, 5000);
}

function getFormData() {
  const formData = new FormData(form);
  const data = {};

  for (let [key, value] of formData.entries()) {
    data[key] = value;
  }

  return data;
}

function showSuccessModal() {
  Swal.fire({
    title: "¡Cuenta creada!",
    text: "Tu usuario ha sido registrado exitosamente.",
    icon: "success",
    showConfirmButton: true,
    confirmButtonText: "Ir al Login",
    allowOutsideClick: false,
    allowEscapeKey: false,
  }).then((result) => {
    if (result.isConfirmed) {
      redirectToLogin();
    }
  });
}

function showSuccessRegistration() {
  const container = document.querySelector(".register-container");
  const formSection = document.querySelector(".form-section");

  const successDiv = document.createElement("div");
  successDiv.className = "text-center fade-in";
  successDiv.innerHTML = `
    <div class="mb-4">
      <div class="success-icon" style="font-size: 4rem; color: var(--success-color); margin-bottom: 1rem;">
        <i class="bi bi-check-circle-fill"></i>
      </div>
      <h3 style="color: var(--text-primary); margin-bottom: 1rem;">¡Cuenta creada exitosamente!</h3>
      <p style="color: var(--text-secondary); margin-bottom: 2rem;">
        Tu cuenta ha sido creada exitosamente. Ahora puedes iniciar sesión para comenzar a usar la plataforma.
      </p>
      <div class="d-grid">
        <a href="${LOGIN_URL}" class="btn btn-register">
          <i class="bi bi-box-arrow-in-right me-2"></i>Ir al login
        </a>
      </div>
    </div>
  `;

  formSection.innerHTML = "";
  formSection.appendChild(successDiv);
  createConfetti();
}

function createConfetti() {
  const colors = ["#10b981", "#059669", "#22c55e", "#f59e0b", "#ef4444"];
  const confettiCount = 50;

  for (let i = 0; i < confettiCount; i++) {
    const confetti = document.createElement("div");
    confetti.style.position = "fixed";
    confetti.style.left = Math.random() * 100 + "vw";
    confetti.style.top = "-10px";
    confetti.style.width = "10px";
    confetti.style.height = "10px";
    confetti.style.backgroundColor =
      colors[Math.floor(Math.random() * colors.length)];
    confetti.style.borderRadius = "50%";
    confetti.style.pointerEvents = "none";
    confetti.style.zIndex = "9999";
    confetti.style.animation = `confettiFall ${
      Math.random() * 3 + 2
    }s linear forwards`;

    document.body.appendChild(confetti);

    setTimeout(() => {
      confetti.remove();
    }, 5000);
  }
}

function redirectToLogin() {
  window.location.href = LOGIN_URL;
}

function handleSocialLogin(provider) {
  showMessage(`Funcionalidad de ${provider} próximamente...`, "info");
}

function showNotification(message, type = "info") {
  const notification = document.createElement("div");
  notification.className = `alert alert-${
    type === "error" ? "danger" : type
  } alert-dismissible fade show position-fixed`;
  notification.style.cssText = `
    top: 20px;
    right: 20px;
    z-index: 9999;
    min-width: 300px;
    box-shadow: var(--shadow-large);
    border-radius: 12px;
    border: none;
  `;

  notification.innerHTML = `
    <div class="d-flex align-items-center">
      <i class="bi bi-${
        type === "error" ? "exclamation-triangle" : "info-circle"
      } me-2"></i>
      <span>${message}</span>
    </div>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  `;

  document.body.appendChild(notification);

  setTimeout(() => {
    notification.remove();
  }, 5000);
}

// Declaramos la variable 'form' aquí para que sea accesible globalmente en este script.
const form = document.getElementById("registerForm");

form.addEventListener("submit", function (e) {
  e.preventDefault();

  // 1. --- LLAMADA A LA VALIDACIÓN DEL FRONTEND ---
  // Usamos tu función de validación. Si devuelve false, no continuamos.
  if (!validateForm()) {
    // La función validateForm ya muestra los mensajes de error.
    console.log("Validación del frontend falló.");
    return;
  }

  // Referencias a los elementos del botón
  const registerBtn = document.getElementById("registerBtn");
  const buttonText = document.getElementById("buttonText");
  const loadingSpinner = document.getElementById("loadingSpinner");

  // Cambiar estado del botón a "cargando"
  registerBtn.disabled = true;
  buttonText.textContent = "Creando cuenta...";
  loadingSpinner.classList.remove("hidden");

  // Recopilar datos del formulario
  const userData = getFormData(); // Usamos tu función auxiliar

  // 2. --- LLAMADA AJAX AL SERVIDOR ---
  $.ajax({
    url: API_ENDPOINT,
    method: "POST",
    dataType: "json",
    data: userData,
    success: function (response) {
      // 3. --- MANEJO CORRECTO DE LA RESPUESTA ---
      if (response.success === true) {
        // ÉXITO REAL: El servidor confirma que el usuario fue creado.
        showSuccessModal(); // Muestra el modal de éxito.
        form.reset();       // Limpia el formulario (esto ahora funciona).
        updateProgress();   // Resetea la barra de progreso.
      } else {
        // FALLO EN EL BACKEND: El servidor encontró un error (ej. usuario ya existe, contraseña débil, etc.).
        const errorMessage = response.message || "Ocurrió un error durante el registro.";
        showMessage(errorMessage, "error"); // Muestra el error que devuelve el PHP.
        showNotification(errorMessage, "error");
      }
    },
    error: function (xhr, status, error) {
      // ERROR DE CONEXIÓN: El servidor no respondió o hubo un error de red.
      console.error("Error AJAX:", status, error);
      const errorMessage = "Error de conexión. Por favor, intenta nuevamente.";
      showMessage(errorMessage, "error");
      showNotification(errorMessage, "error");
    },
    complete: function () {
      // 4. --- RESTAURAR EL BOTÓN (SIEMPRE) ---
      // Este bloque se ejecuta siempre, tanto en éxito como en error.
      registerBtn.disabled = false;
      buttonText.textContent = "Crear cuenta";
      loadingSpinner.classList.add("hidden");
    }
  });
});

// Actualizar progreso cuando se marque términos
document.getElementById("terminos").addEventListener("change", updateProgress);

// Keyboard navigation
document.addEventListener("keydown", function (e) {
  if (
    e.key === "Enter" &&
    e.target.tagName !== "BUTTON" &&
    e.target.type !== "checkbox"
  ) {
    const inputs = form.querySelectorAll('input:not([type="checkbox"])');
    const currentIndex = Array.from(inputs).indexOf(e.target);

    if (currentIndex < inputs.length - 1) {
      inputs[currentIndex + 1].focus();
    } else {
      form.querySelector('button[type="submit"]').click();
    }
  }
});


// Auto-format username (remove spaces, convert to lowercase)
document.getElementById("usuario").addEventListener("input", function () {
  this.value = this.value.toLowerCase().replace(/\s/g, "");
});

// Prevent paste of spaces in username
document.getElementById("usuario").addEventListener("paste", function (e) {
  setTimeout(() => {
    this.value = this.value.toLowerCase().replace(/\s/g, "");
  }, 10);
});


// Email format helper
document.getElementById("correo").addEventListener("blur", function () {
  this.value = this.value.toLowerCase().trim();
});

// Name formatting
document.getElementById("nombre").addEventListener("blur", function () {
  // Capitalize first letter of each word
  this.value = this.value.replace(/\b\w/g, (l) => l.toUpperCase());
});

document.getElementById("apellido").addEventListener("blur", function () {
  // Capitalize first letter of each word
  this.value = this.value.replace(/\b\w/g, (l) => l.toUpperCase());
});

// Initialize - Focus first input
document.addEventListener("DOMContentLoaded", function () {
  const firstInput = document.getElementById("nombreCompleto");
  if (firstInput) {
    firstInput.focus();
  }
});
