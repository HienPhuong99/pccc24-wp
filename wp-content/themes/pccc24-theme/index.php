<?php
/**
 * Fallback Index Template
 */

defined('ABSPATH') || exit;

if (is_front_page()) {
    require __DIR__ . '/front-page.php';
    return;
}

require __DIR__ . '/articles.php';
