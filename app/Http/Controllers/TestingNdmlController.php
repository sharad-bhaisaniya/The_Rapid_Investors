<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use SoapClient;
use SoapFault;

class TestingNdmlController extends Controller
{
    /**
     * TEMP: Check NDML KYC Status ONLY
     * NO upload, NO DB update
     */
    public function checkStatus(Request $request)
    {
        $request->validate([
            'pan' => 'required|string|min:10|max:10',
            'dob' => 'required|date',
        ]);

        $pan = strtoupper($request->pan);
        $dob = date('d-m-Y', strtotime($request->dob));

        try {
           $client = new \SoapClient(
                config('services.ndml.status_wsdl'),
                [
                    'trace' => true,
                    'exceptions' => true,
                ]
            );

            /**
             * NOTE:
             * Method name & params depend on NDML WSDL
             * This is a GENERIC SAFE STRUCTURE for testing
             */
            $response = $client->__soapCall('getKycStatus', [[
                'pan'  => $pan,
                'dob'  => $dob,
                'bpId' => config('services.ndml.bp_id'),
            ]]);

            return response()->json([
                'success' => true,
                'pan' => $pan,
                'dob' => $dob,
                'ndml_response' => $response,
                'kyc_status' => $response->kycStatus ?? null,
            ]);

        } catch (SoapFault $e) {

            return response()->json([
                'success' => false,
                'pan' => $pan,
                'dob' => $dob,
                'error' => 'NDML SOAP ERROR',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}