<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ExportColumn;

class GenericAppController extends Controller
{
    /*
      You have to create in your controller:

      public $default_field_array = 'products';
      public $default_view_prefix = 'product';
      public $default_route_prefix = 'product';
      public $default_export_name = 'products';

      Also, you have to implement:

      public function model_query() {
        return \App\Product::query();
      }

      public function model_new() {
        return new \App\Product;
      }

      public function model_create($array) {
        return \App\Product::create($array);
      }

      in web.php you should use

      Route::resource('product', 'ProductController')->middleware('auth');

    */

    public function model_query() {
      return null;
    }

    public function model_new() {
      return null;
    }

    public function model_create($array) {
      return null;
    }

    public function field_array_name() {
      return $this->default_field_array ?? null;
    }

    public function view_prefix() {
      return $this->default_view_prefix ?? null;
    }

    public function route_prefix() {
      return $this->default_route_prefix ?? null;
    }

    public function export_name() {
      return $this->default_export_name ?? 'default';
    }

    public function standard_view($name, $data) {
      $view_prefix = $this->view_prefix();
      return view("$view_prefix.$name", [
        'default_route' => $this->route_prefix(),
        'default_array' => $this->field_array_name(),
      ] + ($name != 'index' ? [
        'row' => $data,
      ] : [
        'rows' => $data,
      ]));
    }

    public function redirect_to_index() {
      return redirect()->route($this->route_prefix().'.index');
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        return $this->standard_view('edit', $this->model_new());
    }

    public function xlsx(Request $request) {
          $safe_variables =  ['row', 'custom_export_data']; //< add all main variables that can start the export path

          $query = $this->model_query()->orderByDesc('id');

          if ($request->has('export')) {
            $search = $request->export;

            if (\Arr::get($search, 'date_from', null)) {
              $query->where('data_wyjscia', '>=', \Arr::get($search, 'date_from'));
            }

            if (\Arr::get($search, 'date_to', null)) {
              $query->where('data_wyjscia', '<=', \Arr::get($search, 'date_to'));
            }
          }

          $rows = $query->get();

          $date = date('Y-m-d H:i:s');

          $columns = ExportColumn::where('group_name', $this->route_prefix())->orderBy('order')->orderBy('id')->get();

          $writer = new \XLSXWriter();
          $writer->setTitle("Eksport danych z dnia $date");
          $writer->setCompany('Polska Organizacja Turystyczna');
          $writer->setAuthor(\Auth::user()->email);

          // first row contains column names
          $header_style = ['font-style' => 'bold'];
          $column_names = $columns->pluck('name')->toArray();
          $writer->writeSheetRow('Dane', $column_names, $header_style);

          // generate all the data rows
          foreach ($rows as $row) {
            $output_row = [];

            if (method_exists($row, 'custom_export_data')) {
              $custom_export_data = $row->custom_export_data();
            } else {
              $custom_export_data = [];
            }

            foreach ($columns as $column) {
              $decoded = explode('.', $column->path);

              $variable_reference = null;

              foreach ($decoded as $variable_name) {
                if ($variable_reference !== null) {
                  if (isset($variable_reference->$variable_name)) {
                    $variable_reference = $variable_reference->$variable_name;
                  } else if (is_array($variable_reference) && isset($variable_reference[$variable_name])) {

                    $variable_reference = $variable_reference[$variable_name];
                  } else {
                    $variable_reference = null;
                    break;
                  }
                }

                if ($variable_reference === null && isset($$variable_name) && in_array($variable_name, $safe_variables)) {
                  $variable_reference = $$variable_name;
                }
              }

              $output_row[] = ($variable_reference instanceof \Illuminate\Support\Carbon) ? $variable_reference->format('Y-m-d') : $variable_reference;
            }
            $writer->writeSheetRow('Dane', $output_row);
          }

          $export_name = $this->export_name();
          header('Content-Description: File Transfer');
          header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
          header("Content-Transfer-Encoding: binary");
          header("Content-Disposition: attachment;filename={$export_name}_{$date}.xlsx");
          header("Pragma: no-cache");
          header("Expires: 0");
          $writer->writeToStdOut();

          exit();
      }
}
