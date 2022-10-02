<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BookController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\CategoryController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

//halaman awalnya login aja
Route::get('/', function(){
    return view('auth.login');
});

Auth::routes();

//karena ini toko online jadi gada registrasinya, langsung redirect aja ke halaman login kalo ada yang ngakes register
Route::match(["GET", "POST"], "/register", function(){
    return redirect("/login");
})->name("register");

Route::get('/home', [HomeController::class, 'index'])->name('home');


Route::resource("users", UserController::class);

Route::get('/categories/trash', [CategoryController::class, 'trash'])->name('categories.trash');

Route::get('/categories/{id}/restore', [CategoryController::class,'restore'])->name('categories.restore');

Route::delete('/categories/{category}/delete-permanent',[CategoryController::class, 'deletePermanent'])->name('categories.delete-permanent');

Route::resource('categories', CategoryController::class);

Route::get('/books/trash', [BookController::class, 'trash'])->name('books.trash');
Route::post('/books/{book}/restore', [BookController::class, 'restore'])->name('books.restore');
Route::delete('/books/{id}/delete-permanent', [BookController::class,'deletePermanent'])->name('books.delete-permanent');
Route::resource('books', BookController::class);

Route::get('/ajax/categories/search', [CategoryController::class,'ajaxSearch']);

Route::resource('orders', OrderController::class);
