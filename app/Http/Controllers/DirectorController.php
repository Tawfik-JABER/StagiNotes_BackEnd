<?php

namespace App\Http\Controllers;

use App\Models\Director;
use App\Models\Filliere;
use App\Models\Formatteur;
use App\Models\Module;
use App\Models\Tutteur;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Laravel\Sanctum\PersonalAccessToken;

class DirectorController extends Controller
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
    // _________________________________________________________________ Reseting Password
    public function resetPassword(Request $request)
    {
        $email = $request->input('email');
        $director = Director::where('email_verify', $email)->first();

        if (!$director) {
            return response()->json(['message' => 'Invalid email'], 404);
        }

        // Generate a new password (for demonstration purposes)
        $newPassword = substr(md5(microtime()), 0, 8);
        $director->password = Hash::make($newPassword);
        $director->save();
        // $director->update([
        //     "password" => Hash::make($newPassword),
        // ]);

        // Send the new password to the director's email
        Mail::raw("Your new password is: $newPassword", function ($message) use ($email) {
            $message->to($email)->subject('Password Reset');
        });

        return response()->json(['message' => 'Password reset successful']);
    }
    // _________________________________________________________________ Email Verify
    public function emailVerify(Request $request)
    {
        $userId = Auth::user()->id;
        $emailVerify = $request->input('email_verify');

        $director = Director::where('id', $userId)->first();

        if ($director) {
            $director->email_verify = $emailVerify;
            $director->save();
        } else {
            Director::create([
                'id' => $userId,
                'email_verify' => $emailVerify,
            ]);
        }

        return response()->json(['message' => 'Email verification updated successfully']);
    }
    // _________________________________________________________________ Login Process
    public function login(Request $request): Response
    {
        $director = $request->validate([
            'email' => 'required|string',
            'password' => 'required|string|min:6',
        ]);
        if (Auth::guard('director')->attempt($director)) {
            $director = Director::where("email", $request->email)->first();
            if ($director && Hash::check($request->password, $director->password)) {
                $device = $request->userAgent();
                $token = $director->createToken($device)->plainTextToken;
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
    // _________________________________________________________________ Logout Process
    public function logout($token = null): Response
    {
        $director = Auth::guard('sanctum')->user();
        if (null == $token) {
            $director->currentAccessToken()->delete();
        }
        $personaleToken = PersonalAccessToken::findToken($token);
        if ($personaleToken && $director->id === $personaleToken->tokenable_id && get_class($director) === $personaleToken->tokenable_type) {
            $personaleToken->delete();
        }
        return Response([
            'message' => 'logout successful',
        ]);
    }

    // _________________________________________________________________ Formatteur

    public function showFormatteurs(): Response
    {
        $formatteurs = Formatteur::select("id", "cin", "nom", "prenom", "email", "sexe")->get();
        return Response([
            'formatteurs' => $formatteurs,
        ]);
    }
    public function addFormatteur(Request $request): Response
    {
        $valide = $request->validate([
            "cin" => "required|min:7|max:8",
            "nom" => "string|required",
            "prenom" => "string|required",
            "email" => "email|required",
            "password" => "string|required|min:6",
            "sexe" => "string|required",
        ]);
        if ($valide) {
            $addFormatteur = Formatteur::create([
                "cin" => $request->cin,
                "nom" => $request->nom,
                "prenom" => $request->prenom,
                "email" => $request->email,
                "password" => Hash::make($request->password),
                "sexe" => $request->sexe,
            ]);
            $addFormatteur->save();
            return Response([
                "message" => "formatteur a été ajoutée avec succès",
            ]);
        }
        return Response([
            'message' => 'les données de formmateur incorrect',
        ]);
    }
    public function showFormatteur($id): Response
    {
        $findFormatteur = Formatteur::find($id);
        return Response([
            "formatteur" => $findFormatteur,
        ]);
    }
    public function updateFormatteur(Request $request, $id): Response
    {
        $searchFormtteur = Formatteur::findOrFail($id);
        $valide = $request->validate([
            "cin" => "required|min:7|max:8",
            "nom" => "string|required",
            "prenom" => "string|required",
            "email" => "email|required",
            "password" => "string|required|min:6",
            "sexe" => "string|required",
        ]);
        if ($valide) {
            $searchFormtteur->update([
                "cin" => $request->cin,
                "nom" => $request->nom,
                "prenom" => $request->prenom,
                "email" => $request->email,
                "password" => Hash::make($request->password),
                "sexe" => $request->sexe,
            ]);
        }
        return response([
            "success" => true,
            "message" => "Le formateur a été modifié avec succès",
        ], 200);

        return response([
            "success" => false,
            "message"=>"Le formateur a été des errors"
        ]);
    }
    public function deleteFormatteur($id): Response
    {
        $findFormatteur = Formatteur::find($id);
        if ($findFormatteur) {
            $findFormatteur->delete();
            return Response([
                'message' => "suprimmer success",
            ]);
        }
    }

    // _________________________________________________________________ Tutteur

    public function showTutteurs(): Response
    {
        $tutteurs = Tutteur::select("id", "cin", "nom", "prenom", "email", "sexe")->get();
        return Response([
            'tutteurs' => $tutteurs,
        ]);
    }
    public function addTutteur(Request $request): Response
    {
        $valide = $request->validate([
            "cin" => "required|min:7|max:8",
            "nom" => "string|required",
            "prenom" => "string|required",
            "email" => "email|required",
            "password" => "string|required|min:6",
            "sexe" => "string|required",
        ]);
        if ($valide) {
            $addTutteur = Tutteur::create([
                "cin" => $request->cin,
                "nom" => $request->nom,
                "prenom" => $request->prenom,
                "email" => $request->email,
                "password" => Hash::make($request->password),
                "sexe" => $request->sexe,
            ]);
            $addTutteur->save();
            return Response([
                "message" => "formatteur a été ajoutée avec succès",
            ]);
        }
        return Response([
            'message' => 'les données de formmateur incorrect',
        ]);
    }
    public function showTutteur($id): Response
    {
        $findTutteur = Tutteur::find($id);
        return Response([
            "tutteur" => $findTutteur,
        ]);
    }
    public function updateTutteur(Request $request, $id): Response
    {
        $searchTutteur = Tutteur::findOrFail($id);
        $valide = $request->validate([
            "cin" => "required|min:7|max:8",
            "nom" => "string|required",
            "prenom" => "string|required",
            "email" => "email|required",
            "password" => "string|required|min:6",
            "sexe" => "string|required",
        ]);
        if ($valide) {
            $searchTutteur->update([
                "cin" => $request->cin,
                "nom" => $request->nom,
                "prenom" => $request->prenom,
                "email" => $request->email,
                "password" => Hash::make($request->password),
                "sexe" => $request->sexe,
            ]);
        }
        return response([
            "success" => true,
            "message" => "Le formateur a été modifié avec succès",
        ], 200);

        return response([
            "success" => false,
            "message" => "Erreur lors de la modification du formateur",
        ], 400);
    }
    public function deleteTutteur($id): Response
    {
        $findTutteur = Tutteur::find($id);
        if ($findTutteur) {
            $findTutteur->delete();
            return Response([
                'message' => "suprimmer success",
            ]);
        }
    }
    // _________________________________________________________________ Filiere

    public function showFilieres(): Response
    {
        $filieres = Filliere::select("id", "nom", "description")->get();
        return Response([
            'filieres' => $filieres,
        ]);
    }
    public function addFiliere(Request $request): Response
    {
        $valide = $request->validate([
            "nom" => "required|string",
            "description" => "required|string",
        ]);
        if ($valide) {
            $addFiliere = Filliere::create([
                "nom" => $request->nom,
                "description" => $request->description,
            ]);
            $addFiliere->save();
            return Response([
                "message" => "filiere a été ajoutée avec succès",
            ]);
        }
        return Response([
            'message' => 'les données de filiere incorrect',
        ]);
    }
    public function showFiliere($id): Response
    {
        $findFilieres = Filliere::find($id);
        return Response([
            "filiere" => $findFilieres,
        ]);
    }
    public function updateFiliere(Request $request, $id): Response
    {
        $searchFiliere = Filliere::findOrFail($id);
        $valide = $request->validate([
            "nom" => "required|string",
            "description" => "required|string",
        ]);
        if ($valide) {
            $searchFiliere->update([
                "nom" => $request->nom,
                "description" => $request->description,
            ]);
        }
        return response([
            "success" => true,
            "message" => "Le formateur a été modifié avec succès",
        ], 200);

        return response([
            "success" => false,
            "message" => "Erreur lors de la modification du formateur",
        ], 400);
    }
    public function deleteFiliere($id): Response
    {
        $findFiliere = Filliere::find($id);
        if ($findFiliere) {
            $findFiliere->delete();
            return Response([
                'message' => "suprimmer success",
            ]);
        }
    }

    // _________________________________________________________________ Stagiaires

    public function showStagiaires(): Response
    {
        $stagiaires = DB::table("stagiaires")
            ->join("fillieres", "stagiaires.fill_id", "=", "fillieres.id")
            ->select("fillieres.nom AS nomFill", "stagiaires.*")
            ->get();
        // $stagiaires = Stagiaire::with('filliere')->get();
        return Response([
            'stagiaires' => $stagiaires,
        ]);
    }

    // _________________________________________________________________ Modules

    public function showModules(): Response
    {
        $modules = Module::select("id", "nom", "code", "coefficient")->get();
        return Response([
            'modules' => $modules,
        ]);
    }

    // _________________________________________________________________ Change Password

    public function changePassword(Request $request)
    {
        $validate = $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|string|min:6',
            'password_confirmation' => 'required|same:new_password',
        ]);

        $user = Auth::user()->id;
        $director = Director::find($user);
        if (!$validate || !Hash::check($request->input('current_password'), $director->password)) {
            return response()->json(['error' => 'Current password is incorrect'], 401);
        }
        $director->update([
            'password' => Hash::make($request->input('new_password')),
        ]);

        return response()->json(['message' => 'Password changed successfully']);
    }
}
