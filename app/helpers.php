<?php

function validateRule($data, $rules) {
    $error = null;
    $result = [];
    $p1_reqs = ['1','required','req','0'];
    $p2_types = ['choices','choice','phone','integer','array','string','boolean','object','email','number','int','double','decimal','float'];
    $p1_unreq = ['0','unrequired','unreq'];
    foreach ($rules as $fieldName => $rule) {
        $fieldValue = isset($data[$fieldName])?$data[$fieldName]:null;
        $parts = explode('|',$rule);
        $parts1 = isset($parts[0]) ? $parts[0] :'';
        $parts2 = isset($parts[1]) ? $parts[1] :'';
        $parts3 = isset($parts[2]) ? $parts[2] :'';
        $parts4 = isset($parts[3]) ? $parts[3] :'';

        foreach($parts as $k=>$v){
            switch($v) {
                //** first arg like 1| or 0| or required| or req| */
                case ($k == 0):
                    if(!in_array($v,$p1_reqs)) {
                        $error = 'First argument must be one of '.implode(',',$p1_reqs);
                    }
                    if(in_array($v,$p1_reqs)){
                        if(!isset($fieldValue)){
                            $error = $fieldName.' is required';
                        }
                    }
                    break;
                case ($k == 1):
                    // var_dump($parts2);
                    if($fieldValue !== null){
                        if(!in_array($parts2,$p2_types)) {
                            $error = 'Second argument type must be one of '.implode(',',$p2_types);
                        }
                    }
                    $isValid = validatePart2($v,$fieldValue,$parts3);
                    if($isValid->status != 'OK') $error = $fieldName.$isValid->error;
                    break;
                default:
                    $exist_part = explode('=',$parts3);
                    if($fieldValue !== null && !in_array($parts2,['choice','choices']) && !in_array($exist_part[0],['exists','exist'])){
                        $corrected_format = (preg_match('/^\d+,\d+$/', $v) === 1);
                        if(!$corrected_format){
                            $error = $fieldName.' min and max format must format like min,max';
                        }
                        $str = explode(',',$v);
                        $min = $str[0];
                        $max = $str[1];
                        $str_len = strlen($fieldValue);
                        if($min > $str_len || $max < $str_len){
                            $error = $fieldName. ' must be between '.$min.' and '.$max;
                        }
                    }
                    break;
            }
            if($error !=null){
                break;
            }
        }
        $result[$fieldName] = $fieldValue;
    }


    return $error !== null ? (object)['error' => $error, 'result' => []] : (object)['error' => null, 'result' => $result];
}

