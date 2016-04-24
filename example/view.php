<?php
/**
 * @var string APP_ROOT
 * @var int    $failureMode
 * @var string $json_tokens
 * @var string $json_categories
 * @var string $json_products
 * @var string $json_rules
 * @var string $output_result
 * @var string $output_errors
 */
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
                <div class="row">
                    <div class="col-md-4 form-group-sm form-inline">
                        <label class="control-label" for="failureMode">Failure Level:&nbsp;</label>
                        <select class="form-control " name="failureMode"><?php
                            foreach ([3 => 'Engine', 2 => 'Context', 1 => 'Rule'] as $i => $text) {
                                echo '<option value="'.$i.'"'
                                        .($i == $failureMode ? ' selected="selected"' : '').
                                    '>'.$text.'</option>';
                            }
                        ?></select>
                    </div>
                    <div class="col-md-8 text-center">
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
                            $output_result,
                            '<b>Errors:</b>',
                            $output_errors,
                        ]
                    );
                ?></pre>
            </fieldset>
        </div>
        
        <script>
            $(document).ready(function(){
                var rowCounter = 0,
                // default rune editor settings
                    runeEditorOptions = {
                        tokens: <?php echo $json_tokens; ?>
                    },
                // a simple data table populator
                    setupTable = function(table, data, rowGenerator){
                        var $table = $(table),
                            $tbody = $table.find('tbody:last'),
                            updateEmptyRows = function(){
                                $tbody
                                    .find('tr')
                                    .filter(function(){
                                        var empty = true;
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
                                var $tr = rowGenerator($tbody, rowData || {});
                                $tr.find('input, textarea, select').on('change, blur', updateEmptyRows);
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
                        var rowIndex = ++rowCounter,
                            $tr = $('<tr/>');
                        $tbody.append(
                            $tr.append(
                                $('<td/>').append($('<input type="text" name="categories['+rowIndex+'][]" class="form-control" placeholder="Category Name"/>').val(data[0] || '')),
                                $('<td/>').append($('<input type="text" name="categories['+rowIndex+'][]" class="form-control" placeholder="Parent Category ID"/>').val(data[1] || ''))
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
                        var rowIndex = ++rowCounter,
                            $tr = $('<tr/>');
                        $tbody.append(
                            $tr.append(
                                $('<td/>').append($('<input type="text" name="products['+rowIndex+'][]" class="form-control" placeholder="Product Name"/>').val(data[0] || '')),
                                $('<td/>').append($('<input type="text" name="products['+rowIndex+'][]" class="form-control" placeholder="Product Colour"/>').val(data[1] || '')),
                                $('<td/>').append($('<input type="text" name="products['+rowIndex+'][]" class="form-control" placeholder="Category ID"/>').val(data[2] || ''))
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
                        var rowIndex = ++rowCounter,
                            $tr = $('<tr/>'),
                            $nameCell = $('<td/>').append($('<input type="text" name="rules['+rowIndex+'][]" class="form-control" placeholder="Rule Name"/>').val(data[0] || '')),
                            $condCell = $('<td/>').append($('<input type="text" name="rules['+rowIndex+'][]" class="form-control" data-lines="1" data-addclass="form-control" placeholder="Condition"/>').val(data[1] || ''));
                        $tbody.append($tr);
                        $tr.append($nameCell, $condCell);
                        $condCell.find('input').RuneEditor(runeEditorOptions);
                        return $tr;
                    }
                );
            });
        </script>
    </body>
</html>