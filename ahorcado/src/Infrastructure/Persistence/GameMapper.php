<?php
declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Domain\Entity\Game;

final class GameMapper
{
    public static function toArray(Game $game): array
    {
        return [
            'id' => $game->getId(),
            'word' => $game->getWord(),
            'maxAttempts' => $game->getMaxAttempts(),
            'attemptsLeft' => $game->getAttemptsLeft(),
            'usedLetters' => $game->getUsedLetters(),
        ];
    }

    public static function fromArray(array $state): Game
    {
        $game = new Game(
            (string) $state['id'],
            (string) $state['word'],
            (int) $state['maxAttempts']
        );

        $reflection = new \ReflectionClass($game);

        $attemptsProperty = $reflection->getProperty('attemptsLeft');
        $attemptsProperty->setAccessible(true);
        $attemptsProperty->setValue($game, (int) $state['attemptsLeft']);

        $usedLettersProperty = $reflection->getProperty('usedLetters');
        $usedLettersProperty->setAccessible(true);
        $usedLettersProperty->setValue($game, $state['usedLetters'] ?? []);

        return $game;
    }
}
