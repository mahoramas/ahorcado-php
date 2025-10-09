<?php
declare(strict_types=1);

namespace App\Presentation\Views;

/**
 * Renderer visual del ahorcado — reemplaza el ASCII por SVG.
 */
final class Renderer
{
    /**
     * Devuelve el SVG del monigote según los intentos restantes.
     *
     * @param int $attemptsLeft
     * @return string SVG listo para insertar en la vista.
     */
    public function ascii(int $attemptsLeft): string
    {
        $parts = 6; // número total de partes del cuerpo
        $failed = 6 - $attemptsLeft;
        $failed = max(0, min($failed, $parts));

        $bodyParts = [
            '<circle cx="150" cy="60" r="20" class="hangman-head" />',                
            '<line x1="150" y1="80" x2="150" y2="130" class="hangman-body" />',       
            '<line x1="150" y1="90" x2="130" y2="120" class="hangman-arm-left" />',   
            '<line x1="150" y1="90" x2="170" y2="120" class="hangman-arm-right" />',  
            '<line x1="150" y1="130" x2="130" y2="160" class="hangman-leg-left" />',  
            '<line x1="150" y1="130" x2="170" y2="160" class="hangman-leg-right" />', 
        ];

        $visibleParts = array_slice($bodyParts, 0, $failed);

        return '
        <svg viewBox="0 0 200 200" class="hangman-svg" xmlns="http://www.w3.org/2000/svg">
            <!-- Estructura -->
            <line x1="20" y1="180" x2="180" y2="180" class="gallows-base" />
            <line x1="60" y1="20" x2="60" y2="180" class="gallows-pole" />
            <line x1="60" y1="20" x2="150" y2="20" class="gallows-top" />
            <line x1="150" y1="20" x2="150" y2="40" class="gallows-rope" />

            <!-- Partes del cuerpo -->
            ' . implode("\n", $visibleParts) . '
        </svg>';
    }
}
