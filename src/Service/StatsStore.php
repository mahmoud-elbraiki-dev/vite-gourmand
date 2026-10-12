<?php
declare(strict_types=1);

namespace App\Service;

/**
 * Accès aux données NON relationnelles (exigence : statistiques admin
 * issues d'une base NoSQL).
 *
 * Collection « commandes » : un document dénormalisé par commande
 *   { numero, menu_id, menu_titre, montant, nombre_personne, date_commande, statut }
 *
 * - Production : MongoDB (Atlas) via la bibliothèque officielle mongodb/mongodb.
 * - Poste local sans MongoDB : repli automatique sur un magasin de documents JSON
 *   (storage/nosql/commandes.json) exposant exactement la même interface.
 */
final class StatsStore
{
    private static ?self $instance = null;
    private $collection = null;     // \MongoDB\Collection si disponible
    private string $jsonFile;

    private function __construct()
    {
        $this->jsonFile = ROOT_PATH . '/storage/nosql/commandes.json';
        $uri = env('MONGO_URI');
        if ($uri && class_exists(\MongoDB\Client::class)) {
            try {
                $client = new \MongoDB\Client($uri, [], ['typeMap' => ['root' => 'array', 'document' => 'array']]);
                $this->collection = $client->selectCollection(env('MONGO_DB', 'vite_gourmand'), 'commandes');
            } catch (\Throwable $e) {
                error_log('MongoDB indisponible, repli JSON : ' . $e->getMessage());
            }
        }
    }

    public static function instance(): self
    {
        return self::$instance ??= new self();
    }

    public function driver(): string
    {
        return $this->collection ? 'MongoDB' : 'JSON (repli local)';
    }

    /** Insère ou met à jour le document d'une commande (upsert). */
    public function upsert(array $doc): void
    {
        $doc['montant'] = (float) $doc['montant'];
        $doc['menu_id'] = (int) $doc['menu_id'];
        $doc['nombre_personne'] = (int) $doc['nombre_personne'];
        if ($this->collection) {
            $this->collection->updateOne(['numero' => $doc['numero']], ['$set' => $doc], ['upsert' => true]);
            return;
        }
        $this->withJson(function (array $docs) use ($doc) {
            $docs[$doc['numero']] = array_merge($docs[$doc['numero']] ?? [], $doc);
            return $docs;
        });
    }

    public function setStatus(string $numero, string $statut): void
    {
        if ($this->collection) {
            $this->collection->updateOne(['numero' => $numero], ['$set' => ['statut' => $statut]]);
            return;
        }
        $this->withJson(function (array $docs) use ($numero, $statut) {
            if (isset($docs[$numero])) {
                $docs[$numero]['statut'] = $statut;
            }
            return $docs;
        });
    }

    /**
     * Agrégation par menu : nombre de commandes et chiffre d'affaires,
     * filtrable par menu et par période (commandes annulées exclues).
     */
    public function aggregateByMenu(?int $menuId = null, ?string $from = null, ?string $to = null): array
    {
        $match = ['statut' => ['$ne' => OrderStatus::ANNULEE]];
        if ($menuId) {
            $match['menu_id'] = $menuId;
        }
        if ($from || $to) {
            $range = [];
            if ($from) {
                $range['$gte'] = $from . ' 00:00:00';
            }
            if ($to) {
                $range['$lte'] = $to . ' 23:59:59';
            }
            $match['date_commande'] = $range;
        }

        if ($this->collection) {
            $cursor = $this->collection->aggregate([
                ['$match' => $match],
                ['$group' => [
                    '_id' => '$menu_id',
                    'menu_titre' => ['$first' => '$menu_titre'],
                    'nb_commandes' => ['$sum' => 1],
                    'chiffre_affaires' => ['$sum' => '$montant'],
                ]],
                ['$sort' => ['nb_commandes' => -1]],
            ]);
            $rows = [];
            foreach ($cursor as $r) {
                $rows[] = [
                    'menu_id' => (int) $r['_id'],
                    'menu_titre' => $r['menu_titre'],
                    'nb_commandes' => (int) $r['nb_commandes'],
                    'chiffre_affaires' => round((float) $r['chiffre_affaires'], 2),
                ];
            }
            return $rows;
        }

        // Même logique d'agrégation sur le magasin JSON
        $groups = [];
        foreach ($this->readJson() as $d) {
            if (($d['statut'] ?? '') === OrderStatus::ANNULEE) {
                continue;
            }
            if ($menuId && (int) $d['menu_id'] !== $menuId) {
                continue;
            }
            if ($from && $d['date_commande'] < $from . ' 00:00:00') {
                continue;
            }
            if ($to && $d['date_commande'] > $to . ' 23:59:59') {
                continue;
            }
            $id = (int) $d['menu_id'];
            $groups[$id] ??= ['menu_id' => $id, 'menu_titre' => $d['menu_titre'], 'nb_commandes' => 0, 'chiffre_affaires' => 0.0];
            $groups[$id]['nb_commandes']++;
            $groups[$id]['chiffre_affaires'] = round($groups[$id]['chiffre_affaires'] + (float) $d['montant'], 2);
        }
        $rows = array_values($groups);
        usort($rows, fn ($a, $b) => $b['nb_commandes'] <=> $a['nb_commandes']);
        return $rows;
    }

    public function count(): int
    {
        return $this->collection ? (int) $this->collection->countDocuments() : count($this->readJson());
    }

    private function readJson(): array
    {
        if (!is_file($this->jsonFile)) {
            return [];
        }
        return json_decode((string) file_get_contents($this->jsonFile), true) ?: [];
    }

    private function withJson(callable $fn): void
    {
        $fh = fopen($this->jsonFile, 'c+');
        flock($fh, LOCK_EX);
        $content = stream_get_contents($fh);
        $docs = $content ? (json_decode($content, true) ?: []) : [];
        $docs = $fn($docs);
        ftruncate($fh, 0);
        rewind($fh);
        fwrite($fh, json_encode($docs, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        flock($fh, LOCK_UN);
        fclose($fh);
    }
}
