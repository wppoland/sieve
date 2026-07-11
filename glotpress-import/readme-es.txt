=== Sieve - Faceted Filter for WooCommerce ===
Contributors: motylanogha
Tags: woocommerce, filter, faceted search, product filter, ajax filter
Requires at least: 6.4
Tested up to: 7.0
Requires PHP: 8.1
Stable tag: 1.0.2
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Filtrado de productos por facetas rápido y accesible para WooCommerce: filtros AJAX, un cajón móvil y Core Web Vitals por diseño.

== Description ==

Sieve ofrece a tus compradores una forma rápida y moderna de encontrar productos. Marcan algunas casillas, arrastran un rango de precios, escriben una palabra clave y la cuadrícula se actualiza al instante sin recargar la página. Está pensado para resultar cómodo y mantenerse rápido en catálogos grandes, con widgets accesibles, un cajón de filtros móvil y un enfoque de renderizado diseñado para Core Web Vitals: sin saltos de diseño cuando cambian los resultados.

Todo funciona contra un índice prediseñado, así que el filtrado se mantiene rápido incluso con miles de productos, y los recuentos junto a cada opción se actualizan en directo a medida que los compradores acotan la búsqueda.

Filtrado que se siente instantáneo:

* Filtrado AJAX sin recargar la página y URL que se pueden compartir y guardar como favoritos
* Recuentos dependientes en directo que se actualizan a medida que se aplican los filtros
* Chips de filtro activo, restablecimiento con un clic, ordenación y paginación integrados

Cada tipo de faceta que necesitas:

* Casillas de verificación, botones de opción y desplegables con búsqueda
* Muestras de color e imagen, categorías jerárquicas (árbol)
* Autocompletado (opciones con búsqueda) y un índice A-Z
* Controles deslizantes de rango, búsqueda por palabra clave, ordenación, paginación y restablecimiento

Filtra por cualquier cosa de tu catálogo:

* Categorías, etiquetas y atributos de producto
* Precio, estado de existencias y en oferta

Búsqueda predictiva de productos:

* Un desplegable de escritura anticipada instantáneo con miniaturas, precios, coincidencias de SKU y categorías, y navegación completa con el teclado

Rápido y accesible por diseño:

* Índice prediseñado para consultas filtradas rápidas en catálogos grandes
* Cajón de filtros móvil con una barra «Aplicar» fija
* Widgets compatibles con el teclado y los lectores de pantalla
* Core Web Vitals por diseño: sin saltos de diseño cuando se actualizan los resultados

Fácil de colocar y configurar:

* Bloque «Sieve Filter» de Gutenberg, un widget «Sieve Filter» de Elementor y el shortcode `[sieve]`
* Un generador visual de facetas en la administración: añade, reordena y cambia el tipo de las facetas, define el diseño y reconstruye el índice

= Sieve PRO =

Sieve PRO añade control avanzado e integraciones para tiendas en crecimiento:

* Faceta de valoración con estrellas visuales
* Reglas de facetas condicionales: muestra u oculta facetas por categoría, página de tienda o rol de cliente
* Pruebas A/B de diseño para encontrar la disposición de filtros que mejor convierte
* Panel de rendimiento: tamaño del índice, cobertura del catálogo y pruebas de velocidad de filtrado
* Integraciones de búsqueda: SearchWP y Algolia, con respaldo nativo

Documentación: https://plogins.com/es/sieve/docs/

= You may also like these plugins =

Más plugins gratuitos de WooCommerce de WPPoland:

