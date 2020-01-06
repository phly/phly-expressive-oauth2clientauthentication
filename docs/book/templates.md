# Login Templates

This package ships with a number of built-in templates for displaying the
"login" page when a user is unauthorized; these are in the `templates/`
subdirectory, and map to the `oauth2clientauthentication::401` template.

The templates provided expose links for each of the GitHub, Google, and (when
enabled) Debug providers. Further, they are written using [Bootstrap
theming](http://getbootstrap.com). As such, you will likely want to override
them.

Below, we demonstrate each of the default shipped versions.

## Plates

```php
<?php $this->layout('layout::layout', [
    'title' => 'Unauthorized',
]); ?>

<section class="col-md-8 col-md-offset-2 code_401">
  <h2>Unauthorized</h2>

  <p>
    You are not logged in, and therefore cannot perform this action.
  </p>

  <p>
    Login to continue:
  <p>

  <div class="btn-group-vertical" role="group">
    <a class="btn btn-default" href="<?= $auth_path ?>/github?redirect=<?= $redirect ?>">GitHub</a>
    <a class="btn btn-default" href="<?= $auth_path ?>/google?redirect=<?= $redirect ?>">Google</a>
    <?php if ($debug) : ?>
    <a class="btn btn-default" href="<?= $auth_path ?>/debug?redirect=<?= $redirect ?>">Debug</a>
    <?php endif ?>
  </div>
</section>
```

## Twig

```twig
{% extends '@layout/default.html.twig' %}

{% block title %}404 Not Found{% endblock %}

{% block content %}
<section class="col-md-8 col-md-offset-2 code_401">
  <h2>Unauthorized</h2>

  <p>
    You are not logged in, and therefore cannot perform this action.
  </p>

  <p>
    Login to continue:
  <p>

  <div class="btn-group-vertical" role="group">
    <a class="btn btn-default" href="{{ auth_path }}/github?redirect={{ redirect }}">GitHub</a>
    <a class="btn btn-default" href="{{ auth_path }}/google?redirect={{ redirect }}">Google</a>
    {% if debug is defined %}
    <a class="btn btn-default" href="{{ auth_path }}/debug?redirect={{ redirect }}">Debug</a>
    {% endif %}
  </div>
</section>
{% endblock %}
```

## laminas-view

```php
<?php $this->headTitle('Unauthorized') ?>

<section class="col-md-8 col-md-offset-2 code_401">
  <h2>Unauthorized</h2>

  <p>
    You are not logged in, and therefore cannot perform this action.
  </p>

  <p>
    Login to continue:
  <p>

  <div class="btn-group-vertical" role="group">
    <a class="btn btn-default" href="<?= $this->auth_path ?>/github?redirect=<?= $this->redirect ?>">GitHub</a>
    <a class="btn btn-default" href="<?= $this->auth_path ?>/google?redirect=<?= $this->redirect ?>">Google</a>
    <?php if ($debug) : ?>
    <a class="btn btn-default" href="<?= $this->auth_path ?>/debug?redirect=<?= $this->redirect ?>">Debug</a>
    <?php endif ?>
  </div>
</section>
```

## Mustache

This example can be used via the 
[phly-mezzio-mustache](https://github.com/phly/phly-mezzio-mustache)
renderer for mezzio-template:

```mustache
{{<layout::layout}}
{{$title}}Unauthorized{{/title}}
{{$content}}
<section class="col-md-8 col-md-offset-2 code_401">
  <h2>Unauthorized</h2>

  <p>
    You are not logged in, and therefore cannot perform this action.
  </p>

  <p>
    Login to continue:
  <p>

  <div class="btn-group-vertical" role="group">
    <a class="btn btn-default" href="{{auth_path}}/github?redirect={{redirect}}">GitHub</a>
    <a class="btn btn-default" href="{{auth_path}}/google?redirect={{redirect}}">Google</a>
    {{#debug}}
    <a class="btn btn-default" href="{{auth_path}}/debug?redirect={{redirect}}">Debug</a>
    {{/debug}}
  </div>
</section>
{{/content}}
{{/layout::layout}}
```
