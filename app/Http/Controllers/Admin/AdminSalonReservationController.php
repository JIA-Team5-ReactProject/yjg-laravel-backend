<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SalonReservation;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class AdminSalonReservationController extends Controller
{
    public function index(Request $request)
    {
        return response()->json(['reservations' => SalonReservation::all()]);
    }

    public function update(Request $request)
    {
        try {
            $validated = $request->validate([
                'id' => 'required|numeric',
                'status' => 'required|boolean'
            ]);
        } catch (ValidationException $validationException) {
            $errorStatus = $validationException->status;
            $errorMessage = $validationException->getMessage();
            return response()->json(['error' => $errorMessage], $errorStatus);
        }

        try {
            $reservation = SalonReservation::findOrFail($validated['id']);
        } catch (ModelNotFoundException $modelException) {
            $errorMessage = $modelException->getMessage();
            return response()->json(['error' => $errorMessage], 404);
        }

        if($validated['status']) $reservation->status = 'C';
        else $reservation->status = 'R';

        if(!$reservation->save()) return response()->json(['error' => 'Failed to update reservation status'], 500);

        return response()->json(['message' => 'Reservation status updated successfully']);
    }
}
