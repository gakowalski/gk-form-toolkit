<?php

/*

HTML Forms for Laravel-Bootstrap-JetStream-Tailwind

ver 0.0.4.2022.01.13
  breaking changes:
  - new rows created by user for editable_key_value input have inserted "0" in the first cell to prevent row border collapse

ver 0.0.3.20211118
  breaking changes:
  - input_rules for select_multiple do not apply for wrapping div element but to ALL checkboxes
  - readonly suddenly works for select_multiple (something might break if fast-copied input_rules were used)
  - readonly also works for select elements
  - url input type adds 'https://' on init if value not empty

  added new global modes:
  - "hidden" to produce hidden inputs
  - "jetstream" to produce Jetstream/Tailwind compatible form elements
  - "append" to generate elements and append them after regular ones
  - "readonly" to produce readonly inputs

  non-breakin changes:
  - added 'action' param for script_on_change() to specify desired action
  - select via radio buttons
  - input_rules for select_radio do not apply for wrapping div element but to ALL radio buttons
  - add classes for checkboxes in select_multiple and select_radio via 'options_classes'
  - fixed select_multiple JS script cause "Uncaught SyntaxError: Identifier 'initial_values' has already been declared"
  added field_id() and field_name() functions
  added details() for generating summary/details constructs

  added support for glightbox

ver 0.0.2.20210813
  for POST method always set encoding type to multipart/form-data
  (needed for proper file uploading)

ver 0.0.2.20210513
  support for inline styling in form_submit_buttons

ver 0.0.2.20210426
  support for "url" field

ver 0.0.2.20210409
  introduction of "modes"
  custom select default option label and rules
  potentially compatibility breaking fix at reading request classes

ver 0.0.1.20210331
  helper method for creating phone number link

ver 0.0.1.20210326
  fix for edge case of ignoring prefixes in prefix_array

ver 0.0.1.20210320
  convert special chars like & < > ' " to HTML entities in attribute values

ver 0.0.1.20210315
  trying to fix some edge case of generating opening tags without closing tags

ver 0.0.1.20210310
  added id element for hint tags

ver 0.0.1.20210108
  added form_submit_buttons

ver 0.0.1.20201231
  prefix_array can ignore already prefixed elements

ver 0.0.1.20201019
  added select_multilevel
    with support for three levels
    with blocked parent nodes (as optgroup elements and as disabled options)

ver 0.0.1.20201016
  added select_multiple

*/

namespace App;

use Illuminate\Database\Eloquent\Collection;

class Html {
  public static $vars = [];

   public $tag;
   public $attributes = [];
   public $content = '';

   static public $append_fields_callback = null;

   static public $_mode = [
     'labels' => true,            //< show labels
     'labels_inline' => false,    //< labels on left of input instead of above
     'placeholders' => false,     //< put label text in placeholder
     'required' => false,         //< make required fields
     'hidden' => false,           //< make hidden fields
     'jetstream' => false,        //< generate default Laravel Jetstream / Tailwind output code
     'append' => false,           //< append special fields generated by callback function set by set_append_func()
     'readonly' => false,         //< make input readonly
   ];

   public static function get_mode($mode) {
     return self::$_mode[$mode];
   }
   public static function set_mode($mode, $value) {
     self::$_mode[$mode] = $value;
     return '';
   }

   // construct tag with some content (text or inner html)
   // OR without content (and without closing tag) when $content is null
   public function __construct($tag, $content = '', $attributes = []) {
     $this->tag = $tag;
     $this->attributes = $attributes;
     $this->content = $content;
   }

   static public $_self_closing_tags = [
     'input', 'img', 'br', 'hr', 'meta', 'link', 'base', 'area', //< pure HTML
   ];

   public function __toString() {
     $res = '<'.$this->tag;
     foreach ($this->attributes as $key => $value) {
       if ($value === null && in_array($key, ['required', 'checked', 'selected', 'readonly', 'disabled'])) {
         $res .= " $key";
       } else {
         $value = htmlspecialchars($value, ENT_QUOTES);
         $res .= " $key='$value'";
       }
     }
     $res .= '>';
     if ($this->content !== null) {
       $res .= $this->content;
       $res .= '</'.$this->tag.'>';
     } else if (false === in_array($this->tag, self::$_self_closing_tags)) {
       $res .= '</'.$this->tag.'>';
     }
     return $res;
   }

