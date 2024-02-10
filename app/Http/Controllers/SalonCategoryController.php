<?php

namespace App\Http\Controllers;

use App\Exceptions\DestroyException;
use App\Models\SalonCategory;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class SalonCategoryController extends Controller
{
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'category_name' => 'required|string'
            ]);
        } catch(ValidationException $validationException) {
            $errorStatus = $validationException->status;
            $errorMessage = $validationException->getMessage();
            return response()->json(['error' => $errorMessage], $errorStatus);
        }

        $salonCategory = SalonCategory::create([
            'category' => $validated['category_name'],
        ]);

        if(!$salonCategory) return response()->json(['Falied to create category'], 500);

        return response()->json(['salon_category' => $salonCategory], 201);
    }

    public function update(Request $request)
    {
        try {
            $validated = $request->validate([
                'category_id' => 'required|numeric',
                'category_name' => 'required|string',
            ]);
        } catch(ValidationException $validationException) {
            $errorStatus = $validationException->status;
            $errorMessage = $validationException->getMessage();
            return response()->json(['error' => $errorMessage], $errorStatus);
        }

        try {
            $user = SalonCategory::findOrFail($validated['category_id']);
        } catch(ModelNotFoundException $modelException) {
            $errorMessage = $modelException->getMessage();
            return response()->json(['error' => $errorMessage], 404);
        }

        $user->name = $validated['name'];
        $user->phone_number = $validated['phone_number'];

        if(!$user->save()) return response()->json(['error' => 'Failed to update profile'], 500);

        return response()->json(['message' => 'Update profile successfully']);

    }

    public function destroy(String $id)
    {
        if (!SalonCategory::destroy($id)) {
            throw new DestroyException('Failed to destroy category');
        }

        return response()->json(['message'=>'Destroy category successfully']);
    }
}
