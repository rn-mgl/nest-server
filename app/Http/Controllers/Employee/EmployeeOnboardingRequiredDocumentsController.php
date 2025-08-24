<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\UserOnboardingRequiredDocuments;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class EmployeeOnboardingRequiredDocumentsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {

            $attributes = $request->validate([
                "document" => ["File", "required"],
                "onboarding_required_document_id" => ["required", "exists:onboarding_required_documents,id"]
            ]);

            $uploaded = "";

            if ($request->hasFile("document")) {
                $uploaded = cloudinary()->uploadFile($request->file("document")->getRealPath(), ["folder" => "nest-uploads"])->getSecurePath();
            }

            $user = Auth::id();

            $requiredDocumentsAttr = [
                "user_id" => $user,
                "required_document_id" => $attributes["onboarding_required_document_id"],
                "document" => $uploaded
            ];

            $created = UserOnboardingRequiredDocuments::create($requiredDocumentsAttr);

            return response()->json(["success" => $created]);

        } catch (\Throwable $th) {
            throw new Exception($th->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(UserOnboardingRequiredDocuments $employeeOnboardingRequiredDocuments)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(UserOnboardingRequiredDocuments $employeeOnboardingRequiredDocuments)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, UserOnboardingRequiredDocuments $required_document)
    {
        try {

            $request->validate([
                "document" => ["required", "File"]
            ]);

            $uploaded = null;

            if ($request->hasFile("document")) {
                $uploaded = cloudinary()->uploadFile($request->file("document")->getRealPath(), ["folder" => "nest-uploads"])->getSecurePath();
            }

            $updated = $required_document->update(["document" => $uploaded]);

            return response()->json(["success" => $updated]);

        } catch (\Throwable $th) {
            throw new Exception($th->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(UserOnboardingRequiredDocuments $required_document)
    {
        try {
            $deleted = $required_document->update(["document" => null]);

            return response()->json(["success" => $deleted]);

        } catch (\Throwable $th) {
            throw new Exception($th->getMessage());
        }
    }
}
