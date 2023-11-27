<?php

namespace App\Http\Controllers\Location;

use App\Http\Controllers\Controller;
use App\Models\HttpRes;
use App\Models\Location\Country;
use App\Models\UM;
use Illuminate\Http\Request;

class CountryController extends Controller
{
    private $c = null;
    function __construct(){
        $this->c = new Country();
    }
    //
    function save(Request $req){
        $ss = UM::getUserInfoByToken($req);
        if($ss->status_code !=200) return HttpRes::raw($ss);
        return $this->c->save($req->all(),$req->id,$ss);
    }

    function getListPaginate(Request $req){
        $ss = UM::getUserInfoByToken($req);
        if($ss->status_code !=200) return HttpRes::raw($ss);
        return HttpRes::result($this->c->listPaginate($req->all()));
    }

    function getDetails(Request $req){
        $ss = UM::getUserInfoByToken($req);
        if($ss->status_code !=200) return HttpRes::raw($ss);
        return HttpRes::result($this->c->details($req->id,$ss));
    }

    function delete(Request $req){
        $ss = UM::getUserInfoByToken($req);
        if($ss->status_code !=200) return HttpRes::raw($ss);
        return HttpRes::raw($this->c->delete($req->id,$ss));
    }
}
