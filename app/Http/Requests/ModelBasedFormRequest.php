<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ModelBasedFormRequest extends FormRequest
{
    protected function get_from_model($name) {
      $class = get_class($this);

      if (strpos($class, 'Store')) {
        $type = 'store';
      } else if (strpos($class, 'Update')) {
        $type = 'update';
      } else {
        $type = 'default';
      }

      $model = 'App\\Models\\'. strtr($class, [
        'App\\Http\\Requests\\' => '',
        'StoreRequest' => '',
        'UpdateRequest' => '',
        'Request' => '' ,
      ]);

      $prefix = $model::get_validation_rules('prefix');

      if ($prefix) {
        return \App\Html::prefix_array(
          $prefix,
          $model::get_validation_rules($name, $type),
          '.'
        );
      }

      return $model::get_validation_rules($name, $type);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules() {
      return $this->get_from_model(__FUNCTION__);
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages() {
      return $this->get_from_model(__FUNCTION__);
    }

    public function attributes()  {
      return $this->get_from_model(__FUNCTION__);
    }
}
