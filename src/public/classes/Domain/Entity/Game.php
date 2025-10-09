<?php
declare(strict_types=1);

namespace App\Domain\Entity;

final class Game
{
    private string $id;
    private string $word;
    private int $maxAttempts;
    private int $attemptsLeft;
    private array $usedLetters = [];

    public function __construct(string $id, string $word, int $maxAttempts)
    {
        $this->id = $id;
        $this->word = strtoupper($word);
        $this->maxAttempts = $maxAttempts;
        $this->attemptsLeft = $maxAttempts;
    }

    public function guessLetter(string $letter): void
    {
        if ($this->isWon() || $this->isLost()) {
            return;
        }

        $letter = $this->sanitizeLetter($letter);
        if ($letter === '' || in_array($letter, $this->usedLetters, true)) {
            return;
        }

        $this->usedLetters[] = $letter;

        if (strpos($this->word, $letter) === false) {
            $this->attemptsLeft = max(0, $this->attemptsLeft - 1);
        }
    }

    public function getMaskedWord(): string
    {
        return implode('', array_map(
            fn($char) => in_array($char, $this->usedLetters, true) ? $char : '_',
            str_split($this->word)
        ));
    }

    public function getAttemptsLeft(): int
    {
        return $this->attemptsLeft;
    }

    public function getUsedLetters(): array
    {
        return $this->usedLetters;
    }

    public function isWon(): bool
    {
        return $this->getMaskedWord() === $this->word;
    }

    public function isLost(): bool
    {
        return $this->attemptsLeft <= 0 && !$this->isWon();
    }

    public function getWord(): string
    {
        return $this->word;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getMaxAttempts(): int
    {
        return $this->maxAttempts;
    }

    private function sanitizeLetter(string $letter): string
    {
        $letter = strtoupper($letter);
        $letter = preg_replace('/[^A-Z]/', '', $letter);

        return $letter !== null ? substr($letter, 0, 1) : '';
    }
}
