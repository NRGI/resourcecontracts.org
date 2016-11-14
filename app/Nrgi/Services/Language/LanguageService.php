<?php namespace App\Nrgi\Services\Language;

use Carbon\Carbon;
use Illuminate\Http\Request;

/**
 * Class LanguageService
 * @package App\Nrgi\Services\Language
 */
class LanguageService
{
    /**
     * @var Carbon
     */
    protected $carbon;

    /**
     * @var string
     */
    protected $key = 'rc_admin_lang';
    /**
     * @var Request
     */
    protected $request;

    /**
     * LanguageService constructor.
     *
     * @param Carbon  $carbon
     * @param Request $request
     */
    public function __construct(Carbon $carbon, Request $request)
    {
        $this->carbon  = $carbon;
        $this->request = $request;
    }

    /**
     * Get Available Languages
     *
     * @return object
     */
    public function getAvailableLang()
    {
        return config('lang.list');
    }

    /**
     * Get Default Language
     *
     * @return string
     */
    public function defaultLang()
    {
        $code = $this->browserLang();

        if (!is_null($code) && $this->isValidLang($code)) {
            return $code;
        }

        return config('lang.default');
    }

    /**
     * Get Language code
     *
     * @param null $lang
     *
     * @return string
     */
    public function getSiteLang($lang = null)
    {
        if (empty($lang)) {
            $lang = isset($_COOKIE[$this->key]) ? $_COOKIE[$this->key] : $this->defaultLang();
        }

        return $lang;
    }

    /**
     * Set Language code
     *
     * @param $lang
     *
     * @return Void
     */
    public function setSiteLang($lang)
    {
        $lang = $this->getValidLang($lang);
        app()->setLocale($lang);
        setcookie($this->key, $lang, $this->carbon->now()->addYear(1)->timestamp, '/');
    }

    /**
     * Get Valid Language Code.
     *
     * @param $lang
     *
     * @return string
     */
    public function getValidLang($lang)
    {
        $lang = trim(strtolower($lang));

        if ($this->isValidLang($lang)) {
            return $lang;
        }

        return $this->defaultLang();
    }

    /**
     * write brief description
     * @return array
     */
    public function current()
    {
        $code = app()->getLocale();

        return (object) $this->getLangInfo($code);
    }

    /**
     * Get Direction of language
     *
     * @return array
     */
    public function dir()
    {
        $info = $this->current();

        return isset($info->dir) ? $info->dir : 'ltr';
    }

    /**
     * Get Language info
     *
     * @param $code
     *
     * @return array
     */
    public function getLangInfo($code)
    {
        foreach ($this->getAvailableLang() as $lang) {
            if ($lang['code'] == $code) {
                return $lang;
            }
        }

        return [];
    }

    /**
     * Translation languages
     *
     * @return array
     */
    public function translation_lang()
    {
        return config('lang.translation');
    }

    /**
     * Get Current Translated lang
     *
     * @return string
     */
    public function current_translation()
    {
        $code = $this->request->route()->getParameter('lang');

        if (!is_null($code) && $this->isValidTranslationLang($code)) {
            return $code;
        }

        return $this->defaultLang();
    }

    /**
     * Check for valid Translation language
     *
     * @param $code
     *
     * @return bool
     */
    public function isValidTranslationLang($code)
    {
        foreach (config('lang.translation') as $lang) {
            if ($lang['code'] == $code) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check for valid Language code
     *
     * @param $lang
     *
     * @return bool
     */
    protected function isValidLang($lang)
    {
        $info = $this->getLangInfo($lang);

        return empty($info) ? false : true;
    }

    /**
     * Get Browser Language
     *
     * @return string|null
     */
    protected function browserLang()
    {
        if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
            return substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
        }

        return null;
    }

}
