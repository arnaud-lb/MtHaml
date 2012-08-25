# Multi target HAML

[![Build Status](https://secure.travis-ci.org/arnaud-lb/MtHaml.png)](http://travis-ci.org/arnaud-lb/MtHaml)

MtHaml is a PHP implementation of the [HAML language][1] which can target multiple languages.

Currently supported targets are PHP and [Twig][4], and new ones can be added easily.

Mt-Haml implements the exact same syntax as ruby-haml; the only difference is that any supported language can be used everywhere HAML expects Ruby code:

## HAML/Twig:

``` haml
%ul#users
  - for user in users
    %li.user
      = user.name
      Email: #{user.email}
      %a(href=user.url) Home page
```

Rendered:

``` jinja
<ul id="users">
  {% for user in users %}
    <li class="user">
      {{ user.name }}
      Email: {{ user.email }}
      <a href="{{ user.url }}">Home page</a>
    </li>
  {% endfor %}
</ul>
```

## HAML/PHP:

``` haml
%ul#users
  - foreach($users as $user)
    %li.user
      = $user->getName()
      Email: #{$user->getEmail()}
      %a(href=$user->getUrl()) Home page
```

Rendered:

``` php
<ul id="users">
  <?php foreach($users as $user) { ?>
    <li class="user">
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

// Register the MtHaml extension before executing the template:
$twig->addExtension(new MtHaml\Support\Twig\Extension());
$twig->render(...);
```

See [examples][7]

## Escaping

MtHaml escapes everything by default. Since Twig already supports
auto escaping it is recommended to enable it in Twig and disable it in MtHaml:

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

Using [Twig][4] in HAML gives more control over what can be executed, what variables and functions are exposed to the templates, etc. This also allows to use all of Twig's awesome features like template inheritance, macros, blocks, filters, functions, tests, ...

``` haml
- extends "some-template.haml"

- macro printSomething()
  %p something

- block body
  %h1 Title
  = _self.printSomething()
```

### Integration in Twig

MtHaml comes with an example Twig_Loader that will automatically convert HAML into Twig at loading time (Twig will then compile the resulting Twig script and cache it). Scripts starting with `{% haml %}` will be parsed as HAML, and the others will be left untouched.

The loader acts as a proxy and takes an other loader as parameter:

``` php
<?php

$haml = new MtHaml\Environment(...);

$twig_loader = new Twig_Loader_Filesystem(...);
$twig_loader = new MtHaml\Support\Twig\Loader($twig_loader);
```

### Runtime support

Compiled MtHaml/Twig templates need support from MtHaml at runtime in some cases. Because of this, a Twig extension must be loaded before executing the templates.


``` php
<?php
// Register the MtHaml extension before executing the template:
$twig->addExtension(new MtHaml\Support\Twig\Extension());
$twig->render("rendered_twig_template.twig");
```

## Syntax

The syntax is the same as [HAML/Ruby][1]'s syntax, except that PHP or Twig have to be used where Ruby is expected.

See the [tutorial][2] and the [reference][3]

## Performance

MtHaml converts HAML to PHP or Twig code. The resulting code can be cached and executed any number of times, and
doesn't depend on HAML at runtime.

MtHaml has no runtime overhead.

## Helpers

Helpers in HAML/Ruby are just ruby functions exposed to templates.
Any function can be made available to HAML templates by the target language
(the function only have to be available at runtime).

In HAML/Twig you can use all of Twig's functions, filters and tags. In PHP, you can use all PHP functions.

## Filters

Supported filters are `plain`, `preserve`, `javascript` and `css`. Others may be added in the future.

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

[Sass][6] can be used in PHP projects without problem.It only depends on Ruby and does not need to be installed on production servers. So MtHaml will not re-implement Sass.

## Frameworks and CMS support

 - Symfony2: https://github.com/arnaud-lb/MtHamlBundle
 - Zend Framework 1: https://github.com/bonndan/mthaml-zf1

Add yours: https://github.com/arnaud-lb/MtHaml/edit/master/README.markdown

## License

MtHaml is released under the MIT license (same as HAML/Ruby).

[1]: http://haml-lang.com/
[2]: http://haml-lang.com/tutorial.html
[3]: http://haml-lang.com/docs/yardoc/file.HAML_REFERENCE.html
[4]: http://www.twig-project.org/
[5]: http://haml-lang.com/docs/yardoc/file.HAML_REFERENCE.html#attribute_methods
[6]: http://sass-lang.com/
[7]: https://github.com/arnaud-lb/MtHaml/blob/master/examples/example.php
