<?php

namespace App\Policies;

use App\Models\User;

class PaymentReportPolicy
{
    public function viewPaymentReports(User $user): bool
    {
        return $user->hasPermission('payment-reports.view') || $user->hasAnyPermission(['admin.dashboard', 'payment-reports.manage']);
    }

    public function generatePaymentReports(User $user): bool
    {
        return $user->hasPermission('payment-reports.generate') || $user->hasAnyPermission(['admin.dashboard', 'payment-reports.manage']);
    }
} 