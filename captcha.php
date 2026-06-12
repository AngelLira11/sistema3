<?php
/**
 * Securimage CAPTCHA Image Generator
 * Genera una nueva imagen CAPTCHA y la envía al navegador
 */

require_once 'securimage/securimage.php';

$captcha = new Securimage();
$captcha->setImageSize(250, 80);
$captcha->setCodeLength(6);
$captcha->display();
?>
