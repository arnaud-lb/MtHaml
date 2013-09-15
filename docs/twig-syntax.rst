
MtHaml/Twig syntax
==================

Haml is a short syntax for Html, coupled with a template engine. In
MtHaml/Twig, the template engine is Twig.

Quick introduction to HAML
--------------------------

.. container:: code-cols

    .. code-block:: html+jinja

        <strong>{{ item.title }}</strong>

    .. code-block:: haml

        %strong= item.title

In Haml, we start a tag by using the percent character followed by the name
of the tag. This works for ``%div``, ``%html``, ``%li``, or any other tag.

The ``=`` after the tag name tells Haml that what follows is Twig code that
should be printed.

Adding attributes
~~~~~~~~~~~~~~~~~

.. container:: code-cols

    .. code-block:: html+jinja

        <strong class="code" id="message">Hello, World!</strong>

    .. code-block:: haml

        %strong(class="code" id="message") Hello, World!

The syntax for attributes is very much like HTML's.

Since ``class`` and ``id`` attributes are very common, there is a short
notation for them, using CSS syntax:

.. container:: code-cols

    .. code-block:: html+jinja

        <strong class="code" id="message">Hello, World!</strong>

    .. code-block:: haml

        %strong.code#message Hello, World!

Haml also allows to omit the tag name, in which case it defaults to ``%div``.

.. container:: code-cols

    .. code-block:: html+jinja

        <div class='content'>Hello, World!</div>

    .. code-block:: haml

        .message Hello, World!

Examples with nesting
~~~~~~~~~~~~~~~~~~~~~

.. container:: code-cols

    .. code-block:: html+jinja

        <div class='item' id='item<%= item.id %>'>
            {{ item.body }}
        </div>


    .. code-block:: haml

        .item(id="item#{item.id}")
            = item.body

White spaces, or more specifically indentation, is significant in Haml. This allows Haml to close tags automatically:

 - when there is inline content, the tag is closed just after the content,
   on the same line
 - otherwise the tag is closed as soon as the indentation level decreases
   to the level of the opening tag
 - there are also special rules for auto-closing tags like ``%img`` or
   ``%meta``.

.. container:: code-cols

    .. code-block:: html+jinja

        <div id='content'>
            <div class='left column'>
                <h2>Welcome to our site!</h2>
                <p>{{ information }}</p>
            </div>
            <div class="right column">
                {% include "sidebar.twig" %}
            </div>
        </div>

    .. code-block:: haml

        #content
            .left.column
                %h2 Welcome to our site!
                %p= information
            .right.column
                - include "sidebar.twig"

.. note::

    See the Haml reference at http://haml.info/docs/yardoc/file.REFERENCE.html#plain_text

Haml to Twig
~~~~~~~~~~~~

MtHaml works by converting Haml syntax to plain Twig templates, which Twig
then executes.

The easiest way to understand how to write Haml/Twig templates is to understand
how the templates are converted to Twig.

Printing
########

The most common way to print something in Haml is by starting a line with the ``=`` character (or immediately following a tag declaration). Everything after the ``=`` is expected to be a Twig expression, and will be converted to Twig's double curly syntax:

.. container:: code-cols

    .. code-block:: haml

        %p.para= some.twig()|expression

    .. code-block:: html+jinja

        <p class="para">{{ some.twig()|expression }}</p>

You can effectively use any valid Twig expression here; including but not limited to functions, filters, macros, array element access.

There a few other ways to print something in Haml, for instance by using string interpolations syntax:

.. container:: code-cols

    .. code-block:: haml

        %span Hello #{ name }

    .. code-block:: html+jinja

        <span>Hello {{ name }}</span>

And a few other places, like in attribute values:

.. container:: code-cols

    .. code-block:: haml

        %span(id=any_valid_twig|syntax)

    .. code-block:: html+jinja

        <span id={{ any_valid_twig|syntax }}></span>

MtHaml will accept anything in attribute values, as long as parentheses, curly braces, square brackets, quotes, and double quotes are balanced.


Control structures, or Twig tags
################################

Now what about code we don't want to print?

In Haml, executing code we don't want to print is done by starting a line with the ``-`` character (maybe preceeded by indentation).

Everything after the ``-`` is expected to be a Twig tag, and will be converted to Twig's curly percent syntax.

.. container:: code-cols

    .. code-block:: haml

        %p
            - if some.condition|default(0) > 1 or foo in bar
                This is a control structure

        %ul#a-list
            - for value in some.array
                %li.list-item= value

    .. code-block:: html+jinja

        <p>
            {% if some.condition|default(0) > 1 or foo in bar %}
                This is a control structure
            {% endif %}
        </p>

        <ul id="a-list">
            {% for value in some.array %}
                <li class="list-item">{{ value }}</li>
            {% endfor %}
        </ul>

Here too, you can use any valid Twig `tag <http://twig.sensiolabs.org/doc/tags/index.html>`_. For instance, in the `if <http://twig.sensiolabs.org/doc/tags/if.html>`_ tag, you can use any twig `expression <http://twig.sensiolabs.org/doc/templates.html#expressions>`_, including but not limited to `filters <http://twig.sensiolabs.org/doc/filters/index.html>`_, `tests <http://twig.sensiolabs.org/doc/tests/index.html>`_, `operators <http://twig.sensiolabs.org/doc/templates.html#comparisons>`_.

End tags are added automatically if the tag is followed by indented lines. Otherwise, no end tag is added. This simple rule allows MtHaml to support every possible Twig tag, without knowing the meaning of tags.

Example with blocks:

.. container:: code-cols

    .. code-block:: haml

        - extends "layout.twig"

        - block title "this is an inline block"

        - block body
            .content
                %h1 Title
                This block has contents

    .. code-block:: html+jinja

        {% extends "layout.twig" %}

        {% block title "this is an inline block" %}

        {% block body %}
            <div class="content">
                <h1>Title</h1>
                This block has contents
            </div>
        {% endblock %}

Example with macros:

.. container:: code-cols

    .. code-block:: html+jinja

        {% macro input_text(name, value) %}
            <input type="text" name="{{ name }}" value="{{ value }} />
        {% endmacro %}

        {% import _self as forms %}

        {{ forms.input_text("foo", "bar") }}

    .. code-block:: haml

        - macro input_text(name, value)
            %input(type="text" name=name value=value)


        - import _self as forms

        = forms.input_text("foo", "bar")

And every tag you want, even custom tags.

.. note::
    See the documentation of Twig tags: http://twig.sensiolabs.org/doc/tags/index.html

Advanced Haml syntax
~~~~~~~~~~~~~~~~~~~~

MtHaml support all of Haml's syntax, with one major difference: use Twig code instead of Ruby. See the reference: http://haml.info/docs/yardoc/file.REFERENCE.html#plain_text .


