<?php

namespace App\Http\Controllers;

use App\AccountingDrivers\QuickBooksHelper;
use App\Http\Requests\ExpenseRequest;
use App\Transactions;
use Carbon\Carbon;

class WebhookController extends Controller
{
    /**
     * Function to handle expense request entry from webhook
     *
     * @param ExpenseRequest $request Validated expense request params will be received here
     * @return \Illuminate\Http\Response
     */
    public function handle(ExpenseRequest $request)
    {
        // get signature from header
        $receivedSignature = $request->header('X-Signature');

        // make signature from received data
        $makeSignature = hash_hmac('sha256', json_encode($request->all()), env('WEBHOOK_SECRET'));

        // check if not matched return error
        if($receivedSignature != $makeSignature) {
            // return $this->respondJson(400, 'signature_error', false);
        }

        // if arrived here start inserting data
        $tx = new Transactions;
        $tx->transaction_id = $request->transaction_id;
        $tx->token = $request->token;
        $tx->transaction_type = $request->transaction_type;
        $tx->transaction_status = $request->transaction_status;
        $tx->merchant_code = $request->merchant_code;
        $tx->merchant_name = $request->merchant_name;
        $tx->merchant_country = $request->merchant_country;
        $tx->merchant_currency = $request->merchant_currency;
        $tx->amount = $request->amount;
        $tx->transaction_currency = $request->transaction_currency;
        $tx->transaction_amount = $request->transaction_amount;
        $tx->transaction_datetime = Carbon::parse($request->transaction_datetime)->format('Y-m-d H:i:s');
        $tx->auth_code = $request->auth_code;
        $tx->save();

        // accounting sync action here
        QuickBooksHelper::createExpenseRecord($tx);

        // return api response
        return $this->respondJson(200, 'success', true, $tx);
    }

    /**
     * Function to respond json
     *
     * @param integer $numericCode Http status code
     * @param string $message Human readable message
     * @param bool $status True or False
     * @param array $nvpArgs (Optional) Additional response arguments
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondJson($numericCode, $message, $status, $nvpArgs = null )
    {
        return response()->json(['status' => $status, 'message' => $message, 'code' => $numericCode, "data" => $nvpArgs]);
    }
}
