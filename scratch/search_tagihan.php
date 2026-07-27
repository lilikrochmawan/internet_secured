<?php
function searchDir($dir, $patterns) {
    $it = new RecursiveDirectoryIterator($dir);
    foreach (new RecursiveIteratorIterator($it) as $file) {
        if ($file->isDir()) continue;
        if (strpos($file->getPathname(), 'vendor') !== false) continue;
        if (strpos($file->getPathname(), 'node_modules') !== false) continue;
        if (pathinfo($file->getPathname(), PATHINFO_EXTENSION) !== 'php') continue;
        
        $content = file_get_contents($file->getPathname());
        foreach ($patterns as $pattern) {
            if (strpos($content, $pattern) !== false) {
                echo "Found pattern '{$pattern}' in: " . $file->getPathname() . "\n";
                $lines = explode("\n", $content);
                foreach ($lines as $i => $line) {
                    if (strpos($line, $pattern) !== false) {
                        echo "  Line " . ($i + 1) . ": " . trim($line) . "\n";
                    }
                }
            }
        }
    }
}

searchDir('.', ['tb_tagihan', 'GenerateBulananTagihan', 'generate-bulanan-tagihan', 'promo']);
