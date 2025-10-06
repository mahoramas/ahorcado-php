<?php

namespace backend;

class Renderer {
    public function ascii(int $attemptsLeft): string {
        $attemptsLeft = max(0, min(6, $attemptsLeft));
        $stages = [
            6 => "
  +---+
  |   |
      |
      |
      |
      |
========= ",
            5 => "
  +---+
  |   |
  O   |
      |
      |
      |
========= ",
            4 => "
  +---+
  |   |
  O   |
  |   |
      |
      |
========= ",
            3 => "
  +---+
  |   |
  O   |
 /|   |
      |
      |
========= ",
            2 => "
  +---+
  |   |
  O   |
 /|\  |
      |
      |
========= ",
            1 => "
  +---+
  |   |
  O   |
 /|\  |
 /    |
      |
========= ",
            0 => "
  +---+
  |   |
  O   |
 /|\  |
 / \  |
      |
========= ",
        ];

        return '<pre>' . htmlspecialchars($stages[$attemptsLeft], ENT_QUOTES, 'UTF-8') . '</pre>';
    }
}
