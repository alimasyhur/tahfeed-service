<?php

namespace App\Http\Controllers\Api;

use App\Constants\Pagination;
use App\Constants\ReportResponse;
use App\Helpers\CommonHelper;
use App\Http\Controllers\Controller;
use App\Repositories\SummaryRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SummaryController extends Controller
{
    protected $repository;
    protected $studentRepository;
    protected $orgRepository;
    protected $kelasRepository;
    protected $teacherRepository;
    protected $juzPageRepository;
    public function __construct(
        SummaryRepository $repository,
    )
    {
        $this->repository = $repository;
    }

    public function index(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'q' => 'nullable|string',
                'filter.org_uuid' => 'nullable|string',
                'page' => 'nullable|integer',
                'limit' => 'nullable|integer',
                'sortOrder' => sprintf('nullable|string|in:%s,%s', Pagination::ASC_PARAM, Pagination::DESC_PARAM),
                'sortField' => 'nullable|string',
            ])->safe()->all();

            $reports = $this->repository->listSummary($validator);

            return response()->json([
                'status' => ReportResponse::SUCCESS,
                'message' => ReportResponse::SUCCESS_ALL_RETRIEVED,
                'data' => $reports,
            ]);

        } catch (\Throwable $th) {
            $errMessage = $th->getMessage();
            $errCode = CommonHelper::getStatusCode($errMessage);

            return response()->json([
                'status' => ReportResponse::ERROR,
                'message' => $errMessage,
            ], $errCode);
        }
    }
}
