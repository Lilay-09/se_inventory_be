<?php

namespace App\Models\Location;

// use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Illuminate\Database\Eloquent\Model;
use App\Models\ObjectRes;
use DB;
use Illuminate\Pagination\LengthAwarePaginator;
class Country //extends Model
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
            'iso3' => '0|string|1,3',
        ];
        $res = validateRule($arr,$v_rule);
        if($res->error) return ObjectRes::error($res->error);
        $inputs = $res->result;
        $exist = isExist('countries',$id,['name' => $inputs['name']]);
        // var_dump($exist);
        if($exist) return ObjectRes::error($inputs['name'].' is already exist');
        $newID = saveData('countries',['id'=>$id],$inputs,$ss);
        return ObjectRes::depends($newID,'Saved');
    }

    function listPaginate($arr,$ss=null){
        $ss = $ss?$ss:$this->ss;
        $d = (object)$arr;
        $current_page = isset($d->current_page)?$d->current_page:1;
        $per_page = isset($d->per_page)?$d->per_page:10;
        $skip_rows = ($current_page - 1) * $per_page;
        $selectCols = 'c.name,c.id';
        $query = DB::table('countries as c')->selectRaw($selectCols);
        $count_clone = clone($query);
        $count = $count_clone->count('c.id');
        $rows = $query->skip($skip_rows)->take($per_page)->orderBy('c.id','desc')->get();
        return new LengthAwarePaginator($rows,$count,$per_page,$current_page);
    }

    function details($id=null,$ss=null){
        $ss = $ss?$ss:$this->ss;
        $id = $id?$id:$this->id;
        return DB::table('countries')->selectRaw('name,name as country')->get();
    }

    function delete($id=null,$ss=null){
        $ss = $ss?$ss:$this->ss;
        $id = $id?$id:$this->id;
        $inUse = inUseOrLinked('cities',['country_id'=>$id]);
        if($inUse) return ObjectRes::error('Country is linking to district, can not delete');
        $d = DB::table('countries')->where('id',$id)->delete();
        return ObjectRes::depends($d,'Deleted');
    }
}
