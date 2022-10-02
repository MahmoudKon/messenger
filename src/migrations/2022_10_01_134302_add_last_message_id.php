<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLastMessageId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('conversations', function (Blueprint $table) {
            $table->unsignedInteger('last_message_id');
            $table->foreign('last_message_id')->references('id')->on('messages')->onDelete('cascade');
        });

        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'last_seen')) {
                $table->timestamp('last_seen')->nullable();
            }

            if (! Schema::hasColumn('users', 'image')) {
                $table->string('image')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'last_seen')) {
                $table->dropColumn('last_seen');
            }

            if (! Schema::hasColumn('users', 'image')) {
                $table->dropColumn('image');
            }
        });
    }
}
