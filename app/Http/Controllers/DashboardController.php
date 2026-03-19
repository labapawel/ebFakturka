<?php

namespace App\Http\Controllers;

use App\Models\Contractor;
use App\Models\Invoice;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $currentMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();

        // Statystyki z tego miesiąca (Tylko sprzedaż)
        $monthInvoicesCount = Invoice::sales()->whereBetween('issue_date', [$currentMonth, $endOfMonth])->count();
        
        // Suma brutto w tym miesiącu (Tylko sprzedaż)
        $monthRevenue = Invoice::sales()->whereBetween('issue_date', [$currentMonth, $endOfMonth])->sum('gross_total');
        
        $totalContractors = Contractor::count();
        $totalProducts = Product::count();

        // Ostatnie 5 faktur (Tylko sprzedaż)
        $recentInvoices = Invoice::sales()
            ->with(['contractor', 'currency'])
            ->latest('issue_date')
            ->take(5)
            ->get();

        // Statystyki zakupowe
        $monthPurchaseCount = Invoice::purchase()->whereBetween('issue_date', [$currentMonth, $endOfMonth])->count();
        $monthExpenses = Invoice::purchase()->whereBetween('issue_date', [$currentMonth, $endOfMonth])->sum('gross_total');

        // Ostatnie 5 faktur zakupowych
        $recentPurchaseInvoices = Invoice::purchase()
            ->with(['contractor', 'currency'])
            ->latest('issue_date')
            ->take(5)
            ->get();
            
        // Ilość aktywnych cykli
        $activeRecurringCount = \App\Models\RecurringInvoice::where('status', 'active')->count();

        // Bilans
        $monthBalance = $monthRevenue - $monthExpenses;

        return view('dashboard', compact(
            'monthInvoicesCount',
            'monthRevenue',
            'monthPurchaseCount',
            'monthExpenses',
            'monthBalance',
            'totalContractors',
            'totalProducts',
            'recentInvoices',
            'recentPurchaseInvoices',
            'activeRecurringCount'
        ));
    }
}
