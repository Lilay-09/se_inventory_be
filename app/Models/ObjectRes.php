<?php

namespace App\Models;

// use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Illuminate\Database\Eloquent\Model;

class ObjectRes //extends Model
{
    // use HasFactory;
    static function depends($condition,$message=null,$err_msg='Somthing went wrong'){
        if($condition){
            return (object)[
                'status' => 'OK',
                'message' => $message,
                'error_message' => null,
            ];
        }
        return (object)[
            'status' => 'Error',
            'message' => null,
            'error_message' => $err_msg,
        ];
    }

    static function error($error_str){
        return (object)[
            'status' => 'Error',
            'status_code' => 405,
            'error_message' => $error_str,
        ];
    }

    static function success($arrs =[],$status_code=null){
        $status_code = $status_code?$status_code:200;
        $data = (object)['status_code'=>$status_code,'status'=>'OK','error_message'=>null];
        foreach($arrs as $prop=>$val) $data->{$prop} = $val;
        return $data;
    }

    static function errorOrResult($err,$res){
        return (object)['error'=>$err?$err:null,'result'=>$res?$res:null];
    }

}
