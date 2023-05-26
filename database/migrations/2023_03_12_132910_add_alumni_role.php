<?php

use App\Http\Controllers\Secretariat\SemesterEvaluationController;
use App\Models\SemesterStatus;
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
        if (DB::table('roles')->where('name', 'alumni')->doesntExist()) {
            DB::table('roles')->insert([
                'name' => 'alumni',
                'has_workshops' => false,
                'has_objects' => false
            ]);
        }

        SemesterStatus::withoutEvents(function () {
            foreach (SemesterStatus::where('status', 'DEACTIVATED')->get() as $semesterStatus) {
                SemesterEvaluationController::deactivateCollegist($semesterStatus->user);
            }

            SemesterStatus::whereIn('status', ['PENDING', 'INACTIVE'])->update(['status' => 'PASSIVE']);
            SemesterStatus::where('status', 'DEACTIVATED')->delete();
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
};
