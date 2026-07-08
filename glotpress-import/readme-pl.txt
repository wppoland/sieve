=== Sieve - Faceted Filter for WooCommerce ===
Contributors: motylanogha
Tags: woocommerce, filter, faceted search, product filter, ajax filter
Requires at least: 6.4
Tested up to: 7.0
Requires PHP: 8.1
Stable tag: 1.0.1
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Szybkie, dostępne, fasetowe filtrowanie produktów dla WooCommerce: filtry AJAX, mobilna szuflada i z założenia Core Web Vitals.

== Description ==

Sieve zapewnia kupującym szybki i nowoczesny sposób wyszukiwania produktów. Zaznaczają kilka pól, przeciągają zakres cen, wpisują słowo kluczowe, a siatka aktualizuje się natychmiast, bez konieczności ponownego ładowania strony. Został zbudowany tak, aby nie wymagał wysiłku i umożliwiał szybkie przeglądanie dużych katalogów, z dostępnymi widżetami, mobilną szufladą filtrów i podejściem do renderowania zaprojektowanym dla Core Web Vitals: brak zmiany układu w przypadku zmiany wyników.

Wszystko działa według wstępnie utworzonego indeksu, więc filtrowanie jest szybkie nawet w przypadku tysięcy produktów, a liczniki obok każdej opcji są aktualizowane na bieżąco w miarę zmniejszania się liczby klientów.

Filtrowanie, które jest natychmiastowe:

* Filtrowanie AJAX bez ponownego ładowania strony i udostępniane adresy URL z możliwością tworzenia zakładek
* Liczniki zależne na żywo, które aktualizują się po zastosowaniu filtrów
* Wbudowane chipy z aktywnym filtrem, resetowanie jednym kliknięciem, sortowanie i paginacja

Każdy typ aspektu, którego potrzebujesz:

* Pola wyboru, przyciski opcji i listy rozwijane z możliwością przeszukiwania
* Próbki kolorów i obrazów, kategorie hierarchiczne (drzewo).
* Autouzupełnianie (opcje z możliwością wyszukiwania) i indeks A-Z
* Suwaki zakresu, wyszukiwanie słów kluczowych, sortowanie, paginacja i resetowanie

Filtruj według dowolnego elementu w swoim katalogu:

* Kategorie, tagi i atrybuty produktów
* Cena, stan magazynowy i wyprzedaż

Przewidywanie wyszukiwania produktów:

* Natychmiastowe menu rozwijane z miniaturami, cenami, dopasowaniami SKU i kategorii oraz pełną nawigacją za pomocą klawiatury

Szybki i dostępny zgodnie z projektem:

* Wstępnie zbudowany indeks do szybkiego filtrowania zapytań w dużych katalogach
* Mobilna szuflada filtrów z przyklejonym paskiem Apply
* Widżety przyjazne dla klawiatury i czytnika ekranu
* Podstawowe wskaźniki internetowe są zgodne z projektem: brak zmiany układu podczas aktualizacji wyników

Łatwe do umieszczenia i skonfigurowania:

* Blok Gutenberga „Filtr sitowy” i krótki kod `[sieve]`
* Wizualny kreator aspektów w panelu administracyjnym: dodawaj, zmieniaj kolejność i wpisz aspekty, ustawiaj układ i odbudowuj indeks

= Sieve PRO =

Sieve PRO dodaje zaawansowaną kontrolę i integracje dla rozwijających się sklepów:

* Aspekt gwiazdek z wizualnymi gwiazdkami
* Warunkowe zasady aspektów: pokaż lub ukryj aspekty według kategorii, strony sklepu lub roli klienta
* Testowanie układu A/B w celu znalezienia układu filtra, który zapewnia najlepszą konwersję
* Panel wydajności: wielkość indeksu, zasięg katalogu i testy porównawcze szybkości filtra
* Integracja wyszukiwania: SearchWP i Algolia, z natywną funkcją zastępczą

Dokumentacja: https://plogins.com/pl/sieve/docs/

= You may also like these plugins =

Więcej darmowych wtyczek WooCommerce od WPPoland:

