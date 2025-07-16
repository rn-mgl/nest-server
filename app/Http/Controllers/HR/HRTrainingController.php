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

class HRTrainingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(SearchRequest $searchRequest, SortRequest $sortRequest)
    {
        try {

            $searchAttributes = $searchRequest->validated();
            $sortAttributes = $sortRequest->validated();

            $attributes = array_merge($searchAttributes, $sortAttributes);

            $searchKey = $attributes["searchKey"];
            $searchValue = $attributes["searchValue"] ?? "";
            $sortKey = $attributes["sortKey"];
            $isAsc = filter_var($attributes["isAsc"], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            $sortType = $isAsc ? "ASC" : "DESC";

            $trainings = DB::table("trainings as t")
                        ->join("users as u", function(JoinClause $join) {
                            $join->on("t.created_by", "=", "u.id")
                            ->where("u.is_deleted", "=", false);
                        })
                        ->where("t.is_deleted", "=", false)
                        ->whereLike("t.$searchKey", "%$searchValue%")
                        ->orderBy("t.$sortKey", $sortType)
                        ->select(
                            [
                            "t.id as training_id",
                            "t.title",
                            "t.description",
                            "t.deadline_days",
                            "t.created_by",
                            "t.certificate",
                            "u.id as user_id",
                            "u.first_name",
                            "u.last_name",
                            "u.email",
                            ]
                        )->get();

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

            $contents = $request->input("contents");

            foreach($contents as $key => $value) {
                $contents[$key] = json_decode($value, true);
            }

            $reviews = $request->input("reviews") ?? [];

            foreach($reviews as $key => $value) {
                $reviews[$key] = json_decode($value, true);
            }

            // convert json string to valid json
            $request->merge(["contents" => $contents, "reviews" => $reviews]);

            $attributes = $request->validate([
                "title" => ["required", "string"],
                "description" => ["required", "string"],
                "deadline_days" => ["required", "integer"],
                "certificate" => ["required", "file"],
                "contents" => ["array"],
                "contents.*.title" => ["required", "string"],
                "contents.*.description" => ["required", "string"],
                "contents.*.content" => ["required_if:contents.*.type,text", "string"],
                "contents.*.type" => ["required", "string", "in:text,image,video,file"],
                "contentFile" => ["nullable", "array"],
                "contentFile.*" => ["required_if:contents.*.type,image,video,file"],
                "reviews" => ["array"],
                "reviews.*.answer" => ["required", "integer"],
                "reviews.*.choice_1" => ["required", "string"],
                "reviews.*.choice_2" => ["required", "string"],
                "reviews.*.choice_3" => ["required", "string"],
                "reviews.*.choice_4" => ["required", "string"],
                "reviews.*.question" => ["required", "string"],
            ]);

            $certificate = cloudinary()->uploadFile($request->file("certificate")->getRealPath(), ['folder' => 'nest-uploads'])->getSecurePath();

            $trainingAttr = [
                "created_by" => Auth::guard("base")->id(),
                "title" => $attributes["title"],
                "description" => $attributes["description"],
                "deadline_days" => $attributes["deadline_days"],
                "certificate" => $certificate
            ];

            $training = Training::create($trainingAttr);

            foreach($contents as $key => $value) {

                $isFile = in_array($value["type"], ["image", "video", "file"]);

                $contentAttr = [
                    "training_id" => $training->id,
                    "title" => $value["title"],
                    "description" => $value["description"],
                    "content" => $value["content"],
                    "type" => $value["type"],
                ];

                if ($isFile) {
                    $currentContentFile = cloudinary()->uploadFile($request->file("contentFile.$key")->getRealPath(), ['folder' => 'nest-uploads'])->getSecurePath();
                    $contentAttr['content'] = $currentContentFile;
                }

                $trainingContent = TrainingContent::create($contentAttr);
            }

            foreach($reviews as $key => $value) {

                $reviewAttr = [
                    "training_id" => $training->id,
                    "created_by" => Auth::guard("base")->id(),
                    "answer" => $value["answer"],
                    "choice_1" => $value["choice_1"],
                    "choice_2" => $value["choice_2"],
                    "choice_3" => $value["choice_3"],
                    "choice_4" => $value["choice_4"],
                    "question" => $value["question"],
                ];

                $trainingReview = TrainingReview::create($reviewAttr);

            }

            return response()->json(["success" => true]);

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
            $contents = DB::table("training_contents as tc")
                        ->where("training_id", "=", $training->id)
                        ->where("is_deleted", "=", false)
                        ->select([
                            "id as training_content_id",
                            "title",
                            "description",
                            "content",
                            "type"
                        ])
                        ->get();

            $training->contents = $contents;

            $reviews = DB::table("training_reviews as tr")
                        ->where("training_id", "=", $training->id)
                        ->where("is_deleted", "=", false)
                        ->select([
                            "tr.id as training_review_id",
                            "question",
                            "answer",
                            "choice_1",
                            "choice_2",
                            "choice_3",
                            "choice_4",
                        ])
                        ->get();

            $training->reviews = $reviews;

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
            $contents = $request->input("contents");

            foreach ($contents as $key => $value) {
                $decoded = json_decode($value, true);
                $type = $decoded['type'];
                $isFile = in_array($type, ['image','video','file']);

                // if the content is a file, the content is empty, and there is no file for in this index - throw an error
                if ($isFile && empty($decoded['content']) && !$request->hasFile("contentFile.{$key}")) {
                    throw new Exception("No file attached for {$type} Content " . $key + 1);
                }

                $contents[$key] = $decoded;
            }

            $reviews = $request->input("reviews") ?? [];

            foreach ($reviews as $key => $value) {
                $reviews[$key] = json_decode($value, true);
            }

            $request->merge(["contents" => $contents, "reviews" => $reviews]);

            $attributes = $request->validate([
                "title" => ["required", "string"],
                "description" => ["required", "string"],
                "certificate" => ["required"],
                "deadline_days" => ["required", "integer"],
                "contents" => ["required", "array"],
                "contents.*.training_content_id" => ["nullable"],
                "contents.*.title" => ["required", "string"],
                "contents.*.description" => ["required", "string"],
                "contents.*.content" => ["required_if:content.*.type,text", "string"],
                "contents.*.type" => ["required", "string", "in:text,image,video,file"],
                "contentFile" => ["required", "array"],
                "contentsToDelete" => ["array", "nullable"],
                "contentsToDelete.*" => ["nullable", "integer"],
                "reviews" => ["array"],
                "reviews.*.answer" => ["required", "integer", "in:1,2,3,4"],
                "reviews.*.question" => ["required", "string"],
                "reviews.*.choice_1" => ["required", "string"],
                "reviews.*.choice_2" => ["required", "string"],
                "reviews.*.choice_3" => ["required", "string"],
                "reviews.*.choice_4" => ["required", "string"],
                "reviews.*.training_review_id" => ["nullable"],
                "reviewsToDelete" => ["array", "nullable"],
                "reviewsToDelete.*" => ["integer"]
            ]);

            if (!$request->hasFile("certificate") && !is_string($attributes["certificate"])) {
                throw new Exception("Invalid certificate");
            }

            $trainingAttr = [
                "title" => $attributes["title"],
                "description" => $attributes["description"],
                "deadline_days" => $attributes["deadline_days"],
                "certificate" => $attributes["certificate"]
            ];

            if ($request->hasFile("certificate")) {
                $certificate = cloudinary()->uploadFile($request->file("certificate")->getRealPath(), ["folder" => "nest-uploads"])->getSecurePath();
                $trainingAttr["certificate"] = $certificate;
            }

            $updatedTraining = $training->update($trainingAttr);

            $contents = $attributes["contents"];

            foreach($contents as $key => $value) {

                // if training_content_id is set, perform update
                // if not set, perform create

                $contentAttr = [
                    "training_id" => $training->id,
                    "title" => $value["title"],
                    "description" => $value["description"],
                    "content" => $value["content"],
                    "type" => $value["type"],
                ];

                if ($request->hasFile("contentFile.$key")) {
                    $contentFile = cloudinary()->uploadFile($request->file("contentFile.$key")->getRealPath())->getSecurePath();
                    $contentAttr["content"] = $contentFile;
                }

                if (!empty($value["training_content_id"])) {
                    $updatedContent = TrainingContent::where("id", "=", $value["training_content_id"])->update($contentAttr);
                } else {
                    $createdContent = TrainingContent::create($contentAttr);
                }

            }

            $contentsToDelete = $attributes["contentsToDelete"] ?? [];

            foreach($contentsToDelete as $toDelete) {
                $deletedContents = TrainingContent::where("id", "=", $toDelete)->update(["is_deleted" => true]);
            }

            $reviews = $attributes["reviews"];

            foreach($reviews as $key => $value) {

                $reviewAttr = [
                    "training_id" => $training->id,
                    "question" => $value["question"],
                    "answer" => $value["answer"],
                    "choice_1" => $value["choice_1"],
                    "choice_2" => $value["choice_2"],
                    "choice_3" => $value["choice_3"],
                    "choice_4" => $value["choice_4"],
                ];

                if (!empty($value['training_review_id'])) {
                    $updatedReview = TrainingReview::where("id", "=", $value['training_review_id'])->update($reviewAttr);
                } else {
                    $createdReview = TrainingReview::create($reviewAttr);
                }

            }

            $reviewsToDelete = $attributes["reviewsToDelete"] ?? [];

            foreach ($reviewsToDelete as $toDelete) {

                $deletedReviews = TrainingReview::where("id", "=", $toDelete)->update(["is_deleted" => true]);

            }

            return response()->json(["success" => true]);

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
            $deleted = $training->update(["is_deleted" => true]);
            $deletedContents = TrainingContent::where("training_id", "=", $training->id)->update(["is_deleted" => true]);

            return response()->json(["success" => $deleted && $deletedContents]);
        } catch (\Throwable $th) {
            throw new Exception($th->getMessage());
        }
    }
}
