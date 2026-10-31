<?php
declare(strict_types=1);


class Configuracion
{
    private ?array $cache = null;

    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function todas(): array
    {
        if ($this->cache === null) {
            $this->cache = $this->pdo
                ->query('SELECT clave, valor FROM configuracion')
                ->fetchAll(PDO::FETCH_KEY_PAIR);
        }
        return $this->cache;
    }

    public function obtener(string $clave, ?string $porDefecto = null): ?string
    {
        return $this->todas()[$clave] ?? $porDefecto;
    }

    
    public function parametrosFactura(): array
    {
        $c = $this->todas();
        return [
            'isv'            => (float) ($c['isv_porcentaje'] ?? 0.15),
            'descuento_pct'  => (float) ($c['descuento_porcentaje'] ?? 0.10),
            'descuento_min'  => (int)   ($c['descuento_min_unidades'] ?? 10),
            'fletes'         => [
                'mostrador'  => (float) ($c['flete_mostrador'] ?? 0),
                'moto'       => (float) ($c['flete_moto'] ?? 50),
                'camioncito' => (float) ($c['flete_camioncito'] ?? 180),
            ],
        ];
    }
}
