<?php
declare(strict_types=1);

require_once __DIR__ . '/Configuracion.php';

class Factura
{
    private PDO $pdo;
    private Configuracion $config;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
        $this->config = new Configuracion($pdo);
    }

    public function calcular(float $precioUnitario, int $cantidad, string $tipoFlete): array
    {
        $params = $this->config->parametrosFactura();

        $subtotal = round($precioUnitario * $cantidad, 2);

        $porcentajeDesc = 0.00;
        $descEtiqueta = "0%";
        if ($cantidad >= $params['descuento_min']) {
            $porcentajeDesc = (float)$params['descuento_pct'];
            $descEtiqueta = "10% (Mayorista)";
        } elseif ($cantidad >= 3) {
            $porcentajeDesc = 0.05;
            $descEtiqueta = "5% (Volumen)";
        }

        $descuento = round($subtotal * $porcentajeDesc, 2);
        $baseGravable = $subtotal - $descuento;
        $isv = round($baseGravable * (float)$params['isv'], 2);
        $costoFlete = (float)($params['fletes'][$tipoFlete] ?? 0.00);
        $total = round($baseGravable + $isv + $costoFlete, 2);

        return [
            'subtotal' => $subtotal,
            'descuento' => $descuento,
            'pct_descuento' => $descEtiqueta,
            'base_gravable' => $baseGravable,
            'isv' => $isv,
            'costo_flete' => $costoFlete,
            'total' => $total,
        ];
    }

    private function generarCorrelativo(): string
    {
        $st = $this->pdo->query('SELECT id, prefijo, siguiente, rango_final FROM correlativo_factura LIMIT 1 FOR UPDATE');
        $corr = $st ? $st->fetch(PDO::FETCH_ASSOC) : null;

        if ($corr) {
            $prefijo = $corr['prefijo'];
            $sig = (int)$corr['siguiente'];
            $numero = sprintf("%s-%08d", $prefijo, $sig);

            $up = $this->pdo->prepare('UPDATE correlativo_factura SET siguiente = siguiente + 1 WHERE id = :id');
            $up->execute([':id' => $corr['id']]);
            return $numero;
        }

        return sprintf("000-001-01-%08d", random_int(1000, 99999999));
    }

    public function registrar(int $clienteId, int $sucursalId, string $tipoFlete, array $totales, array $items): array
    {
        $this->pdo->beginTransaction();
        try {
            $numeroFactura = $this->generarCorrelativo();

            $sqlFactura = 'INSERT INTO facturas 
                (numero_factura, cliente_id, sucursal_id, fecha, subtotal, descuento, isv, flete_tipo, flete_monto, total)
                VALUES (:num, :cli, :suc, NOW(), :sub, :desc, :isv, :ftipo, :fmonto, :tot)';

            $stFact = $this->pdo->prepare($sqlFactura);
            $stFact->execute([
                ':num' => $numeroFactura,
                ':cli' => $clienteId,
                ':suc' => $sucursalId,
                ':sub' => $totales['subtotal'],
                ':desc' => $totales['descuento'],
                ':isv' => $totales['isv'],
                ':ftipo' => $tipoFlete,
                ':fmonto' => $totales['costo_flete'],
                ':tot' => $totales['total'],
            ]);
            $facturaId = (int)$this->pdo->lastInsertId();

            $sqlDet = 'INSERT INTO factura_detalle 
                (factura_id, repuesto_id, descripcion, cantidad, precio_unitario, subtotal)
                VALUES (:fid, :rid, :des, :cant, :pre, :sub)';
            $stDet = $this->pdo->prepare($sqlDet);

            foreach ($items as $item) {
                $stDet->execute([
                    ':fid' => $facturaId,
                    ':rid' => $item['repuesto_id'] ?? null,
                    ':des' => $item['descripcion'],
                    ':cant' => $item['cantidad'],
                    ':pre' => $item['precio_unitario'],
                    ':sub' => round($item['cantidad'] * $item['precio_unitario'], 2),
                ]);
            }

            $this->pdo->commit();
            return array_merge(['id' => $facturaId, 'numero_factura' => $numeroFactura], $totales);
        } catch (Throwable $t) {
            $this->pdo->rollBack();
            throw $t;
        }
    }
}