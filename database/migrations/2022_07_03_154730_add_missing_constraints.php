<?php

use App\Models\EducationalInformation;
use App\Models\LocalizationContribution;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMissingConstraints extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Transaction::whereNotIn('receiver_id', User::pluck('id'))->update(['receiver_id' => null]);
        Transaction::whereNotIn('payer_id', User::pluck('id'))->update(['payer_id' => null]);

        Schema::table('transactions', function (Blueprint $table) {
            $table->foreign('checkout_id')->references('id')->on('checkouts');
            $table->foreign('receiver_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('payer_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('semester_id')->references('id')->on('semesters');
            $table->foreignId('payment_type_id')->change()->references('id')->on('payment_types');
        });

        Schema::table('checkouts', function (Blueprint $table) {
            $table->dropColumn('password'); //not used anymore
        });

        LocalizationContribution::whereNotIn('contributor_id', User::pluck('id'))->update(['contributor_id' => null]);

        Schema::table('localization_contributions', function (Blueprint $table) {
            $table->foreignId('contributor_id')->nullable()->change()->references('id')->on('users')->nullOnDelete();
        });

        EducationalInformation::whereNotIn('user_id', User::pluck('id'))->delete();

        Schema::table('educational_information', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });


        Schema::table('faults', function (Blueprint $table) {
            $table->foreignId('reporter_id')->nullable()->change()->references('id')->on('users');
        });
        DB::statement("ALTER TABLE workshop_balances CHANGE COLUMN workshop_id workshop_id TINYINT UNSIGNED NOT NULL");
        Schema::table('workshop_balances', function (Blueprint $table) {
            $table->foreign('semester_id')->references('id')->on('semesters');
            $table->foreign('workshop_id')->references('id')->on('workshops');
        });

        Schema::table('import_items', function (Blueprint $table) {
            $table->foreignId('user_id')->change()->references('id')->on('users')->cascadeOnDelete();
        });

        \App\Models\PrintAccountHistory::whereNotIn('user_id', User::pluck('id'))->delete();
        Schema::table('print_account_history', function (Blueprint $table) {
            $table->foreignId('user_id')->change()->references('id')->on('users')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
