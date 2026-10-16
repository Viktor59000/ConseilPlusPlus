<?php
/**
 * Boucle de stylo rouge (le geste du logo), à poser autour d'un chiffre ou d'un mot : <span class="entoure">…<?= boucle_stylo() ?></span>.
 * Elle se trace quand l'élément apparaît à l'écran (js/site.js, classes .a-entourer / .entoure-anime).
 */
function boucle_stylo(): string
{
    return '<svg viewBox="0 0 100 100" preserveAspectRatio="none" aria-hidden="true" focusable="false">'
        . '<path pathLength="1" vector-effect="non-scaling-stroke" d="M14.4 77.6C13.4 76.1 10.1 72.0 8.5 68.9C7.0 65.8 5.9 62.4 5.2 59.1C4.6 55.7 4.4 52.2 4.6 48.8C4.9 45.3 5.7 41.8 6.9 38.6C8.0 35.3 9.7 32.1 11.7 29.2C13.7 26.3 16.1 23.5 18.8 21.1C21.5 18.7 24.6 16.6 27.8 14.9C31.0 13.1 34.6 11.7 38.1 10.8C41.6 9.8 45.4 9.3 49.1 9.1C52.7 9.0 56.5 9.3 60.0 10.0C63.5 10.7 67.0 11.8 70.2 13.3C73.4 14.8 76.5 16.7 79.2 18.8C81.9 21.0 84.3 23.5 86.3 26.2C88.3 28.9 89.9 31.9 91.1 35.0C92.3 38.0 93.1 41.4 93.4 44.6C93.8 47.9 93.6 51.3 93.1 54.5C92.5 57.8 91.5 61.1 90.1 64.1C88.7 67.1 86.8 70.1 84.7 72.7C82.5 75.4 79.9 77.8 77.2 79.9C74.4 82.0 71.3 83.8 68.0 85.2C64.8 86.6 61.4 87.7 57.9 88.4C54.5 89.0 50.8 89.3 47.3 89.1C43.9 89.0 40.3 88.4 37.0 87.5C33.7 86.6 30.5 85.2 27.6 83.6C24.6 82.0 21.9 79.9 19.5 77.7C17.2 75.4 15.1 72.8 13.4 70.1C11.8 67.4 10.5 64.4 9.6 61.3C8.7 58.3 8.5 53.5 8.3 52.0"/></svg>';
}
