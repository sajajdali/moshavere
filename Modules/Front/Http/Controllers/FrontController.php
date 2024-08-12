<?php

namespace Modules\Front\Http\Controllers;

use App\Http\Controllers\Controller;
use FFMpeg\FFMpeg;
use FFMpeg\Format\Video\X264;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Storage;

class FrontController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    public function convertWavToMp4(Request $request)
    {


        // Define the input and output file paths
        $inputFilePath = storage_path('app/public/chat/audio.wav');
        $outputFilePath = storage_path('app/public/chat/audio.mp4');

        // Check if the input file exists
        if (!file_exists($inputFilePath)) {
            return response()->json(['error' => 'Input file not found.'], 404);
        }

        // Initialize FFmpeg
        $ffmpeg = FFMpeg::create([
            'ffmpeg.binaries'  => env('FFMPEG_BINARIES'),
            'ffprobe.binaries' => env('FFPROBE_BINARIES'),
        ]);
        // Open the WAV file
        $audio = $ffmpeg->open($inputFilePath);

        // Define the output format
        $format = new X264();

        // Save the audio as an MP4 video file
        $audio->save($format, $outputFilePath);

        // Return the MP4 file as a download and delete after sending
        return response()->download($outputFilePath);
    }
    public function index()
    {
        return view('front::index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('front::create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        //
    }

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        return view('front::show');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        return view('front::edit');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id): RedirectResponse
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        //
    }
}
