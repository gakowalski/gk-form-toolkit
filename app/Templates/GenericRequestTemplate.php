<?php

$script = basename(__FILE__);
$target = $argv[1];
$type = $argv[2] ?? 'Update';
$command = $argv[3] ?? null;

if ($command != 'stop') {
  `php $script $target Update stop > ..\\Http\\Requests\\{$target}UpdateRequest.php`;
  `php $script $target Store stop > ..\\Http\\Requests\\{$target}StoreRequest.php`;
  exit;
}

$php_tag = '<' . '?php'; //< written in safe way
?><?= $php_tag."\n" ?>

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class <?= $target.$type ?>Request extends ModelBasedFormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }
}
