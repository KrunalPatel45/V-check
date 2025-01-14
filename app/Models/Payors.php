<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payors extends Model
{
    protected $table = 'entities';

    protected $primaryKey = 'EntityID';

    public $timestamps = false;

    protected $fillable = [
        'EntityID',
        'UserID',
        'Name',
        'Address1',
        'Address2',
        'City',
        'State',
        'Zip',
        'Email',
        'Logo',
        'BankName',
        'RoutingNumber',
        'AccountNumber',
        'Type',
        'Status',
        'CreatedAt',
        'UpdatedAt',
    ];
}
