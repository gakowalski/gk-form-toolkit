# GK Form Toolkit

## Instalacja

### Wymagania i zgodność

Pakiet przetestowany na:

* PHP 8.0
* Laravel 8

Teoretycznie powinien działać też na PHP 7.4. W przypadku Laravel 7 mogą wystapić problemy związane ze zmianą struktury folderów oraz (co za tym idzie) przestrzeni nazw.

### Świeży serwis

```bash
composer require gakowalski/gk-form-toolkit
```

### Aktualizacja serwisów korzystających z rozproszonych plików zamiast pakietu

```bash
rm app/Html.php
rm app/Smartcrop.php
rm app/Http/Controllers/GenericAppController.php
rm app/Http/Requests/ModelBasedFormRequest.php
composer require gakowalski/gk-form-toolkit
```

## Użytkowanie

### Obsługa błędów

Aby korzystać z alternatywnej metody raportowania błędów do pliku dziennika, należy w pliku `app\Exceptions\Handler.php` metodę `report()` zmienić na:

```php
public function report(Throwable $exception)
{
  \Kowalski\Laravel\App\Exceptions\Handler::report($exception);
}
```

### Html

#### Wprowadzenie

Klasa `\App\Html` jest zbiorem rozwiazań wspomagającym **na poziomie podstawowym** wytwarzanie kodu HTML tworzenie a **na pozimie rozszerzonym** obsługę formularzy.

Jakkolwiek klasa posiada konstruktor, to tworzenie jej obiektów ma miejsce tylko przy bardzo podstawowym wytwarzaniu pojedynczych znaczników HTML. W 99% praca dzieje się na wyższym pozimie abstrakcji opartym o wywołania jednej z wielu metod statycznych. Niemalże wszystkie zwracają wartość typu `string` lub obiekt bezpośrednio konwertowany na `string` a wynik jest przeznaczony do użytku w plikach widoku, więc wygląda to zazwyczaj tak:

```php
{!! \App\Html::some_function('some_arg', $some_arg_2) !!}
```

Istotne jest użycie `{!! !!}` zamiast typowego `{{ }}` aby treści znakowe zostały przekazane do widoku bez filtrowania.

Dla czytelności wprowadzono możliwość stosowania dedykowanej dyrektywy Blade: `@html`. Wówczas kod wygląda tak:

```php
@html(some_static_function('some_arg', $some_arg_2))
```

Migracja na dyrektywę za pomocą search & replace:

* `{!! \App\Html::` na `@html(`
* ` !!}` oraz `!!}` na `)`

#### Tworzenie prostych znaczników

```php
{!! new \App\Html('p', 'Hello <u>world</u>!', [
  'align' => 'center',
  'id' => 'my-hello',
]) !!}
```

Powyższy kod generuje następujący HTML:

```html
<p align="center" id="my-hello">Hello <u>world<u>!</p>
```

W przypadku niektórych znaczników nie jest generowany tag zamykający, gdy treść znacznika jest równa `null`:

```php
{!! new \App\Html('input', null, [ 'type' => 'submit' ]) !!}
```

Lista takich znaczników znajduje się w `\App\Html::$_self_closing_tags`.

#### Tworzenie linków

```php
1 @html(link('http://domain.com'))
2 @html(link('http://domain.com', 'Moja domena'))
3 @html(new_tab('http://domain.com'))
4 @html(new_tab('http://domain.com', 'Moja domena'))
5 @html(email('info@domain.com'))
6 @html(email('info@domain.com', 'Napisz do mnie'))
7 @html(phone('123456789'))
8 @html(phone('123456789', 'Zadzwoń!'))
```

Odpowiadają mniej więcej:

```html
1 <a href="http://domain.com">http://domain.com<a>
2 <a href="http://domain.com">Moja domena<a>
3 <a href="http://domain.com" target="_blank">http://domain.com<a>
4 <a href="http://domain.com" target="_blank">Moja domena<a>
5 <a href="mailto:info@domain.com">info@domain.com<a>
6 <a href="mailto:info@domain.com">Napisz do mnie<a>
7 <a href="tel:123456789">123456789<a>
8 <a href="tel:123456789">Zadzwoń do mnie<a>
```

#### Tworzennie pól typu select oraz grup radio

##### Grupa radio

Grupa w układzie pionowym

```php
@html(form_group('select_radio', 'answer', $category_id, null, [
  'options' => [
    0 => 'NIE',
    1 => 'TAK',
    2 => 'NIE WIEM',
  ],
]))
```

W przypadku Bootstrap, aby uzyskać układ poziomy należy zawrzeć pole w kontenerze i następnie użyć reguł CSS opartych o flex:

```css
.kontener .form-control {
  display: flex;
  gap: 2em;
  font-size: 1em;
}
```

W przypadku JetStream oraz Tailwind możliwe jest przekazanie odpowiedniego stylu poprzez `options_group_classes`:

```php
@html(form_group('select_radio', 'answer', $category_id, null, [
  'options' => [ 0 => 'NIE', 1 => 'TAK', ],
  'options_group_classes' => 'flex',
]))
```

##### Select wielopoziomowy

```php
@html(form_group('select_multilevel', 'zwierzeta', 'rodzaj', 'Rodzaj zwierzęcia', [
  'options' => json_decode(json_encode([
    [
      'label' => 'Ssaki',
      'value' => '2',
      'children' => [
        [ 'value' => 'pies', 'label' => 'Pies', 'children' => null ],
        [ 'value' => 'kot', 'label' => 'Kot', 'children' => null ],
        [ 'value' => 'kon', 'label' => 'Koń', 'children' => null ],
      ],
    ],
    [
      'label' => 'Ptaki',
      'value' => '1',
      'children' => [
        [ 'value' => 'slowik', 'label' => 'Słowik', 'children' => null ],
        [ 'value' => 'golab', 'label' => 'Gołąb', 'children' => null ],
        [ 'value' => 'wrona', 'label' => 'Wrona', 'children' => null ],
      ],
    ],
  ])),
  'options_default_label' => '(Wybierz zwierzę)',
]))
</div>
```

#### Sterowanie trybami

Działanie klasy `\App\Html` można zmieniać dla wielu generowanych przez nią elementów poprzez zmianę ustawienia trybów. Włączenie trybu realizuje się tak:

```php
@html(set_mode('nazwa_trybu', true))
```

Warto dodać, że metoda zwraca zawsze pusty string.

Wyłączenie trybu wygląda tak:

```php
@html(set_mode('nazwa_trybu', false))
```

Możliwe jest pobranie aktualnego stanu trybu poprzez metodę `get_mode('nazwa_trybu')`. Wynikiem nie jest pusty string lecz wartość `true` lub `false`.

### Formularze

#### Wiązanie zmiennych

Gdy z różnych przyczyn `form_group` nie może odczytać danych z odpowiedniej tablicy, można dokonać ręcznego powiązania nazwy tablicy ze zmienną.

```
@html(form_add_var('search', $search))
```

### Eksport do XLSX

### Szablony klas
