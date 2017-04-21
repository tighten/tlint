<?php

namespace App\Http\Controllers\Admin\Frames;

use Spatie\MediaLibrary\Media;

use App\Entities\Catalog\Frame;
use App\Entities\Catalog\FrameColor;
use App\Entities\Catalog\Lens;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

class FramePhotosController extends Controller
{
    public function index($frameId)
    {
        $frame = $this->getModelOrBack(Frame::with('colors'), $frameId);

        return view('admin.frames.edit.photos', [
            'active' => 'frames',
            'frame' => $frame
        ]);
    }

    public function store(Request $request, $frameId)
    {
        if ($request->hasFile('image')) {
            switch($request->input('collection')) {
                case 'sizingChart':
                case 'detailShot':
                case 'modelM':
                case 'modelF':
                    $this->uploadGenericImage($request, $frameId);
                    break;
                case 'colors':
                    $this->uploadFrameColorImages($request);
                    break;
                default:
                    $this->notify("Unspecified Image Collection");
                    break;
            }
        }
        else
            $this->notify('Please select a file to upload.');

        return back();
    }

    public function destroy($frameId, $imageId)
    {
        $this
            ->getModelOrFail(Media::class, $imageId)
            ->delete();

        $this->notify('Image has been deleted.');

        return response(200);
    }

    private function uploadGenericImage(Request $request, $frameId)
    {
        $frame = $this->getModelOrBack(Frame::class, $frameId);
        $collection = $request->input('collection');

        foreach ($request->file('image') as $rawImage) {
            $frame
                ->addMedia($rawImage)
                ->toCollection($collection);
        }
    }

    private function uploadFrameColorImages(Request $request)
    {
        $frameColor = $this->getModelOrBack(FrameColor::class, $request->input('colorId'));

        foreach ($request->file('image') as $rawImage) {
            $frameColor
                ->addMedia($rawImage)
                ->toCollection('images');
        }

        $this->notify('Uploaded ' . count($request->file('image')) . ' new image(s).');
    }
}
