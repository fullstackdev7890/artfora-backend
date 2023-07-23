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
use App\Http\Requests\Fedex\ShippingRateRequest;
use Illuminate\Support\Str;
use App\Models\Product;
use App\Models\User;


class FedexController extends Controller

{
    private $client;
    public $account_number;
    public $meter_number;
    public $client_id;
    public $api_key;
    public $client_secret;


    public function __construct()
    {
        $this->client_id = config('services.fedex.client_id');
        $this->client_secret = config('services.fedex.client_secret');
        $this->account_number = config('services.fedex.account_number');
        $this->client = new Client([
            'base_uri' => 'https://apis-sandbox.fedex.com/', // Replace with the FedEx API base URL
            'timeout' => 10,
        ]);
    }
    public function getAccessToken()
    {
        $grant_type = 'client_credentials';

        try {
            $response = $this->client->post('oauth/token', [
                'headers' => [
                    'Content-Type' => 'application/x-www-form-urlencoded',
                ],
                'form_params' => [
                    'grant_type' => 'client_credentials',
                    'client_id' => $this->client_id,
                    'client_secret' => $this->client_secret,
                ],
            ]);

            $responseBody = json_decode($response->getBody(), true);

            return $responseBody;
        } catch (RequestException $e) {
            return;
        }
    }
    public function countries()
    {
        $countryClient = new Client([
            'base_uri' => 'https://api.fedex.com/country/v2/countries', // Replace with the FedEx API base URL
            'timeout' => 10,
        ]);

        try {
            $response = $countryClient->get('', [
                'headers' => [
                    'Content-Type' => 'application/x-www-form-urlencoded',
                    'Authorization' => 'Bearer l7xx8daef6fbb1724f21b2ada537e059ad7b',
                ],
                'query' => [
                    '_' => '-1689800400',
                    'type' => 'recipient'
                ]

            ]);

            $responseBody = json_decode($response->getBody(), true);

            return $responseBody;
        } catch (RequestException $e) {
            return $e;
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
    {
        $auth = $this->getAccessToken();

        $token = $auth['access_token'];

        $response = $this->client->request('POST', 'address/v1/addresses/resolve', [
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $token,
            ],
            'body' => json_encode([
                'addressesToValidate' => [
                    [
                        'address' => [
                            'streetLines' => [$request->input('address'), $request->input('address2')],
                            'city' => $request->input('city'),
                            'stateOrProvinceCode' => $request->input('state'),
                            'postalCode' => $request->input('postal_code'),
                            'countryCode' => $request->input('code'),
                        ],
                    ],
                ],
            ]),
        ]);
        $statusCode = $response->getStatusCode();
        if ($statusCode == 200) {
            $result = json_decode($response->getBody(),);
        } else {
            $result = json_decode(false);
        }
        return $result;
    }

    public function postalCodeValidation(PostalCodeValidationRequest $request)
    {
        $auth = $this->getAccessToken();
        $token = $auth['access_token'];
        try {
            $response = $this->client->request('GET', 'country/v1/postal/validate', [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer ' . $token,
                    // 'x-customer-transaction-id' => $transactionId,


                ],
                'query' => [
                    'carrierCode' => ["FXCC"],
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
        } catch (RequestException $e) {
            return response()->json(['message' => 'An error occurred while validating the postal code']);
        }
    }

    public function shipRate(ShippingRateRequest $request)

    {
        $data = $request->onlyValidated();
        $data['user_id'] = $request->user()->id;
        $auth = $this->getAccessToken();
        $token = $auth['access_token'];
        $product = Product::where('id', $data['product_id'])->first();
        $seller = User::where('id', $product->user_id)->first();
        $buyer = User::where('id', $data['user_id'])->first();
        $count = $data['count'];
        $isDelivery = $buyer->dev_email ? true : false;
      return($token);
        try {
            $response = $this->client->request('POST', 'rate/v1/rates/quotes', [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer ' . $token,
                ],
                'body' => json_encode([
                    'accountNumber' => ['value' => $this->account_number],
                    'rateRequestControlParameters' => [
                        'returnTransitTimes' => true,
                        'servicesNeededOnRateFailure' => true,
                        'varaibleOptions' => "FREIGHT_GUARANTEE",
                        'rateSortOrder' => "COMMITASCENDING",
                    ],
                    'requestedShipment' => [
                        'shipper' => [
                            'address' => [
                                'streetLines' => [$seller->sel_address??'', $seller->sel_address2??''],
                                'city' => $seller->sel_city??'',
                                'stateOrProvinceCode' => $seller->sel_state??'',
                                'postalCode' => $seller->sel_postal??'',
                                'countryCode' => $this->getCountryCode($seller->sel_country??''),
                                'residential' => false
                            ]
                        ],
                        'recipient' => [
                            'address' => [
                                'streetLines' => [$isDelivery ? $buyer->dev_address??'' : $buyer->inv_address??'', $isDelivery ? $buyer->dev_address2??"" : $buyer->inv_address2??''],
                                'city' => $isDelivery ? $buyer->dev_city??'' : $buyer->inv_city??'',
                                'stateOrProvinceCode' => $isDelivery ? $buyer->dev_state??'' : $buyer->inv_state??'',
                                'postalCode' => $isDelivery ? $buyer->dev_postal : $buyer->inv_postal,
                                'countryCode' => $this->getCountryCode($isDelivery ? $buyer->dev_country??'' : $buyer->inv_country??''),
                                'residential' => false
                            ]
                        ],
                        'preferredCurrency' => 'USD',
                        'rateRequestType' => ['PREFERRED'],
                        'pickupType' => 'DROPOFF_AT_FEDEX_LOCATION',
                        'serviceType' => 'STANDARD_OVERNIGHT',
                        'requestedPackageLineItems' => [
                            [
                                'groupPackageCount' => $count??'',
                                'weight' => [
                                    'value' => $product->weight??'',
                                    'units' => 'KG'
                                ],
                                'dimensions' => [
                                    'length' => $product->depth??'',
                                    'width' => $product->width??'',
                                    'height' => $product->height??'',
                                    'units' => 'CM'
                                ],
                                // 'declaredValue' => [
                                //     'currency' => 'USD',
                                //     'amount' => $count
                                // ],

                            ]
                        ],

                    ],
                    'carrierCodes' => ['FXCC']
                ])
            ]);
            $statusCode = $response->getStatusCode();
            $responseData = json_decode($response->getBody(), true);

            if ($statusCode == 200) {
                return response()->json($responseData->output->rateReplyDetails->ratedShipmentDetails->totalNetFedExCharge->amount);
            } else {
                return response()->json(['message' => 'invalid']);
            }
        } catch (RequestException $e) {
            return $e;
        }
    }
}
