<?php

use App\Models\Supplier;
use App\Models\Partytransaction;

if (!function_exists('getSupplierBalance')) {
    function getSupplierBalance($code=null, $id=null)
    {
        $data  = [];
        $where = [];
        if(!empty($code)){
            // define default amount
            $initital_balance = $debit = $credit = $commission = $balance = 0;
            // get supplier info
            $supplier_info = Supplier::with('partytransaction')->where('code','=', $code)->first();
            if(!empty($id)){
                $partytransaction = Partytransaction::where([['id', '<', $id], ['party_code', '=', $code]])->get();
                
                foreach($partytransaction as $row){
                    $credit     += $row->credit;
                    $debit      += $row->debit;
                    $commission += $row->commission;
                }
            }else{
                if(!empty($supplier_info->partytransaction)){
                    foreach($supplier_info->partytransaction as $row){
                        $credit     = $row->credit;
                        $debit      = $row->debit;
                        $commission = $row->commission;
                    }
                }
            } 

            $initital_balance = (!empty($supplier_info->initial_balance) ? $supplier_info->initial_balance : 0);
            $credit           = $credit;
            $debit            = $debit;
            $commission       = $commission;

            // get balance
            if ($initital_balance < 0) {
                $balance = $debit - (abs($initital_balance) + $credit);
            } else {
                $balance = ($initital_balance + $debit) - $credit;
            }
            
            $balance = $balance - $commission;
            $balance = number_format($balance, 2,".","");

            $data['code']            = $supplier_info->code;
            $data['name']            = $supplier_info->name;
            $data['initial_balance'] = $initital_balance;
            $data['credit']          = $credit;
            $data['debit']           = $debit;
            $data['commission']      = $commission;
            $data['balance']         = abs($balance);
            $data['real_balance']    = $balance;
            $data['status']          = ($balance <= 0 ? "Payable" : "Receivable");

        }else {
            $data['code']            = '';
            $data['name']            = '';
            $data['initial_balance'] = 0;
            $data['debit']           = 0;
            $data['credit']          = 0;
            $data['commission']      = 0;
            $data['balance']         = 0;
            $data['real_balance']    = 0;
            $data['status']          = "Receivable";
        }

        return $data;
    }
}