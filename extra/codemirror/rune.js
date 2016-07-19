/*
 * Code editor for Expression Language syntax with hinting support.
 *
 * Dependencies:
 * codemirror/lib/codemirror.css
 * codemirror/lib/codemirror.js
 * codemirror/addon/mode/simple.js
 * codemirror/addon/hint/show-hint.js
 * codemirror/addon/hint/show-hint.css
 */
/* global CodeMirror */
(function(){
    "use strict";
    
    var 
        /**
         * Returns true if value is an array type, ie can be used with for/length as opposed to for/in.
         * @param {*} value
         * @param {Boolean} [complex=false]
         * @returns {Boolean}
         */
        isArrayLike = function(value, complex){
            return value
                && typeof value === "object"
                && typeof value.length === "number"
                && value.length >= 0
                && value.length % 1 === 0
                && (!complex || typeof value.splice === "function");
        },
        
        /**
         * @callback eachIterationCallback
         * @param {number|string} index Index (for arrays) or property name (for objects) of current iteration.
         * @param {*} value Value of current iteration.
         */
        /**
         * Run a callback for each property or item in obj.
         * @param {*} obj Object or array to iterate over.
         * @param {eachIterationCallback} callback
         */
        each = function(obj, callback) {
            var length, i = 0;

            if (isArrayLike(obj)) {
                length = obj.length;
                for (; i < length; i++) {
                    if (callback.call(obj[i], i, obj[i]) === false) {
                        break;
                    }
                }
            } else {
                for (i in obj) {
                    if (callback.call(obj[i], i, obj[i]) === false) {
                        break;
                    }
                }
            }
        },
        
        /**
         * @callback mapIterationCallback
         * @param {*} value Value of current iteration.
         * @param {number|string} index Index (for arrays) or property name (for objects) of current iteration.
         */
        /**
         * Run a callback for each property or item in obj and return array built with result of callback.
         * @param {*} obj Object or array to iterate over.
         * @param {mapIterationCallback} callback
         */
        map = function(obj, callback){
            var result = [];
            
            each(obj, function(i, value){
                result[i] = callback(value, i);
            });
            
            return result;
        },

        /**
         * Merges arguments from left to right, (rightmost argument overrides previous), returning merged result.
         * @param {...Object} args
         * @return {Object}
         */
        extend = function(){
            var result = {},
                extender = function(key, val){
                    result[key] = val;
                };

            for(var i = 0; i < arguments.length; i++){
                each(arguments[i], extender);
            }

            return result;
        },

        /**
         * @var number
         */
        StaticCounter = 0,

        /**
         * @property {object} tokens
         * @property {object} tokens.constants
         * @property {string} tokens.constants.name
         * @property {string} tokens.constants.type
         * @property {string[]} tokens.operators
         * @property {object[]} tokens.variables
         * // TODO more docs here
         * @property {object[]} tokens.functions
         * // TODO more docs here
         * @property {object} tokens.typeinfo
         * // TODO more docs here
         */
        DefaultOptions = {
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
                variables: [],
                functions: [],
                typeinfo: {}
            }
        },

        /**
         * @constructor
         * @param {Element} element
         * @param {Object} [options]
         * @todo Change type of 2nd argument in jsdoc
         */
        RuneEditor = function(element, options){
            this.options = extend(DefaultOptions, options);
            this.element = element;
            var mode = 'rune-explang-' + (++StaticCounter);
            this.initHighlightHint(mode);
            this.initHighlightMode(mode);
            this.initialize(mode);
        }
    ;
    
    RuneEditor.prototype = {
        // public methods
    };

    return RuneEditor;
})();
