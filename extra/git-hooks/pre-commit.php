#!/usr/bin/php
<?php

// Warning! This file is updated automatically.
// If you change it, your changes may be lost in the future.
// 
// - To have your own pre-commit file, create a separate file called "pre-commit-inc"
// - To completely override this mechanism, create a normal pre-commit file without
//   this comment.

$pcfp = 'vendor'.DIRECTORY_SEPARATOR.'bin'.DIRECTORY_SEPARATOR.'php-cs-fixer';
exec('git diff --cached --name-status --diff-filter=ACM', $output);
$c = count($output);
$l = strlen((string) $c);

foreach ($output as $i => $file) {
    $fileName = trim(substr($file, 1));

    if (substr($fileName, -4) == '.php') {
        $lint_output = [];
        exec('php -l '.escapeshellarg($fileName), $lint_output, $return);

        if ($return == 0) {
            echo '['.str_pad($i + 1, $l, ' ', STR_PAD_LEFT).'/'.$c.'] '.$fileName.' .';
            exec($pcfp.' fix '.escapeshellarg($fileName));
            echo '.';
            exec('git add '.escapeshellarg($fileName));
            echo '.'.PHP_EOL;
        } else {
            echo implode(PHP_EOL, $lint_output), PHP_EOL;
            exit(1);
        }
    }
}

if (file_exists('pre-commit-inc')) {
    passthru('pre-commit-inc');
}

exit(0);
