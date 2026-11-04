<?php

declare(strict_types=1);

/**
 * @return list<string>
 */
function guardFiles(array $directories): array
{
    $files = [];

    foreach ($directories as $directory) {
        $path = base_path($directory);

        if (! is_dir($path)) {
            continue;
        }

        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path));

        foreach ($iterator as $file) {
            if ($file->isFile() && in_array($file->getExtension(), ['php', 'sh'], true)) {
                $files[] = $file->getPathname();
            }
        }
    }

    return $files;
}

it('tidak menyimpan kredensial di berkas aplikasi', function () {
    $patterns = [
        '/APP_KEY=base64:[A-Za-z0-9+\/=]{20,}/',
        '/ChangeMe/',
        '/\bpassword\s*=>\s*[\'"][^\'"\s]{3,}[\'"]/i',
        '/\b(secret|token)\s*=>\s*[\'"][^\'"\s]{8,}[\'"]/i',
    ];

    $excluded = [
        'app/Console/Commands/HardcodeAudit.php', // memuat daftar istilah yang diaudit
    ];

    $hits = [];

    foreach (guardFiles(['app', 'config', 'scripts', 'routes']) as $path) {
        if (in_array(str_replace(base_path().'/', '', $path), $excluded, true)) {
            continue;
        }

        $content = (string) file_get_contents($path);

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $content) === 1) {
                $hits[] = str_replace(base_path().'/', '', $path).' → '.$pattern;
            }
        }
    }

    expect($hits)->toBe([]);
});

it('tidak mem-commit berkas .env', function () {
    expect((string) file_get_contents(base_path('.gitignore')))->toContain('.env');
});
