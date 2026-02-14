<?php

declare(strict_types=1);

namespace Mint\Core\Database;

use PDO;

class MigrationRunner
{
    /**
     * @param  PDO  $pdo
     * @param  MigrationRepository  $repository
     */
    public function __construct(
        private PDO $pdo,
        private MigrationRepository $repository
    ) {
    }

    /**
     * Run all pending migrations.
     *
     * @param  array<int, MigrationInterface>  $migrations
     *
     * @return void
     */
    public function run(array $migrations): void
    {
        $executed = $this->repository->getExecuted();

        foreach ($migrations as $migration) {
            if (!in_array($migration->getName(), $executed)) {
                echo "Running migration: {$migration->getName()}\n";

                $migration->up($this->pdo);
                $this->repository->markAsExecuted($migration->getName());
            }
        }
    }
}
