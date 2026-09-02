<?php
/**
 * Protección CSRF basada en tokens de sesión.
 *
 *  csrf_field()   → imprime un <input hidden> con el token (para usar en formularios).
 *  csrf_token()   → retorna el token actual (para usar en AJAX u otros contextos).
 *  csrf_validate() → valida el token enviado contra el de sesión; aborta si no coincide.
 */

function csrf_token(): string {
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field(): void {
    echo '<input type="hidden" name="csrf_token" value="' . csrf_token() . '">';
}

function csrf_validate(): void {
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
    $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    if (empty($token) || !hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
        http_response_code(403);
        exit('Token CSRF inválido.');
    }
}
