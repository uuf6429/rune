<?php

return [
    'failureMode' => 2,
    'rules' => [
        ['Red Products', 'product.colour == lower("Red")'],
        ['Red Socks', 'product.colour == "red" and (product.name matches "/socks/i") > 0'],
        ['Green Socks', 'product.colour == "green" and (product.name matches "/socks/i") > 0'],
        ['Socks', '(product.name matches "/socks/i") > 0'],
        ['Toys', 'product.category.in("Toys")'],
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