* [Plogins Tiers](https://wordpress.org/plugins/plogins-tiers/) - niveles de precios por cantidad y volumen con una tabla de precios renderizada en el servidor.
* [Plogins Waitlist](https://wordpress.org/plugins/plogins-waitlist/) - lista de espera de vuelta a stock que envía un correo electrónico a los compradores en cuanto un producto vuelve a estar disponible.
* [Polski for WooCommerce](https://wordpress.org/plugins/polski/) - cumplimiento del mercado polaco: GPSR, Omnibus, RGPD, facturas y módulos de tienda.

Consulta el catálogo completo en https://plogins.com/es/ .

== Installation ==

1. Instala y activa WooCommerce.
2. Instala Sieve y actívalo.
3. Abre el menú Sieve, reconstruye el índice y ajusta el conjunto de facetas si es necesario.
4. Coloca el filtro en cualquier página con el shortcode `[sieve]` o el bloque «Sieve Filter».

== Frequently Asked Questions ==

= Documentation and links =

* <strong>Documentación</strong> - https://plogins.com/es/sieve/docs/
* <strong>Página del plugin</strong> - https://plogins.com/es/sieve/
* <strong>Código fuente</strong> - https://github.com/wppoland/sieve
* <strong>Informes de errores y peticiones de funciones</strong> - https://github.com/wppoland/sieve/issues
* <strong>Debates y preguntas</strong> - https://github.com/wppoland/sieve/discussions


= Does it require WooCommerce? =
Sí. Sieve filtra los archivos de productos de WooCommerce y cualquier página donde coloques el filtro.

= Does filtering reload the page? =
No. El filtrado se realiza a través de AJAX y la URL se mantiene sincronizada, así que los resultados se pueden compartir.

= What can shoppers filter by? =
Sieve puede filtrar productos de WooCommerce por categorías, etiquetas, atributos, precio, estado de existencias, estado de oferta y un campo de búsqueda por palabra clave. También admite controles deslizantes de rango, opciones con búsqueda, muestras de color/imagen, chips de filtro activo y ordenación.

= Is it fast on large stores? =
Sí. Sieve crea un índice de filtro de productos, así que las peticiones de filtrado AJAX no necesitan ejecutar uniones en directo lentas para cada consulta de categoría, atributo y precio.

= How do I add the filter to a page? =
Usa el shortcode `[sieve]` o el bloque «Sieve Filter». Ambos renderizan juntos las facetas, la cuadrícula de resultados, la ordenación, los chips de filtro activo y la paginación.

= How do I add the predictive search box? =
Usa el shortcode `[sieve_search]` o el bloque «Sieve Search». A medida que los compradores escriben, un desplegable muestra los productos coincidentes con miniaturas y precios; es totalmente accesible con el teclado y recurre a la búsqueda de productos estándar cuando JavaScript no está disponible.

= Is Sieve accessible? =
Sí. La interfaz del filtro está pensada para el uso con teclado y lectores de pantalla, con regiones etiquetadas, controles accesibles, anuncios corteses del recuento de resultados y compatibilidad con el movimiento reducido.

= Does Sieve work on mobile? =
Sí. Sieve incluye un cajón de filtros móvil con una barra «Aplicar» fija, para que los compradores puedan filtrar productos sin pelearse con una larga barra lateral en pantallas pequeñas.

= Does this plugin work on WordPress Multisite? =

Sí. Este plugin es compatible con WordPress Multisite. Actívalo para toda la red o en sitios concretos; cada sitio conserva sus propios ajustes y datos.

== Screenshots ==

1. Filtrado por facetas en la página de un producto: facetas de categorías, rango de precios, disponibilidad y oferta con recuentos dependientes en directo, chips de filtro activo y una cuadrícula de resultados.
2. Cajón de filtros móvil con una barra fija «Mostrar resultados».
3. El generador de facetas: añade, reordena y cambia el tipo de las facetas, define el diseño y reconstruye el índice.

== Development ==

La fuente completa y legible por humanos de los recursos compilados se incluye en este plugin en `resources/`, junto con las herramientas de compilación (`package.json`, `scripts/build-wp.mjs`). Los archivos compilados en `build/` se generan a partir de esas fuentes. Para reconstruirlos:

1. `npm install`
2. `npm run build`

Utiliza Vite (scripts de administración y de frontend) y @wordpress/scripts (bloques). No hay ofuscación; cada recurso distribuido se puede regenerar a partir de las fuentes incluidas. El repositorio de fuentes público también está disponible en https://github.com/wppoland/sieve.

== Translations ==

Sieve incluye traducciones al polaco, al alemán y al español para la interfaz del plugin. El dominio de texto es `sieve`, por lo que los paquetes de idioma de WordPress.org también pueden sustituir o ampliar estas traducciones incluidas.

== Changelog ==

= 1.0.2 =
* Se han añadido traducciones incluidas al polaco, al alemán y al español para la interfaz del plugin.

= 1.0.1 =
* Primera versión estable.

= 0.9.7 =
* Documentación: se añadió una sección «También te puede gustar» que enlaza los otros plugins gratuitos de WooCommerce de WPPoland. Sin cambios funcionales.

= 0.9.6 =
* Nuevo: widgets de Elementor para la búsqueda y el filtrado de productos (funciona en Elementor 3.x y 4.0).

= 0.9.5 =
* Administración: se corrige el texto de ayuda del índice que se mostraba en cada fila de faceta; se añaden avisos de índice vacío y faceta vacía, un selector de fuente agrupado, mensajes de error al guardar/reindexar y un estado de fallo de carga.
* Administración: ayuda en línea de los estilos preestablecidos en el panel Apariencia.

= 0.9.4 =
* Extensión: filtro `sieve_search_product_ids` en `SearchResolver` para que las extensiones PRO puedan enrutar la faceta de búsqueda en la cuadrícula y la búsqueda predictiva a través de SearchWP o Algolia.

= 0.9.3 =
* Extensión: filtros `sieve_facet_body`, `sieve_facet_types` y `sieve_facet_catalog` más `FacetTypeRegistry` en la respuesta REST del catálogo de administración para que las extensiones PRO puedan registrar presentaciones de facetas avanzadas (por ejemplo, valoración con estrellas).

= 0.9.2 =
* Extensión: filtro `sieve_settings` y ajuste `layout` (barra lateral, apilado, en línea) para que las extensiones PRO puedan alternar los diseños del panel de filtros y el número de columnas. FilterEngine aplica clases modificadoras de diseño en `.sieve-app`.

= 0.9.1 =
* Extensión: filtro `sieve_facets` y contexto de página (`FacetContext`) para que las extensiones PRO puedan mostrar u ocultar facetas por categoría, página de tienda o rol de cliente. Las peticiones AJAX conservan el contexto mediante las variables de consulta `sf_ctx_*`.

= 0.9.0 =
* Pulido: una interfaz de filtro renovada y más atractiva. Grupos de facetas plegables, una fila de chips de «Filtros activos» con botones de eliminación más claros, un estado vacío amable con una acción de «Borrar todos los filtros» con un solo clic, un indicador de carga accesible y un mensaje de error que se puede reintentar si una actualización falla.
* Diseño: propiedades personalizadas de CSS adaptables al tema, tamaño fluido, modo oscuro automático (prefers-color-scheme) y transiciones elegantes que respetan prefers-reduced-motion. Sin saltos de diseño cuando se aplican los filtros.
* Administración: ayuda en línea en cada ajuste, incluida una breve descripción de cómo se ve cada tipo de faceta para los compradores.
* Accesibilidad: los grupos de facetas exponen su estado expandido/contraído, los recuentos de resultados se anuncian con cortesía, las regiones de paginación y de filtro están etiquetadas, y los botones de eliminación tienen nombres claros y accesibles.

= 0.8.2 =
* Cumplimiento: se documentó el repositorio de fuentes público y los pasos de compilación de los recursos compilados (directrices para plugins de WordPress.org).

= 0.8.1 =
* Internacionalización: las cadenas de la interfaz JavaScript de la administración y del frontend ya se incluyen en la plantilla de traducción, así que todo el plugin (no solo la parte PHP) se puede traducir por completo.

= 0.8.0 =
* Nuevo: ajustes de apariencia. Elige un estilo preestablecido (Predeterminado, Mínimo, Con bordes, Suave, Sin estilo) y personaliza desde la administración los colores de acento, borde, texto atenuado y fondo, con una vista previa en directo y una pista de contraste. Se aplica tanto al filtro como a la búsqueda predictiva. Cero peticiones adicionales, sin saltos de diseño, totalmente compatible con versiones anteriores.

= 0.7.0 =
* La búsqueda ahora se comporta como un filtro: escribe para acotar la cuadrícula en directo sobre la marcha con sugerencias predictivas, tolerantes a diacríticos y a erratas, combinables con cualquier faceta y seguras para la URL y el botón Atrás. Los recuentos de facetas dependientes también reflejan ahora la búsqueda activa.

= 0.6.0 =
* La búsqueda predictiva ahora es insensible a los diacríticos y tolerante a las erratas. Coincide con los títulos de producto y los SKU ignorando las diferencias diacríticas (así «lozko» encuentra «łóżko»), tolera pequeñas erratas, y las categorías coincidentes se encuentran del mismo modo. Esta versión activa una reconstrucción única del índice de búsqueda.

= 0.5.0 =
* La búsqueda predictiva ahora va más allá de los títulos de producto: una pasada parcial por el SKU muestra un producto por su código aunque el título no coincida, y las categorías de producto coincidentes aparecen como su propio grupo en el desplegable para que el comprador pueda saltar directamente al archivo filtrado. Los resultados y las categorías se agrupan con encabezados, y la navegación con el teclado recorre ambos.

= 0.4.0 =
* Nuevos tipos de faceta: Autocompletado (un cuadro de búsqueda que filtra las propias opciones de una faceta mientras escribes, para facetas con muchos valores) e índice A-Z (una barra alfabética que filtra las opciones por la primera letra). Ambos filtran en el lado del cliente sin peticiones adicionales y se degradan a una lista de opciones simple sin JavaScript.

= 0.3.0 =
* Nuevo: búsqueda predictiva de productos. El shortcode `[sieve_search]` y el bloque «Sieve Search» renderizan un cuadro de búsqueda accesible con un desplegable de escritura anticipada instantáneo (miniaturas de producto, precios, SKU), navegación completa con el teclado y un enlace «ver todos los resultados». Construido sobre la búsqueda de productos de WooCommerce, cargado como un paquete ligero e independiente para que las páginas sin él sigan siendo rápidas.

= 0.2.0 =
* Nuevos tipos de faceta: muestras de color e imagen (con color/imagen por término, además de una estimación automática del color a partir de nombres de color habituales) y facetas de categorías jerárquicas (árbol) que muestran solo las ramas que llevan a resultados.

= 0.1.2 =
* Administración: filas del generador de facetas más limpias (controles alineados, botones de reordenar/eliminar agrupados con estado desactivado para el primero/último, la fuente del campo se muestra como pie).

= 0.1.1 =
* Cumplimiento: se añadió al propietario del plugin a la lista de colaboradores y se incluyeron las fuentes legibles por humanos y los pasos de compilación de los recursos compilados (directrices para plugins de WordPress.org).

= 0.1.0 =
* Versión MVP inicial: índice prediseñado, filtrado AJAX con estado en la URL, recuentos de facetas dependientes, facetas de casillas / radio / desplegable / rango / búsqueda, ordenación, chips de filtro activo, paginación, cajón de filtros móvil, generador de facetas en React, shortcode `[sieve]` y bloque «Sieve Filter».
