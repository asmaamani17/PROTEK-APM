<?php

namespace App\Helpers;

class DaerahCoordinates
{
    public static function getCoordinates($daerah)
    {
        $coordinates = [
            'BATU PAHAT' => [
                'lat' => 1.8548,
                'lng' => 102.9325
            ],
            'SEGAMAT' => [
                'lat' => 2.5148,
                'lng' => 102.8158
            ],
            'KOTA TINGGI' => [
                'lat' => 1.7381,
                'lng' => 103.8997
            ],
            'KLUANG' => [
                'lat' => 2.0251,
                'lng' => 103.3328
            ]
        ];

        return $coordinates[$daerah] ?? null;
    }

    public static function getAllCoordinates()
    {
        return [
            'BATU PAHAT' => [
                'lat' => 1.8548,
                'lng' => 102.9325
            ],
            'SEGAMAT' => [
                'lat' => 2.5148,
                'lng' => 102.8158
            ],
            'KOTA TINGGI' => [
                'lat' => 1.7381,
                'lng' => 103.8997
            ],
            'KLUANG' => [
                'lat' => 2.0251,
                'lng' => 103.3328
            ]
        ];
    }
} 