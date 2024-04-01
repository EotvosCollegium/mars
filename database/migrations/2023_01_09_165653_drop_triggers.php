<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //        DB::unprepared('DROP TRIGGER trigger_create_print_account_for_user');
        //        DB::unprepared('DROP TRIGGER trigger_create_internet_access_for_user');
        //        DB::unprepared('DROP TRIGGER trigger_print_account_history_balance');
        //        DB::unprepared('DROP TRIGGER trigger_insert_print_account_history_free_pages');
        //        DB::unprepared('DROP TRIGGER trigger_update_print_account_history_free_pages');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //        DB::unprepared('
        //            CREATE TRIGGER trigger_create_print_account_for_user
        //            AFTER INSERT ON users
        //            FOR EACH ROW
        //            INSERT INTO print_accounts(user_id) VALUES (NEW.id);
        //        ');
        //        DB::unprepared('
        //            CREATE TRIGGER trigger_create_internet_access_for_user
        //            AFTER INSERT ON users
        //            FOR EACH ROW
        //            INSERT INTO internet_accesses(user_id) VALUES (NEW.id);
        //        ');
        //        DB::unprepared('
        //            CREATE TRIGGER trigger_print_account_history_balance
        //            AFTER UPDATE ON print_accounts
        //            FOR EACH ROW
        //                BEGIN
        //                    IF (NEW.balance <> OLD.balance) THEN
        //                        INSERT INTO print_account_history(
        //                            user_id,
        //                            balance_change,
        //                            free_page_change,
        //                            deadline_change,
        //                            modified_by)
        //                        VALUES(OLD.user_id, NEW.balance - OLD.balance, 0, NULL, NEW.last_modified_by);
        //                    END IF;
        //                END;
        //        ');
    }
};
