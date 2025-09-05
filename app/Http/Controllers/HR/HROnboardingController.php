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

            $required_documents = OnboardingRequiredDocument::where("onboarding_id", "=", $onboarding->id)
                ->select([
                    'id as onboarding_required_documents_id',
                    'title',
                    'description'
                ])
                ->get();


            $policy_acknowledgements = OnboardingPolicyAcknowledgement::where("onboarding_id", "=", $onboarding->id)
                ->select([
                    'id as onboarding_policy_acknowledgements_id',
                    'title',
                    'description'
                ])
                ->get();
            $onboarding->required_documents = $required_documents;
            $onboarding->policy_acknowledgements = $policy_acknowledgements;

            return response()->json(["onboarding" => $onboarding]);
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
        logger($request);
        try {
            $attributes = $request->validate([
                "title" => ["required", "string"],
                "description" => ["required", "string"],
                "required_documents" => ["array", "required"],
                "required_documents.*.title" => ["string"],
                "required_documents.*.description" => ["string"],
                "required_documents.*.onboarding_required_document_id" => ["integer", "nullable"],
                "policy_acknowledgements" => ["array", "required"],
                "policy_acknowledgements.*.title" => ["string"],
                "policy_acknowledgements.*.description" => ["string"],
                "policy_acknowledgements.*.onboarding_policy_acknowledgement_id" => ["integer", "nullable"],
                "documentsToDelete" => ["array"],
                "documentsToDelete.*" => ["integer", "nullable"],
                "policiesToDelete" => ["array"],
                "policiesToDelete.*" => ["integer", "nullable"]
            ]);

            $documents = $attributes["required_documents"];
            $policies = $attributes["policy_acknowledgements"];
            $documentsToDelete = $attributes["documentsToDelete"];
            $policiesToDelete = $attributes["policiesToDelete"];

            foreach ($documents as $requirement) {
                $id = $requirement['onboarding_required_document_id'] ?? null;

                logger($requirement);

                $documentAttributes = [
                    'onboarding_id' => $onboarding->id,
                    'title' => $requirement['title'],
                    'description' => $requirement['description'],
                ];

                if ($id) {
                    $document = OnboardingRequiredDocument::find($id);
                    if ($document) {
                        $document->update($documentAttributes);
                    }
                } else {
                    OnboardingRequiredDocument::create($documentAttributes);
                }
            }

            foreach ($policies as $acknowledgement) {
                $id = $acknowledgement['onboarding_policy_acknowledgement_id'] ?? null;

                $acknowledgementAttributes = [
                    "onboarding_id" => $onboarding->id,
                    "title" => $acknowledgement['title'],
                    "description" => $acknowledgement['description'],
                ];

                if ($id) {
                    $acknowledgement = OnboardingPolicyAcknowledgement::find($id);
                    if ($acknowledgement) {
                        $acknowledgement->update($acknowledgementAttributes);
                    }
                } else {
                    OnboardingPolicyAcknowledgement::create($acknowledgementAttributes);
                }
            }

            foreach ($documentsToDelete as $toDelete) {
                $document = OnboardingRequiredDocument::find($toDelete);

                if ($document) {
                    $document->delete();
                }
            }

            foreach ($policiesToDelete as $toDelete) {
                $acknowledgement = OnboardingPolicyAcknowledgement::find($toDelete);

                if ($acknowledgement) {
                    $acknowledgement->delete();
                }
            }

            $onboardingAttributes = [
                'title' => $attributes['title'],
                'description' => $attributes['description']
            ];

            $updatedOnboarding = $onboarding->update($onboardingAttributes);

            return response()->json(["success" => $updatedOnboarding]);

        } catch (\Throwable $th) {
            throw new \Exception($th->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $onboarding)
    {
        try {
            $deletedOnboarding = Onboarding::where("id", "=", $onboarding)->delete();
            $deletedRequiredDocuments = OnboardingRequiredDocument::where("onboarding_id", "=", $onboarding)->delete();
            $deletedPolicyAcknowledgements = OnboardingPolicyAcknowledgement::where("onboarding_id", "=", $onboarding)->delete();

            return response()->json(["success" => $deletedOnboarding || $deletedRequiredDocuments || $deletedPolicyAcknowledgements]);
        } catch (\Throwable $th) {
            throw new \Exception($th->getMessage());
        }
    }
}
