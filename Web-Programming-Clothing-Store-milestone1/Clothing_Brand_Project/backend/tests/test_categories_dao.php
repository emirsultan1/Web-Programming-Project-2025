<?php
declare(strict_types=1);

require_once __DIR__ . '/../dao/CategoriesDAO.php';

$dao = new CategoriesDAO();

$id = $dao->createCategory('Jackets');
echo "Created category_id = $id\n";

print_r($dao->findById($id));

$dao->updateCategory($id, ['name' => 'Outerwear']);
print_r($dao->findById($id));

print_r($dao->listCategories(5, 0));

$dao->deleteCategory($id);
echo "Deleted $id\n";
