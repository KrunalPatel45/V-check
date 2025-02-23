<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WebForm extends Model
{
    protected $table = 'Webform';

    protected $primaryKey = 'Id';

    public $timestamps = false;

    protected $fillable = [
        'Id',
        'UserID',
        'CompanyID',
        'Address',
        'City',
        'State',
        'Zip',
        'Email',
        'Logo',
        'PhoneNumber',
    ];
}
