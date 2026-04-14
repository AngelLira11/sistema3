<?php
session_start();
require_once 'config.php';

if (empty($_SESSION['alumno_id'])) {
    header('Location: index.php');
    exit;
}

$pdo  = getConexion();
$stmt = $pdo->prepare("SELECT * FROM alumnos WHERE id = ? LIMIT 1");
$stmt->execute([$_SESSION['alumno_id']]);
$alumno = $stmt->fetch();

if (!$alumno) {
    header('Location: index.php');
    exit;
}

$opciones_titulacion = [
    "Informe técnico de Residencia Profesional",
    "Informe de Residencia Profesional",
    "Proyecto de Investigación y/o Desarrollo Tecnológico",
    "Proyecto Integrador",
    "Proyecto Productivo",
    "Proyecto de Innovación Tecnológica",
    "Proyecto de emprendedurismo",
    "Proyecto de Educación Dual",
    "Tesis o tesina",
    "Otro",
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Constancia — <?= htmlspecialchars($alumno['nombre']) ?></title>
    <style>
        @page { size: letter; margin: 1cm; }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Arial', sans-serif; font-size: 10pt; line-height: 1.2; padding: 20px; background-color: #f0f0f0; }

        .btn-imprimir {
            display: block; width: 220px; margin: 0 auto 20px; padding: 12px;
            background: #003087; color: white; border: none; border-radius: 5px;
            cursor: pointer; font-weight: bold;
        }

        .documento {
            width: 21.59cm; min-height: 27.94cm; padding: 1.5cm;
            margin: auto; background: white; box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }

        .encabezado-logos { display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; }
        .encabezado-logos img { height: 60px; }
        .titulo-principal { text-align: center; font-weight: bold; font-size: 12pt; text-decoration: underline; margin-bottom: 15px; }

        /* Sección de requisitos */
        .requisitos { font-size: 8.5pt; margin-bottom: 20px; border-bottom: 1px solid #000; padding-bottom: 10px; }
        .requisitos h4 { font-size: 9pt; margin-bottom: 5px; }
        .requisitos ol { margin-left: 20px; }
        .requisitos li { margin-bottom: 2px; }

        .cuerpo-form { display: grid; grid-template-columns: 1.5fr 1fr; border: 1px solid #000; }

        /* Columna Izquierda - Alumno */
        .col-izq { padding: 15px; border-right: 1px solid #000; }
        .seccion-titulo { font-weight: bold; text-align: center; border-bottom: 1px solid #000; margin-bottom: 15px; padding-bottom: 5px; }
        .campo-texto { margin-bottom: 10px; display: flex; }
        .campo-texto .label { font-weight: normal; margin-right: 5px; }
        .campo-texto .linea { flex-grow: 1; border-bottom: 1px solid #000; padding-left: 5px; font-weight: bold; }

        .opciones-grid { display: grid; grid-template-columns: 1fr; gap: 4px; margin-top: 10px; }
        .opcion-item { display: flex; align-items: center; font-size: 9pt; }
        .box { width: 14px; height: 14px; border: 1px solid #000; margin-right: 8px; text-align: center; line-height: 12px; font-weight: bold; }

        /* Columna Derecha - Firma */
        .col-der { padding: 15px; display: flex; flex-direction: column; justify-content: flex-start; }
        .firma-espacio { height: 150px; border-bottom: 1px solid #000; margin-top: 40px; }
        .fecha-espacio { height: 40px; border-bottom: 1px solid #000; margin-top: 60px; }

        /* Footer Servicios Escolares */
        .servicios-escolares { grid-column: 1 / span 2; border-top: 1px solid #000; padding: 15px; }
        
        .footer-direccion { text-align: center; font-size: 7.5pt; margin-top: 20px; color: #444; }

        @media print {
            body { background: none; padding: 0; }
            .documento { box-shadow: none; margin: 0; width: 100%; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>

<button class="btn-imprimir no-print" onclick="window.print()">🖨️ Imprimir Formato</button>

<div class="documento">
    <div class="encabezado-logos">
        <img src="img/logo.png" alt="SEP"> 
    </div>

    <h2 class="titulo-principal">CONSTANCIA DE NO INCONVENIENCIA PARA TITULACIÓN</h2>

    <div class="requisitos">
        <h4>Requisitos para trámite: (LLENAR EN TINTA AZUL 2 ORIGINALES ESTE DOCUMENTO)</h4>
        <ol>
            <li>Acta de Nacimiento (Original formato 2025).</li>
            <li>Certificados de Preparatoria y Licenciatura (Originales).</li>
            <li>Constancias de Servicio Social y de acreditación de lengua extranjera.</li>
            <li>CURP (Descarga reciente) y Constancia de Situación Fiscal actualizada.</li>
            <li>6 fotografías tamaño credencial ovaladas (Ropa formal blanca/saco oscuro).</li>
            <li>Recibos y vales de donación de libros/tesis y pago de protocolo.</li>
            <li>Liberación académica y testimonio E.G.E.L. (si aplica).</li>
        </ol>
    </div>

    <div class="cuerpo-form">
        <div class="col-izq">
            <div class="seccion-titulo">Llenado por el Alumno</div>
            
            <div class="campo-texto">
                <span class="label">Nombre:</span>
                <span class="linea"><?= htmlspecialchars($alumno['nombre']) ?></span>
            </div>
            <div class="campo-texto">
                <span class="label">No. Control:</span>
                <span class="linea"><?= htmlspecialchars($alumno['no_control']) ?></span>
            </div>
            <div class="campo-texto">
                <span class="label">Carrera:</span>
                <span class="linea"><?= htmlspecialchars($alumno['carrera']) ?></span>
            </div>

            <p style="font-size: 9pt; margin-top:10px;">Opción de Titulación:</p>
            <div class="opciones-grid">
                <?php foreach ($opciones_titulacion as $op): 
                    $esSeleccionada = (strtolower(trim($op)) === strtolower(trim($alumno['opcion_titulacion'])));
                ?>
                    <div class="opcion-item">
                        <div class="box"><?= $esSeleccionada ? 'X' : '' ?></div>
                        <span><?= htmlspecialchars($op) ?></span>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="campo-texto" style="margin-top:20px;">
                <span class="label">Mail:</span>
                <span class="linea"><?= htmlspecialchars($alumno['email']) ?></span>
            </div>
            <div class="campo-texto">
                <span class="label">Celular:</span>
                <span class="linea"><?= htmlspecialchars($alumno['celular']) ?></span>
            </div>
        </div>

        <div class="col-der">
            <div class="seccion-titulo" style="font-size: 8pt;">FIRMA Y SELLO DE LA OFICINA DE TITULACIÓN</div>
            <div class="firma-espacio"></div>
            <div class="seccion-titulo" style="border:none; margin-top: 10px;">FECHA DE REVISIÓN</div>
            <div class="fecha-espacio"></div>
        </div>

        <div class="servicios-escolares">
            <div class="seccion-titulo">Llenado por Servicios Escolares</div>
            <div style="display: flex; gap: 20px; align-items: center; justify-content: center; font-size: 10pt;">
                <span>Mención Honorífica: ( &nbsp;&nbsp;&nbsp; )</span>
                <span>[ ] Sí</span>
                <span>[ ] No</span>
                <span>[ ] No aplica</span>
            </div>
        </div>
    </div>

    <div class="footer-direccion">
        Blvd. Revolución y Calz. Instituto Tecnológico de la Laguna S/N. Col. Centro C.P.27000, Torreón, Coahuila<br>
        Tel. (871)705-13-13
    </div>
</div>

</body>
</html>