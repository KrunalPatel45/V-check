<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Package extends Model
{
    protected $table = 'package';

    protected $primaryKey = 'PackageID';

    public $timestamps = false;

    protected $fillable = ['Name', 'Description', 'Price', 'Duration', 'CheckLimitPerMonth', 'RecurringPaymentFrequency', 'Status', 'CreatedAt', 'UpdatedAt'];
}
