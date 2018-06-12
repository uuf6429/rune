/*
 * Code editor for Expression Language syntax with hinting support.
 *
 * Dependencies:
 * /assets/js/libs/codemirror/lib/codemirror.css
 * /assets/js/libs/codemirror/lib/codemirror.js
 * /assets/js/libs/codemirror/addon/mode/simple.js
 * /assets/js/libs/codemirror/addon/hint/show-hint.js
 * /assets/js/libs/codemirror/addon/hint/show-hint.css
 */
/* global CodeMirror */
/* global Infinity */
;
(function($) {
    var self = this,
        staticCounter = 0,
        autocompleteIgnoredKeys = {
            '8': 'backspace',
            '9': 'tab',
            '13': 'enter',
            '16': 'shift',
            '17': 'ctrl',
            '18': 'alt',
            '19': 'pause',
            '20': 'capslock',
            '27': 'escape',
            '33': 'pageup',
            '34': 'pagedown',
            '35': 'end',
            '36': 'home',
            '37': 'left',
            '38': 'up',
            '39': 'right',
            '40': 'down',
            '45': 'insert',
            '46': 'delete',
            '91': 'left window key',
            '92': 'right window key',
            '93': 'select',
            '107': 'add',
            '109': 'subtract',
            '110': 'decimal point',
            '111': 'divide',
            '112': 'f1',
            '113': 'f2',
            '114': 'f3',
            '115': 'f4',
            '116': 'f5',
            '117': 'f6',
            '118': 'f7',
            '119': 'f8',
            '120': 'f9',
            '121': 'f10',
            '122': 'f11',
            '123': 'f12',
            '144': 'numlock',
            '145': 'scrolllock',
            '186': 'semicolon',
            '187': 'equalsign',
            '188': 'comma',
            '189': 'dash',
            '191': 'slash',
            '192': 'graveaccent',
            '220': 'backslash',
            '222': 'quote'
        },
        autocompleteIgnoredTypes = [
            'string',
            'comment'
        ],

        /**
         * @param elements string|jQuery
         * @param options
         */
        RuneEditor = function(elements, options) {
            var me = this;
            me.options = $.extend(true, {}, me.defaultOptions, options);
            me.$elements = $(elements);
            var mode = 'rune-explang-' + ++staticCounter;
            me.initHighlightHint(mode);
            me.initHighlightMode(mode);
            me.initialize(mode);
        };

    $.fn.RuneEditor = function(options) {
        return this.each(function() {
            new RuneEditor(this, options || {});
        });
    };

    RuneEditor.prototype = {
        defaultOptions: {
            tokens: {
                constants: [
                    {
                        name: 'true',
                        type: 'boolean'
                    },
                    {
                        name: 'false',
                        type: 'boolean'
                    },
                    {
                        name: 'null',
                        type: 'null'
                    }
                ],
                operators: ['-', '+', '/', '*', '==', '<', '>', '!'],
                variables: [
                    /*{
                        name: 'SomeDummyVar',
                        types: ['integer'],
                        hint: 'A dummy variable',
                        link: 'http://google.com/#q=SomeDummyVar'
                    },
                    {
                        name: 'MyObject',
                        types: ['MyClass', 'MyInterface'],
                        hint: 'An instance of my class.',
                        link: 'http://google.com/#q=MyClassInstance'
                    },*/
                ],
                functions: [
                    /*{
                        name: 'SomeDummyFn',
                        types: ['mixed'],
                        args: [],
                        hint: 'A dummy function',
                        link: 'http://google.com/#q=SomeDummyFn'
                    }*/
                ],
                typeinfo: {
                    /*'MyClass': {
                        name: 'MyClass',
                        hint: 'My new class.',
                        members: {
                            'name': {
                                name: 'name',
                                types: ['string'],
                                hint: 'Object name'
                            },
                            'getNiceName': {
                                name: 'getNiceName',
                                types: ['function(): string'],
                                hint: 'Returns object name in a nice format'
                            },
                            'parent': {
                                name: 'parent',
                                types: ['MyClass'],
                                hint: 'Returns parent object'
                            }
                        },
                    },
                    'MyInterface': {
                        name: 'MyInterface',
                        hint: 'Some interface.',
                        members: {
                            'getNiceName': {
                                name: 'getNiceName',
                                types: ['function(): string'],
                                hint: 'Returns object name in a nice format'
                            },
                        },
                    },
                    'MyEnum': {
                        values: {
                            0: 'DISABLED',
                            1: 'ENABLED',
                            2: 'INHERIT'
                        }
                    }*/
                }
            }
        },

        $elements: null,

        escapeRegExp: function(str) {
            return str.replace(/[\-\[\]\/{}()*+?.\\^$|]/g, '\\$&');
        },

        initialize: function(mode) {
            var me = this;

            me.$elements.each(function(_, el) {
                var $el = $(el);
                var lines = $el.data('lines');
                var addclass = $el.data('addclass');
                var readonly = !((el.tagName === 'INPUT' || el.tagName === 'TEXTAREA') && !el.readonly);
                $el.hide();

                var cm = CodeMirror(function(elt) {
                    $el.after(elt);
                }, {
                    value: (el.value || el.textContent || el.innerText || el.innerHTML),
                    readOnly: (readonly ? 'nocursor' : false),
                    extraKeys: {
                        "Ctrl-Space": "autocomplete"
                    },
                    theme: 'explang',
                    mode: mode
                });

                if (!readonly) {
                    cm.on('change', function(inst) {
                        if (typeof el.value !== 'undefined') {
                            el.value = inst.getValue();
                        }
                        else if (typeof el.textContent !== 'undefined') {
                            el.textContent = inst.getValue();
                        }
                    });
                }

                cm.on('keyup', function(cm, event) {
                    if (!cm.state.completionActive
                        && typeof(autocompleteIgnoredKeys[event.keyCode.toString()]) === 'undefined'
                        && autocompleteIgnoredTypes.indexOf(cm.getTokenAt(cm.getCursor()).type) === -1
                    ) {
                        CodeMirror.commands.autocomplete(cm, null, { completeSingle: false });
                    }
                });

                if (lines !== null) {
                    cm.setSize(null, 'auto');
                    cm.setOption('viewportMargin', lines === 'auto' ? Infinity : +lines);
                }

                if (addclass) {
                    $(cm.display.wrapper).addClass(addclass);
                }
            });
        },

        initHighlightMode: function(mode) {
            var me = this;
            var errorState = {
                regex: /[.\w]+/,
                token: 'error',
                next: 'start'
            };

            /** @see https://codemirror.net/demo/simplemode.html */
            var states = {
                start: [
                    // double-quoted strings
                    {
                        regex: /"(?:[^\\]|\\.)*?"/,
                        token: 'string'
                    },
                    // single-quoted strings
                    {
                        regex: /'(?:[^\\]|\\.)*?'/,
                        token: 'string'
                    },
                    // brackets
                    {
                        regex: /[{\[(}\])]/,
                        token: 'bracket'
                    }
                ],
                comment: [],
                meta: {}
            };

            // constants
            if (me.options.tokens.constants.length) {
                states.start.push({
                    regex: new RegExp($.map(me.options.tokens.constants, function(token) {
                        return me.escapeRegExp(token.name);
                    }).join('|')),
                    token: 'atom'
                });
            }

            // operators
            if (me.options.tokens.operators.length) {
                states.start.push({
                    regex: new RegExp(
                        $.map(
                            me.options.tokens.operators,
                            me.escapeRegExp
                        ).join('|')
                    ),
                    token: 'operator'
                });
            }

            // variables
            if (me.options.tokens.variables.length) {
                $.each(me.options.tokens.variables, function(i1, variable) {
                    states.start.push({
                        regex: new RegExp(me.escapeRegExp(variable.name)),
                        token: 'variable',
                        next: me.isValidType(variable.types[0])
                            ? ('type_' + variable.types[0]) : 'start'
                    });
                });
            }

            // properties
            for (var typeName in me.options.tokens.typeinfo) {
                var state = [];
                var members = me.options.tokens.typeinfo[typeName].members;

                for (var memberName in members) {
                    state.push({
                        regex: new RegExp(me.escapeRegExp('.'+memberName)),
                        token: 'property',
                        next: me.isValidType(members[memberName].types[0])
                            ? ('type_' + members[memberName].types[0]) : 'start'
                    });
                }
                state.push(errorState);
                state.push({next: 'start'});

                states['type_' + typeName] = state;
            }

            // functions
            if (me.options.tokens.functions.length) {
                states.start.push({
                    regex: new RegExp(
                        $.map(
                            me.options.tokens.functions,
                            function(token) {
                                return me.escapeRegExp(token.name);
                            }
                        ).join('|')
                    ),
                    token: 'function'
                });
            }

            // numbers
            states.start.push({
                regex: /0x[a-f\d]+|[-+]?(?:\.\d+|\d+\.?\d*)(?:e[-+]?\d+)?/i,
                token: 'number'
            });

            // everything else (unexpected)
            states.start.push(errorState);

            CodeMirror.defineSimpleMode(mode, states);
        },

        initHighlightHint: function(mode) {
            var me = this;

            var MAX_ROWS = 20,
                MATCHERS = {
                    matchStartsWith: function(origString, searchString) {
                        return origString.substr(0, searchString.length) === searchString;
                    },
                    matchContains: function(origString, searchString) {
                        return origString.indexOf(searchString) !== -1;
                    }
                };

            CodeMirror.registerHelper('hint', mode, function(editor, options) {
                var cur = editor.getCursor(),
                    curLine = editor.getLine(cur.line),
                    end = cur.ch,
                    start = cur.ch,
                    offset_end = cur.ch,
                    offset_start = cur.ch,
                    list = [];

                while (end && /[\w]+/.test(curLine.charAt(end + 1))) ++end;
                while (start && /[\w.]+/.test(curLine.charAt(start - 1))) --start;
                while (offset_start && /[\w]+/.test(curLine.charAt(offset_start - 1))) --offset_start;

                var curWord = (start !== end && curLine.slice(start, offset_end)) || '',
                    curPath = curWord ? curWord.split('.') : '',
                    isProp = curPath.length > 1,
                    addedTokens = [];

                if (isProp) {
                    // handle property paths
                    me.getSuggestedMembers(MATCHERS, MAX_ROWS, list, addedTokens, curPath);
                } else {
                    // handle simple values
                    me.getSuggestedVariables(MATCHERS, MAX_ROWS, list, addedTokens, curWord);
                }

                return me.initHintsTooltip({
                    list: list.slice(0, MAX_ROWS),
                    from: CodeMirror.Pos(cur.line, offset_start),
                    to: CodeMirror.Pos(cur.line, end + 1)
                });
            });
        },

        initHintsTooltip: function(hints) {
            var $tooltip,
                removeTooltip = function() {
                    if ($tooltip) {
                        $tooltip.remove();
                        $tooltip = null;
                    }
                };

            CodeMirror.on(hints, 'close', function() {
                removeTooltip();
            });

            CodeMirror.on(hints, 'update', function() {
                removeTooltip();
            });

            CodeMirror.on(hints, 'select', function(cur, node) {
                removeTooltip();

                if (cur.docHTML) {
                    $tooltip = $('<div class="CodeMirror-hints cm-hint-hint"/>').css({
                        'left': node.parentNode.getBoundingClientRect().right + window.pageXOffset,
                        'top': node.getBoundingClientRect().top + window.pageYOffset
                    }).html(cur.docHTML);

                    $tooltip.mousedown(function(event) {
                        event.stopPropagation();
                        event.preventDefault();
                    });

                    $(document.body).append($tooltip);
                }
            });

            return hints;
        },

        generateHintHtml: function(token) {
            var html = '';

            /*html += '<b>' + token.name + '</b>';

            if (typeof(token.types) !== 'undefined' && token.types) {
                html += '<i>(' + token.types.join('|')' + ')</i>';
            }

            html += '<br/>';*/

            if (typeof(token.hint) !== 'undefined' && token.hint) {
                html += token.hint + '<br/>';
            }

            if (typeof(token.link) !== 'undefined' && token.link) {
                html += '<a href="' + token.link + '" target="_blank">&raquo; More Info</a>';
            }

            return html;
        },

        getSuggestedMembers: function(matchers, maxResults, prevResults, prevResultTokens, searchPath) {
            var me = this,
                count = 0,
                pathFirst = searchPath[0],
                pathOther = searchPath.slice(1, -1),
                pathLast = searchPath.slice(-1),
                members = [];

            // handle first item in path (variable)
            $.each(me.options.tokens.variables, function(i1, variable) {
                if (variable.name === pathFirst) {
                    members = me.getTypesMembers(variable.types);
                    return false;
                }
            });

            if (!members.length) {
                return;
            }

            // handle middle items in path (completed props)
            $.each(pathOther, function(i1, pName) {
                var member = null;

                $.each(members, function(i2, testMember){
                    if (testMember.name === pName) {
                        member = testMember;
                        return false;
                    }
                });

                if (member === null) {
                    members = [];
                    return false;
                }

                members  = me.getTypesMembers(member.types || member.type);

                if (!members.length) {
                    return false;
                }
            });

            if (!members.length) {
                return;
            }

            if (pathLast !== '') {
                var matchMembers = members;
                members = [];
                count = prevResults.length;
                $.each(matchers, function(i1, matcher) {
                    for (var i2 = 0; i2 < matchMembers.length; i2++) {
                        var member = matchMembers[i2];
                        if (matcher(member.name, pathLast)) {
                            if(++count >= maxResults){
                                return false;
                            }
                            members.push(member);
                        }
                    }
                });
            }

            // add matching members to suggested items
            count = prevResults.length;
            $.each(members, function(i1, token) {
                if(++count >= maxResults){
                    return false;
                }
                if (prevResultTokens.indexOf(token.name) === -1) {
                    prevResults.push({
                        text: token.name + (me.isCallableToken(token) ? '()' : ''),
                        displayText: token.name,
                        className: me.getTypesClass(token.types),
                        docHTML: me.generateHintHtml(token)
                    });
                    prevResultTokens.push(token.name);
                }
            });
        },

        getSuggestedVariables: function(matchers, maxResults, prevResults, prevResultTokens, searchQuery) {
            var me = this;

            var variables = [];
            $.each(matchers, function(i1, matcher) {
                $.each(['constants', 'variables', 'functions' ], function(i2, tokensKey) {
                    $.each(me.options.tokens[tokensKey], function(i3, token) {
                        if (matcher(token.name, searchQuery)) {
                            variables.push(token);
                        }
                    });

                    return prevResults.length < maxResults;
                });

                return prevResults.length < maxResults;
            });

            // add matching variables to suggested items
            var count = prevResults.length;
            for (var key in variables) {
                if(++count >= maxResults){
                    break;
                }
                var token = variables[key];
                if (prevResultTokens.indexOf(token.name) === -1) {
                    prevResults.push({
                        text: token.name + (me.isCallableToken(token) ? '()' : ''),
                        displayText: token.name,
                        className: me.getTypesClass(token.types || token.type),
                        docHTML: me.generateHintHtml(token)
                    });
                    prevResultTokens.push(token.name);
                }
            }
        },

        getTypesClass: function(types) {
            var classNames = ['cm-hint-icon'];

            if (types) {
                if (typeof(types) === 'string') {
                    types = [types];
                }

                var isObject = false;

                for (var i = 0; i < types.length; i++) {
                    isObject = isObject || this.isValidType(types[i]);
                    classNames.push('cm-hint-icon-' + types[i].replace(/\\/g, '-'));
                }

                if (isObject) {
                    classNames.unshift('cm-hint-icon-object');
                }
            }

            return classNames.join(' ');
        },

        getTypesMembers: function(types) {
            var me = this,
                members = [];

            if (types) {
                if (typeof(types) === 'string') {
                    types = [types];
                }

                $.each(types, function (i1, typeName) {
                    if (typeName in me.options.tokens.typeinfo) {
                        for (var i2 in me.options.tokens.typeinfo[typeName].members) {
                            members.push(me.options.tokens.typeinfo[typeName].members[i2]);
                        }
                    }
                });
            }

            return members;
        },

        isValidType: function(name){
            return name && (name in this.options.tokens.typeinfo);
        },
        
        isCallableToken: function(token) {
            return token && token.types && token.types.length && (
                token.types.indexOf('method') !== -1
                || token.types.indexOf('function') !== -1
                || token.types.indexOf('callable') !== -1
            );
        }
    };
})(jQuery);
