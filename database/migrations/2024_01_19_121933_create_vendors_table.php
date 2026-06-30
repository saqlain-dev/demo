<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('vendors', function (Blueprint $table) {
            $table->id();
            $table->string('company_name')->nullable();
            $table->string('contact_person_1')->nullable();
            $table->string('contact_person_2')->nullable();
            $table->string('address_1')->nullable();
            $table->string('address_2')->nullable();
            $table->string('address_3')->nullable();
            $table->string('email_address')->nullable();
            $table->string('telephone_1')->nullable();
            $table->string('telephone_2')->nullable();
            $table->string('cell_phone_1')->nullable();
            $table->string('cell_phone_2')->nullable();
            $table->string('fax_no')->nullable();
            $table->foreignId('supplier_id')->nullable()->constrained('type_values');
            $table->foreignId('service_provider_id')->nullable()->constrained('type_values');
            $table->string('main_area_of_business')->nullable();
            $table->string('other_area_of_business')->nullable();
            $table->unsignedTinyInteger('year_in_business')->nullable()->comment('1) 1 to 2 Years , 2) 2-5 Years, 3) Over 5 Years');
            $table->unsignedTinyInteger('ntn_registered')->nullable()->comment('1) No (Disqualified) 2) Yes with evidence provided 3) Yes without evidence provided');
            $table->string('ntn_number')->nullable();
            $table->unsignedTinyInteger('tax_filling')->nullable()->comment('1) Filer 2) Non-Filer');
            $table->unsignedTinyInteger('sales_tax_registration')->nullable()->comment('1) No  2) Yes with evidence provided 3) Yes without evidence provided');
            $table->string('sales_tax_number')->nullable();
            $table->unsignedTinyInteger('company_bank_account')->nullable()->comment('1) No (Disqualified)  2) Yes in Company’s name 3) Yes in some other name');
            $table->unsignedTinyInteger('shop_premises')->nullable()->comment('1) Excellent 2) Good 3) Reasonable 4) Poor');
            $table->unsignedTinyInteger('invoices')->nullable()->comment('1) Pre-printed 2) On Letterhead 3) On stamped paper');
            $table->unsignedTinyInteger('withholding_tax')->nullable()->comment('1) Allowed to be deducted from invoiced amount 2) Charged as extra on top of price');
            $table->unsignedTinyInteger('done_business_with')->nullable()->comment('1) An NGO (With evidence) 2) More than one NGO (with evidence) 3) No');
            $table->unsignedTinyInteger('past_experience_with_LAS')->nullable()->comment('1) No 2) Yes (verified)');
            $table->unsignedTinyInteger('past_experience_with_government')->nullable()->comment('1) No 2) Yes (with evidence)');
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');
            $table->softDeletes();
            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vendors');
    }
};
