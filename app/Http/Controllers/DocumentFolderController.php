<?php

namespace App\Http\Controllers;

use App\Models\DocumentFolder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DocumentFolderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $folders = DB::table("document_folders as df")
                        ->where("df.is_deleted", "=", false)
                        ->get();

            return response()->json(["folders" => $folders]);
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
                "path" => ["required", "integer"]
            ]);

            $folderAttr = [
                "name" => $attributes["name"],
                "path" => $attributes["path"],
                "created_by" => Auth::guard("base")->id()
            ];

            $createdFolder = DocumentFolder::create($folderAttr);

            return response()->json(["success" => true]);

        } catch (\Throwable $th) {
            throw new \Exception($th->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($documentFolder)
    {
        try {

            if (!$documentFolder) {
                return response()->json(["folder" => []]);
            }

            $folder = DocumentFolder::find($documentFolder);


            return response()->json(["folder" => $folder]);
        } catch (\Throwable $th) {
            throw new \Exception($th->getMessage());
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(DocumentFolder $documentFolder)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, DocumentFolder $documentFolder)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(DocumentFolder $documentFolder)
    {
        //
    }

    public function get_paths(Request $request)
    {
        try {
            $paths = DB::table("document_folders")
                    ->select(
                [
                            "id",
                            "name"
                        ]
                    )
                    ->where("is_deleted", false)
                    ->get()
                    ->map(function($path) {
                        return ["label" => $path->name, "value" => $path->id];
                    });

            return response()->json(["paths" => $paths]);

        } catch (\Throwable $th) {
            throw new \Exception($th->getMessage());
        }
    }
}