* [Plogins Tiers](https://wordpress.org/plugins/plogins-tiers/) - poziomy cen ilościowych i wolumenowych z tabelą cen renderowaną przez serwer.
* [Plogins Waitlist](https://wordpress.org/plugins/plogins-waitlist/) - lista oczekujących na dostawę, która wysyła e-mail do kupujących w momencie zwrotu produktu.
* [Polski for WooCommerce](https://wordpress.org/plugins/polski/) - Zgodność z rynkiem polskim: GPSR, Omnibus, RODO, faktury i moduły sklepowe.

Pełny katalog znajdziesz na https://plogins.com/pl/.

== Installation ==

1. Zainstaluj i aktywuj WooCommerce.
2. Zainstaluj Sive i aktywuj je.
3. Otwórz menu Sito, przebuduj indeks i w razie potrzeby dostosuj zestaw fasetek.
4. Umieść filtr na dowolnej stronie z krótkim kodem `[sieve]` lub blokiem „Sieve Filter”.

== Frequently Asked Questions ==

= Documentation and links =

* <strong>Dokumentacja</strong> - https://plogins.com/pl/sieve/docs/
* <strong>Strona wtyczki</strong> - https://plogins.com/pl/sieve/
* <strong>Kod źródłowy</strong> - https://github.com/wppoland/sieve
* <strong>Raporty o błędach i prośby o nowe funkcje</strong> - https://github.com/wppoland/sieve/issues
* <strong>Dyskusje i pytania</strong> - https://github.com/wppoland/sieve/discussions


= Does it require WooCommerce? =
Tak. Sito filtruje archiwa produktów WooCommerce i każdą stronę, na której umieścisz filtr.

= Does filtering reload the page? =
Nie. Filtrowanie odbywa się za pośrednictwem technologii AJAX, a adresy URL są zsynchronizowane, co umożliwia udostępnianie wyników.

= What can shoppers filter by? =
Sieve może filtrować produkty WooCommerce według kategorii, tagów, atrybutów, ceny, stanu magazynowego, stanu sprzedaży i pola wyszukiwania słów kluczowych. Obsługuje także suwaki zakresu, opcje z możliwością wyszukiwania, próbki kolorów/obrazów, aktywne filtry i sortowanie.

= Is it fast on large stores? =
Tak. Sieve tworzy indeks filtrów produktów, więc żądania filtrów AJAX nie muszą uruchamiać powolnych łączeń na żywo dla każdego zapytania dotyczącego kategorii, atrybutu i ceny.

= How do I add the filter to a page? =
Użyj krótkiego kodu `[sieve]` lub bloku „Sieve Filter”. Obydwa renderują razem aspekty, siatkę wyników, sortowanie, aktywne filtry i paginację.

= How do I add the predictive search box? =
Użyj krótkiego kodu `[sieve_search]` lub bloku „Sieve Search”. Gdy kupujący będą pisać, menu wyświetli pasujące produkty z miniaturami i cenami; jest w pełni dostępny za pomocą klawiatury i powraca do standardowego wyszukiwania produktów, gdy JavaScript jest niedostępny.

= Is Sieve accessible? =
Tak. Interfejs użytkownika filtra jest przystosowany do obsługi klawiatury i czytników ekranu, zawiera oznaczone regiony, dostępne elementy sterujące, grzeczne powiadomienia o liczniku wyników i obsługę ograniczonego ruchu.

= Does Sieve work on mobile? =
Tak. Sito zawiera mobilną szufladę na filtry z przylepnym paskiem do nakładania, dzięki czemu kupujący mogą filtrować produkty bez konieczności borykania się z długim paskiem bocznym na małych ekranach.

= Does this plugin work on WordPress Multisite? =

Tak. Ta wtyczka jest kompatybilna z WordPress Multisite. Aktywuj go w sieci lub aktywuj na poszczególnych stronach; każda witryna przechowuje własne ustawienia i dane.

== Screenshots ==

1. Filtrowanie fasetowe na stronie produktu: kategorie, przedział cenowy, dostępność i aspekty sprzedaży z licznikami zależnymi na bieżąco, aktywnymi filtrami i siatką wyników.
2. Mobilna szuflada filtrów z naklejonym paskiem „Pokaż wyniki”.
3. Kreator aspektów: dodawaj, zmieniaj kolejność i wpisz aspekty, ustawiaj układ i odbudowuj indeks.

== Development ==

Pełne, czytelne dla człowieka źródło skompilowanych zasobów znajduje się w tej wtyczce w sekcji `resources/`, wraz z narzędziami do kompilacji (`package.json`, `scripts/build-wp.mjs`). Skompilowane pliki w `build/` są generowane z tych źródeł. Aby je odbudować:

1. `instalacja npm`
2. `npm run build`

Używa Vite (skrypty administracyjne i front-end) oraz @wordpress/scripts (bloki). Nie ma żadnego zaciemniania; każdy wysłany zasób można zregenerować z dołączonych źródeł. Publiczne repozytorium źródeł jest również dostępne pod adresem https://github.com/wppoland/sieve.

== Changelog ==

= 1.0.1 =
* Pierwsza stabilna wersja.

= 0.9.7 =
* Dokumenty: dodano sekcję „Możesz też polubić” łączącą inne bezpłatne wtyczki WPPoland WooCommerce. Żadnych zmian funkcjonalnych.

= 0.9.6 =
* Nowość: widżety Elementora do wyszukiwania produktów i filtrowania produktów (działa na Elementorze 3.x i 4.0).

= 0.9.5 =
* Administrator: napraw tekst pomocy indeksu wyświetlany w każdym wierszu aspektu; dodaj powiadomienia o pustym indeksie i pustym aspekcie, zgrupowany selektor źródeł, komunikaty o błędach zapisu/ponownego indeksowania oraz stan awarii ładowania.
* Administrator: wbudowana pomoc dotycząca stylu w panelu Wygląd.

= 0.9.4 =
* Rozszerzenie: filtr `sieve_search_product_ids` w `SearchResolver`, dzięki czemu dodatki PRO mogą kierować aspekt wyszukiwania w siatce i wyszukiwanie predykcyjne przez SearchWP lub Algolia.

= 0.9.3 =
* Rozszerzenie: filtry `sieve_facet_body`, `sieve_facet_types` i `sieve_facet_catalog` oraz `FacetTypeRegistry` w katalogu administratora Odpowiedź REST, dzięki czemu dodatki PRO mogą rejestrować zaawansowane prezentacje aspektów (np. ocena w gwiazdkach).

= 0.9.2 =
* Rozszerzenie: filtr `sieve_settings` i ustawienie `układu' (pasek boczny, skumulowany, wbudowany), dzięki czemu dodatki PRO mogą obracać układy paneli filtrów i liczbę kolumn. FilterEngine stosuje klasy modyfikatorów układu w `.sieve-app`.

= 0.9.1 =
* Rozszerzenie: filtr `sieve_facets` i kontekst strony (`FacetContext`), dzięki czemu dodatki PRO mogą pokazywać lub ukrywać aspekty według kategorii, strony sklepu lub roli klienta. Żądania AJAX zachowują kontekst poprzez zmienne zapytania `sf_ctx_*`.

= 0.9.0 =
* Polski: odświeżony, atrakcyjniejszy interfejs filtra. Zwijane grupy aspektów, rząd żetonów „Aktywne filtry” z wyraźniejszymi przyciskami usuwania, przyjazny pusty stan z akcją „Wyczyść wszystkie filtry” jednym kliknięciem, dostępne pokrętło ładowania i komunikat o błędzie umożliwiający ponowną próbę w przypadku niepowodzenia aktualizacji.
* Projekt: niestandardowe właściwości CSS z motywem, płynne dopasowywanie rozmiaru, automatyczny tryb ciemny (preferowany schemat kolorów) i gustowne przejścia uwzględniające preferencje zmniejszonego ruchu. Brak zmiany układu po zastosowaniu filtrów.
* Administrator: wbudowana pomoc dotycząca każdego ustawienia, w tym krótki opis tego, jak każdy typ aspektu wygląda dla kupujących.
* Dostępność: grupy aspektów ujawniają swój stan rozwinięty/zwinięty, liczba wyników jest grzecznie ogłaszana, obszary paginacji i filtrowania są oznaczone, a przyciski usuwania mają przejrzyste, dostępne nazwy.

= 0.8.2 =
* Zgodność: udokumentowane publiczne repozytorium źródeł i etapy kompilacji skompilowanych zasobów (wytyczne dotyczące wtyczek WordPress.org).

= 0.8.1 =
* Internacjonalizacja: ciągi znaków interfejsu administracyjnego i interfejsu JavaScript są teraz zawarte w szablonie tłumaczenia, więc cała wtyczka (nie tylko strona PHP) może zostać w pełni przetłumaczona.

= 0.8.0 =
* Nowość: Ustawienia wyglądu. Wybierz gotowe ustawienie stylu (domyślny, minimalny, z obramowaniem, miękki, bez stylu) i dostosuj kolory akcentu, obramowania, wyciszonego tekstu i tła z poziomu administratora, korzystając z podglądu na żywo i wskazówki dotyczącej kontrastu. Dotyczy zarówno filtra, jak i wyszukiwania predykcyjnego. Zero dodatkowych żądań, brak zmiany układu, w pełni kompatybilny wstecz.

= 0.7.0 =
* Wyszukiwanie działa teraz jak filtr: wpisz, aby zawęzić siatkę na żywo za pomocą sugestii predykcyjnych, tolerujących znaki diakrytyczne i literówki, które można łączyć z każdym aspektem oraz które są bezpieczne dla adresów URL i przycisku Wstecz. Zależne liczby aspektów odzwierciedlają teraz także aktywne wyszukiwanie.

= 0.6.0 =
* Wyszukiwanie predykcyjne jest teraz niewrażliwe na znaki diakrytyczne i toleruje literówki. Dopasowuje tytuły produktów i SKU ignorując różnice diakrytyczne (więc „lozko” znajduje „łóżko”), toleruje drobne literówki, a pasujące kategorie odnajduje się w ten sam sposób. Ta wersja powoduje jednorazową przebudowę indeksu wyszukiwania.

= 0.5.0 =
* Wyszukiwanie predykcyjne wykracza teraz poza tytuły produktów: częściowe podanie SKU wyświetla produkt po jego kodzie, nawet jeśli brakuje tytułu, a pasujące kategorie produktów pojawiają się jako osobna grupa na liście rozwijanej, dzięki czemu kupujący może przejść bezpośrednio do przefiltrowanego archiwum. Wyniki i kategorie są pogrupowane za pomocą nagłówków, a nawigacja za pomocą klawiatury umożliwia poruszanie się po obu nagłówkach.

= 0.4.0 =
* Nowe typy aspektów: Autouzupełnianie (pole wyszukiwania, które filtruje opcje aspektu podczas pisania, dla aspektów o wielu wartościach) i indeks A-Z (pasek alfabetu, który filtruje opcje według pierwszej litery). Obydwa filtrują po stronie klienta bez dodatkowych żądań i degradują do zwykłej listy opcji bez JavaScript.

= 0.3.0 =
* Nowość: predykcyjne wyszukiwanie produktów. Krótki kod `[sieve_search]` i blok „Sieve Search” renderują dostępne pole wyszukiwania z natychmiastową listą rozwijaną (miniatury produktów, ceny, SKU), pełną nawigacją za pomocą klawiatury i łączem „wyświetl wszystkie wyniki”. Zbudowany na wyszukiwarce produktów WooCommerce, ładowany jako samodzielny, lekki pakiet, dzięki czemu strony bez niego działają szybko.

= 0.2.0 =
* Nowe typy aspektów: próbki kolorów i obrazów (z kolorem/obrazem według terminu oraz automatyczne odgadywanie kolorów na podstawie popularnych nazw kolorów) i aspekty kategorii hierarchicznej (drzewo), które pokazują tylko gałęzie prowadzące do wyników.

= 0.1.2 =
* Administrator: czystsze wiersze konstruktora aspektów (wyrównane elementy sterujące, zgrupowane przyciski zmiany kolejności/usuwania z pierwszym/ostatnim wyłączonym stanem, źródło pola pokazane jako podpis).

= 0.1.1 =
* Zgodność: dodano właściciela wtyczki do listy Współtwórców i uwzględniono źródła czytelne dla człowieka oraz kroki tworzenia skompilowanych zasobów (wytyczne dotyczące wtyczek WordPress.org).

= 0.1.0 =
* Wstępna wersja MVP: wstępnie zbudowany indeks, filtrowanie AJAX ze stanem adresu URL, zależna liczba aspektów, pola wyboru / radio / lista rozwijana / zakres / aspekty wyszukiwania, sortowanie, aktywne filtry, paginacja, szuflada filtrów mobilnych, kreator aspektów React, krótki kod `[sieve]` i blok „Sieve Filter”.
