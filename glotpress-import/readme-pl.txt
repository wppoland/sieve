=== Sieve - Faceted Filter for WooCommerce ===
Contributors: motylanogha
Tags: woocommerce, filter, faceted search, product filter, ajax filter
Requires at least: 6.4
Tested up to: 7.0
Requires PHP: 8.1
Stable tag: 1.0.2
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Szybkie, dostępne filtrowanie fasetowe produktów dla WooCommerce: filtry AJAX, mobilna szuflada i Core Web Vitals już w projekcie.

== Description ==

Sieve daje kupującym szybki, nowoczesny sposób na znajdowanie produktów. Zaznaczają kilka pól, przeciągają zakres cen, wpisują słowo kluczowe, a siatka aktualizuje się natychmiast, bez ponownego ładowania strony. Powstał tak, aby działał bez wysiłku i pozostawał szybki nawet przy dużych katalogach — z dostępnymi widżetami, mobilną szufladą filtrów i sposobem renderowania zaprojektowanym pod Core Web Vitals: bez przeskoku układu przy zmianie wyników.

Wszystko działa na wstępnie zbudowanym indeksie, więc filtrowanie pozostaje szybkie nawet przy tysiącach produktów, a liczniki obok każdej opcji aktualizują się na żywo, gdy kupujący zawężają wybór.

Filtrowanie, które działa natychmiast:

* Filtrowanie AJAX bez ponownego ładowania strony oraz adresy URL, które można udostępniać i dodawać do zakładek
* Zależne liczniki na żywo, które aktualizują się w miarę stosowania filtrów
* Wbudowane etykiety aktywnych filtrów, resetowanie jednym kliknięciem, sortowanie i paginacja

Każdy typ fasety, jakiego potrzebujesz:

* Pola wyboru, przyciski radiowe i listy rozwijane z wyszukiwaniem
* Próbki kolorów i obrazów, hierarchiczne (drzewiaste) kategorie
* Autouzupełnianie (opcje z wyszukiwaniem) oraz indeks A-Z
* Suwaki zakresu, wyszukiwanie słów kluczowych, sortowanie, paginacja i resetowanie

Filtruj po dowolnym elemencie katalogu:

* Kategorie, tagi i atrybuty produktów
* Cena, stan magazynowy i status wyprzedaży

Predykcyjne wyszukiwanie produktów:

* Natychmiastowa lista podpowiedzi z miniaturami, cenami, dopasowaniami SKU i kategorii oraz pełną obsługą klawiatury

Szybkie i dostępne z założenia:

* Wstępnie zbudowany indeks do szybkich zapytań filtrujących w dużych katalogach
* Mobilna szuflada filtrów z przyklejonym paskiem „Zastosuj”
* Widżety przyjazne dla klawiatury i czytników ekranu
* Core Web Vitals już w projekcie: bez przeskoku układu przy aktualizacji wyników

Łatwe do umieszczenia i skonfigurowania:

* Blok „Sieve Filter” w Gutenbergu, widżet „Sieve Filter” w Elementorze oraz shortcode `[sieve]`
* Wizualny kreator faset w panelu administracyjnym: dodawaj, zmieniaj kolejność i zmieniaj typ faset, ustaw układ i przebuduj indeks

= Sieve PRO =

Sieve PRO dodaje zaawansowaną kontrolę i integracje dla rozwijających się sklepów:

* Faseta oceny gwiazdkowej z wizualnymi gwiazdkami
* Warunkowe reguły faset: pokazuj lub ukrywaj fasety według kategorii, strony sklepu lub roli klienta
* Testy A/B układu, aby znaleźć układ filtra, który najlepiej konwertuje
* Panel wydajności: rozmiar indeksu, pokrycie katalogu i testy szybkości filtrowania
* Integracje wyszukiwania: SearchWP i Algolia, z natywnym mechanizmem awaryjnym

Dokumentacja: https://plogins.com/pl/sieve/docs/

= You may also like these plugins =

Więcej darmowych wtyczek WooCommerce od WPPoland:

