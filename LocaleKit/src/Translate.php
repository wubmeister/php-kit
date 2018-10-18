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

    public function translateFormat($message, ...$args)
    {
        $numReplace = 0;
        $pos = 0;
        while (($pos = strpos($message, '%', $pos)) !== false) {
            $numReplace++;
            $pos++;
        }

        $replace = array_slice($args, 0, $numReplace);
        $argTail = array_slice($args, $numReplace);

        $message = $this->translate($message, count($argTail) > 0 ? $argTail[0] : 'default', count($argTail) > 1 ? $argTail[1] : null);

        $pos = 0;
        while (($pos = strpos($message, '%')) !== false) {
            $message = substr_replace($message, array_shift($replace), $pos, 1);
        }

        return $message;
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
