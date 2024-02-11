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

    public function updateConfirm(String $id)
    {
        try {
            $reservation = SalonReservation::findOrFail($id);
        } catch (ModelNotFoundException $modelException) {
            $errorMessage = $modelException->getMessage();
            return response()->json(['error' => $errorMessage], 404);
        }

        $reservation->status = 'C';
        if(!$reservation->save()) return response()->json(['error' => 'Failed to update reservation status'], 500);

        return response()->json(['message' => 'Reservation status updated successfully']);
    }

    public function updateRefuse(String $id)
    {
        try {
            $reservation = SalonReservation::findOrFail($id);
        } catch (ModelNotFoundException $modelException) {
            $errorMessage = $modelException->getMessage();
            return response()->json(['error' => $errorMessage], 404);
        }

        $reservation->status = 'R';
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
