<?php

declare(strict_types=1);

/**
 * Lista simple de preguntas tipo quiz para verificar fanatismo Pokemon.
 * Cada entrada tiene: id, question, options (array), answer (text exacto).
 */
$QUIZ_QUESTIONS = [
    [
        'id' => 1,
        'question' => '¿Cuál es la evolución final de Charmander?',
        'options' => ['Charmeleon', 'Charizard', 'Charmander', 'Pidgeot'],
        'answer' => 'Charizard',
    ],
    [
        'id' => 2,
        'question' => '¿Qué tipo es Pikachu?',
        'options' => ['Agua', 'Planta', 'Eléctrico', 'Fuego'],
        'answer' => 'Eléctrico',
    ],
    [
        'id' => 3,
        'question' => '¿Cuál de estos Pokemon es de tipo psíquico? ',
        'options' => ['Machop', 'Abra', 'Totodile', 'Geodude'],
        'answer' => 'Abra',
    ],
    [
        'id' => 4,
        'question' => '¿Cómo se llama la forma inicial de Bulbasaur?',
        'options' => ['Ivysaur', 'Bulbasaur', 'Venusaur', 'Bellsprout'],
        'answer' => 'Bulbasaur',
    ],
    [
        'id' => 5,
        'question' => '¿Qué Pokemon legendario está asociado con el rayo en la primera generación?',
        'options' => ['Zapdos', 'Moltres', 'Articuno', 'Raikou'],
        'answer' => 'Zapdos',
    ],
    [
        'id' => 6,
        'question' => '¿Cuál es la evolución previa de Raichu?',
        'options' => ['Pichu', 'Pikachu', 'Plusle', 'Minun'],
        'answer' => 'Pikachu',
    ],
];

return $QUIZ_QUESTIONS;
