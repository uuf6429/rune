<?php

return PhpCsFixer\Config::create()
    ->setCacheFile(__DIR__ . '/.php_cs.cache')
    ->setRules([
        '@Symfony' => true,
        'array_syntax' => ['syntax' => 'short'],
        'blank_line_after_opening_tag' => true,
        'no_blank_lines_after_class_opening' => true,
        'no_superfluous_phpdoc_tags' => false,
        'concat_space' => ['spacing' => 'one'],
        'no_null_property_initialization' => true,
        'ordered_imports' => ['sort_algorithm' => 'alpha'],
        'protected_to_private' => false,
        'yoda_style' => false,
        'phpdoc_align' => false,
        'single_line_throw' => false,
    ])
    ->setFinder(
        PhpCsFixer\Finder::create()
            ->exclude(['vendor'])
            ->in(__DIR__)
    );
