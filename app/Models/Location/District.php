<?php

namespace App\Models\Location;
use App\Models\ObjectRes;

// use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Illuminate\Database\Eloquent\Model;
use DB;
use Illuminate\Pagination\LengthAwarePaginator;
class District //extends Model
{
    // use HasFactory;
    protected $id =null,$ss=null;
    function __construct($id=null,$ss=null){
        $this->id = $id;
        $this->ss = $ss;
    }
    function save($arr,$id=null,$ss=null){
        $ss = $ss?$ss:$this->ss;
        $id = $id?$id:$this->id;
        $v_rule = [
            'name' => '1|string|1,50',
            'city_id' => '1|number|exists=cities.id'
        ];
        $res = validateRule($arr,$v_rule);
        if($res->error) return ObjectRes::error($res->error);
        $inputs = $res->result;
        $exist = isExist('districts',$id,['name' => $inputs['name']]);
        // var_dump($exist);
        if($exist) return ObjectRes::error($inputs['name'].' is already exist');
        $newID = saveData('districts',['id'=>$id],$inputs,$ss);
        return ObjectRes::depends($newID,'Saved');
    }

    function listPaginate($arr,$ss=null){
        $ss = $ss?$ss:$this->ss;
        $d = (object)$arr;
        $current_page = isset($d->current_page)?$d->current_page:1;
        $per_page = isset($d->per_page)?$d->per_page:10;
        $skip_rows = ($current_page - 1) * $per_page;
        $selectCols = 'd.name as district_name,d.id,(SELECT c.name FROM cities as c WHERE d.city_id = c.id) as city_name';
        $query = DB::table('districts as d')->selectRaw($selectCols);
        $count_clone = clone($query);
        $count = $count_clone->count('d.id');
        $rows = $query->skip($skip_rows)->take($per_page)->orderBy('d.id','desc')->get();
        return new LengthAwarePaginator($rows,$count,$per_page,$current_page);
    }

    function details($id=null,$ss=null){
        $ss = $ss?$ss:$this->ss;
        $id = $id?$id:$this->id;
        return DB::table('districts')->selectRaw('name,name as district_name,id')->get();
    }

    function delete($id=null,$ss=null){
        $ss = $ss?$ss:$this->ss;
        $id = $id?$id:$this->id;
        $d = DB::table('districts')->where('id',$id)->delete();
        return ObjectRes::depends($d,'Deleted');
    }
}
