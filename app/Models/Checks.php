<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Checks extends Model
{
    protected $table = 'Checks';

    protected $primaryKey = 'CheckID';

    public $timestamps = false;

    protected $fillable = [
        'CheckID',
        'UserID',
        'CompanyID',
        'CheckType',
        'Amount',
        'EntityID',
        'CheckNumber',
        'IssueDate',
        'ExpiryDate',
        'Status',
        'DigitalSignatureRequired',
        'DigitalSignature',
        'Memo',
        'CheckPDF',
    ];
}
