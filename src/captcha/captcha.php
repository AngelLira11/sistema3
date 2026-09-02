<?php
session_start();
$alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
$phrase = '';
for ($index = 0; $index < 5; $index++) {
	$phrase .= $alphabet[random_int(0, strlen($alphabet) - 1)];
}

$_SESSION['captcha_phrase'] = $phrase;

header('Content-Type: image/svg+xml');
header('Cache-Control: no-cache, must-revalidate');

$escapedPhrase = htmlspecialchars($phrase, ENT_QUOTES, 'UTF-8');
echo '<svg xmlns="http://www.w3.org/2000/svg" width="200" height="60" viewBox="0 0 200 60" role="img" aria-label="Captcha">';
echo '<rect width="200" height="60" fill="#f2f2f2"/>';
echo '<path d="M10 15 Q 60 45 110 18 T 190 35 M8 45 Q 65 12 125 42 T 192 16" fill="none" stroke="#b7b7b7" stroke-width="2"/>';
echo '<text x="100" y="40" text-anchor="middle" font-family="Arial, sans-serif" font-size="30" font-weight="700" letter-spacing="7" fill="#800000">' . $escapedPhrase . '</text>';
echo '</svg>';