<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PartytransactionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $id   = $this->id; 
        $party_code = $this->party_code;
        $partytransaction_details =  getSupplierBalance($party_code, $id);
        $real_balance     = $partytransaction_details['real_balance'];
        $previous_balance = $partytransaction_details['balance'];
        $previous_status  = $partytransaction_details['status'];

        return [
            "created_at"=>$this->created_at,
            "credit"=>$this->credit,
            "commission"=>$this->commission,
            "debit"=>$this->debit,
            "deleted_at"=>$this->deleted_at,
            "id"=>$this->id,
            "paid_by"=>$this->paid_by,
            "party_code"=>$this->party_code,
            "relation"=>$this->relation,
            "remark"=>$this->remark,
            "status"=>$this->status,
            "transaction_at"=>$this->transaction_at,
            "transaction_by"=>$this->transaction_by,
            "transaction_method"=>$this->transaction_method,
			"transaction_type"=>$this->transaction_type,
            "updated_at"=>$this->updated_at,
            "showroom_id"=>$this->showroom_id,
            "name"=>$this->party->name,
            "real_balance"=>$real_balance,
            "previous_balance"=>$previous_balance,
            "previous_status"=>$previous_status,
            //"party"=>$this->party,
            //"partytransaction_details"=>$partytransaction_details,
        ];
    }
}