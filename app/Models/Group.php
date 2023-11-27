<?php

namespace App\Models;

// use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Illuminate\Database\Eloquent\Model;
use DB;
use Illuminate\Pagination\LengthAwarePaginator;
class Group //extends Model
{
    // use HasFactory;
    protected $id = null,$ss=null;
    function __construct($id=null,$ss=null){
        $this->id = $id;
        $this->ss = $ss;
    }
    function save($arr,$id,$ss=null){
        $v_rule = [
            'name' => '1|string',
        ];
        $res = validateRule($arr,$v_rule);
        if($res->error) return ObjectRes::error($res->error);
        $inputs = $res->result;
        $exist = isExist('groups',$id,['name' =>$inputs['name']]);
        if($exist) return ObjectRes::error('Group name is already taken');
        $newID = saveData('groups',['id'=>$id],$inputs);
        return ObjectRes::depends($newID,'Saved','Error Saving Info');
    }

    function listPaginate($arr,$ss=null){
        $ss = $ss?$ss:$this->ss;
        $d = (object)$arr;
        $current_page = isset($d->current_page)?$d->current_page:1;
        $per_page = isset($d->per_page)?$d->per_page:10;
        $skip_rows = ($current_page - 1) * $per_page;
        $selectCols = 'g.name,g.id';
        $query = DB::table('categories as g')->selectRaw($selectCols);
        $count_clone = clone($query);
        $count = $count_clone->count('g.id');
        $rows = $query->skip($skip_rows)->take($per_page)->orderBy('g.id','desc')->get();
        return new LengthAwarePaginator($rows,$count,$per_page,$current_page);
    }

    function details($id=null,$ss=null){
        $ss = $ss?$ss:$this->ss;
        $id = $id?$id:$this->id;
        $row = DB::table('groups as g')->where('g.id',$id)->selectRaw('g.name,g.id')->first();
        return $row;
    }

    function delete($id=null,$ss=null){
        $id = $id?$id:$this->id;
        $ss = $ss?$ss:$this->ss;
        // $inUse = inUseOrLinked('districts',['city_id'=>$id]);
        // if($inUse) return ObjectRes::error('City is linking to district, can not delete');
        $delete = DB::table('groups')->where('id',$id)->delete();
        return ObjectRes::depends($delete,'Deleted');
    }
}
