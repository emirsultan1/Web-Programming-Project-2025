<?php
declare(strict_types=1);

/**
 * Central API routes file.
 * Includes individual route files for each entity.
 */

// Health + basic
require_once __DIR__ . '/healthRoutes.php';

// Core entities
require_once __DIR__ . '/productRoutes.php';
require_once __DIR__ . '/categoryRoutes.php';
require_once __DIR__ . '/userRoutes.php';
require_once __DIR__ . '/orderRoutes.php';
require_once __DIR__ . '/orderItemRoutes.php';
require_once __DIR__ . '/paymentRoutes.php';
require_once __DIR__ . '/reviewRoutes.php';

// Auth (register, login, etc.)
require_once __DIR__ . '/authRoutes.php';
