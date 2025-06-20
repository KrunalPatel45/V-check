<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentSubscription extends Model
{
    protected $table = 'PaymentSubscription';

    protected $primaryKey = 'PaymentSubscriptionID';

    public $timestamps = false;

    protected $fillable = [
        'PaymentSubscriptionID',
        'UserID',
        'PackageID',
        'PaymentMethodID',
        'PaymentAmount',
        'PaymentStartDate',
        'PaymentEndDate',
        'NextRenewalDate',
        'ChecksGiven',
        'ChecksUsed',
        'RemainingChecks',
        'PaymentDate',
        'PaymentAttempts',
        'TransactionID',
        'PromotionID',
        'Status',
        'NextPackageID',
        'ChecksReceived',
        'ChecksSent',
    ];

}
