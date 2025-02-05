<?php

namespace App\Http\Controllers;

use App\Models\Document;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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

            $documents = DB::table("documents as d")
                        ->where("d.is_deleted", false)
                        ->where("d.path", $path)
                        ->join("users as u", function(JoinClause $join) {
                            $join->on("u.id", "=", "d.created_by")
                            ->where("u.is_deleted", false);
                        })
                        ->select([
                            "d.id",
                            "u.id as user_id",
                            "u.first_name",
                            "u.last_name",
                            "u.email",
                            "name",
                            "description",
                            "document",
                            "created_by",
                            "type",
                            "path"
                        ]);

            $compiled = DB::table("document_folders as df")
                        ->where("df.is_deleted", false)
                        ->where("df.path", $path)
                        ->join("users as u", function(JoinClause $join) {
                            $join->on("u.id", "=", "df.created_by")
                            ->where("u.is_deleted", false);
                        })
                        ->union($documents)
                        ->select([
                            "df.id",
                            "u.id as user_id",
                            "u.first_name",
                            "u.last_name",
                            "u.email",
                            "name",
                            DB::raw("NULL as description"),
                            DB::raw("NULL as document"),
                            "created_by",
                            DB::raw("'folder' as type"),
                            "path"
                        ])
                        ->get();

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
            $attributes["created_by"] = Auth::guard("base")->id();

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
        //
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
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Document $document)
    {
        //
    }
}
