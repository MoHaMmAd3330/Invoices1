<?php

namespace App\Http\Controllers;

use App\Exports\InvoicesExport;
use App\Models\Invoices;
use App\Models\Invoices_attachments;
use App\Models\Invoices_details;
use App\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Notification;
use App\Notifications\InvoicePaid;
use File;
use App\Models\Sections;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class InvoicesController extends Controller
{

    public function index(){
        $section = Sections::all();
        $invoices = Invoices::all();
        return view('invoices.invoices',compact('invoices','section'));
    }


    public function create(){
        $sections = Sections::all();
        return view('invoices.add_invoice',compact('sections'));
    }


    public function store(Request $request){
        try {
            $Invoices = Invoices::all();
            if (!$Invoices)
                return redirect('/invoices/create')->with(['error' => 'هذا الفاتورة موجود مسبقاً ']);
//            DB::beginTransaction();
            Invoices::create([
                'invoice_number' => $request->invoice_number,
                'invoice_Date' => $request->invoice_Date,
                'Due_date' => $request->Due_date,
                'product' => $request->product,
                'section_id' => $request->Section,
                'Amount_collection' => $request->Amount_collection,
                'Amount_Commission' => $request->Amount_Commission,
                'Discount' => $request->Discount,
                'Value_VAT' => $request->Value_VAT,
                'Rate_VAT' => $request->Rate_VAT,
                'Total' => $request->Total,
                'Status' => 'غير مدفوعة',
                'Value_Status' => 2,
                'note' => $request->note,
            ]);
            $invoice_id = Invoices::latest()->first()->id;
            Invoices_details::create([
                'id_Invoice' => $invoice_id,
                'invoice_number' => $request->invoice_number,
                'product' => $request->product,
                'Sections' => $request->Section,
                'Status' => 'غير مدفوعة',
                'Value_Status' => 2,
                'note' => $request->note,
                'user' => (Auth::user()->name),
            ]);
            if ($request->hasFile('pic')) {

                $invoice_id = Invoices::latest()->first()->id;
                $image = $request->file('pic');
                $file_name = $image->getClientOriginalName();
                $invoice_number = $request->invoice_number;

                $attachments = new Invoices_attachments();
                $attachments->file_name = $file_name;
                $attachments->invoice_number = $invoice_number;
                $attachments->Created_by = Auth::user()->name;
                $attachments->invoice_id = $invoice_id;
                $attachments->save();

                // move pic
                $imageName = $request->pic->getClientOriginalName();
                $request->pic->move(public_path('Attachments/' . $invoice_number), $imageName);
            }
//            DB::commit();
//            $user = User::first();
//            $invoices = Invoices::latest()->first();
////            Notification::send($user, new \App\Notifications\InvoicePaid($invoices));

            $user = User::get();

            $invoices = Invoices::latest()->first();
//            $user ->notify(new \App\Notifications\Add_invoice($invoices));
            Notification::send($user, new \App\Notifications\Add_invoice($invoices));


            return redirect('/invoices/create')->with(['add' => 'تم اضافة الفاتورة']);
        }catch (\Exception $ex){
            return $ex;
//            DB::rollback();
            return redirect('/invoices/create')->with(['error' => 'هناك خطأ حاول فيما بعد']);

        }
    }


    public function edit($id)
    {
        $Invoices = Invoices::where('id',$id)->first();
        $sections = Sections::all();
        return view('invoices.edit_invoice',compact('Invoices','sections'));


    }

    public function update(Request $request){
        try {
        Invoices::findOrFail($request->invoice_id)->update([
            'invoice_number' => $request->invoice_number,
            'invoice_Date' => $request->invoice_Date,
            'Due_date' => $request->Due_date,
            'product' => $request->product,
            'section_id' => $request->Section,
            'Amount_collection' => $request->Amount_collection,
            'Amount_Commission' => $request->Amount_Commission,
            'Discount' => $request->Discount,
            'Value_VAT' => $request->Value_VAT,
            'Rate_VAT' => $request->Rate_VAT,
            'Total' => $request->Total,
            'note' => $request->note,
            'user' => (Auth::user()->name),
            'Status' => 'غير مدفوعة',
            'Value_Status' => 2,
        ]);
            return redirect()->back()->with(['add' => 'تم تحديث الفاتورة']);
        }catch (\Exception $ex){
            return $ex;
            return redirect()->back()->with(['error' => 'هناك خطأ حاول فيما بعد']);
        }
    }

    public function getProducts($id){
        $Products = DB::table("products")->where("section_id",$id)->pluck("Product_name",'id');
        return json_encode($Products);
    }

    public function destroy(Request $request){
//        return   $request;
        $id = $request->invoice_id;
        $invoices = Invoices::where('id', $id)->first();
        $Details = Invoices_attachments::where('invoice_id',$id);
        $id_page =$request->id_page;


        if (!$id_page==2) {

            if (!empty($Details->invoice_number)) {

                Storage::disk('public_uploads')->deleteDirectory($Details->invoice_number);
            }

            $invoices->forceDelete();
            session()->flash('delete_invoice');
            return redirect('/invoices');

        }

        else {

            $invoices->delete();
            session()->flash('archive_invoice');
            return redirect('/invoices');
        }


    }





//    public function archive(Request $request){
//        try {
//
//
//        $id = $request->invoice_id;
//        $invoices = Invoices::where('id', $id)->first();
//        if ($invoices != null){
//
//            $invoices->delete();
//            session()->flash('archive_invoice');
//            return redirect('/invoices');
//        }
//        }catch (ModelNotFoundException $es){
//                return $es;
////            return redirect()->back()->with('error','يرجا المحاولة مرة اخرى');
//        }




    public function show($id)
    {
        $invoices = Invoices::where('id', $id)->first();
        return view('invoices.status_update', compact('invoices'));
    }


    public function Status_Update($id, Request $request)
    {
        $invoices = Invoices::findOrFail($id);

        if ($request->Status === 'مدفوعة') {

            $invoices->update([
                'Value_Status' => 1,
                'Status' => $request->Status,
                'Payment_Date' => $request->Payment_Date,
            ]);

            Invoices_details::create([
                'id_Invoice' => $request->invoice_id,
                'invoice_number' => $request->invoice_number,
                'product' => $request->product,
                'Sections' => $request->Section,
                'Status' => $request->Status,
                'Value_Status' => 1,
                'note' => $request->note,
                'Payment_Date' => $request->Payment_Date,
                'user' => (Auth::user()->name),
            ]);
        }

        else {
            $invoices->update([
                'Value_Status' => 3,
                'Status' => $request->Status,
                'Payment_Date' => $request->Payment_Date,
            ]);
            Invoices_details::create([
                'id_Invoice' => $request->invoice_id,
                'invoice_number' => $request->invoice_number,
                'product' => $request->product,
                'Sections' => $request->Section,
                'Status' => $request->Status,
                'Value_Status' => 3,
                'note' => $request->note,
                'Payment_Date' => $request->Payment_Date,
                'user' => (Auth::user()->name),
            ]);
        }
        session()->flash('Status_Update');
        return redirect('/invoices');

    }

    public function Invoice_Paid()
    {
        $invoices = Invoices::where('Value_Status', 1)->get();
        return view('invoices.invoices_paid',compact('invoices'));
    }

    public function Invoice_unPaid()
    {
        $invoices = Invoices::where('Value_Status',2)->get();
        return view('invoices.invoices_unpaid',compact('invoices'));
    }

    public function Invoice_Partial()
    {
        $invoices = Invoices::where('Value_Status',3)->get();
        return view('invoices.invoices_Partial',compact('invoices'));
    }

    public function Print_invoice($id)
    {
        $invoices = Invoices::where('id', $id)->first();
        return view('invoices.Print_invoice',compact('invoices'));
    }

    public function export()
    {
        return Excel::download(new InvoicesExport, 'قائمة الفواتير.xlsx');
    }




    public function MarkAsRead_all (Request $request)
    {

        $userUnreadNotification= auth()->user()->unreadNotifications;

        if($userUnreadNotification) {
            $userUnreadNotification->markAsRead();
            return back();
        }


    }

}
