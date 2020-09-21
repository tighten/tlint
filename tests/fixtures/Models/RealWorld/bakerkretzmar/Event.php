<?php

namespace App;

use App\Enums\EventTypes;
use BenSampo\Enum\Traits\CastsEnums;
use Laravel\Nova\Actions\Actionable;
use Laravel\Scout\Searchable;

class Event extends Model
{
    use Actionable,
        CastsEnums,
        Concerns\Featurable,
        Concerns\Flaggable,
        Concerns\HasCoordinates,
        Concerns\HasPreviewImage,
        Concerns\HasTimeslots,
        Concerns\Publishable,
        Concerns\Validatable,
        Searchable;

    public $translatable = [
        'name',
        'description',
        'organizer_name',
        'organizer_description',
        'place_name',
        'directions',
    ];

    protected $attributes = [
        'files' => '[]',
        'links' => '[]',
        'times' => '[]',
        'is_submitter_contact' => true,
        'languages' => '[]',
        'accessibility' => '[]',
    ];

    protected $casts = [
        'covid_terms' => 'boolean',
        'files' => 'array',
        'links' => 'array',
        'times' => 'array',
        'is_submitter_contact' => 'boolean',
        'languages' => 'array',
        'accessibility' => 'array',
        'is_pwyc' => 'boolean',
        'unbounded' => 'boolean',
        'type' => 'integer',
    ];

    protected $enumCasts = [
        'type' => EventTypes::class,
    ];

    protected $hidden = [
        'notes',
    ];

    public static function draftRules(): array
    {
        return [
            'name.en' => [],
            'name.fr' => [],
            'files' => ['array'],
            'description.en' => [],
            'description.fr' => [],
            'type' => [],
            'covid_terms' => [],
            'times' => ['array', 'max:6'],
            'times.*.start' => [],
            'times.*.end' => [],
            'unbounded' => [],
            'tag_slugs' => ['array', 'max:5'],
            'links' => ['array'],
            'links.*.en' => [],
            'links.*.fr' => [],
            'links.*.url' => [],
            'video' => ['max:255'],
            'recording' => ['max:255'],
            'coordinates' => ['array'],
            'place_name.en' => [],
            'place_name.fr' => [],
            'address' => ['max:255'],
            'city' => ['max:255'],
            'province_id' => ['required', 'enum_value:App\Enums\Provinces,false'],
            'directions.en' => [],
            'directions.fr' => [],
            'organizer_name.en' => [],
            'organizer_name.fr' => [],
            'organizer_description.en' => [],
            'organizer_description.fr' => [],
            'organizer_type' => ['nullable'],
            'is_submitter_contact' => ['boolean'],
            'contact_name' => ['max:255'],
            'contact_email' => ['max:255'],
            'contact_phone' => ['max:30'],
            'languages' => ['array'],
            'accessibility' => ['array'],
            'accessibility_other' => ['max:255'],
            'is_pwyc' => ['boolean'],
        ];
    }

