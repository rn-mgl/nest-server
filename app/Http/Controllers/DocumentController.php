<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Models\Folder;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class DocumentController extends Controller
{

    ############
    # RESOURCE #
    ############

    /**
     * Display a listing of the resource.
     */
    public function resourceIndex(Request $request)
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
                    "documents.title",
                    "documents.created_at",
                    "description",
                    "created_by",
                    DB::raw("'Document' as type"),
                    "path"
                ]);

            $folders = Folder::with(["createdBy"])
                ->where("folders.path", "=", $path)
                ->select([
                    "folders.id",
                    "folders.title",
                    "folders.created_at",
                    DB::raw("NULL as description"),
                    "created_by",
                    DB::raw("'Folder' as type"),
                    "path"
                ]);


            $compiled = $documents->union($folders)->orderBy("created_at")->get();

            return response()->json(["documents" => $compiled]);
        } catch (\Throwable $th) {
            throw new Exception($th->getMessage());
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function resourceStore(Request $request)
    {
        try {
            $attributes = $request->validate([
                "title" => ["required", "string"],
                "description" => ["required", "string"],
                "path" => ["required", "integer"],
                "document" => ["required", "file"]
            ]);

            $createdDocument = DB::transaction(function () use ($attributes, $request) {

                unset($attributes["document"]);

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

                $attributes["created_by"] = Auth::id();

                return $createdDocument;

            });

            return response()->json(["success" => $createdDocument]);

        } catch (\Throwable $th) {
            throw new Exception($th->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function resourceShow(Document $document)
    {
        try {
            return response()->json(["document" => $document->load(["document", "folder"])]);
        } catch (\Throwable $th) {
            throw new Exception($th->getMessage());
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function resourceUpdate(Request $request, Document $document)
    {

        try {
            $attributes = $request->validate([
                "title" => ["required", "string"],
                "description" => ["required", "string"],
                "document" => [
                    "required",
                    Rule::when(
                        $request->hasFile("document"),
                        "file",
                        "string"
                    )
                ],
                "path" => ["required", "integer"],
            ]);

            $updated = DB::transaction(function () use ($attributes, $request, $document) {
                $documentAttr = [
                    "title" => $attributes["title"],
                    "description" => $attributes["description"],
                    "path" => $attributes["path"],
                ];

                if ($request->hasFile("document")) {
                    $file = $request->file("document");

                    // delete old
                    $document->document()->delete();

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
    public function resourceDestroy(Document $document)
    {
        try {
            return response()->json(["success" => $document->delete()]);
        } catch (\Throwable $th) {
            throw new Exception($th->getMessage());
        }
    }
}