function validatePart2($rule,$value,$part3=null,$parts4=null){
    $error = null;
    $status = 'OK';
    $numbs = ['number','float','integer','positive','double','int'];
    if($value){
        if(in_array($rule,$numbs)){
            if(!isNum($value)){
                $error = ' must be a number';
                $status = 'Error';
            }

            if($rule == 'float'){
               if(!is_float($value)){
                    $error = ' must be float';
                    $status = 'Error';
               }
            }
            if($rule == 'double'){
                if(!is_double($value)){
                        $error = ' must be double';
                        $status = 'Error';
                }
            }

            if($rule == 'int'){
                if(!is_int($value)){
                    $error = ' must be integer';
                    $status = 'Error';
                }
            }
        }
        if($rule == 'email'){
            $pattern = '/^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}$/';
            $f = preg_match($pattern, $value);
            if(!$f){
                $error = ' must be email format like yourname@example.com';
                $status = 'Error';
            }
        }
        if($rule == 'phone'){
            $value = str_replace(' ','',$value);
            $f = onlyNumber($value);
            if(!$f){
                $error = ' must be a number';
                $status = 'Error';
            }
        }
        if($rule == 'choices' || $rule == 'choice'){
            $choices = explode(',',$part3);
            if(!in_array($value,$choices)){
                $error = ' must be one of '.$part3;
                $status = 'Error';
            }
        }

        if($rule == 'exists' || $rule == 'exist'){
            $exist_part = explode('=',$part3);
            $exist_value = explode('.',$exist_part[1]);
            $table = $exist_value[0];
            $col = isset($exist_value[1])?$exist_value[1]:'id';
            if(!Schema::hasTable($table)){
                $error = ' table '.$table.' not found';
                $status = 'Error';
            }else if(!Schema::hasColumn($table, $col)){
                $error = ' table '.$table.' has no column '.$col;
                $status = 'Error';
            }
            else{
                $check = DB::table($table)->where($col,$value)->take(1)->value($col);
                if(!$check){
                    $error = ' invalid value or not found in table '.$table;
                    $status = 'Error';
                }
            }
        }
        $exist_part3 = explode('=',$part3);
        if(in_array($exist_part3[0],['exists','exist'])){
            $exist_value = explode('.',$exist_part3[1]);
            $table = $exist_value[0];
            $col = isset($exist_value[1])?$exist_value[1]:'id';
            if(!Schema::hasTable($table)){
                $error = ' table('.$table.') not found';
                $status = 'Error';
            }else if(!Schema::hasColumn($table, $col)){
                $error = ' table '.$table.' has no column '.$col;
                $status = 'Error';
            }
            else {
                $check = DB::table($table)->where($col,$value)->take(1)->value($col);
                if(!$check){
                    $error = ' is invalid or not found in table '.$table;
                    $status = 'Error';
                }
            }

        }

    }
    return (object)[
        'status' => $status,
        'error' => $error,
    ];
}

function isNum($value){
    return (is_int($value) || is_float($value) || is_double($value));
}


function saveData($table,$uniqueKey=[],$data=[],$ss=null) {
    $existingRecord = DB::table($table)->where($uniqueKey)->first();
    $create = false;
    if ($existingRecord) {
        if($ss){
            $data['update_uid'] = $ss->user->id;
            $data['update_user'] = $ss->user->full_name;
        }
        DB::table($table)->where($uniqueKey)->update($data);
        return $existingRecord->id;
    } else $create = true;

    if(!$existingRecord || $create){
        $data['id'] = null;
        if($ss){
            $data['create_uid'] = $ss->user->id;
            $data['create_user'] = $ss->user->full_name;
            $data['update_uid'] = $ss->user->id;
            $data['update_user'] = $ss->user->full_name;
        }
        $id = DB::table($table)->insertGetId($data);
        return $id;
    }
}

function isExist($tbl_name,$pk_id,$findCols){
    $lowercaseCols = array_combine(array_map('strtolower', array_keys($findCols)), array_values($findCols));
    $pk_id = DB::table($tbl_name)->where('id','<>',$pk_id)->where($lowercaseCols)->take(1)->value('id');
    return $pk_id>0?true:false;
}

function findExist($tbl_name,$keys){
    return DB::table($tbl_name)->where($keys)->exists();
}

function getNowTime(){
    return date('Y-m-d H:i:s');
}


function onlyNumber($number){
    if(preg_match('/^\d+$/', $number)) return true;
    return false;
}

function inUseOrLinked($tbl_name,$pk){
    $id = DB::table($tbl_name)->where($pk)->take(1)->value('id');
    return $id>0?true:false;
}

function diffDays($startDate, $endDate) {
    $start = new DateTime($startDate);
    $end = new DateTime($endDate);

    $interval = $start->diff($end);

    $days = $interval->format('%a');
    $hours = $interval->format('%h');
    $minutes = $interval->format('%i');

    $days = $days>1?$days.' days': $days .' day';
    $hours = $hours ? $hours.' hours': $hours .' hour';
    $minutes ? $minutes.' minutes': $minutes .' minute';
    return (object)[
        'days' => $days,
        'hours' => $hours,
        'mins' => $minutes,
        'end' => $days.'-'.$hours.'-'.$minutes
    ];
}

function isBase64($string) {
    return base64_encode(base64_decode($string, true)) === $string;
}
