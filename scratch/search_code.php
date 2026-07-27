<?php
function searchDir($dir, $pattern) {
    $it = new RecursiveDirectoryIterator($dir);
    foreach (new RecursiveIteratorIterator($it) as $file) {
        if ($file->isDir()) continue;
        if (strpos($file->getPathname(), 'vendor') !== false) continue;
        if (strpos($file->getPathname(), 'node_modules') !== false) continue;
        if (pathinfo($file->getPathname(), PATHINFO_EXTENSION) !== 'php') continue;
        
        $content = file_get_contents($file->getPathname());
        if (strpos($content, $pattern) !== false) {
            echo "Found pattern '{$pattern}' in: " . $file->getPathname() . "\n";
            // Print matching lines
            $lines = explode("\n", $content);
            foreach ($lines as $i => $line) {
                if (strpos($line, $pattern) !== false) {
                    echo "  Line " . ($i + 1) . ": " . trim($line) . "\n";
                }
            }
        }
    }
}

echo "Search for /ppp/active/remove:\n";
searchDir('.', '/ppp/active/remove');

echo "\nSearch for active/remove:\n";
searchDir('.', 'active/remove');
