=== Sieve - Faceted Filter for WooCommerce ===
Contributors: motylanogha
Tags: woocommerce, filter, faceted search, product filter, ajax filter
Requires at least: 6.4
Tested up to: 7.0
Requires PHP: 8.1
Stable tag: 1.0.2
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Schnelle, barrierefreie facettierte Produktfilterung für WooCommerce: AJAX-Filter, eine mobile Schublade und Core Web Vitals von Grund auf.

== Description ==

Sieve bietet deinen Käufern eine schnelle, moderne Möglichkeit, Produkte zu finden. Sie kreuzen ein paar Kästchen an, ziehen eine Preisspanne, geben ein Stichwort ein, und das Raster aktualisiert sich sofort, ohne Neuladen der Seite. Es ist so gebaut, dass es sich mühelos anfühlt und bei großen Katalogen schnell bleibt – mit barrierefreien Widgets, einer mobilen Filterschublade und einem Rendering-Ansatz, der für Core Web Vitals entwickelt wurde: keine Layout-Verschiebung, wenn sich die Ergebnisse ändern.

Alles läuft gegen einen vorgefertigten Index, sodass die Filterung selbst bei Tausenden von Produkten schnell bleibt und die Anzahl neben jeder Option live aktualisiert wird, während die Käufer ihre Auswahl eingrenzen.

Filtern, das sich sofort anfühlt:

* AJAX-Filterung ohne Neuladen der Seite und teilbare URLs, die sich als Lesezeichen speichern lassen
* Live-abhängige Zählungen, die sich aktualisieren, während Filter angewendet werden
* Aktive-Filter-Chips, Zurücksetzen mit einem Klick, integrierte Sortierung und Paginierung

Jeder Facettentyp, den du brauchst:

* Kontrollkästchen, Optionsfelder und durchsuchbare Dropdowns
* Farb- und Bildmuster, hierarchische (Baum-)Kategorien
* Autovervollständigung (durchsuchbare Optionen) und ein A-Z-Index
* Bereichsregler, Stichwortsuche, Sortierung, Paginierung und Zurücksetzen

Filtere nach allem in deinem Katalog:

* Kategorien, Schlagwörter und Produktattribute
* Preis, Lagerbestand und im Angebot

Vorausschauende Produktsuche:

* Ein sofortiges Typeahead-Dropdown mit Miniaturbildern, Preisen, Treffern für SKU und Kategorie sowie vollständiger Tastaturnavigation

Schnell und barrierefrei von Grund auf:

* Vorgefertigter Index für schnelle gefilterte Abfragen bei großen Katalogen
* Mobile Filterschublade mit angehefteter Anwenden-Leiste
* Tastatur- und Screenreader-freundliche Widgets
* Core Web Vitals von Grund auf: keine Layout-Verschiebung, wenn sich die Ergebnisse aktualisieren

Einfach zu platzieren und zu konfigurieren:

* Gutenberg-Block „Sieve Filter“, ein Elementor-Widget „Sieve Filter“ und der Shortcode `[sieve]`
* Ein visueller Facetten-Builder im Adminbereich: Facetten hinzufügen, neu anordnen und ihren Typ ändern, das Layout festlegen und den Index neu aufbauen

= Sieve PRO =

Sieve PRO bietet erweiterte Steuerung und Integrationen für wachsende Shops:

* Sternebewertungs-Facette mit visuellen Sternen
* Bedingte Facettenregeln: Facetten nach Kategorie, Shop-Seite oder Kundenrolle anzeigen oder ausblenden
* A/B-Layout-Tests, um das Filter-Layout zu finden, das am besten konvertiert
* Performance-Dashboard: Indexgröße, Katalogabdeckung und Benchmarks zur Filtergeschwindigkeit
* Suchintegrationen: SearchWP und Algolia, mit nativem Fallback

Dokumentation: https://plogins.com/de/sieve/docs/

= You may also like these plugins =

Weitere kostenlose WooCommerce-Plugins von WPPoland:

