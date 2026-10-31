<?php
/**
 * Sidebar Services (replicated from app/Views/services/left.php)
 */
defined('ABSPATH') || exit;

$service_types = pccc24_get_service_types();
?>
<div class="border-tab"></div>
<div class="flex justify-between items-center">
    <div class="w-full">
        <label class="popular-label popular-label-icon w-full">
            <i class="bx bx-cog"></i>
            <span class="text">Dịch vụ</span>
        </label>
    </div>
</div>
<div class="mb-5 border p-2">
    <?php foreach ($service_types as $st) : ?>
        <a class="w-full flex gap-2 my-3" href="<?= esc_url($st->url) ?>">
            <span class="limit-line limit-line-4 text-base">
                <?= esc_html($st->name) ?>
            </span>
        </a>
    <?php endforeach; ?>
</div>
