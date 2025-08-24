<?php

namespace App\Http\Controllers\Base;

use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Models\Folder;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class FolderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
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

            $attributes["created_by"] = Auth::id();

            $createdFolder = Folder::create($attributes);

            return response()->json(["success" => $createdFolder]);

        } catch (\Throwable $th) {
            throw new Exception($th->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Folder $folder)
    {
        try {
            return response()->json(["folder" => $folder]);
        } catch (\Throwable $th) {
            throw new Exception($th->getMessage());
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Folder $folder)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Folder $folder)
    {
        try {
            $attributes = $request->validate([
                "name" => ["required", "string"],
                "path" => ["required", "integer"]
            ]);

            $updatedFolder = $folder->update($attributes);

            return response()->json(["success" => $updatedFolder]);

        } catch (\Throwable $th) {
            throw $th;
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $folder)
    {
        try {
            // delete folder and everything below it
            $paths = $this->get_child_paths($folder)->pluck("id")->push($folder)->toArray();

            $deletedFolders = Folder::whereIn("id", $paths)->delete();
            $deletedDocuments = Document::whereIn("path", $paths)->delete();

            // as long as a folder or document is deleted (there could be folders with no documents)
            return response()->json(["success" => $deletedFolders || $deletedDocuments]);
        } catch (\Throwable $th) {
            throw new Exception($th->getMessage());
        }
    }
    /**
     * Retrieves the paths of folders based on the incoming request.
     *
     * This method processes the provided request and returns a JSON response
     * containing the relevant folder paths. Throws an exception if the operation fails.
     *
     * @param \Illuminate\Http\Request $request The HTTP request instance containing folder query parameters.
     * @return \Illuminate\Http\JsonResponse JSON response with folder paths.
     * @throws \Exception If unable to retrieve folder paths.
     */
    public function get_folder_paths(Request $request)
    {
        try {

            $attributes = $request->validate([
                "folder" => ["required", "integer"] // current folder where the paths will be fetched against
            ]);

            $folder = intval($attributes["folder"]);

            // the paths to avoid are the children of the current folder to be moved if the folder is not the base
            // do not move a parent folder to its child folders, "ouroboros"
            // allow documents to be moved anywhere
            $pathToAvoid = $folder === 0 ? [] : $this->get_child_paths($folder)->pluck("id")->toArray();

            // other available paths that are not the children of the folder to move
            $paths = Folder::whereNotIn("id", $pathToAvoid)->get();

            // map as label => value object pairs
            $availablePaths = $paths->map(fn($path) => ["label" => $path->name, "value" => $path->id])
                                ->prepend(["label" => "Home", "value" => 0])
                                ->toArray();

            return response()->json(["paths" => $availablePaths]);

        } catch (\Throwable $th) {
            throw new Exception($th->getMessage());
        }
    }

    /**
     * Retrieves the parent folder paths for a given folder ID.
     *
     * This method returns a collection of Folder instances representing
     * the hierarchical path from the specified parent folder up to the root.
     *
     * @param int $currentFolder The ID of the parent folder to start from.
     * @throws \Exception If the folder cannot be found or another error occurs.
     * @return \Illuminate\Support\Collection<int, \App\Models\Folder> Collection of Folder instances representing the parent path.
     */
    public function get_parent_paths(int $currentFolder)
    {
        try {

            $parents = collect();

            $current = Folder::find($currentFolder);

            // get the parent folder of the current folder
            // NOTE: using this as the starting point of getting folders via path will include the sibling folders of $currentFolder
            $parentFolder = $current->path;

            // if the base path hasn't been reached yet
            while ($parentFolder !== 0) {

                // get the parent folder details
                $parent = Folder::find($parentFolder);

                if (!$parent) {
                    break;
                }

                // move up a level to find the parent paths and not get the siblings
                // set the new parent path
                $parentFolder = $parent->path;

                // get folders under parent path
                $children = Folder::where("path", "=", $parentFolder)->get();

                // merge the folders
                $parents = $parents->merge($children);
            }

            // return the parents
            return $parents;
        } catch (\Throwable $th) {
            throw new Exception($th->getMessage());
        }
    }

    /**
     * Retrieves all child folder paths of the specified parent folder using depth-first search.
     *
     * @param int $parentFolder The ID of the parent folder to start the search from.
     * @return \Illuminate\Support\Collection Collection of child folder paths.
     * @throws \Exception If an error occurs during retrieval.
     */
    public function get_child_paths(int $parentFolder)
    {
        try {

            $children = collect();

            $this->dfs_child_paths($parentFolder, $children);

            return $children;

        } catch (\Throwable $th) {
            throw new Exception($th->getMessage());
        }
    }

    /**
     * Removes all child paths from the provided list that are descendants of a specified parent path.
     *
     * This function ensures that only the parent path is retained in the result, eliminating any nested child paths.
     *
     * @param array $paths Array of file or folder paths.
     * @param string $parentPath The parent path whose children should be removed from the list.
     * @return void
     */
    private function dfs_child_paths(int $parentFolder, Collection &$children)
    {
        // get the folders where the path is the parent
        $childPaths = Folder::where("path", "=", $parentFolder)->get();

        foreach($childPaths as $child) {
            // push the current child to the children record to be stored in the next iteration and recursion
            $children->push($child);
            $this->dfs_child_paths($child->id, $children);
        }
    }
}
