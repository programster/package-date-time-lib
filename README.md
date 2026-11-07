Date/Time Library Package
=========================

This is a package for PHP that contains a library for handling dates/times. This makes use of the
[brick/date-time](https://github.com/brick/date-time) library underneath, and adds:

* `LocalDateCollection` - a collection that can only hold `Brick\DateTime\LocalDate` objects, with helpers for
  removing weekends or specific dates.
* `TimeLib` - static helpers for working with sets of dates, such as finding contiguous ranges, weekends and
  intersections.
* `TimeZoneEnum` - an enum of every timezone, so you can't mistype one.


## Requirements

* PHP 8.1 or newer.


## Installation

```bash
composer require programster/date-time-lib
```


## Usage

All of the examples below assume these imports:

```php
use Brick\DateTime\DayOfWeek;
use Brick\DateTime\LocalDate;
use Brick\DateTime\LocalDateRange;
use Programster\DateTime\LocalDateCollection;
use Programster\DateTime\TimeLib;
use Programster\DateTime\TimeZoneEnum;
```


### LocalDateCollection

```php
$dates = new LocalDateCollection(LocalDate::of(2024, 1, 1), LocalDate::of(2024, 1, 2));
$dates[] = LocalDate::of(2024, 1, 3);

// Anything that isn't a LocalDate is rejected with a TypeError.
$dates[] = '2024-01-04';
```

Create a collection from strings. Each string must exactly match the format and be a real date, otherwise an
`InvalidArgumentException` is thrown. E.g. `2020-02-30` is rejected rather than being rolled over to the 1st of March.

```php
$dates = LocalDateCollection::fromArrayOfStringDates(['2020-12-25', '2020-12-26']);
$ukDates = LocalDateCollection::fromArrayOfStringDates(['25/12/2020'], 'd/m/Y');
```

Get copies of the collection with some dates removed. These never change the original collection.

```php
$weekdays = $dates->withoutWeekends();

// If your weekend isn't Saturday and Sunday, pass the days that are.
$weekdays = $dates->withoutWeekends([DayOfWeek::FRIDAY, DayOfWeek::SATURDAY]);

$withoutChristmas = $dates->withoutDates(LocalDate::of(2020, 12, 25));
$withoutHolidays = $dates->withoutDatesInCollection($holidays);
```

Convert the collection back to strings:

```php
$strings = $dates->getArrayOfStringsForm(); // e.g. ['2020-12-25', '2020-12-26']
```


### TimeLib

Group dates into contiguous ranges. The dates don't need to be in order, and duplicates are ignored.

```php
// Gives two LocalDateRange objects: 2024-01-01 to 2024-01-02, and 2024-01-05 to 2024-01-05.
$ranges = TimeLib::convertDatesToContiguousDateRanges(
    LocalDate::of(2024, 1, 2),
    LocalDate::of(2024, 1, 5),
    LocalDate::of(2024, 1, 1),
);
```

Find the largest run of consecutive dates. The first parameter is whether to keep duplicate dates in the result.

```php
$longestRun = TimeLib::getLargestContiguousDatesSubset(false, ...$dates);
```

Get every date in a range:

```php
$january = LocalDateRange::of(LocalDate::of(2024, 1, 1), LocalDate::of(2024, 1, 31));
$dates = TimeLib::convertDateRangeToDateCollection($january);
```

Work with weekends. `getWeekendsBetweenDates()` returns every weekend date from the earliest date in the collection
to the latest, whereas `getWeekendDatesInSet()` only returns the weekend dates that are in the collection itself.
Both default to Saturday and Sunday, and take an array of `DayOfWeek` cases to change that.

```php
$weekendsInJanuary = TimeLib::getWeekendsBetweenDates($dates);
$fridaysAndSaturdays = TimeLib::getWeekendDatesInSet($dates, [DayOfWeek::FRIDAY, DayOfWeek::SATURDAY]);
```

Get the dates that are in every one of several collections. Each date appears in the result only once.

```php
$commonDates = TimeLib::getDatesIntersection($aliceFreeDates, $bobFreeDates, $carolFreeDates);
```

Create a Brick timezone from the enum:

```php
$timezone = TimeLib::brickTimeZone(TimeZoneEnum::Europe_London);
$now = LocalDate::now($timezone);
```


## Upgrading from 0.4 to 0.5

0.5 contains breaking changes:

* `TimeLib::getWeekendsBetweenDates()` and `TimeLib::getWeekendDatesInSet()` now take a `LocalDateCollection` first,
  followed by the weekend days, so the Saturday/Sunday default can actually be used. E.g.
  `TimeLib::getWeekendsBetweenDates([6, 7], ...$dates)` becomes
  `TimeLib::getWeekendsBetweenDates(new LocalDateCollection(...$dates))`.
* The weekend days are now given as `DayOfWeek` cases, such as `DayOfWeek::SATURDAY`, rather than integers. This
  applies to `LocalDateCollection::withoutWeekends()` too. Integers are rejected with an `InvalidArgumentException`,
  because PHP's own `date('w')` numbers Sunday as `0`, whereas Brick numbers it as `7`, making it easy to pick the
  wrong day.
* `getWeekendsBetweenDates()` throws an `InvalidArgumentException` if the collection is empty.
* `LocalDateCollection::fromArrayOfStringDates()` throws an `InvalidArgumentException` for strings that don't match
  the format or aren't real dates, rather than quietly rolling them over or failing with a `TypeError`.
* Adding something other than a `LocalDate` to a `LocalDateCollection` throws a `TypeError` rather than an
  `Exception`. This now also applies to `exchangeArray()`.
* `TimeLib::getDatesIntersection()` returns an empty collection when given no collections, and no longer returns
  duplicate dates.
* `TimeLib::brickTimeZone()` takes this package's `Programster\DateTime\TimeZoneEnum`, rather than the one from
  `programster/core-libs`, which this package no longer depends on.


## Testing

```bash
composer install
composer test
```

This exits with `0` if every test passes, and `1` if any fail.
