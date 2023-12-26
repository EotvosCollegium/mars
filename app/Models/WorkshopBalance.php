<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkshopBalance extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'semester_id',
        'workshop_id',
        'allocated_balance',
        'used_balance',
    ];

    /**
     * Returns the workshop the balance belongs to.
     */
    public function workshop()
    {
        return $this->belongsTo('App\Models\Workshop');
    }

    /**
     * Returns the semester the balance has been calculated for.
     */
    public function semester()
    {
        return $this->belongsTo('App\Models\Semester');
    }

    /**
     * Generates all the workshops' allocated balance in the current semester.
     * For all active members in a workshop who paid kkt:
     *      paid kkt * (isResident ? $workshop_balance_resident
     *                              : $workshop_balance_extern)
     *                / member's workshops' count
     * Uses the config values for the ratios if they are null.
     * 
     * @param Semester $semester
     * @param ?float $workshop_balance_resident
     * @param ?float $workshop_balance_extern
     * @return void
     */
    public static function generateBalances(Semester $semester,
                                            ?float $workshop_balance_resident = null,
                                            ?float $workshop_balance_extern = null): void
    {
        if (is_null($workshop_balance_resident)) {
            $workshop_balance_resident = config("custom.workshop_balance_resident");
        }
        if (is_null($workshop_balance_extern)) {
            $workshop_balance_extern = config("custom.workshop_balance_extern");
        }

        $workshops = Workshop::with('users:id')->get();

        if (!self::where('semester_id', $semester->id)->count()) {
            $balances = [];
            foreach ($workshops as $workshop) {
                $balances[] = [
                    'semester_id' => $semester->id,
                    'workshop_id' => $workshop->id
                ];
            }

            self::insert($balances);
        }

        $active_users = User::active($semester->id)->with(['roles' => function ($q) {
            $q->where('name', Role::COLLEGIST);
        }, 'workshops:id'])->get()->keyBy('id')->all();

        foreach ($workshops as $workshop) {
            $balance = 0;
            $resident = 0;
            $extern = 0;
            $not_yet_paid = 0;
            foreach ($workshop->users as $member) {
                if (isset($active_users[$member->id])) {
                    $amount = $member->paidKKTInSemester($semester);
                    if (!is_null($amount)) {
                        if ($member->isResident()) {
                            $amount *= $workshop_balance_resident;
                            $resident++;
                        } else {
                            $amount *= $workshop_balance_extern;
                            $extern++;
                        }
                        $balance += $amount / $member->workshops->count();
                    } else {
                        $not_yet_paid++;
                    }
                }
            }
            self::where(['semester_id' => $semester->id, 'workshop_id' => $workshop->id])
                ->update([
                    'allocated_balance' => $balance,
                    'extern' => $extern,
                    'resident' => $resident,
                    'not_yet_paid' => $not_yet_paid
                ]);
        }
    }
}
