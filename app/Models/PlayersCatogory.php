<?php

namespace App\Models;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PlayersCatogory extends Model
{
    protected $table = 'players_categories';


    ///static function
    public static function newCategory($title, $desc, array $details = [])
    {
        $newCatg = new self;
        $newCatg->title = $title;
        $newCatg->desc = $desc;
        try {
            $newCatg->save();
            if (count($details) > 0) {
                $newCatg->setDetails($details);
            }
        } catch (Exception $e) {
            report($e);
        }
    }


    ////model functions
    public function getDue($attendance): int|null
    {
        $this->loadMissing('details');
        foreach ($this->details as $detaila) {
            if ($detaila->attendance == $attendance) return $detaila->payment;
        }
        return null;
    }

    public function editCategory($title, $desc, array $details = [])
    {
        $this->title = $title;
        $this->desc = $desc;

        try {
            $this->save();
            if (count($details) > 0) {
                $this->setDetails($details);
            }
        } catch (Exception $e) {
            report($e);
        }
    }

    public function setCategoryDetails(array $details)
    {
        $this->details()->delete();
        foreach ($details as $row)
            $this->details()->updateOrCreate([
                "attendance"    =>  $row['attendance']
            ], [
                "payment"       =>  $row['payment']
            ]);
    }

    public function deleteCategory()
    {
        $this->details()->delete();
        $this->delete();
        return 1;
    }

    /////relations
    public function players(): HasMany
    {
        return $this->hasMany(User::class, 'players_category_id');
    }

    public function details(): HasMany
    {
        return $this->hasMany(CategoryDetail::class, 'players_category_id');
    }
}
