<?php
declare(strict_types=1);

namespace App\Application\Services;

use App\Domain\Entity\Game;
use App\Domain\Repository\GameRepositoryInterface;
use App\Domain\Repository\WordRepositoryInterface;

/**
 * Servicio de aplicación encargado de coordinar las partidas del juego del ahorcado.
 * Separa la lógica de dominio (en la entidad Game) de la lógica de aplicación (coordinación y persistencia).
 */
final class ServicioPartida
{
    private GameRepositoryInterface $gameRepository;
    private WordRepositoryInterface $wordRepository;
    private int $maxAttempts;

    /**
     * @param GameRepositoryInterface $gameRepository Repositorio para guardar/recuperar partidas.
     * @param WordRepositoryInterface $wordRepository Repositorio de palabras disponibles.
     * @param int $maxAttempts Número máximo de intentos permitido (viene de config.php).
     */
    public function __construct(
        GameRepositoryInterface $gameRepository,
        WordRepositoryInterface $wordRepository,
        int $maxAttempts
    ) {
        $this->gameRepository = $gameRepository;
        $this->wordRepository = $wordRepository;
        $this->maxAttempts = $maxAttempts;
    }

    /**
     * Crea una nueva partida y la guarda en el repositorio.
     *
     * @return string El ID del nuevo juego creado.
     */
    public function crearNuevaPartida(): string
    {
        $id = uniqid('ahorcado_', true);
        $word = $this->wordRepository->randomWord();

        $game = new Game($id, $word, $this->maxAttempts);
        $this->gameRepository->save($game);

        return $game->getId();
    }


    /**
     * Procesa un intento de letra en una partida existente.
     *
     * @param string $idPartida ID de la partida.
     * @param string $letra Letra propuesta por el jugador.
     * @return array Estado actualizado del juego (para la capa de presentación).
     */
    public function probarLetra(string $idPartida, string $letra): array
    {
        $game = $this->gameRepository->find($idPartida);

        if (!$game) {
            return ['error' => 'Partida no encontrada.'];
        }

        $game->guessLetter($letra);
        $this->gameRepository->save($game);

        return $this->estadoComoArray($game);
    }

    /**
     * Obtiene el estado actual de una partida por su ID.
     *
     * @param string $idPartida ID de la partida.
     * @return array Estado actual o mensaje de error si no existe.
     */
    public function obtenerEstado(string $idPartida): array
    {
        $game = $this->gameRepository->find($idPartida);

        if (!$game) {
            return ['error' => 'Partida no encontrada.'];
        }

        return $this->estadoComoArray($game);
    }

    /**
     * Convierte la entidad Game en un array listo para mostrar o serializar.
     *
     * @param Game $game Entidad de dominio.
     * @return array Estado formateado para la vista.
     */
    private function estadoComoArray(Game $game): array
    {
        return [
            'id' => $game->getId(),
            'palabra_oculta' => $game->getMaskedWord(),
            'intentos_restantes' => $game->getAttemptsLeft(),
            'letras_usadas' => $game->getUsedLetters(),
            'estado' => $game->isWon()
                ? 'ganado'
                : ($game->isLost() ? 'perdido' : 'en curso'),
        ];
    }
}
