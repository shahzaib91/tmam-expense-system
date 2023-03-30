<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id('expense_id');
            $table->integer('transaction_id')->unique();
            $table->string('token');
            $table->enum('transaction_type', ['d','c']);
            $table->enum('transaction_status', [0,1]);
            $table->string('merchant_code');
            $table->string('merchant_name');
            $table->string('merchant_country');
            $table->string('merchant_currency');
            $table->double('amount');
            $table->string('transaction_currency');
            $table->double('transaction_amount');
            $table->dateTime('transaction_datetime');
            $table->string('auth_code');
            $table->enum('is_synced', [0,1])->default(0);
            $table->longText('sync_response')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('transactions');
    }
}
