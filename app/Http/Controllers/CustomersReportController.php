<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Section;
use App\Models\Invoice;
class CustomersReportController extends Controller
{
    public function index()
    {
        $sections = Section::all();
        return view('reports.customers_report',compact('sections'));
    }


    public function Search_customers(Request $request)
    {
        if ($request->Section && $request->product && $request->start_at =='' && $request->end_at=='') {
            $invoices = Invoice::select('*')->where('section_id','=',$request->Section)->where('product','=',$request->product)->get();
            $sections = Section::all();
            return view('reports.customers_report',compact('sections'))->withDetails($invoices);
        }else {
            $start_at = date($request->start_at);
            $end_at = date($request->end_at);
            $invoices = Invoice::whereBetween('invoice_Date',[$start_at,$end_at])->where('section_id','=',$request->Section)->where('product','=',$request->product)->get();
            $sections = Section::all();
            return view('reports.customers_report',compact('sections'))->withDetails($invoices);
        }
    }
}