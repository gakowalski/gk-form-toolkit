# GK Form Toolkit

## Instalacja

### Wymagania i zgodność

Pakiet przetestowany na:

* PHP 8.0
* Laravel 8

Teoretycznie powinien działać też na PHP 7.4. W przypadku Laravel 7 mogą wystapić problemy związane ze zmianą struktury folderów oraz (co za tym idzie) przestrzeni nazw.

### Świeży serwis

```
composer install gakowalski/gk-form-toolkit
```

### Aktualizacja serwisów korzystających z rozproszonych plików zamiast pakietu

```
rm app/Html.php
rm app/Http/Controllers/GenericAppController.php
rm app/Http/Requests/ModelBasedFormRequest.php
composer install gakowalski/gk-form-toolkit
```

## Użytkowanie

### Html

#### Wprowadzenie

Klasa `\App\Html` jest zbiorem rozwiazań wspomagającym **na poziomie podstawowym** wytwarzanie kodu HTML tworzenie a **na pozimie rozszerzonym** obsługę formularzy.

Jakkolwiek klasa posiada konstruktor, to tworzenie jej obiektów ma miejsce tylko przy bardzo podstawowym wytwarzaniu pojedynczych znaczników HTML. W 99% praca dzieje się na wyższym pozimie abstrakcji opartym o wywołania jednej z wielu metod statycznych. Niemalże wszystkie zwracają wartość typu `string` lub obiekt bezpośrednio konwertowany na `string` a wynik jest przeznaczony do użytku w plikach widoku, więc wygląda to zazwyczaj tak:

```php
{!! \App\Html::some_function('some_arg', $some_arg_2) !!}
```

Istotne jest użycie `{!! !!}` zamiast typowego `{{ }}` aby treści znakowe zostały przekazane do widoku bez filtrowania.

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
1 {!! \App\Html::link('http://domain.com') !!}
2 {!! \App\Html::link('http://domain.com', 'Moja domena') !!}
3 {!! \App\Html::new_tab('http://domain.com') !!}
4 {!! \App\Html::new_tab('http://domain.com', 'Moja domena') !!}
5 {!! \App\Html::email('info@domain.com') !!}
6 {!! \App\Html::email('info@domain.com', 'Napisz do mnie') !!}
7 {!! \App\Html::phone('123456789') !!}
8 {!! \App\Html::phone('123456789', 'Zadzwoń!') !!}
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

#### Sterowanie trybami

Działanie klasy `\App\Html` można zmieniać dla wielu generowanych przez nią elementów poprzez zmianę ustawienia trybów. Włączenie trybu realizuje się tak:

```php
{!! \App\Html::set_mode('nazwa_trybu', true) !!}
```

Warto dodać, że metoda zwraca zawsze pusty string.

Wyłączenie trybu jest wygląda tak:

```php
{!! \App\Html::set_mode('nazwa_trybu', false) !!}
```

Możliwe jest pobranie aktualnego stanu trybu poprzez metodę `get_mode('nazwa_trybu')`. Wynikiem nie jest pusty string lecz wartość `true` lub `false`.


### Eksport do XLSX

### Szablony klas