<?php
/** Sidebar dịch vụ. */
defined('ABSPATH') || exit;
?>
<div class="side-block">
    <h2>Dịch vụ</h2>
    <ul class="side-links">
        <?php foreach (pccc24_get_service_types() as $st) : ?>
            <li><a href="<?= esc_url($st->url) ?>"><?= esc_html($st->name) ?></a></li>
        <?php endforeach; ?>
    </ul>
</div>
<?php get_template_part('template-parts/quote-tag', null, ['title' => 'Khảo sát miễn phí', 'text' => 'Kỹ thuật viên đến tận nơi khảo sát và lập phương án PCCC theo quy định hiện hành.']); ?>
