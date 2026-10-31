<?php
declare(strict_types=1);


class Sucursal
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function listar(): array
    {
        return $this->pdo->query(
            'SELECT id, nombre, ciudad, direccion, telefono, horario
             FROM sucursales ORDER BY ciudad, nombre'
        )->fetchAll();
    }

    public function listarPorCiudad(): array
    {
        $grupos = [];
        foreach ($this->listar() as $s) {
            $grupos[$s['ciudad']][] = $s;
        }
        return $grupos;
    }

    public function obtener(int $id): ?array
    {
        $st = $this->pdo->prepare(
            'SELECT id, nombre, ciudad, direccion, telefono, horario FROM sucursales WHERE id = :id'
        );
        $st->execute([':id' => $id]);
        return $st->fetch() ?: null;
    }
}
