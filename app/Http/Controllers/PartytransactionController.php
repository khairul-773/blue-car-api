<?php

namespace App\Http\Controllers;

use App\Models\Partytransaction;
use App\Models\Supplier;
use App\Models\Showroom;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;
use App\Traits\ApiResponse;

class PartytransactionController extends Controller
{
    use ApiResponse;
    /**
     * Summary of index
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        try {
            $where = [];
            $from_date = request()->query('from_date');
            $to_date = request()->query('to_date');
            $showroom_id = request()->query('showroom_id');
            $party_code = request()->query('party_code');

            if (!empty($from_date) || !empty($to_date)) {
                if (!empty($from_date)) {
                    $where[] = ['partytransactions.transaction_at', '>=', $from_date];
                }
                if (!empty($to_date)) {
                    $where[] = ['partytransactions.transaction_at', '<=', $to_date];
                }
            }

            if (!empty($showroom_id)) {
                $where[] = ['partytransactions.showroom_id', '=', $showroom_id];
            }
            if (!empty($party_code)) {
                $where[] = ['partytransactions.party_code', '=', $party_code];
            }

            $query = Partytransaction::addSelect([
                'name' => Supplier::select('name')
                    ->whereColumn('code', 'partytransactions.party_code')
            ])
            ->addSelect([
                'showrooms' => Showroom::select('name')
                    ->whereColumn('id', 'partytransactions.showroom_id')
            ]);

            if (!empty($where)) {
                $query->where($where);
            }

            $data = $query->orderBy("id", "desc")->get();

            return $this->successResponse('Party transactions fetched successfully.', $data, Response::HTTP_OK);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to fetch party transactions.', $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    /**
     * Summary of store
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            $data = new Partytransaction;

            $data->transaction_at = $request->transaction_at;
            $data->remark = $request->remark;
            $data->party_code = $request->party_code;
            $data->transaction_type = $request->transaction_type;
            $data->transaction_method = $request->transaction_method;
            $data->showroom_id = $request->showroom_id;
            $data->relation = Partytransaction::generateUniqueInvoice();

            if ($request->balance_status == 'Payable') {
                if ($request->transaction_type == 'receive') {
                    $data['credit'] = $request->payment;
                    $data['debit'] = 0;
                } else {
                    $data['debit'] = $request->payment;
                    $data['credit'] = 0;
                }
            } else {
                if ($request->transaction_type == 'receive') {
                    $data['debit'] = 0;
                    $data['credit'] = $request->payment;
                } else {
                    $data['credit'] = 0;
                    $data['debit'] = $request->payment;
                }
            }

            $data->commission = $request->commission ?? 0;
            $data->status = "transaction";
            $data->transaction_by = "supplier";

            $data->save();

            DB::commit();

            return $this->successResponse('Party Transaction successfully added.', $data, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse('Failed to create party transaction.', $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    /**
     * Summary of show
     * @param mixed $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        try {
            $data = Partytransaction::findOrFail($id);
            return $this->successResponse('Party transaction details retrieved successfully.', $data, Response::HTTP_OK);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to retrieve party transaction.', $e->getMessage(), Response::HTTP_NOT_FOUND);
        }
    }
    /**
     * Summary of update
     * @param \Illuminate\Http\Request $request
     * @param mixed $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $data = Partytransaction::findOrFail($id);

            $data->transaction_at = $request->transaction_at;
            $data->remark = $request->remark;
            $data->party_code = $request->party_code;
            $data->transaction_type = $request->transaction_type;
            $data->transaction_method = $request->transaction_method;
            $data->showroom_id = $request->showroom_id;

            if ($request->balance_status == 'Payable') {
                if ($request->transaction_type == 'receive') {
                    $data['credit'] = $request->payment;
                    $data['debit'] = 0;
                } else {
                    $data['debit'] = $request->payment;
                    $data['credit'] = 0;
                }
            } else {
                if ($request->transaction_type == 'receive') {
                    $data['debit'] = 0;
                    $data['credit'] = $request->payment;
                } else {
                    $data['credit'] = 0;
                    $data['debit'] = $request->payment;
                }
            }

            $data->commission = $request->commission ?? 0;
            $data->status = "transaction";
            $data->transaction_by = "supplier";

            $data->save();

            DB::commit();

            return $this->successResponse('Party Transaction successfully updated.', $data, Response::HTTP_OK);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse('Failed to update party transaction.', $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    /**
     * Summary of destroy
     * @param mixed $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        try {
            $data = Partytransaction::findOrFail($id);
            $data->delete();

            return $this->successResponse('Party Transaction successfully deleted.', null, Response::HTTP_OK);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to delete party transaction.', $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}