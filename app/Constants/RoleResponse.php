<?php

namespace App\Constants;

class RoleResponse {

    CONST SUCCESS                           = 'success';
    CONST SUCCESS_CREATED                   = 'Role created successfully.';
    CONST SUCCESS_ALL_RETRIEVED             = 'Role retrieved successfully.';
    CONST SUCCESS_RETRIEVED                 = 'Role retrieved successfully.';
    CONST SUCCESS_UPDATED                   = 'Role updated successfully.';
    CONST SUCCESS_DELETED                   = 'Role deleted successfully.';
    CONST NOT_FOUND                         = 'Role is not found';
    CONST UNABLE_CHANGE_ADMIN_ROLE          = 'Unable to Change Role. This Org only have one Admin';
    CONST ERROR                             = 'error';
    CONST EXIST                             = 'Role is already exist';
    CONST IN_USED                           = 'Role is in used. You can`t delete it';
    CONST NOT_AUTHORIZED                    = 'You are not authorized to perform this action';
    CONST ALREADY_ASSIGNED                  = 'User is already assigned to this Role';
}
