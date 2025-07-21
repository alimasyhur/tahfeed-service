<?php

namespace App\Constants;

class KelasResponse {

    CONST SUCCESS                           = 'success';
    CONST SUCCESS_CREATED                   = 'Class created successfully.';
    CONST SUCCESS_ALL_RETRIEVED             = 'Class retrieved successfully.';
    CONST SUCCESS_RETRIEVED                 = 'Class retrieved successfully.';
    CONST SUCCESS_UPDATED                   = 'Class updated successfully.';
    CONST SUCCESS_DELETED                   = 'Class deleted successfully.';
    CONST NOT_FOUND                         = 'Class is not found';
    CONST UNABLE_CHANGE_ADMIN_ROLE          = 'Unable to Change Class. This Org only have one Admin';
    CONST ERROR                             = 'error';
    CONST EXIST                             = 'Class is already exist';
    CONST IN_USED                           = 'Class is in used. You can`t delete it';
    CONST NOT_AUTHORIZED                    = 'You are not authorized to perform this action';
    CONST ALREADY_ASSIGNED                  = 'Student is already assigned to this Class';
    CONST HAS_ACTIVE_STUDENT                = 'Unable to delete Class. Class has active student.';
    CONST HAS_ACTIVE_CLASS                  = 'Unable to Create CLass. Teacher has another active class.';
    CONST HAS_ACTIVE_CLASS_ASSIGNED         = 'Student is already assigned to Active Class';
}
