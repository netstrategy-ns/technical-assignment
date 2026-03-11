<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->uuid('public_id')->nullable()->unique()->after('id');
        });

        DB::table('orders')
            ->whereNull('public_id')
            ->orderBy('id')
            ->cursor()
            ->each(static function (object $order): void {
                DB::table('orders')
                    ->where('id', $order->id)
                    ->update(['public_id' => (string) Str::uuid()]);
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->dropColumn('public_id');
        });
    }
};
