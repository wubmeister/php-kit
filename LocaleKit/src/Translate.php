<?php

namespace LocaleKit;

class Translate
{
    protected $translations = [];
    protected $domains = [];
    protected $locale;

    public function registerDomain($domain, $path)
    {
        $this->domains[$domain] = $path;
    }

    public function setLocale($locale)
    {
        $this->locale = $locale;
    }

    public function translate($message, $domain = 'default', $locale = null)
    {
        if (!$locale) $locale = $this->locale;
        if (!isset($this->translations[$locale]) || !isset($this->translations[$locale][$domain])) {
            $this->loadTranslations($domain, $locale);
        }

        if (!isset($this->translations[$locale][$domain][$message])) {
            return $message;
        }

        return $this->translations[$locale][$domain][$message];
    }

    protected function loadTranslations($domain, $locale)
    {
        if (!isset($this->domains[$domain])) {
            throw new Exception('Domain not registered: \'' . $domain . '\'');
        }

        $path = rtrim($this->domains[$domain], '/') . '/' . $locale . '.php';
        if (!isset($this->translations[$locale])) {
            $this->translations[$locale] = [];
        }

        $this->translations[$locale][$domain] = include $path;
    }
}
