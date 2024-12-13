<?php

namespace App\Http\Controllers\Api;

use App\Constants\Pagination;
use App\Constants\TemplateQuranResponse;
use App\Helpers\CommonHelper;
use App\Http\Controllers\Controller;
use App\Repositories\TemplateQuranRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TemplateQuranController extends Controller
{
    protected $repository;
    protected $roleUserRepository;
    public function __construct(
        TemplateQuranRepository $repository,
    )
    {
        $this->repository = $repository;
    }

    public function index(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'q' => 'nullable|string',
                'filter.name' => 'nullable|string',
                'filter.description' => 'nullable|string',
                'page' => 'nullable|integer',
                'limit' => 'nullable|integer',
                'sortOrder' => sprintf('nullable|string|in:%s,%s', Pagination::ASC_PARAM, Pagination::DESC_PARAM),
                'sortField' => 'nullable|string',
            ])->safe()->all();

            $roles = $this->repository->browse($validator);
            $totalRoles = $this->repository->count($validator);

            return response()->json([
                'status' => TemplateQuranResponse::SUCCESS,
                'message' => TemplateQuranResponse::SUCCESS_ALL_RETRIEVED,
                'data' => $roles,
                'total' => $totalRoles,
            ]);

        } catch (\Throwable $th) {
            $errMessage = $th->getMessage();
            $errCode = CommonHelper::getStatusCode($errMessage);

            return response()->json([
                'status' => TemplateQuranResponse::ERROR,
                'message' => $errMessage,
            ], $errCode);
        }
    }

    public function show($uuid)
    {
        $templateQuran = $this->repository->findWIthJuz($uuid);

        if(empty($templateQuran)) {
            return response()->json([
                'status' => TemplateQuranResponse::SUCCESS,
                'message' => TemplateQuranResponse::NOT_FOUND,
                'error' => true,
                'data' => [],
            ], 404);
        }

        return response()->json([
            'status' => TemplateQuranResponse::SUCCESS,
            'message' => TemplateQuranResponse::SUCCESS_RETRIEVED,
            'data' => $templateQuran,
        ]);
    }
}
