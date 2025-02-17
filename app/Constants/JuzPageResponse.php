<?php

namespace App\Constants;

class JuzPageResponse {
    CONST SUCCESS                           = 'success';
    CONST SUCCESS_CREATED                   = 'Juz Page created successfully.';
    CONST SUCCESS_ALL_RETRIEVED             = 'Juz Page retrieved successfully.';
    CONST SUCCESS_RETRIEVED                 = 'Juz Page retrieved successfully.';
    CONST SUCCESS_UPDATED                   = 'Juz Page updated successfully.';
    CONST SUCCESS_DELETED                   = 'Juz Page deleted successfully.';
    CONST NOT_FOUND                         = 'Juz Page is not found';
    CONST UNABLE_CHANGE_ADMIN_ROLE          = 'Unable to Change Juz Page. This Org only have one Admin';
    CONST ERROR                             = 'error';
    CONST EXIST                             = 'Juz Page is already exist';
    CONST IN_USED                           = 'Juz Page is in used. You can`t delete it';
    CONST NOT_AUTHORIZED                    = 'You are not authorized to perform this action';
    CONST ALREADY_ASSIGNED                  = 'User is already assigned to this Juz Page';
    CONST START_GREATER_END_PAGE            = 'Start Juz Page is greater End Juz Page';
}
