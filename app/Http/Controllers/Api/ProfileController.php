<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * Self-service profile actions. Currently just the signature image —
 * each user uploads their own once, and it's reused automatically
 * wherever their name is printed on a generated PDF (PR, RFQ, etc),
 * instead of a blank hand-signing line.
 */
class ProfileController extends Controller
{
    public function uploadSignature(Request $request)
    {
        $request->validate([
            'signature' => 'required|file|max:2048|mimes:jpg,jpeg,png',
        ]);

        $user = $request->user();

        // Remove the old file so we don't accumulate orphaned uploads
        // every time someone re-uploads their signature.
        if ($user->signature_path && file_exists(public_path($user->signature_path))) {
            @unlink(public_path($user->signature_path));
        }

        $file = $request->file('signature');
        $destination = public_path('uploads/signatures');
        if (! is_dir($destination)) {
            mkdir($destination, 0755, true);
        }
        $filename = 'user-'.$user->id.'-'.uniqid().'.'.$file->getClientOriginalExtension();
        $file->move($destination, $filename);

        $user->update([
            'signature_path' => 'uploads/signatures/'.$filename,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Signature uploaded successfully',
            'data' => $user->fresh(),
        ]);
    }

    public function deleteSignature(Request $request)
    {
        $user = $request->user();

        if ($user->signature_path && file_exists(public_path($user->signature_path))) {
            @unlink(public_path($user->signature_path));
        }

        $user->update(['signature_path' => null]);

        return response()->json([
            'success' => true,
            'message' => 'Signature removed',
            'data' => $user->fresh(),
        ]);
    }
}