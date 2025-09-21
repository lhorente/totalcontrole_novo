<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaction;

class DashboardController extends Controller
{
    public function index(){
      $dateStart = new \DateTime('first day of this month 00:00:00');
      $dateEnd = new \DateTime('last day of this month 23:59:59');

      $current_month = $dateStart;

      $lendings = Transaction::getLendingsTotalsByPeriod($dateStart,$dateEnd);
      $lendings_notPaid = Transaction::getLendingsNotPaidTotals($dateStart,$dateEnd);

      return view('dashboard/index',compact('lendings','lendings_notPaid','current_month'));
    }
}
