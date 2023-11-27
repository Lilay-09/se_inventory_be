<?php

namespace App\Models\Location;
use App\Models\ListPaginate;
use App\Models\ObjectRes;

// use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Illuminate\Database\Eloquent\Model;
use DB;
use Illuminate\Pagination\LengthAwarePaginator;
class City //extends Model
{
    // use HasFactory;
    protected $id = null,$ss=null;
    function __construct($id=null,$ss=null){
        $this->id = $id;
        $this->ss = $ss;
    }

    function save($arr,$id=null,$ss=null){
        $id = $id?$id:$this->id;
        $ss = $ss?$ss:$this->ss;
        $v_rule = [
            'name' => '1|string',
            'country_id' => '1|number|exists=countries.id',
            'post_code' => '0|string',
        ];
        $res = validateRule($arr,$v_rule);
        if($res->error) return ObjectRes::error($res->error);
        $inputs = $res->result;
        $exist = isExist('cities',$id,['name'=>$inputs['name']]);
        if($exist) return ObjectRes::error('City name is already exists');
        $newID = saveData('cities',['id'=>$id],$inputs,$ss);
        return ObjectRes::depends($newID,'Saved','Error Saving City');
    }

    function listPaginate($arr,$ss=null){
        $ss = $ss?$ss:$this->ss;
        $d = (object)$arr;
        $current_page = isset($d->current_page)?$d->current_page:1;
        $per_page = isset($d->per_page)?$d->per_page:10;
        $skip_rows = ($current_page - 1) * $per_page;
        $selectCols = 'ct.name,ct.id,(SELECT c.name FROM countries as c WHERE ct.country_id = c.id) as country_name';
        $query = DB::table('cities as ct')->selectRaw($selectCols);
        $count_clone = clone($query);
        $count = $count_clone->count('ct.id');
        $rows = $query->skip($skip_rows)->take($per_page)->orderBy('ct.id','desc')->get();
        return new LengthAwarePaginator($rows,$count,$per_page,$current_page);
    }

    function details($id=null,$ss=null){
        $id = $id?$id:$this->id;
        $row = DB::table('cities as ct')->where('id',$id)->selectRaw('ct.name,ct.id')->first();
        return $row;
    }

    function delete($id=null,$ss=null){
        $id = $id?$id:$this->id;
        $inUse = inUseOrLinked('districts',['city_id'=>$id]);
        if($inUse) return ObjectRes::error('City is linking to district, can not delete');
        $delete = DB::table('cities')->where('id',$id)->delete();
        return ObjectRes::depends($delete,'Deleted');
    }
}
