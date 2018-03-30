<?php

namespace App\Http\Controllers;

use App\Transaction;
use App\Taxpayer;
use App\Cycle;
use App\AccountMovement;
use Illuminate\Http\Request;
use DB;

class AccountPayableController extends Controller
{
    /**
    * Display a listing of the resource.
    *
    * @return \Illuminate\Http\Response
    */
    public function index(Taxpayer $taxPayer, Cycle $cycle)
    {
        return view('/commercial/accounts-payable');
    }

    public function get_account_payable(Taxpayer $taxPayer, Cycle $cycle, $skip)
    {
        $transactions = Transaction::MyPurchases()
        ->join('taxpayers', 'taxpayers.id', 'transactions.supplier_id')
        ->join('currencies', 'transactions.currency_id','currencies.id')
        ->join('transaction_details as td', 'td.transaction_id', 'transactions.id')
        ->leftJoin('account_movements', 'transactions.id', 'account_movements.transaction_id')
        ->where('transactions.customer_id', $taxPayer->id)
        ->where('transactions.payment_condition', '>', 0)
        //->whereRaw('ifnull(sum(account_movements.debit), 0) < sum(td.value)')
        ->groupBy('transactions.id')
        ->select(DB::raw('max(transactions.id) as ID'),
        DB::raw('max(taxpayers.name) as Supplier'),
        DB::raw('max(taxpayers.taxid) as SupplierTaxID'),
        DB::raw('max(currencies.code) as Currency'),
        DB::raw('max(transactions.payment_condition) as PaymentCondition'),
        DB::raw('max(transactions.date) as Date'),
        DB::raw('DATE_ADD(max(transactions.date), INTERVAL max(transactions.payment_condition) DAY) as Expiry'),
        DB::raw('max(transactions.number) as Number'),
        DB::raw('ifnull(sum(account_movements.debit/account_movements.rate), 0) as Paid'),
        DB::raw('sum(td.value/transactions.rate) as Value'))
        ->orderByRaw('DATE_ADD(max(transactions.date), INTERVAL max(transactions.payment_condition) DAY)', 'desc')
        ->orderByRaw('max(transactions.number)', 'desc')
        ->skip($skip)
        ->take(100)
        ->get();

        return response()->json($transactions);
    }

    public function get_account_payableByID($taxPayerID, $id)
    {
        $accountMovement = AccountMovement::join('charts', 'charts.id', 'account_movements.chart_id')
        ->join('currencies', 'currencies.id', 'account_movements.currency_id')
        ->where('account_movements.taxpayer_id', $taxPayerID)
        ->where('debit','>',0)
        ->select('account_movements.id',
        'charts.name',
        'currencies.name',
        'account_movements.rate',
        'account_movements.debit',
        'account_movements.credit',
        'account_movements.date')
        ->first();
        return response()->json($accountMovement);
    }
    /**
    * Show the form for creating a new resource.
    *
    * @return \Illuminate\Http\Response
    */
    public function create()
    {
        //
    }

    /**
    * Store a newly created resource in storage.
    *
    * @param  \Illuminate\Http\Request  $request
    * @return \Illuminate\Http\Response
    */
    public function store(Request $request)
    {
        if ($request->id == 0)
        {
            $accountMovement = new AccountMovement();
        }
        else
        {
            $accountMovement = AccountMovement::where('id', $request->id)->first();
        }

        $accountMovement->taxpayer_id = $request->taxpayer_id;
        $accountMovement->chart_id =$request->chart_id ;
        $accountMovement->date = $request->date;

        $accountMovement->transaction_id = $request->transaction_id!=''?$request->transaction_id:null;
        $accountMovement->currency_id = $request->currency_id;
        $accountMovement->rate = $request->rate;
        $accountMovement->debit = $request->debit!=''?$request->debit:0;
        $accountMovement->credit = $request->credit!=''?$request->credit:0;
        $accountMovement->comment = $request->comment;

        $accountMovement->save();

        return response()->json('ok');
    }

    /**
    * Display the specified resource.
    *
    * @param  \App\AccountMovement  $accountMovement
    * @return \Illuminate\Http\Response
    */
    public function show(AccountMovement $accountMovement)
    {
        //
    }

    /**
    * Show the form for editing the specified resource.
    *
    * @param  \App\AccountMovement  $accountMovement
    * @return \Illuminate\Http\Response
    */
    public function edit(AccountMovement $accountMovement)
    {
        //
    }

    /**
    * Update the specified resource in storage.
    *
    * @param  \Illuminate\Http\Request  $request
    * @param  \App\AccountMovement  $accountMovement
    * @return \Illuminate\Http\Response
    */
    public function update(Request $request, AccountMovement $accountMovement)
    {
        //
    }

    /**
    * Remove the specified resource from storage.
    *
    * @param  \App\AccountMovement  $accountMovement
    * @return \Illuminate\Http\Response
    */
    public function destroy(AccountMovement $accountMovement)
    {
        //
    }
}
