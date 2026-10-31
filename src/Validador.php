<?php
declare(strict_types=1);


final class Validador
{
  
    public static function rtn(string $valor): ?string
    {
        $limpio = preg_replace('/[\s-]/', '', $valor) ?? '';
        return preg_match('/^\d{14}$/', $limpio) === 1 ? $limpio : null;
    }

    
    public static function telefono(string $valor): ?string
    {
        $limpio = preg_replace('/[\s-]/', '', $valor) ?? '';
        $limpio = preg_replace('/^(\+?504)/', '', $limpio) ?? '';
        return preg_match('/^[2389]\d{7}$/', $limpio) === 1 ? $limpio : null;
    }

    public static function correo(string $valor): ?string
    {
        $v = trim($valor);
        return filter_var($v, FILTER_VALIDATE_EMAIL) !== false ? $v : null;
    }


    public static function formatearRtn(string $rtn): string
    {
        return strlen($rtn) === 14
            ? substr($rtn, 0, 4) . '-' . substr($rtn, 4, 4) . '-' . substr($rtn, 8)
            : $rtn;
    }
}
