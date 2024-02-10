<?php

namespace App\Http\Controllers;

use App\Models\SalonPrice;
use App\Models\SalonService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class SalonServiceController extends Controller
{
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'category_id' => 'required|numeric',
                'service_name' => 'required|string',
                'price_male' => 'string',
                'price_female' => 'string',
            ]);
        } catch (ValidationException $validationException) {
            $errorStatus = $validationException->status;
            $errorMessage = $validationException->getMessage();
            return response()->json(['error' => $errorMessage], $errorStatus);
        }

        $salonService = SalonService::create([
            'salon_category_id' => $validated['category_id'],
            'service' => $validated['service_name'],
        ]);

        if (!empty($validated['price_male'])) {
            $salonService->salonPrices()->create([
                'gender' => 'M',
                'price' => $validated['price_male'],
            ]);
        }
        if (!empty($validated['price_female'])) {
            $salonService->salonPrices()->create([
                'gender' => 'F',
                'price' => $validated['price_female'],
            ]);
        }

        return response()->json(['service' => SalonService::with(['salonPrices' => function ($query) {
            $query->select('salon_service_id', 'gender', 'price');
        }])->where('id', $salonService->id)->get(['id', 'salon_category_id', 'service'])]);
    }


    public function update(Request $request)
    {
        try {
            $validated = $request->validate([
                'service_id' => 'required|numeric',
                'service_name' => 'string',
                'price_male' => 'string',
                'price_female' => 'string',
            ]);
        } catch (ValidationException $validationException) {
            $errorStatus = $validationException->status;
            $errorMessage = $validationException->getMessage();
            return response()->json(['error' => $errorMessage], $errorStatus);
        }
        try {
            $salonService = SalonService::findOrFail($validated['service_id']);
        } catch (ModelNotFoundException $modelException) {
            $errorMessage = $modelException->getMessage();
            return response()->json(['error' => $errorMessage], 404);
        }

        $salonService->service = $validated['service_name'];

        if(!$salonService->save()) return response()->json(['error' => 'Failed to update service name'], 500);

        $priceMale = 1;
        $priceFemale = 1;

        if (!empty($validated['price_male'])) {
            $priceMale = $salonService->salonPrices()->where('gender' , 'M')->update(['price' => $validated['price_male']]);
        }
        if (!empty($validated['price_female'])) {
            $priceFemale = $salonService->salonPrices()->where('gender' , 'F')->update(['price' => $validated['price_female']]);
        }

        if(!$priceMale || !$priceFemale) return response()->json(['error' => 'Failed to update price'], 500);

        return response()->json(['success' => 'Updated successfully']);
    }

    public function destroy(String $id)
    {
        if(!SalonService::destroy($id)) return response()->json(['error' => 'Failed to destroy Service'], 500);

        return response()->json(['success' => 'Service deleted successfully']);
    }
}
