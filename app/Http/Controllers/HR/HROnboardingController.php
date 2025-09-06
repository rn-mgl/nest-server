<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Http\Requests\SearchRequest;
use App\Http\Requests\SortRequest;
use App\Models\Onboarding;
use App\Models\OnboardingPolicyAcknowledgement;
use App\Models\OnboardingRequiredDocument;
use App\Models\UserOnboarding;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class HROnboardingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {

            $user = Auth::id();

            $onboardings = Onboarding::with(["createdBy"])->get();

            return response()->json(["onboardings" => $onboardings]);

        } catch (\Throwable $th) {
            throw new \Exception($th->getMessage());
        }
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
                $onboardingAttributes = [
                    'title' => $attributes['title'],
                    'description' => $attributes['description'],
                    'created_by' => Auth::id()
                ];

                $onboarding = Onboarding::create($onboardingAttributes);

                $documentsData = collect($attributes["required_documents"])->map(function ($document) use ($onboarding) {
                    return [
                        "title" => $document["title"],
                        "description" => $document["description"],
                        "onboarding_id" => $onboarding->id
                    ];
                });

                OnboardingRequiredDocument::insert($documentsData->all());

                $policiesData = collect($attributes["policy_acknowledgements"])->map(function ($policy) use ($onboarding) {
                    return [
                        "title" => $policy["title"],
                        "description" => $policy["description"],
                        "onboarding_id" => $onboarding->id
                    ];
                });

                OnboardingPolicyAcknowledgement::insert($policiesData->all());

                return $onboarding;
            });

            return response()->json(["success" => $onboarding]);

        } catch (\Throwable $th) {
            throw new \Exception($th->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Onboarding $onboarding)
    {
        try {
            return response()->json(["onboarding" => $onboarding->load(["requiredDocuments", "policyAcknowledgements"])]);
        } catch (\Throwable $th) {
            throw new \Exception($th->getMessage());
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Onboarding $onboarding)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Onboarding $onboarding)
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
                    'description' => $attributes['description']
                ]);

                return $updatedOnboarding;
            });

            return response()->json(["success" => $updated]);

        } catch (\Throwable $th) {
            throw new \Exception($th->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Onboarding $onboarding)
    {
        try {

            $deletedOnboarding = $onboarding->delete();

            $deletedRequiredDocuments = OnboardingRequiredDocument::where("onboarding_id", "=", $onboarding->id)
                ->delete();

            $deletedPolicyAcknowledgements = OnboardingPolicyAcknowledgement::where("onboarding_id", "=", $onboarding->id)
                ->delete();

            return response()->json(["success" => $deletedOnboarding || $deletedRequiredDocuments || $deletedPolicyAcknowledgements]);
        } catch (\Throwable $th) {
            throw new \Exception($th->getMessage());
        }
    }
}
