<?php

declare(strict_types=1);

it('tidak memakai dependensi terlarang', function () {
    $forbidden = [
        'redis',
        'docker',
        'minio',
        'puppeteer',
        'pg_partman',
        'npm run',
    ];

    $excluded = [
        'app/Console/Commands/DocsCheck.php', // memuat daftar istilah yang justru dilarang
    ];

    $hits = [];

    foreach (guardFiles(['app', 'config', 'scripts', 'routes']) as $path) {
        $relative = str_replace(base_path().'/', '', $path);

        if (in_array($relative, $excluded, true)) {
            continue;
        }

        $content = (string) file_get_contents($path);

        foreach ($forbidden as $term) {
            if (stripos($content, $term) !== false) {
                $hits[] = $relative.' → '.$term;
            }
        }
    }

    expect($hits)->toBe([]);
});

it('composer.json tidak menarik paket redis/minio', function () {
    $composer = (string) file_get_contents(base_path('composer.json'));

    expect(stripos($composer, 'predis'))->toBeFalse()
        ->and(stripos($composer, 'redis'))->toBeFalse()
        ->and(stripos($composer, 'league/flysystem-aws'))->toBeFalse();
});
