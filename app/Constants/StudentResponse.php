<?php

namespace App\Constants;

class StudentResponse {
    CONST SUCCESS                           = 'success';
    CONST SUCCESS_CREATED                   = 'Student created successfully.';
    CONST SUCCESS_ALL_RETRIEVED             = 'Student retrieved successfully.';
    CONST SUCCESS_RETRIEVED                 = 'Student retrieved successfully.';
    CONST SUCCESS_UPDATED                   = 'Student updated successfully.';
    CONST SUCCESS_DELETED                   = 'Student deleted successfully.';
    CONST NOT_FOUND                         = 'Student is not found';
    CONST UNABLE_CHANGE_ADMIN_ROLE          = 'Unable to Change Student. This Org only have one Admin';
    CONST ERROR                             = 'error';
    CONST EXIST                             = 'Student is already exist';
    CONST IN_USED                           = 'Student is in used. You can`t delete it';
    CONST NOT_AUTHORIZED                    = 'You are not authorized to perform this action';
    CONST ALREADY_ASSIGNED                  = 'User is already assigned to this Student';
}
