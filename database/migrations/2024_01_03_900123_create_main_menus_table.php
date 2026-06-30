<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
	public function up(): void
	{
		Schema::create('main_menus', function (Blueprint $table) {
			$table->id();
			$table->string('name', 70);
			$table->string('route', 100);
			$table->string('permission', 125)->nullable();
			$table->tinyInteger('order')->default(0);
			$table->bigInteger('parent_id')->default(0);
			$table->tinyInteger('display')->default(1);
			$table->timestamps();
		});
	}

	public function down(): void
	{
		Schema::dropIfExists('main_menus');
	}
};
