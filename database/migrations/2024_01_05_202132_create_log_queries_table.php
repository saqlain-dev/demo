<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
	public function up()
	{
		Schema::create('log_queries', function (Blueprint $table) {
			$table->id();
			$table->foreignId('user_id')->constrained();
			$table->string('table_name', 100);
			$table->string('query_type', 10);
			$table->longText('data');
			$table->timestamps();
		});
	}

	public function down()
	{
		Schema::dropIfExists('log_queries');
	}
};
