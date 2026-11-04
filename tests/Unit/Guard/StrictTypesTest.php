<?php

declare(strict_types=1);

it('semua berkas app mendeklarasikan strict_types', function () {
    $missing = [];

    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator(base_path('app'))
    );

    foreach ($iterator as $file) {
        if (! $file->isFile() || $file->getExtension() !== 'php') {
            continue;
        }

        $content = (string) file_get_contents($file->getPathname());

        if (! str_contains($content, 'declare(strict_types=1);')) {
            $missing[] = str_replace(base_path().'/', '', $file->getPathname());
        }
    }

    expect($missing)->toBe([]);
});
