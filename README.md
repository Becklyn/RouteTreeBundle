BecklynRouteTreeBundle
======================

Adds a simple implementation for an automatic generation of a route tree to build a menu.



Installation
------------

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



Defining The Route Tree Nodes
-----------------------------
You add elements to the tree by setting the options of the routes:

```yaml
homepage:
    path: /

my_route:
    path: /my_route
    options:
        tree:
            parent: homepage
            title:  "My Route"
```



Path Options
------------

```yaml
route:
    # ...
    options:
        tree:
            parent:     homepage       # the name of the parent node
            priority:   0              # (optional) the 
            title:      "abc"          # (optional) title of the node
            parameters: {}             # (optional) the default values for the parameters
            security:   ~              # (optional) the security expression
```

`parent` must be set. Also all referenced `parent`-routes need to exist.


### Priority
All child nodes of all nodes are sorted by descending priority.


### Hidden
All items are automatically hidden, if they have no title set (or if the security expression evaluates to false).


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

**If you do not define a value, the parameter is looked up in the request attributes of the current request. If it doesn't find anything there, `1` is used.**


### Extra Parameters
You can define additional parameters, that can be used in the menu template.
All path options that are not recognized (see "Path Options") are automatically added as extra parameters.

```yaml
route:
    options:
        tree:
            parent: homepage
            title: Pages
            icon: pages
```

These extra parameters are available in the template under the `extra` property:

```twig
{{ item.extra("icon") }}
```


### Error Cases
If the page tree is invalid a `InvalidRouteTreeException` is thrown, on the first construction of the page tree.



KnpMenuBundle MenuBuilder
-------------------------

There is an automatic menu builder, that you can just use in the templates:

```twig
{{- knp_menu_render(route_tree_menu("my_route"), {...}) -}}
```


Getting The Route Tree
----------------------
You can inject the service `Becklyn\RouteTreeBundle\Tree\RouteTree` and use it to retrieve a node:

```php
    // get a node with a specific route. With this node you can traverse the route tree.
    $treeUnderMyRoute = $routeTree->getNode("my_route");
```

The return value is a `Becklyn\RouteTreeBundle\Node\Node`.
