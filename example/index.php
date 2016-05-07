<?php

namespace uuf6429\Rune\example;

require_once __DIR__.'/../vendor/autoload.php';

use uuf6429\Rune;

// This check prevents access to demo on live systems if uploaded by mistake.
// Shamelessly copied from silex-skeleton
if (!defined('SHOW_EXAMPLE') && (isset($_SERVER['HTTP_CLIENT_IP'])
    || isset($_SERVER['HTTP_X_FORWARDED_FOR'])
    || !in_array(@$_SERVER['REMOTE_ADDR'], ['127.0.0.1', 'fe80::1', '::1'])
)) {
    header('HTTP/1.0 403 Forbidden');
    exit('You are not allowed to access this file. Check <code>example/index.php</code> for more information.');
}

define('APP_ROOT', '/');

// serve static files
if (in_array($_SERVER['SCRIPT_NAME'], [
    APP_ROOT.'extra/codemirror/rune.js',
    APP_ROOT.'extra/codemirror/rune.css',
])) {
    return false;
}

// load simple example
if (in_array($_SERVER['SCRIPT_NAME'], [
    APP_ROOT.'simple',
    APP_ROOT.'simple.php',
])) {
    return require_once 'simple.php';
}

// load default data and override it with $_POST data (do some cleanup here)
$data = array_merge(
    require __DIR__.'/data.php',
    array_map(
        function ($group) {
            return !is_array($group) ? $group : array_filter(
                array_values($group),
                function ($data) {
                    return array_filter($data);
                }
            );
        },
        $_POST
    )
);

$failureMode = $data['failureMode'];

$rules = array_map(
    function ($index, $data) {
        return new Rune\Rule\GenericRule($index + 1, $data[0], $data[1]);
    },
    array_keys($data['rules']),
    $data['rules']
);

$categories = [];

$categoryProvider = function ($id) use (&$categories) {
    return $id ? $categories[$id - 1] : null;
};

$categories = array_map(
    function ($index, $data) use ($categoryProvider) {
        return new Model\Category($index + 1, $data[0], $data[1], $categoryProvider);
    },
    array_keys($data['categories']),
    $data['categories']
);

$products = array_map(
    function ($index, $data) use ($categoryProvider) {
        return new Model\Product($index + 1, $data[0], $data[1], $data[2], $categoryProvider);
    },
    array_keys($data['products']),
    $data['products']
);

$errors = [];
$engine = new Rune\Engine();
$action = new Action\PrintAction();

ob_start();
foreach ($products as $product) {
    $context = new Context\ProductContext($product);
    $engine->execute($context, $rules, $action, $data['failureMode']);
    $errors += $engine->getErrors();
}
$output_result = htmlspecialchars(ob_get_clean(), ENT_QUOTES);
$output_errors = count($errors) ? implode(PHP_EOL, $errors) : '<i>None</i>';

$context = new Context\ProductContext();
$descriptor = $context->getContextDescriptor();

$json_tokens = json_encode([
    'constants' => [
        [
            'name' => 'true',
            'type' => 'boolean',
        ],
        [
            'name' => 'false',
            'type' => 'boolean',
        ],
        [
            'name' => 'null',
            'type' => 'null',
        ],
    ],
    'operators' => [
        '+', '-', '*', '/', '%', '**',                              // arithmetic
        '&', '|', '^',                                              // bitwise
        '==', '===', '!=', '!==', '<', '>', '<=', '>=', 'matches',  // comparison
        'not', '!', 'and', '&&', 'or', '||',                        // logical
        '~',                                                        // concatentation
        'in', 'not in',                                             // array
        '..',                                                       // range
        '?', '?:', ':',                                             // ternary
    ],
    'variables' => array_values($descriptor->getVariableTypeInfo()),
    'functions' => array_values($descriptor->getFunctionTypeInfo()),
    'typeinfo' => $descriptor->getDetailedTypeInfo(),
]);
$json_categories = json_encode($data['categories']);
$json_products = json_encode($data['products']);
$json_rules = json_encode($data['rules']);

require_once 'view.php';
