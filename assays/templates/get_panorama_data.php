{% extends layout_template_name %}
{% block styles_head %}
  {{ parent() }}
  <link href="/site/library/css/styles.css" rel="stylesheet" type="text/css" />
  <link href="/assays/library/css/styles.css" rel="stylesheet" type="text/css" />
{% endblock %}
{% block content %}
{% include 'secondary_navigation.php' %}
<div id="genSlotMainNav">
  {% include 'top_navigation.php' %}
</div>
<div class="row-outer row-outer-details">

  <h3>{{ page_title }}</h3>

  <div class="row-fluid" style="clear:both;">
    {% if flash['success'] %}
      <div class="alert alert-success">
        {{ flash['success'] }}
      </div>
    {% endif %}

    <div class="span12">
      <h4>Query name: Peptide</h4>
      <hr>
      {% for key, field in data %}
        {% for k, f in field %}
          <dl>
          <dt>{{ k }}</dt>
          <dd>{{ f }}</dd>
        </dl>
        {% endfor %}
      {% endfor %}
    </div>

  </div>
</div>

{% endblock %}
{% block js_head %}
  {{ parent() }}

{% endblock %}
{% block js_bottom %}
  {{ parent() }}

{% endblock %}