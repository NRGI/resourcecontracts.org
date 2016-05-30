<?php namespace App\Http\Middleware;

use App\Nrgi\Services\Language\LanguageService;
use Closure;

/**
 * Class Localization
 * @package App\Http\Middleware
 */
class Localization
{
    /**
     * @var LanguageService
     */
    protected $lang;

    /**
     * Localization constructor.
     *
     * @param LanguageService $lang
     */
    public function __construct(LanguageService $lang)
    {
        $this->lang = $lang;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure                 $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $lang = $this->lang->getSiteLang($request->input('lang'));
        $this->lang->setSiteLang($lang);

        return $next($request);
    }
}
