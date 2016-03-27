<?php

return [
    'rules' => [
        ['Red Products', 'product.colour == "red"'],
        ['Red Socks', 'product.colour == "red" and (product.name matches "/socks/i") > 0'],
        ['Green Socks', 'product.colour == "green" and (product.name matches "/socks/i") > 0'],
        ['Socks', '(product.name matches "/socks/i") > 0'],
        ['Toys', 'product.category.is("Toys")'],
    ],
    'categories' => [
        ['Root', null],
        ['Clothes', 1],
        ['Toys', 1],
        ['Underwear', 2],
        ['Jackets', 2],
        ['Socks', 4],
    ],
    'products' => [
        ['Bricks', 'red', 3],
        ['Soft Socks', 'green', 6],
        ['Sporty Socks', 'yellow', 6],
        ['Lego Blocks', '', 3],
        ['Adidas Jacket', 'black', 5],
    ],
];
