<?php
namespace App\Core;

use Flight;

/**
 * Initializes the backend environment (Milestone 4)
 * - loads DAOs (plain PHP classes)
 * - loads core helpers used by services
 * - registers Services in Flight's container
 * - sets timezone
 */
final class Bootstrap {
  public static function init(): void {
    // timezone
    date_default_timezone_set('Africa/Cairo');

    // === Require Core helpers (used in Services) ===
    // If you already have Composer PSR-4 autoload for App\Core, these are harmless.
    // If you DON'T, these prevent "Class not found" errors.
    require_once __DIR__ . '/Validator.php';
    require_once __DIR__ . '/Paginator.php';

    // === Require DAOs ===
    require_once __DIR__ . '/../dao/BaseDAO.php';
    require_once __DIR__ . '/../dao/ProductsDAO.php';
    require_once __DIR__ . '/../dao/CategoriesDAO.php';
    require_once __DIR__ . '/../dao/UsersDAO.php';
    require_once __DIR__ . '/../dao/OrdersDAO.php';
    require_once __DIR__ . '/../dao/OrderItemsDAO.php';
    require_once __DIR__ . '/../dao/PaymentsDAO.php';
    require_once __DIR__ . '/../dao/ReviewsDAO.php';

    // === Require Services ===
    require_once __DIR__ . '/../services/ProductService.php';
    require_once __DIR__ . '/../services/CategoryService.php';
    require_once __DIR__ . '/../services/UserService.php';
    require_once __DIR__ . '/../services/OrderService.php';
    require_once __DIR__ . '/../services/OrderItemService.php';
    require_once __DIR__ . '/../services/PaymentService.php';
    require_once __DIR__ . '/../services/ReviewService.php';

    // === Instantiate DAOs ===
    $productDao     = new \ProductsDAO();
    $categoryDao    = new \CategoriesDAO();
    $userDao        = new \UsersDAO();
    $ordersDao      = new \OrdersDAO();
    $orderItemsDao  = new \OrderItemsDAO();
    $paymentsDao    = new \PaymentsDAO();
    $reviewsDao     = new \ReviewsDAO();

    // === Register Services ===
    Flight::set('ProductService',    new \App\Services\ProductService($productDao));
    Flight::set('CategoryService',   new \App\Services\CategoryService($categoryDao));
    Flight::set('UserService',       new \App\Services\UserService($userDao));
    Flight::set('OrderService',      new \App\Services\OrderService($ordersDao, $orderItemsDao, $productDao, $userDao));
    Flight::set('OrderItemService',  new \App\Services\OrderItemService($orderItemsDao, $ordersDao, $productDao));
    Flight::set('PaymentService',    new \App\Services\PaymentService($paymentsDao, $ordersDao));
    Flight::set('ReviewService',     new \App\Services\ReviewService($reviewsDao, $userDao, $productDao));
  }
}
