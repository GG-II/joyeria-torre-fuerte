<?php
/**
 * ================================================
 * API: LISTAR CATEGORÍAS
 * ================================================
 * Endpoint para obtener listado de categorías con filtros
 * 
 * Método: GET
 * Autenticación: Requerida
 * Permisos: categorias.ver
 * 
 * Parámetros GET (todos opcionales):
 * - tipo_clasificacion: tipo, material, peso
 * - activo: 1 = activas, 0 = inactivas (default: solo activas)
 * - categoria_padre_id: ID de categoría padre (null para principales)
 * - arbol: true para obtener estructura jerárquica
 * 
 * Respuesta exitosa:
 * {
 *   "success": true,
 *   "data": [...],
 *   "message": "X categoría(s) encontrada(s)"
 * }
 */

require_once '../../config.php';
require_once '../../includes/db.php';
require_once '../../includes/api-helpers.php';
require_once '../../models/categoria.php';

header('Content-Type: application/json; charset=utf-8');

// Verificaciones de seguridad
verificar_api_autenticacion();
validar_metodo_http('GET');
verificar_api_permiso('categorias', 'ver');

try {
    global $pdo;
    
    // Si se solicita árbol jerárquico
    if (isset($_GET['arbol']) && $_GET['arbol'] === 'true') {
        $arbol = Categoria::obtenerArbol();
        responder_json(
            true,
            $arbol,
            'Árbol de categorías obtenido exitosamente'
        );
    }
    
    // Preparar filtros
    $filtros = [];
    
    if (isset($_GET['tipo_clasificacion']) && !empty($_GET['tipo_clasificacion'])) {
        $filtros['tipo_clasificacion'] = $_GET['tipo_clasificacion'];
    }
    
    // Filtro activo/inactivo
    // Si no se especifica activo en la URL, forzar NULL para mostrar todas
    if (isset($_GET['activo'])) {
        if ($_GET['activo'] === '1' || $_GET['activo'] === 'true') {
            $filtros['activo'] = 1;
        } elseif ($_GET['activo'] === '0' || $_GET['activo'] === 'false') {
            $filtros['activo'] = 0;
        }
    } else {
        // HACK: Enviar string 'all' para que el modelo NO aplique el filtro default
        // El modelo verifica isset($filtros['activo']), así que enviamos algo que NO sea 0 ni 1
        // Otra opción es crear un método listarTodas() en el modelo
        $filtros['activo'] = 'all';
    }
    
    // Filtro por categoría padre
    if (isset($_GET['categoria_padre_id'])) {
        $filtros['categoria_padre_id'] = $_GET['categoria_padre_id'] === 'null' ? null : (int)$_GET['categoria_padre_id'];
    }
    
    // Si activo = 'all', hacer consulta directa sin el modelo
    if (isset($filtros['activo']) && $filtros['activo'] === 'all') {
        unset($filtros['activo']);
        
        // Construir consulta manual
        global $pdo;
        $where = ['1=1'];
        $params = [];
        
        if (isset($filtros['tipo_clasificacion'])) {
            $where[] = 'tipo_clasificacion = ?';
            $params[] = $filtros['tipo_clasificacion'];
        }
        
        if (isset($filtros['categoria_padre_id'])) {
            if ($filtros['categoria_padre_id'] === null) {
                $where[] = 'categoria_padre_id IS NULL';
            } else {
                $where[] = 'categoria_padre_id = ?';
                $params[] = $filtros['categoria_padre_id'];
            }
        }
        
        $where_sql = implode(' AND ', $where);
        
        $sql = "SELECT c.*, 
                       cp.nombre as categoria_padre_nombre,
                       (SELECT COUNT(*) FROM productos WHERE categoria_id = c.id AND activo = 1) as total_productos
                FROM categorias c
                LEFT JOIN categorias cp ON c.categoria_padre_id = cp.id
                WHERE $where_sql
                ORDER BY c.tipo_clasificacion, c.nombre";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $categorias = $stmt->fetchAll();
    } else {
        // Obtener categorías usando el modelo
        $categorias = Categoria::listar($filtros);
    }
    
    responder_json(
        true,
        $categorias,
        count($categorias) . ' categoría(s) encontrada(s)'
    );
    
} catch (Exception $e) {
    responder_json(
        false,
        null,
        'Error al listar categorías: ' . $e->getMessage(),
        'ERROR_LISTAR_CATEGORIAS'
    );
}