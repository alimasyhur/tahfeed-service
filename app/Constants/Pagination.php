<?php

namespace App\Constants;

class Pagination
{
    const ASC_QUERY = 'asc';
    const DESC_QUERY = 'desc';

    const ASC_PARAM = '1';
    const DESC_PARAM = '-1';

    const SORT_OPTIONS = [
        Pagination::ASC_PARAM => Pagination::ASC_QUERY,
        Pagination::DESC_PARAM => Pagination::DESC_QUERY,
    ];
}
