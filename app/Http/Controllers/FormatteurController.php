<?php

namespace App\Http\Controllers;

use App\Models\DeuxiemControle;
use App\Models\Efm;
use Illuminate\Http\Request;
use App\Models\Filliere;
use App\Models\Formateur_filliere_module;
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
            ->distinct()
            ->get();

        $annees = DB::table('stagiaires')
            ->join('formateur_filliere_modules', 'stagiaires.fill_id', '=', 'formateur_filliere_modules.filliere_id')
            ->select('stagiaires.annee')
            ->where('formateur_filliere_modules.formateur_id', '=', $userId)
            ->distinct()
            ->get();

        $groups = DB::table('stagiaires')
            ->join('formateur_filliere_modules', 'stagiaires.fill_id', '=', 'formateur_filliere_modules.filliere_id')
            ->select('stagiaires.group')
            ->where('formateur_filliere_modules.formateur_id', '=', $userId)
            ->distinct()
            ->get();

        $modules = DB::table('modules')
            ->join('formateur_filliere_modules', 'modules.id', '=', 'formateur_filliere_modules.module_id')
            ->select('modules.*')
            ->where('formateur_filliere_modules.formateur_id', '=', $userId)
            ->distinct()
            ->get();

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
        if ($controleType === 'PremierControle') {
            $controleModel = PremierControle::class;
        } elseif ($controleType === 'DeuxiemControle') {
            $controleModel = DeuxiemControle::class;
        } elseif ($controleType === 'TroisiemControle') {
            $controleModel = TroisiemControle::class;
        }else {
            $controleModel = Efm::class;
        }

        // Check if a record already exists
        $controle = $controleModel::where('stagiaire_id', $stagiaireId)
            ->where('module_id', $moduleId)
            ->where('annee_schol', date("Y"))
            ->first();

        if ($controle) {
            // If exists, update the note
            $controle->update(['note' => $note]);
            return response()->json(['message' => 'Note updated successfully']);
        } else {
            // Otherwise, create a new entry
            $controleModel::create([
                'stagiaire_id' => $stagiaireId,
                'module_id' => $moduleId,
                'annee_schol' => date("Y"),
                'note' => $note
            ]);

            return response()->json(['message' => 'Note stored successfully']);
        }
    }

    public function getNotes(Request $request)
    {
        $stagiaireIds = $request->input('stagiaireIds'); // Array of stagiaire IDs
        $moduleId = $request->input('moduleId');
        $controleType = $request->input('controleType');

        // Determine the correct model based on controleType
        if ($controleType === 'PremierControle') {
            $controleModel = PremierControle::class;
        } elseif ($controleType === 'DeuxiemControle') {
            $controleModel = DeuxiemControle::class;
        } else {
            $controleModel = TroisiemControle::class;
        }

        // Fetch notes for selected stagiaires and module
        $notes = $controleModel::whereIn('stagiaire_id', $stagiaireIds)
            ->where('module_id', $moduleId)
            ->pluck('note', 'stagiaire_id'); // Returns associative array: [stagiaire_id => note]

        return response()->json($notes);
    }

    public function getModulesByFilliere($filliereId)
    {
        $userId = Auth::user()->id;

        // Fetch only the modules that the formateur teaches in the selected filliere
        $modules = DB::table('modules')
            ->join('formateur_filliere_modules', 'modules.id', '=', 'formateur_filliere_modules.module_id')
            ->where('formateur_filliere_modules.filliere_id', '=', $filliereId)
            ->where('formateur_filliere_modules.formateur_id', '=', $userId) // Ensure only the formateur's modules
            ->select('modules.id', 'modules.nom')
            ->distinct()
            ->get();

        return response()->json($modules);
    }

    public function getGroupsByFilliere($filliereId)
    {
        // Fetch unique groups from stagiaires where fill_id matches the selected filliere
        $groups = DB::table('stagiaires')
            ->where('fill_id', '=', $filliereId)
            ->select('group')
            ->distinct()
            ->get();

        return response()->json($groups);
    }

    public function showStagiaireInfo()
    {
        $formateurId = Auth::user()->id;
        // Get the fillieres taught by the formateur
        $fillieres = Formateur_filliere_module::where('formateur_id', $formateurId)
                    ->pluck('filliere_id');

        // Get the stagiaires who belong to these fillieres
        $stagiaires = Stagiaire::whereIn('fill_id', $fillieres)->get();

        return response()->json([
            'stagiaires' => $stagiaires,
        ]);
    }

    public function showStagiaireNotes(Request $request) : Response
    {
        $id = $request->id;

        $stagiaire = Stagiaire::where('id', $id)->first();

        $modules = $stagiaire->modules;

        $notes = [];

        foreach ($modules as $module) {
            $premierControle = $module->premierControles()->where('stagiaire_id', $stagiaire->id)->first();
            $deuxiemeControle = $module->deuxiemeControles()->where('stagiaire_id', $stagiaire->id)->first();
            $troisiemeControle = $module->troisiemControles()->where('stagiaire_id', $stagiaire->id)->first();
            $efm = $module->efm()->where('stagiaire_id', $stagiaire->id)->first();

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
            'notes' => $notes
        ]);
    }



    // ----------------------------------------------------- insert notes Process
}
