<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use App\Models\Partytransaction;
use App\Models\Supplier;
use App\Models\Showroom;

class PartytransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
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
            // ->paginate(request()->query('per_page')); // Uncomment if you want to use pagination

            return response()->json($data, Response::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to fetch party transactions.',
                'error' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            $data = new Partytransaction;

            $data->transaction_at = $request->transaction_at;
            $data->paid_by = $request->paid_by;
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

            return response()->json([
                'success' => 'Party Transaction successfully added.',
                'data' => $data,
            ], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to create party transaction.',
                'error' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Partytransaction  $partytransaction
     * @return \Illuminate\Http\Response
     */
    public function show($partytransaction)
    {
        try {
            $data = Partytransaction::with('party:code,name,id,mobile,address')->findOrFail($partytransaction);
            
            $current_balance = getSupplierBalance($data->party_code);
            $previous_balance = getSupplierBalance($data->party_code, $data->id);
            
            $data['previous_balance'] = (!empty($previous_balance) ? $previous_balance['balance'].' ['.$previous_balance['status'].']' : 0);
            $data['current_balance'] = (!empty($current_balance) ? $current_balance['balance'].' ['.$current_balance['status'].']' : 0);

            return response()->json($data, Response::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Party transaction not found.',
                'error' => $e->getMessage(),
            ], Response::HTTP_NOT_FOUND);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Partytransaction  $partytransaction
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $data = Partytransaction::findOrFail($id);

            $data->transaction_at = $request->transaction_at;
            $data->paid_by = $request->paid_by;
            $data->remark = $request->remark;
            $data->transaction_type = $request->transaction_type;
            $data->transaction_method = $request->transaction_method;

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
            $data->commission = $request->commission;

            $data->save();

            DB::commit();

            return response()->json([
                'success' => 'Party Transaction successfully updated.',
                'data' => $data,
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to update party transaction.',
                'error' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            $partytransaction = Partytransaction::findOrFail($id);
            $partytransaction->delete();

            $data = Partytransaction::addSelect([
                'name' => Supplier::select('name')
                    ->whereColumn('code', 'partytransactions.party_code')
            ])
            ->addSelect([
                'showroom_name' => Showroom::select('name')
                    ->whereColumn('id', 'partytransactions.showroom_id')
            ])
            ->orderBy("id", "desc")
            ->get();

            return response()->json(null, Response::HTTP_NO_CONTENT);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to delete party transaction.',
                'error' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Restore the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function restore($id)
    {
        try {
            $partytransaction = Partytransaction::onlyTrashed()->findOrFail($id);
            $partytransaction->restore();

            return response()->json($partytransaction, Response::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to restore party transaction.',
                'error' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}