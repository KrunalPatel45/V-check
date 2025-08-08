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
        'PayeeID',
        'CheckType',
        'Amount',
        'ServiceFees',
        'Total',
        'PayorID',
        'CheckNumber',
        'IssueDate',
        'ExpiryDate',
        'Status',
        'DigitalSignatureRequired',
        'DigitalSignature',
        'Memo',
        'CheckPDF',
        'signed',
        'SignID',
        'is_email_send',
        'is_seen',
        'GridSchemaHistoryID',
        'GridItems'
    ];

}
