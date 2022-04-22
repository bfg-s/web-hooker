<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWebhookTable extends Migration
{
    /**
     * Run the migrations.
     * @table users
     *
     * @return void
     */
    public function up()
    {
        Schema::create('webhooks', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->nullableMorphs('wh');
            $table->string('organizer', 512);
            $table->string('event', 45);
            $table->json('settings');
            $table->string('hash', 512)->nullable();
            $table->smallInteger('status')->default('0');
            $table->json('response');
            $table->timestamp('response_at')->nullable();
            $table->timestamp('subscribe_at')->nullable();
            $table->timestamp('subscribed_at')->nullable();
            $table->timestamp('unsubscribe_at')->nullable();
            $table->timestamp('unsubscribed_at')->nullable();
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
       Schema::dropIfExists('webhooks');
     }
}
