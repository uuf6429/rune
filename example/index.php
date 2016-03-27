<?php

// This check prevents access to debug front controllers that are deployed by accident to production servers.
// Feel free to remove this, extend it, or make something more sophisticated.
// Shamelessly copied from silex-skeleton
if (isset($_SERVER['HTTP_CLIENT_IP'])
    || isset($_SERVER['HTTP_X_FORWARDED_FOR'])
    || !in_array(@$_SERVER['REMOTE_ADDR'], array('127.0.0.1', 'fe80::1', '::1'))
) {
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

require_once __DIR__.'/../vendor/autoload.php';

// load default data and override it with $_POST data (do some cleanup here)
$data = array_merge(
    require __DIR__.'/data.php',
    array_map(
        function ($group) {
            return array_filter(
                array_values($group),
                function ($data) {
                    return array_filter($data);
                }
            );
        },
        $_POST
    )
);

use uuf6429\Rune;

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
        return new Rune\Example\Model\Category($index + 1, $data[0], $data[1], $categoryProvider);
    },
    array_keys($data['categories']),
    $data['categories']
);

$products = array_map(
    function ($index, $data) use ($categoryProvider) {
        return new Rune\Example\Model\Product($index + 1, $data[0], $data[1], $data[2], $categoryProvider);
    },
    array_keys($data['products']),
    $data['products']
);

