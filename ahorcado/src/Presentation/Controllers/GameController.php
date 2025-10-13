<?php
declare(strict_types=1);

namespace App\Presentation\Controllers;

use App\Application\Services\ServicioPartida;

/**
 * Controlador principal del juego del Ahorcado.
 *
 * Gestiona las peticiones del usuario (GET/POST),
 * interactúa con el servicio de aplicación (ServicioPartida)
 * y devuelve los datos necesarios para renderizar la vista.
 */
final class GameController
{
    private ServicioPartida $servicioPartida;

    public function __construct(ServicioPartida $servicioPartida)
    {
        $this->servicioPartida = $servicioPartida;
    }

    /**
     * Maneja la solicitud actual y devuelve los datos para la vista.
     *
     * @return array Datos del estado actual del juego.
     */
    public function handle(): array
    {
        session_start();

        // Crear nueva partida si no existe
        if (!isset($_SESSION['game_id'])) {
            $gameId = $this->servicioPartida->crearNuevaPartida();
            $_SESSION['game_id'] = $gameId;
        } else {
            $gameId = $_SESSION['game_id'];
        }

        $mensaje = '';
        $palabraOculta = '';
        $letrasUsadas = [];
        $intentosRestantes = 0;
        $bodyState = 'playing';
        $palabraReal = '';

        $estado = $this->servicioPartida->obtenerEstado($gameId);
        $palabraOculta = $estado['palabra_oculta'] ?? '';
        $letrasUsadas = $estado['letras_usadas'] ?? [];
        $intentosRestantes = $estado['intentos_restantes'] ?? 0;
        $isWon = $estado['ganado'] ?? false;
        $isLost = $estado['perdido'] ?? false;
        $palabraReal = $estado['palabra_real'] ?? '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['letra']) && !$isWon && !$isLost) {
            $letra = trim($_POST['letra']);
            if ($letra !== '') {
                $resultado = $this->servicioPartida->probarLetra($gameId, $letra);
                $mensaje = $resultado['mensaje'] ?? '';
                $palabraOculta = $resultado['palabra_oculta'] ?? '';
                $letrasUsadas = $resultado['letras_usadas'] ?? [];
                $intentosRestantes = $resultado['intentos_restantes'] ?? 0;
                $isWon = $resultado['ganado'] ?? false;
                $isLost = $resultado['perdido'] ?? false;
                $palabraReal = $resultado['palabra_real'] ?? $palabraReal;
            }
        }

        if ($isWon) {
            $bodyState = 'won';
            $mensaje = '🎉 ¡Has ganado! La palabra era: ' . $palabraReal;
        } elseif ($isLost) {
            $bodyState = 'lost';
            $mensaje = '💀 Has perdido. La palabra era: ' . $palabraReal;
        }

        return [
            'mensaje' => $mensaje,
            'palabra_oculta' => $palabraOculta,
            'letras_usadas' => $letrasUsadas,
            'intentos_restantes' => $intentosRestantes,
            'estado' => $bodyState,
        ];
    }
}
