<?php
declare(strict_types=1);

require __DIR__ . '/../src/Infrastructure/Autoload/Autoloader.php';
\App\Infrastructure\Autoload\Autoloader::register('App\\', __DIR__ . '/../src');

use App\Application\Services\ServicioPartida;
use App\Infrastructure\Persistence\JsonGameRepository;
use App\Infrastructure\Persistence\JsonWordRepository;
use App\Presentation\Controllers\GameController;
use App\Presentation\Views\Renderer;

$config = require __DIR__ . '/../config/config.php';
$gamesPath   = $config['storage']['games_file'];
$wordsPath   = $config['storage']['words_file'];
$maxAttempts = (int)$config['game']['max_attempts'];

$gameRepository = new JsonGameRepository($gamesPath);
$wordRepository = new JsonWordRepository($wordsPath);
$servicioPartida = new ServicioPartida($gameRepository, $wordRepository, $maxAttempts);

$controller = new GameController($servicioPartida);
$responseData = $controller->handle();

$renderer = new Renderer();

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
</head>
<body class="<?= htmlspecialchars($bodyState, ENT_QUOTES, 'UTF-8') ?>">
<div class="background"></div>
<main class="app">
<header class="app__header">
    <div class="theme-toggle-wrapper">
        <label class="theme-toggle">
            🌞
            <input type="checkbox" id="themeSwitch">
            🌙
        </label>
    </div>
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
<script>
    const themeSwitch = document.getElementById('themeSwitch');
    const body = document.body;

    if (localStorage.getItem('theme') === 'dark') {
        body.classList.add('dark');
        themeSwitch.checked = true;
    }

    themeSwitch.addEventListener('change', () => {
        if (themeSwitch.checked) {
            body.classList.add('dark');
            localStorage.setItem('theme', 'dark');
        } else {
            body.classList.remove('dark');
            localStorage.setItem('theme', 'light');
        }
    });
</script>
</body>
</html>
