<?php
declare(strict_types=1);

namespace App\Service;

/**
 * Règles de calcul du prix (recalculées côté serveur : le prix envoyé
 * par le navigateur n'est jamais pris en compte).
 *  - prix menu = prix par personne × nombre de personnes
 *  - réduction de 10 % si nombre >= minimum + 5
 *  - livraison : 0 € à Bordeaux, sinon 5 € + 0,59 €/km
 */
final class PricingService
{
    public const DISCOUNT_RATE = 0.10;
    public const DISCOUNT_EXTRA_PEOPLE = 5;
    public const DELIVERY_BASE = 5.00;
    public const DELIVERY_PER_KM = 0.59;

    public static function isBordeaux(string $ville): bool
    {
        $v = mb_strtolower(trim($ville));
        $v = strtr($v, ['é' => 'e', 'è' => 'e', '-' => ' ']);
        return $v === 'bordeaux' || preg_match('/^bordeaux( cedex)?( \d+)?$/', $v) === 1;
    }

    public static function compute(float $prixParPersonne, int $minimum, int $nombre, string $ville, float $distanceKm): array
    {
        $brut = round($prixParPersonne * $nombre, 2);
        $reduction = $nombre >= $minimum + self::DISCOUNT_EXTRA_PEOPLE ? round($brut * self::DISCOUNT_RATE, 2) : 0.0;
        $menu = round($brut - $reduction, 2);
        $livraison = self::isBordeaux($ville) ? 0.0 : round(self::DELIVERY_BASE + self::DELIVERY_PER_KM * max(0, $distanceKm), 2);

        return [
            'brut'      => $brut,
            'reduction' => $reduction,
            'menu'      => $menu,
            'livraison' => $livraison,
            'total'     => round($menu + $livraison, 2),
        ];
    }
}
