<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

use FedEx\AddressValidationService\Request as addressRequest;
use FedEx\AddressValidationService\ComplexType;
use League\ISO3166\ISO3166;

use App\Http\Requests\Fedex\AddressValidationRequest;
use App\Http\Requests\Fedex\PostalCodeValidationRequest;
use Illuminate\Support\Str;
class FedexController extends Controller

{
    private $client;


    public function __construct()
    {
        $this->client = new Client([
            'base_uri' => 'https://apis-sandbox.fedex.com/', // Replace with the FedEx API base URL
            'timeout' => 10,
        ]);
    }
    public function getAccessToken()
    {
        $grant_type = 'client_credentials';
        $client_id = config('services.fedex.client_id');
        $client_secret = config('services.fedex.client_secret');

        try {
            $response = $this->client->post('oauth/token', [
                'headers' => [
                    'Content-Type' => 'application/x-www-form-urlencoded',
                ],
                'form_params' => [
                    'grant_type' => 'client_credentials',
                    'client_id' => $client_id,
                    'client_secret' => $client_secret,
                ],
            ]);

            $responseBody = json_decode($response->getBody(), true);
            
            return $responseBody;
        } catch (RequestException $e) {
            return; 
        }
    }
    public function getCountryCode($countryName)
    {
        $iso3166 = new ISO3166();
        $countryData = $iso3166->name($countryName);

        $countryCode = $countryData['alpha2'];

        return $countryCode;
    }

    public function addressValidation(Request $request)
    { $auth= $this->getAccessToken();
      
        $token = $auth['access_token'];
        $transactionId = Str::uuid()->toString();

        $response = $this->client->request('POST', 'address/v1/addresses/resolve', [
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' .$token,
                // 'x-customer-transaction-id' => $transactionId,
              
               
            ],
            'body' => json_encode([
              
                    'addressesToValidate' => [
                        [
                            'address' => [
                                'streetLines' =>[ $request->input('address'),$request->input('address2')],
                                'city' => $request->input('city'),
                                'stateOrProvinceCode' => $request->input('state'),
                                'postalCode' => $request->input('postal_code'),
                                'countryCode' =>$this->getCountryCode($request->input('country')),
                            ],
                        ],
                    ],
            ]),
        ]);
        $statusCode = $response->getStatusCode();
        if ($statusCode == 200) { $result = json_decode($response->getBody(), );}
        else{
            $result = json_decode(false);
        }
        return $result;
    }

    public function postalCodeValidation(PostalCodeValidationRequest $request)
    {
        $auth= $this->getAccessToken();
        $token = $auth['access_token'];
        dd($token);
        try {
            $response = $this->client->request('GET', 'country/v1/postal/validate' , [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer ' .$token,
                    // 'x-customer-transaction-id' => $transactionId,
                  
                   
                ],
                'query' => [
                    'carrierCode'=>"FDXE",
                    'countryCode' => $request->input('country_code'), // 'US
                    'postalCode' => $request->input('postal_code'),
                    'stateOrProvinceCode' => $request->input('state'),
                    'shipDate' => '2020-12-25'

                ]
            ]);

            $statusCode = $response->getStatusCode();
            $responseData = json_decode($response->getBody(), true);

            if ($statusCode == 200) {
                return response()->json(['message' => 'Postal code is valid']);
            } else {
                return response()->json(['message' => 'Postal code is invalid']);
            }
        } catch (Exception $e) {
            return response()->json(['message' => 'An error occurred while validating the postal code']);
        }
    }

    public function shipRate()
    {
        $auth= $this->getAccessToken();
        $token = $auth['access_token'];
       
        try {
            $response = $this->client->request('POST', 'rate/v1/rates/quotes' , [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer ' .$token,
                    // 'x-customer-transaction-id' => $transactionId,
                  
                   
                ],
                'body' => json_encode([
                'accountNumber' => ['value'=>config('services.fedex.account_number')],
                'rateRequestControlParameters'=>[
                    'returnTransitTimes'=>false,
                    'servicesNeededOnRateFailure'=>true,
                    'varaibleOptions'=>"FREIGHT_GUARANTEE",
                    'rateSortOrder'=>"SERVICENAMETRAIDIIONAL",
                ],
                'requestedShipment'=>[
                    'shipper' => [
                        'address' => [
                            'streetLines' => ['10 Fed Ex Pkwy',''],
                            'city' => 'Memphis',
                            'stateOrProvinceCode' => 'TN',
                            'postalCode' => '38115',
                            'countryCode' => 'US',
                            'residential' => false
                        ]
                    ],
                    'recipient' => [
                        'address' => [
                            'streetLines' => ['13450 Farmcrest Ct',''],
                            'city' => 'Herndon',
                            'stateOrProvinceCode' => 'VA',
                            'postalCode' => '20171',
                            'countryCode' => 'US',
                            'residential' => false
                        ]
                    ],
                    'emailNotificationDetail'=>[
                        'recipients'=>[
                            [
                                'emailAddress'=>'string',
                                'notificationEventType'=>['ON_DELIVERY'],
                                'smsDetails'=>[
                                    'phoneNumber'=>'string',
                                    'phoneNumberCountryCode'=>'string'
                                ],
                                'notificationEmailType'=>'HTML',
                                'emailNotificationRecipientType'=>'BROKER',
                                'notificationFormatType'=>'EMAIL',
                                'locale'=>'string'
                            ],
                            'personalMessage'=>'string',
                            'PrintedReference'=>[
                                'type'=>'BILL_OF_LADING',
                                'value'=>'string'
                            ],
                    ]],
                    'preferredCurrency'=>'EUR',
                    'pickupType' => 'DROPOFF_AT_FEDEX_LOCATION',
                    'rateRequestTypes' => ['ACCOUNT','LIST'],
                    'serviceType' => 'INTERNATIONAL_PRIORITY',
                    'requestedPackageLineItems'=>[
                        [
                            'groupPackageCount'=>1,
                            'weight'=>[
                                'value'=>2,
                                'units'=>'KG'
                            ],
                            'dimensions'=>[
                                'length'=>10,
                                'width'=>10,
                                'height'=>10,
                                'units'=>'CM'
                            ],
                            'declaredValue'=>[
                                'currency'=>'EUR',
                                'amount'=>100
                            ],
                            'variableHandlingChargeDetail'=>[
                                'fixedValue'=>[
                                    'currency'=>'EUR',
                                    'amount'=>10],
                                'percentValue'=>10,
                                'rateElementBasis'=>'BASE_CHARGE',
                                'rateTypeBasis'=>'LIST'
                            ]
                        ]
                   
                    ]
                ]
                ])
            ]);
            $statusCode = $response->getStatusCode();
            $responseData = json_decode($response->getBody(), true);

            if ($statusCode == 200) {
                return response()->json($responseData);
            } else {
                return response()->json(['message' => 'invalid']);
            }
        } catch (RequestException $e) {
            return response()->json(['message' => 'An error occurred while validating the postal code']);
        }

    }
}
