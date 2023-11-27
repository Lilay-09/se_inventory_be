<?php

namespace App\Models;

// use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Illuminate\Database\Eloquent\Model;

use DB;
class Partner //extends Model
{
    // use HasFactory;
    static function savePartner($arr,$id,$ss){
        $v_rule = [
            'name' => '1|string',
            'partner_type_id' => '1|string',
            'detail_id' => '1|number',
            'partner_id' => '1|number',
            'district_id' => '1|number',
            'country_id' => '1|number',
            'address' => '1|string',
        ];
        $res = validateRule($arr,$v_rule);
        if($res->error) return ObjectRes::error($res->error);
        $inputs = $res->result;
        $newID = saveData('partners',['id'=>$id],$inputs);
        return ObjectRes::depends($newID,'Saved','Error Saving Info');
    }
}
