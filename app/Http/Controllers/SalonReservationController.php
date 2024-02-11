<?php

namespace App\Http\Controllers;

use App\Models\SalonReservation;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class SalonReservationController extends Controller
{
    // For Admin
    public function index(Request $request)
    {
        return response()->json(['reservations' => SalonReservation::all()]);
    }

    public function updateStatus(Request $request)
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
            $reservation = SalonReservation::findOrFail();
        } catch (ModelNotFoundException $modelException) {
            $errorMessage = $modelException->getMessage();
            return response()->json(['error' => $errorMessage], 404);
        }

        if($reservation->status) $reservation->status = 'C';
        else $reservation->status = 'R';

        if(!$reservation->save()) return response()->json(['error' => 'Failed to update reservation status'], 500);

        return response()->json(['message' => 'Reservation status updated successfully']);
    }

    // For Student
    public function show(Request $request)
    {
        $userId = $request->user()->id;

        return response()->json(['reservations' => SalonReservation::with('salonPrice')->where('user_id', $userId)->get()]);
    }
}
