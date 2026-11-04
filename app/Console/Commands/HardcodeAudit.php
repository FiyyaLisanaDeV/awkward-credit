<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use PhpParser\Node;
use PhpParser\Node\Scalar\Float_;
use PhpParser\Node\Scalar\Int_;
use PhpParser\Node\Scalar\String_;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;
use PhpParser\ParserFactory;
use Throwable;

final class HardcodeAudit extends Command
{
    /** @var list<int|float> */
    public const ALLOWED_NUMBERS = [0, 1, 2, -1, 200, 201, 204, 400, 401, 403, 404, 409, 422, 429, 500, 503];

    /** @var list<string> */
    public const DENYLIST = [
        'TPP',
        'MODAL_KERJA',
        'GRACE_PERIOD',
        'Flat',
        'Anuitas',
        'Grace Period',
        'Layak',
        'TIDAK LAYAK',
        'KDI',
        'ChangeMe',
        'BPR Bahteramas',
    ];

    protected $signature = 'hardcode:audit {--path=* : Path relatif yang dipindai}';

    protected $description = 'AST scan literal angka/string bisnis di luar sumber yang sah';

    public function handle(): int
    {
        $paths = $this->option('path');

        if ($paths === []) {
            $paths = ['app/Services', 'app/Http', 'app/Livewire', 'app/Support', 'app/Domain'];
        }

        $findings = [];
        $parser = (new ParserFactory)->createForNewestSupportedVersion();

        foreach ($paths as $root) {
            $directory = base_path((string) $root);

            if (! is_dir($directory)) {
                continue;
            }

            $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($directory));

            foreach ($iterator as $file) {
                if (! $file->isFile() || $file->getExtension() !== 'php') {
                    continue;
                }

                try {
                    $ast = $parser->parse((string) file_get_contents($file->getPathname()));
                } catch (Throwable) {
                    continue;
                }

                if ($ast === null) {
                    continue;
                }

                $relative = str_replace(base_path().'/', '', $file->getPathname());

                $visitor = new class($relative) extends NodeVisitorAbstract
                {
                    /** @var list<array{0: string, 1: string, 2: string}> */
                    public array $findings = [];

                    public function __construct(private string $file) {}

                    public function enterNode(Node $node): ?Node
                    {
                        if ($node instanceof Int_ || $node instanceof Float_) {
                            $value = $node->value;

                            if (! in_array($value, HardcodeAudit::ALLOWED_NUMBERS, true)) {
                                $this->findings[] = [$this->file.':'.$node->getStartLine(), 'angka', (string) $value];
                            }

                            return null;
                        }

                        if ($node instanceof String_ && in_array($node->value, HardcodeAudit::DENYLIST, true)) {
                            $this->findings[] = [$this->file.':'.$node->getStartLine(), 'string', $node->value];
                        }

                        return null;
                    }
                };

                $traverser = new NodeTraverser;
                $traverser->addVisitor($visitor);
                $traverser->traverse($ast);

                $findings = array_merge($findings, $visitor->findings);
            }
        }

        if ($findings === []) {
            $this->info('hardcode:audit — 0 pelanggaran');

            return self::SUCCESS;
        }

        $this->table(['lokasi', 'jenis', 'nilai'], $findings);
        $this->error(sprintf('hardcode:audit — %d pelanggaran', count($findings)));

        return self::FAILURE;
    }
}
