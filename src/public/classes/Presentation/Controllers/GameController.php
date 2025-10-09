<?php
declare(strict_types=1);

namespace App\Presentation\Controllers;

use App\Application\Services\ServicioPartida;

/**
 * Controlador principal del juego del Ahorcado.
 * 
 * Responsable de manejar las peticiones del usuario (GET / POST),
 * interactuar con el servicio de aplicación (ServicioPartida)
 * y devolver los datos necesarios para renderizar la vista.
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
     * @return array Datos del estado actual del juego (palabra oculta, letras usadas, intentos, etc.).
     */
    public function handle(): array
    {
        session_start();

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

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['letra'])) {
            $letra = trim($_POST['letra']);
            $resultado = $this->servicioPartida->probarLetra($gameId, $letra);

            $mensaje = $resultado['mensaje'] ?? '';
            $palabraOculta = $resultado['palabra_oculta'] ?? '';
            $letrasUsadas = $resultado['letras_usadas'] ?? [];
            $intentosRestantes = $resultado['intentos_restantes'] ?? 0;
        } else {
            $estado = $this->servicioPartida->obtenerEstado($gameId);
            $palabraOculta = $estado['palabra_oculta'] ?? '';
            $letrasUsadas = $estado['letras_usadas'] ?? [];
            $intentosRestantes = $estado['intentos_restantes'] ?? 0;
        }

        $estadoJuego = $this->servicioPartida->obtenerEstado($gameId);
        $isWon = $estadoJuego['ganado'] ?? false;
        $isLost = $estadoJuego['perdido'] ?? false;

        if ($isWon) {
            $bodyState = 'won';
            $mensaje = '¡Has ganado! La palabra era: ' . $estadoJuego['palabra_real'];
        } elseif ($isLost) {
            $bodyState = 'lost';
            $mensaje = 'Has perdido. La palabra era: ' . $estadoJuego['palabra_real'];
        } else {
            $bodyState = 'playing';
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
