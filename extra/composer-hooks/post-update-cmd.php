<?php

$file_head = 'Warning! This file is updated automatically.';
$file_srsc = 'extra/git-hooks/pre-commit.php';
$file_trgt = '.git/hooks/pre-commit';

$trgt_exists = file_exists($file_trgt);
$can_install = !$trgt_exists || strpos(file_get_contents($file_trgt), $file_head) !== false;

if ($can_install) {
    if ($trgt_exists && (file_get_contents($file_srsc) == file_get_contents($file_trgt))) {
        echo 'Pre-commit hook already up-to-date.' . PHP_EOL;
    } else {
        echo 'Installing pre-commit hook...' . PHP_EOL;

        if ($trgt_exists) {
            unlink($file_trgt);
        }
        copy($file_srsc, $file_trgt);
        chmod($file_trgt, 0777);

        echo '... DONE' . PHP_EOL;
    }
} else {
    echo 'Pre-commit hook NOT installed.' . PHP_EOL;
}
