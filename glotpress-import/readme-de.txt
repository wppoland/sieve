=== Sieve - Faceted Filter for WooCommerce ===
Contributors: motylanogha
Tags: woocommerce, filter, faceted search, product filter, ajax filter
Requires at least: 6.4
Tested up to: 7.0
Requires PHP: 8.1
Stable tag: 1.0.1
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Schnelle, zugängliche, facettenreiche Produktfilterung für WooCommerce: AJAX-Filter, eine mobile Schublade und Core Web Vitals by Design.

== Description ==

Sieve bietet deinen Käufern eine schnelle und moderne Möglichkeit, Produkte zu finden. Sie kreuzen ein paar Kästchen an, ziehen eine Preisspanne, geben ein Schlüsselwort ein und das Raster wird sofort aktualisiert, ohne dass die Seite neu geladen werden muss. Es ist so konzipiert, dass es sich mühelos anfühlt und bei großen Katalogen schnell bleibt, mit zugänglichen Widgets, einer mobilen Filterschublade und einem für Core Web Vitals entwickelten Rendering-Ansatz: keine Layoutverschiebung, wenn sich Ergebnisse ändern.

Alles läuft mit einem vorgefertigten Index ab, sodass die Filterung auch bei Tausenden von Produkten schnell bleibt und die Anzahl neben jeder Option live aktualisiert wird, wenn die Käufer ihre Auswahl eingrenzen.

Filtern, das sich sofort anfühlt:

* AJAX-Filterung ohne Neuladen der Seite und gemeinsam nutzbare, mit Lesezeichen versehene URLs
* Live-abhängige Zählungen, die aktualisiert werden, wenn Filter angewendet werden
* Aktive Filterchips, Zurücksetzen mit einem Klick, integrierte Sortierung und Paginierung

Jeder Facettentyp, den du benötigst:

* Kontrollkästchen, Optionsfelder und durchsuchbare Dropdown-Listen
* Farb- und Bildfelder, hierarchische (Baum-)Kategorien
* Autovervollständigung (durchsuchbare Optionen) und ein A-Z-Index
* Bereichsschieberegler, Stichwortsuche, Sortierung, Paginierung und Zurücksetzen

Filtere nach allem in deinem Katalog:

* Kategorien, Tags und Produktattribute
* Preis, Lagerbestand und im Angebot

Vorausschauende Produktsuche:

* Ein sofortiges Typeahead-Dropdown-Menü mit Miniaturansichten, Preisen, SKU- und Kategorieübereinstimmungen und vollständiger Tastaturnavigation

Vom Design her schnell und zugänglich:

* Vorgefertigter Index für schnelle gefilterte Abfragen in großen Katalogen
* Mobile Filterschublade mit klebriger Auftragsleiste
* Tastatur- und Screenreader-freundliche Widgets
* Core Web Vitals durch Design: Keine Layoutverschiebung, wenn die Ergebnisse aktualisiert werden

Einfach zu platzieren und zu konfigurieren:

* Gutenberg-Block „Sieve Filter“ und der Shortcode „[sieve]“.
* Ein visueller Facetten-Builder im Admin: Facetten hinzufügen, neu anordnen und eingeben, das Layout festlegen und den Index neu erstellen

= Sieve PRO =

Sieve PRO bietet erweiterte Steuerung und Integrationen für wachsende Geschäfte:

* Sternebewertungsseite mit visuellen Sternen
* Bedingte Facettenregeln: Facetten nach Kategorie, Shop-Seite oder Kundenrolle anzeigen oder ausblenden
* A/B-Layout-Tests, um das Filterlayout zu finden, das am besten konvertiert
* Leistungs-Dashboard: Indexgröße, Katalogabdeckung und Filtergeschwindigkeits-Benchmarks
* Suchintegrationen: SearchWP und Algolia, mit nativem Fallback

Dokumentation: https://plogins.com/de/sieve/docs/

= You may also like these plugins =

Weitere kostenlose WooCommerce-Plugins von WPPoland:

