<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Filliere;
use App\Models\Stagiaire;
use App\Models\Module;
use App\Models\Tutteur;
use Carbon\Carbon;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Laravel\Sanctum\PersonalAccessToken;
use Illuminate\Support\Facades\Storage;

class StagiaireController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum')->except('login', "resetPassword");
    }

    public function index() : Response
    {
        $stagiaireId = Auth::user()->id;
        $data = DB::table("stagiaires")
        ->join("fillieres","stagiaires.fill_id", "=", "fillieres.id")
        ->select("stagiaires.*","fillieres.nom AS nomFill")
        ->where("stagiaires.id", $stagiaireId)
        ->get();
        return Response([
            "data" => $data,
        ]);
    }
    // ----------------------------------------------------- Login Process
    public function login(Request $request): Response
    {
        $stagiaire = $request->validate([
            'email' => 'required|string',
            'password' => 'required|string|min:6',
        ]);
        if (Auth::guard('stagiaire')->attempt($stagiaire)) {
            $stagiaire = Stagiaire::where("email", $request->email)->first();
            if ($stagiaire && Hash::check($request->password, $stagiaire->password)) {
                $device = $request->userAgent();
                $token = $stagiaire->createToken($device)->plainTextToken;
                // ------------- the login's time ------------------
                DB::table('stagiaires')
                ->where('id', $stagiaire->id)
                ->update(['login_at' => Carbon::now()->toDateString()]);
                // ------------- the login's time ------------------
                return Response([
                    "success" => true,
                    "token" => $token,
                ]);
            } else {
                return Response([
                    'message' => 'Your data is incorect',
                ]);
            }
        }
        return Response([
            'message' => 'Your data is incorect',
        ]);
    }
    // ----------------------------------------------------- Logout Process
    public function logout($token = null): Response
    {
        $stagiaire = Auth::guard('sanctum')->user();
        if (null == $token) {
            $stagiaire->currentAccessToken()->delete();
        }
        $personaleToken = PersonalAccessToken::findToken($token);
        if ($personaleToken && $stagiaire->id === $personaleToken->tokenable_id && get_class($stagiaire) === $personaleToken->tokenable_type) {
            $personaleToken->delete();
        }
        return Response([
            'message' => 'logout successful',
        ]);
    }
    // ----------------------------------------------------- Change Password

    public function changePassword(Request $request)
    {
        $validate = $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|string|min:6',
            'password_confirmation' => 'required|same:new_password',
        ]);

        $user = Auth::user()->id;
        $stagiaire = Stagiaire::find($user);
        if (!$validate || !Hash::check($request->input('current_password'), $stagiaire->password)) {
            return response()->json(['error' => 'Current password is incorrect'], 401);
        }
        $stagiaire->update([
            'password' => Hash::make($request->input('new_password')),
        ]);

        return response()->json(['message' => 'Password changed successfully']);
    }
    // ----------------------------------------------------- Reseting Password
    public function resetPassword(Request $request)
    {
        $email = $request->input('email');
        $stagiaire = Stagiaire::where('email_verify', $email)->first();

        if (!$stagiaire) {
            return response()->json(['message' => 'Invalid email'], 404);
        }

        // Generate a new password (for demonstration purposes)
        $newPassword = substr(md5(microtime()), 0, 8);
        $stagiaire->password = Hash::make($newPassword);
        $stagiaire->save();
        // $stagiaire->update([
        //     "password" => Hash::make($newPassword),
        // ]);

        // Send the new password to the stagiaire's email
        Mail::raw("Your new password is: $newPassword", function ($message) use ($email) {
            $message->to($email)->subject('Password Reset');
        });

        return response()->json(['message' => 'Password reset successful']);
    }
    // ----------------------------------------------------- Email Verify
    public function emailVerify(Request $request)
    {
        $userId = Auth::user()->id;
        $emailVerify = $request->input('email_verify');

        $stagiaire = Stagiaire::where('id', $userId)->first();

        if ($stagiaire) {
            $stagiaire->email_verify = $emailVerify;
            $stagiaire->save();
        } else {
            Stagiaire::create([
                'id' => $userId,
                'email_verify' => $emailVerify,
            ]);
        }

        return response()->json(['message' => 'Email verification updated successfully']);
    }

//------------------------------------------------------------- Show Notes
public function showNotes(Request $request) : Response
    {
        $id = $userId = Auth::user()->id;;

        $stagiaires = Stagiaire::where('id', $id)->first();

        $modules = $stagiaires->modules;

        $notes = [];

        foreach ($modules as $module) {
            $premierControle = $module->premierControles()->where('stagiaire_id', $stagiaires->id)->first();
            $deuxiemeControle = $module->deuxiemeControles()->where('stagiaire_id', $stagiaires->id)->first();
            $troisiemeControle = $module->troisiemControles()->where('stagiaire_id', $stagiaires->id)->first();
            $efm = $module->efm()->where('stagiaire_id', $stagiaires->id)->first();

            $notes[] = [
                'module' => $module->nom,
                'premierControle' => $premierControle ? $premierControle->note : '',
                'deuxiemeControle' => $deuxiemeControle ? $deuxiemeControle->note : '',
                'troisiemeControle' => $troisiemeControle ? $troisiemeControle->note : '',
                'efm' => $efm ? $efm->note : '',
                'noteGenerale' => $module->pivot->note_general,
            ];
        }

        return response([
            'notes' => $notes,
            'stagiaires' => $stagiaires
        ]);
    }
        // _________________________________________________________________ upload Profile Image

    public function uploadProfileImage(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg|max:2048', // Validate image
        ]);

        $stagiaire = Auth::user(); // Get authenticated stagiaire

        // Delete the old image if exists
        if ($stagiaire->image_url) {
            Storage::delete('public/images/' . basename($stagiaire->image_url));
        }

        // Store new image
        $imagePath = $request->file('image')->store('public/images');
        $imageUrl = str_replace('public/', 'storage/', $imagePath); // Convert path for public access

        // Update stagiaire's image_url
        $stagiaire->update(['image_url' => $imageUrl]);

        return response()->json([
            'message' => 'Profile image uploaded successfully',
            'image_url' => asset($imageUrl),
        ], 200);
    }

    // _________________________________________________________________ delete Profile Image

    public function deleteProfileImage()
    {
        $stagiaire = Auth::user();

        if (!$stagiaire->image_url) {
            return response()->json(['message' => 'No image to delete'], 400);
        }

        // Delete the stored image
        Storage::delete('public/images/' . basename($stagiaire->image_url));

        // Remove image URL from database
        $stagiaire->update(['image_url' => null]);

        return response()->json(['message' => 'Profile image deleted successfully'], 200);
    }
}
