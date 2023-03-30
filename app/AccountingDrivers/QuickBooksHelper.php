<?php
namespace App\AccountingDrivers;

use App\Tokens;
use App\Transactions;
use Illuminate\Support\Facades\URL;
use QuickBooksOnline\API\Core\OAuth\OAuth2\OAuth2AccessToken;
use QuickBooksOnline\API\DataService\DataService;
use QuickBooksOnline\API\Facades\Purchase;

class QuickBooksHelper
{
    private static $merchantId = 1000;

    /**
     * Function to create transaction in quick books
     *
     * @param Transactions $tx Takes newly created transaction as input and pass it to expense entry
     * @return array
     */
    public static function createExpenseRecord(Transactions $tx)
    {
        // get validated access token
        $accessToken = self::getActiveAccessToken();

        // in case no valid access token
        if(!$accessToken["status"]){
            return false;
        }

        // get data service object and config auth client
        $ds = self::getDataService();
        $at = new OAuth2AccessToken(env('QUICKBOOKS_CLIENT_ID'), env('QUICKBOOKS_CLIENT_SECRET'), $accessToken["token"]->access_token, $accessToken["token"]->refresh_token);
        $at->setAccessToken($accessToken["token"]->access_token);
        $at->setRealmID(env('QUICKBOOKS_REALM_ID'));
        $ds->updateOAuth2Token($at);

        // prepare expense entry and add to service object
        $resultingObj = $ds->Add(Purchase::create([
            "PaymentType" => "CreditCard",
            "AccountRef" =>
            [
                "name" => "Visa",
                "value" => "3"
            ],
            "Line" =>
            [
                [
                    "DetailType" => "AccountBasedExpenseLineDetail",
                    "Amount" => $tx->transaction_amount,
                    "AccountBasedExpenseLineDetail" =>
                    [
                        "AccountRef" =>
                        [
                            "name" => "Meals and Entertainment",
                            "value" => "13"
                        ]
                    ]
                ]
            ]
        ]));

        // handle response
        $error = $ds->getLastError();
        if ($error) {

            // update transaction
            $tx->sync_response = $error->getResponseBody();
            $tx->save();

            return ['status'=>false, 'id'=>null, 'message'=>$error->getHttpStatusCode().': '.$error->getOAuthHelperError().': '.$error->getResponseBody()];
        }

        // update transaction
        $tx->is_synced	 = '1';
        $tx->sync_response = $resultingObj->Id;
        $tx->save();

        return ['status'=>true, 'id'=>$resultingObj->Id];
    }

    /**
     * Function to get auth url
     * @return string
     */
    public static function getAuthUrl()
    {
        $ds = self::getDataService();
        $loginHelper = $ds->getOAuth2LoginHelper();
        $authUrl = $loginHelper->getAuthorizationCodeURL();
        return $authUrl;
    }

    /**
     * Function to get access token
     * @param string $code Obtained code during the 0auth
     * @param string $realmId Obtained from the app
     * @return array
     * @throws \Exception
     */
    public static function exchangeCodeForToken($code, $realmId)
    {
        if(empty($code) || empty($realmId))
            throw new \Exception("Invalid token");

        try
        {
            $ds = self::getDataService();
            $loginHelper = $ds->getOAuth2LoginHelper();
            $accessToken = $loginHelper->exchangeAuthorizationCodeForToken($code, $realmId);
            $ds->updateOAuth2Token($accessToken);
            return ['status'=>true, 'token'=>$accessToken, 'message'=>null];
        }
        catch(\Exception $ex)
        {
            return ['status'=>false, 'token'=>null, 'message'=>$ex->getMessage()];
        }
    }

    /**
     * Helper function to retrieve data service object with settings saved in it
     * @return DataService
     * @throws \QuickBooksOnline\API\Exception\SdkException
     */
    private static function getDataService()
    {
        return DataService::Configure
        ([
            'auth_mode' => 'oauth2',
            'ClientID' => env('QUICKBOOKS_CLIENT_ID'),
            'ClientSecret' =>  env('QUICKBOOKS_CLIENT_SECRET'),
            'RedirectURI' => URL::to("quickbooks-response"),
            'scope' => 'com.intuit.quickbooks.accounting openid profile email phone address',
            'baseUrl' => self::getBasePath()
        ]);
    }

    /**
     * Helper function to get base/rest api path
     * @return mixed
     */
    private static function getBasePath()
    {
        if(env('QUICKBOOKS_ENV')=="sandbox"){
            return env("QUICKBOOKS_SANDBOX_URL");
        } else {
            return env("QUICKBOOKS_PRODUCTION_URL");
        }
    }

    /**
     * Helper function to check if we have valid refresh token
     * @return bool|null
     */
    private static function isValidRefreshToken()
    {
        $accessToken = Tokens::getTokenInfoByMerchantID(self::$merchantId);
        if($accessToken)
        {
            $timeNow = strtotime(date('Y-m-d H:i:s'));
            $timeThen = strtotime($accessToken->refresh_token_expiry);
            $timeResult = $timeThen - $timeNow;

            if($timeResult > 3600 ) {
                return true;
            }

            return false;
        }
        return null;
    }

    /**
     * Helper function to check if we have valid access token
     * @return bool|null
     */
    private static function isValidAccessToken()
    {
        $accessToken = Tokens::getTokenInfoByMerchantID(self::$merchantId);
        if($accessToken)
        {
            $timeNow = strtotime(date('Y-m-d H:i:s'));
            $timeThen = strtotime($accessToken->access_token_expiry);
            $timeResult = $timeThen - $timeNow;

            if($timeResult > 300 ) {
                return true;
            }

            return false;
        }
        return null;
    }

    /**
     * Helper function to check if we have valid refresh token in db
     * @return bool
     */
    private static function hasAccessAndRefreshTokens()
    {
        $accessToken = Tokens::getTokenInfoByMerchantID(self::$merchantId);
        if($accessToken)
            return true;
        return false;
    }

    /**
     * Helper function to get access token in various requests
     * @return array
     * @throws \Exception
     */
    private static function getActiveAccessToken()
    {
        if(!self::hasAccessAndRefreshTokens() || !self::isValidRefreshToken())
            throw new \Exception("Please authenticate quickbooks api from settings panel!");

        try
        {
            $ds = self::getDataService();
            $loginHelper = $ds->getOAuth2LoginHelper();
            $accessToken = Tokens::getTokenInfoByMerchantID(self::$merchantId);

            if(self::isValidAccessToken()) {
                return ["status"=>true, "token"=> $accessToken ];
            }
            $accessTokenObj = $loginHelper->refreshAccessTokenWithRefreshToken($accessToken->refresh_token);

            $accessToken->access_token =  $accessTokenObj->getAccessToken();
            $accessToken->access_token_expiry = $accessTokenObj->getAccessTokenExpiresAt();
            $accessToken->refresh_token = $accessTokenObj->getRefreshToken();
            $accessToken->refresh_token_expiry = $accessTokenObj->getRefreshTokenExpiresAt();
            $accessToken->save();

            return ["status"=>true, "token"=> $accessToken];
        }
        catch(\Exception $ex)
        {
            return ["status"=>false, "token"=>$ex->getMessage()];
        }
    }
}
