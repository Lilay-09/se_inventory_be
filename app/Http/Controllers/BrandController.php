<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\HttpRes;
use App\Models\UM;
use Illuminate\Http\Request;

class BrandController extends Controller
{
    //
    private $ct = null;
    function __construct(){
        $this->ct = new Brand();
    }
    function save(Request $req){
        $ss = UM::getUserInfoByToken($req);
        if($ss->status_code !=200) return HttpRes::raw($ss);
        return HttpRes::result($this->ct->save($req->all(),$req->id,$ss));
    }

    function getListPaginate(Request $req){
        $ss = UM::getUserInfoByToken($req);
        if($ss->status_code !=200) return HttpRes::raw($ss);
        return HttpRes::result($this->ct->listPaginate($req->all(),$ss));
    }

    function getDetails(Request $req){
        $ss = UM::getUserInfoByToken($req);
        if($ss->status_code !=200) return HttpRes::raw($ss);
        return HttpRes::result($this->ct->details($req->id,$ss));
    }

    function delete(Request $req){
        $ss = UM::getUserInfoByToken($req);
        if($ss->status_code !=200) return HttpRes::raw($ss);
        return HttpRes::result($this->ct->delete($req->id,$ss));
    }
}
