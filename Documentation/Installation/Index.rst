..  include:: /Includes.rst.txt

..  _installation:

============
Installation
============

Installation Type
=================

Composer
--------

You can install tile proxy with the following shell command:

.. code-block:: bash

   composer req codemacher/tile_proxy

Extensionmanager
----------------

If you want to install tile proxy traditionally with ExtensionManager, follow these steps:

#. Open ExtensionManager

#. Switch over to `Get Extensions`

#. Search for `tile proxy`

#. Install extension


Include TypoScript template
===========================

It is necessary to include at least the basic TypoScript provided by this
extension.

Go module :guilabel:`Web > Template` and chose your root page. It should
already contain a TypoScript template record. Switch to view
:guilabel:`Info/Modify` and click on :guilabel:`Edit the whole template record`.

Switch to tab :guilabel:`Includes` and add the following templates from the list
to the right: :guilabel:`Tile Proxy (tile_proxy)`.

Read more about possible configurations via TypoScript in the
:ref:`Reference <typoscript>` section.

