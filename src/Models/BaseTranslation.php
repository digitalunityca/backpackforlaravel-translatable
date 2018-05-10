<?php

namespace DigitalUnity\Translatable\Models;

use Backpack\LangFileManager\app\Models\Language;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class BaseTranslation extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'locale_id',
        'entity_id',
        'field',
        'slug',
        'value',
    ];


    /**
     * Get value by Field
     * @return mixed
     */
    public function scopeByField(Builder $query, $field)
    {
        return $query->where('field', $field)
                     ->where('locale_id', appLocaleId());
    }

    /**
     * Get all values by Field
     * @return mixed
     */
    public function scopeByFieldAll(Builder $query, $field)
    {
        return $query->where('field', $field);
    }

    /**
     * Get value by SLUG
     * @return mixed
     */
    public function scopeBySlug(Builder $query, $slug, $field = 'name')
    {
        return $query->where('slug', $slug)
                     ->where('field', $field);
    }

    /**
     * Get value by active locale SLUG
     * @return mixed
     */
    public function scopeByActiveSlug(Builder $query, $slug, $field = 'name')
    {
        return $query->BySlug($slug,$field)->ByActiveLocale();
    }

    /**
     * Get value by Active locale
     * @return mixed
     */
    public function scopeByActiveLocale(Builder $query)
    {
        return $query->where('locale_id', appLocaleId());
    }

    /**
     * Get value by values arrats
     * @return mixed
     */
    public function scopeByValues(Builder $query, string $field, array $values)
    {
        return $query->whereIn('value', $values)
                     ->where('field', $field);
    }


    /**
     * Get value by Field and Locale / LocaleId
     * @return mixed
     */
    public function scopeByFieldAndLocaleId(Builder $query, $field, $locale)
    {
        if (is_int($locale)) {
            $localeId = $locale;
        } else {
            $language = Language::where('abbr', $locale)->first();
            $localeId = $language->id;
        }

        return $query->where('field', $field)
                     ->where('locale_id', $localeId);
    }

    /**
     * Get value by Field,Value and Locale / LocaleId
     * @return mixed
     */
    public function scopeByFieldValueLocaleId(Builder $query, $value, $field, $locale)
    {
        if (is_int($locale)) {
            $localeId = $locale;
        } else {
            $language = Language::where('abbr', $locale)->first();
            $localeId = $language->id;
        }

        return $query->where('field', $field)
                     ->where('value', $value)
                     ->where('locale_id', $localeId);
    }

    /**
     * Get value by Field,Value and Locale / LocaleId
     * @return mixed
     */
    public function scopeByFieldSlugLocaleId(Builder $query, $value, $field, $locale=null)
    {
        if (is_null($locale)) {
            $localeId = appFrontLocale(true);
        } else {
            if (is_int($locale)) {
                $localeId = $locale;
            } else {
                $language = Language::where('abbr', $locale)->first();
                $localeId = $language->id;
            }
        }

        return $query->where('field', $field)
                     ->where('slug', $value)
                     ->where('locale_id', $localeId);
    }

    /**
     * Get value by value
     * @return mixed
     */
    public function scopeByValueLike(Builder $query, $value)
    {
        return $query->where('value', 'LIKE', '%'.$value.'%');
    }

    /**
     * Get value by value
     * @return mixed
     */
    public function scopeByValue(Builder $query, $value)
    {
        return $query->where('value', $value);
    }
}
