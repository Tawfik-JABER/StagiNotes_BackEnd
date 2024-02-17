<?php

namespace App\Http\Controllers;

use App\Models\DeuxiemControle;
use App\Models\Efm;
use Illuminate\Http\Request;
use App\Models\Filliere;
use App\Models\Formatteur;
use App\Models\Module;
use App\Models\PremierControle;
use App\Models\Stagiaire;
use App\Models\TroisiemControle;
use App\Models\Tutteur;
use Carbon\Carbon;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Laravel\Sanctum\PersonalAccessToken;

class FormatteurController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum')->except('login', "resetPassword");
    }

    public function index(): Response
    {
        $data = Auth::user();
        return Response([
            'data' => $data,
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
        $formatteur = Formatteur::find($user);
        if (!$validate || !Hash::check($request->input('current_password'), $formatteur->password)) {
            return response()->json(['error' => 'Current password is incorrect'], 401);
        }
        $formatteur->update([
            'password' => Hash::make($request->input('new_password')),
        ]);

        return response()->json(['message' => 'Password changed successfully']);
    }
    // ----------------------------------------------------- Reseting Password
    public function resetPassword(Request $request)
    {
        $email = $request->input('email');
        $formatteur = Formatteur::where('email_verify', $email)->first();

        if (!$formatteur) {
            return response()->json(['message' => 'Invalid email'], 404);
        }

        // Generate a new password (for demonstration purposes)
        $newPassword = substr(md5(microtime()), 0, 8);
        $formatteur->password = Hash::make($newPassword);
        $formatteur->save();
        // $formatteur->update([
        //     "password" => Hash::make($newPassword),
        // ]);

        // Send the new password to the formatteur's email
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

        $formatteur = Formatteur::where('id', $userId)->first();

        if ($formatteur) {
            $formatteur->email_verify = $emailVerify;
            $formatteur->save();
        } else {
            Formatteur::create([
                'id' => $userId,
                'email_verify' => $emailVerify,
            ]);
        }

        return response()->json(['message' => 'Email verification updated successfully']);
    }
    // ----------------------------------------------------- Login Process
    public function login(Request $request): Response
    {
        $formatteur = $request->validate([
            'email' => 'required|string',
            'password' => 'required|string|min:6',
        ]);
        if (Auth::guard('formatteur')->attempt($formatteur)) {
            $formatteur = Formatteur::where("email", $request->email)->first();
            if ($formatteur && Hash::check($request->password, $formatteur->password)) {
                $device = $request->userAgent();
                $token = $formatteur->createToken($device)->plainTextToken;
                // ------------- the login's time ------------------
                DB::table('formatteurs')
                ->where('id', $formatteur->id)
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
        $formatteur = Auth::guard('sanctum')->user();
        if (null == $token) {
            $formatteur->currentAccessToken()->delete();
        }
        $personaleToken = PersonalAccessToken::findToken($token);
        if ($personaleToken && $formatteur->id === $personaleToken->tokenable_id && get_class($formatteur) === $personaleToken->tokenable_type) {
            $personaleToken->delete();
        }
        return Response([
            'message' => 'logout successful',
        ]);
    }

    // ----------------------------------------------------- insert notes Process

    public function form_data()
    {
        $userId = Auth::user()->id;
        $fillieres = DB::table('fillieres')
        ->join('formateur_filliere_modules', 'fillieres.id', '=', 'formateur_filliere_modules.filliere_id')
        ->select('fillieres.*')
        ->where('formateur_filliere_modules.formateur_id', '=', $userId)
        ->get();
        $annees = DB::table('stagiaires')
        ->join('formateur_filliere_modules', 'stagiaires.fill_id', '=', 'formateur_filliere_modules.filliere_id')
        ->select('stagiaires.annee')
        ->where('formateur_filliere_modules.formateur_id', '=', $userId)
        ->get();
        $groups = DB::table('stagiaires')
        ->join('formateur_filliere_modules', 'stagiaires.fill_id', '=', 'formateur_filliere_modules.filliere_id')
        ->select('stagiaires.group')
            ->where('formateur_filliere_modules.formateur_id', '=', $userId)
            ->get();
        $modules = DB::table('modules')
            ->join('formateur_filliere_modules', 'modules.id', '=', 'formateur_filliere_modules.module_id')
            ->select('modules.*')
            ->where('formateur_filliere_modules.formateur_id', '=', $userId)
            ->get();
            // $fillieres = Filliere::all(['id', 'nom']);
        // $annees = Stagiaire::distinct('annee')->pluck('annee');
        // $groups = Stagiaire::distinct('group')->pluck('group');
        // $modules = Module::all(['id', 'nom']);

        $formData = [
            'fillieres' => $fillieres,
            'annees' => $annees,
            'groups' => $groups,
            'modules' => $modules,
        ];

        return response()->json($formData);
    }

    public function stagiaires(Request $request)
    {
        $filliereId = $request->input('filliereId');
        $annee = $request->input('annee');
        $group = $request->input('group');

        $stagiaires = Stagiaire::where('fill_id', $filliereId)
        ->where('annee', $annee)
        ->where('group', $group)
        ->get(['id', 'nom', 'prenom']);

        return response()->json($stagiaires);
    }

    public function store_note(Request $request)
    {
        $note = $request->input('note');
        $stagiaireId = $request->input('stagiaireId');
        $moduleId = $request->input('moduleId');
        $controleType = $request->input('controleType');

        // Determine the appropriate Controle model based on controleType
        $controleModel = $this->getControleModel($controleType);

        // Store the note in the appropriate Controle model
        $controle = $controleModel::create(
            ['stagiaire_id' => $stagiaireId, 'module_id' => $moduleId,"annee_schol"=>"2023"
            ,'note' => $note]
        );
        $controle->save();

        return response()->json(['message' => 'Note stored successfully']);
    }

    private function getControleModel($controleType)
    {
        // Map controleType to the corresponding Controle model

        switch ($controleType) {
            case 'premier_controles':
                return PremierControle::class;
                case 'deuxieme_controles':
                    return DeuxiemControle::class;
                case 'troisiem_controles':
                    return TroisiemControle::class;
                case 'efms':
                    return Efm::class;
                default:
                // Throw an error or handle the case where the controleType is not recognized
                break;
            }
    }
    // ----------------------------------------------------- insert notes Process
}
