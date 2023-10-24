<?php

namespace App\Enum;

use Symfony\Component\HttpFoundation\Response;
use ReflectionClass;

class CodeResponseType extends Response
{
    protected static $descriptions = [];
    protected $value;

    protected static function getValues(): array
    {
        static $constants = null;
        if (!$constants) {
            // Get constants
            $refl = new ReflectionClass(get_called_class());

            $constants = array_flip($refl->getConstants());
            ksort($constants);

            // Map descriptions
            foreach (static::$descriptions as $constValue => $description) {
                $constants[$constValue] = $description;
            }
        }

        return $constants;
    }

    public static function getAll(): array
    {
        return static::getValues();
    }
}
