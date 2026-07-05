<?php
declare(strict_types=1);

/**
 * Archivo: usuario_actual.php
 *
 * Proposito:
 * Endpoint encargado de devolver el usuario actualmente autenticado.
 *
 * Uso:
 * Se puede llamar desde el frontend para saber si existe una sesion activa
 * y que datos basicos tiene el usuario.
 *
 * Metodo esperado:
 * - GET
 *
 * Entrada esperada:
 * - No requiere parametros.
 *
 * Salida:
 * - JSON con el usuario actual o null si no hay sesion.
 *
 * Flujo:
 * 1. Carga las funciones comunes de la API.
 * 2. Exige una peticion GET.
 * 3. Lee el usuario guardado en sesion.
 * 4. Devuelve respuesta JSON al frontend.
 */

/* ============================================================
   Carga de dependencias
   ============================================================ */

// Carga helpers compartidos para respuestas JSON, sesion, validaciones y logs.
require_once __DIR__ . '/comun.php';

/* ============================================================
   Validacion inicial de la peticion
   ============================================================ */

// Asegura que este endpoint solo acepte peticiones GET.
requerir_metodo('GET');

/* ============================================================
   Respuesta final
   ============================================================ */

// Devuelve el usuario de sesion o null si no hay usuario autenticado.
respuesta_exitosa(['usuario' => usuario_autenticado()]);
