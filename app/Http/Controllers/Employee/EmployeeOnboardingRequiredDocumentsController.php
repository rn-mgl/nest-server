<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\UserOnboardingRequiredDocuments;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

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

            $requiredDocumentsAttr = [
                "user_id" => Auth::id(),
                "required_document_id" => $attributes["onboarding_required_document_id"]
            ];

            $requirement = UserOnboardingRequiredDocuments::create($requiredDocumentsAttr);

            if ($request->hasFile("document")) {
                $file = $request->file("document");

                $disk = "user_required_documents";

                $uploaded = Storage::disk($disk)->put("/requirements", $file);

                $requirement->document()->create([
                    "disk" => $disk,
                    "path" => $uploaded,
                    "original_name" => $file->getClientOriginalName(),
                    "mime_type" => $file->getMimeType(),
                    "size" => $file->getSize(),
                ]);
            }

            return response()->json(["success" => $requirement]);

        } catch (\Throwable $th) {
            throw new Exception($th->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(UserOnboardingRequiredDocuments $requiredDocument)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(UserOnboardingRequiredDocuments $requiredDocument)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, UserOnboardingRequiredDocuments $requiredDocument)
    {
        try {

            $request->validate([
                "document" => ["required", "File"]
            ]);

            $uploaded = null;

            if ($request->hasFile("document")) {
                $file = $request->file("document");

                $disk = "user_required_documents";

                $uploaded = Storage::disk($disk)->put("/requirements", $file);

                $requiredDocument->document()->create([
                    "disk" => $disk,
                    "path" => $uploaded,
                    "original_name" => $file->getClientOriginalName(),
                    "mime_type" => $file->getMimeType(),
                    "size" => $file->getSize()
                ]);
            }

            $updated = $requiredDocument->update(["document" => $uploaded]);

            return response()->json(["success" => $updated]);

        } catch (\Throwable $th) {
            throw new Exception($th->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(UserOnboardingRequiredDocuments $requiredDocument)
    {
        try {
            $deleted = $requiredDocument->document()->delete();

            return response()->json(["success" => $deleted]);

        } catch (\Throwable $th) {
            throw new Exception($th->getMessage());
        }
    }
}
