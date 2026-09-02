<?php
/**
 * Funciones de validación de perfil de alumno
 */

/**
 * Verifica si el perfil del alumno está incompleto
 * Retorna array con estado e indicador del campo incompleto, si aplica
 */
function isProfileIncomplete($alumno) {
    $campos_requeridos = [
        'nombre',
        'no_control',
        'carrera',
        'opcion_titulacion',
        'email',
        'celular',
        'fecha_egreso',
        'graduacion'
    ];
    
    foreach ($campos_requeridos as $campo) {
        if (empty($alumno[$campo]) || ($campo === 'graduacion' && !in_array($alumno[$campo], ['1', '2']))) {
            return [
                'incomplete' => true,
                'missing_field' => $campo
            ];
        }
    }
    
    return [
        'incomplete' => false,
        'missing_field' => null
    ];
}

/**
 * Obtiene los datos del alumno por ID
 */
function getAlumnoById($pdo, $alumno_id) {
    $stmt = $pdo->prepare("SELECT * FROM alumnos WHERE id = ? LIMIT 1");
    $stmt->execute([$alumno_id]);
    return $stmt->fetch();
}

/**
 * Redirige si el perfil está incompleto
 */
function redirectIfIncomplete($alumno) {
    $profile = isProfileIncomplete($alumno);
    if ($profile['incomplete']) {
        header('Location: ../../public/complete_profile.php');
        exit;
    }
}
?>
