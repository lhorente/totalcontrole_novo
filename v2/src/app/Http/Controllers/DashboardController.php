<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaction;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(){
      // Last 10 transactions by creation date
      $recentTransactions = Transaction::with(['category', 'credit_card', 'contact', 'wallet'])
          ->orderBy('created_at', 'desc')
          ->limit(10)
          ->get();

      // Fetch all transactions for current month + next 5 months (6 months total)
      $rangeStart = Carbon::now()->startOfMonth();
      $rangeEnd   = Carbon::now()->addMonths(5)->endOfMonth();

      $allTransactions = Transaction::with(['category', 'credit_card', 'contact'])
          ->where('status', '!=', 'cancelado')
          ->whereBetween('data', [$rangeStart, $rangeEnd])
          ->orderBy('data')
          ->get();

      // Build per-month summaries
      $monthlySummaries = [];
      for ($i = 0; $i < 6; $i++) {
          $monthDate = Carbon::now()->addMonths($i)->startOfMonth();
          $y = $monthDate->year;
          $m = $monthDate->month;

          $monthlyTx = $allTransactions->filter(
              fn($t) => $t->data->year == $y && $t->data->month == $m
          );

          $monthlySummaries[] = [
              'date'        => $monthDate,
              'transactions'=> $monthlyTx,
              'by_category' => $monthlyTx->groupBy('id_categoria')->sortByDesc(fn($g) => $g->sum('valor')),
              'by_card'     => $monthlyTx->filter(fn($t) => !is_null($t->id_cartao))->groupBy('id_cartao')->sortByDesc(fn($g) => $g->sum('valor')),
              'lendings'    => $monthlyTx->where('tipo', 'emprestimo'),
          ];
      }

      // Legacy: keep existing lending widgets for backward compat
      $dateStart = new \DateTime('first day of this month 00:00:00');
      $dateEnd   = new \DateTime('last day of this month 23:59:59');
      $current_month     = $dateStart;
      $lendings          = Transaction::getLendingsTotalsByPeriod($dateStart, $dateEnd);
      $lendings_notPaid  = Transaction::getLendingsNotPaidTotals($dateStart, $dateEnd);

      return view('dashboard/index', compact(
          'lendings', 'lendings_notPaid', 'current_month',
          'recentTransactions', 'monthlySummaries'
      ));
    }
}
