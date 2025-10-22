<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Models\Folder;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class FolderController extends Controller
{

    ############
    # RESOURCE #
    ############

    /**
     * Store a newly created resource in storage.
     */
    public function resourceStore(Request $request)
    {
        try {
            $attributes = $request->validate([
                "title" => ["required", "string"],
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
    public function resourceShow(Folder $folder)
    {
        try {
            return response()->json(["folder" => $folder]);
        } catch (\Throwable $th) {
            throw new Exception($th->getMessage());
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function resourceUpdate(Request $request, Folder $folder)
    {
        try {
            $attributes = $request->validate([
                "title" => ["required", "string"],
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
    public function resourceDestroy(int $folder)
    {
        try {
            // delete folder and everything below it
            $paths = $this->getChildFolders($folder)->pluck("id")->push($folder);

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
    public function getFolderPaths(Request $request)
    {
        try {

            $attributes = $request->validate([
                "folder" => ["required", "integer"] // current folder where the paths will be fetched against
            ]);

            $folder = intval($attributes["folder"]);

            // the paths to avoid are the children of the current folder to be moved if the folder is not the base
            // do not move a parent folder to its child folders, "ouroboros"
            // allow documents to be moved anywhere
            $pathToAvoid = $folder === 0 ? [] : $this->getChildFolders($folder)->pluck("id")->toArray();

            // other available paths that are not the children of the folder to move
            $paths = Folder::whereNotIn("id", $pathToAvoid)->get();

            // map as label => value object pairs
            $availablePaths = $paths->map(fn($path) => ["label" => $path->title, "value" => $path->id])
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
     * @param int $folderId The ID of the current folder to start from.
     * @throws \Exception If the folder cannot be found or another error occurs.
     * @return \Illuminate\Support\Collection<int, \App\Models\Folder> Collection of Folder instances representing the parent path.
     */
    public function getParentPaths(int $folderId)
    {
        try {

            $parents = collect();

            $current = Folder::find($folderId);

            // get the parent folder of the current folder
            // NOTE: using this as the starting point of getting folders via path will include the sibling folders of $folderId
            $currentPath = $current->path;

            // if the base path hasn't been reached yet
            while ($currentPath !== 0) {

                // get the parent folder details
                $currentFolder = Folder::find($currentPath);

                if (!$currentFolder) {
                    break;
                }

                // move up a level to find the parent paths and not get the siblings
                // set the new parent path
                $parentPath = $currentFolder->path;

                // get folders under parent path
                $children = Folder::where("path", "=", $parentPath)->get();

                // merge the folders
                $parents = $parents->merge($children);

                $currentPath = $parentPath;
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
     * @param int $folderId The ID of the parent folder to start the search from.
     * @return Collection Collection of child folder paths.
     * @throws \Exception If an error occurs during retrieval.
     */
    public function getChildFolders(int $folderId)
    {
        try {

            $children = collect();

            $this->dfsChildFolders($folderId, $children);

            return $children;

        } catch (\Throwable $th) {
            throw new Exception($th->getMessage());
        }
    }

    /**
     * Recursively retrieves the child of a parent until none is found.
     *
     * @param int $path The parent path.
     * @param Collection $children The compilation of children.
     * @return void
     */
    private function dfsChildFolders(int $path, Collection &$children)
    {
        // get the folders where the path is the parent
        $childPaths = Folder::where("path", "=", $path)->get();

        foreach ($childPaths as $child) {
            // push the current child to the children record to be stored in the next iteration and recursion
            $children->push($child);
            $this->dfsChildFolders($child->id, $children);
        }
    }
}
