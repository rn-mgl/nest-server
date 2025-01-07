<?php

namespace App\Http\Controllers;

use App\Models\Training;
use Illuminate\Http\Request;

class HRTrainingController extends Controller
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

            $contents = $request->input("contents");

            foreach($contents as $key => $value) {
                $contents[$key] = json_decode($value, true);
            }

            $request["contents"] = $contents;

            $attributes = $request->validate([
                "title" => ["required", "string"],
                "description" => ["required", "string"],
                "contents" => ["array"],
                "contents.*.title" => ["required", "string"],
                "contents.*.description" => ["required", "string"],
                "contents.*.content" => ["required_if:contents.*.type,text", "string"],
                "contents.*.type" => ["required", "string", "in:text,image,video,file"],
                "contentFile" => ["nullable", "array"],
                "contentFile.*" => ["required_if:contents.*.type,image,video,file"]
            ]);

            $contentFile = $request->file("contentFile");

            foreach($contents as $key => $value) {
                logger($key);
            }

        } catch (\Throwable $th) {
            throw new \Exception($th->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Training $training)
    {
        //
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
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Training $training)
    {
        //
    }
}
