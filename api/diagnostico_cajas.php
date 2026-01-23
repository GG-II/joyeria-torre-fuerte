<?php
/**
 * DIAGNÓSTICO TEMPORAL - TABLA CAJAS
 */

require_once '../../config.php';
require_once '../../includes/db.php';
require_once '../../includes/api-helpers.php';

header('Content-Type: application/json; charset=utf-8');

verificar_api_autenticacion();

try {
    // Ver estructura de cajas
    $estructura = db_query("DESCRIBE cajas");
    
    // Ver primeros registros
    $registros = db_query("SELECT * FROM cajas LIMIT 3");
    
    responder_json(true, array(
        'estructura' => $estructura,
        'registros' => $registros
    ), 'Diagnóstico completado');
    
} catch (Exception $e) {
    responder_json(false, null, 'Error: ' . $e->getMessage(), 'ERROR');
}
