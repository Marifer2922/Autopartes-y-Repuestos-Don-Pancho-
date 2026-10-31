<?php
declare(strict_types=1);

require_once __DIR__ . '/Validador.php';

class Cliente
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function obtener(int $id): ?array
    {
        $st = $this->pdo->prepare('SELECT * FROM clientes WHERE id = :id');
        $st->execute([':id' => $id]);
        return $st->fetch() ?: null;
    }

    public function buscarPorRtn(string $rtn): ?array
    {
        $normalizado = Validador::rtn($rtn);
        if ($normalizado === null) {
            return null;
        }
        $st = $this->pdo->prepare('SELECT * FROM clientes WHERE rtn = :rtn');
        $st->execute([':rtn' => $normalizado]);
        return $st->fetch() ?: null;
    }

    
    public function guardar(array $datos): int
    {
        $nombre = trim((string) ($datos['nombre'] ?? ''));
        if ($nombre === '' || mb_strlen($nombre) > 120) {
            throw new InvalidArgumentException('El nombre del cliente es obligatorio.');
        }

        $rtn = Validador::rtn((string) ($datos['rtn'] ?? ''));
        if ($rtn === null) {
            throw new InvalidArgumentException('El RTN debe tener 14 dígitos.');
        }

        $telefono = Validador::telefono((string) ($datos['telefono'] ?? ''));
        if ($telefono === null) {
            throw new InvalidArgumentException('El teléfono debe tener 8 dígitos (ejemplo: 9999-0000).');
        }

        $correoCrudo = trim((string) ($datos['correo'] ?? ''));
        $correo = null;
        if ($correoCrudo !== '') {
            $correo = Validador::correo($correoCrudo);
            if ($correo === null) {
                throw new InvalidArgumentException('El correo no es válido.');
            }
        }

        $direccion = trim((string) ($datos['direccion'] ?? ''));
        $direccion = $direccion === '' ? null : mb_substr($direccion, 0, 200);

        $existente = $this->buscarPorRtn($rtn);
        $params = [
            ':nombre'    => $nombre,
            ':telefono'  => $telefono,
            ':correo'    => $correo,
            ':direccion' => $direccion,
        ];

        if ($existente !== null) {
            $st = $this->pdo->prepare(
                'UPDATE clientes SET nombre = :nombre, telefono = :telefono,
                        correo = :correo, direccion = :direccion
                 WHERE id = :id'
            );
            $st->execute($params + [':id' => $existente['id']]);
            return (int) $existente['id'];
        }

        $st = $this->pdo->prepare(
            'INSERT INTO clientes (nombre, rtn, telefono, correo, direccion)
             VALUES (:nombre, :rtn, :telefono, :correo, :direccion)'
        );
        $st->execute($params + [':rtn' => $rtn]);
        return (int) $this->pdo->lastInsertId();
    }
}
