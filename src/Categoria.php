<?php
declare(strict_types=1);

class Categoria
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function listar(): array
    {
        return $this->pdo->query('SELECT id, nombre, descripcion FROM categorias ORDER BY id')->fetchAll();
    }

    public function obtener(int $id): ?array
    {
        $st = $this->pdo->prepare('SELECT id, nombre, descripcion FROM categorias WHERE id = :id');
        $st->execute([':id' => $id]);
        return $st->fetch() ?: null;
    }

    public function listarMarcas(): array
    {
        return $this->pdo->query('SELECT id, nombre FROM marcas_vehiculo ORDER BY id')->fetchAll();
    }
}
