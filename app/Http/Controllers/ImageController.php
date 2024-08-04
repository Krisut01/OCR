<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Image;
use Illuminate\Support\Facades\Storage;
use thiagoalessio\TesseractOCR\TesseractOCR;

class ImageController extends Controller
{
    public function index()
    {
        $images = Image::all();
        return view('home', ['images' => $images]);
    }

    public function upload(Request $request)
    {
        // Validate the input
        $request->validate([
            'images.*' => 'image|required',
        ]);
    
        $files = $request->file('images');
    
        if (is_null($files)) {
            return response()->json(['error' => 'No files were uploaded.'], 400);
        }
    
        $uploadedImages = [];
        foreach ($files as $file) {
            $path = $file->store('images');
    
            // Ensure Tesseract executable is in the PATH, or specify its location
            $text = (new TesseractOCR(storage_path('app/' . $path)))
                ->lang('eng') // Optional: specify language
                ->run();
    
            $image = Image::create([
                'file_path' => $path,
                'text' => $text,
            ]);
    
            $uploadedImages[] = $image;
        }
    
        return response()->json([
            'images' => $uploadedImages
        ]);
    }
    

    public function destroy($id)
    {
        $image = Image::findOrFail($id);
        
        // Delete the image file from storage
        Storage::delete($image->file_path);
        
        // Delete the image record from the database
        $image->delete();
        
        return redirect()->route('home')->with('success', 'Image deleted successfully');
    }
    public function deleteAll()
{
    Image::query()->delete(); // Deletes all images from the database
    Storage::deleteDirectory('images'); // Optionally, delete all images from storage

    return response()->json(['success' => true]);
}

}
