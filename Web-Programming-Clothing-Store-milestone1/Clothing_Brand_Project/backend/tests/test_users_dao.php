<?php
declare(strict_types=1);

require_once __DIR__ . '/../dao/UsersDAO.php';

// init DAO
$dao = new UsersDAO();

// 1) Create
echo "Creating user...\n";
$userId = $dao->createUser(
  'Alice Example',
  'alice@example.com',
  password_hash('123456', PASSWORD_BCRYPT),
  'customer'
);
echo "✅ Created user_id = $userId\n";

// 2) Read by id
$user = $dao->findById($userId);
echo "Fetched by ID:\n";
print_r($user);

// 3) Update
$ok = $dao->updateUser($userId, ['name' => 'Alice Updated', 'role' => 'admin']);
echo $ok ? "✅ Updated user\n" : "❌ Update failed\n";
print_r($dao->findById($userId));

// 4) Read by email
$byEmail = $dao->findByEmail('alice@example.com');
echo "Fetched by email:\n";
print_r($byEmail);

// 5) List some users
$list = $dao->listUsers(5, 0);
echo "List users (first 5):\n";
print_r($list);

// 6) Delete
$ok = $dao->deleteUser($userId);
echo $ok ? "✅ Deleted user $userId\n" : "❌ Delete failed\n";
