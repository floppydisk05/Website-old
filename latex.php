<?php
function parseLatex($expression) {
    $expression = str_replace(' ', '%20', $expression);
    echo "<img src=\"https://latex.codecogs.com/png.image?$expression\">";
}

parseLatex('\frac{5}{2} = 2.5');
?>
