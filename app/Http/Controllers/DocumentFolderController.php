<?php

namespace App\Http\Controllers;

use App\Models\DocumentFolder;
use Exception;
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
        try {
            $attributes = $request->validate([
                "name" => ["required", "string"],
                "path" => ["required", "integer"]
            ]);

            $updatedFolder = $documentFolder->update($attributes);

            return response()->json(["success" => $updatedFolder]);

        } catch (\Throwable $th) {
            throw $th;
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($documentFolder)
    {
        try {
            // delete folder and everything below it
            $paths = DB::table("document_folders")
            ->select([
                "id",
                "path"
            ])
            ->where("is_deleted", false)
            ->get();

            $childPaths = $this->get_child_paths($documentFolder, $paths);

            foreach($childPaths as $child) {
                $deletedFolders = DB::table("document_folders")
                            ->where("id", $child)
                            ->update(["is_deleted" => true]);

                $deletedDocuments = DB::table("documents")
                                    ->where("path", $child)
                                    ->update(["is_deleted" => true]);
            }

            $deletedFolder = DB::table("document_folders")
                            ->where("id", $documentFolder)
                            ->update(["is_deleted" => true]);

            $deletedDocument = DB::table("documents")
                            ->where("path", $documentFolder)
                            ->update(["is_deleted" => true]);

            return response()->json(["success" => $deletedFolder]);
        } catch (\Throwable $th) {
            throw new Exception($th->getMessage());
        }
    }

    public function get_parent_paths(Request $request)
    {
        try {

            $attributes = $request->validate([
                "path" => ["required", "integer"]
            ]);

            $currentPath = intval($attributes["path"]);

            $paths = DB::table("document_folders")
                    ->select(
                [
                            "id",
                            "name",
                            "path"
                        ]
                    )
                    ->where("is_deleted", false)
                    ->get();

            // only remove child path if base path of document/folder is not home
            if ($currentPath !== 0) {
                // compile paths with same path value as current path to see if they have child paths first
                $similarPaths = $paths->filter(function ($path) use($currentPath) {
                    return $path->path == $currentPath;
                })->pluck("id")->toArray();

                $parentPaths = $this->remove_child_paths($similarPaths, $paths);

                $paths = $parentPaths->filter(function($path)  use($currentPath) {
                    return $path->path != $currentPath;
                });
            }

            $availablePaths = $paths->map(function($path) {
                return ["label" => $path->name, "value" => $path->id];
            })->toArray();

            return response()->json(["paths" => array_values($availablePaths)]);

        } catch (\Throwable $th) {
            throw new \Exception($th->getMessage());
        }
    }

    public function get_child_paths($parent, $paths, $child = [])
    {
        foreach($paths as $key => $path) {
            if ($path->path == $parent) {
                unset($paths[$key]);
                $child[] = $path->id;
                $child = $this->get_child_paths($path->id, $paths, $child);
            }
        }

        return $child;
    }

    // function remove the child paths of a parent in the paths return
    private function remove_child_paths($parentIds, $paths)
    {
        foreach($paths as $key => $path) {
            if (in_array($path->path, $parentIds)) {
                unset($paths[$key]);
                $paths = $this->remove_child_paths([$path->id], $paths);
            }
        }

        return $paths;

    }
}
