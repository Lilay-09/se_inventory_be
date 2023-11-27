<?php

namespace App\Models;

use Config;
use Firebase\JWT\JWT;
// use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Illuminate\Database\Eloquent\Model;
use DB;
use Firebase\JWT\Key;
class UM //extends Model
{
    // use HasFactory;
    protected static $jwt_key = 'Sth about key';
    protected static $jwt_secret = 'HS256';
    protected static $default_lf_span = 3600; //1hour
    private static $app_id = null;

    protected static $user_classes = [];

    function __construct(){
        self::$app_id = Config::get('app.app_id');
        self::$user_classes = [
            'admin' => ['name' => 'admin','token_lifetime' => self::$default_lf_span],
            'staff' => ['name' => 'staff','token_lifetime' => self::$default_lf_span]
        ];
    }

    static function createJWTToken($userInfo=[],$life_span=null){
        $life_span = $life_span?$life_span:self::$default_lf_span;
        $nowTime = time();
        $payload['issue_at'] = $nowTime;
        $payload['my_key'] = '2398sdklfjsperysfhw48972345rem';
        $payload['app_key'] = Config::get('app.app_id');
        $payload['exp'] = date('Y-m-d H:i:s',strtotime($life_span));
        return JWT::encode($payload,self::$jwt_key,self::$jwt_secret);
    }

    function saveUser($arr){
        $v_rule = [
            'user_id' => '0|number|exists=users.id',
            'login_name' => '1|string|1,30',
            'password' => '1|string|1,30',
            'email' => '0|email|1,100',
            'phone_number' => '0|phone',
            'full_name' => '0|string|1,50',
            'official_code' => '0|string|1,20',
            'official_id' => '0|number',
            'app_id' => '1|string',
            'photo' => '0|string',
            'user_class' => '1|choice|admin,staff'
        ];
        $res = validateRule($arr,$v_rule);
        if($res->error) return ObjectRes::error($res->error);
        $inputs = $res->result;
        if(self::$app_id !== $inputs['app_id']) return ObjectRes::error('Your app ID could not be registered with the application');
        if(!isset($inputs['full_name'])) $inputs['full_name'] = $inputs['login_name'];
        $image = $inputs['photo'];
        // return Utility::saveImage($image,'');
        $hpwd = password_hash($inputs['password'],null);
        $inputs['password'] = $hpwd;
        $user_id = $inputs['user_id'];
        $login_name = $inputs['login_name'];
        $app_id = $inputs['app_id'];
        if(self::validateDuplicateUser($user_id,$login_name,$app_id)) return ObjectRes::error('User is already exists');
        unset($inputs['user_id'],$inputs['photo']);
        $newID = saveData('users',['id' => $user_id],$inputs);
        return ObjectRes::depends(1,$inputs);
    }

    function verifyUser($login_name,$password){
        if(!$login_name) return ObjectRes::error('Login name is not valid');
        $user = DB::table('users')->where('login_name',$login_name)->selectRaw('official_id,official_code,id,login_name,password,app_id,is_lock,user_class')->first();
        if(!$user) return ObjectRes::error('User does not exist or not available to access this application');
        if($user->is_lock) return ObjectRes::error('Your account has been locked');
        if($user->app_id !== self::$app_id) return ObjectRes::error('You have not permission to access this application');
        $user_id = $user->id;
        $app_id = $user->app_id;
        $user_class = $user->user_class;
        if(password_verify($password,$user->password)){
            $session = self::setUserSession($app_id,$user_id,$user_class);
            if($session->status == 'OK'){
                $user_info = (object)[
                    'user_id' => $user_id,
                    'id' => $user_id,
                    'official_id' => $user->official_id,
                    'access_token' => $session->access_token,
                    'user_class' => self::$user_classes[$user_class]['name'],
                    'exp_at' => $session->exp_at,//diffDays(getNowTime(),$session->exp_at)
                ];
                return (object)[
                    'status' => 'OK',
                    'user' => $user_info,
                    'error_message' => $session->error_message
                ];
            }
            return ObjectRes::error($session->error_message);
        }
        return ObjectRes::error('Incorrect password');
    }

    static function setUserSession($app_id,$user_id,$user_class){
        $expired_at = date('Y-m-d H:i:s', time() + self::$user_classes[$user_class]['token_lifetime']);
        $access_token = self::createJWTToken([],$expired_at);
        if(!$user_id) return ObjectRes::error('Failed to login to this application');
        $notExpiredID = DB::table('user_sessions')->where('app_id',$app_id)->where('user_id',$user_id)->take(1)->value('id');
        if(!$notExpiredID){
            $x = DB::table('user_sessions')->where('user_id',$user_id)->where('app_id',$app_id)->where('expire_date','>',date('Y-m-d H:i:s'))->take(1)->value('id');
            if($x){
                saveData('user_sessions',['id'=>$notExpiredID],[
                    'user_id' => $user_id,
                    'app_id' => $app_id,
                    'last_active_date' => getNowTime(),
                    'access_token' => $access_token,
                    'expire_date' => $expired_at
                ]);
            }else{
                DB::table('user_sessions')->where('app_id',$app_id)->insert([
                    'user_id' => $user_id,
                    'app_id' => $app_id,
                    'start_date' => getNowTime(),
                    'last_active_date' => getNowTime(),
                    'access_token' => $access_token,
                    'expire_date' => $expired_at
                ]);
            }

        }else{
            saveData('user_sessions',['id'=>$notExpiredID],[
                'user_id' => $user_id,
                'app_id' => $app_id,
                'last_active_date' => getNowTime(),
                'access_token' => $access_token,
                'expire_date' => $expired_at
            ]);
        }
        $refresh_token = self::createJWTToken([],self::$user_classes[$user_class]['token_lifetime']);
        return (object)[
            'status' => 'OK',
            'access_token' => $access_token,
            'refresh_token' => $refresh_token,
            'exp_at' => $expired_at,
            'error_message' => null
        ];
    }

    static function validateDuplicateUser($id,$login_name,$app_id){
        $str_self = '1=1';
        if($id) $str_self = 'id <> '.$id;
        $user = DB::table('users')->whereRaw($str_self)->where('login_name',$login_name)->where( 'app_id',$app_id)->take(1)->value('id');
        return $user?true:false;
    }

    static function getUserInfoByToken($req,$prn=null){
        $validToken = self::validToken($req);
        if($validToken->status == 'Error'){
            return ObjectRes::error($validToken->error_message);
        }
        $access_token = $validToken->token;
        $user = DB::table('user_sessions as us')->join('users as u','u.id','=','us.user_id')->where('us.access_token',$access_token)->selectRaw('u.full_name,us.id,us.user_id')->first();
        if(!$user) return ObjectRes::error('Invalid Token or User not found');
        $res = (object)[
            'user'=>$user
        ];

        return ObjectRes::success($res);
    }



    static function validToken($req){
        $token = $req->bearerToken();
        if(!$token) return ObjectRes::error('Invalid token.');
        $decoded = JWT::decode($token, new Key(self::$jwt_key, self::$jwt_secret));
        $expirationTimestamp = $decoded->exp;
        $expiration = date('Y-m-d H:i:s', strtotime($expirationTimestamp));
        $currentTimestamp = date('Y-m-d H:i:s');
        if ($currentTimestamp > $expiration) {
            return ObjectRes::error('Token expired.');
        }
        return ObjectRes::success(['token'=>$token,'cur_date'=>$currentTimestamp,'exp_date'=>$expirationTimestamp]);
    }

}
