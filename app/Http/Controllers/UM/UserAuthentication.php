<?php

namespace App\Http\Controllers\UM;

use App\Http\Controllers\Controller;
use App\Models\HttpRes;
use App\Models\UM;
use Config;
use Illuminate\Http\Request;

class UserAuthentication extends Controller
{
    //
    protected $UM = null;
    protected $app_id = null;
    function __construct(){
        $this->UM = new UM();
    }
    function intnLogin(Request $req){
        $user = $this->UM->verifyUser($req->login_name,$req->password);
        return HttpRes::raw($user);
    }

    function saveUser(Request $req){
        return HttpRes::raw($this->UM->saveUser($req->all()));
    }

    function getUserProfile(Request $req){
        $ss = UM::getUserInfoByToken($req);
        if($ss->status_code !=200) return HttpRes::raw($ss);
        return HttpRes::raw(UM::getUserInfoByToken($req));
    }
}
