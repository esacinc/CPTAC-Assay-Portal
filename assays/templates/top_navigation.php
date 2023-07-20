<div class="contentid slot-item only-SI navbar navbar-inverse">
  <ul class="genSiteAdditionalMainNav">
    <li><a href="http://proteomics.cancer.gov/"><span>CPTAC Home</span></a></li>
  </ul>
  <button type="button" class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
    <span class="icon-bar"></span>
    <span class="icon-bar"></span>
    <span class="icon-bar"></span>
  </button>

  <ul id="menu-top-menu" class="genSiteMainNav">
    <li id="menu-item-189" class="menu-item menu-item-type-custom menu-item-object-custom menu-item-189 current-menu-item">
      <a href="/"><span>Available Assays</span></a>
    </li>
    <li id="menu-item-16" class="menu-item menu-item-type-custom menu-item-object-custom menu-item-189">
      <a href="/about/"><span>About</span></a>
      <ul>
        <li><a href="/about/resources/"><span>Resources</span></a></li>
        <li><a href="/about/faq/"><span>FAQ</span></a></li>
        <li><a href="/contact/"><span>Contact Us</span></a></li>
      </ul>
    </li>
  </ul>

{#   <ul id="menu-top-menu" class="genSiteMainNav">
  {% for item in menu %}
      {% set class = '' %}
      {% if item.title == 'Available Assays' %}
        {% set class = ' class=current-menu-item' %}
      {% endif %}
      <li id="menu-item"{{class}}>
        <a href="{{ item.link }}"><span>{{ item.title }}</span></a>
        {% if item.menu_item_parent == 'true' %}
          <ul>
              {% for child in item.get_children %}
                <li><a href="{{child.get_path}}"><span>{{child.title}}</span></a></li>
              {% endfor %}
            </ul>
         {% endif %}
      </li>
  {% endfor %}
  </ul> #}

</div>


{#
          <div class="contentid slot-item only-SI navbar navbar-inverse">
            <ul class="genSiteAdditionalMainNav">
              <li><a href="http://proteomics.cancer.gov/"><span>CPTAC Home</span></a></li>
            </ul>
              <ul id="menu-top-menu" class="genSiteMainNav">
              {% for item in main_nav.get_items %}
                  <li id="menu-item-{{ item.ID }}" class="{{ item.class }}">
                    <a href="{{ item.link }}"><span>{{ item.title }}</span></a>
                    {% if item.get_children %}
                      <ul>
                          {% for child in item.get_children %}
                            <li><a href="{{child.get_path}}"><span>{{child.title}}</span></a></li>
                          {% endfor %}
                        </ul>
                     {% endif %}
                  </li>
              {% endfor %}
              </ul>
          </div>
#}