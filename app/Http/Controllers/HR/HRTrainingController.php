<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Http\Requests\SearchRequest;
use App\Http\Requests\SortRequest;
use App\Models\Training;
use App\Models\TrainingContent;
use App\Models\TrainingReview;
use Exception;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

use function PHPSTORM_META\map;

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
            $review = $decode($request->input("reviews"));

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
                "contents.*.content" => ["required_if:contents.*.type,text", "string"],

                "content_file" => ["nullable", "array"],
                "content_file.*" => ["required_if:contents.*.type,image,video,file"],

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

                        $content->content()->create([
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
            return response()->json(["training" => $training->load(["contents", "reviews"])]);
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

            $contents = $decode($request->input("contents", "[]"));
            $reviews = $decode($request->input("reviews", "[]"));
            $contentsToDelete = json_decode($request->input("contents_to_delete"), true);
            $reviewsToDelete = json_decode($request->input("reviews_to_delete"), true);

            $request->merge([
                "contents" => $contents,
                "reviews" => $reviews,
                "contents_to_delete" => $contentsToDelete,
                "reviews_to_delete" => $reviewsToDelete
            ]);

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
                "contents.*.training_content_id" => ["nullable"],
                "contents.*.title" => ["required", "string"],
                "contents.*.description" => ["required", "string"],
                "contents.*.content" => ["required_if:content.*.type,text", "string"],

                "content_file" => ["required", "array"],
                "content_file.*" => [
                    "required_unless:contents.*.type,text",
                    Rule::when(
                        $request->hasFile("content_file.*"),
                        ["file"],
                        ["nullable"]
                    )
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
                "reviews.*.training_review_id" => ["nullable"],

                "reviews_to_delete" => ["array", "nullable"],
                "reviews_to_delete.*" => ["integer"]
            ]);



            $updated = DB::transaction(function () use ($attributes, $training, $request) {

                $trainingAttr = [
                    "title" => $attributes["title"],
                    "description" => $attributes["description"],
                    "deadline_days" => $attributes["deadline_days"],
                    "certificate" => $attributes["certificate"]
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

                    if (!$content["training_content_id"]) {
                        $content = TrainingContent::create([
                            "training_id" => $training->id,
                            "title" => $content["title"],
                            "description" => $content["description"],
                            "content" => $content["content"] ?? null,
                        ]);
                    } else {
                        $content = TrainingContent::find($content["training_content_id"]);
                        $content->update([
                            "title" => $content["title"],
                            "description" => $content["description"],
                            "content" => $content["content"] ?? null,
                        ]);
                    }

                    if ($request->hasFile("content_file.{$index}")) {
                        $file = $request->file("content_file.{$index}");

                        $content->content()->delete();

                        $uploaded = Storage::disk($disk)->put("/contents", $file);

                        $content->content()->create([
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
                        "id" => $review["training_review_id"] ?? null,
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

                TrainingContent::whereIn("id", $attributes["contentsToDelete"] ?? [])->delete();

                TrainingReview::whereIn("id", $attributes["reviewsToDelete"] ?? [])->delete();

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

            $deleted = $training->delete();
            $deletedContents = TrainingContent::where("training_id", "=", $training->id)->delete();

            return response()->json(["success" => $deleted || $deletedContents]);
        } catch (\Throwable $th) {
            throw new Exception($th->getMessage());
        }
    }
}
