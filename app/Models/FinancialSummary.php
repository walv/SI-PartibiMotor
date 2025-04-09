<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FinancialSummary extends Model
{
    use HasFactory;
    
    protected $table = 'financial_summaries';

    protected $fillable = [
        'date',
        'total_income',
        'total_expense',
        'net_profit',
    ];
}
