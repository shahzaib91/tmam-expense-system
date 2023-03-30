<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Type\Integer;

class Tokens extends Model
{
    /**
     * Function takes merchantId as input and return token if present
     *
     * @param Integer $merchantId
     * @return App\Tokens
     */
    public function getTokenInfoByMerchantID(Integer $merchantId)
    {
        return Tokens::where('merchant_code', $merchantId)->first();
    }
}
