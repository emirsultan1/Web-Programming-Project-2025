<?php
declare(strict_types=1);

/**
 * Milestone 2 end-to-end smoke test
 * - Uses a unique email per run to avoid duplicate collisions across runs
 * - Still verifies unique(email) by attempting a second insert with SAME email
 */

require_once __DIR__ . '/../dao/UsersDAO.php';
require_once __DIR__ . '/../dao/CategoriesDAO.php';
require_once __DIR__ . '/../dao/ProductsDAO.php';
require_once __DIR__ . '/../dao/OrdersDAO.php';
require_once __DIR__ . '/../dao/OrderItemsDAO.php';
require_once __DIR__ . '/../dao/PaymentsDAO.php';
require_once __DIR__ . '/../dao/ReviewsDAO.php';
require_once __DIR__ . '/../db/db.php';

$passes = 0; $fails = 0;
function ok($cond, string $msg) {
    global $passes, $fails;
    if ($cond) { echo "✅ $msg\n"; $passes++; }
    else { echo "❌ $msg\n"; $fails++; }
}

// Use a unique email per run so repeated executions never clash
$testEmail = 'smoke_user+' . uniqid('', true) . '@example.com';

// Optional: also clean any old fixed email from previous versions of this test
try {
    $pdo = (new Database())->connect();
    $pdo->prepare("DELETE FROM reviews WHERE user_id IN (SELECT user_id FROM users WHERE email = 'smoke_user@example.com')")->execute();
    // cascade cleanup for orders-related
    $pdo->prepare("
        DELETE p FROM payments p
        JOIN orders o ON o.order_id = p.order_id
        JOIN users u ON u.user_id = o.user_id
        WHERE u.email = 'smoke_user@example.com'
    ")->execute();
    $pdo->prepare("
        DELETE oi FROM order_items oi
        JOIN orders o ON o.order_id = oi.order_id
        JOIN users u ON u.user_id = o.user_id
        WHERE u.email = 'smoke_user@example.com'
    ")->execute();
    $pdo->prepare("
        DELETE o FROM orders o
        JOIN users u ON u.user_id = o.user_id
        WHERE u.email = 'smoke_user@example.com'
    ")->execute();
    $pdo->prepare("DELETE FROM users WHERE email = 'smoke_user@example.com'")->execute();
} catch (Throwable $t) {
    // ignore cleanup errors
}

try {
    echo "== Smoke test starting ==\n";

    $users = new UsersDAO();
    $cats  = new CategoriesDAO();
    $prods = new ProductsDAO();
    $orders= new OrdersDAO();
    $items = new OrderItemsDAO();
    $pays  = new PaymentsDAO();
    $revs  = new ReviewsDAO();

    // --- Users ---
    $uid = $users->createUser('Smoke User', $testEmail, password_hash('x', PASSWORD_BCRYPT), 'customer');
    ok($uid > 0, "Users: create");
    $u = $users->findById($uid);
    ok($u && $u['email'] === $testEmail, "Users: read by id");
    ok($users->updateUser($uid, ['name' => 'Smoke User Updated']), "Users: update");
    $u2 = $users->findById($uid);
    ok($u2 && $u2['name'] === 'Smoke User Updated', "Users: verify update");

    // Unique email check (attempt duplicate with SAME email used above)
    $caught = false;
    try {
        $users->createUser('Dup', $testEmail, password_hash('x', PASSWORD_BCRYPT), 'customer');
    } catch (PDOException $e) {
        $caught = $e->getCode() === '23000';
    }
    ok($caught, "Users: unique(email) enforced");

    // --- Categories & Products ---
    $catId = $cats->createCategory('Smoke Category');
    ok($catId > 0, "Categories: create");

    $prodId = $prods->createProduct('Smoke Product', 'Test desc', 12.34, 7, $catId);
    ok($prodId > 0, "Products: create");
    $p = $prods->findById($prodId);
    ok($p && (int)$p['category_id'] === (int)$catId, "Products: read with category_id");

    ok($prods->updateProduct($prodId, ['stock_qty' => 9]), "Products: update");
    $p2 = $prods->findById($prodId);
    ok($p2 && (int)$p2['stock_qty'] === 9, "Products: verify update");

    // Delete category -> product.category_id should become NULL (ON DELETE SET NULL)
    ok($cats->deleteCategory($catId), "Categories: delete");
    $p3 = $prods->findById($prodId);
    ok($p3 && is_null($p3['category_id']), "Products: category_id set NULL after category delete");

    // --- Orders & OrderItems & Payments ---
    $orderId = $orders->createOrder($uid, 'pending', 0.00);
    ok($orderId > 0, "Orders: create");
    ok(!!$orders->findById($orderId), "Orders: read by id");

    $itemId = $items->createItem($orderId, $prodId, 2, 12.34, 'M', 'Black');
    ok($itemId > 0, "OrderItems: create");
    $listItems = $items->getItemsByOrder($orderId);
    ok(count($listItems) === 1 && (int)$listItems[0]['order_item_id'] === (int)$itemId, "OrderItems: list by order");

    ok($items->updateItem($itemId, ['quantity' => 3]), "OrderItems: update");
    $it2 = $items->findById($itemId);
    ok($it2 && (int)$it2['quantity'] === 3, "OrderItems: verify update");

    $payId = $pays->createPayment($orderId, 'credit_card', 24.68, 'USD', 'completed', 'TXN_SMOKE_1');
    ok($payId > 0, "Payments: create");
    $py = $pays->findById($payId);
    ok($py && $py['status'] === 'completed', "Payments: read by id");

    // --- Reviews (unique user+product) ---
    $revId = $revs->createReview($uid, $prodId, 5, 'Nice!');
    ok($revId > 0, "Reviews: create");
    $dupCaught = false;
    try {
        $revs->createReview($uid, $prodId, 4, 'dup should fail');
    } catch (PDOException $e) {
        $dupCaught = $e->getCode() === '23000';
    }
    ok($dupCaught, "Reviews: unique(user_id,product_id) enforced");

    ok($revs->updateReview($revId, ['comment' => 'Nice!!']), "Reviews: update");
    $rv2 = $revs->findById($revId);
    ok($rv2 && $rv2['comment'] === 'Nice!!', "Reviews: verify update");

    // --- Cascades: delete order should remove items & payments (ON DELETE CASCADE) ---
    ok($orders->deleteOrder($orderId), "Orders: delete");
    ok($items->findById($itemId) === null, "OrderItems: cascaded delete verified");
    ok($pays->findById($payId) === null, "Payments: cascaded delete verified");

    // Cleanup residuals
    ok($revs->deleteReview($revId), "Reviews: delete");
    ok($prods->deleteProduct($prodId), "Products: delete");
    ok($users->deleteUser($uid), "Users: delete");

    echo "== Done. Passed: $passes, Failed: $fails ==\n";
    exit($fails > 0 ? 1 : 0);

} catch (Throwable $t) {
    echo "💥 Uncaught error: " . $t->getMessage() . "\n";
    echo $t->getTraceAsString() . "\n";
    exit(1);
}
