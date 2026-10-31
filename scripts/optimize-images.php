<?php
/**
 * Chặng A: Sinh bản ảnh nhẹ WebP (-card.webp 360px, -hero.webp 1100px, -detail.webp 760px)
 * cho toàn bộ ảnh đại diện của sản phẩm, slider, bài viết, dịch vụ, chính sách.
 */
defined('ABSPATH') || exit;

$posts = get_posts([
    'post_type'   => ['pccc_product', 'pccc_slider', 'pccc_policy', 'pccc_service', 'post'],
    'post_status' => 'publish',
    'numberposts' => -1,
]);

function pccc24_make_webp(string $src_path, string $dst_path, int $max_w, int $max_h, int $quality = 78): bool
{
    if (is_file($dst_path) && filesize($dst_path) > 100) {
        return true;
    }
    if (!is_file($src_path)) {
        return false;
    }
    $info = @getimagesize($src_path);
    if (!$info) {
        return false;
    }
    [$w, $h, $type] = $info;
    if ($w <= 0 || $h <= 0) {
        return false;
    }
    $im = match ($type) {
        IMAGETYPE_JPEG => @imagecreatefromjpeg($src_path),
        IMAGETYPE_PNG  => @imagecreatefrompng($src_path),
        IMAGETYPE_WEBP => @imagecreatefromwebp($src_path),
        IMAGETYPE_GIF  => @imagecreatefromgif($src_path),
        default        => false,
    };
    if (!$im) {
        return false;
    }

    $scale = min(1.0, $max_w / $w, $max_h / $h);
    $nw = max(1, (int) round($w * $scale));
    $nh = max(1, (int) round($h * $scale));

    $canvas = imagecreatetruecolor($nw, $nh);
    imagealphablending($canvas, false);
    imagesavealpha($canvas, true);
    $transparent = imagecolorallocatealpha($canvas, 255, 255, 255, 127);
    imagefilledrectangle($canvas, 0, 0, $nw, $nh, $transparent);
    imagecopyresampled($canvas, $im, 0, 0, 0, 0, $nw, $nh, $w, $h);

    $ok = @imagewebp($canvas, $dst_path, $quality);
    imagedestroy($im);
    imagedestroy($canvas);
    return (bool) $ok;
}

$done = 0;
$seen = [];
foreach ($posts as $p) {
    $thumb_id = (int) get_post_thumbnail_id($p->ID);
    if (!$thumb_id || isset($seen[$thumb_id . ':' . $p->post_type])) {
        continue;
    }
    $seen[$thumb_id . ':' . $p->post_type] = true;
    $file = get_attached_file($thumb_id);
    if (!$file || !is_file($file)) {
        continue;
    }
    $base = preg_replace('/\.[^.]+$/', '', $file);
    if ($p->post_type === 'pccc_slider') {
        if (pccc24_make_webp($file, $base . '-hero.webp', 1100, 480, 78)) {
            $done++;
        }
    } elseif ($p->post_type === 'pccc_policy') {
        if (pccc24_make_webp($file, $base . '-card.webp', 360, 270, 78)) {
            $done++;
        }
    } else {
        if (pccc24_make_webp($file, $base . '-card.webp', 360, 360, 76)) {
            $done++;
        }
        if ($p->post_type === 'pccc_product') {
            pccc24_make_webp($file, $base . '-detail.webp', 720, 720, 80);
        }
    }
}

WP_CLI::success("Generated/verified $done optimized WebP derivatives.");
