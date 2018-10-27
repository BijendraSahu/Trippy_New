<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LocationMaster extends Model
{
    protected $table = 'locations';
    public $timestamps = false;

    public function scopeGetActiveLocation($query)
    {
        return $query->where('is_active', '=', 1)->get();
    }

    public static function checkLocationName($unitname)
    {
        $unitname = LocationMaster::where(['is_active' => 1, 'name' => $unitname])->first();
        if (is_null($unitname)) return true;
        else return false;
    }

    public static function echeckLocationName($unitname,$id)
    {
        $unitname = LocationMaster::where(['is_active' => 1, 'name' => $unitname])->where('id', '!=', $id)->first();
        if (is_null($unitname)) return true;
        else return false;
    }

    public
    function scopegetLocationDropdown()
    {
        $Mcat = LocationMaster::where('is_active', '1')->get(['id', 'name']);
        $arr[0] = "SELECT";
        foreach ($Mcat as $Mcat) {
            $arr[$Mcat->id] = $Mcat->name;
        }
        return $arr;
    }
}
