<?php

$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__ . '/Classes');

return (new PhpCsFixer\Config())
    ->setRules([
        '@Symfony' => true,
        'concat_space' => ['spacing' => 'one'],
        'global_namespace_import' => true,
        'yoda_style' => false,
    ])
    ->setIndent('  ')
    ->setFinder($finder);
