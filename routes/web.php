<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
	return view('welcome');
});

use App\Http\Controllers\HomeController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ResetPassword;
use App\Http\Controllers\ChangePassword;

use App\Http\Controllers\MasterDivisiController;
use App\Http\Controllers\MasterPegawaiController;
use App\Http\Controllers\TransPekerjaanController;
use App\Http\Controllers\LaporanPekerjaanController;

// use App\Http\Controllers\ProfileController;


Route::get('/', function () {
	return redirect('/dashboard');
})->middleware('auth');
Route::get('/register', [RegisterController::class, 'create'])->middleware('guest')->name('register');
Route::post('/register', [RegisterController::class, 'store'])->middleware('guest')->name('register.perform');
Route::get('/login', [LoginController::class, 'show'])->middleware('guest')->name('login');
Route::post('/login', [LoginController::class, 'login'])->middleware('guest')->name('login.perform');
Route::get('/reset-password', [ResetPassword::class, 'show'])->middleware('guest')->name('reset-password');
Route::post('/reset-password', [ResetPassword::class, 'send'])->middleware('guest')->name('reset.perform');
Route::get('/change-password', [ChangePassword::class, 'show'])->middleware('guest')->name('change-password');
Route::post('/change-password', [ChangePassword::class, 'update'])->middleware('guest')->name('change.perform');
Route::get('/dashboard', [\App\Http\Controllers\DashboardController::class, 'index'])
	->middleware('auth')
	->name('home');

Route::prefix('master')->group(function () {
	Route::resource('divisi', MasterDivisiController::class)->except(['show', 'create']);
	Route::resource('pegawai', MasterPegawaiController::class)->except(['show', 'create']);
	Route::post('pegawai/{id}/toggle', [MasterPegawaiController::class, 'toggle'])->name('pegawai.toggle');
});

Route::prefix('trans')->name('trans.')->group(function () {
	Route::resource('pekerjaan', TransPekerjaanController::class)->except(['show', 'create']);
	Route::delete('pekerjaan/foto/{foto}', [TransPekerjaanController::class, 'destroyFoto'])->name('pekerjaan.foto.destroy');
});

Route::middleware(['auth'])->group(function () {
	Route::get('/laporan/pekerjaan', [LaporanPekerjaanController::class, 'index'])->name('laporan.pekerjaan.index');
	Route::get('/laporan/pekerjaan/export', [LaporanPekerjaanController::class, 'export'])->name('laporan.pekerjaan.export'); // Excel
	Route::get('/laporan/pekerjaan/export-word', [LaporanPekerjaanController::class, 'exportWord'])->name('laporan.pekerjaan.exportWord'); // Word

	Route::get('/trans/pekerjaan/daftar', [TransPekerjaanController::class, 'daftar'])
		->name('trans.pekerjaan.daftar');

	Route::post('/profile/password', [ProfileController::class, 'updatePassword'])
		->name('profile.password.update');
});


Route::group(['middleware' => 'auth'], function () {
	Route::get('/virtual-reality', [PageController::class, 'vr'])->name('virtual-reality');
	Route::get('/rtl', [PageController::class, 'rtl'])->name('rtl');
	Route::get('/profile', [ProfileController::class, 'index'])->name('profile');
	Route::post('/profile', [ProfileController::class, 'update'])->name('profile.update');
	Route::get('/profile-static', [PageController::class, 'profile'])->name('profile-static');
	Route::get('/sign-in-static', [PageController::class, 'signin'])->name('sign-in-static');
	Route::get('/sign-up-static', [PageController::class, 'signup'])->name('sign-up-static');
	Route::get('/{page}', [PageController::class, 'index'])->name('page');
	Route::post('logout', [LoginController::class, 'logout'])->name('logout');
});
