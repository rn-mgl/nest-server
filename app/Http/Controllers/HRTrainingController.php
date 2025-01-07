<?php

namespace App\Http\Controllers;

use App\Models\Training;
use App\Models\TrainingContent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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

            // convert json string to valid json
            $request->merge(["contents" => $contents]);

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
                "contentFile.*" => ["required_if:contents.*.type,image,video,file"]
            ]);

            // $certificate = cloudinary()->upload($request->file("certificate")->getRealPath(), ['folder' => 'nest-uploads'])->getSecurePath();

            $trainingAttr = [
                "created_by" => Auth::guard("base")->id(),
                "title" => $attributes["title"],
                "description" => $attributes["description"],
                "deadline_days" => $attributes["deadline_days"],
                "certificate" => "sad"
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
                    $currentContentFile = cloudinary()->upload($request->file("contentFile.$key")->getRealPath(), ['folder' => 'nest-uploads'])->getSecurePath();
                    $contentAttr['content'] = $currentContentFile;
                }

                $trainingContent = TrainingContent::create($contentAttr);
            }

            return response()->json(["success" => true]);

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
