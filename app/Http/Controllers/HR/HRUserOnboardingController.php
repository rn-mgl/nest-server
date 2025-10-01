<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserOnboarding;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class HRUserOnboardingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {

            $attributes = $request->validate([
                "onboarding_id" => ["required", "integer"]
            ]);

            $users = User::with(
                [
                    "assignedOnboardings" => function ($query) use ($attributes) {
                        $query->where("onboarding_id", "=", $attributes["onboarding_id"])
                            ->withTrashed();
                    },
                    "image"
                ]
            )->get()->each(function ($user) {
                if ($user->relationLoaded("assignedOnboardings")) {
                    $user->assigned_onboarding = $user->assignedOnboardings?->first();
                    $user->unsetRelation("assignedOnboardings");
                }
            });

            return response()->json(["users" => $users]);

        } catch (\Throwable $th) {
            throw new Exception($th->getMessage());
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
                "user_ids" => ["array"],
                "user_ids.*" => ["integer", "exists:users,id"],
                "onboarding_id" => ["required", "integer", "exists:onboardings,id"]
            ]);

            DB::transaction(function () use ($attributes) {
                $checkedUserIds = collect($attributes["user_ids"] ?? []);

                $assignedOnboardings = UserOnboarding::where("onboarding_id", "=", $attributes["onboarding_id"])
                    ->withTrashed()
                    ->get();

                $alreadyAssigned = $assignedOnboardings->pluck("assigned_to");
                $newlyAssigned = $checkedUserIds->diff($alreadyAssigned);
                $revoked = $alreadyAssigned->diff($checkedUserIds);

                // assign to employees
                $userOnboardingData = $newlyAssigned->map(function ($id) use ($attributes) {
                    return [
                        "assigned_by" => Auth::id(),
                        "onboarding_id" => $attributes["onboarding_id"],
                        "assigned_to" => $id
                    ];
                });

                UserOnboarding::insert($userOnboardingData->all());

                // trashed records that were re-checked
                $assignedOnboardings
                    ->filter(
                        fn($onboarding) => $onboarding->trashed() && $checkedUserIds->contains($onboarding->assigned_to)
                    )->each(fn($onboarding) => $onboarding->restore());

                UserOnboarding::where("onboarding_id", "=", $attributes["onboarding_id"])
                    ->whereIn("assigned_to", $revoked)
                    ->delete();
            });

            return response()->json(["success" => true]);

        } catch (\Throwable $th) {
            throw new Exception($th->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
