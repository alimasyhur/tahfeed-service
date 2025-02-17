<?php

namespace App\Constants;

class Sorting
{
    const ASC_QUERY = 'asc';
    const DESC_QUERY = 'desc';

    const ASC_PARAM = '1';
    const DESC_PARAM = '-1';

    const ORDER_OPTIONS = [
        Sorting::ASC_PARAM => Sorting::ASC_QUERY,
        Sorting::ASC_QUERY => Sorting::ASC_QUERY,
        Sorting::DESC_PARAM => Sorting::DESC_QUERY,
        Sorting::DESC_QUERY => Sorting::DESC_QUERY,
    ];
}
