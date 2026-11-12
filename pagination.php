<?php
// Беттерге бөлу (пагинация) көмекші функциялары.
// Қолдану: include "pagination.php"; renderPagination($page, $totalPages, $filters);

// Ағымдағы сүзгілерді сақтап, басқа бетке сілтеме жасау
function pageUrl($page, $filters) {
    $filters["page"] = $page;
    return "?" . http_build_query($filters);
}

// «← Алдыңғы  1 2 3  Келесі →» батырмаларын шығару
function renderPagination($page, $totalPages, $filters) {
    if ($totalPages <= 1) {
        return;
    }

    echo '<nav class="pagination">';

    if ($page > 1) {
        echo '<a href="' . htmlspecialchars(pageUrl($page - 1, $filters)) . '">← Алдыңғы</a>';
    } else {
        echo '<span class="disabled">← Алдыңғы</span>';
    }

    for ($i = 1; $i <= $totalPages; $i++) {
        if ($i === $page) {
            echo '<span class="current">' . $i . '</span>';
        } else {
            echo '<a href="' . htmlspecialchars(pageUrl($i, $filters)) . '">' . $i . '</a>';
        }
    }

    if ($page < $totalPages) {
        echo '<a href="' . htmlspecialchars(pageUrl($page + 1, $filters)) . '">Келесі →</a>';
    } else {
        echo '<span class="disabled">Келесі →</span>';
    }

    echo '</nav>';
}
