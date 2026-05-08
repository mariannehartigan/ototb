<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\Todo;

class HomeController extends Controller
{
  public function index() {
    $categoriesWithTodos = Category::with(['todos' => function ($query) {
      $query->where('user_id', auth()->id())->orderBy('position');
    }])->get();

    return inertia('Home', [
        'categoriesWithTodos' => $categoriesWithTodos,
    ]);
  }
}
