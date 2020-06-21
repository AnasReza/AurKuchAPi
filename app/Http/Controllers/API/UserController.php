<?php
namespace App\Http\Controllers\API;

use Illuminate\Http\Request; 
use App\Http\Controllers\Controller; 
use App\User; 
use Illuminate\Support\Facades\Auth; 
use Validator;
use Illuminate\Validation\Rule;

class UserController extends Controller 
{
    public $successStatus = 200;
    public $errorStatus = 401;

    /** 
     * login api 
     * 
     * @return \Illuminate\Http\Response 
     */ 
    public function login(){ 
        
        try{
            if(Auth::attempt(
                [
                    'username' => request('username'), 
                    'password' => request('password')
                ]
            )){ 
                $user = Auth::user(); 
                $result['token'] =  $user->createToken('MyApp')-> accessToken; 
                return $this->formateSuccessResponse($result); 
            } 
            else{ 
                return $this->formateErrorResponse('Unauthorised');
            } 
        }catch (\Exception $ex) {
            return $this->formateErrorResponse($ex->getMessage());
        }
    }

    /** 
     * Register api 
     * 
     * @return \Illuminate\Http\Response 
     */ 
    public function register(Request $request) 
    { 
        try {
            $validator = Validator::make($request->all(), [ 
                'username' => ['required','unique:users','min:5'],
                'password' => ['required', 'string', 'min:8'],
                'gender' => 'required'
            ]);
            
            if ($validator->fails()) { 
                return $this->formateErrorResponse($validator->errors());
            }

            $input = $request->all(); 

            $input['password'] = bcrypt($input['password']); 
            $user = User::create($input);

            $result['token'] =  $user->createToken('MyApp')-> accessToken;
            $result['username'] =  $user->username;

            return $this->formateSuccessResponse($result);

        } catch (\Exception $ex) {
            return $this->formateErrorResponse($ex->getMessage());
        }
    }

    /** 
     * details api 
     * 
     * @return \Illuminate\Http\Response 
     */ 
    public function details() 
    { 
        try{
            $user = Auth::user(); 
            $result = ['user' => $user];
            return $this->formateSuccessResponse($result);

        }catch (\Exception $ex) {
            return $this->formateErrorResponse($ex->getMessage());
        }
    }


    public function formateSuccessResponse($result){
        return response()->json(
            [
                'status' => true,
                'message' => '',
                'result' => $result
            ], 
            $this-> successStatus
        ); 
    }

    public function subArraysToString($ar, $sep = ', ') {
        $str = '';
        foreach ($ar as $val) {
            $str .= implode($sep, $val);
            $str .= $sep; // add separator between sub-arrays
        }
        $str = rtrim($str, $sep); // remove last separator
        return $str;
    }

    public function formateErrorResponse($message){
        
        if(is_object($message)){
            $messages = $message->getMessages();
            if(is_array($messages)){
                $message = $this->subArraysToString($messages,' ');
            }
        }
        return response()->json(
            [
                'status' => false,
                'message' => $message,
                'result' => []
            ], 
            $this-> errorStatus
        ); 
    } 
}