<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUrlsTable extends Migration
{

    public function up()
    {
        Schema::create('urls', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('url')->comment('URL文字列');
            $table->boolean('exist')->comment('404チェック結果');
            $table->timestamp('checked_at')
                ->nullable()
                ->useCurrent()
                ->comment('チェックされた直近のタイムスタンプ');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('urls');
    }
}
