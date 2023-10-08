<?php

return (new PhpCsFixer\Config())
    ->setCacheFile(__DIR__ . '/.php_cs.cache')
    ->setRules([
        '@PSR12' => true,
        'array_syntax' => ['syntax' => 'short'],
        'blank_line_after_opening_tag' => true,
        'concat_space' => ['spacing' => 'one'],
        'no_null_property_initialization' => true,
        'ordered_imports' => ['sort_algorithm' => 'alpha'],
        'protected_to_private' => false,
        'yoda_style' => false,
    ])
    ->setFinder(
        PhpCsFixer\Finder::create()
            ->exclude(['vendor'])
            ->in(__DIR__)
    );
