<?php

namespace App\Http\Controllers;
use App\Http\Resources\PartytransactionResource;

use App\Models\Partytransaction;
use App\Models\Supplier;
use App\Models\Showroom;
use Illuminate\Http\Request;

class PartytransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {   
        $where = [];
        
        if(!empty($request->from_date) || !empty($request->to_date)){
            if(!empty($request->from_date)){
                $where[] = ['partytransactions.transaction_at','>=', $request->from_date];
            }
            if(!empty($request->to_date)){
                $where[] = ['partytransactions.transaction_at','<=', $request->to_date];
            }
        }else{
            $where[] = ['partytransactions.transaction_at','=', date("Y-m-d")];
        }

        if(!empty($request->showroom_id)){
            $where[] = ['partytransactions.showroom_id','=',$request->showroom_id];
        } 
        if(!empty($request->party_code)){
            $where[] = ['partytransactions.party_code','=',$request->party_code];
        }
        
        $data =
            Partytransaction::addSelect(['name' => Supplier::select('name')
            ->whereColumn('code', 'partytransactions.party_code')])
            ->addSelect(['showrooms' => Showroom::select('name')
            ->whereColumn('id', 'partytransactions.showroom_id')])
            ->where($where)
			->orderBy("id", "desc")
            ->paginate($request->per_page); 
            
            $data =
            Partytransaction::all();
        return response()
            ->json($data, 200);
    }

    /** 
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $data = new Partytransaction;

        $data->transaction_at = $request->transaction_at;
        $data->paid_by = $request->paid_by;
        $data->remark = $request->remark;
        $data->party_code = $request->party_code;
		$data->transaction_type = $request->transaction_type;
        $data->transaction_method = $request->transaction_method;
        $data->showroom_id = $request->showroom_id;

        // generate transaction invoice
        $invoice = rand(100000, 999999);
        while (Partytransaction::where('relation', $invoice)->first())
        {
            $invoice = rand(100000, 999999);
        }
        $data->relation = $invoice;
        
        if($request->balance_status== 'Payable'){
            if($request->transaction_type == 'receive'){
                $data['credit'] = $request->payment;
                $data['debit'] = 0;
            }else{
                $data['debit'] = $request->payment;
                $data['credit'] = 0;
            }
        }else{
            if($request->transaction_type == 'receive'){
                $data['debit'] = 0;
                $data['credit'] = $request->payment;
            }else{
                $data['credit'] = 0;
                $data['debit'] = $request->payment;
            }
        }

        $data->commission       = $request->commission ? $request->commission : 0;
        $data->status           = "transaction";
        $data->transaction_by   = "suplier";

        $data->save();

        $data = ['success' => 'Party Transaction successfully added.'];
        
        return response()
        ->json($data, 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Partytransaction  $partytransaction
     * @return \Illuminate\Http\Response
     */
    public function show($partytransaction)
    {
        $data = Partytransaction::with('party:code,name,id,mobile,address')->find($partytransaction);
        
        $current_balance = getSupplierBalance($data->party_code);
        $previuse_balance = getSupplierBalance($data->party_code, $data->id);
        
        $data['previuse_balance'] = (!empty($previuse_balance) ? $previuse_balance['balance'].' ['.$previuse_balance['status'].']': 0);
        $data['current_balance'] = (!empty($current_balance) ? $current_balance['balance'].' ['.$current_balance['status'].']': 0);

        return response()
        ->json($data, 200);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Partytransaction  $partytransaction
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {   
        $data =  new PartytransactionResource(Partytransaction::with("party")->find($id));
        return response()
        ->json($data, 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Partytransaction  $partytransaction
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $data = Partytransaction::find($request->id);

        $data->transaction_at = $request->date;
        $data->paid_by = $request->paid_by;
        $data->remark = $request->remark;
		$data->transaction_type = $request->transaction_type;
        $data->transaction_method = $request->transaction_method;

        if($request->balance_status== 'Payable'){
            if($request->transaction_type == 'receive'){
                $data['credit'] = $request->payment;
                $data['debit'] = 0;
            }else{
                $data['debit'] = $request->payment;
                $data['credit'] = 0;
            }
        }else{
            if($request->transaction_type == 'receive'){
                $data['debit'] = 0;
                $data['credit'] = $request->payment;
            }else{
                $data['credit'] = 0;
                $data['debit'] = $request->payment;
            }
        }
        $data->commission = $request->commission;
        $data->save();

        $data = ['success' => 'Party Transaction successfully update.'];
        
        return response()
        ->json($data, 200);
    }
    public function destroy($id)
    {
        if (Partytransaction::find($id)->delete())
        {
            $data =
            Partytransaction::addSelect(['name' => Supplier::select('name')
            ->whereColumn('code', 'partytransactions.party_code')])
            ->addSelect(['warehouse_name' => Warehouse::select('name')
            ->whereColumn('id', 'partytransactions.showroom_id')])
			->orderBy("id", "desc")
            ->paginate(12); 
        return response()
            ->json($data, 200);
        }
        else
        {
            $data = ['warning' => 'Supplier Transaction successfully not deleted.'];
        }
        return response()->json($data, 200);
    }
}