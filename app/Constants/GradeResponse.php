<?php

namespace App\Constants;

class GradeResponse {

    CONST SUCCESS                           = 'success';
    CONST SUCCESS_CREATED                   = 'Grade created successfully.';
    CONST SUCCESS_ALL_RETRIEVED             = 'Grade retrieved successfully.';
    CONST SUCCESS_RETRIEVED                 = 'Grade retrieved successfully.';
    CONST SUCCESS_UPDATED                   = 'Grade updated successfully.';
    CONST SUCCESS_DELETED                   = 'Grade deleted successfully.';
    CONST NOT_FOUND                         = 'Grade is not found';
    CONST UNABLE_CHANGE_ADMIN_ROLE          = 'Unable to Change Grade. This Org only have one Admin';
    CONST ERROR                             = 'error';
    CONST EXIST                             = 'Grade is already exist';
    CONST IN_USED                           = 'Grade is in used. You can`t delete it';
    CONST NOT_AUTHORIZED                    = 'You are not authorized to perform this action';
    CONST ALREADY_ASSIGNED                  = 'User is already assigned to this Grade';
    CONST HAS_ACTIVE_STUDENT                = 'Unable to delete Grade. Grade has active student.';
}
