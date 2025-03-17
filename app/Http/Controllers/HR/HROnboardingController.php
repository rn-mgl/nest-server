<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Http\Requests\SearchRequest;
use App\Http\Requests\SortRequest;
use App\Models\Onboarding;
use App\Models\OnboardingPolicyAcknowledgements;
use App\Models\OnboardingRequiredDocuments;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class HROnboardingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(SearchRequest $searchRequest, SortRequest $sortRequest)
    {
        try {

            $searcAttributes = $searchRequest->validated();
            $sortAttributes = $sortRequest->validated();
            $attributes = array_merge($searcAttributes, $sortAttributes);

            $isAsc = filter_var($attributes["isAsc"], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

            $sortType = $isAsc ? "ASC" : "DESC";
            $sortKey = $attributes["sortKey"];
            $searchValue = $attributes["searchValue"] ?? "";
            $searchKey = $attributes["searchKey"];

            $onboardings = DB::table("onboardings as o")
                            ->join("users as u",  function(JoinClause $join) {
                                $join->on("u.id", "=", "o.created_by")
                                ->where("u.is_deleted", "=", false);
                            })
                            ->where("o.is_deleted", "=", false)
                            ->whereLike($searchKey, "%{$searchValue}%")
                            ->orderBy("o.{$sortKey}", $sortType)
                            ->select([
                                "o.id as onboarding_id",
                                "o.created_by",
                                "o.title",
                                "o.description"
                            ])
                            ->get();

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
                'required_documents.*.document' => ["string"],
                'policy_acknowledgements' => ["array", "required"],
                'policy_acknowledgements.*.policy' => ["string"],
            ]);

            $onboardingAttributes = [
                'title' => $attributes['title'],
                'description' => $attributes['description'],
                'created_by' => Auth::id()
            ];

            $onboarding = Onboarding::create($onboardingAttributes);

            foreach ($attributes["required_documents"] as $reqs) {
                $documentsAttributes = [
                    'document' => $reqs["document"],
                    'onboarding_id' => $onboarding->id
                ];
                OnboardingRequiredDocuments::create($documentsAttributes);
            }

            foreach ($attributes["policy_acknowledgements"] as $acks) {
                $policyAttributes = [
                    'policy' => $acks["policy"],
                    'onboarding_id' => $onboarding->id
                ];
                OnboardingPolicyAcknowledgements::create($policyAttributes);
            }


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

            $required_documents = DB::table('onboarding_required_documents as ord')
                                    ->where("onboarding_id", "=", $onboarding->id)
                                    ->where('is_deleted', "=", false)
                                    ->select([
                                        'id as onboarding_required_documents_id',
                                        'document'
                                    ])
                                    ->get();


            $policy_acknowledgements = DB::table('onboarding_policy_acknowledgements as opa')
                                    ->where("onboarding_id", "=", $onboarding->id)
                                    ->where('is_deleted', "=", false)
                                    ->select([
                                        'id as onboarding_policy_acknowledgements_id',
                                        'policy'
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
                "required_documents.*.document" => ["string"],
                "required_documents.*.onboarding_required_documents_id" => ["integer", "nullable"],
                "policy_acknowledgements" => ["array", "required"],
                "policy_acknowledgements.*.policy" => ["string"],
                "policy_acknowledgements.*.onboarding_policy_acknowledgements_id" => ["integer", "nullable"],
                "documentsToDelete" => ["array"],
                "documentsToDelete.*" => ["integer", "nullable"],
                "policiesToDelete" => ["array"],
                "policiesToDelete.*" => ["integer", "nullable"]
            ]);

            $documents = $attributes["required_documents"];
            $policies = $attributes["policy_acknowledgements"];
            $documentsToDelete = $attributes["documentsToDelete"];
            $policiesToDelete = $attributes["policiesToDelete"];

            foreach($documents as $requirement) {
                $id = $requirement['onboarding_required_documents_id'] ?? null;

                logger($requirement);

                if ($id) {
                    $document = OnboardingRequiredDocuments::find($id);
                    $documentAttributes = [
                        'onboarding_id' => $onboarding->id,
                        'document' => $requirement['document']
                    ];
                    if ($document) {
                        $document->update($documentAttributes);
                    }
                } else {
                    $documentAttributes = [
                        'onboarding_id' => $onboarding->id,
                        'document' => $requirement['document']
                    ];
                    OnboardingRequiredDocuments::create($documentAttributes);
                }
            }

            foreach($policies as $acknowledgement) {
                $id = $acknowledgement['onboarding_policy_acknowledgements_id'] ?? null;

                if ($id) {
                    $acknowledgement = OnboardingPolicyAcknowledgements::find($id);
                    $acknowledgementAttributes = [
                        "onboarding_id" => $onboarding->id,
                        "policy" => $acknowledgement['policy']
                    ];
                    if ($acknowledgement) {
                        $acknowledgement->update($acknowledgementAttributes);
                    }
                } else {
                    $acknowledgementAttributes = [
                        "onboarding_id" => $onboarding->id,
                        "policy" => $acknowledgement['policy']
                    ];
                    OnboardingPolicyAcknowledgements::create($acknowledgementAttributes);
                }
            }

            foreach($documentsToDelete as $toDelete) {
                $document = OnboardingRequiredDocuments::find($toDelete);

                if ($document) {
                    $document->update(["is_deleted" => true]);
                }
            }

            foreach($policiesToDelete as $toDelete) {
                $acknowledgement = OnboardingPolicyAcknowledgements::find($toDelete);

                if ($acknowledgement) {
                    $acknowledgement->update(["is_deleted" => true]);
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
    public function destroy(Onboarding $onboarding)
    {
        try {
            $deletedOnboarding = $onboarding->update(["is_deleted" => true]);

            return response()->json(["success" => $deletedOnboarding]);
        } catch (\Throwable $th) {
            throw new \Exception($th->getMessage());
        }
    }
}
