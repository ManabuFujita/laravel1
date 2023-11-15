<?php
 
namespace App\Http\Controllers;
 
use Illuminate\Http\Request;
use App\Models\TodoList;//追記
 
class TodoListController extends Controller
{
  public function index(Request $request)
  {
    $todo_lists = TodoList::all();
 
    return view('todo_list.index')->with('todo_lists', $todo_lists);
  }
}