<?php

namespace App\Http\Controllers\API;

use App\FixedAsset;
use App\Taxpayer;
use App\Cycle;
use App\Chart;
use App\ChartVersion;
use App\ChartAlias;
use App\Http\Controllers\ChartController;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;

class FixedAssetController extends Controller
{
  public function start(Request $request)
  {
    $movementData = array();

    $startDate = '';
    $endDate = '';

    $cycle = null;




    //Process Transaction by 100 to speed up but not overload.
    for ($i = 0; $i < 100 ; $i++)
    {
      $chunkedData = $request[$i];

      if (isset($chunkedData))
      {
        $taxPayer = $this->checkTaxPayer($chunkedData['TaxpayerTaxID'], $chunkedData['TaxpayerName']);
        $cycle = Cycle::where('start_date', '<=',  Carbon::now())
        ->where('end_date', '>=',  Carbon::now())
        ->where('taxpayer_id', $taxPayer->id)
        ->first();

        if (!isset($cycle))
        {
          $current_date = Carbon::now();
          $version = ChartVersion::where('taxpayer_id', $taxPayer->id)->first();

          if (!isset($version))
          {
            $version = new ChartVersion();
            $version->taxpayer_id = $taxPayer->id;
            $version->name = 'Version Automatica';
            $version->save();
          }

          $cycle = new Cycle();
          $cycle->chart_version_id = $version->id;
          $cycle->year = $current_date->year;
          $cycle->start_date = new Carbon('first day of January');
          $cycle->end_date = new Carbon('last day of December');
          $cycle->taxpayer_id = $taxPayer->id;
          $cycle->save();
        }
        else
        {
          $startDate = $cycle->start_date;
          $endDate = $cycle->end_date;
        }


        try
        {
          $fixedAsset = $this->insertFixedAsset($chunkedData, $taxPayer,$cycle);

          $movementData[$i] = $fixedAsset;
        }
        catch (\Exception $e)
        {
          //Write items that don't insert into a variable and send back to ERP.
          //Do Nothing
        }
      }
    }

    return response()->json($movementData);
  }

  public function insertFixedAsset($data, Taxpayer $taxPayer,Cycle $cycle)
  {

    $fixedAsset = FixedAsset::where('ref_id', $data['id'])->where('taxpayer_id', $taxPayer->id)->first() ?? new FixedAsset();

    $ChartController= new ChartController();
    //$fixedAsset->ref_id = $data['id'];
    $fixedAsset->chart_id = $ChartController->createIfNotExists_FixedAsset($taxPayer,$cycle,$assetGroup,$lifeSpan)->id;

    $fixedAsset->taxpayer_id = $taxPayer->id;
    $fixedAsset->currency_id = $this->checkCurrency($data['CurrencyCode'], $taxPayer);

    // if ($data['CurrencyRate'] ==  '' )
    // { $fixedAsset->rate = $this->checkCurrencyRate($fixedAsset->currency_id, $taxPayer, $data['PurchaseDate']) ?? 1; }
    // else
    // { $fixedAsset->rate = $data['CurrencyRate'] ?? 1; }

    $fixedAsset->serial = $data['ItemCode'];
    $fixedAsset->name = $data['ItemName'];
    $fixedAsset->current_value  = $data['CurrentValue'];

    $fixedAsset->purchase_date = $this->convert_date($data['PurchaseDate']);

    $fixedAsset->purchase_value = $data['PurchaseValue'];

    $fixedAsset->quantity = $data['Quantity'];
    //Take todays date to keep track of how new data really is.

    $fixedAsset->sync_date = Carbon::now();

    $fixedAsset->save();
    //Return account movement if not null.
    return FixedAsset::find($fixedAsset->id)->with('chart')??null;
  }


}
