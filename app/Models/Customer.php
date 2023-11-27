<?php

namespace App\Models;

// use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Illuminate\Database\Eloquent\Model;
use DB;
use Illuminate\Pagination\LengthAwarePaginator;
class Customer //extends Model
{
    // use HasFactory;
    protected $id =null,$ss=null;
    function __construct($id=null,$ss=null){
        $this->id = $id;
        $this->ss = $ss;
    }
    function saveCustomer($arr,$id=null,$ss=null){
        $id = $id?$id:$this->id;
        $ss = $ss?$ss:$this->ss;
        $d = (object)$arr;
        $success = 0;
        $v_rule = [
            'location' => '1|string',
            'remarks' => '1|string',
            'customer_type_id' => '1|number',
            'partner_id' => '1|number',
        ];
        $res = validateRule($arr,$v_rule);
        if($res->error) return ObjectRes::error($res->error);
        $inputs = $res->result;
        $savePartner = Partner::savePartner([
            'name' => $d->name,
            'partner_type_id' => $d->partner_type_id,
            'district_id' => $d->district_id,
            'country_id' => $d->country_id,
            'address' => $d->address,
            'partner_id' => $d->partner_id
        ],$id,$ss);
        if($savePartner->status != 'OK') return ObjectRes::error($savePartner->error_mmessage);
        $customerID = saveData('customers',['partner_id' => $id],$inputs);
        if($customerID){
            $success++;
        }
        return ObjectRes::depends($success,'Saved','Error Saving Info');
    }


    function customerListPaginate($arr,$ss=null){
        $d = (object)$arr;
        $query = DB::table('partners as p')->join('customers as c','p.id','=','c.partner_id')->join('partner_details as pd','pd.id','=','p.detail_id')->selectRaw('p.name,c.remarks,c.address');
        $current_page = isset($d->current_page)?$d->current_page:1;
        $per_page = isset($d->per_page)?$d->per_page:10;
        $skip_row = ($current_page - 1)*$per_page;
        $count_clone = clone $query;
        $count = $count_clone->count('p.id');
        $rows = $query->skip($skip_row)->take($per_page)->get();
        return new LengthAwarePaginator($rows,$count,$per_page,$current_page);
    }

    function customerDetails($id=null,$ss=null){
        $id = $id?$id:$this->id;
        $ss = $ss?$ss:$this->ss;
        $query = 'SELECT p.name FROM partners as p INNER JOIN customers as c ON p.id = c.partner_id INNER JOIN partner_details as d ON d.id = p.detail_id WHERE p.id = ' . $id;
        $row = DB::selectOne($query);
        return $row;
    }

    function deleteCustomer($id=null,$ss=null){
        $id = $id?$id:$this->id;
        $ss = $ss?$ss:$this->ss;
    }
}