   // set attribute to value
   public function attr($key, $value = null) {
     $this->attributes[$key] = $value;
     return $this;
   }

   // set content / inner HTML / text
   public function text($content) {
     $this->content = $content;
     return $this;
   }

  // general tag generating function
  static function tag($name, $wrap, $inside) {
    return "<$name $wrap>$inside</$name>";
  }

  static function details($summary, $details, $options) {
    return new Html('details',
      new Html('summary', $summary, $options['summary'] ?? [])
      . $details,
      $options['details'] ?? [],
    );
  }

  // simple link
  static function link($href, $text = null) {
    if ($text === null) $text = $href;
    return new Html('a', $text, [
      'href' => $href
    ]);
  }

  // simple link with target blank
  static function new_tab($href, $text = null) {
    if ($text === null) $text = $href;
    return self::link($href, $text)->attr('target', '_blank');
  }

  // simple email link
  static function email($email, $text = null) {
    if ($text === null) $text = $email;
    return self::link('mailto:'.$email, $text);
  }

  static function phone($phone, $text = null) {
    if ($text === null) $text = $phone;
    return self::link('tel:'.$phone, $text);
  }

  static function form_add_var($name, $value) {
    self::$vars[$name] = $value;
    return "<!-- $name -->";
  }

  static function form_add_vars($array) {
    foreach($array as $name => $var) {
      self::form_add_var($name, $var);
    }
    return "<!-- array of vars added -->";
  }

  static function form_open($action = '', $method = 'POST', $attributes='') {
    if ($method == 'POST') $attributes .= ' enctype="multipart/form-data"';
    return "<form action='$action' method='$method' $attributes>"
    . (
      new Html('input', null, [
        'type' => 'hidden', 'name' => '_token', 'value' => csrf_token(),
      ])
    );
  }

  static function form_close() {
    return '</form>';
  }
/*
  static function form_enable_upload($route) {
    return
        self::form_open($route, 'POST', ' id="file-upload--form"')
      . new Html('file', null, [
        'id' => 'file-upload--input',
      ])
      . self::form_close();
  }
*/
  static function get_var($variable, $field = null, $default = '') {
    if (isset(self::$vars[$variable])) {
      if ($field === null) {
        return self::$vars[$variable];
      } else {
        if (isset(self::$vars[$variable]->$field)) {
          if (self::$vars[$variable]->$field instanceof Collection) {
            return self::$vars[$variable]->$field->implode('id', '|');
          }
          return self::$vars[$variable]->$field;
        }
        if (isset(self::$vars[$variable][$field])) {
          return self::$vars[$variable][$field];
        }
      }
    }
    return old("$variable.$field", $default) ?? $default;
  }

  static function form_submit_buttons($buttons_array = [], $class = 'row', $style = '') {
    $content = '';
    foreach ($buttons_array as $button) {
      $content .= new Html('button', $button['label'], [
          'type' => 'submit',
        ] + (isset($button['action']) ? [ 'formaction' => $button['action'] ] : [])
          + (isset($button['class']) ? [ 'class' => $button['class'] ] : [])
      );
    }
    return new Html('div', $content, ['class' => $class, 'style' => $style ]);
  }

  static function field_id($variable, $field = '', $postfix = '', $separator = '--') {
    if ($field) $field = "$separator$field";
    if ($postfix) $postfix = "$separator$postfix";
    return "$variable$field$postfix";
  }
  static function field_name($variable, $field) {
    return $variable."[$field]";
  }

  // callback format: function($type, $variable, $field, $label, $options) { return ''; }
  // same format as form_group
  static function set_append_func($callback) {
    self::$append_fields_callback = $callback;
    return '<!-- append_func set -->';
  }

