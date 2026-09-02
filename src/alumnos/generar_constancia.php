<?php
session_start();
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../auth/profile_check.php';

if (empty($_SESSION['alumno_id'])) {
    header('Location: ../../public/index.php');
    exit;
}

$pdo  = getConexion();
$alumno = getAlumnoById($pdo, $_SESSION['alumno_id']);

if (!$alumno) {
    header('Location: ../../public/index.php');
    exit;
}

// Verificar que el perfil esté completo
$profile_check = isProfileIncomplete($alumno);
if ($profile_check['incomplete']) {
    header('Location: ../../public/complete_profile.php');
    exit;
}

$opciones_titulacion = [
    'Proyecto de Investigación y/o Desarrollo Tecnológico',
    'Proyecto Integrador',
    'Proyecto Productivo',
    'Proyecto de Innovación Tecnológica',
    'Proyecto de Emprendedurismo',
    'Proyecto de Educación Dual',
    'Tesis o tesinas',
    'Informe de residencia profesional',
    'Informe técnico de residencia profesional',
    'Memoria de experiencia profesional',
    'Memoria de residencia profesional',
    'Otro',
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Constancia — <?= htmlspecialchars($alumno['nombre']) ?></title>
    <style>
        /* Una sola página carta, sin overflow */
        @page { size: letter; margin: 0; }
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: Arial, sans-serif;
            font-size: 9.5pt;
            background: #e8e8e8;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        .btn-imprimir {
            display: block; width: 220px; margin: 12px auto;
            padding: 11px; background: #800000; color: white;
            border: none; border-radius: 5px; cursor: pointer;
            font-weight: bold; font-size: 14px;
        }

        .aviso-impresion {
            width: min(380px, 90%);
            margin: -4px auto 14px;
            padding: 7px 12px;
            text-align: center;
            color: #6b4f00;
            font-weight: 700;
            font-size: 13px;
            background: #fff8e1;
            border-left: 4px solid #d4af37;
            border-radius: 3px;
        }

        /* ── Hoja carta fija en UNA página ── */
        .documento {
            width: 21.59cm;
            height: 27.94cm;          /* fijo, no min-height */
            margin: 0 auto;
            background: white;
            padding: 0.7cm 1.6cm 0.5cm 1.6cm;
            display: flex;
            flex-direction: column;
            overflow: hidden;          /* corta si sobra, nunca rompe */
        }

        /* ── Logos ── */
        .encabezado-logos { text-align: center; margin-bottom: 0.3cm; }
        .encabezado-logos img { width: 12.5cm; max-width: 100%; height: auto; }

        /* ── Título ── */
        .titulo-principal {
            text-align: center; font-weight: bold;
            font-size: 11pt; margin-bottom: 0.2cm;
        }

        /* ── Requisitos ── */
        .req-titulo { font-size: 9.8pt; font-weight: bold; margin-bottom: 0.15cm; }
        .flecha { color: #1a56db; font-size: 13pt; vertical-align: middle; }

        .req-lista { font-size: 8.3pt; line-height: 1.45; margin-bottom: 0.25cm; }
        .req-lista ol { padding-left: 1cm; }
        .req-lista li { margin-bottom: 0; }
        .req-lista a { color: #1155cc; text-decoration: underline; }

        /* ── Tabla principal — ocupa el espacio restante ── */
        .tabla-principal {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #555;
            font-size: 9.5pt;
            flex: 1;                  /* llena el espacio vertical que queda */
        }

        /* Celda alumno */
        .celda-alumno {
            width: 66%;
            vertical-align: top;
            padding: 5px 10px;
            border-right: 1px solid #555;
        }
        .celda-alumno-titulo {
            text-align: center; font-size: 9.5pt; margin-bottom: 5px;
        }

        /* Campos con línea */
        .campo {
            display: flex; align-items: baseline;
            margin-bottom: 4px; font-size: 9.5pt;
        }
        .campo .lbl { white-space: nowrap; margin-right: 3px; }
        .campo .val {
            border-bottom: 1px solid #444;
            flex: 1; padding-bottom: 1px;
            color: #0000cc; font-size: 9.5pt; min-width: 20px;
        }

        /* Nombre y No. Control en la misma línea */
        .campo-nombre-row {
            display: flex; gap: 6px;
            margin-bottom: 4px; font-size: 9.5pt; align-items: baseline;
        }
        .nombre-grupo { display: flex; flex: 3; align-items: baseline; gap: 3px; }
        .no-grupo     { display: flex; align-items: baseline; gap: 3px; white-space: nowrap; }
        .campo-nombre-row .val {
            border-bottom: 1px solid #444;
            padding-bottom: 1px; color: #0000cc;
        }
        .val-nombre { flex: 1; }
        .val-no     { min-width: 55px; }

        /* Opciones titulación */
        .opciones-titulo { font-size: 9.5pt; margin: 4px 0 2px 0; }
        .opcion-row {
            display: flex; align-items: flex-start;
            gap: 4px; font-size: 8.8pt;
            margin-bottom: 1px; line-height: 1.25;
        }
        .chk {
            width: 10px; height: 10px;
            border: 1px solid #333;
            display: inline-flex; align-items: center; justify-content: center;
            flex-shrink: 0; margin-top: 1px;
            font-size: 8pt; font-weight: bold; color: #0000cc;
        }
        .otro-linea {
            border-bottom: 1px solid #444;
            flex: 1; display: inline-block; min-width: 80px;
        }

        /* ── Celda sello — espacios fieles al PDF ── */
        .celda-sello {
            width: 34%;
            vertical-align: top;
            padding: 5px 10px;
        }
        .sello-titulo {
            text-align: center; font-size: 9pt;
            margin-bottom: 4px; line-height: 1.3;
        }
        .sello-linea {
            border-top: 1px solid #444;
            width: 110px; margin: 0 auto;
        }
        /* Espacio grande para el sello (firma) — igual al PDF */
        .sello-espacio { height: 2.5cm; }

        .fecha-lbl { font-size: 9pt; margin-bottom: 0; text-align: center;}
        /* Espacio para fecha de revisión */
        .fecha-espacio { height: 2.5cm; }
        .fecha-linea {
            border-top: 1px solid #444;
            width: 110px; margin: 0 auto;
        }

        /* Fila servicios escolares */
        .celda-escolar {
            border-top: 1px solid #555;
            padding: 5px 10px;
        }
        .escolar-titulo {
            font-weight: bold; text-align: center;
            margin-bottom: 4px; font-size: 9.5pt;
        }
        .mencion-row { font-size: 9.5pt; }

        .barra-naranja {
            width: 100%;
            height: 4px;
            margin-top: 0.15cm;
            background: #c65a14;
        }

        /* Pie */
        .footer-direccion {
            text-align: center; font-size: 7pt;
            margin-top: 0.2cm; color: #333; line-height: 1.4;
        }

        @media print {
            body { background: none; }
            .documento { box-shadow: none; margin: 0; }
            .no-print { display: none !important; }
            /* Forzar UNA sola página */
            html, body { height: 27.94cm; overflow: hidden; }
        }
    </style>
</head>
<body>

<button class="btn-imprimir no-print" onclick="window.print()">🖨️ Imprimir / Guardar PDF</button>
<p class="aviso-impresion no-print">Favor de imprimir la hoja a color.</p>

<div class="documento">

    <!-- LOGOS -->
    <div class="encabezado-logos">
        <img src="../../public/assets/img/logo.png" alt="SEP / TecNM / ITL">
    </div>

    <!-- TÍTULO -->
    <div class="titulo-principal">
        CONSTANCIA DE NO INCONVENIENCIA PARA TITULACIÓN
    </div>

    <!-- REQUISITOS -->
    <div class="req-titulo">
        Requisitos para tramité: <span class="aviso">(LLENAR EN TINTA AZUL 2 ORIGINALES ESTE DOCUMENTO)</span>
        <span class="flecha">⬇</span>
    </div>

    <div class="req-lista">
        <ol>
            <li>Acta de Nacimiento. <strong>(Original y actualizada)</strong></li>
            <li>Certificado de Preparatoria y Carta de Autenticidad del mismo "Que incluya el periodo de estudios". <strong>(Original)</strong></li>
            <li>Certificado de Licenciatura. <strong>(Original)</strong></li>
            <li>Constancia de Servicios Social.</li>
            <li>Constancia del Centro de Idiomas del Instituto Tecnológico que otorgue la acreditación de lengua extranjera (estudiantes que ingresaron a partir de 1993), en caso de no contar con ella comunicarse a: <a href="mailto:leng_laguna@tecnm.mx">leng_laguna@te&nbsp;cnm.mx</a> o al teléfono (871)7051334. <strong>(Original)</strong></li>
            <li>CURP <strong>ACTUAL</strong></li>
            <li>Constancia de situación fiscal actualizada.</li>
            <li>6 fotografías tamaño credencial ovaladas de frente en blanco y negro, papel mate con blusa o camisa blanca y saco oscuro las mujeres, los hombres con saco y corbata oscuros, sin anteojos, con bigote recortado (en caso necesario) dejando al descubierto la comisura de los labios, sin patillas y sin barba.</li>
            <li>Vale de donación de libros. "Canjear recibo de pago en Biblioteca" <strong>(Original)</strong></li>
            <li>Vale de donación de tesis de biblioteca "enviar proyecto anexando oficio de liberación académica al siguiente correo cinformacion@correo.itlalaguna.edu.mx" <strong>(Original)</strong></li>
            <li>Recibo de pago de protocolo. <strong>(Original)</strong></li>
            <li>Liberación por parte del departamento académico "Se expide en División de Estudios". <strong>(Original)</strong><br>
                O si la opción de titulación es por E.G.E.L. traer testimonio del examen. <strong>(Original)</strong></li>
        </ol>
    </div>

    <!-- TABLA -->
    <table class="tabla-principal">
        <tr>
            <!-- COLUMNA ALUMNO -->
            <td class="celda-alumno">
                <div class="celda-alumno-titulo">Llenado por el Alumno.</div>

                <!-- Nombre y No. Control en la misma línea -->
                <div class="campo">
                    <span class="lbl">Nombre:</span>
                    <span class="val"><?= htmlspecialchars($alumno['nombre']) ?></span>
                </div>
                <div class="campo">
                    <span class="lbl">No. Control:</span>
                    <span class="val"><?= htmlspecialchars($alumno['no_control']) ?></span>
                </div>

                <div class="campo">
                    <span class="lbl">Carrera:</span>
                    <span class="val"><?= htmlspecialchars($alumno['carrera']) ?></span>
                </div>

                <div class="opciones-titulo">Opción de Titulación:</div>

                <?php foreach ($opciones_titulacion as $op):
                    $sel = (strtolower(trim($op)) === strtolower(trim($alumno['opcion_titulacion'])));
                    if ($op === 'Otro'): ?>
                    <div class="opcion-row">
                        <div class="chk"><?= $sel ? '✓' : '' ?></div>
                        <span>Otro <span class="otro-linea"></span></span>
                    </div>
                <?php else: ?>
                    <div class="opcion-row">
                        <div class="chk"><?= $sel ? '✓' : '' ?></div>
                        <span><?= htmlspecialchars($op) ?></span>
                    </div>
                <?php endif; endforeach; ?>

                <div class="campo" style="margin-top:8px;">
                    <span class="lbl">Mail:</span>
                    <span class="val"><?= htmlspecialchars($alumno['email']) ?></span>
                </div>
                <div class="campo">
                    <span class="lbl">Celular:</span>
                    <span class="val"><?= htmlspecialchars($alumno['celular']) ?></span>
                </div>
            </td>

            <!-- COLUMNA SELLO -->
            <td class="celda-sello">
                <div class="sello-titulo">FIRMA Y SELLO DE LA<br>OFICINA&nbsp;DE TITULACIÓN.</div>
                <!-- Espacio amplio para la firma/sello, fiel al PDF -->
                <div class="sello-espacio"></div>
                <div class="sello-linea"></div>
                <!-- Espacio para fecha de revisión -->
                <div class="fecha-espacio"></div>
                <div class="fecha-lbl">FECHA DE REVISIÓN</div>
                <div class="fecha-espacio" style="height:0.8cm;"></div>
                <div class="fecha-linea"></div>
            </td>
        </tr>

        <!-- SERVICIOS ESCOLARES -->
        <tr>
            <td colspan="2" class="celda-escolar">
                <div class="escolar-titulo">Llenado por Servicios Escolares</div>
                <div class="mencion-row">
                    Mención Honorífica: &nbsp;( &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; ) &nbsp;&nbsp;&nbsp;
                    [ &nbsp;] Si &nbsp;&nbsp;&nbsp;&nbsp;
                    [ &nbsp;] No &nbsp;&nbsp;&nbsp;&nbsp;
                    [ &nbsp;] No aplica
                </div>
            </td>
        </tr>
    </table>

    <div class="barra-naranja" aria-hidden="true"></div>

    <!-- PIE -->
    <div class="footer-direccion">
        Blvd. Revolución y Calz. Instituto Tecnológico de la Laguna S/N. Col. Centro C.P. 27000, Torreón, Coahuila<br>
        Tel. (871)705-13-13
    </div>

</div>
</body>
</html>