<?php

return Symfony\CS\Config\Config::create()
    ->level(Symfony\CS\FixerInterface::SYMFONY_LEVEL)
    ->fixers([
        '-concat_without_spaces',
        'concat_with_spaces',
        'phpdoc_order',
        'short_array_syntax',
    ])
    ->finder(
        Symfony\CS\Finder\DefaultFinder::create()
            ->exclude(['var', 'vendor'])
            ->in(__DIR__)
    )
;
