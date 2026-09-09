<?php

function layout_top() {
    include __DIR__ . '/header.php';
    include __DIR__ . '/sidebar.php';
    echo '<div class="col-md-10 min-vh-100 p-3 p-md-4">';
}

function layout_bottom() {
    echo '</div>';
    include __DIR__ . '/footer.php';
}
