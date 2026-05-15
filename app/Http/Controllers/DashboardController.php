<?php

namespace App\Http\Controllers;

use App\Models\Budget;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user    = Auth::user();
        $budgets = Budget::where('user_id', $user->id)
            ->latest()
            ->limit(5)
            ->get();

        $stats = [
            'total_budgets'    => Budget::where('user_id', $user->id)->count(),
            'budgets_month'    => $user->budgets_this_month ?? 0,
            'total_value'      => Budget::where('user_id', $user->id)->sum('total'),
            'budgets_limit'    => $user->plan?->budget_limit,
        ];

        return view('dashboard', compact('budgets', 'stats', 'user'));
    }
}
