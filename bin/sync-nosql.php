<?php
declare(strict_types=1);

/**
 * Initialise / resynchronise la base NoSQL (collection « commandes »)
 * à partir des commandes présentes dans MySQL.
 * Usage : php bin/sync-nosql.php
 */
require dirname(__DIR__) . '/src/bootstrap.php';

$n = (new App\Repository\CommandeRepository())->syncAllToNoSql();
echo sprintf("%d commande(s) synchronisée(s) vers %s\n", $n, App\Service\StatsStore::instance()->driver());
