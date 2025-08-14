<?php

namespace App\Http\Controllers;

use App\Http\Requests\CategoryRequest;
use App\Http\Requests\SearchRequest;
use App\Http\Requests\SortRequest;
use App\Models\Document;
use App\Models\Folder;
use Exception;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DocumentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, SearchRequest $searchRequest, CategoryRequest $categoryRequest, SortRequest $sortRequest)
    {
        try {

            $requestAttributes = $request->validate([
                "path" => ["required", "integer"]
            ]);

            $searchAttributes = $searchRequest->validated();
            $categoryAttributes = $categoryRequest->validated();
            $sortAttributes = $sortRequest->validated();

            $attributes = array_merge($searchAttributes, $categoryAttributes, $sortAttributes, $requestAttributes);

            $path = $attributes["path"];
            $searchKey = $attributes["searchKey"];
            $searchValue = $attributes["searchValue"] ?? "";
            $sortKey = $attributes["sortKey"];
            $isAsc = filter_var($attributes["isAsc"], FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE);
            $sortType = $isAsc ? "ASC" : "DESC";
            $categoryKey = $attributes["categoryKey"];
            $categoryValue = $attributes["categoryValue"];

            if ($categoryValue === "folders") {
                $searchKey = "name";
            }

            $documents = Document::where("documents.is_deleted", false)
                ->where("documents.path", $path)
                ->when(in_array($categoryValue, ["documents", "all"]), function($query) use($searchKey, $searchValue, $sortKey, $sortType) {
                    return $query->whereLike("documents.{$searchKey}", "%{$searchValue}%")
                    ->orderBy("documents.{$sortKey}", $sortType);
                })
                ->join("users as u", function(JoinClause $join) {
                    $join->on("u.id", "=", "documents.created_by")
                    ->where("u.is_deleted", false);
                })
                ->select([
                    "documents.id",
                    "u.id as user_id",
                    "u.first_name",
                    "u.last_name",
                    "u.email",
                    "name",
                    "documents.created_at",
                    "description",
                    "document",
                    "created_by",
                    "type",
                    "path"
                ]);

            $folders = Folder::where("folders.is_deleted", false)
                ->where("folders.path", $path)
                ->when($categoryValue === "folders", function($query) use($searchKey, $searchValue, $sortKey, $sortType) {
                    return $query->whereLike("folders.{$searchKey}", "%{$searchValue}%")
                    ->orderBy("folders.{$sortKey}", $sortType);
                })
                ->join("users as u", function(JoinClause $join) {
                    $join->on("u.id", "=", "folders.created_by")
                    ->where("u.is_deleted", false);
                })
                ->select([
                    "folders.id",
                    "u.id as user_id",
                    "u.first_name",
                    "u.last_name",
                    "u.email",
                    "name",
                    "folders.created_at",
                    DB::raw("NULL as description"),
                    DB::raw("NULL as document"),
                    "created_by",
                    DB::raw("'folder' as type"),
                    "path"
                ]);

            if ($categoryValue === "all") {
                $compiled = $documents->union($folders)
                            ->when($categoryValue === "all", function($query) use($sortKey, $sortType) {
                                return $query->orderBy("{$sortKey}", $sortType);
                            })->get();
            } else if ($categoryValue === "folders") {
                $compiled = $folders->get();
            } else if ($categoryValue === "documents") {
                $compiled = $documents->get();
            } else {
                $compiled = null;
            }

            return response()->json(["documents" => $compiled]);
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
                "name" => ["required", "string"],
                "description" => ["required", "string"],
                "type" => ["required", "string"],
                "path" => ["required", "integer"],
                "document" => ["required", "File"]
            ]);

            $document = cloudinary()->uploadFile($request->file("document")->getRealPath(), ["folder" => "nest-uploads"])->getSecurePath();

            $attributes["document"] = $document;
            $attributes["created_by"] = Auth::id();

            $createdDocument = Document::create($attributes);

            return response()->json(["success" => $createdDocument]);
        } catch (\Throwable $th) {
            throw new \Exception($th->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Document $document)
    {
        try {
            return response()->json(["document" => $document->load("folders")]);
        } catch (\Throwable $th) {
            throw new \Exception($th->getMessage());
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Document $document)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Document $document)
    {

        try {
            $attributes = $request->validate([
                "name" => ["required", "string"],
                "description" => ["required", "string"],
                "document" => ["required"],
                "path" => ["required", "string"],
                "type" => ["required", "string"],
            ]);

            if (!$request->hasFile("document") && !is_string($attributes["document"])) {
                throw new Exception("Invalid Document");
            }

            $documentAttr = [
                "name" => $attributes["name"],
                "description" => $attributes["description"],
                "document" => $attributes["document"],
                "path" => $attributes["path"],
                "type" => $attributes["type"],
            ];

            if ($request->hasFile("document")) {
                $file = cloudinary()->uploadFile($request->file("document")->getRealPath(), ["folder" => "nest-uploads"])->getSecurePath();
                $documentAttr['document'] = $file;
            }

            $updatedDocument = $document->update($documentAttr);

            return response()->json(["success" => $updatedDocument]);

        } catch (\Throwable $th) {
            throw new \Exception($th->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Document $document)
    {
        try {
            $deleted = $document->update(["is_deleted" => true]);
            return response()->json(["success" => $deleted]);
        } catch (\Throwable $th) {
            throw new Exception($th->getMessage());
        }
    }
}
