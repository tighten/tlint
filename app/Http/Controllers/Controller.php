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
    private function tableTemplate()
    {
        return (new TableBuilder(new TablePresenter()))
            ->column(
                'Name',
                [],
                Object::getProp('name')
            )
            ->column(
                'Colors',
                ['class' => 'text-right'],
                ViewHelper::countRelations('colors')
            )
            ->column(
                'Lenses',
                ['class' => 'text-right'],
                ViewHelper::countRelations('lenses')
            )
            ->column(
                'Price',
                ['class' => 'text-right'],
                Lambda::compose(ViewHelper::formatCurrency(), Object::getProp('price'))
            )
            ->column(
                '',
                ['class' => 'actions'],
                function($frame) {
                    return Strings::join('', [
                        ViewHelper::button('Edit', [], route('admin.frames.show', ['id' => $frame->id])),
                        ViewHelper::deleteButton(
                            route('admin.frames.destroy', ['id' => $frame->id]),
                            'Are you sure you want to delete this frame? This action cannot be undone.'
                        )
                    ]);
                }
            );
    }
}
