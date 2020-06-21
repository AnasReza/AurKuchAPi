<?php
namespace App\Http\Controllers\API;

use Illuminate\Http\Request; 
use App\Http\Controllers\Controller; 
use App\User; 
use App\Helpers\Data as HelperData; 
use Illuminate\Support\Facades\Auth; 
use Validator;
use Twilio\Rest\Client;

class SmsController extends Controller 
{
	private $twilioToken;
    private $twilioSid;
    private $twilioVerifySid;
    protected $helperData;

    public function __construct(
    	HelperData $helperData
    ) {
        $this->twilioToken = getenv('TWILIO_AUTH_TOKEN');
        $this->twilioSid = getenv('TWILIO_SID');
        $this->twilioVerifySid = getenv('TWILIO_VERIFY_SID');
    	$this->helperData = $helperData;
    }

	public function sendOtpToNumber(Request $request){
        try {

            $userId = Auth::user()->id; 

            $validator = Validator::make($request->all(), [ 
                'phone_number' => ['required', 'numeric', 'unique:users'],
            ]);

            if ($validator->fails()) { 
                return $this->helperData->formateErrorResponse($validator->errors());
            }
            
            $input = $request->all(); 

            /********* Send SMS **********/ 
            // $twilio = new Client($this->twilioSid, $this->twilioToken);
            // $result = $twilio->verify->v2
            //     ->services($this->twilioVerifySid)
            //     ->verifications
            //     ->create($input['phone_number'], "sms");

            /******** Mobile number inserted into user table *********/ 
            User::where('id', $userId)->update(['phone_number' => $input['phone_number']]);

            return $this->helperData->formateSuccessResponse('OPT successfully sended');

        } catch (\Exception $ex) {
            return $this->helperData->formateErrorResponse($ex->getMessage());
        }

    }

    /** 
     * details api 
     * 
     * @return \Illuminate\Http\Response 
     */ 
    public function details() 
    { 
        var_dump('expression');   
    }

}