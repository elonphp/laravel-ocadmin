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
        Schema::create('organizations', function (Blueprint $table) {
            $table->id();
            $table->string('code', 15)->nullable()->unique()->comment('代碼');
            $table->unsignedInteger('parent_id')->default(0)->nullable()->comment('母公司、子公司, organization_id');
            $table->unsignedInteger('group_id')->default(0)->nullable()->comment('不相隸屬的多家公司組合, organization_id');
            $table->string('type', 1)->nullable()->comment('類型');
            $table->string('name', 100)->nullable()->comment('公司名稱');
            $table->string('short_name', 100)->nullable()->comment('公司簡稱');
            $table->string('telephone', 20)->nullable()->comment('電話');
            $table->string('contact_name', 50)->nullable()->comment('聯絡人姓名');
            $table->string('tax_id_number', 12)->nullable()->comment('稅號');
            $table->string('shipping_recipient', 50)->nullable()->comment('收件人');
            $table->string('shipping_mobile', 20)->nullable()->comment('收件人手機');
            $table->string('shipping_country_code', 2)->nullable()->comment('國家代碼');
            $table->string('shipping_zip_code', 15)->nullable()->comment('郵遞區號');
            $table->string('shipping_state', 100)->nullable()->comment('州');
            $table->string('shipping_city', 100)->nullable()->comment('市');
            $table->string('shipping_address1')->nullable()->comment('地址1');
            $table->string('shipping_address2')->nullable()->comment('地址2');
            $table->boolean('is_active')->default(false)->comment('啟用');
            $table->timestamps();
        });

        Schema::create('organization_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->string('locale', 10);
            $table->string('name');
            $table->string('short_name')->nullable()->default('');
            $table->softDeletes();
            $table->unique(['organization_id', 'locale']);
        });

        Schema::create('organization_identities', function (Blueprint $table) {
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->string('identity', 20)->comment('身份：dealer, customer, supplier');

            $table->primary(['organization_id', 'identity']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('organization_identities');
        Schema::dropIfExists('organization_translations');
        Schema::dropIfExists('organizations');
    }
};
