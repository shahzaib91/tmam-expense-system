<?php

namespace App\Http\Controllers;

use App\AccountingDrivers\QuickBooksHelper;
use App\Tokens;
use App\Transactions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;

class FrontendController extends Controller
{
    /**
     * Function redirects from root to list page
     *
     * @return redirect
     */
    public function index()
    {
        return redirect(URL::to('list/1000'));
    }

    /**
     * Function to list transactions of merchant
     *
     * @param integer $merchant_id Merchant id value from url
     * @return view
     */
    public function list($merchant_id)
    {
        $transactions = Transactions::where('merchant_code', $merchant_id)->get();
        return view('index', ['transactions' => $transactions]);
    }

    /**
     * Function to trigger quick books authentication to obtain access token
     *
     * @return redirect
     */
    public function quickBooksAuth()
    {
        return redirect(QuickBooksHelper::getAuthUrl());
    }

    /**
     * Function to receive code from query string returned by quick books auth
     *
     * @param Request $request Code query string value
     * @return void
     */
    public function handleQuickBooksAuth(Request $request)
    {
        if(!empty($request->code)) {

            // get access token
            $accessToken = QuickBooksHelper::exchangeCodeForToken($request->code, env('QUICKBOOKS_REALM_ID'));

            // start storing access token
            if($accessToken['status'] && $accessToken['token'] != null) {

                // insert token
                $token = new Tokens;
                $token->access_token =  $accessToken['token']->getAccessToken();
                $token->access_token_expiry = $accessToken['token']->getAccessTokenExpiresAt();
                $token->refresh_token = $accessToken['token']->getRefreshToken();
                $token->refresh_token_expiry =$accessToken['token']->getRefreshTokenExpiresAt();
                $token->platform = 'quickbooks';
                $token->merchant_code = 1000;
                $token->save();

                // display obtained message
                echo 'Access token has been obtained successfully! <a href="'.URL::to('/').'">Home</a>';
                return;

            }
        }

        echo $accessToken['message'].' <a href="'.URL::to('/auth').'">Retry</a>';
        return;
    }


}
