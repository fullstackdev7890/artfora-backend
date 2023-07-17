<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

use FedEx\AddressValidationService\Request as addressRequest;
use FedEx\AddressValidationService\ComplexType;




class FedexController extends Controller
{
    public function getAccessToken()
    {
        $grant_type = 'client_credentials';
        $client_id = config('services.fedex.client_id');
        $client_secret = config('services.fedex.client_secret');

        try {
            $client = new Client();
            $response = $client->post('https://apis-sandbox.fedex.com/oauth/token', [
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
            $authToken = $responseBody['access_token'];
            return $authToken;
        } catch (RequestException $e) {
            // Handle authentication request errors
        }
    }
    // public function getCountryCode($countryName)
    // {
    //     $iso3166 = new ISO3166();
    //     $countryData = $iso3166->search($countryName);

    //     $countryCode = $countryData['alpha2'];

    //     return $countryCode;
    // }

    public function addressValidation(AddressRequest $request)
    {
        $addressValidationRequest = new ComplexType\AddressValidationRequest();

        // User Credentials
        $addressValidationRequest->WebAuthenticationDetail->UserCredential->Key = config('services.fedex.key');
        $addressValidationRequest->WebAuthenticationDetail->UserCredential->Password = config('services.fedex.password');

        // Client Detail
        $addressValidationRequest->ClientDetail->AccountNumber = config('services.fedex.account_number');
        $addressValidationRequest->ClientDetail->MeterNumber = config('services.fedex.meter_number');
        // Version
        $addressValidationRequest->Version->ServiceId = 'aval';
        $addressValidationRequest->Version->Major = 8;
        $addressValidationRequest->Version->Intermediate = 0;
        $addressValidationRequest->Version->Minor = 0;

        // Address(es) to validate.
        $addressValidationRequest->AddressesToValidate = [new ComplexType\AddressToValidate()]; // just validating 1 address in this example.
        $addressValidationRequest->AddressesToValidate[0]->Address->StreetLines = ['12345 Main Street'];
        $addressValidationRequest->AddressesToValidate[0]->Address->City = 'Anytown';
        $addressValidationRequest->AddressesToValidate[0]->Address->StateOrProvinceCode = 'NY';
        $addressValidationRequest->AddressesToValidate[0]->Address->PostalCode = 47711;
        $addressValidationRequest->AddressesToValidate[0]->Address->CountryCode = 'US';

        $request = new addressRequest();
        try {
            $addressValidationReply = $request->getAddressValidationReply($addressValidationRequest);
            return ($addressValidationReply);
        } catch (\Exception $e) {
            return $e->getMessage();
            return $request->getSoapClient()->__getLastResponse();
        }
    }

    public function ship()
    {
    }
}
