<?php

namespace App\Helper;

class Tools
{

    public static function dateToString($date, $format)
    {
        return $date->format($format);
    }

    public static function getDateFormatFromDBDriver($dbDriver)
    {
        switch ($dbDriver) {
            case 'pdo_mysql':
            case 'pdo_sqlsrv': {
                return 'Y-m-d';
                break;
            }

            default: {
                return 'd/m/Y';
                break;
            }
        }
    }

}
