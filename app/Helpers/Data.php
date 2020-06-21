<?php

namespace App\Helpers;

class Data
{
    public $successStatus = 200;
    public $errorStatus = 401;

    public function formateSuccessResponse($result){
        return response()->json(
            [
                'status' => true,
                'message' => '',
                'result' => $result
            ], 
            $this->successStatus
        ); 
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
            $this->errorStatus
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
}
