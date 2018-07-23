<?php

namespace App\Http\Controllers;

use App\TaxpayerIntegration;
use App\TaxpayerSetting;
use App\Taxpayer;
use Illuminate\Http\Request;

class TaxpayerIntegrationController extends Controller
{
    /**
    * Display a listing of the resource.
    *
    * @return \Illuminate\Http\Response
    */
    public function index($teamID, $userID)
    {
        $taxPayerIntegration = TaxpayerIntegration::MyTaxPayers($teamID)
        ->leftJoin('taxpayer_favs', 'taxpayer_favs.taxpayer_id', 'taxpayers.id')
        ->select('taxpayer_integrations.id as id',
        'taxpayers.country',
        'taxpayers.name',
        'taxpayers.alias',
        'taxpayers.taxid',
        'taxpayer_favs.id as is_favorite')
        ->get();

        return $taxPayerIntegration;
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
    * Display the specified resource.
    *
    * @param  \App\TaxpayerIntegration  $taxpayerIntegration
    * @return \Illuminate\Http\Response
    */
    public function show($taxpayerIntegrationID)
    {
        $taxPayerIntegration = TaxpayerIntegration::where('id', $taxpayerIntegrationID)
        ->with(['taxpayer', 'taxpayer.setting'])
        ->get();

        return view('taxpayer/profile')->with('taxPayerIntegration', $taxPayerIntegration);
    }

    /**
    * Show the form for editing the specified resource.
    *
    * @param  \App\TaxpayerIntegration  $taxpayerIntegration
    * @return \Illuminate\Http\Response
    */
    public function edit(TaxpayerIntegration $taxpayerIntegration)
    {
        //
    }

    /**
    * Update the specified resource in storage.
    *
    * @param  \Illuminate\Http\Request  $request
    * @param  \App\TaxpayerIntegration  $taxpayerIntegration
    * @return \Illuminate\Http\Response
    */
    public function update(Request $request, TaxpayerIntegration $taxPayerIntegration)
    {

        if (isset($taxPayerIntegration))
        {

            $taxPayer = TaxPayer::where('id', $taxPayerIntegration->taxpayer_id)->with('setting')->first();
            if (isset($taxPayer))
            {
                $taxPayer->alias = $taxPayer->alias;
                $taxPayer->address = $request->address;
                $taxPayer->telephone = $request->telephone;
                $taxPayer->email = $request->email;
                $taxPayer->save();

                // $taxPayer->setting->regime_type=$request->setting_regime;
                // $taxPayer->setting->agent_name = $request->setting_agent ;
                // $taxPayer->setting->agent_taxid = $request->setting_agenttaxid ;
                // $taxPayer->setting->show_inventory = $request->setting_inventory = true ? 1 : 0;
                // $taxPayer->setting->show_production = $request->setting_production = true ? 1 : 0;
                // $taxPayer->setting->show_fixedasset = $request->setting_fixedasset = true ? 1 : 0;
                // $taxPayer->setting->does_export = $request->setting_export = true ? 1 : 0;
                // $taxPayer->setting->does_import = $request->setting_import = true ? 1 : 0;
                // $taxPayer->setting->is_company = $request->setting_is_company;
                //$taxPayer->setting->save();





            }

            return response()->json('Ok', 200);
        }

        return response()->json('Resource not found', 404);
    }

    /**
    * Remove the specified resource from storage.
    *
    * @param  \App\TaxpayerIntegration  $taxpayerIntegration
    * @return \Illuminate\Http\Response
    */
    public function destroy(TaxpayerIntegration $taxpayerIntegration)
    {
        //
    }
}
