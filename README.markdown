# Multi target HAML

MtHaml is a PHP implementation of the [HAML language][1] which can target multiple languages.

Currently supported targets are PHP and [Twig][4], and new ones can be added easily.

Mt-Haml implements the exact same syntax as ruby-haml; the only difference is that any supported language can be used everywhere HAML expects Ruby code:

## HAML/Twig:

``` haml
ul#users
  - for user in users
    %li
      = user.name
      Email: #{user.email}
      %a(href=user.url) Home page
```

Rendered:

``` jinja
<ul id="users">
  {% for user in users %}
    <li>
      {{ user.name }}
      Email: {{ user.email }}
      <a href="{{ user.url }}">Home page</a>
    </li>
  {% endfor %}
</ul>
```

## HAML/PHP:

``` haml
ul#users
  - foreach($users as $user)
    %li
      = $user->getName()
      Email: #{$user->getEmail()}
      %a(href=$user->getUrl()) Home page
```

Rendered:

``` php
<ul id="users">
  <?php foreach($users as $user) { ?>
    <li>
      <?php echo $user->getName(); ?>
      Email: <?php echo $user->getEmail(); ?>
      <a href="<?php echo $user->getUrl(); ?>">Home page</a>
    </li>
  <?php } ?>
</ul>
```

## Usage

PHP:

``` php
<?php
$haml = new MtHaml\Environment('php');
$compiled = $haml->compileString($haml_template, "filename");
```

[Twig][4]:

``` php
<?php
$haml = new MtHaml\Environment('twig', array('enable_escaper' => false));
$compiled = $haml->compileString($haml_template, "filename");
```

See [examples][7]

## Escaping

MtHaml will escape everything by default. As twig already supports auto escaping it is recommended to enable it in Twig and disable it in MtHaml:

`new MtHaml\Environment('twig', array('enable_escaper' => false));`

HAML/PHP is rendered like this when auto escaping is enabled:

``` haml
Email #{$user->getEmail()}
%a(href=$user->getUrl()) Home page
```

``` php
Email <?php echo htmlspecialchars($user->getEmail(), ENT_QUOTES, 'UTF-8'); ?>
<a href="<?php echo htmlspecialchars($user->getUrl(), ENT_QUOTES, 'UTF-8'); ?>">Home page</a>
```

## Twig

Using [Twig][4] in HAML give more control over what can be executed, what variables and functions are exposed to the templates, etc. This also allows to use all of Twig's awesome features like template inheritance, macros, blocks, filters, functions, tests, ...

``` haml
- extends "some-template.haml"

- macro printSomething()
  %p something

- block body
  %h1 Title
  = _self.printSomething()
```

### Integration in Twig

MtHaml comes with a Twig_Loader that will automatically convert HAML into Twig at loading time (Twig will then compile the resulting Twig script and cache it). Scripts starting with `{% haml %}` will be parsed as HAML, and the others will be left untouched.

The loader acts as a proxy and takes an other loader as parameter:

``` php
<?php

$haml = new MtHaml\Environment(...);

$twig_loader = new Twig_Loader_Filesystem(...);
$twig_loader = new MtHaml\Support\Twig\Loader($twig_loader);
```

## Syntax

The syntax is the same as [HAML/Ruby][1]'s syntax; except that PHP or Twig have to be used where Ruby is expected.

See the [tutorial][2] and the [reference][3]

## Performance

MtHaml has no overhead as everything is done at compile time. Compiled templates are cacheable and don't even need MtHaml to execute.

## Unsupported features

Some features are still to be implemented:

 * merging of dynamically-named attributes: `%p{"#{foo}" => "bar", "#{foo}" => "baz"}` will render twice the same attribute
 * special merging of the id attribute: `%p(id="a" id="b")` will render as `<p id="b">`
 * special handling of the data attribute
 * handling of attribute methods and boolean attributes
 * special handling of arrays, hashes and objects as attribute value
 * indenting of dynamic content (while the HTML will be correctly indented, the generated content will be left untouched)

## Helpers

Helpers un HAML/Ruby are just ruby functions exposed to templates. Any function can be made available to HAML templates by the target language (the function only have to be available at runtime).

## Filters

Supported filters are `plain`, `javascript` and `css`. Others may be added in the future.

Example:

``` haml
%p something
:javascript
  some.javascript.code("#{var|escape('js')}");
```

``` jinja
<p>something</p>
<script type="text/javascript">
//<![CDATA[
  some.javascript.code("{{ var|escape('js') }}");
//]]>
</script>
```

## Sass

[Sass][6] can be used in PHP projects without problem. It only depends on Ruby and does not need to be installed on production servers. So MtHaml will not re-implement Sass.

## License

MtHaml is released under the MIT license (same as HAML/Ruby).

[1]: http://haml-lang.com/
[2]: http://haml-lang.com/tutorial.html
[3]: http://haml-lang.com/docs/yardoc/file.HAML_REFERENCE.html
[4]: http://www.twig-project.org/
[5]: http://haml-lang.com/docs/yardoc/file.HAML_REFERENCE.html#attribute_methods
[6]: http://sass-lang.com/
[7]: https://github.com/arnaud-lb/MtHaml/blob/master/examples/example.php
