<?php
namespace App\Http\Controllers\API;

use Illuminate\Http\Request; 
use App\Http\Controllers\Controller; 
use App\User; 
use App\Helpers\Data as HelperData; 
use Illuminate\Support\Facades\Auth; 
use Validator;
use Twilio\Rest\Client;
use Twilio\Http\CurlClient;

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
                'phone_number' => ['required', 'numeric'],
            ]);

            if ($validator->fails()) { 
                return $this->helperData->formateErrorResponse($validator->errors());
            }
            
            $input = $request->all(); 

            /********* Send SMS **********/ 
            $client = new Client($this->twilioSid, $this->twilioToken);
            $curlOptions = [ CURLOPT_SSL_VERIFYHOST => false, CURLOPT_SSL_VERIFYPEER => false];
		    $client->setHttpClient(new CurlClient($curlOptions));

            $result = $client->verify->v2
                ->services($this->twilioVerifySid)
                ->verifications
                ->create($input['phone_number'], "sms");

            /******** Mobile number inserted into user table *********/ 
            User::where('id', $userId)->update(['phone_number' => $input['phone_number']]);

            return $this->helperData->formateSuccessResponse('OPT successfully sended');

        } catch (\Exception $ex) {
            return $this->helperData->formateErrorResponse($ex->getMessage());
        }

    }

    public function verifyOtp(Request $request){
    	try {
    		$userId = Auth::user()->id; 
	        $validator = Validator::make($request->all(), [ 
	        	'verification_code' => ['required', 'numeric'],
	            'phone_number' => ['required', 'numeric']
	        ]);

	        $client = new Client($this->twilioSid, $this->twilioToken);
	        $curlOptions = [ CURLOPT_SSL_VERIFYHOST => false, CURLOPT_SSL_VERIFYPEER => false];
			$client->setHttpClient(new CurlClient($curlOptions));
	        
	        $input = $request->all(); 

	        $verification = $client->verify->v2
	        	->services($this->twilioVerifySid)
	            ->verificationChecks
	            ->create($input['verification_code'], array('to' => $input['phone_number']));

	        if ($verification->valid) {
	            $user = tap(User::where('phone_number', $input['phone_number']))->update(['is_verified' => true]);
	            return $this->helperData->formateSuccessResponse('Phone Number Verified');
	        }else{
	            return $this->helperData->formateErrorResponse('Verification code is not valid');
	        }
    	}catch (\Exception $ex) {
            return $this->helperData->formateErrorResponse($ex->getMessage());
        }
    }

}