<?php
session_start();
require_once dirname(__DIR__, 2) . '/vendor/autoload.php';

use Gregwar\Captcha\CaptchaBuilder;
use Gregwar\Captcha\PhraseBuilder;

// 5 caracteres, sin letras/números ambiguos (0, O, 1, l, I)
$phraseBuilder = new PhraseBuilder(5);
$builder = new CaptchaBuilder(null, $phraseBuilder);

$builder->setDistortion(false);      // quita la distorsión/ondulado del texto
$builder->setMaxBehindLines(2);      // menos líneas detrás del texto (default suele ser más)
$builder->setMaxFrontLines(1);       // menos líneas encima del texto (esto es lo que más estorba)
$builder->setMaxAngle(8);            // menos rotación por letra (default puede llegar a 15-20)
$builder->setMaxOffset(3);           // menos desnivel vertical entre letras

$builder->build(200, 60);            // un poco más grande = más fácil de leer

$_SESSION['captcha_phrase'] = $builder->getPhrase();

header('Content-Type: image/jpeg');
header('Cache-Control: no-cache, must-revalidate');

$builder->output();