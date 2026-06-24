<?php
$dir = dirname(__FILE__) . '/../';
$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
$matches = [];

foreach ($files as $file) {
    if ($file->isDir()) continue;
    $path = $file->getRealPath();
    if (strpos($path, 'vendor') !== false || strpos($path, 'node_modules') !== false || strpos($path, 'scratch') !== false) {
        continue;
    }
    if (pathinfo($path, PATHINFO_EXTENSION) === 'php') {
        $content = file_get_contents($path);
        if (strpos($content, '/ppp/secret') !== false || strpos($content, 'RouterosAPI') !== false || strpos($content, '/ppp/active') !== false) {
            $matches[] = $path;
        }
    }
}

echo "Found matches in:\n";
foreach ($matches as $match) {
    echo "- " . str_replace($dir, '', $match) . "\n";
}
?>
