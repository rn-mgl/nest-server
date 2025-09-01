<?php

namespace App\Http\Controllers\Base;

use App\Http\Controllers\Controller;
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
use Illuminate\Support\Facades\Storage;

class DocumentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {

            $attributes = $request->validate([
                "path" => ["required", "integer"]
            ]);

            $path = $attributes["path"];

            $documents = Document::with(["createdBy"])
                ->where("documents.path", "=", $path)
                ->select([
                    "documents.id",
                    "documents.name",
                    "documents.created_at",
                    "description",
                    "document",
                    "created_by",
                    "type",
                    "path"
                ]);

            $folders = Folder::with(["createdBy"])
                ->where("folders.path", "=", $path)
                ->select([
                    "folders.id",
                    "folders.name",
                    "folders.created_at",
                    DB::raw("NULL as description"),
                    DB::raw("NULL as document"),
                    "created_by",
                    DB::raw("'folder' as type"),
                    "path"
                ]);


            $compiled = $documents->union($folders)->get();

            return response()->json(["documents" => $compiled]);
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
                "name" => ["required", "string"],
                "description" => ["required", "string"],
                "type" => ["required", "string"],
                "path" => ["required", "integer"],
                "document" => ["required", "File"]
            ]);

            $createdDocument = DB::transaction(function () use ($attributes, $request) {

                $createdDocument = Document::create($attributes);

                if ($request->hasFile("document")) {
                    $file = $request->file("document");

                    $uploaded = Storage::disk("document")->put("", $file);

                    $createdDocument->document()->create([
                        "disk" => "document",
                        "path" => $uploaded,
                        "original_name" => $file->getClientOriginalName(),
                        "mime_type" => $file->getMimeType(),
                        "size" => $file->getSize()
                    ]);
                }

                $document = cloudinary()->uploadFile($file->getRealPath(), ["folder" => "nest-uploads"])->getSecurePath();

                $attributes["document"] = $document;
                $attributes["created_by"] = Auth::id();

                return $createdDocument;

            });

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

            // if there is no new document and the current document attribute is not a string (link), throw an error
            if (!$request->hasFile("document") && !is_string($attributes["document"])) {
                throw new Exception("Invalid Document");
            }

            $updated = DB::transaction(function () use ($attributes, $request, $document) {
                $documentAttr = [
                    "name" => $attributes["name"],
                    "description" => $attributes["description"],
                    "document" => $attributes["document"],
                    "path" => $attributes["path"],
                    "type" => $attributes["type"],
                ];

                if ($request->hasFile("document")) {
                    $file = $request->file("document");

                    $uploaded = Storage::disk("document")->put("", $file);

                    $document->document()->create([
                        "disk" => "document",
                        "path" => $uploaded,
                        "original_name" => $file->getClientOriginalName(),
                        "mime_type" => $file->getMimeType(),
                        "size" => $file->getSize()
                    ]);
                }

                $updatedDocument = $document->update($documentAttr);

                return $updatedDocument;
            });



            return response()->json(["success" => $updated]);

        } catch (\Throwable $th) {
            throw new Exception($th->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Document $document)
    {
        try {
            return response()->json(["success" => $document->delete()]);
        } catch (\Throwable $th) {
            throw new Exception($th->getMessage());
        }
    }
}
