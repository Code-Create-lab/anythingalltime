<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'settings';

    public static function valActDeMode(): bool
    {
        /* die(HVAL); */
        $demK = get_option('demode_authorization');
        if ($demK == '') {
            $demK = getenv('APP_DEMO');
        }
        if (! isset($demK) || ! defined('HVAL') || $demK !== HVAL) {
            return false;
        }

        return false;
    }
}
