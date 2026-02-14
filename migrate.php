<?php

declare(strict_types=1);

require __DIR__ . '/bootstrap.php';

use Mint\Core\Database\Database;
use Mint\Core\Database\MigrationInterface;
use Mint\Core\Database\MigrationRepository;
use Mint\Core\Database\MigrationRunner;

$migrationFiles = glob(__DIR__ . '/database/Migrations/*.php');

foreach ($migrationFiles as $file) {
    require_once $file;
}

$migrations = [];

foreach (get_declared_classes() as $class) {
    if (in_array(MigrationInterface::class, class_implements($class))) {
        $migrations[] = new $class();
    }
}

$pdo = Database::getConnection();

$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$repository = new MigrationRepository($pdo);
$runner = new MigrationRunner($pdo, $repository);

$runner->run($migrations);
