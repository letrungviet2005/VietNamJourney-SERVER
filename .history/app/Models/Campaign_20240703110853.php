<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Campaign extends Model
{
    use HasFactory;

    protected $table = 'campaigns';

    protected $fillable = [
        'userid', 'name', 'province', 'district', 'location', 
        'dateStart', 'dateEnd', 'totalMoney', 'moneyByVNJN', 
        'timeline', 'infoContact', 'infoOrganization', 
        'image', 'description', 'status'
    ];
}
