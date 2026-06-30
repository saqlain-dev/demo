<?php

namespace App\Http\Controllers\Api\V1\Admin\Asset;

use App\Http\Controllers\Controller;
use App\Models\Admin\Asset\FixedAsset;
use App\Models\Admin\Asset\FixedAssetDepreciation;
use App\Models\Admin\Asset\FixedAssetRegister;
use App\Models\Admin\FinancialYear;
use App\Models\Admin\ItemVariant;
use App\Models\Item;
use App\Models\ItemCategory;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Log;

class FixedAssetRegisterController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $fixed_register = FixedAssetRegister::all();
        return resp(1, 'Successful!', $fixed_register, Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        ini_set('max_execution_time', 300);
        $request->validate([
            'start_date' => 'required|date|date_format:Y-m-d',
            'end_date' => 'required|date|date_format:Y-m-d',
            // 'name' => 'required',
        ]);
        try {
            DB::beginTransaction();
            $start_date = $request->start_date;
            $end_date = $request->end_date;
            $existing = FixedAssetRegister::where('start_date', $request->start_date)
                ->where('end_date', $request->end_date)
                ->first();

            if ($existing) {
                return resp(0, 'A register already exists for this financial year.', Response::HTTP_EXPECTATION_FAILED);
            }

            $register = FixedAssetRegister::firstOrCreate([
                'name' => $request->name ?? null,
                'start_date' => $start_date,
                'end_date' => $end_date,
            ]);
            /*if($register) {

                $itemVariants = ItemVariant::query()->whereNotNull('cost')->limit(10)->get(); // Filter only fixed assets

                foreach ($itemVariants as $item) {
                    // Create or find fixed asset
                    $fixedAsset = FixedAsset::firstOrCreate([
                        'item_variant_id' => $item->id,
                    ], [
                        'item_id' => $item->item_id,
                        'inventory_id' => $item->inventory_id,
                        'depreciation_rate' => $item->depreciation_rate ?? 33.00,
                    ]);

                    // Calculate depreciation
                    $months = Carbon::parse($start_date)->diffInMonths(Carbon::parse($end_date)) + 1;
                    if ($months <= 0) continue;

                    $rate = $fixedAsset->depreciation_rate / 100;
                    $cost = $item->cost ?? 0;

                    $annualDep = $cost * $rate;
                    $yearlyDep = round(($annualDep / 12) * $months, 2);

                    $lastDep = $fixedAsset->depreciations()->latest()->first();
                    $accumulated = $lastDep ? $lastDep->accumulated_depreciation + $yearlyDep : $yearlyDep;
                    $nbv = $cost - $accumulated;


                    FixedAssetDepreciation::create([
                        'fixed_asset_id' => $fixedAsset->id,
                        'register_id' => $register->id,
                        'fiscal_year' => Carbon::parse($start_date)->format('Y') . '-' . Carbon::parse($end_date)->format('Y'),
                        'depreciation_start_date' => $start_date,
                        'depreciation_end_date' => $end_date,
                        'months' => $months,
                        'depreciation_amount' => $yearlyDep,
                        'accumulated_depreciation' => $accumulated,
                        'net_book_value' => $nbv,
                        'cost' => $cost,
                    ]);
                }
            }*/
            DB::commit();
            return resp(1, 'Successful!', $register, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to update record!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(FixedAssetRegister $fixed_asset_register)
    {
        
        $data['fixedAssetRegister'] = $fixed_asset_register->load([
            'depreciations.itemVariant'=>['inventory','assignToEmploy'=>['branchOffice'],'assignToDept','vendor','location','purchaseOrder','item'=>['itemCategory','subCategory']], 
        ]); 

        return resp(1, 'Successful!', $data, Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, FixedAssetRegister $fixedAssetRegister)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(FixedAssetRegister $fixedAssetRegister)
    {
        $fixedAssetRegister->delete();
        $depreciation = FixedAssetDepreciation::where('register_id',$fixedAssetRegister->id)->delete();
        return resp(1, 'Fixed asset register deleted successfully.', [], Response::HTTP_OK);
    }

    public function fixedAssetRegDropDown()
    {
        // $data['fiscal_year']=FinancialYear::query()->with('financialYear')->get();
        $data['categories'] = ItemCategory::whereHas('items')->get();
       // $data['items']=Item::query()->with('itemCategory')->get();
        return resp(1, 'Successful!', $data, Response::HTTP_OK);
    }

    // public function addNewAssetToExistingRegister(Request $request)
    // {
    //     $request->validate([
    //         'register_id' => 'required|integer|exists:fixed_asset_registers,id',
    //         'item_category_id' => 'required|integer|exists:categories,id',
    //         'depreciation_rate' => 'required|numeric|min:0.01',
    //     ]);
    //     try {
    //         DB::beginTransaction();

    //         $register = FixedAssetRegister::findOrFail($request->register_id);

    //             $categoryID = $request->item_category_id;
    //             $rateInput = $request->depreciation_rate;
    //             try {

    //                 $items = Item::where('category_id', $categoryID)->get();
    //                 foreach($items as $item){
    //                     $assets = ItemVariant::where('item_id', $item->id)
    //                                         ->whereHas('inventory', function($q) {
    //                                             $q->where('inventory_type', 1);
    //                                         })
    //                                         ->with(['inventory' => function($q) {
    //                                             $q->where('inventory_type', 1);
    //                                         }])
    //                                         ->get();
    //                     foreach($assets as $asset){
    //                             $existing = FixedAssetDepreciation::where('item_variant_id', $asset->id)
    //                                         ->where('register_id', $register->id)
    //                                         ->exists();

    //                             if ($existing) continue;

    //                             $purchaseDate = Carbon::parse($asset->purchase_date);
    //                             $registerEndDate = Carbon::parse($register->end_date);

    //                             if ($purchaseDate->gt($registerEndDate)) continue;

    //                             $startDate = $purchaseDate->gt($register->start_date)
    //                                 ? $purchaseDate
    //                                 : Carbon::parse($register->start_date);

    //                             $months = $startDate->diffInMonths(Carbon::parse($register->end_date));
    //                             if ($months <= 0) continue;

    //                             $rate = $rateInput / 100;
    //                             $annualDep = $asset->cost * $rate;
    //                             $yearlyDep = round(($annualDep / 12) * $months, 2);

    //                             $lastDep = FixedAssetDepreciation::where('item_variant_id', $asset->id)->latest()->first();
    //                             $accumulated = $lastDep ? $lastDep->accumulated_depreciation + $yearlyDep : $yearlyDep;
    //                             $nbv = $asset->cost - $accumulated;

    //                             FixedAssetDepreciation::create([
    //                                 'item_variant_id' => $asset->id,
    //                                 'register_id' => $register->id,
    //                                 'fiscal_year' => Carbon::parse($register->start_date)->format('Y') . '-' . Carbon::parse($register->end_date)->format('Y'),
    //                                 'depreciation_start_date' => $startDate,
    //                                 'depreciation_end_date' => $register->end_date,
    //                                 'months' => $months,
    //                                 'depreciation_amount' => $yearlyDep,
    //                                 'accumulated_depreciation' => $accumulated,
    //                                 'net_book_value' => $nbv,
    //                                 'cost' => $asset->cost,
    //                                 'depreciation_rate'=>$rateInput
    //                         ]);
    //                     }
    //                 }

    //                  DB::commit();
    //                 return resp(1, 'Assets processed successfully.', [], Response::HTTP_CREATED);
    //             } catch (ModelNotFoundException $e) {
    //                 // Skip item if not found
    //                 continue;
    //             }

    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         return resp(0, 'Failed to process assets!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
    //     }


    // }
    // public function addNewAssetToExistingRegister(Request $request)
    // {
    //     $request->validate([
    //         'register_id' => 'required|integer|exists:fixed_asset_registers,id',
    //         'item_category_id' => 'required|integer',
    //         'depreciation_rate' => 'required|numeric|min:0.01',
    //     ]);

    //     try {
    //         DB::beginTransaction();

    //         $register = FixedAssetRegister::findOrFail($request->register_id);
    //         $categoryID = $request->item_category_id;
    //         $rateInput = $request->depreciation_rate;

    //         $items = Item::where('category_id', $categoryID)->get();

    //         foreach ($items as $item) {
    //             try {
    //                 $assets = ItemVariant::where('item_id', $item->id)
    //                     ->whereHas('inventory', function($q) {
    //                         $q->where('inventory_type', 1);
    //                     })
    //                     ->with(['inventory' => function($q) {
    //                         $q->where('inventory_type', 1);
    //                     }])
    //                     ->get();

    //                 foreach ($assets as $asset) {
    //                     $existing = FixedAssetDepreciation::where('item_variant_id', $asset->id)
    //                         ->where('register_id', $register->id)
    //                         ->exists();

    //                     if ($existing) continue;

    //                     $purchaseDate = Carbon::parse($asset->purchase_date);
    //                     $registerEndDate = Carbon::parse($register->end_date);

    //                     if ($purchaseDate->gt($registerEndDate)) continue;
                        
    //                     $lastDep = FixedAssetDepreciation::where('item_variant_id', $asset->id)->latest()->first();
                       
    //                     if(!$lastDep){
    //                         $startDate = $purchaseDate->gt($register->start_date)
    //                         ? $purchaseDate
    //                         : Carbon::parse($register->start_date);
    //                     }else{
    //                         $startDate = Carbon::parse($register->start_date);
    //                     }

    //                     $months = $startDate->diffInMonths(Carbon::parse($register->end_date)) + 1;
                       
    //                     if ($months <= 0) continue;

    //                     $rate = $rateInput / 100;
    //                     $annualDep = ($asset->cost - ($lastDep->accumulated_depreciation ?? 0) - ($lastDep->depreciation_amount ?? 0)) * $rate;
                       
    //                     $yearlyDep = round(($annualDep / 12) * $months, 2);
                        
    //                     $nbv = 0;
    //                     if($lastDep){
    //                         $accumulated =  $lastDep->accumulated_depreciation + $yearlyDep;
    //                         $nbv = $asset->cost - $accumulated - $yearlyDep;
                          
    //                     }else{
    //                         $accumulated = 0;
    //                         $nbv = $asset->cost - ($accumulated + $yearlyDep);
                          
    //                     }
                        

    //                     FixedAssetDepreciation::create([
    //                         'item_variant_id' => $asset->id,
    //                         'register_id' => $register->id,
    //                         'fiscal_year' => Carbon::parse($register->start_date)->format('Y') . '-' . Carbon::parse($register->end_date)->format('Y'),
    //                         'depreciation_start_date' => $startDate,
    //                         'depreciation_end_date' => $register->end_date,
    //                         'months' => $months,
    //                         'depreciation_amount' => $yearlyDep,
    //                         'accumulated_depreciation' => $accumulated,
    //                         'net_book_value' => $nbv,
    //                         'cost' => $asset->cost,
    //                         'depreciation_rate' => $rateInput
    //                     ]);
    //                 }
    //             } catch (ModelNotFoundException $e) {
    //                 // Skip item if not found
    //                 continue;
    //             }
    //         }
    //         DB::commit();
    //         return resp(1, 'Assets processed successfully.', [], Response::HTTP_CREATED);
    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         return resp(0, 'Failed to process assets!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
    //     }
    // }
    public function addNewAssetToExistingRegister(Request $request)
    {
        $request->validate([
            'register_id' => 'required|integer|exists:fixed_asset_registers,id',
            'item_category_id' => 'required|integer',
            'depreciation_rate' => 'required|numeric|min:0.01',
        ]);

        try {
            DB::beginTransaction();

            $register = FixedAssetRegister::findOrFail($request->register_id);
            $categoryID = $request->item_category_id;
            $rateInput = $request->depreciation_rate;
            $rate = $rateInput / 100;

            $items = Item::where('category_id', $categoryID)->get();

            foreach ($items as $item) {
                try {
                    $assets = ItemVariant::where('item_id', $item->id)
                        ->whereHas('inventory', function($q) {
                            $q->where('inventory_type', 1);
                        })
                        ->with(['inventory' => function($q) {
                            $q->where('inventory_type', 1);
                        }])
                        ->get();

                    foreach ($assets as $asset) {
                        $exists = FixedAssetDepreciation::where('item_variant_id', $asset->id)
                            ->where('register_id', $register->id)
                            ->exists();
                        if ($exists) continue;

                        $purchaseDate = Carbon::parse($asset->purchase_date);
                        $registerEndDate = Carbon::parse($register->end_date);
                        if ($purchaseDate->gt($registerEndDate)) continue;

                        $lastDep = FixedAssetDepreciation::where('item_variant_id', $asset->id)->latest()->first();

                        $startDate = $lastDep
                            ? Carbon::parse($register->start_date)
                            : ($purchaseDate->gt($register->start_date)
                                ? $purchaseDate
                                : Carbon::parse($register->start_date));

                        $months = $startDate->diffInMonths(Carbon::parse($register->end_date)) + 1;
                        if ($months <= 0) continue;

                        $prevAccumulated = $lastDep->accumulated_depreciation ?? 0;
                        $prevDep = $lastDep->depreciation_amount ?? 0;
                        $prevTotal = $prevAccumulated;

                        $currentNBV = $asset->cost - $prevAccumulated;
                        $annualDep = $currentNBV * $rate;
                        $yearlyDep = round(($annualDep / 12) * $months, 2);
                        $accumulated = $prevAccumulated + $yearlyDep;
                        $nbv = $asset->cost - $accumulated;

                        FixedAssetDepreciation::create([
                            'item_variant_id' => $asset->id,
                            'register_id' => $register->id,
                            'fiscal_year' => Carbon::parse($register->start_date)->format('Y') . '-' . Carbon::parse($register->end_date)->format('Y'),
                            'depreciation_start_date' => $startDate,
                            'depreciation_end_date' => $register->end_date,
                            'months' => $months,
                            'depreciation_amount' => $yearlyDep,
                            'accumulated_depreciation' => $accumulated,
                            'net_book_value' => $nbv,
                            'cost' => $asset->cost,
                            'depreciation_rate' => $rateInput
                        ]);
                    }
                } catch (ModelNotFoundException $e) {
                    continue;
                }
            }

            DB::commit();
            return resp(1, 'Assets processed successfully.', [], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to process assets!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }



    public function getFixedAssetVariants(Request $request)
    {
        $request->validate([
            'register_id' => 'required|integer|exists:fixed_asset_registers,id',
            'item_id' => 'required|integer|exists:items,id',
        ]);
        try {
            DB::beginTransaction();
            $registerId=$request->register_id;
            $data['fixed_asset_variants'] = ItemVariant::query()
                ->where('item_id', $request->item_id)
                ->whereHas('inventory', function ($q) {
                    $q->where('inventory_type', 1); // Filter by inventory type
                })
                ->whereDoesntHave('directDepreciations', function ($q) use ($registerId) {
                    $q->where('register_id', $registerId); // Exclude already added variants for the register
                })
                ->with(['inventory','assignToEmploy'=>['branchOffice'],'assignToDept','vendor','location','purchaseOrder','item'=>['itemCategory','subCategory']])
                ->get();



            DB::commit();
            return resp(1, 'successfully.', $data, Response::HTTP_CREATED);

        } catch (\Exception $e) {
            DB::rollBack();
            return resp(0, 'Failed to process assets!', ['error' => $e->getMessage(), 'line' => $e->getLine()], Response::HTTP_EXPECTATION_FAILED);
        }
    }

}
