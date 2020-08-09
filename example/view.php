<?php
/**
 * @var string APP_ROOT
 * @var string $json_tokens
 * @var string $json_categories
 * @var string $json_products
 * @var string $json_rules
 * @var string $output_result
 * @var string $output_generated
 * @var string $output_errors
 */
?><!DOCTYPE html>
<html lang="en">
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
        <link rel="stylesheet" href="<?php echo CDN_ROOT; ?>/extra/codemirror/rune.css">
        <script src="<?php echo CDN_ROOT; ?>/extra/codemirror/rune.js"></script>
        <!-- Some custom CSS -->
        <style type="text/css">
            .cm-hint-icon-uuf6429-Rune-example-Model-Product:before {
                content: "\1F455";
                font-size: 8px;
            }
            .cm-hint-icon-uuf6429-Rune-example-Model-Category:before {
                content: "\2731";
            }
        </style>
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
                                <tr>
                                    <th width="32px">ID</th>
                                    <th>Name</th>
                                    <th width="80px">Parent</th>
                                </tr>
                            </thead>
                        </table>
                    </fieldset>

                    <fieldset class="col-md-8">
                        <legend>Products</legend>
                        <table class="table table-hover table-condensed" id="products">
                            <thead>
                                <tr>
                                    <th width="32px">ID</th>
                                    <th>Name</th>
                                    <th>Colour</th>
                                    <th width="80px">Category</th>
                                </tr>
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
                                <tr>
                                    <th width="32px">ID</th>
                                    <th width="30%">Name</th>
                                    <th>Condition</th>
                                </tr>
                            </thead>
                        </table>
                    </fieldset>
                </div>
                &nbsp;
                <div class="row">
                    <div class="text-center">
                        <input type="submit" class="btn btn-primary btn-lg" value="Execute"/>
                        <a class="btn btn-link" href="<?php echo APP_ROOT; ?>">Reset Changes</a>
                    </div>
                </div>
                &nbsp;
            </form>

            <fieldset id="results">
                <legend>Rule Engine Result</legend>
                <pre><?php
                    echo implode(
                        PHP_EOL,
                        [
                            '<b>Result:</b>',
                            htmlspecialchars($output_result, ENT_QUOTES),
                            '<b>Compiled:</b>',
                            htmlspecialchars($output_generated, ENT_QUOTES),
                            '<b>Errors:</b>',
                            $output_errors ? htmlspecialchars($output_errors, ENT_QUOTES) : '<i>None</i>',
                        ]
                    );
                ?></pre>
            </fieldset>
        </div>

        <script>
            $(document).ready(function(){
                let globalRowCount = 0,
                // default rune editor settings
                    runeEditorOptions = {
                        tokens: <?php echo $json_tokens; ?>
                    },
                // a simple data table populator
                    setupTable = function(table, data, rowGenerator){
                        let $table = $(table),
                            $tbody = $table.find('tbody:last'),
                            updateEmptyRows = function(){
                                $tbody
                                    .find('tr')
                                    .filter(function(){
                                        let empty = true;
                                        $(this).find('input, textarea, select').each(function(){
                                            if($(this).val()){
                                                empty = false;
                                                return false;
                                            }
                                        });
                                        return empty;
                                    })
                                    .remove();
                                addRow();
                            },
                            addRow = function(rowData){
                                const $tr = rowGenerator($tbody, rowData || {});
                                $tr.find('input, textarea, select').on('change, blur', updateEmptyRows);
                                $tbody.find('.row-num-autogen').each(function(num, el){
                                    el.innerHTML = (num + 1).toString();
                                });
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
                    <?php echo $json_categories; ?>,
                    function($tbody, data){
                        const rowIndex = ++globalRowCount,
                            $tr = $('<tr/>');
                        $tbody.append(
                            $tr.append(
                                $('<td/>').append($('<div style="padding: 5px 0;" class="row-num-autogen"/>')),
                                $('<td/>').append($('<input type="text" name="categories['+rowIndex+'][]"'
                                    + ' class="form-control input-sm" placeholder="Category Name"/>').val(data[0] || '')),
                                $('<td/>').append($('<input type="text" name="categories['+rowIndex+'][]"'
                                    + ' class="form-control input-sm" placeholder="ID"/>').val(data[1] || ''))
                            )
                        );
                        return $tr;
                    }
                );

                // products table
                setupTable(
                    '#products',
                    <?php echo $json_products; ?>,
                    function($tbody, data){
                        const rowIndex = ++globalRowCount,
                            $tr = $('<tr/>');
                        $tbody.append(
                            $tr.append(
                                $('<td/>').append($('<div style="padding: 5px 0;" class="row-num-autogen"/>')),
                                $('<td/>').append($('<input type="text" name="products['+rowIndex+'][]"'
                                    + ' class="form-control input-sm" placeholder="Product Name"/>').val(data[0] || '')),
                                $('<td/>').append($('<input type="text" name="products['+rowIndex+'][]"'
                                    + ' class="form-control input-sm" placeholder="Product Colour"/>').val(data[1] || '')),
                                $('<td/>').append($('<input type="text" name="products['+rowIndex+'][]"'
                                    + ' class="form-control input-sm" placeholder="ID"/>').val(data[2] || ''))
                            )
                        );
                        return $tr;
                    }
                );

                // rules table
                setupTable(
                    '#rules',
                    <?php echo $json_rules; ?>,
                    function($tbody, data){
                        const rowIndex = ++globalRowCount,
                            $tr = $('<tr/>'),
                            $numCell = $('<td/>').append($('<div style="padding: 7px 0;" class="row-num-autogen"/>')),
                            $nameCell = $('<td/>').append($('<input type="text" name="rules['+rowIndex+'][]"'
                                + ' class="form-control" placeholder="Rule Name"/>').val(data[0] || '')),
                            $condCell = $('<td/>').append($('<input type="text" name="rules['+rowIndex+'][]"'
                                + ' class="form-control" data-lines="1" data-addclass="form-control" placeholder="Condition"/>').val(data[1] || ''));
                        $tbody.append($tr);
                        $tr.append($numCell, $nameCell, $condCell);
                        $condCell.find('input').RuneEditor(runeEditorOptions);
                        return $tr;
                    }
                );
            });
        </script>
    </body>
</html>
