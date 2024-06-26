# WallaceMaxters\Timespan Library

This is a library to work only with time durations in PHP.

## Instalation

```bash
composer require wallacemaxters/timespan
```

## How to work

With it you can work with the time very simply:


```php
use WallaceMaxters\Timespan\Timespan;

$time = new Timespan(0, 0, 10);

echo $time->format(); // '00:00:10'

$time->addSeconds(30);

echo $time->format(); // '00:00:40'

$time->addSeconds(-50);

echo $time->format(); // '-00:00:10'

$time->addMinutes(2);

echo $time->format('%i minutes %s seconds');  // '1 minutes 50 seconds'

```

An example of time duration:

```php
$time = Timespan::createFromFormat(
    Timespan::DEFAULT_FORMAT, 
    '26:00:00'
);

echo $time->format(); // '26:00:00'
```


For create time duration from DateTime Diff, you can use `Timespan::createFromDateDiff`.

```php
$timespan = Timespan::createFromDateDiff(
    new DateTime('2021-01-01 23:00:00'),
    new DateTime('2021-01-03 02:00:00')
);

echo $timespan->format(); // '27:00:00'
```


## Available time format characters


<table>
    <tr>
        <th>Character</th>
        <th>Description</th>
    </tr>
    <tr>
        <td>%h</td>
        <td>The hour value.</td>
    </tr>
    <tr>
        <td>%i</td>
        <td>The minute value. From `0`to `59`.</td>
    </tr>
    <tr>
        <td>%I</td>
        <td>The total minutes value.</td>
    </tr>
    <tr>
        <td>%s</td>
        <td>The second value. From `0`to `59`. </td>
    </tr>
</table>