  // BOOTSTRAP FORMS
  /*
    $type - type of HTML5 input field (text, select etc.)
    $variable - name of array variable in which data was stored or will be stored
    $field - array index of $variable
    $label - label
    $options
      ['request'] - string, name of request class (\App\Requests\...) to take some input rules from
      ['hint']  - string, Text content of additional info about the input field
      ['input_rules'] - array, attributes for input element
      ['group_rules'] - array, attributes for outer wrapping element (probably div)
      ['group_class'] - string, class for outer wrapping element (won't overwrite default values)
      ['thead'] - array, text content for table headers (eg. for editable key-value control)
      ['options'] - array, key-value (id-name) options for select dropdown
      ['options_default_label'] - label for first pseudo-option
      ['options_default_rules'] - rules for first pseudo-option
  */
  static function form_group($type, $variable = null, $field = null, $label = null, $options = []) {
    $content = '';
    $input_rules = [];
    $group_rules = [];
    if (self::get_mode('placeholders')) $input_rules['placeholder'] = $label;
    if (self::get_mode('required')) $input_rules['required'] = null;
    if (self::get_mode('readonly')) $input_rules['readonly'] = null;
    if (self::get_mode('hidden')) $group_rules['hidden'] = null;
    if (self::get_mode('labels') === false) $label = null;
    if ($label !== null) {
      $content .= new Html('label', $label, [
        'for' => "$variable--$field",

      ]
      + ($type != 'checkbox' ? [
        'class' => self::get_mode('jetstream') ? ((self::get_mode('labels_inline') ? '' : 'block ') . "font-medium text-sm text-gray-700") : ''
      ] : [])
      + ($type == 'checkbox' ? [
        'class' => self::get_mode('jetstream') ? ((self::get_mode('labels_inline') ? '' : 'block ') . "font-medium text-sm text-gray-700 ml-2") : 'form-check-label'
      ] : []));
    }
    if (isset($options['request'])) {
      $class_name = '\\App\\Http\\Requests\\'.$options['request'];
      $request = new $class_name;
      $input_rules = [];

      $rules = $request->rules();
      if (isset($rules["$variable.$field"])) {
        $rules = $rules["$variable.$field"];
        if (isset($rules['required']) || isset($rules['accepted'])) {
          $input_rules['required'] = null;
        }
      } else {
        \Log::warning("No rules for $variable.$field in request " . $options['request']);
      }
    }
    if (isset($options['input_rules'])) {
      $input_rules = $input_rules + $options['input_rules'];
    }

/* SELECT */

    if ($type == 'select') {
      $opts = $options['options'] ?? [];
      $opts_html = new Html('option',
        $options['options_default_label'] ?? '--',
        [ 'value' => '' ] + ($options['options_default_rules'] ?? [])
      );

      foreach ($opts as $opt_value => $opt_label) {
        $opts_opts = [ 'value' => $opt_value ];
        if ($variable && self::get_var($variable, $field) == $opt_value) {
          $opts_opts += [ 'selected' => null ];
        }
        $opts_html .= new Html('option', $opt_label, $opts_opts);
      }

      if (array_key_exists('readonly', $input_rules)) {
        $input_rules['onfocus'] = 'this.oldvalue=this.value; this.blur();';
        $input_rules['onchange'] = 'this.value=this.oldvalue;';
      }

      $content .= new Html('select', $opts_html, $input_rules + [
        'class' => self::get_mode('jetstream') ? 'border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm block mt-1 w-full' : 'form-control',
        'name' => $variable."[$field]",
        'id' => "$variable--$field",
      ]);

/* SELECT VIA RADIO BUTTONS */
} else if ($type == 'select_radio') {

  $opts = $options['options'] ?? [];
  $opts_classes = $options['options_classes'] ?? [];
  $opts_html = '';

  foreach ($opts as $opt_value => $opt_label) {
    $class = $opts_classes[$opt_value] ?? '';
    $opts_opts = [
      'type' => 'radio',
      'id' => "$variable--$field--$opt_value",
      'name' => $variable."[$field]",
      'class' => "$variable--$field",
      'value' => $opt_value
    ];
    if ($variable && self::get_var($variable, $field) == $opt_value) {
      $opts_opts += [ 'checked' => null ];
    }
    if (array_key_exists('readonly', $input_rules)) {
      $input_rules['onclick'] = 'return false;';
    }
    $opts_html .= new HTML('label', (new Html('input', null, $input_rules + $opts_opts))." $opt_label", [
      'style' => 'display:block',
      'class' => $class,
    ]);
  }

  $content .= new Html('div', $opts_html, [
    'class' => self::get_mode('jetstream') ? ($options['options_group_classes'] ?? '') : 'form-control',
    'name' => $variable."[$field]",
    'id' => "$variable--$field",
  ]);

/* SELECT MULTILEVEL */
/* support for three levels */
} else if ($type == 'select_multilevel') {
  $opts_html = new Html('option', '--', [ 'value' => '' ]);

  $createOptions = function($options, $variable, $field, $level = 0) use (&$createOptions) {
    $opts_html = '';

    foreach ($options as $opt) {
      if ($opt->children) {
        if ($level === 0) {
          $opts_html .= new Html('optgroup', $createOptions($opt->children, $variable, $field, $level + 1), [ 'label' => $opt->label ]);
        }
        if ($level === 1) {
          $opts_html .= new Html('option', $opt->label, [ 'disabled' => null ]);
          $opts_html .= $createOptions($opt->children, $variable, $field, $level + 1);
        }
      } else {
        $opts_opts = [ 'value' => $opt->value ];
        if ($variable && self::get_var($variable, $field) == $opt->value) {
          $opts_opts += [ 'selected' => null ];
        }
        $opts_html .= new Html('option', ($level === 2 ? '&nbsp;&nbsp;↳ ' : '' ) . $opt->label, $opts_opts);
      }
    }
    return $opts_html;
  };

  $opts_html .= $createOptions($options['options'], $variable, $field);

  $content .= new Html('select', $opts_html, $input_rules + [
    'class' => 'form-control',
    'name' => $variable."[$field]",
    'id' => "$variable--$field",
  ]);

/* SELECT MULTIPLE (PSV) */

} else if ($type == 'select_multiple') {

      $content .= new Html('textarea', self::get_var($variable, $field), $input_rules + [
        //'class' => 'form-control',
        'name' => $variable."[$field]",
        'id' => "$variable--$field",
        'hidden' => 'hidden',
      ]);

      $opts = $options['options'] ?? [];
      $opts_classes = $options['options_classes'] ?? [];
      $checkboxes = '';

      foreach ($opts as $opt_value => $opt_label) {
        $class = $opts_classes[$opt_value] ?? '';
        $opts_opts = [
          'type' => 'checkbox',
          'id' => "$variable--$field--$opt_value",
          //'name' => $variable."[$field]",
          'class' => "$variable--$field",
          'value' => $opt_value
        ];
        if (array_key_exists('readonly', $input_rules)) {
          $input_rules['onclick'] = 'return false;';
        }
        //$checkboxes .= "<label style='display:block' class='$class'><input type='checkbox' id='$variable--$field--$opt_value' class='$variable--$field' value='$opt_value'> $opt_label</label>\n";
        $checkboxes .= new HTML('label', (new Html('input', null, $input_rules + $opts_opts))." $opt_label", [
          'style' => 'display:block',
          'class' => $class,
        ]);
      }

      $content .= new Html('div', $checkboxes, [
        'class' => self::get_mode('jetstream') ? ($options['options_group_classes'] ?? '') : 'form-control',
        'style' => 'height:initial',  //< fix for bootstrap strange height calculation
      ]);

      $content .= new Html('script', "
        if (true) {
          let initial_values = document.getElementById('$variable--$field').value.split('|');
          for (i = 0; i < initial_values.length; ++i) {
            let e = document.getElementById('$variable--$field--' + initial_values[i]);
            if (e) e.checked = 'checked'
          }

          let elements = document.getElementsByClassName('$variable--$field');
          for (i = 0; i < elements.length; ++i) {
            elements[i].addEventListener('change', function() {
              let values = Array.prototype.slice.call(document.querySelectorAll('input:checked.$variable--$field')).reduce(function(acc, cur) { acc.push(cur.value); return acc; }, []);
              values = values.join('|');
              document.getElementById('$variable--$field').value = values;
            }, false);
          }
        }
      ");

/* TEXT AREA */

    } else if ($type == 'textarea') {
      $content .= new Html('textarea', self::get_var($variable, $field), $input_rules + [
        'class' => self::get_mode('jetstream') ? 'border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm block mt-1 w-full' : 'form-control',
        'name' => $variable."[$field]",
        'id' => "$variable--$field",
      ]);

/* CHECKBOX */

    } else if ($type == 'checkbox') {
      $checked = self::get_var($variable, $field) ? [ 'checked' => 'checked' ] : [];
      // hidden input to post value for unchecked checkbox
      $hidden_fix = new Html('input', null, [
        'type' => 'hidden',
        'value' => 0,
        'name' => $variable."[$field]",
      ]);
      $content = new Html('input', null, $input_rules + [
        'type' => 'checkbox',
        'class' => self::get_mode('jetstream') ? 'rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50' : 'form-check-input',
        'value' => 1,
        'name' => $variable."[$field]",
        'id' => "$variable--$field",
      ] + $checked) . $content;
      $content = new Html('div', $content, [
        'class' => self::get_mode('jetstream') ? 'flex items-center' : 'form-check',
      ]);
      $content = $hidden_fix . $content;


/* EDITABLE LIST (stored as JSON) */

    } else if ($type == 'editable_list') {
      $content .= new Html('textarea', self::get_var($variable, $field), $input_rules + [
        //'class' => 'form-control',
        'name' => $variable."[$field]",
        'id' => "$variable--$field",
        'hidden' => 'hidden',
      ]);
      //ddd([$variable, $field, self::get_var($variable, $field, '[""]')]);
      $content .= new Html('ul', array_reduce(json_decode(self::get_var($variable, $field, '[""]'), true), function ($c, $i) { return "$c<li>$i</li>"; }), $input_rules + [
        'id' => "$variable--$field--list",
        'contenteditable' => 'true',
        'class' => 'form-control pl-5',
        'style' => 'height:initial',  //< fix for bootstrap strange height calculation
      ]);
      $content .= new Html('script', "
          document.getElementById('$variable--$field--list').addEventListener('input', function() {
            document.getElementById('$variable--$field').value = JSON.stringify(document.getElementById('$variable--$field--list').innerHTML.replace(/^<li>|<\/li>$/g, '').split('</li><li>'));
          }, false);
      ");

/* EDITABLE LIST (stored as pipe-separated values) */

} else if ($type == 'editable_list_psv') {
      $content .= new Html('textarea', self::get_var($variable, $field), $input_rules + [
        //'class' => 'form-control',
        'name' => $variable."[$field]",
        'id' => "$variable--$field",
        'hidden' => 'hidden',
      ]);
      $content .= new Html('ul', '<li>' . strtr(self::get_var($variable, $field, ''), ['|' => '</li><li>']) . '</li>', $input_rules + [
        'id' => "$variable--$field--list",
        'contenteditable' => 'true',
        'class' => 'form-control pl-5',
        'style' => 'height:initial',  //< fix for bootstrap strange height calculation
      ]);
      $content .= new Html('script', "
          document.getElementById('$variable--$field--list').addEventListener('input', function() {
            document.getElementById('$variable--$field').value = document.getElementById('$variable--$field--list').innerHTML.replace(/<\/li>\s*<li>/g, '|').replace(/<li>|<\/li>/g, '');
          }, false);
      ");

/* EDITABLE KEY-VALUE (stored as JSON) */
    } else if ($type == 'editable_key_value') {
      $content .= new Html('textarea', self::get_var($variable, $field), $input_rules + [
        // 'class' => 'form-control',
        'name' => $variable."[$field]",
        'id' => "$variable--$field",
        'hidden' => 'hidden',
      ]);
      $assoc_array = json_decode(self::get_var($variable, $field, '[""]'));
      $td_class = 'px-3';
      $table_content = '';
      if (isset($options['thead'])) {
        $key_heading = $options['thead'][0] ?? '';
        $value_heading = $options['thead'][1] ?? '';
        $table_content .= new Html('thead', new Html('th', $key_heading, [ 'class' => $td_class ]) . new Html('th', $value_heading, [ 'class' => $td_class ]));
      }
      $tbody_content = '';
      foreach ($assoc_array as $key => $value) {
        $tbody_content .= new Html('tr', new Html('td', $key, [ 'class' => $td_class ]) . new Html('td', $value, [ 'class' => $td_class ]));
      }
      $table_content .= new Html('tbody', $tbody_content, [
        'id' => "$variable--$field--tbody",
        'contenteditable' => 'true',
      ]);
      $content .= new Html('table', $table_content, $input_rules + [
        'id' => "$variable--$field--table",
        //'contenteditable' => 'true',
        //'class' => 'form-control',
        'border' => '1',
        'wdith' => '100%',
        //'style' => 'height:initial',  //< fix for bootstrap strange height calculation
      ]);
      $content .= new Html('button', 'Dodaj wiersz', $input_rules + [
        'id' => "$variable--$field--insert",
        'class' => 'btn btn-secondary btn-sm my-1',
        'type' => 'button',
      ]);
      $content = new Html('div', $content, [
        'class' => 'form-control',
        'style' => 'height:initial', //< fix for bootstrap strange height calculation
      ]);
      $content .= new Html('script', "
          document.getElementById('$variable--$field--tbody').addEventListener('input', function() {
            let rows = document.getElementById('$variable--$field--tbody').innerHTML
              .replace(/^<tr>|<\/tr>$/g, '')
              .split('</tr><tr>');

            var assoc_array = {};
            for (index = 0; index < rows.length; ++index) {
                let row = rows[index].replace(/^<td class=\"$td_class\">|<\/td>$/g, '')
                .split('</td><td class=\"$td_class\">');
                //console.log(row);
                assoc_array[row[0]] = row[1];
            }

            document.getElementById('$variable--$field').value = JSON.stringify(assoc_array);
          }, false);

          document.getElementById('$variable--$field--insert').addEventListener('click', function() {
            let row = document.getElementById('$variable--$field--table').insertRow(-1);
            let c0 = row.insertCell(0);
            let c1 = row.insertCell(1);
            c0.innerHTML = '0';
            c0.classList.add('$td_class');
            c1.classList.add('$td_class');
            return false;
          });
      ");

/* DATE */

    } else if ($type == 'date') {
      $content .= new Html('input', null, $input_rules + [
        'type' => 'date',
        'class' => 'form-control',
        'name' => $variable."[$field]",
        'id' => "$variable--$field",
        'value' => \Carbon\Carbon::parse(self::get_var($variable, $field, \Carbon\Carbon::parse('0000-00-00')))->format('Y-m-d'),
      ]);

/* URL */

} else if ($type == 'url') {
      $content .= new Html('input', null, $input_rules + [
        'type' => 'url',
        'class' => self::get_mode('jetstream') ? 'border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm block mt-1 w-full' : 'form-control',
        'name' => $variable."[$field]",
        'id' => "$variable--$field",
        'value' => self::get_var($variable, $field),
        'onblur' => "if (this.value && !~this.value.indexOf('http')) this.value = 'https://' + this.value",
      ]);
      $content .= new Html('script', "
        {
          let e = document.getElementById('$variable--$field');
          if (e.value && !~e.value.indexOf('http')) e.value = 'https://' + e.value;
        }
      ", []);

/* FILE (single) */

} else if ($type == 'file') {

  $gallery_selector = $options['upload']['gallery_selector'] ?? '';

      if (self::get_var($variable, $field)) {

        $default_source = $options['upload']['default_source'] ?? 'https://upload.wikimedia.org/wikipedia/commons/a/ae/Icon-txt.svg';
        $smartcrop = $options['upload']['use_smartcrop'] ?? false;

        $content .=
        new Html('figure',
          new Html('a',
            new Html('img', null, [
          'src' =>
            $smartcrop
            ? (\App\Smartcrop::asset(in_array(pathinfo(self::get_var($variable, $field))['extension'], ['jpg', 'png', 'jpeg', 'png', 'JPG']) ? self::get_var($variable, $field) : $default_source, 300, 300))
            : (in_array(pathinfo(self::get_var($variable, $field))['extension'], ['jpg', 'png', 'jpeg', 'png', 'JPG']) ? self::get_var($variable, $field) : $default_source),
          'id' => "$variable--$field--image-preview",
          'width' => $options['upload']['preview_width'] ?? 100,
          'height' => $options['upload']['preview_height'] ?? 100,
        ]),
        [
          'href' => self::get_var($variable, $field),
          'id' => "$variable--$field--link",
          'target' => '_blank',
          'class' =>
            (in_array(pathinfo(self::get_var($variable, $field))['extension'], ['jpg', 'png', 'jpeg', 'png', 'JPG'])
            ? $gallery_selector //< glightbox
            : ''),
          'data-gallery' => $options['upload']['gallery_name'] ?? 'default', //< glightbox
        ])
        . new Html('figcaption', (explode('--', self::get_var($variable, $field)))[1], [
          'id' => "$variable--$field--figcaption",
          'style' => 'overflow-wrap: anywhere;',
        ]),
        [
          'id' => "$variable--$field--figure",
          'title' => (explode('--', self::get_var($variable, $field)))[1],
        ]);

      } else {

        $content .=
        new Html('figure',
          new Html('a',
            new Html('img', null, [
          'src' => $options['upload']['loader'] ?? null,
          'id' => "$variable--$field--image-preview",
          'width' => $options['upload']['preview_width'] ?? 100,
          'height' => $options['upload']['preview_height'] ?? 100,
        ]),
        [
          'href' => '',
          'id' => "$variable--$field--link",
          'target' => '_blank',
          'class' => '', //< glightbox
          'data-gallery' => $options['upload']['gallery_name'] ?? 'default', //< glightbox
        ]) . new Html('figcaption', '', [
          'id' => "$variable--$field--figcaption",
          'style' => 'overflow-wrap: anywhere;',
        ]),
        [
          'id' => "$variable--$field--figure",
          'hidden' => null,
        ]);
      }

      $content .= new Html('input', null, [
        'type' => 'hidden',
        'id' => "$variable--$field",
        'name' => $variable."[$field]",
        'value' => self::get_var($variable, $field),
      ]);
      if (false === empty(self::get_var($variable, $field)) && array_key_exists('required', $input_rules)) {
        unset($input_rules['required']);
      }
      $content .= new Html('input', null, $input_rules + [
        'type' => 'file',
        'id' => "$variable--$field--upload-input",
      ]);

      $route = $options['upload']['route'];
      $use_loader = $options['upload']['loader'] ? 'true' : 'false';

      $content .= new Html('script', "
      if (true) {
          let e = document.getElementById('$variable--$field--upload-input');
          e.addEventListener('change', function (event) {
            if (e.files.length) {
              //console.log(e.files[0]);
              const formData = new FormData();
              formData.append('single_file', e.files[0]);

              if ($use_loader) {
                document.getElementById('$variable--$field--figure').hidden = false;
              }

              fetch('$route', {
                method: 'POST',
                body: formData
              })
              .then(response => response.json())
              .then(result => {
                console.log('Success:', result);
                document.getElementById('$variable--$field').value = result.url;
                document.getElementById('$variable--$field').dispatchEvent(new Event('change'));
                document.getElementById('$variable--$field--link').href = result.url;
                document.getElementById('$variable--$field--image-preview').src = result.thumbnail.length > 0 ? result.thumbnail : result.preview;
                document.getElementById('$variable--$field--figure').hidden = false;
                document.getElementById('$variable--$field--figure').title = result.title;
                document.getElementById('$variable--$field--figcaption').innerHTML = result.title;
                if (result.thumbnail.length > 0) {
                  document.getElementById('$variable--$field--link').classList.add('$gallery_selector');
                }
              })
              .catch(error => {
                console.error('Error:', error);
                alert('Nie udało się dodać załącznika.');
              });
            }
          });
      }
      ");

/* FILEPOND */
/*
} else if ($type == 'filepond') {

      $content .= new Html('input', null, $input_rules + [
        'type' => 'file',
        'id' => "$variable--$field",
        'multiple' => null,
      ]);
      $content .= new Html('script', "
          const pond = FilePond.create(document.getElementById('$variable--$field'), {
            name: '$variable[$field]',
          });
      ");
*/
/* GENERIC INPUT */

    } else {
      $content .= new Html('input', null, $input_rules + [
        'type' => $type,
        'class' =>
          self::get_mode('jetstream') ? 'border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm block mt-1 w-full' : 'form-control',
        'name' => $variable."[$field]",
        'id' => "$variable--$field",
        'value' => self::get_var($variable, $field),
      ]);
    }

    if (isset($options['hint'])) {
      $content .= new Html('small', $options['hint'], [
        'id' => "$variable--$field--hint",
        'class' => 'form-text text-muted',
      ]);
    }

    $group_rules = $group_rules + ($options['group_rules'] ?? []);
    $group_class = $options['group_class'] ?? '';
    if (self::get_mode('labels_inline')) {
      $group_class = " form-inline $group_class";
    }
    $wrapper = new Html('div', $content, $group_rules + [
      'class' => (self::get_mode('jetstream') ? "mt-4 $group_class" : "form-group $group_class") . " app_html_$type",
      'id'    => "$variable--$field--group",
    ]);

    if (self::get_mode('append') && self::$append_fields_callback) {
      self::set_mode('append', false);
      $wrapper = $wrapper . (self::$append_fields_callback)($type, $variable, $field, $label, $options);
      self::set_mode('append', true);
    }

    return $wrapper;
  }

/*
  $type = e.g. checkbox (w przyszlosci moga byc obslugiwane rozne typy)

  uwaga: nie obsluguje nazw targetow z "-" w nazwie
*/
  static function script_on_change($type, $variable = null, $field = null, $targets = [], $action = 'show_display') {
    $show = [];
    $hide = [];

    foreach ($targets as $target) {
        $target_var = $target[0];
        $target_field = $target[1];
        switch ($action) {
          case 'show_display':
            $show[] = "document.getElementById('$target_var--$target_field--group').style.display = 'block'";
            $hide[] = "document.getElementById('$target_var--$target_field--group').style.display = 'none'";
            break;
          case 'show_hidden':
            $show[] = "document.getElementById('$target_var--$target_field--group').hidden = false";
            $hide[] = "document.getElementById('$target_var--$target_field--group').hidden = true";
            break;
          case 'make_required':
          case 'require':
            $show[] = "document.getElementById('$target_var--$target_field').required = true";
            $hide[] = "document.getElementById('$target_var--$target_field').required = false";
            break;
        }
    }

    $show = implode(";\n", $show);
    $hide = implode(";\n", $hide);
    $field_filtered = strtr($field, [ '--' => '__']);

    $content = new Html('script', "
      function {$variable}__{$field_filtered}__{$action}() {
        var e = document.getElementById('$variable--$field');
        if (e) {
          e.addEventListener('change', function (event) {
              if (e.checked) {
                $show
              } else {
                $hide
              }
          });
          e.dispatchEvent(new Event('change'));
        } else {
          console.log('element not found');
        }
      }
      {$variable}__{$field_filtered}__{$action}();
    ");

    return $content;
  }

  static function prefix_array($prefix, $array, $ignore_when_strpos = null) {
    $new = [];
    foreach ($array as $key => $value) {
        if ($ignore_when_strpos && strpos($key, $ignore_when_strpos) !== false) {
          $new[$key] = $value;
        } else {
          $new["$prefix$key"] = $value;
        }
    }
    return $new;
  }
}
