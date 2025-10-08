<?php
namespace App\Application;

use App\Domain\Repository\GameRepositoryInterface;
use App\Domain\Entity\Game;

class ServicioPartida
{
    private GameRepositoryInterface $repository;

    public function __construct(GameRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function crearNuevaPartida(string $palabra): string
    {
        $id = uniqid('ahorcado_', true);
        $game = new Game($id, $palabra, [], 0);
        $this->repository->save($game);
        return $id;
    }

    public function probarLetra(string $idPartida, string $letra): array
    {
        $game = $this->repository->find($idPartida);
        if (!$game) {
            return ['error' => 'Partida no encontrada'];
        }

        // Obtener las letras usadas
        $letrasUsadas = $game->getLetrasUsadas();

        // Ver si la letra ya fue utilizada
        if (in_array($letra, $letrasUsadas)) {
            return [
                'mensaje' => 'Letra ya utilizada',
                'estado' => $this->obtenerEstadoDesdeGame($game)
            ];
        }

        // Añadir la letra a las usadas
        $letrasUsadas[] = $letra;

        // Actualizar el objeto Game
        $game->setLetrasUsadas($letrasUsadas);

        // Ver si la letra está en la palabra
        if (strpos($game->getPalabra(), $letra) !== false) {
            $mensaje = '¡Letra correcta!';
        } else {
            $game->setErrores($game->getErrores() + 1);
            $mensaje = 'Letra incorrecta';
        }

        // Guardar la partida actualizada
        $this->repository->save($game);

        // Devolver estado actualizado
        return [
            'mensaje' => $mensaje,
            'errores' => $game->getErrores(),
            'letras_usadas' => $game->getLetrasUsadas()
        ];
    }

    public function obtenerEstado(string $idPartida): array
    {
        $game = $this->repository->find($idPartida);
        if (!$game) {
            return ['error' => 'Partida no encontrada'];
        }

        // Crear la representación visual de la palabra oculta
        $palabra = $game->getPalabra();
        $letrasUsadas = $game->getLetrasUsadas();

        $palabraOculta = '';
        foreach (str_split($palabra) as $char) {
            if (in_array($char, $letrasUsadas)) {
                $palabraOculta .= $char . ' ';
            } else {
                $palabraOculta .= '_ ';
            }
        }

        return [
            'palabra_oculta' => trim($palabraOculta),
            'errores' => $game->getErrores(),
            'letras_usadas' => $letrasUsadas
        ];
    }

    // Método auxiliar para convertir un objeto Game en array de estado
    private function obtenerEstadoDesdeGame(Game $game): array
    {
        $palabra = $game->getPalabra();
        $letrasUsadas = $game->getLetrasUsadas();

        $palabraOculta = '';
        foreach (str_split($palabra) as $char) {
            if (in_array($char, $letrasUsadas)) {
                $palabraOculta .= $char . ' ';
            } else {
                $palabraOculta .= '_ ';
            }
        }

        return [
            'palabra_oculta' => trim($palabraOculta),
            'errores' => $game->getErrores(),
            'letras_usadas' => $letrasUsadas
        ];
    }
}