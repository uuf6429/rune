<?php

return Symfony\CS\Config\Config::create()
    ->level(Symfony\CS\FixerInterface::SYMFONY_LEVEL)
    ->fixers([
        'array_element_no_space_before_comma',
        'array_element_white_space_after_comma',
        'blankline_after_open_tag',
        'concat_with_spaces',
        'lowercase_cast',
        'multiline_array_trailing_comma',
        'namespace_no_leading_whitespace',
        'ordered_use',
        'phpdoc_order',
        'remove_leading_slash_use',
        'remove_lines_between_uses',
        'return',
        'short_array_syntax',
        'single_quote',
        'standardize_not_equal',
        'ternary_spaces',
        'unused_use',
    ])
    ->finder(
        Symfony\CS\Finder\DefaultFinder::create()
            ->exclude(['var', 'vendor'])
            ->in(__DIR__)
    )
;
