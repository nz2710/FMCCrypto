<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCryptoTransactionsTable  extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('solana_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('signature', 128)->unique();
            $table->string('from_address');
            $table->string('to_address');
            $table->decimal('amount', 18, 9);
            $table->string('status')->default('pending');
            $table->timestamp('block_time')->nullable();
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
        Schema::dropIfExists('solana_transactions');
    }
}
