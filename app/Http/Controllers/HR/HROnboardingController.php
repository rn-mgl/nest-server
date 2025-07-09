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
                                "o.description",
                                "u.first_name",
                                "u.last_name",
                                "u.email",
                                "u.id as user_id"
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
                'required_documents.*.title' => ["string"],
                'required_documents.*.description' => ["string"],
                'policy_acknowledgements' => ["array", "required"],
                'policy_acknowledgements.*.title' => ["string"],
                'policy_acknowledgements.*.description' => ["string"],
            ]);

            $onboardingAttributes = [
                'title' => $attributes['title'],
                'description' => $attributes['description'],
                'created_by' => Auth::id()
            ];

            $onboarding = Onboarding::create($onboardingAttributes);

            foreach ($attributes["required_documents"] as $req) {
                $documentsAttributes = [
                    'title' => $req["title"],
                    'description' => $req["description"],
                    'onboarding_id' => $onboarding->id
                ];
                OnboardingRequiredDocuments::create($documentsAttributes);
            }

            foreach ($attributes["policy_acknowledgements"] as $ack) {
                $policyAttributes = [
                    'title' => $ack["title"],
                    'description' => $ack["description"],
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
                                        'title',
                                        'description'
                                    ])
                                    ->get();


            $policy_acknowledgements = DB::table('onboarding_policy_acknowledgements as opa')
                                    ->where("onboarding_id", "=", $onboarding->id)
                                    ->where('is_deleted', "=", false)
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

            foreach($documents as $requirement) {
                $id = $requirement['onboarding_required_document_id'] ?? null;

                logger($requirement);

                $documentAttributes = [
                    'onboarding_id' => $onboarding->id,
                    'title' => $requirement['title'],
                    'description' => $requirement['description'],
                ];

                if ($id) {
                    $document = OnboardingRequiredDocuments::find($id);
                    if ($document) {
                        $document->update($documentAttributes);
                    }
                } else {
                    OnboardingRequiredDocuments::create($documentAttributes);
                }
            }

            foreach($policies as $acknowledgement) {
                $id = $acknowledgement['onboarding_policy_acknowledgement_id'] ?? null;

                $acknowledgementAttributes = [
                    "onboarding_id" => $onboarding->id,
                    "title" => $acknowledgement['title'],
                    "description" => $acknowledgement['description'],
                ];

                if ($id) {
                    $acknowledgement = OnboardingPolicyAcknowledgements::find($id);
                    if ($acknowledgement) {
                        $acknowledgement->update($acknowledgementAttributes);
                    }
                } else {
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
    public function destroy(string $onboarding)
    {
        try {
            $deletedOnboarding = DB::table("onboardings")
                                ->where("id", "=", $onboarding)
                                ->update(["is_deleted" => true]);

            $deletedRequiredDocuments = DB::table("onboarding_required_documents")
                                ->where("onboarding_id", "=", $onboarding)
                                ->update(["is_deleted" => true]);

            $deletedPolicyAcknowledgements = DB::table("onboarding_policy_acknowledgements")
                                            ->where("onboarding_id", "=", $onboarding)
                                            ->update(["is_deleted" => true]);

            return response()->json(["success" => $deletedOnboarding]);
        } catch (\Throwable $th) {
            throw new \Exception($th->getMessage());
        }
    }
}
