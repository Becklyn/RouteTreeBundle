BecklynPageTreeBundle
=====================

Adds a simple implementation for an automatic generation of a page tree to build a menu.


## Installation

Install the bundle via [packagist](https://packagist.org/packages/becklyn/page-tree-bundle):

```javascript
    // ...
    require: {
        // ...
        "becklyn/page-tree-bundle": "~1.0"
    },
    // ...
```

Load the bundle in your `app/AppKernel.php`:

```php
    public function registerBundles()
    {
        $bundles = array(
            // ...
            new \Becklyn\PageTreeBundle\BecklynPageTreeBundle(),
        );
        // ...
    }
```


## Defining the page tree nodes
You add elements to the page tree by setting the options of the routes:

```yml
homepage:
    path: /

my_route:
    path: /my_route
    options:
        page_tree:
            parent: homepage
            title:  "My Route"
```

All routes without the `page_tree` option are not included in the page tree.


## Getting the page tree
You can directly get the service `becklyn.page_tree` and load the page tree from there.

```php
    $pageTreeModel = $container->get("becklyn.page_tree");

    // the complete page tree
    $tree1 = $pageTreeModel->getPageTree();

    // the tree under my_route, including all child nodes, excluding my_route
    $tree2 = $pageTreeModel->getPageTree("my_route");
```

The return value is a list of nodes.


## All path options

```yml
route:
    options:
        page_tree:
            parent:     homepage       # the name of the parent node
            is_root:    false          # whether this is a root node
            title:      "abc"          # (optional) title of the node
            hidden:     false          # (optional) whether the node should be hidden when rendering
            parameters: {}             # the default values for the parameters
```

Either `parent` or `is_root` (must be `true`) must be set.
Also all referenced `parent`-routes need to exist.

*Notice:* if you pass _both_ `parent` and `is_root` the parent will be discarded and it will be a root page.


### Hidden
The `hidden` flag hides the menu item (including all children) when rendering:

```html
<ul>
    <!-- ... -->
    <li style="display:none">
        <a href="#">This is hidden</a>
    </li>
    <!-- ... -->
</ul>
```

This is necessary, because currently (v2.0.0@alpha) there is only one pagetree in the KnpMenuBundle, which is used for both rendering and voting on the active element.
So if you want to display active parents without including the actual element in the menu, it needs to be in the tree, but hidden in the generated HTML.

Please note: if you use the bootstrap menu renderer, the hidden items are correctly stripped from the HTML, instead of hiding the via CSS.


### Parameters
The parameters can define default values for parameters:

```yml
page_listing:
    path: /listing/{page}
    options:
        page_tree:
            parameters:
                page: 1
```

**If you do not define a default value, `1` is used**

You can use the expression language for the default values.
Currently supported functions:
* `date()` (wraps the [PHP date](php.net/manual/en/function.date.php) function, for the current timestamp)

```yml
calendar:
    path: /calendar/{year}
    options:
        page_tree:
            parameters:
                year: "date('Y')"
```


### Error Cases
If the page tree is invalid a `InvalidPageTreeException` is thrown, on the first construction of the page tree.


## KnpMenuBundle MenuBuilder
There is an automatic menu builder, based on the page tree. You need to define your menus as service, the menu builder service is named `becklyn.page_tree.menu_builder`.

Just add the definition to your `services.yml` this is pretty much based on [the official KnpMenuBundle documentation](https://github.com/KnpLabs/KnpMenuBundle/blob/master/Resources/doc/menu_service.md).
```yml
my_app.menu:
    class: Knp\Menu\MenuItem
    factory_service: becklyn.page_tree.menu_builder
    factory_method: buildMenu
    arguments: ["my_route"]    # <- the starting route. Can be left empty (or pass null explicitly), to include the complete page tree
    tags:
        - { name: knp_menu.menu, alias: menu_name }
```

The menu builder will use your `title` as link text. If no `title` is set, the route name is used.


### Bootstrap compatible menu renderer
A Bootstrap 3 compatible menu renderer is included in the bundle.
It will render the inner `<ul class="nav navbar-nav">` of the navbar for you.

```jinja
{{ renderPageTreeBootstrapMenu("menu_name") }}
```

It will automatically strip all hidden menu items from the HTML - therefore the known `li.first` and `li.last` from the default KnpMenu theme will not be included.

It also has new options, in addition to the existing KnpMenu options:

```yaml
hoverDropdown: true
```

If you set `hoverDropdown` to true, the `data-toggle="dropdown"` from the items with dropdown is removed - you could add a hover
functionality via CSS then ([as shown here](https://gist.github.com/apfelbox/8541060#file-hover-navbar-css)).


## Known limitations
This is a pretty simple implementation, which is intended:

* It should be fast. The page tree is not (yet?) cached, so the generation should be fast.
* No existing site should break by just activating the bundle.
* The declaration effort should be low but avoid clashes.
* The title can currently not be changed dynamically, it needs to be "hardcoded" in your route definition.
* Fake parameters, only generation with `1` as parameters

#### Fake parameters
To build a complete page tree, the bundle needs to build URLs for every route inside the page tree.
This bundle's main use case is to build a "hidden" menu, which only marks the top level main menu elements as active. Therefore it is valid to just use "fake" route parameters and fill everything with 1 (the voter needs to only compare the routes and not the route parameters).