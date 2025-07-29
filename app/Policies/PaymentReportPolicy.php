<?php

namespace App\Policies;

use App\Models\User;

class PaymentReportPolicy
{
    public function viewPaymentReports(User $user): bool
    {
        return $user->roles()->where('value', 'admin')->exists();
    }

    public function generatePaymentReports(User $user): bool
    {
        return $user->roles()->where('value', 'admin')->exists();
    }
} 