<?php
// php scripts/zip-dir.php <thư-mục> <file.zip>
// Nén giữ tên file tiếng Việt dạng UTF-8 (tar.exe/Compress-Archive của Windows ghi theo codepage ANSI -> "b?nh-ch?a-ch?y.png" 404 trên hosting Linux).
[, $dir, $out] = $argv;
$dir = rtrim(realpath($dir), '\\/');
$zip = new ZipArchive();
if ($zip->open($out, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
    exit("Không tạo được $out\n");
}
$it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::SELF_FIRST);
foreach ($it as $f) {
    $name = str_replace('\\', '/', substr($f->getPathname(), strlen($dir) + 1));
    if ($f->isDir()) {
        $zip->addEmptyDir($name);
        continue;
    }
    $zip->addFile($f->getPathname(), $name);
    if (preg_match('/\.(jpe?g|png|webp|gif|zip|gz)$/i', $name)) {
        $zip->setCompressionName($name, ZipArchive::CM_STORE); // ảnh đã nén sẵn
    }
}
$n = $zip->numFiles;
if (!$zip->close()) {
    exit("Lỗi ghi $out\n");
}
echo "$n mục -> $out\n";
