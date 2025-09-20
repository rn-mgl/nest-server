<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\Training;
use App\Models\TrainingContent;
use App\Models\TrainingReview;
use App\Models\UserTraining;
use App\Models\UserTrainingReviewResponse;
use Closure;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class HRTrainingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {

            $trainings = Training::with(["createdBy"])->get();

            return response()->json(["trainings" => $trainings]);
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

            $decode = fn($value) => collect($value ?? [])
                ->map(fn($item) => json_decode($item, true))
                ->all();

            $contents = $decode($request->input("contents"));
            $reviews = $decode($request->input("reviews"));

            $request->merge(compact("contents", "reviews"));

            // convert json string to valid json
            $attributes = $request->validate([
                "title" => ["required", "string"],
                "description" => ["required", "string"],
                "deadline_days" => ["required", "integer"],
                "certificate" => ["required", "file"],

                "contents" => ["array"],
                "contents.*.title" => ["required", "string"],
                "contents.*.description" => ["required", "string"],
                // content specific validation
                "contents.*" => [
                    function (string $attribute, mixed $value, Closure $fail) use ($request) {

                        $index = Str::of($attribute)->after(".");
                        $content = $value['content'];
                        $type = $request->input("contents.{$index}.type");

                        if (
                            $type === "text" &&
                            (
                                empty($content) ||
                                gettype($content) !== "string"
                            )
                        ) {
                            $fail("The {$attribute} is required when the type is a text");
                        }

                    }
                ],

                "content_file" => ["nullable", "array"],
                "content_file.*" => [
                    function (string $attribute, mixed $value, Closure $fail) use ($request) {
                        $index = Str::of($attribute)->after(".");

                        $type = $request->input("contents.{$index}.type");

                        if (
                            $type !== "text" &&
                            (
                                !$request->hasFile("content_file.{$index}") ||
                                !$request->file("content_file.{$index}") instanceof UploadedFile
                            )
                        ) {
                            $fail("The {$attribute} is required when the type is not a text.");
                        }
                    }
                ],

                "reviews" => ["array"],
                "reviews.*.answer" => ["required", "integer"],
                "reviews.*.choice_1" => ["required", "string"],
                "reviews.*.choice_2" => ["required", "string"],
                "reviews.*.choice_3" => ["required", "string"],
                "reviews.*.choice_4" => ["required", "string"],
                "reviews.*.question" => ["required", "string"],
            ]);

            $created = DB::transaction(function () use ($attributes, $request) {
                $training = Training::create([
                    "created_by" => Auth::id(),
                    "title" => $attributes["title"],
                    "description" => $attributes["description"],
                    "deadline_days" => $attributes["deadline_days"]
                ]);

                $disk = "training";

                if ($request->hasFile("certificate")) {
                    $file = $request->file("certificate");

                    $uploaded = Storage::disk($disk)->put("/certificates", $file);

                    $training->certificate()->create([
                        "disk" => $disk,
                        "path" => $uploaded,
                        "original_name" => $file->getClientOriginalName(),
                        "mime_type" => $file->getMimeType(),
                        "size" => $file->getSize(),
                    ]);
                }

                foreach ($attributes["contents"] as $key => $value) {
                    $content = TrainingContent::create([
                        "training_id" => $training->id,
                        "title" => $value["title"],
                        "description" => $value["description"],
                        "content" => $value["content"] ?? null
                    ]);

                    if ($request->hasFile("content_file.{$key}")) {
                        $file = $request->file("content_file.{$key}");

                        $uploaded = Storage::disk($disk)->put("/contents", $file);

                        $content->contentFile()->create([
                            "disk" => $disk,
                            "path" => $uploaded,
                            "original_name" => $file->getClientOriginalName(),
                            "mime_type" => $file->getMimeType(),
                            "size" => $file->getSize()
                        ]);
                    }
                }

                $reviewsData = collect($attributes["reviews"])
                    ->map(function ($review) use ($training) {
                        return [
                            "training_id" => $training->id,
                            "created_by" => Auth::id(),
                            "answer" => $review["answer"],
                            "choice_1" => $review["choice_1"],
                            "choice_2" => $review["choice_2"],
                            "choice_3" => $review["choice_3"],
                            "choice_4" => $review["choice_4"],
                            "question" => $review["question"],
                        ];
                    });

                TrainingReview::insert($reviewsData->all());

                return $training;
            });

            return response()->json(["success" => $created]);

        } catch (\Throwable $th) {
            throw new Exception($th->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Training $training)
    {

        try {

            $training->load(
                [
                    "contents" => ["contentFile"],
                    "reviews",
                    "certificate"
                ]
            );

            $training->contents->each(function ($content) {
                $content->content = $content->contentFile ?? $content->content;
                $content->unsetRelation('contentFile');
            });

            return response()->json(["training" => $training]);
        } catch (\Throwable $th) {
            throw new Exception($th->getMessage());
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Training $training)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Training $training)
    {
        try {

            $decode = fn($value) => collect($value ?? [])
                ->map(fn($item) => json_decode($item, true))
                ->all();

            $contents = $decode($request->input("contents", []));
            $reviews = $decode($request->input("reviews", []));

            $request->merge(compact("contents", "reviews"));

            $attributes = $request->validate([
                "title" => ["required", "string"],
                "description" => ["required", "string"],
                "deadline_days" => ["required", "integer"],
                "certificate" => [
                    "required",
                    Rule::when(
                        $request->hasFile("certificate"),
                        ["file"],
                        ["string"]
                    )
                ],

                "contents" => ["required", "array"],
                "contents.*.id" => ["nullable"],
                "contents.*.title" => ["required", "string"],
                "contents.*.description" => ["required", "string"],
                "contents.*" => [
                    function (string $attribute, mixed $value, Closure $fail) use ($request) {
                        $index = Str::of($attribute)->after(".");
                        $type = $request->input("contents.{$index}.type");
                        $content = $value['content'];

                        if (
                            $type === "text" &&
                            (
                                empty($content) ||
                                gettype($content) !== "string"
                            )
                        ) {
                            $fail("The {$attribute} must be a text");
                        }
                    }
                ],

                "content_file" => ["required", "array"],
                "content_file.*" => [
                    function (string $attribute, mixed $value, Closure $fail) use ($request) {
                        $index = Str::of($attribute)->after(".");
                        $type = $request->input("contents.{$index}.type");

                        if (
                            $type !== "text" &&
                            (
                                !$request->hasFile("content_file.{$index}") ||
                                !$request->file("content_file.{$index}") instanceof UploadedFile
                            ) &&
                            !is_array(json_decode($request->input("content_file.{$index}"), true))
                        ) {
                            $fail("The {$attribute} must be a file.");
                        }
                    }
                ],

                "contents_to_delete" => ["array", "nullable"],
                "contents_to_delete.*" => ["nullable", "integer"],

                "reviews" => ["array"],
                "reviews.*.answer" => ["required", "integer", "in:1,2,3,4"],
                "reviews.*.question" => ["required", "string"],
                "reviews.*.choice_1" => ["required", "string"],
                "reviews.*.choice_2" => ["required", "string"],
                "reviews.*.choice_3" => ["required", "string"],
                "reviews.*.choice_4" => ["required", "string"],
                "reviews.*.id" => ["nullable"],

                "reviews_to_delete" => ["array", "nullable"],
                "reviews_to_delete.*" => ["integer"]
            ]);

            $updated = DB::transaction(function () use ($attributes, $training, $request) {

                $trainingAttr = [
                    "title" => $attributes["title"],
                    "description" => $attributes["description"],
                    "deadline_days" => $attributes["deadline_days"]
                ];

                $updated = $training->update($trainingAttr);

                $disk = "training";

                if ($request->hasFile("certificate")) {
                    // soft delete old certificate to be overwritten by new one
                    $training->certificate()->delete();

                    $file = $request->file("certificate");

                    $uploaded = Storage::disk($disk)->put("/content", $file);

                    $training->certificate()->create([
                        "disk" => $disk,
                        "path" => $uploaded,
                        "original_name" => $file->getClientOriginalName(),
                        "mime_type" => $file->getMimeType(),
                        "size" => $file->getSize()
                    ]);
                }

                foreach ($attributes["contents"] as $index => $content) {

                    // upsert can't be used here to get the instance of the created/searched TrainingContent
                    if (!isset($content["id"])) {
                        $content = TrainingContent::create([
                            "training_id" => $training->id,
                            "title" => $content["title"],
                            "description" => $content["description"],
                            "content" => $content["content"] ?? null,
                        ]);
                    } else {
                        $content = TrainingContent::find($content["id"]);
                        $content->update([
                            "title" => $content["title"],
                            "description" => $content["description"],
                            "content" => $content["content"] ?? null,
                        ]);
                    }

                    // soft delete and update the current content's file
                    if ($request->hasFile("content_file.{$index}")) {
                        $file = $request->file("content_file.{$index}");

                        $content->contentFile()->delete();

                        $uploaded = Storage::disk($disk)->put("/contents", $file);

                        $content->contentFile()->create([
                            "disk" => $disk,
                            "path" => $uploaded,
                            "original_name" => $file->getClientOriginalName(),
                            "mime_type" => $file->getMimeType(),
                            "size" => $file->getSize()
                        ]);
                    }
                }

                $reviewData = collect($attributes["reviews"] ?? [])->map(function ($review) use ($training) {
                    return [
                        "id" => $review["id"] ?? null,
                        "training_id" => $training->id,
                        "question" => $review["question"],
                        "answer" => $review["answer"],
                        "choice_1" => $review["choice_1"],
                        "choice_2" => $review["choice_2"],
                        "choice_3" => $review["choice_3"],
                        "choice_4" => $review["choice_4"],
                    ];
                });

                TrainingReview::upsert(
                    $reviewData->all(),
                    ["id"],
                    ["question", "answer", "choice_1", "choice_2", "choice_3", "choice_4"]
                );

                TrainingContent::whereIn("id", $attributes["contents_to_delete"] ?? [])->delete();

                TrainingReview::whereIn("id", $attributes["reviews_to_delete"] ?? [])->delete();

                return $updated;
            });

            return response()->json(["success" => $updated]);

        } catch (\Throwable $th) {
            throw new Exception($th->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Training $training)
    {
        try {

            $deleted = DB::transaction(function () use ($training) {

                $deleted = $training->delete();

                $deletedContents = TrainingContent::where("training_id", "=", $training->id)->delete();

                $affectedReviews = TrainingReview::where("training_id", "=", $training->id)->get()->pluck("id");

                $deletedReviews = TrainingReview::whereIn("id", $affectedReviews)->delete();

                $deletedUserTrainings = UserTraining::where("training_id", "=", $training->id)->delete();

                $deletedUserReviewResponse = UserTrainingReviewResponse::whereIn("training_review_id", $affectedReviews)->delete();

                return $deleted || $deletedContents || $deletedReviews || $deletedUserTrainings || $deletedUserReviewResponse;

            });

            return response()->json(["success" => $deleted]);
        } catch (\Throwable $th) {
            throw new Exception($th->getMessage());
        }
    }
}
