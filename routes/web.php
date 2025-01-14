<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

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
Route::get('/', function () {
    return view('auth.login');
});

Auth::routes();
Route::group(['middleware' => ['auth']], function() {
Route::get('home', 'HomeController@index')->name('home');
Route::resource('invoices', 'InvoicesController');
Route::resource('Sections', 'SectionsController');
Route::resource('Products', 'ProductsController');
Route::resource('InvoiceAttachments','InvoicesAttachmentsController');
Route::resource('Archive', 'InvoiceArchiveController');
#################################################################

################################## Route Delete #########################
Route::post('/delete_file','InvoicesDetailsController@destroy')->name('delete_file');

Route::post('invoices/delete/','InvoicesController@destroy')->name('invoices.destroy');

Route::post('Products/delete/','ProductsController@destroy')->name('Products.destroy');

Route::post('Sections/delete','SectionsController@destroy')->name('Sections.destroy');

################################## Route Delete End #########################

################################## Route Roles  #########################

Route::resource('roles','UserManagement\RoleController');

Route::resource('users','UserController');
################################## Route Roles End #########################

Route::get('/edit/{id}','InvoicesController@edit')->name('edit');

Route::get('/edit/{id}','UserController@EditUser');

Route::post('/edit/{id}','UserController@EditUpdate')->name('users.EditUpdate');

Route::get('/section/{id}','InvoicesController@getProducts');

Route::get('/details/{id}','InvoicesDetailsController@index');

Route::get('InvoicesDetails/{id}','InvoicesDetailsController@index');

Route::get('/View_file/{invoice_number}/{file_name}','InvoicesDetailsController@Openfile');

Route::get('/Download_file/{invoice_number}/{file_name}','InvoicesDetailsController@Downloadfile');

Route::get('/Status_show/{id}', 'InvoicesController@show')->name('Status_show');

Route::post('/Status_Update/{id}', 'InvoicesController@Status_Update')->name('Status_Update');

Route::get('Invoice_Paid','InvoicesController@Invoice_Paid')->name('Invoice_Paid');

Route::get('Invoice_UnPaid','InvoicesController@Invoice_UnPaid')->name('Invoice_UnPaid');

Route::get('Invoice_Partial','InvoicesController@Invoice_Partial')->name('Invoice_Partial');

Route::get('Print_invoice/{id}','InvoicesController@Print_invoice');

Route::get('export_invoices','InvoicesController@export');

Route::get('changeStatus/{id}','UserController@changeStatus') -> name('changeStatus');

Route::get('invoices_report', 'InvoicesReportController@index')->name('invoices_report');

Route::post('Search_invoices', 'InvoicesReportController@Search_invoices');

Route::get('customers_report', 'CustomersReportController@index')->name("customers_report");

Route::post('Search_customers', 'CustomersReportController@Search_customers');

Route::get('MarkAsRead_all','InvoicesController@MarkAsRead_all')->name('MarkAsRead_all');

Route::get('/{page}', 'AdminController@index');
});
