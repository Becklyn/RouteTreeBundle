BecklynRouteTreeBundle
======================

Adds a simple implementation for an automatic generation of a route tree to build a menu.


## Installation

Install the bundle via [packagist](https://packagist.org/packages/becklyn/route-tree-bundle):

```javascript
    // ...
    require: {
        // ...
        "becklyn/route-tree-bundle": "^2.0"
    },
    // ...
```

Load the bundle in your `app/AppKernel.php`:

```php
    public function registerBundles()
    {
        $bundles = array(
            // ...
            new \Becklyn\RouteTreeBundle\BecklynRouteTreeBundle(),
        );
        // ...
    }
```


## Defining the route tree nodes
You add elements to the tree by setting the options of the routes:

```yml
homepage:
    path: /

my_route:
    path: /my_route
    options:
        tree:
            parent: homepage
            title:  "My Route"
```

## Getting the route tree
You can directly get the service `becklyn.route_tree` and load the tree from there.

```php
    $routeTreeModel = $container->get("becklyn.route_tree");

    // get a node with a specific route. With this node you can traverse the route tree.
    $tree1 = $routeTreeModel->getNode("my_route");
```

The return value is a `Becklyn\RouteTreeBundle\Tree\Node`.


## All path options

```yml
route:
    options:
        tree:
            parent:     homepage       # the name of the parent node
            title:      "abc"          # (optional) title of the node
            hidden:     false          # (optional) whether the node should be hidden when rendering
            parameters: {}             # (optional) the default values for the parameters
            separator:  null           # (optional) where to place a separator
```

`parent` must be set. Also all referenced `parent`-routes need to exist.


### Hidden
The `hidden` flag hides the menu item (including all children) when rendering:


### Parameters
The parameters can define default values for parameters:

```yml
page_listing:
    path: /listing/{page}
    options:
        tree:
            parameters:
                page: 1
```

**If you do not define a value, the parameter is looked up in the request attributes of the current request. If it doesn't find anything there, `null` is used.**


### Separator
You can place a separator before or after any menu item. The used template must properly support it.

In the bootstrap theme this is only properly supported for dropdown menus. Please note, that adjacent separators are not merged.

Possible values:
*   `null`: no separator (default)
*   `before`: place separator right before this item
*   `after`: place separator right after this item


### Error Cases
If the page tree is invalid a `InvalidRouteTreeException` is thrown, on the first construction of the page tree.
If the configuration of a node is not correct, a `InvalidNodeDataException` is thrown.


## KnpMenuBundle MenuBuilder
There is an automatic menu builder, based on the route tree. You need to define your menus as service, the menu builder service is named `becklyn.route_tree.knp_menu.menu_builder`.

Just add the definition to your `services.yml` this is pretty much based on [the official KnpMenuBundle documentation](https://github.com/KnpLabs/KnpMenuBundle/blob/master/Resources/doc/menu_service.md).
```yml
my_app.menu:
    class: Knp\Menu\MenuItem
    factory: ["@becklyn.route_tree.knp_menu.menu_builder", buildMenu]
    arguments: ["my_route"]         # <- the starting route
    tags:
        - { name: knp_menu.menu, alias: menu_name }
```

The menu builder will use your `title` as link text. If no `title` is set, the route name is used.


### Bootstrap compatible menu renderer
A Bootstrap 3 compatible menu renderer is included in the bundle.
It will render the inner `<ul class="nav navbar-nav">` of the navbar for you.

```jinja
{{ routeTreeBootstrapMenu("menu_name") }}
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

* It should be fast. The tree is not (yet?) cached, so the generation should be fast.
* No existing site should break by just activating the bundle.
* The declaration effort should be low but avoid clashes.
* "Fake" parameters, only generation with `null` as default parameters
