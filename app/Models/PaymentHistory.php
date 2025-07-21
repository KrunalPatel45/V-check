<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentHistory extends Model
{
    protected $table = 'PaymentHistory';

    protected $primaryKey = 'PaymentHistoryID';

    public $timestamps = false;

    protected $fillable = [
        'PaymentHistoryID',
        'PaymentSubscriptionID',
        'PaymentAmount',
        'PaymentDate',
        'PaymentStatus',
        'PaymentAttempts',
        'TransactionID',
        'PaymentUrl'
    ];

}