<?php
/**
 * Reload CAPTCHA via AJAX
 * Retorna el timestamp para forzar recarga de imagen (cache-busting)
 */

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(400);
    exit;
}

header('Content-Type: application/json');
echo json_encode(['timestamp' => time()]);
?>
