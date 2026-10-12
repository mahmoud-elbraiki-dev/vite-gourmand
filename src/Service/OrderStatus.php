<?php
declare(strict_types=1);

namespace App\Service;

/** Cycle de vie d'une commande (règles de l'espace employé). */
final class OrderStatus
{
    public const EN_ATTENTE = 'en attente';
    public const ACCEPTE = 'accepté';
    public const PREPARATION = 'en préparation';
    public const LIVRAISON = 'en cours de livraison';
    public const LIVRE = 'livré';
    public const RETOUR_MATERIEL = 'en attente du retour de matériel';
    public const TERMINEE = 'terminée';
    public const ANNULEE = 'annulée';

    /** Statuts que l'employé peut appliquer, dans l'ordre du processus. */
    public const WORKFLOW = [
        self::ACCEPTE, self::PREPARATION, self::LIVRAISON, self::LIVRE, self::RETOUR_MATERIEL, self::TERMINEE,
    ];

    public const ALL = [
        self::EN_ATTENTE, self::ACCEPTE, self::PREPARATION, self::LIVRAISON,
        self::LIVRE, self::RETOUR_MATERIEL, self::TERMINEE, self::ANNULEE,
    ];

    public static function badge(string $statut): string
    {
        return match ($statut) {
            self::EN_ATTENTE => 'badge-wait',
            self::ANNULEE => 'badge-cancel',
            self::TERMINEE => 'badge-done',
            self::RETOUR_MATERIEL => 'badge-alert',
            default => 'badge-progress',
        };
    }

    /** Le client peut modifier / annuler tant que la commande n'est pas acceptée. */
    public static function editableByClient(string $statut): bool
    {
        return $statut === self::EN_ATTENTE;
    }
}
