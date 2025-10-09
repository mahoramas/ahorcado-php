<?php
declare(strict_types=1);

require __DIR__ . '/../src/Infrastructure/Autoload/Autoloader.php';
\App\Infrastructure\Autoload\Autoloader::register('App\\', __DIR__ . '/../src');

use App\Application\Services\ServicioPartida;
use App\Infrastructure\Persistence\JsonGameRepository;
use App\Infrastructure\Persistence\JsonWordRepository;
use App\Presentation\Controllers\GameController;
use App\Presentation\Views\Renderer;

// Cargar configuración
$config = require __DIR__ . '/../config/config.php';
$gamesPath   = $config['storage']['games_file'];
$wordsPath   = $config['storage']['words_file'];
$maxAttempts = (int)$config['game']['max_attempts'];

// Inicializar repositorios y servicio
$gameRepository = new JsonGameRepository($gamesPath);
$wordRepository = new JsonWordRepository($wordsPath);
$servicioPartida = new ServicioPartida($gameRepository, $wordRepository, $maxAttempts);

// Crear controlador y procesar solicitud
$controller = new GameController($servicioPartida);
$responseData = $controller->handle();

// Crear renderer
$renderer = new Renderer();

// Variables de presentación
$maskedWordDisplay = $responseData['palabra_oculta'] ?? '';
$attemptsLeft      = $responseData['intentos_restantes'] ?? $maxAttempts;
$usedLetters       = $responseData['letras_usadas'] ?? [];
$message           = $responseData['mensaje'] ?? '';
$bodyState         = $responseData['estado'] ?? 'playing';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ahorcado en PHP</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .word-display .value {
            letter-spacing: 0.4em;
            font-family: monospace;
            font-size: 1.4em;
        }
        .ascii-art pre {
            font-family: monospace;
            white-space: pre;
            line-height: 1.1;
            margin: 0;
        }
        .reset-button {
            display: inline-block;
            margin-top: 1.2em;
            padding: 0.7em 1.3em;
            font-size: 1em;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: background 0.3s ease;
            text-decoration: none;
        }
        .reset-button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body class="<?= htmlspecialchars($bodyState, ENT_QUOTES, 'UTF-8') ?>">
<div class="background"></div>
<main class="app">
    <header class="app__header">
        <h1>Juego del Ahorcado</h1>
        <p class="app__subtitle">Adivina la palabra antes de que se complete la figura.</p>
    </header>

    <section class="game">
        <div class="game__visual">
            <div class="hangman-card">
                <?= $renderer->ascii($attemptsLeft) ?>
                <span class="attempts-badge">
                    Intentos restantes: <strong><?= $attemptsLeft ?></strong>
                </span>
            </div>
        </div>

        <div class="game__panel">
            <div class="word-display" aria-live="polite">
                <span class="label">Palabra</span>
                <span class="value">
                    <?= htmlspecialchars(implode(' ', str_split($maskedWordDisplay)), ENT_QUOTES, 'UTF-8') ?>
                </span>
            </div>

            <div class="used-letters" aria-live="polite">
                <span class="label">Letras usadas</span>
                <div class="letters">
                    <?php if (empty($usedLetters)): ?>
                        <span class="letters__placeholder">Aún no has probado ninguna letra.</span>
                    <?php else: ?>
                        <?php foreach ($usedLetters as $letter): ?>
                            <span class="chip"><?= htmlspecialchars($letter, ENT_QUOTES, 'UTF-8') ?></span>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
            <?php if ($bodyState === 'playing'): ?>
                <form class="guess-form" method="post">
                    <label for="letra" class="label">Introduce una letra</label>
                    <div class="guess-form__controls">
                        <input type="text" id="letra" name="letra" maxlength="1" autocomplete="off" required>
                        <button type="submit">Adivinar</button>
                    </div>
                </form>
            <?php else: ?>
                <div class="result-banner" role="status">
                    <strong><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></strong>
                </div>
                <a class="reset-button" href="reset.php">Jugar de nuevo</a>
            <?php endif; ?>
        </div>
    </section>
</main>
</body>
</html>
