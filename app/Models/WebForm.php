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
        'PayeeID',
        'Logo',
        'page_url',
        'page_desc',
        'service_fees',
        'service_fees_type',
    ];
}
