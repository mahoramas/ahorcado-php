<?php
declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Domain\Entity\Game;
use App\Domain\Repository\GameRepositoryInterface;

final class JsonGameRepository implements GameRepositoryInterface
{
    public function __construct(private string $file)
    {
        if (!is_file($this->file)) {
            file_put_contents(
                $this->file,
                json_encode(['games' => []], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
            );
        }
    }

    public function save(Game $game): void
    {
        $data = $this->readAll();

        $data['games'][$game->getId()] = GameMapper::toArray($game);

        $this->writeAll($data);
    }

    public function find(string $id): ?Game
    {
        $data = $this->readAll();

        if (!isset($data['games'][$id])) {
            return null;
        }

        return GameMapper::fromArray($data['games'][$id]);
    }

    /**
     * Lee el JSON completo del archivo con bloqueo compartido
     */
    private function readAll(): array
    {
        $fh = fopen($this->file, 'c+');
        if ($fh === false) {
            throw new \RuntimeException('No se pudo abrir el fichero de juegos.');
        }

        try {
            flock($fh, LOCK_SH);
            $content = stream_get_contents($fh);
            $json = $content ? json_decode($content, true) : ['games' => []];

            return is_array($json) ? $json : ['games' => []];
        } finally {
            flock($fh, LOCK_UN);
            fclose($fh);
        }
    }

    /**
     * Escribe el JSON completo con escritura atómica y bloqueo exclusivo
     */
    private function writeAll(array $data): void
    {
        $tmp = $this->file . '.tmp';
        $fh = fopen($tmp, 'w');

        if ($fh === false) {
            throw new \RuntimeException('No se pudo crear el fichero temporal para guardar la partida.');
        }

        try {
            flock($fh, LOCK_EX);
            fwrite($fh, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            fflush($fh);
            flock($fh, LOCK_UN);
        } finally {
            fclose($fh);
        }

        rename($tmp, $this->file);
    }
}
