<?php

namespace CoreKit;

class FlashMessage
{
    const INFO = 'info';
    const SUCCESS = 'success';
    const WARNING = 'warning';
    const ERROR = 'error';

    public static function getAll()
    {
        if (!isset($_SESSION) || !isset($_SESSION['flash'])) {
            return [];
        }

        $result = $_SESSION['flash'];
        $messages = [];
        foreach ($result as $message) {
            if (!$message['expires'] || $message['expires'] < time()) {
                $messages[] = $message;
            }
        }

        return $message;
    }

    public static function clear()
    {
        if (!isset($_SESSION) || !isset($_SESSION['flash'])) {
            return;
        }
        unset($_SESSION['flash']);
    }

    public static function add(string $type, string $message, $expires = null)
    {
        if (!isset($_SESSION)) {
            // session_start() not called, probably
            return;
        }

        if ($expires) {
            if (!is_numeric($expires)) {
                if (($time = strtotime($expires)) === false) {
                    // Assume time interval
                    $i = DateInterval::createFromDateString('1 day');
                    if ($i) {
                        $date = (new DateTime()).add($i);
                        if ($date) {
                            $time = $date->getTimestamp();
                        }
                    }
                }
                if ($time) {
                    $expires = $time;
                } else {
                    $expires = null;
                }
            }
        }

        if (!isset($_SESSION['flash'])) {
            $_SESSION['flash'] = [];
        }
        $_SESSION['flash'][] = [
            'type' => $type,
            'message' => $message,
            'expires' => $expires
        ];
    }
}
