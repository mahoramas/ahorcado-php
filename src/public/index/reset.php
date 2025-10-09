<?php
declare(strict_types=1);

/**
 * Script encargado de reiniciar la partida del Ahorcado.
 * 
 * - Destruye la sesión actual.
 * - Limpia cualquier identificador de juego almacenado.
 * - Redirige al index.php para crear una nueva partida.
 */

session_start();

/**
 * Eliminar el identificador de la partida actual
 */
if (isset($_SESSION['game_id'])) {
    unset($_SESSION['game_id']);
}

/**
 * Destruir la sesión
 */
session_unset();
session_destroy();

/**
 * Redirigir al usuario a la página principal del juego.
 */
header('Location: index.php');
exit;
