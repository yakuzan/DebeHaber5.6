<?php

namespace App\Http\Controllers;

use App\Inventory;
use App\AccountMovement;
use App\Transaction;
use App\Taxpayer;
use App\Cycle;
use App\Journal;
use App\JournalDetail;
//use App\JournalTransaction;
use App\Http\Resources\JournalResource;
use DB;
use App\Jobs\GenerateJournal;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Resources\JournalCollection;
use Illuminate\Support\Facades\Log;


class JournalController extends Controller
{
    /**
    * Display a listing of the resource.
    *
    * @return \Illuminate\Http\Response
    */
    public function index(Taxpayer $taxPayer, Cycle $cycle)
    {
        return view('/accounting/journals');
    }

    public function getJournals(Taxpayer $taxPayer, Cycle $cycle)
    {
        return JournalResource::collection(
            Journal::with(['details:journal_uuid,chart_id,debit,credit',
            'details.chart:id,name,code,type'])
            ->orderBy('date', 'desc')
            ->paginate(100)
        );
    }

    public function getJournalsByID($taxPayerID, Cycle $cycle, $id)
    {
        $journals = Journal::with('details:uuid,journal_uuid,chart_id,debit,credit')
        ->withUuid($id)
        ->get();

        return response()->json($journals);
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
    public function store(Request $request,Taxpayer $taxPayer,Cycle $cycle)
    {
        $journal = $request->id == 0 ? new Journal() : Journal::where('id', $request->id)->first();

        $journal->date = $request->date;
        $journal->number = $request->number ;
        $journal->comment = $request->comment;
        $journal->cycle_id = $cycle->id;
        $journal->save();

        foreach ($request->details as $detail)
        {
            $journalDetail = $detail['id'] == 0 ? new JournalDetail() : JournalDetail::where('id', $detail['id'])->first();
            $journalDetail->journal_id = $journal->id;
            $journalDetail->chart_id = $detail['chart_id'];
            $journalDetail->debit = $detail['debit'];
            $journalDetail->credit = $detail['credit'];
            $journalDetail->save();
        }

        return response()->json('ok');
    }

    public function storeOpeningBalance(Request $request, Taxpayer $taxPayer, Cycle $cycle)
    {
        //return response()->json($request[0]['debit'],500);
        $journal =  Journal::where('is_first', true)->where('cycle_id',$cycle->id)->first() ?? new Journal();

        $journal->date = $cycle->start_date;
        $journal->comment = $cycle->year . '- Opening Balance';
        $journal->is_first= true;
        $journal->cycle_id = $cycle->id;
        $journal->save();

        $details = collect($request)->where('is_accountable', '=', 1);

        foreach ($details as $detail)
        {
            // JournalDetail::where('id', $detail->journal_id)->first() ??
            $journalDetail = new JournalDetail();

            $journalDetail->journal_id = $journal->id;
            $journalDetail->chart_id = $detail['id'];
            $journalDetail->debit = $detail['debit'] ?? 0;
            $journalDetail->credit = $detail['credit'] ?? 0;

            //Save only if there are values ot be saved. avoid saving blank values.
            if ($journalDetail->debit > 0 || $journalDetail->credit > 0)
            {
                $journalDetail->save();
            }
        }

        return response()->json('Ok', 200);
    }

    public function getJournalsByCycleID(Request $request, Taxpayer $taxPayer, Cycle $cycle, $id)
    {
        $journals = Journal::where('is_first', 1)->where('cycle_id',$cycle->id)
        ->join('journal_details', 'journals.id', 'journal_details.journal_id')
        ->join('charts', 'journal_details.chart_id','charts.id')
        ->select('journal_details.id as id',
        'journal_details.chart_id',
        'charts.is_accountable',
        'charts.code',
        'charts.name',
        'debit',
        'credit')
        ->get();

        return response()->json($journals);
    }

    /**
    * Display the specified resource.
    *
    * @param  \App\Journal  $journal
    * @return \Illuminate\Http\Response
    */
    public function show(Journal $journal)
    {
        //
    }

    /**
    * Show the form for editing the specified resource.
    *
    * @param  \App\Journal  $journal
    * @return \Illuminate\Http\Response
    */
    public function edit(Journal $journal)
    {
        //
    }

    /**
    * Update the specified resource in storage.
    *
    * @param  \Illuminate\Http\Request  $request
    * @param  \App\Journal  $journal
    * @return \Illuminate\Http\Response
    */
    public function update(Request $request, Journal $journal)
    {
        //
    }

    /**
    * Remove the specified resource from storage.
    *
    * @param  \App\Journal  $journal
    * @return \Illuminate\Http\Response
    */
    public function destroy(Journal $journal)
    {
        //
    }

    public function generateJournalsByRange(Taxpayer $taxPayer, Cycle $cycle, $startDate, $endDate)
    {
        GenerateJournal::dispatch($taxPayer, $cycle, $startDate, $endDate);
        return back();
    }
}