* [Plogins Tiers](https://wordpress.org/plugins/plogins-tiers/) - Mengen- und Volumenpreisstufen mit einer serverseitig gerenderten Preistabelle.
* [Plogins Waitlist](https://wordpress.org/plugins/plogins-waitlist/) - Warteliste für wieder verfügbare Produkte, die Käufer per E-Mail benachrichtigt, sobald ein Produkt zurück ist.
* [Polski for WooCommerce](https://wordpress.org/plugins/polski/) - Compliance für den polnischen Markt: GPSR, Omnibus, DSGVO, Rechnungen und Shop-Module.

Durchsuche den vollständigen Katalog unter https://plogins.com/de/ .

== Installation ==

1. Installiere und aktiviere WooCommerce.
2. Installiere Sieve und aktiviere es.
3. Öffne das Sieve-Menü, baue den Index neu auf und passe bei Bedarf den Facettensatz an.
4. Platziere den Filter auf einer beliebigen Seite mit dem Shortcode `[sieve]` oder dem Block „Sieve Filter“.

== Frequently Asked Questions ==

= Documentation and links =

* <strong>Dokumentation</strong> - https://plogins.com/de/sieve/docs/
* <strong>Plugin-Seite</strong> - https://plogins.com/de/sieve/
* <strong>Quellcode</strong> - https://github.com/wppoland/sieve
* <strong>Fehlerberichte und Funktionswünsche</strong> - https://github.com/wppoland/sieve/issues
* <strong>Diskussionen und Fragen</strong> - https://github.com/wppoland/sieve/discussions


= Does it require WooCommerce? =
Ja. Sieve filtert WooCommerce-Produktarchive und jede Seite, auf der du den Filter platzierst.

= Does filtering reload the page? =
Nein. Die Filterung läuft über AJAX, wobei die URL synchron gehalten wird, sodass die Ergebnisse teilbar sind.

= What can shoppers filter by? =
Sieve kann WooCommerce-Produkte nach Kategorien, Schlagwörtern, Attributen, Preis, Lagerbestand, Angebotsstatus und einem Stichwort-Suchfeld filtern. Es unterstützt außerdem Bereichsregler, durchsuchbare Optionen, Farb-/Bildmuster, Aktive-Filter-Chips und Sortierung.

= Is it fast on large stores? =
Ja. Sieve baut einen Produktfilter-Index auf, sodass AJAX-Filteranfragen nicht für jede Kategorie-, Attribut- und Preisabfrage langsame Live-Joins ausführen müssen.

= How do I add the filter to a page? =
Verwende den Shortcode `[sieve]` oder den Block „Sieve Filter“. Beide rendern die Facetten, das Ergebnisraster, die Sortierung, die Aktive-Filter-Chips und die Paginierung zusammen.

= How do I add the predictive search box? =
Verwende den Shortcode `[sieve_search]` oder den Block „Sieve Search“. Während die Käufer tippen, zeigt ein Dropdown passende Produkte mit Miniaturbildern und Preisen; es ist vollständig per Tastatur bedienbar und fällt auf die Standard-Produktsuche zurück, wenn JavaScript nicht verfügbar ist.

= Is Sieve accessible? =
Ja. Die Filter-Oberfläche ist für die Bedienung per Tastatur und für Screenreader gebaut – mit beschrifteten Regionen, barrierefreien Bedienelementen, höflichen Ansagen der Ergebniszahl und Unterstützung für reduzierte Bewegung.

= Does Sieve work on mobile? =
Ja. Sieve enthält eine mobile Filterschublade mit angehefteter Anwenden-Leiste, sodass Käufer Produkte filtern können, ohne auf kleinen Bildschirmen mit einer langen Seitenleiste kämpfen zu müssen.

= Does this plugin work on WordPress Multisite? =

Ja. Dieses Plugin ist mit WordPress Multisite kompatibel. Aktiviere es netzwerkweit oder auf einzelnen Websites; jede Website behält ihre eigenen Einstellungen und Daten.

== Screenshots ==

1. Facettenfilterung auf einer Produktseite: Facetten für Kategorien, Preisspanne, Verfügbarkeit und Angebote mit Live-abhängigen Zählungen, Aktive-Filter-Chips und einem Ergebnisraster.
2. Mobile Filterschublade mit angehefteter Leiste „Ergebnisse anzeigen“.
3. Der Facetten-Builder: Facetten hinzufügen, neu anordnen und ihren Typ ändern, das Layout festlegen und den Index neu aufbauen.

== Development ==

Die vollständige, für Menschen lesbare Quelle der kompilierten Assets ist in diesem Plugin unter `resources/` enthalten, zusammen mit dem Build-Tooling (`package.json`, `scripts/build-wp.mjs`). Die kompilierten Dateien unter `build/` werden aus diesen Quellen generiert. So baust du sie neu auf:

1. `npm install`
2. `npm run build`

Dabei kommen Vite (Admin- und Frontend-Skripte) und @wordpress/scripts (Blöcke) zum Einsatz. Es gibt keine Verschleierung; jedes ausgelieferte Asset lässt sich aus den enthaltenen Quellen neu erzeugen. Das öffentliche Quell-Repository ist außerdem unter https://github.com/wppoland/sieve verfügbar.

== Translations ==

Sieve enthält polnische, deutsche und spanische Übersetzungen für die Plugin-Oberfläche. Die Textdomain ist `sieve`, sodass Sprachpakete von WordPress.org diese mitgelieferten Übersetzungen ebenfalls überschreiben oder erweitern können.

== Changelog ==

= 1.0.2 =
* Mitgelieferte polnische, deutsche und spanische Übersetzungen für die Plugin-Oberfläche hinzugefügt.

= 1.0.1 =
* Erste stabile Version.

= 0.9.7 =
* Doku: Ein Abschnitt „Das könnte dir auch gefallen“ wurde hinzugefügt, der die anderen kostenlosen WooCommerce-Plugins von WPPoland verlinkt. Keine funktionalen Änderungen.

= 0.9.6 =
* Neu: Elementor-Widgets für Produktsuche und Produktfilter (funktioniert mit Elementor 3.x und 4.0).

= 0.9.5 =
* Adminbereich: Der in jeder Facettenzeile angezeigte Index-Hilfetext wurde korrigiert; Hinweise für leeren Index und leere Facette, ein gruppierter Quellen-Picker, Fehlermeldungen beim Speichern/Neuindizieren und ein Ladefehler-Zustand hinzugefügt.
* Adminbereich: Inline-Hilfe zu den Stil-Presets im Darstellungs-Panel.

= 0.9.4 =
* Erweiterung: Filter `sieve_search_product_ids` an `SearchResolver`, damit PRO-Add-ons die In-Grid-Suchfacette und die vorausschauende Suche über SearchWP oder Algolia leiten können.

= 0.9.3 =
* Erweiterung: Filter `sieve_facet_body`, `sieve_facet_types` und `sieve_facet_catalog` sowie `FacetTypeRegistry` in der REST-Antwort des Admin-Katalogs, damit PRO-Add-ons erweiterte Facetten-Darstellungen registrieren können (z. B. Sternebewertung).

= 0.9.2 =
* Erweiterung: Filter `sieve_settings` und Einstellung `layout` (sidebar, stacked, inline), damit PRO-Add-ons Filter-Panel-Layouts und Spaltenanzahlen wechseln können. FilterEngine wendet Layout-Modifikator-Klassen auf `.sieve-app` an.

= 0.9.1 =
* Erweiterung: Filter `sieve_facets` und Seitenkontext (`FacetContext`), damit PRO-Add-ons Facetten nach Kategorie, Shop-Seite oder Kundenrolle ein- oder ausblenden können. AJAX-Anfragen bewahren den Kontext über die Abfragevariablen `sf_ctx_*`.

= 0.9.0 =
* Feinschliff: eine aufgefrischte, ansprechendere Filter-Oberfläche. Einklappbare Facettengruppen, eine Chip-Reihe „Aktive Filter“ mit klareren Entfernen-Buttons, ein freundlicher Leerzustand mit der Ein-Klick-Aktion „Alle Filter löschen“, ein barrierefreier Lade-Spinner und eine wiederholbare Fehlermeldung, falls ein Update fehlschlägt.
* Design: thembare CSS-Custom-Properties, fließende Größenanpassung, automatischer Dunkelmodus (prefers-color-scheme) und geschmackvolle Übergänge, die prefers-reduced-motion respektieren. Keine Layout-Verschiebung, wenn Filter angewendet werden.
* Adminbereich: Inline-Hilfe zu jeder Einstellung, samt einer kurzen Beschreibung, wie jeder Facettentyp für Käufer aussieht.
* Barrierefreiheit: Facettengruppen geben ihren auf-/zugeklappten Zustand bekannt, Ergebniszahlen werden höflich angesagt, die Paginierungs- und Filterregionen sind beschriftet, und Entfernen-Buttons haben klare, barrierefreie Namen.

= 0.8.2 =
* Compliance: das öffentliche Quell-Repository und die Build-Schritte für die kompilierten Assets dokumentiert (WordPress.org-Plugin-Richtlinien).

= 0.8.1 =
* Internationalisierung: Die JavaScript-Oberflächentexte von Adminbereich und Frontend sind jetzt in der Übersetzungsvorlage enthalten, sodass sich das gesamte Plugin (nicht nur die PHP-Seite) vollständig übersetzen lässt.

= 0.8.0 =
* Neu: Darstellungseinstellungen. Wähle ein Stil-Preset (Standard, Minimal, Mit Rahmen, Weich, Ohne Stil) und passe im Adminbereich die Akzent-, Rahmen-, gedämpfte-Text- und Hintergrundfarben an – mit Live-Vorschau und Kontrast-Hinweis. Gilt sowohl für den Filter als auch für die vorausschauende Suche. Null zusätzliche Anfragen, keine Layout-Verschiebung, vollständig abwärtskompatibel.

= 0.7.0 =
* Die Suche verhält sich jetzt wie ein Filter: Tippe, um das Live-Raster direkt einzugrenzen – mit vorausschauenden, diakritik- und tippfehlertoleranten Vorschlägen, kombinierbar mit jeder Facette und sicher für URL und Zurück-Button. Abhängige Facettenzahlen spiegeln jetzt auch die aktive Suche wider.

= 0.6.0 =
* Die vorausschauende Suche ist jetzt diakritik-unabhängig und tippfehlertolerant. Sie gleicht Produkttitel und SKUs ab und ignoriert dabei diakritische Unterschiede (so findet „lozko“ „łóżko“), toleriert kleine Tippfehler, und passende Kategorien werden auf dieselbe Weise gefunden. Diese Version löst eine einmalige Neuerstellung des Suchindex aus.

= 0.5.0 =
* Die vorausschauende Suche schaut jetzt über Produkttitel hinaus: Ein Teil-SKU-Durchlauf bringt ein Produkt über seinen Code hervor, selbst wenn der Titel nicht trifft, und passende Produktkategorien erscheinen als eigene Gruppe im Dropdown, sodass ein Käufer direkt zum gefilterten Archiv springen kann. Ergebnisse und Kategorien werden mit Überschriften gruppiert, und die Tastaturnavigation führt durch beide.

= 0.4.0 =
* Neue Facettentypen: Autovervollständigung (ein Suchfeld, das die eigenen Optionen einer Facette während der Eingabe filtert, für Facetten mit vielen Werten) und A-Z-Index (eine alphabetische Leiste, die Optionen nach Anfangsbuchstaben filtert). Beide filtern clientseitig ohne zusätzliche Anfrage und fallen ohne JavaScript auf eine einfache Optionsliste zurück.

= 0.3.0 =
* Neu: vorausschauende Produktsuche. Der Shortcode `[sieve_search]` und der Block „Sieve Search“ rendern ein barrierefreies Suchfeld mit sofortigem Typeahead-Dropdown (Produkt-Miniaturbilder, Preise, SKU), vollständiger Tastaturnavigation und einem Link „Alle Ergebnisse anzeigen“. Aufgebaut auf der WooCommerce-Produktsuche, als eigenständiges, leichtes Bundle geladen, sodass Seiten ohne es schnell bleiben.

= 0.2.0 =
* Neue Facettentypen: Farb- und Bildmuster (mit Farbe/Bild pro Begriff sowie einer automatischen Farbschätzung anhand gängiger Farbnamen) und hierarchische (Baum-)Kategorie-Facetten, die nur Zweige anzeigen, die zu Ergebnissen führen.

= 0.1.2 =
* Adminbereich: aufgeräumtere Zeilen im Facetten-Builder (ausgerichtete Bedienelemente, gruppierte Buttons zum Neuanordnen/Entfernen mit deaktiviertem Zustand für erstes/letztes Element, Feldquelle als Beschriftung angezeigt).

= 0.1.1 =
* Compliance: den Plugin-Eigentümer zur Contributors-Liste hinzugefügt und die für Menschen lesbaren Quellen sowie Build-Schritte für die kompilierten Assets beigefügt (WordPress.org-Plugin-Richtlinien).

= 0.1.0 =
* Erste MVP-Version: vorgefertigter Index, AJAX-Filterung mit URL-Status, abhängige Facettenzahlen, Facetten mit Kontrollkästchen / Radio / Dropdown / Bereich / Suche, Sortierung, Aktive-Filter-Chips, Paginierung, mobile Filterschublade, React-Facetten-Builder, Shortcode `[sieve]` und Block „Sieve Filter“.
