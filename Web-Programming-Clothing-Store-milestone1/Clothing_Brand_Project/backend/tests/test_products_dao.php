<?php
declare(strict_types=1);

require_once __DIR__ . '/../dao/CategoriesDAO.php';
require_once __DIR__ . '/../dao/ProductsDAO.php';

$cats = new CategoriesDAO();
$prods = new ProductsDAO();

$catId = $cats->createCategory('Accessories');
$pid = $prods->createProduct('Leather Belt', 'Genuine leather', 19.99, 50, $catId);
echo "Created product_id = $pid\n";

print_r($prods->findById($pid));

$prods->updateProduct($pid, ['stock_qty' => 45, 'price' => 18.99]);
print_r($prods->findById($pid));

print_r($prods->listProducts($catId, 5, 0));

$prods->deleteProduct($pid);
$cats->deleteCategory($catId);
echo "Deleted product and category\n";
