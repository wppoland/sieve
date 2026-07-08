=== Sieve - Faceted Filter for WooCommerce ===
Contributors: motylanogha
Tags: woocommerce, filter, faceted search, product filter, ajax filter
Requires at least: 6.4
Tested up to: 7.0
Requires PHP: 8.1
Stable tag: 1.0.1
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Filtrado de productos por facetas rápido y accesible para WooCommerce: filtros AJAX, un cajón móvil y Core Web Vitals por diseño.

== Description ==

Sieve ofrece a sus compradores una forma rápida y moderna de encontrar productos. Marcan algunas casillas, arrastran un rango de precios, escriben una palabra clave y la cuadrícula se actualiza instantáneamente sin recargar la página. Está diseñado para que resulte sencillo y rápido en catálogos grandes, con widgets accesibles, un cajón de filtros móvil y un enfoque de renderizado diseñado para Core Web Vitals: sin cambios de diseño cuando cambian los resultados.

Todo se compara con un índice prediseñado, por lo que el filtrado se mantiene rápido incluso con miles de productos, y los recuentos junto a cada opción se actualizan en vivo a medida que los compradores reducen la cantidad.

Filtrado que se siente instantáneo:

* Filtrado AJAX sin recarga de página y URL que se pueden compartir y marcar como favoritos
* Recuentos dependientes en vivo que se actualizan a medida que se aplican filtros
* Chips de filtro activo, reinicio con un clic, clasificación y paginación integrados

Cada tipo de faceta que necesitas:

* Casillas de verificación, botones de opción y menús desplegables de búsqueda
* Muestras de color e imagen, categorías jerárquicas (árbol)
* Autocompletar (opciones de búsqueda) y un índice A-Z
* Controles deslizantes de rango, búsqueda de palabras clave, clasificación, paginación y reinicio

Filtra por cualquier cosa en tu catálogo:

* Categorías, etiquetas y atributos de productos.
* Precio, estado de existencias y en oferta.

Búsqueda predictiva de productos:

* Un menú desplegable instantáneo de escritura anticipada con miniaturas, precios, coincidencias de SKU y categorías, y navegación completa con el teclado

Rápido y accesible por diseño:

* Índice prediseñado para consultas filtradas rápidas en catálogos grandes
* Cajón de filtro móvil con barra de aplicación adhesiva.
* Widgets compatibles con teclado y lector de pantalla
* Core Web Vitals por diseño: no hay cambios de diseño cuando se actualizan los resultados

Fácil de colocar y configurar:

* Bloque "Filtro de tamiz" de Gutenberg y el código corto `[sieve]`
* Un generador de facetas visuales en el administrador: añade, reordene y vuelva a escribir facetas, establezca el diseño y reconstruya el índice

= Sieve PRO =

Sieve PRO añade control avanzado e integraciones para tiendas en crecimiento:

* Faceta de calificación de estrellas con estrellas visuales
* Reglas de facetas condicionales: mostrar u ocultar facetas por categoría, página de tienda o rol de cliente
* Pruebas de diseño A/B para encontrar el diseño de filtro que convierta mejor
* Panel de rendimiento: tamaño del índice, cobertura del catálogo y puntos de referencia de velocidad del filtro
* Integraciones de búsqueda: SearchWP y Algolia, con respaldo nativo

Documentación: https://plogins.com/es/sieve/docs/

= You may also like these plugins =

Más complementos gratuitos de WooCommerce de WPPoland:

