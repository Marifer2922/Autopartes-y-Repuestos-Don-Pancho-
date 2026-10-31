<?php
declare(strict_types=1);


class Repuesto
{
    private const SELECT_BASE =
        'SELECT r.id, r.codigo, r.nombre, r.descripcion, r.especificaciones,
                r.categoria_id, c.nombre AS categoria,
                r.marca_id, m.nombre AS marca,
                r.precio, r.stock, r.fotografia, r.destacado, r.activo
         FROM repuestos r
         INNER JOIN categorias c ON c.id = r.categoria_id
         LEFT JOIN marcas_vehiculo m ON m.id = r.marca_id';

    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

 
    public function listar(?int $categoriaId = null, ?string $busqueda = null, bool $soloActivos = true): array
    {
        $where  = [];
        $params = [];

        if ($soloActivos) {
            $where[] = 'r.activo = 1';
        }
        if ($categoriaId !== null) {
            $where[]               = 'r.categoria_id = :categoria';
            $params[':categoria']  = $categoriaId;
        }
        if ($busqueda !== null && trim($busqueda) !== '') {
            
            $where[]       = '(r.nombre LIKE :q1 OR r.codigo LIKE :q2 OR r.especificaciones LIKE :q3)';
            $texto         = '%' . trim($busqueda) . '%';
            $params[':q1'] = $texto;
            $params[':q2'] = $texto;
            $params[':q3'] = $texto;
        }

        $sql = self::SELECT_BASE
             . ($where ? ' WHERE ' . implode(' AND ', $where) : '')
             . ' ORDER BY c.id, r.nombre';

        $st = $this->pdo->prepare($sql);
        $st->execute($params);
        return $st->fetchAll();
    }

    public function destacados(int $limite = 5): array
    {
        $st = $this->pdo->prepare(
            self::SELECT_BASE . ' WHERE r.destacado = 1 AND r.activo = 1 ORDER BY r.id LIMIT :limite'
        );
        $st->bindValue(':limite', $limite, PDO::PARAM_INT);
        $st->execute();
        return $st->fetchAll();
    }

    public function obtener(int $id): ?array
    {
        $st = $this->pdo->prepare(self::SELECT_BASE . ' WHERE r.id = :id');
        $st->execute([':id' => $id]);
        return $st->fetch() ?: null;
    }

    
    public function crear(array $datos): int
    {
        $d = $this->validar($datos);

        try {
            $st = $this->pdo->prepare(
                'INSERT INTO repuestos
                   (codigo, nombre, descripcion, especificaciones, categoria_id, marca_id,
                    precio, stock, fotografia, destacado)
                 VALUES
                   (:codigo, :nombre, :descripcion, :especificaciones, :categoria_id, :marca_id,
                    :precio, :stock, :fotografia, :destacado)'
            );
            $st->execute($d);
        } catch (PDOException $e) {
            $this->traducirErrorDuplicado($e);
        }
        return (int) $this->pdo->lastInsertId();
    }

    
    public function actualizar(int $id, array $datos): bool
    {
        $actual = $this->obtener($id);
        if ($actual === null) {
            throw new RuntimeException('El repuesto no existe.');
        }

        $d = $this->validar($datos);
        if ($d[':fotografia'] === null) {
            $d[':fotografia'] = $actual['fotografia'];
        }
        $d[':id'] = $id;

        try {
            $st = $this->pdo->prepare(
                'UPDATE repuestos SET
                   codigo = :codigo, nombre = :nombre, descripcion = :descripcion,
                   especificaciones = :especificaciones, categoria_id = :categoria_id,
                   marca_id = :marca_id, precio = :precio, stock = :stock,
                   fotografia = :fotografia, destacado = :destacado
                 WHERE id = :id'
            );
            return $st->execute($d);
        } catch (PDOException $e) {
            $this->traducirErrorDuplicado($e);
        }
    }

   
    public function eliminar(int $id): ?string
    {
        $actual = $this->obtener($id);
        if ($actual === null) {
            throw new RuntimeException('El repuesto no existe.');
        }
        $st = $this->pdo->prepare('DELETE FROM repuestos WHERE id = :id');
        $st->execute([':id' => $id]);
        return $actual['fotografia'];
    }

   
    public function descontarStock(int $id, int $cantidad): bool
    {
        $st = $this->pdo->prepare(
            'UPDATE repuestos SET stock = stock - :c WHERE id = :id AND stock >= :c2'
        );
        $st->execute([':c' => $cantidad, ':id' => $id, ':c2' => $cantidad]);
        return $st->rowCount() === 1;
    }

    
    private function validar(array $d): array
    {
        $codigo = strtoupper(trim((string) ($d['codigo'] ?? '')));
        $nombre = trim((string) ($d['nombre'] ?? ''));

        if ($codigo === '' || strlen($codigo) > 30) {
            throw new InvalidArgumentException('El código es obligatorio (máximo 30 caracteres).');
        }
        if ($nombre === '' || strlen($nombre) > 120) {
            throw new InvalidArgumentException('El nombre es obligatorio (máximo 120 caracteres).');
        }

        $precio = filter_var($d['precio'] ?? null, FILTER_VALIDATE_FLOAT);
        if ($precio === false || $precio < 0) {
            throw new InvalidArgumentException('El precio debe ser un número mayor o igual a 0.');
        }

        $stock = filter_var($d['stock'] ?? null, FILTER_VALIDATE_INT);
        if ($stock === false || $stock < 0) {
            throw new InvalidArgumentException('El stock debe ser un entero mayor o igual a 0.');
        }

        $categoria = filter_var($d['categoria_id'] ?? null, FILTER_VALIDATE_INT);
        if ($categoria === false) {
            throw new InvalidArgumentException('Selecciona una categoría.');
        }

        $marca = $d['marca_id'] ?? null;
        $marca = ($marca === null || $marca === '') ? null : filter_var($marca, FILTER_VALIDATE_INT);
        if ($marca === false) {
            throw new InvalidArgumentException('Marca no válida.');
        }

        $foto = $d['fotografia'] ?? null;

        return [
            ':codigo'  => $codigo,
            ':nombre'  => $nombre,
            ':descripcion' => $this->textoOpcional($d['descripcion'] ?? null),
            ':especificaciones' => $this->textoOpcional($d['especificaciones'] ?? null),
            ':categoria_id'  => $categoria,
            ':marca_id' => $marca,
            ':precio'   => round((float) $precio, 2),
            ':stock'  => $stock,
            ':fotografia'   => ($foto === null || $foto === '') ? null : (string) $foto,
            ':destacado'=> empty($d['destacado']) ? 0 : 1,
        ];
    }

    private function textoOpcional(mixed $valor): ?string
    {
        $t = trim((string) ($valor ?? ''));
        return $t === '' ? null : mb_substr($t, 0, 255);
    }

    private function traducirErrorDuplicado(PDOException $e): never
    {
        if ($e->getCode() === '23000') {
            $mensaje = str_contains($e->getMessage(), 'codigo')
                ? 'Ya existe un repuesto con ese código.'
                : 'La categoría o la marca seleccionada no existe.';
            throw new RuntimeException($mensaje);
        }
        throw $e;
    }
}