* [Plogins Tiers](https://wordpress.org/plugins/plogins-tiers/) - progi cenowe ilościowe i wolumenowe z tabelą cen renderowaną po stronie serwera.
* [Plogins Waitlist](https://wordpress.org/plugins/plogins-waitlist/) - lista oczekujących na powrót do magazynu, która wysyła e-mail do kupujących w chwili, gdy produkt znów jest dostępny.
* [Polski for WooCommerce](https://wordpress.org/plugins/polski/) - zgodność z rynkiem polskim: GPSR, Omnibus, RODO, faktury i moduły sklepowe.

Przejrzyj pełny katalog na https://plogins.com/pl/ .

== Installation ==

1. Zainstaluj i włącz WooCommerce.
2. Zainstaluj Sieve i włącz je.
3. Otwórz menu Sieve, przebuduj indeks i w razie potrzeby dostosuj zestaw faset.
4. Umieść filtr na dowolnej stronie za pomocą shortcode’u `[sieve]` lub bloku „Sieve Filter”.

== Frequently Asked Questions ==

= Documentation and links =

* <strong>Dokumentacja</strong> - https://plogins.com/pl/sieve/docs/
* <strong>Strona wtyczki</strong> - https://plogins.com/pl/sieve/
* <strong>Kod źródłowy</strong> - https://github.com/wppoland/sieve
* <strong>Zgłoszenia błędów i propozycje funkcji</strong> - https://github.com/wppoland/sieve/issues
* <strong>Dyskusje i pytania</strong> - https://github.com/wppoland/sieve/discussions


= Does it require WooCommerce? =
Tak. Sieve filtruje archiwa produktów WooCommerce oraz każdą stronę, na której umieścisz filtr.

= Does filtering reload the page? =
Nie. Filtrowanie odbywa się przez AJAX, a adres URL pozostaje zsynchronizowany, więc wyniki można udostępniać.

= What can shoppers filter by? =
Sieve może filtrować produkty WooCommerce według kategorii, tagów, atrybutów, ceny, stanu magazynowego, statusu wyprzedaży oraz pola wyszukiwania słów kluczowych. Obsługuje też suwaki zakresu, opcje z wyszukiwaniem, próbki kolorów/obrazów, etykiety aktywnych filtrów i sortowanie.

= Is it fast on large stores? =
Tak. Sieve buduje indeks filtra produktów, więc żądania filtrowania AJAX nie muszą wykonywać wolnych złączeń na żywo przy każdym zapytaniu o kategorię, atrybut i cenę.

= How do I add the filter to a page? =
Użyj shortcode’u `[sieve]` lub bloku „Sieve Filter”. Oba renderują razem fasety, siatkę wyników, sortowanie, etykiety aktywnych filtrów i paginację.

= How do I add the predictive search box? =
Użyj shortcode’u `[sieve_search]` lub bloku „Sieve Search”. Gdy kupujący pisze, lista rozwijana pokazuje pasujące produkty z miniaturami i cenami; jest w pełni dostępna z klawiatury i wraca do standardowego wyszukiwania produktów, gdy JavaScript jest niedostępny.

= Is Sieve accessible? =
Tak. Interfejs filtra jest zbudowany z myślą o obsłudze klawiatury i czytnikach ekranu — z oznaczonymi regionami, dostępnymi elementami sterującymi, uprzejmymi komunikatami o liczbie wyników i obsługą ograniczonego ruchu.

= Does Sieve work on mobile? =
Tak. Sieve zawiera mobilną szufladę filtrów z przyklejonym paskiem zastosowania, dzięki czemu kupujący mogą filtrować produkty bez walki z długim panelem bocznym na małych ekranach.

= Does this plugin work on WordPress Multisite? =

Tak. Ta wtyczka jest zgodna z WordPress Multisite. Włącz ją dla całej sieci lub w pojedynczych witrynach; każda witryna zachowuje własne ustawienia i dane.

== Screenshots ==

1. Filtrowanie fasetowe na stronie produktu: fasety kategorii, zakresu cen, dostępności i wyprzedaży z zależnymi licznikami na żywo, etykietami aktywnych filtrów i siatką wyników.
2. Mobilna szuflada filtrów z przyklejonym paskiem „Pokaż wyniki”.
3. Kreator faset: dodawaj, zmieniaj kolejność i zmieniaj typ faset, ustaw układ i przebuduj indeks.

== Development ==

Pełne, czytelne dla człowieka źródło skompilowanych zasobów jest dołączone do tej wtyczki w katalogu `resources/`, obok narzędzi kompilacji (`package.json`, `scripts/build-wp.mjs`). Skompilowane pliki w `build/` są generowane z tych źródeł. Aby je przebudować:

1. `npm install`
2. `npm run build`

Wykorzystuje Vite (skrypty panelu administracyjnego i front-endu) oraz @wordpress/scripts (bloki). Nie ma żadnej obfuskacji; każdy dostarczany zasób można ponownie wygenerować z dołączonych źródeł. Publiczne repozytorium źródeł jest również dostępne pod adresem https://github.com/wppoland/sieve.

== Translations ==

Sieve zawiera polskie, niemieckie i hiszpańskie tłumaczenia interfejsu wtyczki. Domena tekstowa to `sieve`, dzięki czemu paczki językowe z WordPress.org mogą również nadpisywać lub rozszerzać dołączone tłumaczenia.

== Changelog ==

= 1.0.2 =
* Dodano dołączone polskie, niemieckie i hiszpańskie tłumaczenia interfejsu wtyczki.

= 1.0.1 =
* Pierwsza stabilna wersja.

= 0.9.7 =
* Dokumentacja: dodano sekcję „Może Ci się też spodobać”, która linkuje do pozostałych darmowych wtyczek WooCommerce od WPPoland. Bez zmian funkcjonalnych.

= 0.9.6 =
* Nowość: widżety Elementora do wyszukiwania produktów i filtrowania produktów (działają w Elementorze 3.x i 4.0).

= 0.9.5 =
* Panel administracyjny: naprawiono tekst pomocy indeksu wyświetlany w każdym wierszu fasety; dodano powiadomienia o pustym indeksie i pustej fasecie, zgrupowany selektor źródła, komunikaty o błędach zapisu/ponownego indeksowania oraz stan niepowodzenia ładowania.
* Panel administracyjny: wbudowana pomoc dotycząca gotowych stylów w panelu Wygląd.

= 0.9.4 =
* Rozszerzenie: filtr `sieve_search_product_ids` w `SearchResolver`, dzięki czemu dodatki PRO mogą kierować fasetę wyszukiwania w siatce oraz wyszukiwanie predykcyjne przez SearchWP lub Algolia.

= 0.9.3 =
* Rozszerzenie: filtry `sieve_facet_body`, `sieve_facet_types` i `sieve_facet_catalog` oraz `FacetTypeRegistry` w odpowiedzi REST katalogu w panelu administracyjnym, dzięki czemu dodatki PRO mogą rejestrować zaawansowane sposoby prezentacji faset (np. ocenę gwiazdkową).

= 0.9.2 =
* Rozszerzenie: filtr `sieve_settings` i ustawienie `layout` (sidebar, stacked, inline), dzięki czemu dodatki PRO mogą zmieniać układy panelu filtra i liczbę kolumn. FilterEngine stosuje klasy modyfikujące układ na `.sieve-app`.

= 0.9.1 =
* Rozszerzenie: filtr `sieve_facets` i kontekst strony (`FacetContext`), dzięki czemu dodatki PRO mogą pokazywać lub ukrywać fasety według kategorii, strony sklepu lub roli klienta. Żądania AJAX zachowują kontekst przez zmienne zapytania `sf_ctx_*`.

= 0.9.0 =
* Dopracowanie: odświeżony, atrakcyjniejszy interfejs filtra. Zwijane grupy faset, rząd etykiet „Aktywne filtry” z czytelniejszymi przyciskami usuwania, przyjazny stan pusty z akcją „Wyczyść wszystkie filtry” jednym kliknięciem, dostępny wskaźnik ładowania oraz komunikat o błędzie z możliwością ponowienia, gdy aktualizacja się nie powiedzie.
* Wygląd: konfigurowalne właściwości niestandardowe CSS, płynne skalowanie, automatyczny tryb ciemny (prefers-color-scheme) oraz gustowne przejścia respektujące prefers-reduced-motion. Bez przeskoku układu przy stosowaniu filtrów.
* Panel administracyjny: wbudowana pomoc przy każdym ustawieniu, w tym krótki opis tego, jak każdy typ fasety wygląda dla kupujących.
* Dostępność: grupy faset udostępniają swój stan rozwinięcia/zwinięcia, liczba wyników jest ogłaszana w sposób uprzejmy, obszary paginacji i filtra są oznaczone etykietami, a przyciski usuwania mają czytelne, dostępne nazwy.

= 0.8.2 =
* Zgodność: udokumentowano publiczne repozytorium źródeł i kroki kompilacji skompilowanych zasobów (wytyczne dla wtyczek WordPress.org).

= 0.8.1 =
* Umiędzynarodowienie: ciągi interfejsu w JavaScript panelu administracyjnego i front-endu są teraz zawarte w szablonie tłumaczenia, więc całą wtyczkę (nie tylko stronę PHP) można w pełni przetłumaczyć.

= 0.8.0 =
* Nowość: ustawienia wyglądu. Wybierz gotowy styl (Domyślny, Minimalny, Z obramowaniem, Miękki, Bez stylu) i dostosuj z poziomu panelu kolory akcentu, obramowania, przygaszonego tekstu i tła, z podglądem na żywo i wskazówką dotyczącą kontrastu. Dotyczy zarówno filtra, jak i wyszukiwania predykcyjnego. Zero dodatkowych żądań, bez przeskoku układu, w pełni zgodne wstecz.

= 0.7.0 =
* Wyszukiwanie działa teraz jak filtr: pisz, aby zawęzić siatkę na żywo w miejscu, dzięki podpowiedziom predykcyjnym, tolerującym znaki diakrytyczne i literówki, które można łączyć z każdą fasetą i które są bezpieczne dla adresu URL oraz przycisku Wstecz. Zależne liczniki faset odzwierciedlają teraz również aktywne wyszukiwanie.

= 0.6.0 =
* Wyszukiwanie predykcyjne jest teraz niewrażliwe na znaki diakrytyczne i toleruje literówki. Dopasowuje tytuły produktów i numery SKU, ignorując różnice diakrytyczne (więc „lozko” znajduje „łóżko”), toleruje drobne literówki, a pasujące kategorie odnajdywane są w ten sam sposób. To wydanie uruchamia jednorazową przebudowę indeksu wyszukiwania.

= 0.5.0 =
* Wyszukiwanie predykcyjne sięga teraz poza tytuły produktów: częściowe dopasowanie SKU pokazuje produkt po jego kodzie, nawet gdy tytuł nie pasuje, a pasujące kategorie produktów pojawiają się jako osobna grupa na liście rozwijanej, dzięki czemu kupujący może przejść od razu do przefiltrowanego archiwum. Wyniki i kategorie są pogrupowane z nagłówkami, a nawigacja z klawiatury przechodzi przez oba.

= 0.4.0 =
* Nowe typy faset: Autouzupełnianie (pole wyszukiwania, które filtruje własne opcje fasety podczas pisania, dla faset z wieloma wartościami) oraz indeks A-Z (alfabetyczny pasek filtrujący opcje według pierwszej litery). Oba filtrują po stronie klienta bez dodatkowego żądania i sprowadzają się do zwykłej listy opcji bez JavaScript.

= 0.3.0 =
* Nowość: predykcyjne wyszukiwanie produktów. Shortcode `[sieve_search]` i blok „Sieve Search” renderują dostępne pole wyszukiwania z natychmiastową listą podpowiedzi (miniatury produktów, ceny, SKU), pełną obsługą klawiatury i odnośnikiem „zobacz wszystkie wyniki”. Zbudowane na wyszukiwarce produktów WooCommerce, ładowane jako samodzielny, lekki pakiet, dzięki czemu strony bez niego pozostają szybkie.

= 0.2.0 =
* Nowe typy faset: próbki kolorów i obrazów (z kolorem/obrazem dla każdego terminu oraz automatycznym odgadywaniem koloru na podstawie popularnych nazw kolorów) i hierarchiczne (drzewiaste) fasety kategorii, które pokazują tylko gałęzie prowadzące do wyników.

= 0.1.2 =
* Panel administracyjny: czytelniejsze wiersze kreatora faset (wyrównane elementy sterujące, zgrupowane przyciski zmiany kolejności/usuwania ze stanami wyłączenia dla pierwszego/ostatniego, źródło pola pokazane jako podpis).

= 0.1.1 =
* Zgodność: dodano właściciela wtyczki do listy współtwórców i dołączono czytelne dla człowieka źródła oraz kroki kompilacji skompilowanych zasobów (wytyczne dla wtyczek WordPress.org).

= 0.1.0 =
* Pierwsze wydanie MVP: wstępnie zbudowany indeks, filtrowanie AJAX ze stanem w adresie URL, zależne liczniki faset, fasety pól wyboru / radiowe / listy rozwijanej / zakresu / wyszukiwania, sortowanie, etykiety aktywnych filtrów, paginacja, mobilna szuflada filtrów, kreator faset w React, shortcode `[sieve]` i blok „Sieve Filter”.
