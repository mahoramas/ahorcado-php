<?php
declare(strict_types=1);

return [
    'storage' => [
        // Rutas absolutas para los archivos de persistencia JSON
        'words_file' => __DIR__ . '/../storage/words.json',
        'games_file' => __DIR__ . '/../storage/games.json',
    ],
    'game' => [
        // Número máximo de intentos para cada partida
        'max_attempts' => 6,
    ],
];