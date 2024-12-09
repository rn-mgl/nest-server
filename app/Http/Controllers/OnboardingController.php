<?php

namespace App\Http\Controllers;

use App\Models\Onboarding;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OnboardingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {

            $onboardings = DB::table("onboardings as o")
                            ->join("users as u",  function(JoinClause $join) {
                                $join->on("u.id", "=", "o.created_by")
                                ->where("u.is_deleted", "=", false);
                            })
                            ->where("o.is_deleted", "=", false)
                            ->select([
                                "o.id as onboarding_id",
                                "o.created_by",
                                "o.title",
                                "o.description",
                                "o.required_documents",
                                "o.policy_acknowledgements",
                            ])
                            ->get();

            foreach ($onboardings as $onboarding) {
                $onboarding->required_documents = explode("\n", trim($onboarding->required_documents));
                $onboarding->policy_acknowledgements = explode("\n", trim($onboarding->policy_acknowledgements));
            }

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
                'required_documents.*' => ["string"],
                'policy_acknowledgements' => ["array", "required"],
                'policy_acknowledgements.*' => ["string"],
            ]);

            $requiredDocuments = "";

            foreach ($attributes["required_documents"] as $reqs) {
                $requiredDocuments .= "$reqs\n";
            }

            $policyAcknowledgements = "";

            foreach ($attributes["policy_acknowledgements"] as $acks) {
                $policyAcknowledgements .= "$acks\n";
            }

            $attributes['created_by'] = Auth::id();
            $attributes['required_documents'] = $requiredDocuments;
            $attributes['policy_acknowledgements'] = $policyAcknowledgements;

            $onboarding = Onboarding::create($attributes);

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
        //
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
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Onboarding $onboarding)
    {
        //
    }
}
