<?php

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

if (env('APP_ENV', 'production') === 'production') {
    URL::forceScheme('https');
}

Route::get('/', function () {
    if (\Illuminate\Support\Facades\Auth::guest()) {
        return view('welcome');
    } else {
        $role = Auth::user()->role_id;
        switch ($role) {
            case "1":
                return redirect(route('participant.home'));

            case "2":
                return redirect(route('oc.home'));

            default:
                return redirect(route('home'));
        }
    }
})->name('home');

//CAS
Route::get('/login', 'UnauthenticatedController@login')->name('cas.login');

//Participants
Route::get('/account/registration', 'RegistrationController@registrationShow')->name('participant.registration.show');
Route::post('/account/registration', 'RegistrationController@registration')->name('participant.registration');
Route::get('/account/nonregistered/logout', 'RegistrationController@logout')->name('participant.nonregistered.logout');
Route::get('/account', 'ParticipantController@index')->name('participant.home');
Route::get('/account/profile', 'ParticipantController@showProfile')->name('profile');
Route::get('/account/payment', 'ParticipantController@payment')->name('participant.payment');
Route::post('/account/validateCard', 'ParticipantController@validateCard')->name('participant.validateCard');
Route::get('/account/charge', 'ParticipantController@charge')->name('participant.charge');
Route::get('/account/deposit', 'ParticipantController@deposit')->name('participant.deposit');
Route::post('/account/parseToken', 'ParticipantController@parseToken')->name('participant.parseToken');
Route::get('/account/chargeDeposit', 'ParticipantController@chargeDeposit')->name('participant.deposit.charge');
Route::get('/account/proof', 'ParticipantController@generateProof')->name('participant.generateProof');
Route::get('/account/logout', 'ParticipantController@logout')->name('participant.logout');

//OC
Route::get('/oc', 'OCController@index')->name('oc.home');
Route::get('/oc/approved', 'OCController@approved')->name('oc.approved');
Route::get('/oc/cashflow', 'OCController@cashflow')->name('oc.cashflow');
//Maybe
Route::get('/oc/cashflow/deposits', 'OCController@cashflowDeposits')->name('oc.cashflow.deposits');
Route::put('/oc/cashflow/deposits/acquire/{transaction}', 'OCController@acquireDeposit')->name('oc.deposits.acquire');
Route::delete('/oc/cashflow/deposits/refund/{transaction}', 'OCController@refundDeposit')->name('oc.deposits.refund');
//EndMaybe
Route::get('/oc/transaction/{transaction}', 'OCController@transaction')->name('oc.transaction.show');
Route::get('/oc/user/{user}', 'OCController@user')->name('oc.user.show');
Route::put('/oc/comments/edit', 'OCController@editUserComments')->name('oc.comments.edit');
Route::get('/oc/logout', 'OCController@logout')->name('oc.logout');

//Misc
Route::get('/terms', 'MiscController@terms')->name('terms');
Route::get('/event/terms', 'MiscController@eventterms')->name('event.terms');


//Test
Route::get('/test', 'ParticipantController@test')->name('participant.test');