* [Plogins Tiers](https://wordpress.org/plugins/plogins-tiers/) – Mengen- und Volumenpreisstufen mit einer vom Server gerenderten Preistabelle.
* [Plogins Waitlist](https://wordpress.org/plugins/plogins-waitlist/) – Warteliste für wieder verfügbare Lagerbestände, die Käufer per E-Mail benachrichtigt, sobald ein Produkt zurückkommt.
* [Polski for WooCommerce](https://wordpress.org/plugins/polski/) – Einhaltung des polnischen Marktes: GPSR, Omnibus, DSGVO, Rechnungen und Storefront-Module.

Durchsuche den vollständigen Katalog unter https://plogins.com/de/ .

== Installation ==

1. Installieren und aktiviere WooCommerce.
2. Installiere Sieve und aktiviere es.
3. Öffne das Sieve-Menü, erstelle den Index neu und passe den Facettensatz bei Bedarf an.
4. Platziere den Filter auf einer beliebigen Seite mit dem Shortcode „[sieve]“ oder dem Block „Sieve Filter“.

== Frequently Asked Questions ==

= Documentation and links =

* <strong>Dokumentation</strong> - https://plogins.com/de/sieve/docs/
* <strong>Plugin-Seite</strong> - https://plogins.com/de/sieve/
* <strong>Quellcode</strong> – https://github.com/wppoland/sieve
* <strong>Fehlerberichte und Funktionsanfragen</strong> – https://github.com/wppoland/sieve/issues
* <strong>Diskussionen und Fragen</strong> – https://github.com/wppoland/sieve/discussions


= Does it require WooCommerce? =
Ja. Siebfilter WooCommerce-Produktarchive und jede Seite, auf der du den Filter platzieren.

= Does filtering reload the page? =
Nein. Die Filterung erfolgt über AJAX, wobei die URL synchronisiert bleibt, sodass die Ergebnisse geteilt werden können.

= What can shoppers filter by? =
Sieve kann WooCommerce-Produkte nach Kategorien, Tags, Attributen, Preis, Lagerstatus, Verkaufsstatus und einem Stichwortsuchfeld filtern. Es unterstützt außerdem Bereichsschieberegler, durchsuchbare Optionen, Farb-/Bildmuster, aktive Filterchips und Sortierung.

= Is it fast on large stores? =
Ja. Sieve erstellt einen Produktfilterindex, sodass AJAX-Filteranfragen nicht für jede Kategorie-, Attribut- und Preisabfrage langsame Live-Joins ausführen müssen.

= How do I add the filter to a page? =
Verwende den Shortcode „[sieve]“ oder den Block „Sieve Filter“. Beide rendern die Facetten, das Ergebnisraster, die Sortierung, die aktiven Filterchips und die Paginierung zusammen.

= How do I add the predictive search box? =
Verwende den Shortcode „[sieve_search]“ oder den Block „Sieve Search“. Während Käufer tippen, werden in einem Dropdown-Menü passende Produkte mit Miniaturansichten und Preisen angezeigt. Es ist vollständig über die Tastatur zugänglich und greift auf die Standardproduktsuche zurück, wenn JavaScript nicht verfügbar ist.

= Is Sieve accessible? =
Ja. Die Filter-Benutzeroberfläche ist für die Tastaturverwendung und Bildschirmleseprogramme konzipiert und verfügt über beschriftete Bereiche, zugängliche Steuerelemente, höfliche Ergebniszählansagen und Unterstützung für reduzierte Bewegungen.

= Does Sieve work on mobile? =
Ja. Sieve verfügt über eine mobile Filterschublade mit Klebeleiste, sodass Käufer Produkte filtern können, ohne sich mit einer langen Seitenleiste auf kleinen Bildschirmen herumschlagen zu müssen.

= Does this plugin work on WordPress Multisite? =

Ja. Dieses Plugin ist mit WordPress Multisite kompatibel. Aktiviere es im Netzwerk oder auf einzelnen Websites. Jede Site behält ihre eigenen Einstellungen und Daten.

== Screenshots ==

1. Facettenfilterung auf einer Produktseite: Kategorien, Preisspanne, Verfügbarkeit und Sonderangebote mit Live-abhängigen Zählungen, aktiven Filterchips und einem Ergebnisraster.
2. Mobile Filterschublade mit klebriger Leiste „Ergebnisse anzeigen“.
3. Der Facetten-Builder: Facetten hinzufügen, neu anordnen und eingeben, das Layout festlegen und den Index neu erstellen.

== Development ==

Die vollständige, für Menschen lesbare Quelle für die kompilierten Assets ist in diesem Plugin unter „resources/“ enthalten, zusammen mit den Build-Tools („package.json“, „scripts/build-wp.mjs“). Die kompilierten Dateien unter „build/“ werden aus diesen Quellen generiert. Um sie wieder aufzubauen:

1. „npm install“.
2. „npm run build“.

Hierzu werden Vite (Admin- und Frontend-Skripte) und @wordpress/scripts (Blöcke) verwendet. Es gibt keine Verschleierung; Jedes versendete Asset kann aus den enthaltenen Quellen neu generiert werden. Das öffentliche Quell-Repository ist auch unter https://github.com/wppoland/sieve verfügbar.

== Changelog ==

= 1.0.1 =
* Erste stabile Version.

= 0.9.7 =
* Dokumente: Es wurde ein Abschnitt „Das könnte dir auch gefallen“ hinzugefügt, der die anderen kostenlosen WPPoland WooCommerce-Plugins verlinkt. Keine funktionalen Änderungen.

= 0.9.6 =
* Neu: Elementor-Widgets für Produktsuche und Produktfilter (funktioniert auf Elementor 3.x und 4.0).

= 0.9.5 =
* Admin: Index-Hilfetext korrigiert, der in jeder Facettenzeile angezeigt wird; Füge Hinweise zu leeren Indizes und leeren Facetten, eine gruppierte Quellenauswahl, Fehlermeldungen zum Speichern/Neuindizieren und einen Ladefehlerstatus hinzu.
* Admin: Stilvoreingestellte Inline-Hilfe im Bedienfeld „Darstellung“.

= 0.9.4 =
* Erweiterung: „sieve_search_product_ids“-Filter auf „SearchResolver“, damit PRO-Add-ons die In-Grid-Suchfacette und die prädiktive Suche über SearchWP oder Algolia weiterleiten können.

= 0.9.3 =
* Erweiterung: Filter „sieve_facet_body“, „sieve_facet_types“ und „sieve_facet_catalog“ sowie „FacetTypeRegistry“ in der REST-Antwort des Admin-Katalogs, damit PRO-Add-Ons erweiterte Facettenpräsentationen (z. B. Sternebewertung) registrieren können.

= 0.9.2 =
* Erweiterung: „sieve_settings“-Filter und „Layout“-Einstellung (Seitenleiste, gestapelt, inline), damit PRO-Add-Ons Filterpanel-Layouts und Spaltenanzahlen drehen können. FilterEngine wendet Layoutmodifikatorklassen auf „.sieve-app“ an.

= 0.9.1 =
* Erweiterung: „sieve_facets“-Filter und Seitenkontext („FacetContext“), damit PRO-Add-Ons Facetten nach Kategorie, Shop-Seite oder Kundenrolle ein- oder ausblenden können. AJAX-Anfragen behalten den Kontext über „sf_ctx_*“-Abfragevariablen bei.

= 0.9.0 =
* Polnisch: eine aktualisierte, attraktivere Filter-Benutzeroberfläche. Reduzierbare Facettengruppen, eine Chipreihe „Aktive Filter“ mit übersichtlicheren Schaltflächen zum Entfernen, ein benutzerfreundlicher leerer Status mit der Ein-Klick-Aktion „Alle Filter löschen“, ein zugänglicher Lade-Spinner und eine wiederholbare Fehlermeldung, wenn ein Update fehlschlägt.
* Design: thematisch anpassbare benutzerdefinierte CSS-Eigenschaften, flüssige Größenanpassung, automatischer Dunkelmodus (Prefers-Color-Schema) und geschmackvolle Übergänge, die Prefers-Reduced-Motion berücksichtigen. Keine Layoutverschiebung, wenn Filter angewendet werden.
* Admin: Inline-Hilfe zu jeder Einstellung, einschließlich einer kurzen Beschreibung, wie jeder Facettentyp für Käufer aussieht.
* Barrierefreiheit: Facettengruppen zeigen ihren erweiterten/reduzierten Status an, die Ergebniszählung wird höflich angekündigt, die Paginierungs- und Filterbereiche sind beschriftet und die Schaltflächen zum Entfernen haben klare, barrierefreie Namen.

= 0.8.2 =
* Compliance: Dokumentiert das öffentliche Quell-Repository und die Erstellungsschritte für die kompilierten Assets (WordPress.org-Plugin-Richtlinien).

= 0.8.1 =
* Internationalisierung: Die Admin- und Front-End-JavaScript-Schnittstellenzeichenfolgen sind jetzt in der Übersetzungsvorlage enthalten, sodass das gesamte Plugin (nicht nur die PHP-Seite) vollständig übersetzt werden kann.

= 0.8.0 =
* Neu: Darstellungseinstellungen. Wähle eine Stilvoreinstellung (Standard, Minimal, Mit Rand, Weich, Ohne Stil) und passe die Akzent-, Rahmen-, gedämpften Text- und Hintergrundfarben im Administratorbereich mit einer Live-Vorschau und einem Kontrasthinweis an. Gilt sowohl für den Filter als auch für die prädiktive Suche. Keine zusätzlichen Anfragen, keine Layoutverschiebung, vollständig abwärtskompatibel.

= 0.7.0 =
* Die Suche verhält sich jetzt wie ein Filter: Gib ein, um das Live-Raster mit prädiktiven, diakritischen und tippfehlertoleranten Vorschlägen einzugrenzen, mit jeder Facette kombinierbar und URL- und Zurück-Button-sicher. Abhängige Facettenzahlen spiegeln jetzt auch die aktive Suche wider.

= 0.6.0 =
* Die prädiktive Suche ist jetzt unabhängig von diakritischen Zeichen und tolerant gegenüber Tippfehlern. Es gleicht Produkttitel und SKUs ab und ignoriert dabei diakritische Unterschiede (also findet „lozko“ „łóżko“), toleriert kleine Tippfehler und passende Kategorien werden auf die gleiche Weise gefunden. Diese Version löst eine einmalige Neuerstellung des Suchindex aus.

= 0.5.0 =
* Die vorausschauende Suche geht jetzt über Produkttitel hinaus: Bei einem teilweisen SKU-Durchlauf wird ein Produkt anhand seines Codes angezeigt, selbst wenn der Titel fehlt, und passende Produktkategorien werden als eigene Gruppe im Dropdown-Menü angezeigt, sodass ein Käufer direkt zum gefilterten Archiv springen kann. Ergebnisse und Kategorien werden mit Überschriften gruppiert und die Tastaturnavigation bewegt sich durch beide.

= 0.4.0 =
* Neue Facettentypen: Autovervollständigung (ein Suchfeld, das die eigenen Optionen einer Facette während der Eingabe filtert, für Facetten mit vielen Werten) und A-Z-Index (eine alphabetische Leiste, die Optionen nach Anfangsbuchstaben filtert). Beide filtern clientseitig ohne zusätzliche Anforderung und werden zu einer einfachen Optionsliste ohne JavaScript.

= 0.3.0 =
* Neu: vorausschauende Produktsuche. Der Shortcode „[sieve_search]“ und der Block „Sieve Search“ stellen ein zugängliches Suchfeld mit einem sofortigen Typeahead-Dropdown (Produktminiaturansichten, Preise, SKU), vollständiger Tastaturnavigation und einem Link „Alle Ergebnisse anzeigen“ dar. Basierend auf der WooCommerce-Produktsuche, geladen als eigenständiges, leichtes Paket, sodass Seiten ohne diese Funktion schnell bleiben.

= 0.2.0 =
* Neue Facettentypen: Farb- und Bildfelder (mit Farbe/Bild pro Begriff sowie einer automatischen Farbschätzung anhand allgemeiner Farbnamen) und hierarchische (Baum-)Kategoriefacetten, die nur Zweige anzeigen, die zu Ergebnissen führen.

= 0.1.2 =
* Admin: Sauberere Facetten-Builder-Zeilen (ausgerichtete Steuerelemente, gruppierte Schaltflächen zum Neuanordnen/Entfernen mit erstem/letztem deaktiviertem Status, Feldquelle wird als Beschriftung angezeigt).

= 0.1.1 =
* Compliance: Der Plugin-Eigentümer wurde zur Liste der Mitwirkenden hinzugefügt und die für Menschen lesbaren Quellen und Erstellungsschritte für die kompilierten Assets hinzugefügt (WordPress.org-Plugin-Richtlinien).

= 0.1.0 =
* Erste MVP-Version: vorgefertigter Index, AJAX-Filterung mit URL-Status, Anzahl abhängiger Facetten, Kontrollkästchen/Radio/Dropdown/Bereich/Suchfacetten, Sortierung, Aktivfilter-Chips, Paginierung, mobile Filterschublade, React-Facetten-Builder, „[sieve]“-Shortcode und „Sieve Filter“-Block.
