<?php

namespace App\Http\Controllers\Onboarding;

use App\Http\Controllers\Controller;
use App\Models\UserOnboarding;
use App\Models\UserOnboardingPolicyAcknowledgement;
use App\Models\UserOnboardingRequiredDocuments;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class AssignedOnboardingController extends Controller
{

    ############
    # ASSIGNED #
    ############

    /**
     * Display a listing of the resource.
     */
    public function assignedIndex()
    {
        try {

            $user = Auth::id();

            $onboardings = UserOnboarding::with(["onboarding", "assignedBy"])->where("assigned_to", "=", $user)->get();

            return response()->json(["onboardings" => $onboardings]);
        } catch (\Throwable $th) {
            throw new Exception($th->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function assignedShow(UserOnboarding $userOnboarding)
    {
        try {

            // an employee onboarding is connected to the parent onboarding,
            // the parent onboarding has acknowledgement and document,
            // acknowledgement and document each has user compliance
            $userOnboarding->load(
                [
                    "onboarding" =>
                        [
                            "policyAcknowledgements" => [
                                "userAcknowledgement" => function ($query) use ($userOnboarding) {
                                    $query->where("acknowledged_by", "=", $userOnboarding->assigned_to);
                                }
                            ],
                            "requiredDocuments" => [
                                "userCompliance" => function ($query) use ($userOnboarding) {
                                    $query->where("complied_by", "=", $userOnboarding->assigned_to);
                                },
                                "userCompliance.document"
                            ]
                        ]
                ]
            );

            return response()->json(["onboarding" => $userOnboarding]);
        } catch (\Throwable $th) {
            throw new Exception($th->getMessage());
        }
    }


    /**
     * Store a newly created resource in storage.
     */
    public function policyAcknowledgementStore(Request $request)
    {
        try {

            $attributes = $request->validate([
                "policy_acknowledged" => ["required", "boolean"],
                "policy_acknowledgement_id" => ["required", "integer"]
            ]);

            $user = Auth::id();

            $acknowledgementAttributes = [
                "acknowledged_by" => $user,
                "policy_acknowledgement_id" => $attributes["policy_acknowledgement_id"],
                "acknowledged" => $attributes["policy_acknowledged"]
            ];

            $acknowledged = UserOnboardingPolicyAcknowledgement::create($acknowledgementAttributes);

            return response()->json(["success" => $acknowledged]);

        } catch (\Throwable $th) {
            throw new Exception($th->getMessage());
        }
    }


    /**
     * Store a newly created resource in storage.
     */
    public function requiredDocumentStore(Request $request)
    {
        try {

            $attributes = $request->validate([
                "document" => ["File", "required"],
                "onboarding_required_document_id" => ["required", "exists:onboarding_required_documents,id"]
            ]);

            $requirement = DB::transaction(function () use ($attributes, $request) {

                $requiredDocumentsAttr = [
                    "complied_by" => Auth::id(),
                    "required_document_id" => $attributes["onboarding_required_document_id"]
                ];

                $requirement = UserOnboardingRequiredDocuments::create($requiredDocumentsAttr);

                if ($request->hasFile("document")) {
                    $file = $request->file("document");

                    $disk = "user_required_document";

                    $uploaded = Storage::disk($disk)->put("/", $file);

                    $requirement->document()->create([
                        "disk" => $disk,
                        "path" => $uploaded,
                        "original_name" => $file->getClientOriginalName(),
                        "mime_type" => $file->getMimeType(),
                        "size" => $file->getSize(),
                    ]);
                }

                return $requirement;
            });

            return response()->json(["success" => $requirement]);

        } catch (\Throwable $th) {
            throw new Exception($th->getMessage());
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function requiredDocumentUpdate(Request $request, UserOnboardingRequiredDocuments $requiredDocument)
    {
        try {

            $request->validate([
                "document" => ["required", "File"]
            ]);

            $uploaded = null;

            if ($request->hasFile("document")) {
                $file = $request->file("document");

                $disk = "user_required_document";

                $uploaded = Storage::disk($disk)->put("/", $file);

                $requiredDocument->document()->create([
                    "disk" => $disk,
                    "path" => $uploaded,
                    "original_name" => $file->getClientOriginalName(),
                    "mime_type" => $file->getMimeType(),
                    "size" => $file->getSize()
                ]);
            }

            return response()->json(["success" => $uploaded]);

        } catch (\Throwable $th) {
            throw new Exception($th->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function requiredDocumentDestroy(UserOnboardingRequiredDocuments $requiredDocument)
    {
        try {
            $deleted = $requiredDocument->document()->delete();

            return response()->json(["success" => $deleted]);

        } catch (\Throwable $th) {
            throw new Exception($th->getMessage());
        }
    }


}
