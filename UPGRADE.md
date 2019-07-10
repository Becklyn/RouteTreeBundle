3.x to 4.0
==========

*   Use `becklyn/menu` instead of `knplabs/knp-menu-bundle`.
*   The twig function `route_tree_menu()` was removed. Directly use `route_tree_render()`.
*   Removed parameter handling in regular menu rendering (it is only available when rendering a breadcrumb). Implement a menu visitor
    to set custom parameters instead.
