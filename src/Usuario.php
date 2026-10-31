<?php
declare(strict_types=1);



class Usuario
{
    public const ROL_ADMIN     = 'admin';
    public const ROL_BODEGUERO = 'bodeguero';

    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function autenticar(string $correo, string $password): ?array
    {
        $sql = 'SELECT u.id, u.nombre, u.correo, u.password_hash, r.nombre AS rol
                FROM usuarios u
                INNER JOIN roles r ON r.id = u.rol_id
                WHERE u.correo = :correo AND u.activo = 1
                LIMIT 1';
        $st = $this->pdo->prepare($sql);
        $st->execute([':correo' => trim($correo)]);
        $usuario = $st->fetch();

        if (!$usuario || !password_verify($password, $usuario['password_hash'])) {
            return null;
        }

        if (password_needs_rehash($usuario['password_hash'], PASSWORD_DEFAULT)) {
            $up = $this->pdo->prepare('UPDATE usuarios SET password_hash = :h WHERE id = :id');
            $up->execute([':h' => password_hash($password, PASSWORD_DEFAULT), ':id' => $usuario['id']]);
        }

        unset($usuario['password_hash']);
        return $usuario;
    }

    public function obtener(int $id): ?array
    {
        $st = $this->pdo->prepare(
            'SELECT u.id, u.nombre, u.correo, u.activo, r.nombre AS rol
             FROM usuarios u INNER JOIN roles r ON r.id = u.rol_id
             WHERE u.id = :id'
        );
        $st->execute([':id' => $id]);
        return $st->fetch() ?: null;
    }

    public function listar(): array
    {
        return $this->pdo->query(
            'SELECT u.id, u.nombre, u.correo, u.activo, r.nombre AS rol
             FROM usuarios u INNER JOIN roles r ON r.id = u.rol_id
             ORDER BY u.nombre'
        )->fetchAll();
    }

    
    public function crear(string $nombre, string $correo, string $password, string $rol): int
    {
        if ($nombre === '' || !filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('Nombre o correo no validos.');
        }
        if (strlen($password) < 8) {
            throw new InvalidArgumentException('La contraseña debe tener al menos 8 caracteres.');
        }

        $st = $this->pdo->prepare('SELECT id FROM roles WHERE nombre = :rol');
        $st->execute([':rol' => $rol]);
        $rolId = $st->fetchColumn();
        if ($rolId === false) {
            throw new InvalidArgumentException('Rol no valido.');
        }

        try {
            $ins = $this->pdo->prepare(
                'INSERT INTO usuarios (nombre, correo, password_hash, rol_id)
                 VALUES (:nombre, :correo, :hash, :rol)'
            );
            $ins->execute([
                ':nombre' => trim($nombre),
                ':correo' => trim($correo),
                ':hash'   => password_hash($password, PASSWORD_DEFAULT),
                ':rol'    => $rolId,
            ]);
        } catch (PDOException $e) {
            if ($e->getCode() === '23000') {
                throw new RuntimeException('Ya existe un usuario con ese correo.');
            }
            throw $e;
        }
        return (int) $this->pdo->lastInsertId();
    }

    public static function rolPuedeAdministrar(?string $rol): bool
    {
        return in_array($rol, [self::ROL_ADMIN, self::ROL_BODEGUERO], true);
    }
}
