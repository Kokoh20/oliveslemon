<?php
declare(strict_types=1);
require __DIR__ . '/util.php';

$products = [
    // Coffee Based (Iced)
    ['id' => 'coffee-iced-cappuccino', 'name' => 'Cappuccino', 'category' => 'coffee-iced', 'variants' => [['name' => 'Medium', 'price' => 90], ['name' => 'Large', 'price' => 110]]],
    ['id' => 'coffee-iced-latte', 'name' => 'Latte', 'category' => 'coffee-iced', 'variants' => [['name' => 'Medium', 'price' => 90], ['name' => 'Large', 'price' => 110]]],
    ['id' => 'coffee-iced-mocha', 'name' => 'Mocha', 'category' => 'coffee-iced', 'variants' => [['name' => 'Medium', 'price' => 90], ['name' => 'Large', 'price' => 110]]],
    ['id' => 'coffee-iced-caramel-macchiato', 'name' => 'Caramel Macchiato', 'category' => 'coffee-iced', 'variants' => [['name' => 'Medium', 'price' => 90], ['name' => 'Large', 'price' => 110]]],
    ['id' => 'coffee-iced-white-mocha', 'name' => 'White Mocha', 'category' => 'coffee-iced', 'variants' => [['name' => 'Medium', 'price' => 90], ['name' => 'Large', 'price' => 110]]],
    ['id' => 'coffee-iced-spanish-latte', 'name' => 'Spanish Latte', 'category' => 'coffee-iced', 'variants' => [['name' => 'Medium', 'price' => 90], ['name' => 'Large', 'price' => 110]]],
    ['id' => 'coffee-iced-hazelnut', 'name' => 'Hazelnut', 'category' => 'coffee-iced', 'variants' => [['name' => 'Medium', 'price' => 90], ['name' => 'Large', 'price' => 110]]],
    ['id' => 'coffee-iced-salted-caramel', 'name' => 'Salted Caramel', 'category' => 'coffee-iced', 'variants' => [['name' => 'Medium', 'price' => 90], ['name' => 'Large', 'price' => 110]]],
    ['id' => 'coffee-iced-french-vanilla', 'name' => 'French Vanilla', 'category' => 'coffee-iced', 'variants' => [['name' => 'Medium', 'price' => 90], ['name' => 'Large', 'price' => 110]]],
    ['id' => 'coffee-iced-americano', 'name' => 'Americano', 'category' => 'coffee-iced', 'variants' => [['name' => 'Medium', 'price' => 55], ['name' => 'Large', 'price' => 75]]],

    // Frappuccino
    ['id' => 'frappuccino-biscoff', 'name' => 'Biscoff', 'category' => 'frappuccino', 'variants' => [['name' => '16oz Medium', 'price' => 140], ['name' => '22oz Large', 'price' => 160]]],
    ['id' => 'frappuccino-java-chip', 'name' => 'Java Chip', 'category' => 'frappuccino', 'variants' => [['name' => '16oz Medium', 'price' => 140], ['name' => '22oz Large', 'price' => 160]]],
    ['id' => 'frappuccino-cookies-n-cream', 'name' => "Cookies n' Cream", 'category' => 'frappuccino', 'variants' => [['name' => '16oz Medium', 'price' => 140], ['name' => '22oz Large', 'price' => 160]]],

    // Hot Drinks
    ['id' => 'hot-drinks-hot-cappuccino', 'name' => 'Hot Cappuccino', 'category' => 'hot-drinks', 'variants' => [['name' => 'Regular', 'price' => 80]]],
    ['id' => 'hot-drinks-hot-latte', 'name' => 'Hot Latte', 'category' => 'hot-drinks', 'variants' => [['name' => 'Regular', 'price' => 80]]],
    ['id' => 'hot-drinks-hot-mocha', 'name' => 'Hot Mocha', 'category' => 'hot-drinks', 'variants' => [['name' => 'Regular', 'price' => 80]]],
    ['id' => 'hot-drinks-hot-white-mocha', 'name' => 'Hot White Mocha', 'category' => 'hot-drinks', 'variants' => [['name' => 'Regular', 'price' => 80]]],
    ['id' => 'hot-drinks-hot-americano', 'name' => 'Hot Americano', 'category' => 'hot-drinks', 'variants' => [['name' => 'Regular', 'price' => 55]]],
    ['id' => 'hot-drinks-hot-chocolate', 'name' => 'Hot Chocolate', 'category' => 'hot-drinks', 'variants' => [['name' => 'Regular', 'price' => 80]]],

    // Matcha Series
    ['id' => 'matcha-series-matcha-latte', 'name' => 'Matcha Latte', 'category' => 'matcha-series', 'variants' => [['name' => 'Medium', 'price' => 110], ['name' => 'Large', 'price' => 130]]],
    ['id' => 'matcha-series-matcha-strawberry', 'name' => 'Matcha Strawberry', 'category' => 'matcha-series', 'variants' => [['name' => 'Medium', 'price' => 120], ['name' => 'Large', 'price' => 140]]],

    // Non Coffee
    ['id' => 'non-coffee-chocolate', 'name' => 'Chocolate', 'category' => 'non-coffee', 'variants' => [['name' => 'Medium', 'price' => 80], ['name' => 'Large', 'price' => 100]]],
    ['id' => 'non-coffee-strawberry', 'name' => 'Strawberry', 'category' => 'non-coffee', 'variants' => [['name' => 'Medium', 'price' => 80], ['name' => 'Large', 'price' => 100]]],

    // Juices
    ['id' => 'juices-mango', 'name' => 'Mango', 'category' => 'juices', 'variants' => [['name' => 'Medium', 'price' => 80], ['name' => 'Large', 'price' => 100]]],
    ['id' => 'juices-green-apple', 'name' => 'Green Apple', 'category' => 'juices', 'variants' => [['name' => 'Medium', 'price' => 80], ['name' => 'Large', 'price' => 100]]],

    // Food - Appetizers
    ['id' => 'appetizers-fries', 'name' => 'Fries', 'category' => 'appetizers', 'variants' => [['name' => 'Regular', 'price' => 55]]],
    ['id' => 'appetizers-nachos', 'name' => 'Nachos', 'category' => 'appetizers', 'variants' => [['name' => 'Regular', 'price' => 75]]],

    // Food - Pasta
    ['id' => 'pasta-carbonara', 'name' => 'Carbonara', 'category' => 'pasta', 'variants' => [['name' => 'Regular', 'price' => 99]]],
    ['id' => 'pasta-bolognese', 'name' => 'Bolognese', 'category' => 'pasta', 'variants' => [['name' => 'Regular', 'price' => 99]]],

    // Food - Rice Meals
    ['id' => 'rice-meals-chicken-teriyaki', 'name' => 'Chicken Teriyaki', 'category' => 'rice-meals', 'variants' => [['name' => 'Regular', 'price' => 99]]],
    ['id' => 'rice-meals-beef-tapa', 'name' => 'Beef Tapa', 'category' => 'rice-meals', 'variants' => [['name' => 'Regular', 'price' => 99]]],

    // Food - Sandwich
    ['id' => 'sandwich-clubhouse', 'name' => 'Clubhouse', 'category' => 'sandwich', 'variants' => [['name' => 'Regular', 'price' => 89]]],
    ['id' => 'sandwich-ham-cheese', 'name' => 'Ham & Cheese', 'category' => 'sandwich', 'variants' => [['name' => 'Regular', 'price' => 69]]],
];

send_json(['products' => $products]);
