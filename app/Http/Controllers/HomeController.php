<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
    $count_all =Invoice::count();
    $count_invoices1 = Invoice::where('Value_Status', 1)->count();
    $count_invoices2 = Invoice::where('Value_Status', 2)->count();
    $count_invoices3 = Invoice::where('Value_Status', 3)->count();

    if($count_invoices2 == 0){
        $nspainvoices2=0;
    } else {
        $nspainvoices2 = $count_invoices2/ $count_all*100;
    }

        if($count_invoices1 == 0){
            $nspainvoices1=0;
        } else {
            $nspainvoices1 = $count_invoices1/ $count_all*100;
        }

        if($count_invoices3 == 0){
            $nspainvoices3=0;
        } else {
            $nspainvoices3 = $count_invoices3/ $count_all*100;
        }

        $chartjs1 = app()->chartjs
            ->name('barChartTest')
            ->type('bar')
            ->size(['width' => 350, 'height' => 200])
            ->labels(['الفواتير الغير المدفوعة', 'الفواتير المدفوعة','الفواتير المدفوعة جزئيا'])
            ->datasets([
                [
                    "label" => "الفواتير الغير المدفوعة",
                    'backgroundColor' => ['#F94C66'],
                    'data' => [$nspainvoices2]
                ],
                [
                    "label" => "الفواتير المدفوعة",
                    'backgroundColor' => ['#53BF9D'],
                    'data' => [$nspainvoices1]
                ],
                [
                    "label" => "الفواتير المدفوعة جزئيا",
                    'backgroundColor' => ['#FFC54D'],
                    'data' => [$nspainvoices3]
                ],
            ])
            ->options([]);
        $chartjs = app()->chartjs
            ->name('pieChartTest')
            ->type('pie')
            ->size(['width' => 350, 'height' => 200])
            ->labels(['الفواتير الغير المدفوعة', 'الفواتير المدفوعة','الفواتير المدفوعة جزئيا'])
            ->datasets([
                [
                    'backgroundColor' => ['#F94C66', '#53BF9D','#FFC54D'],
                    'data' => [$nspainvoices2, $nspainvoices1,$nspainvoices3]
                ]
            ])
            ->options([]);
        return view('home', compact('chartjs','chartjs1'));
    }
}