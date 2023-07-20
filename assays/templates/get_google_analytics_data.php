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
      <p>Number of unique visitors: {{ data.totalsForAllResults['ga:visitors'] }}</p>
    </div>

  </div>
</div>

{% endblock %}