    public static function publishRules(): array
    {
        return array_merge_recursive(static::draftRules(), [
            'name.en' => ['required_without:name.fr', 'max:100'],
            'name.fr' => ['required_without:name.en', 'max:100'],
            'files' => ['required', 'max:4'],
            'description.en' => [
                'required_without:description.fr',
                'min_words_without:description.fr,30',
                'max_words:500',
            ],
            'description.fr' => [
                'required_without:description.en',
                'min_words_without:description.en,30',
                'max_words:500',
            ],
            'type' => ['required', 'enum_value:App\Enums\EventTypes,false'],
            'covid_terms' => ['exclude_unless:type,' . EventTypes::InPerson, 'accepted'],
            'times' => [
                'required_if:require_times,true',
                'exclude_if:unbounded,true',
            ],
            'times.*.start' => [
                'bail',
                'required',
                'date',
                'before:times.*.end',
                'on_culturedays',
            ],
            'times.*.end' => [
                'bail',
                'required',
                'date',
                'after:times.*.start',
                'on_culturedays',
            ],
            'unbounded' => ['nullable', 'boolean'],
            'links' => ['max:4'],
            'links.*.en' => ['required_without:links.*.fr', 'max:100'],
            'links.*.fr' => ['required_without:links.*.en', 'max:100'],
            'links.*.url' => ['required', 'active_url'],
            'video' => ['nullable', 'active_url'],
            'recording' => ['nullable', 'active_url'],
            'coordinates.*' => ['required'],
            'address' => ['max:100'],
            'city' => ['required', 'max:100'],
            'place_name.en' => ['max:100'],
            'place_name.fr' => ['max:100'],
            'directions.en' => ['max_words:100'],
            'directions.fr' => ['max_words:100'],
            'organizer_name.en' => ['required_without:organizer_name.fr', 'max:100'],
            'organizer_name.fr' => ['required_without:organizer_name.en', 'max:100'],
            'organizer_description.en' => [
                'required_without:organizer_description.fr',
                // 'min_words_without:organizer_description.fr,30',
                'max_words:500',
            ],
            'organizer_description.fr' => [
                'required_without:organizer_description.en',
                // 'min_words_without:organizer_description.en,30',
                'max_words:500',
            ],
            'organizer_type' => ['required', 'enum_value:App\Enums\Organizers,false'],
            'contact_name' => ['required_if:is_submitter_contact,0', 'max:100'],
            'contact_email' => ['nullable', 'required_if:is_submitter_contact,0', 'email:filter'],
            'languages' => ['required', 'min:1'],
            'languages.*' => ['not_regex:/\b(the|and|or|any|no)\b/i']
        ]);
    }

    public function collections()
    {
        return $this->belongsToMany(Collection::class)
            ->using(CollectionEvent::class)
            ->withPivot(['id', 'order']);
    }

    public function hubs()
    {
        return $this->belongsToMany(Hub::class)
            ->using(EventHub::class)->as('invitation')
            ->withPivot(['id', 'accepted_at', 'declined_at'])
            ->withTimestamps();
    }

    public function province()
    {
        return $this->belongsTo(Province::class);
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getTagSlugsAttribute(): array
    {
        return $this->tags->pluck('slug')->all();
    }

    public function getHubAttribute(): ?Hub
    {
        return $this->hubs->where('published_at', '!=', null)->firstWhere('invitation.accepted_at', '!=', null);
    }

    public function setTagSlugsAttribute(array $tags)
    {
        $this->tags()->sync(Tag::whereIn('slug', $tags)->get());
    }

    /**
     * Get the event's searchable data.
     */
    public function toSearchableArray(): array
    {
        return $this->transform(array_merge([
            'type' => 'event',
        ], $this->only([
            'id',
            'uuid',
            'name',
            'description',
            'files',
            'place_name',
            'city',
            'status',
            'is_featured',
            'days',
            'times_by_day',
            'organizer_name',
            'languages',
            'accessibility',
            'published_at',
        ]), [
            'event_type' => optional($this->type)->value,
            'last_end_time_stamp' => optional($this->last_end_time)->timestamp,
            'unbounded' => $this->unbounded || optional($this->type)->is(EventTypes::DigitalRecording),
            'tags' => $this->tag_slugs,
            'province' => $this->province->slug,
            'recording' => filled($this->recording),
            '_geoloc' => [
                'lat' => (float) $this->latitude,
                'lng' => (float) $this->longitude,
            ],
        ]));
    }

    public function toGeoJson()
    {
        return [
            'type' => 'Feature',
            'geometry' => [
                'type' => 'Point',
                'coordinates' => [(float) $this->longitude, (float) $this->latitude],
            ],
            'properties' => [
                'icon' => 'circle',
                'uuid' => $this->uuid,
                'name' => $this->name,
                'preview_image' => $this->preview_image_url,
                'organizer_name' => $this->organizer_name,
                'hub' => optional($this->hub)->only(['name', 'uuid']),
            ],
        ];
    }
}
