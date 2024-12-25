<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTransactionsTable extends Migration
{
    public function up()
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wallet_id')->constrained();
            $table->string('transaction_hash', 88)->unique();
            $table->string('block_hash', 88)->nullable();
            $table->unsignedBigInteger('block_number')->nullable();
            $table->enum('type', ['deposit', 'withdrawal']);
            $table->decimal('amount', 18, 9);
            $table->enum('status', ['pending', 'processing', 'completed', 'failed'])
                  ->default('pending');
            $table->text('error_message')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();

            $table->index(['wallet_id', 'status']);
            $table->index(['block_number']);
            $table->index(['transaction_hash']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('transactions');
    }
}
