<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Income;

class HomeController extends Controller
{
  public function index() {
    return inertia('Home', [
      'incomes' => Income::orderBy('day_deposited')->get(),
    ]);
  }
}
