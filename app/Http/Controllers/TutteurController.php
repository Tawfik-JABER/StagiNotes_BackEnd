<?php

namespace App\Http\Controllers;

use App\Models\Absence;
use App\Models\Director;
use App\Models\Filliere;
use App\Models\Formateur_filliere_module;
use App\Models\Formatteur;
use App\Models\Module;
use App\Models\Stagiaire;
use App\Models\Stagiaire_module;
use App\Models\Tutteur;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Laravel\Sanctum\PersonalAccessToken;

class TutteurController extends Controller
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
    // ----------------------------------------------------- Reseting Password
    public function resetPassword(Request $request)
    {
        $email = $request->input('email');
        $tutteur = Tutteur::where('email_verify', $email)->first();

        if (!$tutteur) {
            return response()->json(['message' => 'Invalid email'], 404);
        }

        // Generate a new password (for demonstration purposes)
        $newPassword = substr(md5(microtime()), 0, 8);
        $tutteur->password = Hash::make($newPassword);
        $tutteur->save();

        // Send the new password to the director's email
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

        $tutteur = Tutteur::where('id', $userId)->first();

        if ($tutteur) {
            $tutteur->email_verify = $emailVerify;
            $tutteur->save();
        } else {
            Tutteur::create([
                'id' => $userId,
                'email_verify' => $emailVerify,
            ]);
        }
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
            $tutteur = Tutteur::find($user);
            if (!$validate || !Hash::check($request->input('current_password'), $tutteur->password)) {
                return response()->json(['error' => 'Current password is incorrect'], 401);
            }
            $tutteur->update([
                'password' => Hash::make($request->input('new_password')),
            ]);

            return response()->json(['message' => 'Password changed successfully']);
        }
    // ----------------------------------------------------- Login Process
    public function login(Request $request): Response
    {
        $tutteur = $request->validate([
            'email' => 'required|string',
            'password' => 'required|string|min:6',
        ]);
        if (Auth::guard('tutteur')->attempt($tutteur)) {
            $tutteur = Tutteur::where("email", $request->email)->first();
            if ($tutteur && Hash::check($request->password, $tutteur->password)) {
                $device = $request->userAgent();
                $token = $tutteur->createToken($device)->plainTextToken;
                // ------------- the login's time ------------------
                DB::table('tutteurs')
                ->where('id', $tutteur->id)
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
        $tutteur = Auth::guard('sanctum')->user();
        if (null == $token) {
            $tutteur->currentAccessToken()->delete();
        }
        $personaleToken = PersonalAccessToken::findToken($token);
        if ($personaleToken && $tutteur->id === $personaleToken->tokenable_id && get_class($tutteur) === $personaleToken->tokenable_type) {
            $personaleToken->delete();
        }
        return Response([
            'message' => 'logout successful',
        ]);
    }
    // ----------------------------------------------------- Stagiare CRUD
    public function showStagiaires(): Response
    {
        $filieres   = Filliere::all();
        $stagiaires = DB::table("stagiaires")
            ->join("fillieres", "stagiaires.fill_id", "=", "fillieres.id")
            ->select("fillieres.nom AS nomFill", "stagiaires.*")
            ->get();
        // $stagiaires = Stagiaire::with('filliere')->get();
        return Response([
            'stagiaires' => $stagiaires,
            'filieres' => $filieres,
        ]);
    }
    public function addStagiaire(Request $request): Response
    {
        $valide = $request->validate([
            "cin" => "required|min:7|max:8",
            "nom" => "string|required",
            "prenom" => "string|required",
            "email" => "email|required",
            "password" => "string|required|min:6",
            'fill_id'=>"string|required",
            'numero'=>"string|required",
            'cef'=>"string|required",
            'group'=>"string|required",
            'annee'=>"string|required",
            'niveau'=>"string|required",
            "sexe" => "string|required",
        ]);
        if ($valide) {
            $addStagiaire = Stagiaire::create([
                "cin" => $request->cin,
                "nom" => $request->nom,
                "prenom" => $request->prenom,
                "email" => $request->email,
                "password" => Hash::make($request->password),
                "fill_id" => $request->fill_id,
                'numero'=>$request->numero,
                'cef'=>$request->cef,
                'group'=>$request->group,
                'annee'=>$request->annee,
                'niveau'=>$request->niveau,
                "sexe" => $request->sexe,
            ]);
            $addStagiaire->save();
            return Response([
                "message" => "Stagiaire a été ajoutée avec succès",
            ]);
        }
        return Response([
            'message' => 'les données de Stagiaire incorrect',
        ]);
    }
    public function showStagiaire($id): Response
    {
        $filieres   = Filliere::all();
        $findStagiaire = Stagiaire::find($id);
        return Response([
            "stagiaire" => $findStagiaire,
            'filieres' => $filieres,
        ]);
    }
    public function updateStagiaire(Request $request, $id): Response
    {
        $searchStagiaire = Stagiaire::findOrFail($id);
        $valide = $request->validate([
            "cin" => "required|min:7|max:8",
            "nom" => "string|required",
            "prenom" => "string|required",
            "email" => "email|required",
            "password" => "string|required|min:6",
            'fill_id'=>"string|required",
            'numero'=>"string|required",
            'cef'=>"string|required",
            'group'=>"string|required",
            'annee'=>"string|required",
            'niveau'=>"string|required",
            "sexe" => "string|required",
        ]);
        if ($valide) {
            $searchStagiaire->update([
                "cin" => $request->cin,
                "nom" => $request->nom,
                "prenom" => $request->prenom,
                "email" => $request->email,
                "password" => Hash::make($request->password),
                "fill_id" => $request->fill_id,
                'numero'=>$request->numero,
                'cef'=>$request->cef,
                'group'=>$request->group,
                'annee'=>$request->annee,
                'niveau'=>$request->niveau,
                "sexe" => $request->sexe,
            ]);
        }
        return response([
            "success" => true,
            "message" => "Le Stagiaire a été modifié avec succès",
        ], 200);

        return response([
            "success" => false,
            "message" => "Erreur lors de la modification du Stagiaire",
        ], 400);
    }
    public function deleteStagiaire($id): Response
    {
        $findStagiaire = Stagiaire::find($id);
        if ($findStagiaire) {
            $findStagiaire->delete();
            return Response([
                'message' => "suprimmer success",
            ]);
        }
    }
    // ----------------------------------------------------- Module CRUD
    public function showModules(): Response
    {
        // 'nom',
        // 'code',
        // 'coefficient'
        $modules = Module::select("id", "code", "nom","coefficient")->get();
        return Response([
            'modules' => $modules,
        ]);
    }
    public function addModule(Request $request): Response
    {
        $valide = $request->validate([
            "code" => "required|string",
            "nom" => "string|required",
            "coefficient" => "string|required",
        ]);
        if ($valide) {
            $addModule = Module::create([
                "code" => $request->code,
                "nom" => $request->nom,
                "coefficient" => $request->coefficient,
            ]);
            $addModule->save();
            return Response([
                "message" => "le module a été ajoutée avec succès",
            ]);
        }
        return Response([
            'message' => 'les données de le module incorrect',
        ]);
    }
    public function showModule($id): Response
    {
        $findModule = module::find($id);
        return Response([
            "module" => $findModule,
        ]);
    }
    public function updateModule(Request $request, $id): Response
    {
        $searchModule = Module::findOrFail($id);
        $valide = $request->validate([
            "code" => "required|string",
            "nom" => "string|required",
            "coefficient" => "string|required",
        ]);
        if ($valide) {
            $searchModule->update([
                "code" => $request->code,
                "nom" => $request->nom,
                "coefficient" => $request->coefficient,
            ]);
        }
        return response([
            "success" => true,
            "message" => "Le Module a été modifié avec succès",
        ], 200);

        return response([
            "success" => false,
            "message"=>"Le Module a été des errors"
        ]);
    }
    public function deleteModule($id): Response
    {
        $findModule = Module::find($id);
        if ($findModule) {
            $findModule->delete();
            return Response([
                'message' => "suprimmer success",
            ]);
        }
    }
    // ----------------------------------------------------- FormatteurFillierModule CRUD

    public function showFormatteurFiliereModules(): Response
    {
        $filieres   = Filliere::all();
        $modules   = Module::all();
        $formatteurs   = Formatteur::all();
        $FormatteurFiliereModules = DB::table('formateur_filliere_modules')
        ->join('fillieres', 'formateur_filliere_modules.filliere_id', '=', 'fillieres.id')
        ->join('modules', 'formateur_filliere_modules.module_id', '=', 'modules.id')
        ->join('formatteurs', 'formateur_filliere_modules.formateur_id', '=', 'formatteurs.id')
        ->select('formateur_filliere_modules.id','fillieres.nom AS nomFiliere', 'modules.nom AS nomModule', 'formatteurs.nom AS nomFormatteur')
        ->get();;
        // $stagiaires = Stagiaire::with('filliere')->get();
        return Response([
            'FormatteurFiliereModules' => $FormatteurFiliereModules,
            'filieres' => $filieres,
            'modules' => $modules,
            'formatteurs' => $formatteurs,
        ]);
    }
    public function addFormatteurFiliereModule(Request $request): Response
    {
            // 'formateur_id',
            // 'filliere_id',
            // 'module_id',
        $valide = $request->validate([
            "formateur_id" => "required|string",
            "filliere_id" => "string|required",
            "module_id" => "string|required",
        ]);
        if ($valide) {
            $addFormatteurFiliereModule = Formateur_filliere_module::create([
                "formateur_id" => $request->formateur_id,
                "filliere_id" => $request->filliere_id,
                "module_id" => $request->module_id,
            ]);
            $addFormatteurFiliereModule->save();
            return Response([
                "message" => "Stagiaire a été ajoutée avec succès",
            ]);
        }
        return Response([
            'message' => 'les données de Stagiaire incorrect',
        ]);
    }
    public function updateFormatteurFiliereModule(Request $request, $id): Response
    {
        $FormatteurFiliereModule = Formateur_filliere_module::findOrFail($id);
        $valide = $request->validate([
            "formateur_id" => "required|string",
            "filliere_id" => "string|required",
            "module_id" => "string|required",
        ]);
        if ($valide) {
            $FormatteurFiliereModule->update([
                "formateur_id" => $request->formateur_id,
                "filliere_id" => $request->filliere_id,
                "module_id" => $request->module_id,
            ]);
        }
        return response([
            "success" => true,
            "message" => "Le Stagiaire a été modifié avec succès",
        ], 200);

        return response([
            "success" => false,
            "message" => "Erreur lors de la modification du Stagiaire",
        ], 400);
    }
    public function deleteFormatteurFiliereModule($id): Response
    {
        $findFormatteurFiliereModule = Formateur_filliere_module::find($id);
        if ($findFormatteurFiliereModule) {
            $findFormatteurFiliereModule->delete();
            return Response([
                'message' => "suprimmer success",
            ]);
        }
        return Response([
            'message' => $id,
        ]);
    }

    // ----------------------------------------------------- StgiaireModule  CRUD

    public function showStagiaireModules(): Response
    {
        $modules   = Module::all();
        $stagiaires   = Stagiaire::all();
        $stagiaireModules = DB::table('stagiaire_modules')
        ->join('modules', 'stagiaire_modules.module_id', '=', 'modules.id')
        ->join('stagiaires', 'stagiaire_modules.stagiaire_id', '=', 'stagiaires.id')
        ->select('stagiaire_modules.id','modules.nom AS nomModule', 'stagiaires.nom AS nomStagiaire')
        ->get();;
        // $stagiaires = Stagiaire::with('filliere')->get();
        return Response([
            'stagiaireModules' => $stagiaireModules,
            'modules' => $modules,
            'stagiaires' => $stagiaires,
        ]);
    }
    public function addStagiaireModule(Request $request): Response
    {

            // 'stagiaire_id',
            // 'module_id',
            $valide = $request->validate([
            "stagiaire_id" => "required|string",
            "module_id" => "string|required",
        ]);
        if ($valide) {
            $addStagiaireModule = Stagiaire_module::create([
                "stagiaire_id" => $request->stagiaire_id,
                "module_id" => $request->module_id,
            ]);
            $addStagiaireModule->save();
            return Response([
                "message" => "Stagiaire a été ajoutée avec succès",
            ]);
        }
        return Response([
            'message' => 'les données de Stagiaire incorrect',
        ]);
    }
    public function updateStagiaireModule(Request $request, $id): Response
    {
        $StagiaireModule = Stagiaire_module::findOrFail($id);
        $valide = $request->validate([
            "stagiaire_id" => "required|string",
            "module_id" => "string|required",
        ]);
        if ($valide) {
            $StagiaireModule->update([
                "stagiaire_id" => $request->stagiaire_id,
                "module_id" => $request->module_id,
            ]);
        }
        return response([
            "success" => true,
            "message" => "Le Stagiaire a été modifié avec succès",
        ], 200);

        return response([
            "success" => false,
            "message" => "Erreur lors de la modification du Stagiaire",
        ], 400);
    }
    public function deleteStagiaireModule($id): Response
    {
        $findStagiaireModule = Stagiaire_module::find($id);
        if ($findStagiaireModule) {
            $findStagiaireModule->delete();
            return Response([
                'message' => "suprimmer success",
            ]);
        }
        return Response([
            'message' => $id,
        ]);
    }
            // ----------------------------------------------------- Absence CRUD
    public function showAbsences(): Response
    {
        $stagiaires   = Stagiaire::all();
        $absences = DB::table("absences")
            ->join("stagiaires", "stagiaires.id", "=", "absences.stagiaire_id")
            ->select("absences.*", "stagiaires.nom","stagiaires.prenom")
            ->get();
        return Response([
            'stagiaires' => $stagiaires,
            'absences' => $absences,
        ]);
    }
    public function addAbsence(Request $request): Response
    {
        // 'stagiaire_id',
        // 'durrée',
        // 'date',
        // 'justifié'
        $valide = $request->validate([
            "stagiaire_id" => "required|string",
            "durrée" => "string|required",
            "date" => "date|required",
            "justifié" => "string|required",
        ]);
        if ($valide) {
            $addAbsence = Absence::create([
                "stagiaire_id" => $request->stagiaire_id,
                "durrée" => $request->durrée,
                "date" => $request->date,
                "justifié" => $request->justifié,
            ]);
            $addAbsence->save();
            return Response([
                "message" => "Absence a été ajoutée avec succès",
            ]);
        }
        return Response([
            'message' => 'les données de Stagiaire incorrect',
        ]);
    }
    public function showAbsence($id): Response
    {
        $stagiaires   = Stagiaire::all();
        $findAbsence = Absence::find($id)->first();
        return Response([
            "absence" => $findAbsence,
            "stagiaires" => $stagiaires,
        ]);
    }
    public function updateAbsence(Request $request, $id): Response
    {
        $searchAbsence = Absence::findOrFail($id);
        $valide = $request->validate([
            "stagiaire_id" => "required|string",
            "durrée" => "string|required",
            "date" => "date|required",
            "justifié" => "string|required",
        ]);
        if ($valide) {
            $searchAbsence->update([
                "stagiaire_id" => $request->stagiaire_id,
                "durrée" => $request->durrée,
                "date" => $request->date,
                "justifié" => $request->justifié,
            ]);
        }
        return response([
            "success" => true,
            "message" => "L'Absence a été modifié avec succès",
        ], 200);

        return response([
            "success" => false,
            "message" => "Erreur lors de la modification du Stagiaire",
        ], 400);
    }
    public function deleteAbsence($id): Response
    {
        $findAbsence = Absence::find($id);
        if ($findAbsence) {
            $findAbsence->delete();
            return Response([
                'message' => "suprimmer success",
            ]);
        }
    }
    // ----------------------------------------------------- Affiche Stagiaire Notes
    public function showStagiaireInfo() : Response
    {
        $stagiaires   = Stagiaire::all();
        return Response([
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


}
