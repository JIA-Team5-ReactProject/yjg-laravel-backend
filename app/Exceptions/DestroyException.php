<?php

namespace App\Exceptions;

use Exception;

class DestroyException extends Exception
{
    /**
     * Report the exception.
     *
     * @return bool|null
     */
    public function report()
    {
        //
    }

    /**
     * Render the exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function render()
    {
        return response()->json(['error'=>'Failed to destroy user'],500);
    }
}
