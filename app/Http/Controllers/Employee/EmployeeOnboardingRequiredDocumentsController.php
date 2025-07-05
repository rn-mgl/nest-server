<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\EmployeeOnboardingRequiredDocuments;
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
                $uploaded = cloudinary()->uploadFile($request->file("document")->getRealPath(), ["folders" => "nest-uploads"])->getSecurePath();
            }

            $user = Auth::guard("base")->id();

            $requiredDocumentsAttr = [
                "employee_id" => $user,
                "required_document_id" => $attributes["onboarding_required_document_id"],
                "document" => $uploaded
            ];

            $created = EmployeeOnboardingRequiredDocuments::create($requiredDocumentsAttr);

            return response()->json(["success" => $created]);

        } catch (\Throwable $th) {
            throw new Exception($th->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(EmployeeOnboardingRequiredDocuments $employeeOnboardingRequiredDocuments)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(EmployeeOnboardingRequiredDocuments $employeeOnboardingRequiredDocuments)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $employeeOnboardingRequiredDocumentId)
    {
        try {

            $request->validate([
                "document" => ["required", "File"]
            ]);

            $uploaded = null;

            if ($request->hasFile("document")) {
                $uploaded = cloudinary()->uploadFile($request->file("document")->getRealPath(), ["folders" => "nest-uploads"])->getSecurePath();
            }

            $updated = DB::table("employee_onboarding_required_documents")
                        ->where("id", "=", $employeeOnboardingRequiredDocumentId)
                        ->update(["document" => $uploaded]);

            return response()->json(["success" => $updated]);

        } catch (\Throwable $th) {
            throw new Exception($th->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($employeeOnboardingRequiredDocumentId)
    {
        try {
            $deleted = DB::table("employee_onboarding_required_documents")
                        ->where("id", "=", $employeeOnboardingRequiredDocumentId)
                        ->update(["document" => null]);

            return response()->json(["success" => $deleted]);

        } catch (\Throwable $th) {
            throw new Exception($th->getMessage());
        }
    }
}
