<?php

namespace App\Models;

// use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Illuminate\Database\Eloquent\Model;

class HttpRes //extends Model
{
    // use HasFactory;
    static function raw($data){
        // $data->status_code = isset($data->status) == 'OK'?200:405;
        if(!isset($data->status_code)){
            $status = isset($data->status) == 'OK' ? $data->status : 'Error';
            // $data->status_code = $status == 'OK'?200:400;
        }
        return response()->json($data);
    }

    static function result($data){
        return response()->json([
            'status_code' => 200,
            'error_message' =>null,
            'data' => $data,
        ]);
    }
}
