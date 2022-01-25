<?php

$script = basename(__FILE__);
$prefix = explode('Controller', $script)[0];
$controller = $prefix . 'Controller.php';
$field_name = strtolower($prefix);
$variable = '$' . $field_name;

if ($argv[1] == 'make') {
  $prefix = $argv[2];
  $new_name = $prefix . 'ControllerTemplate.php';
  copy($script, $new_name);
  $script = $new_name;
  $controller = $prefix . 'Controller.php';
}
if ($argv[1] != 'stop') {
  `php $script stop > ..\\Http\\Controllers\\$controller`;
  `php GenericRequestTemplate.php $prefix`;
  `php GenericModelTemplate.php $prefix`;
  exit;
}

function prefix($text) {
  global $prefix;
  return strtr($text, [
    '__' => $prefix,
  ]);
}

$php_tag = '<' . '?php'; //< written in safe way
$namespace = 'App\Http\Controllers';
$use_array = [
  prefix('App\Http\Requests\__StoreRequest'),
  prefix('App\Http\Requests\__UpdateRequest'),
  prefix('App\Models\__'),
  'Illuminate\Http\Request',
];

?><?= $php_tag."\n" ?>

namespace <?= $namespace ?>;

<?php foreach ($use_array as $use) echo "use $use;\n"; ?>

class <?= $prefix ?>Controller extends GenericAppController
{
    public $default_field_array   = '<?= $field_name ?>';
    public $default_view_prefix   = '<?= $field_name ?>';
    public $default_route_prefix  = '<?= $field_name ?>';
    public $default_export_name   = '<?= $field_name ?>';

    public function model_query() {
      return <?= $prefix ?>::query();
    }

    public function model_new() {
      return new <?= $prefix ?>;
    }

    public function model_create($array) {
      return <?= $prefix ?>::create($array);
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = $this->model_query()->with([])->orderByDesc('id');

        if ($request->has('search')) {
          $search = $request->search;

          if ($v = $request->input('search.nazwa', null)) {
            $query->whereHas('organization', function ($q) use ($v) {
              $q->where('name', 'like', "%$v%");
            });
          }
          if ($v = $request->input('search.data_od', null)) {
            $query->where('updated_at', '>=', $v);
          }
          if ($v = $request->input('search.data_do', null)) {
            $query->where('updated_at', '>=', $v);
          }
          if ($v = $request->input('search.id', null)) {
            $query->where('id', $v);
          }
          if ($v = $request->input('search.status', null)) {
            $query->where('state_id', $v);
          }
          if ($v = $request->input('search.stars', null)) {
            $query->where('stars', $v);
          }

          $request->flash();
        }

        return $this->standard_view('index', $query->paginate(25));
    }

    /**
     * @param \App\Http\Requests\<?= $prefix ?>StoreRequest $request
     * @return \Illuminate\Http\Response
     */
    public function store(<?= $prefix ?>StoreRequest $request)
    {
        <?= $variable ?> = $this->model_create($request->validated()[$this->default_field_array] + ['user_id' => \Auth::user()->id ]);

        $request->session()->flash('<?= $field_name ?>.id', <?= $variable ?>->id);

        return $this->redirect_to_index();
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\<?= $prefix ?> <?= $variable."\n" ?>
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, <?= $prefix ?> <?= $variable ?>)
    {
      return $this->standard_view('show', <?= $variable ?>);
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\<?= $prefix ?> <?= $variable."\n" ?>
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request, <?= $prefix ?> <?= $variable ?>)
    {
        return $this->standard_view('edit', <?= $variable ?>);
    }

    /**
     * @param \App\Http\Requests\<?= $prefix ?>UpdateRequest $request
     * @param \App\Models\<?= $prefix ?> <?= $variable."\n" ?>
     * @return \Illuminate\Http\Response
     */
    public function update(<?= $prefix ?>UpdateRequest $request, <?= $prefix ?> <?= $variable ?>)
    {
        <?= $variable ?>->update($request->validated()[$this->default_field_array]);

        $request->session()->flash('<?= $field_name ?>.id', <?= $variable ?>->id);

        return $this->redirect_to_index();
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\<?= $prefix ?> <?= $variable."\n" ?>
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, <?= $prefix ?> <?= $variable ?>)
    {
        <?= $variable ?>->delete();
        return $this->redirect_to_index();
    }
}
