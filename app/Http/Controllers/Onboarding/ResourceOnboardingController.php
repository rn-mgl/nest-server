<?php

namespace App\Http\Controllers\Onboarding;

use App\Http\Controllers\Controller;
use App\Models\Onboarding;
use App\Models\OnboardingPolicyAcknowledgement;
use App\Models\OnboardingRequiredDocument;
use App\Models\User;
use App\Models\UserOnboarding;
use App\Models\UserOnboardingPolicyAcknowledgement;
use App\Models\UserOnboardingRequiredDocuments;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ResourceOnboardingController extends Controller
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

    ############
    # RESOURCE #
    ############

    /**
     * Display a listing of the resource.
     */
    public function resourceIndex()
    {
        try {

            $onboardings = Onboarding::with(["createdBy"])->get();

            return response()->json(["onboardings" => $onboardings]);

        } catch (\Throwable $th) {
            throw new Exception($th->getMessage());
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function resourceStore(Request $request)
    {
        try {
            $attributes = $request->validate([
                'title' => ["string", "required"],
                'description' => ["string", "required"],
                'required_documents' => ["array", "required"],
                'required_documents.*.title' => ["string"],
                'required_documents.*.description' => ["string"],
                'policy_acknowledgements' => ["array", "required"],
                'policy_acknowledgements.*.title' => ["string"],
                'policy_acknowledgements.*.description' => ["string"],
            ]);

            $onboarding = DB::transaction(function () use ($attributes) {

                $hr = Auth::id();

                $onboardingAttributes = [
                    'title' => $attributes['title'],
                    'description' => $attributes['description'],
                    'created_by' => $hr
                ];

                $onboarding = Onboarding::create($onboardingAttributes);

                $documentsData = collect($attributes["required_documents"])->map(function ($document) use ($onboarding, $hr) {
                    return [
                        "title" => $document["title"],
                        "description" => $document["description"],
                        "onboarding_id" => $onboarding->id,
                        "created_by" => $hr
                    ];
                });

                OnboardingRequiredDocument::insert($documentsData->all());

                $policiesData = collect($attributes["policy_acknowledgements"])->map(function ($policy) use ($onboarding, $hr) {
                    return [
                        "title" => $policy["title"],
                        "description" => $policy["description"],
                        "onboarding_id" => $onboarding->id,
                        "created_by" => $hr
                    ];
                });

                OnboardingPolicyAcknowledgement::insert($policiesData->all());

                return $onboarding;
            });

            return response()->json(["success" => $onboarding]);

        } catch (\Throwable $th) {
            throw new Exception($th->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function resourceShow(Onboarding $onboarding)
    {
        try {
            return response()->json(["onboarding" => $onboarding->load(["requiredDocuments", "policyAcknowledgements"])]);
        } catch (\Throwable $th) {
            throw new Exception($th->getMessage());
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function resourceUpdate(Request $request, Onboarding $onboarding)
    {

        try {
            $attributes = $request->validate([
                "title" => ["required", "string"],
                "description" => ["required", "string"],
                "required_documents" => ["array", "required"],
                "required_documents.*.title" => ["string"],
                "required_documents.*.description" => ["string"],
                "required_documents.*.id" => ["integer", "nullable"],
                "policy_acknowledgements" => ["array", "required"],
                "policy_acknowledgements.*.title" => ["string"],
                "policy_acknowledgements.*.description" => ["string"],
                "policy_acknowledgements.*.id" => ["integer", "nullable"],
                "documents_to_delete" => ["array"],
                "documents_to_delete.*" => ["integer", "nullable"],
                "policies_to_delete" => ["array"],
                "policies_to_delete.*" => ["integer", "nullable"]
            ]);

            $updated = DB::transaction(function () use ($attributes, $onboarding) {
                $documents = collect($attributes["required_documents"] ?? []);
                $policies = collect($attributes["policy_acknowledgements"] ?? []);
                $documentsToDelete = $attributes["documents_to_delete"];
                $policiesToDelete = $attributes["policies_to_delete"];

                $documentsData = $documents->map(function ($document) use ($onboarding) {
                    return [
                        "id" => $document["id"] ?? null,
                        "onboarding_id" => $onboarding->id,
                        "created_by" => Auth::id(),
                        "title" => $document["title"],
                        "description" => $document["description"],
                    ];
                });

                OnboardingRequiredDocument::upsert($documentsData->all(), ["id"], ["title", "description"]);

                $policiesData = $policies->map(function ($policy) use ($onboarding) {
                    return [
                        "id" => $policy["id"] ?? null,
                        "onboarding_id" => $onboarding->id,
                        "created_by" => Auth::id(),
                        "title" => $policy["title"],
                        "description" => $policy["description"]
                    ];
                });

                OnboardingPolicyAcknowledgement::upsert($policiesData->all(), ["id"], ["title", "description"]);

                OnboardingRequiredDocument::whereIn("id", $documentsToDelete)->delete();

                OnboardingPolicyAcknowledgement::whereIn("id", $policiesToDelete)->delete();

                $updatedOnboarding = $onboarding->update([
                    'title' => $attributes['title'],
                    'description' => $attributes['description'],
                    "created_by" => Auth::id()
                ]);

                return $updatedOnboarding;
            });

            return response()->json(["success" => $updated]);

        } catch (\Throwable $th) {
            throw new Exception($th->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function resourceDestroy(Onboarding $onboarding)
    {
        try {

            $deleted = DB::transaction(function () use ($onboarding) {
                $deletedOnboarding = $onboarding->delete();

                $affectedRequiredDocuments = OnboardingRequiredDocument::where("onboarding_id", "=", $onboarding->id)->get()->pluck("id");

                $affectedPolicyAcknowledgements = OnboardingPolicyAcknowledgement::where("onboarding_id", "=", $onboarding->id)->get()->pluck("id");

                $deletedRequiredDocuments = OnboardingRequiredDocument::whereIn("id", $affectedRequiredDocuments)->delete();

                $deletedPolicyAcknowledgements = OnboardingPolicyAcknowledgement::whereIn("id", $affectedPolicyAcknowledgements)
                    ->delete();

                $deletedAssignedOnboarding = UserOnboarding::where("onboarding_id", "=", $onboarding->id)->delete();

                $deletedUserDocuments = UserOnboardingRequiredDocuments::whereIn("required_document_id", $affectedRequiredDocuments)->delete();

                $deletedUserAcknowledgements = UserOnboardingPolicyAcknowledgement::whereIn("policy_acknowledgement_id", $affectedPolicyAcknowledgements)->delete();

                return $deletedOnboarding || $deletedRequiredDocuments || $deletedPolicyAcknowledgements || $deletedAssignedOnboarding || $deletedUserDocuments || $deletedUserAcknowledgements;
            });

            return response()->json(["success" => $deleted]);
        } catch (\Throwable $th) {
            throw new Exception($th->getMessage());
        }
    }
}
