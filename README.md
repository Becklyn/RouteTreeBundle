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
            title: "My Route"
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
            parent:  homepage       # the name of the parent node
            is_root: true           # whether this is a root node
            title:   "abc"          # optional title of the node
```

Either `parent` or `is_root` (must be `true`) must be set.
Also all referenced `parent`-routes need to exist.

*Notice:* if you pass _both_ `parent` and `is_root` the parent will be discarded and it will be a root page.

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


## Known limitations
This is a pretty simple implementation, which is intended:

* It should be fast. The page tree is not (yet?) cached, so the generation should be fast.
* No existing site should break by just activating the bundle.
* The declaration effort should be low but avoid clashes.
* The title can currently not be changed dynamically, it needs to be "hardcoded" in your route definition.