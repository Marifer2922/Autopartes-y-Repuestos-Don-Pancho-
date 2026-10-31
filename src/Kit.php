<?php
declare(strict_types=1);



class Kit
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function tiposVehiculo(): array
    {
        return $this->pdo->query(
            'SELECT id, nombre, litros_aceite FROM tipos_vehiculo ORDER BY id'
        )->fetchAll();
    }

    /** @param string $tipo 'aceite' | 'filtro' | 'adicional' */
    public function opciones(string $tipo): array
    {
        $st = $this->pdo->prepare(
            'SELECT id, tipo, nombre, marca, viscosidad, precio
             FROM kit_opciones WHERE tipo = :tipo AND activo = 1 ORDER BY id'
        );
        $st->execute([':tipo' => $tipo]);
        return $st->fetchAll();
    }

    
    public function cotizar(int $tipoVehiculoId, int $aceiteId, array $filtrosIds = [], array $adicionalesIds = []): array
    {
        // Vehiculo
        $st = $this->pdo->prepare('SELECT nombre, litros_aceite FROM tipos_vehiculo WHERE id = :id');
        $st->execute([':id' => $tipoVehiculoId]);
        $vehiculo = $st->fetch();
        if (!$vehiculo) {
            throw new InvalidArgumentException('Selecciona el tipo de vehículo.');
        }

        $aceite = $this->opcionPorId($aceiteId, 'aceite');
        if ($aceite === null) {
            throw new InvalidArgumentException('Selecciona la marca y viscosidad del aceite.');
        }
        $litros     = (float) $vehiculo['litros_aceite'];
        $totalAceite = round($litros * (float) $aceite['precio'], 2);

        $lineas = [[
            'descripcion'     => sprintf(
                'Kit Tune-Up %s: %s %s %s (%s L)',
                $vehiculo['nombre'], $aceite['nombre'], $aceite['marca'], $aceite['viscosidad'],
                rtrim(rtrim(number_format($litros, 1, '.', ''), '0'), '.')
            ),
            'cantidad'        => 1,
            'precio_unitario' => $totalAceite,
            'subtotal'        => $totalAceite,
        ]];

        foreach ([['filtro', $filtrosIds], ['adicional', $adicionalesIds]] as [$tipo, $ids]) {
            foreach (array_unique(array_map('intval', $ids)) as $id) {
                $op = $this->opcionPorId($id, $tipo);
                if ($op === null) {
                    throw new InvalidArgumentException("Una de las opciones de tipo '$tipo' no es válida.");
                }
                $precio  = round((float) $op['precio'], 2);
                $lineas[] = [
                    'descripcion'     => 'Kit Tune-Up: ' . $op['nombre'],
                    'cantidad'        => 1,
                    'precio_unitario' => $precio,
                    'subtotal'        => $precio,
                ];
            }
        }

        $subtotal = round(array_sum(array_column($lineas, 'subtotal')), 2);
        return ['lineas' => $lineas, 'subtotal' => $subtotal];
    }

    private function opcionPorId(int $id, string $tipo): ?array
    {
        $st = $this->pdo->prepare(
            'SELECT id, tipo, nombre, marca, viscosidad, precio
             FROM kit_opciones WHERE id = :id AND tipo = :tipo AND activo = 1'
        );
        $st->execute([':id' => $id, ':tipo' => $tipo]);
        return $st->fetch() ?: null;
    }
}
