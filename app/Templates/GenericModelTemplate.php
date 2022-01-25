<?php

$script = basename(__FILE__);
$target = $argv[1];
$target_lowercase = strtolower($target);
$command = $argv[2] ?? null;

if ($command != 'stop') {
  `php $script $target stop > ..\\Models\\{$target}.php`;
  exit;
}

$php_tag = '<' . '?php'; //< written in safe way
?><?= $php_tag."\n" ?>

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class <?= $target ?> extends Model
{
    //use SoftDeletes;
    //protected $table = 'table_name';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [

    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [

    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [

    ];

    public static $validation = [
      'prefix' => [
        'default' => ' <?= $target_lowercase ?>.',
      ],
      'rules' => [
        'default' => [
          '<?= $target_lowercase ?>.*' => ['sometimes'],
        ],
      ],
      'messages' => [
        'default' => [

        ],
      ],
      'attributes' => [
        'default' => [

        ],
      ],
    ];

    public static function get_validation_rules($name, $type = 'default') {
      return self::$validation[$name][$type] ?? self::$validation[$name]['default'];
    }
}
