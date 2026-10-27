# EC-SI-SieStorage

A web dashboard that reports storage usage for the Intel Shannon site.

## What it does

- `stoddump/dump.sh` runs Intel's `stodstatus` tool for each site and
  saves allocations, storage users, areas, disks, file servers, business
  groups, owners and projects as CSV files, with a daily snapshot.
- The PHP pages read those CSV files and show usage by business group,
  owner, project and user, plus usage trends over time.
- `createreport.php` builds a report from the data.

## Tech

- PHP
- JavaScript with Highcharts and jQuery
- Bash

## Running

Serve the folder with PHP and run `stoddump/dump.sh` from cron on a
host where `stodstatus` is available.

## License

Licensed under the Apache License 2.0. See [LICENSE](LICENSE).
