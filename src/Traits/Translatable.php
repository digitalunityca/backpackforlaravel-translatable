<?php
namespace DigitalUnity\Translatable\Traits;

use Illuminate\Database\Eloquent\Builder;
use Imvescor\Admin\App\Models\BaseTranslation;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Symfony\Component\Console\Exception\LogicException;

trait Translatable
{

    /**
     * Return translatable fields
     * @return mixed
     */
    public function getTranslatableFields()
    {
        return $this->translatable;
    }

    /**
     * Override __get() to get the translations
     *
     * @param string $property
     * @return mixed
     */
    public function __get($key)
    {
        /**
         * Get localized format for active lang
         */
        if (isset($this->translatable) && in_array($key, $this->translatable)) {

            if (!$this->translations){
                $this->load('translations');
            }

            $translations = $this->translations;

            $translation = $translations->where('field',$key)
                ->where('entity_id', $this->id)
                ->where('locale_id', appFrontLocale(true))
                ->first();

            if (is_null($translation)) {
                return '';
            }

            return $translation->value;
        }

        /**
         * Get localized input__locale format key
         * Ex. description__en, title__fr
         */
        if (isLocalizedInput($key)) {
            $matches = getLocalePatternMatches($key);

            // get the translation
            $translation = $this->translations()->ByFieldAndLocaleId($matches[1], $matches[2])->where('entity_id', $this->id)->first();

            if (is_null($translation)) {
                return '';
            }

            return $translation->value;
        }

        return $this->getAttribute($key);
    }

    /**
     * Get slug
     *
     * @param string $property
     * @return mixed
     */
    public function __slug($key='name')
    {
        if (isset($this->translatable) && in_array($key, $this->translatable)) {
            // get the translation
            $translation = $this->translations()->ByField($key)->where('entity_id', $this->id)->first();

            if (is_null($translation)) {
                return '';
            }

            return $translation->slug;
        }

        return '';
    }

    /**
     * Save translations for model
     * @return void
     */
    public function saveTranslations($nameField = null)
    {
//        $this->addLocalizedAddressInRequest();

        foreach (request()->all() as $field=>$value) {
            if (isLocalizedInput($field) && $value) {
                $matches = getLocalePatternMatches($field);
                $localeId = localeId($matches['2']);

                $translation = $this->translations()->firstOrCreate([
                    'entity_id' =>  $this->id,
                    'field'     =>  $matches[1],
                    'locale_id' =>  $localeId
                ]);

                // if is uploaded file
                if ($value instanceof UploadedFile) {
                    /** @var UploadedFile $uploadedFile */
                    $uploadedFile = $value;
                    $value = '/'.\Storage::putFile('uploads/'.str_replace('_', '/', $matches[1]), $uploadedFile);

                    $slug = null;
                } else {
                    $slug = substr(str_slug($value), 0, 150);
                }

                if (!is_null($nameField) && $field==$nameField && $localeId==1){
                    // save __name in main table
                    $this->update([
                        '__name' => $value
                    ]);
                }

                $translation->update([
                    'value' =>  $value,
                    'slug'  =>  $slug
                ]);
            }
        }
    }

    /**
     *
     * @param string $field
     * @param $locale
     */
    public function getTranslation(string $field, $localeId)
    {
        if (is_string($localeId)) {
            $localeId = localeId($localeId);
        }

        $translation = $this->translations()->byFieldAndLocaleId($field, $localeId)->first();

        if (is_null($translation)) {
            return null;
        }

        return $translation->value;
    }

    /**
     *
     * @param string $field
     * @param $locale
     */
    public function getTranslationSlug(string $field, $localeId = null)
    {
        if (is_null($localeId)) {
            $localeId = appFrontLocale(true);
        }

        $translation = $this->translations()->byFieldAndLocaleId($field, $localeId)->first();

        if (is_null($translation)) {
            return null;
        }

        return $translation->slug;
    }


    /**
     *
     * @param string $field
     * @param $locale
     */
    public function getTranslationValueForField(string $field, $localeId = null)
    {
        if (is_null($localeId)) {
            $localeId = appFrontLocale(true);
        }

        $translation = $this->translations()->byFieldAndLocaleId($field, $localeId)->first();

        if (is_null($translation)) {
            return null;
        }

        return $translation->value;
    }

    /**
     * Find model by translation locale value
     * @param Builder $query
     * @param string $slug
     * @return Builder
     */
    public function scopeByTranslation($query, $field, $value, $localeId=null)
    {
        if (is_null($localeId)) {
            $localeId = appLocaleId();
        }

        if (is_string($localeId)) {
            $localeId = localeId($localeId);
        }

        return $query->whereHas('translations', function ($q) use ($field, $value, $localeId) {
            $q->ByFieldValueLocaleId($value, $field, $localeId);
        });
    }

    /**
     * Find model by translation locale slug
     * @param Builder $query
     * @param string $slug
     * @return Builder
     */
    public function scopeByTranslationSlug($query, $field, $slug, $localeId=null)
    {
        if (is_null($localeId)) {
            $localeId = appFrontLocale(true);
        }

        if (is_string($localeId)) {
            $localeId = localeId($localeId);
        }

        return $query->whereHas('translations', function ($q) use ($field, $slug, $localeId) {
            $q->ByFieldSlugLocaleId($slug, $field, $localeId);
        });
    }

    /**
     * Find model by translation  slug
     * @param Builder $query
     * @param string $slug
     * @return Builder
     */
    public function scopeBySlugNoLocale($query, $slug, $field='name')
    {
        return $query->whereHas('translations', function ($q) use ($field, $slug) {
            $q->BySlug($slug, $field);
        });
    }

    /**
     * Search by field
     * @param Builder $query
     * @param $field
     * @param $value
     */
    public function scopeWhereTranslationLike(Builder $query, $field, $searchTerm)
    {
        return $query->whereHas('translations', function (Builder $q) use ($field, $searchTerm) {
            return $q->ByField($field)->where('value','LIKE','%'.$searchTerm.'%');
        });
    }

    /**
     * Search by name in translation
     *
     * @param Builder $query
     * @param $searchTerm
     * @return mixed
     */
    public function scopeSearchByName(Builder $query, $searchTerm)
    {
        return $query->whereTranslationLike('name',$searchTerm);
    }

    /**
     * Search by field in translation
     *
     * @param Builder $query
     * @param $searchTerm
     * @return mixed
     */
    public function scopeSearchByField(Builder $query, $field, $searchTerm)
    {
        return $query->whereTranslationLike($field,$searchTerm);
    }


}
