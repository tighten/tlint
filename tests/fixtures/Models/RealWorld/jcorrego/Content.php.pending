<?php

namespace App\XXX\Contents;

use Image;
use App\User;
use MediaUploader;
use Spatie\Tags\HasTags;
use Plank\Mediable\Media;
use Plank\Metable\Metable;
use Plank\Mediable\Mediable;
use App\XXX\Blog\Post;
use Illuminate\Database\Eloquent\Model;
use App\XXX\Challenges\ThematicArea;
use App\Http\Controllers\Traits\CommentableTrait;
use App\Http\Controllers\Traits\LikeableTrait;
use Plank\Mediable\SourceAdapters\SourceAdapterInterface;

// @todo: https://github.com/tighten/tlint/pull/169
class Content extends Model
{
    use Mediable, HasTags, Metable, CommentableTrait, LikeableTrait;

    protected $appends = ['main_image'];
    protected $guarded = [];
    protected $hidden = [];
    protected $casts = [
        'obstacles' => 'array',
        'lessons'   => 'array',
        'tools'     => 'array',
        'ideas'     => 'array',
        'case'      => 'array',
        'trends'    => 'array',
    ];


    public function posts()
    {
        return $this->belongsToMany(Post::class)->withTimestamps();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class)->withPivot('count', 'rate')->withTimestamps();
    }

    public function thematicArea()
    {
        return $this->belongsTo(ThematicArea::class, 'thematic_area_id');
    }

    public function getMainImageAttribute()
    {
        if ($media = $this->getMedia('main-image')->first()) {
            return $media->getUrl();
        } else {

            $image = 'XXX/image-default.png';

            $media = MediaUploader::fromSource(url($image))->toDirectory('contents/' . $this->id)->upload();
            $this->syncMedia($media, 'main-image');

            return $media->getUrl();
        }
    }

    public function getMainImageSmallAttribute()
    {
        if ($media = $this->getMedia('main-image-small')->first()) {
            return $media->getUrl();
        } else if ($media = $this->getMedia('main-image')->first()) {
            $imageSmall = Image::make($media->getUrl())->resize(400, 225)->encode('png', 80);
            $mediaSmall    = MediaUploader::fromSource($imageSmall->stream('png'))->toDirectory('tutorials/' . $this->id)
                ->beforeSave(function (Media $model, SourceAdapterInterface $source) {
                    $model->setAttribute('user_id', $this->user_id ? $this->user_id : auth()->user()->id);
                })->upload();
            $this->syncMedia($mediaSmall, 'main-image-small');
            return $mediaSmall->getUrl();
        }

        return $this->getMainImageAttribute();

    }

    public function getUserRateAttribute()
    {
        if (auth()->user() && ($content_user = $this->users()->wherePivot('user_id', auth()->user()->id)->first())) {
            return $content_user->pivot->rate;
        }

        return null;
    }

    public function getMetaDescriptionAttribute()
    {
        return $this->getMeta('description', '');
    }

    public function getMetaCaseAttribute()
    {
        return $this->getMeta('success_case', '');
    }

    public function getMediaDescriptionAttribute()
    {
        $file = null;
        if ($media = $this->getMedia('description')->first()) {
            $file = collect([
                'id'   => $media->id,
                'url'  => $media->getUrl(),
                'name' => $media->basename,
                'size' => $media->readableSize(),
                'type' => $media->aggregate_type,
            ]);
        }

        return $file;
    }

    public function getMediaCaseAttribute()
    {
        $file = null;
        if ($media = $this->getMedia('success_case')->first()) {
            $file = collect([
                'id'   => $media->id,
                'url'  => $media->getUrl(),
                'name' => $media->basename,
                'size' => $media->readableSize(),
                'type' => $media->aggregate_type,
            ]);
        }

        return $file;
    }

}