?><!DOCTYPE html>
<html>
    <head>
        <title>Rule Engine Example</title>
        <!-- jQuery -->
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/2.2.2/jquery.min.js"></script>
        <!-- Twitter Bootstrap -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.6/css/bootstrap.min.css">
        <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.6/js/bootstrap.min.js"></script>
        <!-- CodeMirror -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.13.2/codemirror.css">
        <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.13.2/codemirror.min.js"></script>
        <!-- CodeMirror Simple Mode -->
        <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.13.2/addon/mode/simple.js"></script>
        <!-- CodeMirror Hints -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.13.2/addon/hint/show-hint.css">
        <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.13.2/addon/hint/show-hint.js"></script>
        <!-- Rune CodeMirror Support -->
        <link rel="stylesheet" href="<?php echo APP_ROOT; ?>../extra/codemirror/rune.css">
        <script src="<?php echo APP_ROOT; ?>../extra/codemirror/rune.js"></script>
    </head>
    <body>
        <div class="container">
            <h1>Rule Engine Shop Example</h1>
            
            <form action="<?php echo APP_ROOT; ?>#results" method="post">
                &nbsp;
                <div class="row">
                    <fieldset class="col-md-4">
                        <legend>Categories</legend>
                        <table class="table table-hover table-condensed" id="categories">
                            <thead>
                                <th>Name</th>
                                <th width="80px">Parent</th>
                            </thead>
                        </table>
                    </fieldset>

                    <fieldset class="col-md-8">
                        <legend>Products</legend>
                        <table class="table table-hover table-condensed" id="products">
                            <thead>
                                <th>Name</th>
                                <th>Colour</th>
                                <th width="80px">Category</th>
                            </thead>
                        </table>
                    </fieldset>
                </div>
                &nbsp;
                <div class="row">
                    <fieldset class="col-md-12">
                        <legend>Rules</legend>
                        <table class="table table-hover table-condensed" id="rules">
                            <thead>
                                <th width="30%">Name</th>
                                <th>Condition</th>
                            </thead>
                        </table>
                    </fieldset>
                </div>
                &nbsp;
                <div class="row text-center">
                    <input type="submit" class="btn btn-primary btn-lg" value="Execute"/>
                    <a class="btn btn-link" href="<?php echo APP_ROOT; ?>">Reset Changes</a>
                </div>
                &nbsp;
            </form>

            <fieldset id="results">
                <legend>Rule Engine Result</legend>
                <pre><?php

                    $action = new Rune\Example\Action\PrintAction();

                    $contexts = array_map(
                        function ($product) use ($action) {
                            return new Rune\Example\Context\ProductContext($action, $product);
                        },
                        $products
                    );

                    echo 'Result:'.PHP_EOL;
                    $engine = new Rune\Engine($contexts, $rules);
                    $engine->execute();

                    echo PHP_EOL.'Errors: '.PHP_EOL;
                    echo $engine->hasErrors() ? implode(PHP_EOL, $engine->getErrors()) : '<i>None</i>';

                ?></pre>
            </fieldset>
        </div>
            
        <script>
            $(document).ready(function(){
                var rowCounter = 0,
                // default rune editor settings
                    runeEditorOptions = {
                        tokens: <?php
                            $context = new Rune\Example\Context\ProductContext();
                            echo json_encode([
                                'operators' => [
                                    '+', '-', '*', '/', '%', '**',                              // arithmetic
                                    '&', '|', '^',                                              // bitwise
                                    '==', '===', '!=', '!==', '<', '>', '<=', '>=', 'matches',  // comparison
                                    'not', '!', 'and', '&&', 'or', '||',                        // logical
                                    '~',                                                        // concatentation
                                    'in', 'not in',                                             // array
                                    '..',                                                       // range
                                    '?', '?:', ':',                                              // ternary
                                ],
                                'variables' => array_map(
                                    function ($field) {
                                        return array(
                                            'name' => $field->getName(),
                                            'types' => $field->getTypes(),
                                            'hint' => $field->getInfo(),
                                            'link' => $field->getLink(),
                                        );
                                    },
                                    array_values($context->getFields())
                                ),
                                'typeinfo' => $context->getTypeInfo(),
                            ]);
                        ?>
                    },
                // a simple data table populator
                    setupTable = function(table, data, rowGenerator){
                        var $table = $(table),
                            $tbody = $table.find('tbody:last'),
                            addRow = function(rowData){
                                rowGenerator($tbody, rowData || {});
                            };
                        if(!$tbody.length){
                            $tbody = $('<tbody/>');
                            $table.append($tbody);
                        }
                        $table.width('100%');
                        $.each(data, function(i, rowData){ addRow(rowData); });
                        addRow();
                    };

                // category table
                setupTable(
                    '#categories',
                    <?php echo json_encode($data['categories']); ?>,
                    function($tbody, data){
                        var rowIndex = ++rowCounter;
                        $tbody.append(
                            $('<tr/>').append(
                                $('<td/>').append($('<input type="text" name="categories['+rowIndex+'][]" class="form-control" placeholder="Category Name"/>').val(data[0] || '')),
                                $('<td/>').append($('<input type="text" name="categories['+rowIndex+'][]" class="form-control" placeholder="Parent Category ID"/>').val(data[1] || ''))
                            )
                        );
                    }
                );

                // products table
                setupTable(
                    '#products',
                    <?php echo json_encode($data['products']); ?>,
                    function($tbody, data){
                        var rowIndex = ++rowCounter;
                        $tbody.append(
                            $('<tr/>').append(
                                $('<td/>').append($('<input type="text" name="products['+rowIndex+'][]" class="form-control" placeholder="Product Name"/>').val(data[0] || '')),
                                $('<td/>').append($('<input type="text" name="products['+rowIndex+'][]" class="form-control" placeholder="Product Colour"/>').val(data[1] || '')),
                                $('<td/>').append($('<input type="text" name="products['+rowIndex+'][]" class="form-control" placeholder="Category ID"/>').val(data[2] || ''))
                            )
                        );
                    }
                );

                // rules table
                setupTable(
                    '#rules',
                    <?php echo json_encode($data['rules']); ?>,
                    function($tbody, data){
                        var rowIndex = ++rowCounter,
                            $tr = $('<tr/>'),
                            $nameCell = $('<td/>').append($('<input type="text" name="rules['+rowIndex+'][]" class="form-control" placeholder="Rule Name"/>').val(data[0] || '')),
                            $condCell = $('<td/>').append($('<input type="text" name="rules['+rowIndex+'][]" class="form-control" data-lines="1" data-addclass="form-control" placeholder="Condition"/>').val(data[1] || ''));
                        $tbody.append($tr);
                        $tr.append($nameCell, $condCell);
                        $condCell.find('input').RuneEditor(runeEditorOptions);
                    }
                );
            });
        </script>
    </body>
</html>