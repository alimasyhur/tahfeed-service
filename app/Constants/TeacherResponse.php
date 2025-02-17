<?php

namespace App\Constants;

class TeacherResponse {
    CONST SUCCESS                           = 'success';
    CONST SUCCESS_CREATED                   = 'Teacher created successfully.';
    CONST SUCCESS_ALL_RETRIEVED             = 'Teacher retrieved successfully.';
    CONST SUCCESS_RETRIEVED                 = 'Teacher retrieved successfully.';
    CONST SUCCESS_UPDATED                   = 'Teacher updated successfully.';
    CONST SUCCESS_DELETED                   = 'Teacher deleted successfully.';
    CONST NOT_FOUND                         = 'Teacher is not found';
    CONST UNABLE_CHANGE_ADMIN_ROLE          = 'Unable to Change Teacher. This Org only have one Admin';
    CONST ERROR                             = 'error';
    CONST EXIST                             = 'Teacher is already exist';
    CONST IN_USED                           = 'Teacher is in used. You can`t delete it';
    CONST NOT_AUTHORIZED                    = 'You are not authorized to perform this action';
    CONST ALREADY_ASSIGNED                  = 'User is already assigned to this Teacher';
}
