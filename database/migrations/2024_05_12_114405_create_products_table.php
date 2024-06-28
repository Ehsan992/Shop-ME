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
        Schema::create('products', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('category_id')->nullable();
            $table->unsignedBigInteger('subcategory_id')->nullable();
            $table->unsignedBigInteger('childcategory_id')->nullable();
            $table->unsignedBigInteger('brand_id')->nullable();
            $table->unsignedBigInteger('pickup_point_id')->nullable();
            $table->string('name', 255);
            $table->string('slug', 255)->nullable();
            $table->string('code', 255)->nullable();
            $table->string('unit', 255)->nullable();
            $table->string('tags', 255)->nullable();
            $table->string('color', 255)->nullable();
            $table->string('size', 255)->nullable();
            $table->string('video', 255)->nullable();
            $table->string('purchase_price', 255)->nullable();
            $table->string('selling_price', 255)->nullable();
            $table->string('discount_price', 255)->nullable();
            $table->string('stock_quantity', 255)->nullable();
            $table->string('warehouse', 255)->nullable();
            $table->string('description', 2000)->nullable();
            $table->string('additional_info', 2000)->nullable();
            $table->string('thumbnail', 255)->nullable();
            $table->string('images', 255)->nullable();
            $table->integer('featured')->nullable();
            $table->integer('today_deal')->nullable();
            $table->tinyInteger('new_added')->nullable();
            $table->integer('product_views')->default(0);
            $table->integer('status')->nullable();
            $table->integer('trendy')->default(0);
            $table->unsignedBigInteger('flash_deal_id')->nullable();
            $table->integer('cash_on_delivery')->nullable();
            $table->unsignedBigInteger('admin_id')->nullable();
            $table->string('date', 191)->nullable();
            $table->string('month', 191)->nullable();
            $table->timestamps();

            // Adding foreign key constraints
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('cascade');
            $table->foreign('subcategory_id')->references('id')->on('subcategories')->onDelete('cascade');
            $table->foreign('childcategory_id')->references('id')->on('childcategories')->onDelete('cascade');
            $table->foreign('brand_id')->references('id')->on('brands')->onDelete('cascade');
            // Assuming you have a `flash_deals` table
            $table->foreign('flash_deal_id')->references('id')->on('flash_deals')->onDelete('cascade');
            // Assuming you have an `admins` table
            $table->foreign('admin_id')->references('id')->on('admins')->onDelete('cascade');
        
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
