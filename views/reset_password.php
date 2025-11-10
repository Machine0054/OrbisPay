<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/../models/conexion.php';

$token = isset($_GET['token']) ? trim($_GET['token']) : '';
$tokenValido = false;
$correo = '';

if ($token !== '') {
    $conn = conectar();
    // Busca token vigente
    $stmt = $conn->prepare("
        SELECT email, expira_en
        FROM T_PASSWORD_RESETS
        WHERE token = ?
        LIMIT 1
    ");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($row = $res->fetch_assoc()) {
        if (strtotime($row['expira_en']) > time()) {
            $tokenValido = true;
            $correo = $row['email'];
        }
    }
    $stmt->close();
    $conn->close();
}
?>
<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>Restablecer contraseña - OrbisPay</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <style>
    body {
        font-family: system-ui, -apple-system, Segoe UI, Roboto, Ubuntu;
        background: #f7fafc;
        margin: 0
    }

    .card {
        max-width: 420px;
        margin: 6rem auto;
        background: #fff;
        border-radius: 14px;
        padding: 24px;
        box-shadow: 0 10px 25px rgba(0, 0, 0, .08)
    }

    .h1 {
        font-weight: 700;
        font-size: 1.25rem;
        margin: 0 0 6px
    }

    .muted {
        color: #6b7280;
        margin: 0 0 18px
    }

    label {
        display: block;
        margin: 12px 0 6px;
        color: #374151
    }

    input[type=password] {
        width: 100%;
        padding: 12px;
        border: 1px solid #d1d5db;
        border-radius: 10px
    }

    button {
        width: 100%;
        margin-top: 16px;
        padding: 12px;
        border: 0;
        border-radius: 10px;
        background: #2563eb;
        color: #fff;
        font-weight: 600;
        cursor: pointer
    }

    .msg {
        margin-top: 12px;
        font-size: .95rem
    }

    .ok {
        color: #16a34a
    }

    .err {
        color: #dc2626
    }
    </style>
</head>

<body>
    <div class="card">
        <?php if (!$tokenValido): ?>
        <h1 class="h1">Enlace inválido o caducado</h1>
        <p class="muted">Solicita nuevamente el restablecimiento desde la pantalla de inicio de sesión.</p>
        <a href="/views/index.php">Volver al inicio</a>
        <?php else: ?>
        <h1 class="h1">Crea tu nueva contraseña</h1>
        <p class="muted">Para: <strong><?php echo htmlspecialchars($correo) ?></strong></p>

        <form id="resetForm">
            <input type="hidden" name="token" value="<?php echo htmlspecialchars($token) ?>">
            <label>Nueva contraseña</label>
            <input type="password" name="password" id="p1" required minlength="8" />
            <label>Confirmar contraseña</label>
            <input type="password" id="p2" required minlength="8" />
            <button type="submit">Guardar contraseña</button>
            <div id="msg" class="msg"></div>
        </form>

        <script>
        const f = document.getElementById('resetForm');
        const msg = document.getElementById('msg');
        f.addEventListener('submit', async (e) => {
            e.preventDefault();
            const p1 = document.getElementById('p1').value;
            const p2 = document.getElementById('p2').value;
            if (p1 !== p2) {
                msg.textContent = 'Las contraseñas no coinciden.';
                msg.className = 'msg err';
                return;
            }
            msg.textContent = 'Guardando...';
            msg.className = 'msg';

            const body = new FormData(f);
            body.append('password_confirm', p2);

            const r = await fetch('/models/procesar_restablecimiento.php', {
                method: 'POST',
                body
            });
            const j = await r.json();
            msg.textContent = j.message;
            msg.className = 'msg ' + (j.success ? 'ok' : 'err');

            if (j.success) {
                setTimeout(() => {
                    window.location.href = '/views/index.php';
                }, 1800);
            }
        });
        </script>
        <?php endif; ?>
    </div>
</body>

</html>