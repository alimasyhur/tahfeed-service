<?php

namespace App\Constants;

class ReportResponse {

    CONST SUCCESS                           = 'success';
    CONST SUCCESS_CREATED                   = 'Report created successfully.';
    CONST SUCCESS_ALL_RETRIEVED             = 'Report retrieved successfully.';
    CONST SUCCESS_RETRIEVED                 = 'Report retrieved successfully.';
    CONST SUCCESS_UPDATED                   = 'Report updated successfully.';
    CONST SUCCESS_DELETED                   = 'Report deleted successfully.';
    CONST NOT_FOUND                         = 'Report is not found';
    CONST UNABLE_CHANGE_ADMIN_ROLE          = 'Unable to Change Role. This Org only have one Admin';
    CONST ERROR                             = 'error';
    CONST EXIST                             = 'Report is already exist';
    CONST IN_USED                           = 'Report is in used. You can`t delete it';
    CONST NOT_AUTHORIZED                    = 'You are not authorized to perform this action';
    CONST ALREADY_ASSIGNED                  = 'User is already assigned to this Role';
    CONST ALREADY_LOCKED                     = 'Report is already locked. Unable to modify report.';
}
