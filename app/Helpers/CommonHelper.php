<?php

namespace App\Helpers;

use App\Constants\Sorting;
use App\Models\Spo;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Builder;

class CommonHelper
{
    public static function getOffset(int $page, int $limit)
    {
        return ($page - 1) * $limit;
    }

    public static function sortPageFilter(Builder $query, $q_params = null) {
        $limit = 10;
        if (isset($q_params['limit'])) {
            $limit = $q_params['limit'];
            $query->limit($limit);
        }

        if (isset($q_params['page'])) {
            if (!isset($q_params['limit'])) {
                $query->limit($limit);
            }
            $query->offset(CommonHelper::getOffset($q_params['page'], $limit));
        }

        if (isset($q_params['sortOrder']) && isset($q_params['sortField'])) {
            $query->orderBy($q_params['sortField'], Sorting::ORDER_OPTIONS[$q_params['sortOrder']]);
        } else {
            $query->orderBy('created_at', 'asc');
        }
    }

    public static function num2roman($angka)
    {

        $angka = intval($angka);
        $result = '';

        $array = array(
            'M' => 1000,
            'CM' => 900,
            'D' => 500,
            'CD' => 400,
            'C' => 100,
            'XC' => 90,
            'L' => 50,
            'XL' => 40,
            'X' => 10,
            'IX' => 9,
            'V' => 5,
            'IV' => 4,
            'I' => 1
        );

        foreach ($array as $roman => $value) {
            $matches = intval($angka / $value);

            $result .= str_repeat($roman, $matches);

            $angka = $angka % $value;
        }

        return $result;
    }

    public static function getStatusCode($message)
    {
        $code = 422;

        if ($message == 'This action is unauthorized.') {
            $code = 403;
        }

        if (Str::contains($message, 'No query results')) {
            $code = 404;
        }

        return $code;
    }
}