* [Plogins Tiers](https://wordpress.org/plugins/plogins-tiers/): niveles de precios por cantidad y volumen con una tabla de precios generada por el servidor.
* [Plogins Waitlist](https://wordpress.org/plugins/plogins-waitlist/): lista de espera de disponibilidad de existencias que envía un correo electrónico a los compradores en el momento en que regresa un producto.
* [Polski for WooCommerce](https://wordpress.org/plugins/polski/) - Cumplimiento del mercado polaco: GPSR, Omnibus, GDPR, facturas y módulos de escaparate.

Consulta el catálogo completo en https://plogins.com/es/.

== Installation ==

1. Instale y active WooCommerce.
2. Instale Sieve y actívelo.
3. Abra el menú Tamiz, reconstruya el índice y ajuste el conjunto de facetas si es necesario.
4. Coloque el filtro en cualquier página con el código abreviado `[sieve]` o el bloque "Filtro de tamiz".

== Frequently Asked Questions ==

= Documentation and links =

* <strong>Documentación</strong> - https://plogins.com/es/sieve/docs/
* <strong>Página de complementos</strong> - https://plogins.com/es/sieve/
* <strong>Código fuente</strong> - https://github.com/wppoland/sieve
* <strong>Informes de errores y solicitudes de funciones</strong> - https://github.com/wppoland/sieve/issues
* <strong>Discusiones y preguntas</strong> - https://github.com/wppoland/sieve/discussions


= Does it require WooCommerce? =
Sí. Sieve filtra los archivos de productos de WooCommerce y cualquier página donde coloque el filtro.

= Does filtering reload the page? =
No. El filtrado se realiza a través de AJAX y la URL se mantiene sincronizada para que los resultados se puedan compartir.

= What can shoppers filter by? =
Sieve puede filtrar productos WooCommerce por categorías, etiquetas, atributos, precio, estado de existencias, estado de venta y un campo de búsqueda de palabras clave. También admite controles deslizantes de rango, opciones de búsqueda, muestras de color/imagen, chips de filtro activo y clasificación.

= Is it fast on large stores? =
Sí. Sieve crea un índice de filtro de productos, por lo que las solicitudes de filtro AJAX no necesitan ejecutar uniones en vivo lentas para cada consulta de categoría, atributo y precio.

= How do I add the filter to a page? =
Utilice el código abreviado `[sieve]` o el bloque "Filtro de tamiz". Ambos renderizan las facetas, la cuadrícula de resultados, la clasificación, los chips de filtro activo y la paginación juntos.

= How do I add the predictive search box? =
Utilice el código corto `[sieve_search]` o el bloque "Sieve Search". A medida que los compradores escriben, un menú desplegable muestra productos coincidentes con miniaturas y precios; es totalmente accesible mediante teclado y recurre a la búsqueda de productos estándar cuando JavaScript no está disponible.

= Is Sieve accessible? =
Sí. La interfaz de usuario del filtro está diseñada para el uso del teclado y lectores de pantalla, con regiones etiquetadas, controles accesibles, anuncios educados de recuento de resultados y soporte de movimiento reducido.

= Does Sieve work on mobile? =
Sí. Sieve incluye un cajón de filtro móvil con una barra de aplicación adhesiva, para que los compradores puedan filtrar productos sin tener que luchar con una larga barra lateral en pantallas pequeñas.

= Does this plugin work on WordPress Multisite? =

Sí. Este complemento es compatible con WordPress Multisite. Activarlo en red o activarlo en sitios individuales; Cada sitio mantiene su propia configuración y datos.

== Screenshots ==

1. Filtrado por facetas en la página de un producto: categorías, rango de precios, disponibilidad y facetas de oferta con recuentos dependientes en vivo, chips de filtro activo y una cuadrícula de resultados.
2. Cajón de filtro móvil con una barra adhesiva "Mostrar resultados".
3. El generador de facetas: añade, reordene y vuelva a escribir facetas, establezca el diseño y reconstruya el índice.

== Development ==

La fuente completa y legible por humanos para los activos compilados se incluye en este complemento en `resources/`, junto con las herramientas de compilación (`package.json`, `scripts/build-wp.mjs`). Los archivos compilados en `build/` se generan a partir de esas fuentes. Para reconstruirlos:

1. `instalación npm`
2. `npm ejecutar compilación`

Esto utiliza Vite (scripts de administración y front-end) y @wordpress/scripts (bloques). No hay confusión; Cada activo enviado se puede regenerar a partir de las fuentes incluidas. El repositorio de fuentes públicas también está disponible en https://github.com/wppoland/sieve.

== Changelog ==

= 1.0.1 =
* Primera versión estable.

= 0.9.7 =
* Documentos: se agregó una sección "También te puede gustar" que vincula los otros complementos gratuitos de WPPoland WooCommerce. Sin cambios funcionales.

= 0.9.6 =
* Nuevo: widgets de Elementor para búsqueda y filtrado de productos (funciona en Elementor 3.x y 4.0).

= 0.9.5 =
* Administrador: corrige el texto de ayuda del índice que se muestra en cada fila de facetas; añade avisos de índice vacío y facetas vacías, selector de fuente agrupado, mensajes de error para guardar/volver a indexar y estado de falla de carga.
* Administrador: ayuda en línea con estilo preestablecido en el panel Apariencia.

= 0.9.4 =
* Extensión: filtro `sieve_search_product_ids` en `SearchResolver` para que los complementos PRO puedan enrutar la faceta de búsqueda en la cuadrícula y la búsqueda predictiva a través de SearchWP o Algolia.

= 0.9.3 =
* Extensión: filtros `sieve_facet_body`, `sieve_facet_types` y `sieve_facet_catalog` más `FacetTypeRegistry` en la respuesta REST del catálogo de administración para que los complementos PRO puedan registrar presentaciones de facetas avanzadas (por ejemplo, calificación de estrellas).

= 0.9.2 =
* Extensión: filtro `sieve_settings` y configuración de `diseño` (barra lateral, apilada, en línea) para que los complementos PRO puedan rotar los diseños del panel de filtros y el recuento de columnas. FilterEngine aplica clases modificadoras de diseño en `.sieve-app`.

= 0.9.1 =
* Extensión: filtro `sieve_facets` y contexto de página (`FacetContext`) para que los complementos PRO puedan mostrar u ocultar facetas por categoría, página de tienda o rol de cliente. Las solicitudes AJAX preservan el contexto a través de variables de consulta `sf_ctx_*`.

= 0.9.0 =
* Polaco: una interfaz de usuario de filtro actualizada y más atractiva. Grupos de facetas plegables, una fila de chips de "Filtros activos" con botones de eliminación más claros, un estado vacío amigable con una acción de "Borrar todos los filtros" con un solo clic, un control giratorio de carga accesible y un mensaje de error que se puede volver a intentar si falla una actualización.
* Diseño: propiedades personalizadas de CSS temáticas, tamaño fluido, modo oscuro automático (prefiere esquema de color) y transiciones elegantes que respetan el movimiento reducido. No hay cambios de diseño cuando se aplican filtros.
* Administrador: ayuda en línea sobre cada configuración, incluida una breve descripción de cómo ven cada tipo de faceta a los compradores.
* Accesibilidad: los grupos de facetas exponen su estado expandido/contraído, los recuentos de resultados se anuncian cortésmente, las regiones de paginación y filtro están etiquetadas y los botones de eliminación tienen nombres claros y accesibles.

= 0.8.2 =
* Cumplimiento: documentó el repositorio de fuentes públicas y los pasos de compilación de los activos compilados (directrices del complemento de WordPress.org).

= 0.8.1 =
* Internacionalización: las cadenas de interfaz JavaScript de administración y front-end ahora están incluidas en la plantilla de traducción, por lo que todo el complemento (no solo el lado PHP) se puede traducir por completo.

= 0.8.0 =
* Nuevo: configuración de apariencia. Elija un estilo preestablecido (Predeterminado, Mínimo, Con bordes, Suave, Sin estilo) y personalice el acento, el borde, el texto apagado y los colores de fondo desde el administrador, con una vista previa en vivo y una sugerencia de contraste. Se aplica tanto al filtro como a la búsqueda predictiva. Cero solicitudes adicionales, sin cambios de diseño, totalmente compatible con versiones anteriores.

= 0.7.0 =
* La búsqueda ahora se comporta como un filtro: escriba para limitar la cuadrícula en vivo con sugerencias predictivas, tolerantes a diacríticos y errores tipográficos, combinables con todas las facetas y seguras para URL y botones de retroceso. Los recuentos de facetas dependientes ahora también reflejan la búsqueda activa.

= 0.6.0 =
* La búsqueda predictiva ahora no distingue signos diacríticos y tolera errores tipográficos. Coincide con títulos de productos y SKU ignorando las diferencias diacríticas (por lo que "lozko" encuentra "łóżko"), tolera pequeños errores tipográficos y las categorías coincidentes se encuentran de la misma manera. Esta versión desencadena una reconstrucción única del índice de búsqueda.

= 0.5.0 =
* La búsqueda predictiva ahora va más allá de los títulos de productos: un pase de SKU parcial muestra un producto por su código incluso cuando falta el título, y las categorías de productos coincidentes aparecen como su propio grupo en el menú desplegable para que el comprador pueda ir directamente al archivo filtrado. Los resultados y las categorías se agrupan con títulos y la navegación con el teclado se mueve a través de ambos.

= 0.4.0 =
* Nuevos tipos de facetas: Autocompletar (un cuadro de búsqueda que filtra las opciones propias de una faceta a medida que escribe, para facetas con muchos valores) e índice A-Z (una barra alfabética que filtra las opciones por primera letra). Ambos filtran el lado del cliente sin solicitudes adicionales y se degradan a una lista de opciones simple sin JavaScript.

= 0.3.0 =
* Nuevo: búsqueda predictiva de productos. El código abreviado `[sieve_search]` y el bloque "Sieve Search" generan un cuadro de búsqueda accesible con un menú desplegable de escritura anticipada instantánea (miniaturas de productos, precios, SKU), navegación completa con el teclado y un enlace "ver todos los resultados". Basado en la búsqueda de productos de WooCommerce, cargado como un paquete liviano e independiente para que las páginas sin él se mantengan rápidas.

= 0.2.0 =
* Nuevos tipos de facetas: muestras de color e imagen (con color/imagen por término, además de una suposición automática de color a partir de nombres de colores comunes) y facetas de categorías jerárquicas (árbol) que muestran solo ramas que conducen a resultados.

= 0.1.2 =
* Administrador: filas más limpias del generador de facetas (controles alineados, botones agrupados para reordenar/eliminar con el primer/último estado deshabilitado, la fuente del campo se muestra como un título).

= 0.1.1 =
* Cumplimiento: agregó el propietario del complemento a la lista de Colaboradores e incluyó las fuentes legibles por humanos y los pasos de compilación para los activos compilados (directrices del complemento de WordPress.org).

= 0.1.0 =
* Lanzamiento inicial de MVP: índice prediseñado, filtrado AJAX con estado de URL, recuentos de facetas dependientes, casillas de verificación/radio/desplegable/rango/facetas de búsqueda, clasificación, chips de filtro activo, paginación, cajón de filtro móvil, generador de facetas React, código abreviado `[sieve]` y bloque "Filtro de tamiz".
